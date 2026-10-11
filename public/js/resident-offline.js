(function () {
    'use strict';

    var userId = Number(window.BIS_USER_ID || 0);
    var role = String(window.BIS_USER_ROLE || '').toLowerCase();
    var storageKey = 'bis_pending_sync_queue_' + (userId || 'guest');
    var DB_NAME = 'bis-offline-outbox';
    var STORE_NAME = 'queue';
    var nativeFetch = window.fetch ? window.fetch.bind(window) : null;
    var flushing = false;
    var dbReady = null;

    function canUseOffline() {
        return userId > 0 && (role === 'resident' || role === 'admin' || role === 'secretary');
    }

    function operationForForm(form) {
        if (!(form instanceof HTMLFormElement)) {
            return '';
        }
        var marked = String(form.getAttribute('data-offline-sync') || '');
        if (marked === 'clearance_request' || marked === 'blotter_submission') {
            return marked;
        }
        var action = String(form.getAttribute('action') || '');
        if (/\/clearance\/store(?:\/|$|\?)/i.test(action) || form.id === 'clearanceForm') {
            return 'clearance_request';
        }
        if (/\/blotter\/store(?:\/|$|\?)/i.test(action) || form.id === 'blotterForm') {
            return 'blotter_submission';
        }
        return '';
    }

    function allowedForRole(operation) {
        if (role === 'resident') {
            return operation === 'clearance_request';
        }
        if (role === 'admin' || role === 'secretary') {
            return operation === 'blotter_submission';
        }
        return false;
    }

    function openDatabase() {
        if (dbReady) {
            return dbReady;
        }
        dbReady = new Promise(function (resolve, reject) {
            if (!window.indexedDB) {
                resolve(null);
                return;
            }
            var request = indexedDB.open(DB_NAME, 1);
            request.onupgradeneeded = function () {
                if (!request.result.objectStoreNames.contains(STORE_NAME)) {
                    request.result.createObjectStore(STORE_NAME, { keyPath: 'id' });
                }
            };
            request.onsuccess = function () {
                resolve(request.result);
            };
            request.onerror = function () {
                reject(request.error);
            };
        }).catch(function () {
            return null;
        });
        return dbReady;
    }

    function readLocalQueue() {
        try {
            var raw = localStorage.getItem(storageKey);
            var queue = raw ? JSON.parse(raw) : [];
            return Array.isArray(queue) ? queue : [];
        } catch (error) {
            return [];
        }
    }

    function writeLocalQueue(queue) {
        localStorage.setItem(storageKey, JSON.stringify(queue));
    }

    async function readQueue() {
        var db = await openDatabase();
        if (!db) {
            return readLocalQueue();
        }
        return new Promise(function (resolve) {
            var request = db.transaction(STORE_NAME, 'readonly').objectStore(STORE_NAME).getAll();
            request.onsuccess = function () {
                var rows = Array.isArray(request.result) ? request.result : [];
                rows.sort(function (a, b) {
                    return (a.createdAt || 0) - (b.createdAt || 0);
                });
                resolve(rows);
            };
            request.onerror = function () {
                resolve(readLocalQueue());
            };
        });
    }

    async function writeQueue(queue) {
        var db = await openDatabase();
        if (!db) {
            writeLocalQueue(queue);
            return;
        }
        await new Promise(function (resolve) {
            var tx = db.transaction(STORE_NAME, 'readwrite');
            var store = tx.objectStore(STORE_NAME);
            store.clear();
            (queue || []).forEach(function (item) {
                store.put(item);
            });
            tx.oncomplete = function () {
                resolve();
            };
            tx.onerror = function () {
                writeLocalQueue(queue);
                resolve();
            };
        });
        writeLocalQueue((queue || []).map(function (item) {
            return {
                id: item.id,
                url: item.url,
                operation: item.operation,
                createdAt: item.createdAt,
                summary: item.summary || null
            };
        }));
    }

    async function remember(entry) {
        var queue = await readQueue();
        queue.push(entry);
        await writeQueue(queue);
    }

    function banner() {
        var node = document.getElementById('bisOfflineStatusBanner');
        if (node) {
            return node;
        }
        node = document.createElement('div');
        node.id = 'bisOfflineStatusBanner';
        node.setAttribute('role', 'status');
        node.style.position = 'fixed';
        node.style.right = '16px';
        node.style.bottom = '16px';
        node.style.zIndex = '99999';
        node.style.maxWidth = '360px';
        node.style.padding = '12px 14px';
        node.style.background = '#16325c';
        node.style.color = '#fff';
        node.style.fontSize = '12px';
        node.style.fontWeight = '600';
        node.style.lineHeight = '1.45';
        node.style.fontFamily = 'Poppins, sans-serif';
        node.style.boxShadow = '0 10px 24px rgba(22,50,92,.28)';
        node.style.display = 'none';
        document.body.appendChild(node);
        return node;
    }

    function showStatus(message, tone) {
        var node = banner();
        node.textContent = message;
        node.style.display = 'block';
        node.style.background = tone === 'ok' ? '#1f8f5f' : (tone === 'wait' ? '#a66000' : '#16325c');
    }

    function hideStatusLater() {
        window.setTimeout(function () {
            readQueue().then(function (queue) {
                if (navigator.onLine && queue.length === 0) {
                    var node = document.getElementById('bisOfflineStatusBanner');
                    if (node) {
                        node.style.display = 'none';
                    }
                }
            });
        }, 4000);
    }

    function shouldQueueForm(form) {
        if (!canUseOffline() || form.dataset.offlineSync === 'off') {
            return false;
        }
        if (form.querySelector('input[type="password"]')) {
            return false;
        }
        var method = String(form.getAttribute('method') || form.method || 'get').toUpperCase();
        if (method === 'GET' || method === 'HEAD') {
            return false;
        }
        return allowedForRole(operationForForm(form));
    }

    function fieldText(fields, key) {
        var match = (fields || []).filter(function (field) {
            return field.key === key && !field.file;
        }).pop();
        return match && match.value ? String(match.value) : '';
    }

    function validateForm(form) {
        if (typeof window.bisValidateOfflineForm === 'function') {
            try {
                if (window.bisValidateOfflineForm(form) === false) {
                    return false;
                }
            } catch (error) {
                return false;
            }
        }
        if (form.id === 'clearanceForm') {
            var chosen = form.querySelector('input[name="document_type"]:checked:not(:disabled)');
            var purpose = form.querySelector('[name="purpose"]');
            var purposeOther = form.querySelector('[name="purpose_other"]');
            var otherMissing = purpose && purpose.value === 'Other' && purposeOther && purposeOther.value.trim() === '';
            if (!chosen || (purpose && purpose.value === '') || otherMissing) {
                showStatus(
                    !chosen
                        ? 'Choose a document type before saving this request.'
                        : (otherMissing ? 'Specify the exact purpose of your request.' : 'Choose a purpose before saving this request.'),
                    'wait'
                );
                return false;
            }
        }
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            if (typeof form.reportValidity === 'function') {
                form.reportValidity();
            }
            showStatus('Complete the required fields. The submission will then be saved on this device.', 'wait');
            return false;
        }
        return true;
    }

    function readFile(file) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function () {
                resolve(String(reader.result || ''));
            };
            reader.onerror = function () {
                reject(reader.error || new Error('Could not read the file.'));
            };
            reader.readAsDataURL(file);
        });
    }

    function dataUrlToBlob(dataUrl) {
        var parts = String(dataUrl).split(',');
        var header = parts[0] || '';
        var body = parts[1] || '';
        var mime = (header.match(/data:([^;]+)/) || [])[1] || 'application/octet-stream';
        var binary = atob(body);
        var bytes = new Uint8Array(binary.length);
        for (var i = 0; i < binary.length; i += 1) {
            bytes[i] = binary.charCodeAt(i);
        }
        return new Blob([bytes], { type: mime });
    }

    async function serializeForm(form) {
        var fields = [];
        var total = 0;
        var data = new FormData(form);
        var entries = Array.from(data.entries());
        for (var i = 0; i < entries.length; i += 1) {
            var key = entries[i][0];
            var value = entries[i][1];
            if (typeof File !== 'undefined' && value instanceof File) {
                if (!value.name || value.size === 0) {
                    continue;
                }
                if (value.size > 5 * 1024 * 1024) {
                    throw new Error('A file is too large to save on this device. Connect to the internet and submit it directly.');
                }
                total += value.size;
                if (total > 12 * 1024 * 1024) {
                    throw new Error('These attachments are too large to save offline. Connect to the internet and submit them directly.');
                }
                fields.push({
                    key: key,
                    file: {
                        name: value.name,
                        type: value.type || 'application/octet-stream',
                        dataUrl: await readFile(value)
                    }
                });
            } else {
                fields.push({ key: key, value: String(value) });
            }
        }
        return fields;
    }

    function buildBody(entry) {
        var body = new FormData();
        (entry.fields || []).forEach(function (field) {
            if (field.file && field.file.dataUrl) {
                var blob = dataUrlToBlob(field.file.dataUrl);
                body.append(field.key, new File([blob], field.file.name, { type: field.file.type }));
            } else {
                body.append(field.key, field.value == null ? '' : String(field.value));
            }
        });
        return body;
    }

    function plainMessage(html) {
        var box = document.createElement('div');
        box.innerHTML = html;
        return (box.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function entrySummary(operation, fields) {
        if (operation === 'clearance_request') {
            return {
                name: fieldText(fields, 'for_member') || 'You',
                relationship: fieldText(fields, 'member_relationship'),
                document: fieldText(fields, 'document_type') || 'Document request',
                purpose: fieldText(fields, 'purpose') || '—'
            };
        }
        return {
            name: fieldText(fields, 'complainant_name') || 'Complainant',
            document: fieldText(fields, 'incident_type') || 'Blotter report',
            purpose: fieldText(fields, 'incident_date') || '—',
            relationship: fieldText(fields, 'respondent_name') || fieldText(fields, 'persons_involved') || '—'
        };
    }

    function releaseLabel() {
        var date = new Date();
        var day = date.getDay();
        if (day === 6) {
            date.setDate(date.getDate() + 2);
        } else if (day === 0) {
            date.setDate(date.getDate() + 1);
        }
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var dayNum = String(date.getDate()).padStart(2, '0');
        return months[date.getMonth()] + ' ' + dayNum + ', ' + date.getFullYear();
    }

    function closeRequestModal(form) {
        if (typeof window.closeModal === 'function') {
            window.closeModal('newModal');
        }
        var overlay = document.getElementById('newModal');
        if (overlay && (!form || overlay.contains(form))) {
            overlay.classList.remove('active');
        }
        document.querySelectorAll('.db-modal-overlay.active').forEach(function (node) {
            if (!form || node.contains(form)) {
                node.classList.remove('active');
            }
        });
    }

    function showSuccessFlash(message) {
        var existing = document.getElementById('clearanceFlash');
        if (existing) {
            existing.remove();
        }
        var container = document.querySelector('.bis-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'bis-toast-container';
            document.body.appendChild(container);
        }
        var flash = document.createElement('div');
        flash.id = 'clearanceFlash';
        flash.className = 'db-alert db-alert--success bis-toast';
        flash.setAttribute('role', 'status');
        var icon = document.createElement('i');
        icon.className = 'fas fa-check-circle';
        flash.appendChild(icon);
        flash.appendChild(document.createTextNode(' ' + message));
        container.appendChild(flash);
        window.setTimeout(function () {
            flash.classList.add('is-hiding');
            window.setTimeout(function () {
                if (flash.parentNode) {
                    flash.remove();
                }
            }, 250);
        }, 5000);
    }

    function paintPendingClearance(summary) {
        var tableBody = document.querySelector('#requestsTable tbody');
        if (!tableBody) {
            return;
        }
        var emptyRow = tableBody.querySelector('.clr-empty');
        if (emptyRow && emptyRow.closest('tr')) {
            emptyRow.closest('tr').remove();
        }
        var row = document.createElement('tr');
        row.setAttribute('data-offline-pending', '1');
        row.innerHTML = '<td></td><td></td><td></td><td>Saved on this device</td><td>—</td><td><span class="clr-badge clr-badge--pending"><i class="fas fa-clock"></i> Waiting to send</span></td>';
        row.cells[0].innerHTML = '<div class="clr-for-name"></div>' + (summary.relationship ? '<div class="clr-for-rel"></div>' : '');
        row.cells[0].querySelector('.clr-for-name').textContent = summary.name || 'You';
        if (summary.relationship) {
            row.cells[0].querySelector('.clr-for-rel').textContent = summary.relationship;
        }
        row.cells[1].textContent = summary.document || 'Document request';
        row.cells[2].textContent = summary.purpose || '—';
        tableBody.insertBefore(row, tableBody.firstChild);
    }

    function paintPendingBlotter(summary) {
        var tableBody = document.querySelector('.db-table tbody');
        if (!tableBody || !/\/blotter\/?$/.test(window.location.pathname.replace(/\/index\.php/, ''))) {
            return;
        }
        if (tableBody.querySelector('td[colspan]')) {
            tableBody.innerHTML = '';
        }
        var row = document.createElement('tr');
        row.setAttribute('data-offline-pending', '1');
        row.innerHTML = '<td></td><td></td><td></td><td></td><td></td><td></td><td>—</td>';
        row.cells[0].innerHTML = '<div style="font-weight:600;font-size:13px;"></div><div style="font-size:11px;color:#9aa0b4;">Saved on this device</div>';
        row.cells[0].querySelector('div').textContent = summary.name || 'Complainant';
        row.cells[1].textContent = summary.document || 'Blotter report';
        row.cells[2].textContent = summary.purpose || '—';
        row.cells[3].textContent = summary.relationship || '—';
        row.cells[4].textContent = 'Waiting to send';
        row.cells[5].innerHTML = '<span class="db-badge db-badge--pending"><i class="fas fa-clock"></i> Waiting to send</span>';
        tableBody.insertBefore(row, tableBody.firstChild);
    }

    function showSavedRequest(form, fields, operation) {
        var note = form.querySelector('[data-offline-sync-status]');
        if (note) {
            note.textContent = operation === 'blotter_submission'
                ? 'Saved on this device. The report will be sent when the internet returns.'
                : 'Saved on this device. The request will be sent when the internet returns.';
        }
        closeRequestModal(form);
        var summary = entrySummary(operation, fields);
        if (operation === 'clearance_request') {
            showSuccessFlash('Request saved on this device. It will be sent when you are back online. Estimated release: ' + releaseLabel());
            paintPendingClearance(summary);
        } else {
            showSuccessFlash('Blotter report saved on this device. It will be sent automatically when the connection returns.');
            paintPendingBlotter(summary);
        }
    }

    async function flushQueue() {
        if (flushing || !navigator.onLine || !nativeFetch || !canUseOffline()) {
            return;
        }
        var queue = await readQueue();
        if (!queue.length) {
            return;
        }
        flushing = true;
        showStatus('Back online. Sending ' + queue.length + ' saved submission' + (queue.length === 1 ? '' : 's') + '...', 'sync');
        var remaining = queue.slice();
        var sentAny = false;
        while (remaining.length && navigator.onLine) {
            var entry = remaining[0];
            try {
                var response = await nativeFetch(entry.url, {
                    method: 'POST',
                    body: buildBody(entry),
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                var text = '';
                try {
                    text = await response.text();
                } catch (readError) {
                    text = '';
                }
                if (response.status === 401 || response.status === 419) {
                    showStatus('Sign in again so the saved submission can be sent.', 'wait');
                    break;
                }
                if (response.status >= 500) {
                    entry.attempts = (entry.attempts || 0) + 1;
                    if (entry.attempts >= 4) {
                        remaining.shift();
                        showStatus('A saved submission could not be sent. Please submit it again.', 'wait');
                    } else {
                        showStatus('The office could not be reached. The submission stays on this device.', 'wait');
                        break;
                    }
                } else {
                    remaining.shift();
                    sentAny = true;
                    var errorHtml = text.match(/db-alert--(?:error|danger)[\s\S]*?<\/div>/i);
                    if (errorHtml) {
                        showStatus(plainMessage(errorHtml[0]) || 'The saved submission was rejected. Please review it and submit again.', 'wait');
                    }
                }
            } catch (error) {
                showStatus('Still offline. Saved submissions will be sent when the connection returns.', 'wait');
                break;
            }
            await writeQueue(remaining);
        }
        await writeQueue(remaining);
        flushing = false;
        if (!remaining.length && navigator.onLine) {
            showStatus('Saved submissions were sent.', 'ok');
            hideStatusLater();
            if (sentAny && /\/(clearance|blotter)(?:\/|$)/.test(window.location.pathname)) {
                window.setTimeout(function () {
                    window.location.reload();
                }, 800);
            }
        }
    }

    function queueForm(form) {
        if (form.dataset.bisSaving === '1') {
            return Promise.resolve();
        }
        if (!validateForm(form)) {
            return Promise.resolve();
        }
        form.dataset.bisSaving = '1';
        var operation = operationForForm(form);
        return serializeForm(form).then(function (fields) {
            return remember({
                id: Date.now().toString(16) + Math.random().toString(16).slice(2),
                url: form.action || window.location.href,
                operation: operation,
                fields: fields,
                summary: entrySummary(operation, fields),
                createdAt: Date.now(),
                attempts: 0
            }).then(function () {
                showSavedRequest(form, fields, operation);
                showStatus('Saved on this device. It will be sent automatically when you are back online.', 'wait');
                form.reset();
            });
        }).catch(function (error) {
            showStatus(error && error.message ? error.message : 'This form could not be saved offline.', 'wait');
        }).then(function () {
            form.dataset.bisSaving = '';
        });
    }

    function interceptSubmit(event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (form.querySelector('input[type="password"]') && !navigator.onLine) {
            event.preventDefault();
            showStatus('Sign-in and password changes need a connection. Offline mode does not allow login.', 'wait');
            return;
        }
        if (!shouldQueueForm(form)) {
            return;
        }
        if (form.dataset.bisSaving === '1') {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }
        if (navigator.onLine) {
            form.dataset.bisSaving = '1';
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        queueForm(form);
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest('button, input') : null;
        if (!button || !button.form) {
            return;
        }
        var type = String(button.getAttribute('type') || button.type || 'submit').toLowerCase();
        if (type !== 'submit') {
            return;
        }
        var form = button.form;
        if (!shouldQueueForm(form) || navigator.onLine) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        queueForm(form);
    }, true);

    document.addEventListener('submit', interceptSubmit, true);

    window.addEventListener('online', function () {
        flushQueue();
    });

    window.addEventListener('offline', function () {
        if (!canUseOffline()) {
            return;
        }
        readQueue().then(function (queue) {
            var count = queue.length;
            showStatus(count
                ? 'You are offline. ' + count + ' saved submission' + (count === 1 ? '' : 's') + ' will be sent when the connection returns.'
                : 'You are offline. Clearance and blotter submissions on this page are saved on this device and sent when the connection returns.', 'wait');
        });
    });

    function tellServiceWorker() {
        if (!('serviceWorker' in navigator) || !userId) {
            return;
        }
        var message = { type: 'bis-user', userId: String(userId), role: role };
        var send = function (worker) {
            if (worker) {
                worker.postMessage(message);
            }
        };
        navigator.serviceWorker.ready.then(function (registration) {
            send(registration.active);
        }).catch(function () {});
        navigator.serviceWorker.addEventListener('controllerchange', function () {
            send(navigator.serviceWorker.controller);
        });
        send(navigator.serviceWorker.controller);
    }

    document.addEventListener('click', function (event) {
        var link = event.target && event.target.closest ? event.target.closest('a[href*="logout"]') : null;
        if (!link || !navigator.serviceWorker || !navigator.serviceWorker.controller) {
            return;
        }
        navigator.serviceWorker.controller.postMessage({ type: 'bis-logout', userId: String(userId || '') });
    }, true);

    if (!document.querySelector('link[rel="manifest"]')) {
        var manifest = document.createElement('link');
        manifest.rel = 'manifest';
        manifest.href = '/manifest.webmanifest';
        document.head.appendChild(manifest);
    }
    if (!document.querySelector('meta[name="theme-color"]')) {
        var theme = document.createElement('meta');
        theme.name = 'theme-color';
        theme.content = '#16325c';
        document.head.appendChild(theme);
    }

    tellServiceWorker();
    readQueue().then(function (queue) {
        queue.forEach(function (entry) {
            if (!entry || !entry.summary) {
                return;
            }
            if (entry.operation === 'clearance_request') {
                paintPendingClearance(entry.summary);
            } else if (entry.operation === 'blotter_submission') {
                paintPendingBlotter(entry.summary);
            }
        });
        if (!navigator.onLine && canUseOffline()) {
            showStatus(queue.length
                ? 'You are offline. ' + queue.length + ' saved submission' + (queue.length === 1 ? '' : 's') + ' will be sent when the connection returns.'
                : 'You are offline. Submissions on this page are saved on this device and sent when the connection returns.', 'wait');
        } else {
            flushQueue();
        }
    });
}());
