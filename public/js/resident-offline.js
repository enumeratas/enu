(function () {
    'use strict';

    var userId = Number(window.BIS_USER_ID || 0);
    var role = String(window.BIS_USER_ROLE || '').toLowerCase();
    var storageKey = 'bis_pending_sync_queue_' + (userId || 'guest');
    var nativeFetch = window.fetch ? window.fetch.bind(window) : null;
    var flushing = false;

    function canQueueRole() {
        return role === 'resident' || role === 'sk';
    }

    function readQueue() {
        try {
            var raw = localStorage.getItem(storageKey);
            var queue = raw ? JSON.parse(raw) : [];
            return Array.isArray(queue) ? queue : [];
        } catch (error) {
            return [];
        }
    }

    function writeQueue(queue) {
        localStorage.setItem(storageKey, JSON.stringify(queue));
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
            if (navigator.onLine && readQueue().length === 0) {
                var node = document.getElementById('bisOfflineStatusBanner');
                if (node) {
                    node.style.display = 'none';
                }
            }
        }, 4000);
    }

    function queueablePath(url) {
        try {
            var path = new URL(url, window.location.origin).pathname;
            if (/\/logout(?:\/|$)/i.test(path)) {
                return false;
            }
            return /^\/(resident|sk)(?:\/|$)/.test(path);
        } catch (error) {
            return false;
        }
    }

    function shouldQueueForm(form) {
        if (!canQueueRole() || !(form instanceof HTMLFormElement)) {
            return false;
        }
        if (form.dataset.offlineSync === 'off') {
            return false;
        }
        if (form.querySelector('input[type="password"]')) {
            return false;
        }
        var method = String(form.getAttribute('method') || form.method || 'get').toUpperCase();
        if (method === 'GET' || method === 'HEAD') {
            return false;
        }
        return queueablePath(form.getAttribute('action') || window.location.href);
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
                if (value.size > 1200000) {
                    throw new Error('A file is too large to save on this device. Connect to the internet and submit it directly.');
                }
                total += value.size;
                if (total > 2500000) {
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

    function remember(entry) {
        var queue = readQueue();
        queue.push(entry);
        writeQueue(queue);
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

    async function flushQueue() {
        if (flushing || !navigator.onLine || !nativeFetch || !canQueueRole()) {
            return;
        }
        var queue = readQueue();
        if (!queue.length) {
            return;
        }
        flushing = true;
        showStatus('Back online. Sending ' + queue.length + ' saved submission' + (queue.length === 1 ? '' : 's') + '...', 'sync');
        var remaining = queue.slice();
        while (remaining.length && navigator.onLine) {
            var entry = remaining[0];
            try {
                var response = await nativeFetch(entry.url, {
                    method: 'POST',
                    body: buildBody(entry),
                    credentials: 'same-origin'
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
                    var errorHtml = text.match(/db-alert--(?:error|danger)[\s\S]*?<\/div>/i);
                    if (errorHtml) {
                        showStatus(plainMessage(errorHtml[0]) || 'The saved submission was rejected. Please review it and submit again.', 'wait');
                    }
                }
            } catch (error) {
                showStatus('Still offline. Saved submissions will be sent when the connection returns.', 'wait');
                break;
            }
            writeQueue(remaining);
        }
        writeQueue(remaining);
        flushing = false;
        if (!remaining.length && navigator.onLine) {
            showStatus('Saved submissions were sent.', 'ok');
            hideStatusLater();
        }
    }

    function fieldText(fields, key) {
        var match = (fields || []).filter(function (field) {
            return field.key === key && !field.file;
        }).pop();
        return match && match.value ? String(match.value) : '';
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

    function showSavedRequest(form, fields) {
        var note = form.querySelector('[data-offline-sync-status]');
        if (note) {
            note.textContent = '';
        }
        closeRequestModal(form);
        var isClearance = form.id === 'clearanceForm' || form.getAttribute('data-offline-sync') === 'clearance_request';
        if (isClearance) {
            showSuccessFlash('Request submitted successfully! Estimated release: ' + releaseLabel());
        }
        var tableBody = document.querySelector('#requestsTable tbody');
        if (tableBody && (form.id === 'clearanceForm' || form.getAttribute('data-offline-sync') === 'clearance_request')) {
            var emptyRow = tableBody.querySelector('.clr-empty');
            if (emptyRow && emptyRow.closest('tr')) {
                emptyRow.closest('tr').remove();
            }
            var row = document.createElement('tr');
            row.innerHTML = '<td></td><td></td><td></td><td>Saved on this device</td><td>—</td><td><span class="clr-badge clr-badge--pending"><i class="fas fa-clock"></i> Waiting to send</span></td>';
            row.cells[0].textContent = fieldText(fields, 'for_member') || 'You';
            row.cells[1].textContent = fieldText(fields, 'document_type') || 'Document request';
            row.cells[2].textContent = fieldText(fields, 'purpose') || '—';
            tableBody.insertBefore(row, tableBody.firstChild);
        }
    }

    function queueForm(form) {
        if (form.dataset.bisSaving === '1') {
            return Promise.resolve();
        }
        form.dataset.bisSaving = '1';
        return serializeForm(form).then(function (fields) {
            remember({
                id: Date.now().toString(16) + Math.random().toString(16).slice(2),
                url: form.action || window.location.href,
                fields: fields,
                createdAt: Date.now(),
                attempts: 0
            });
            showSavedRequest(form, fields);
            if (form.id !== 'clearanceForm' && form.getAttribute('data-offline-sync') !== 'clearance_request') {
                showStatus('Saved on this device. It will be sent automatically when you are back online.', 'wait');
            }
            form.reset();
        }).catch(function (error) {
            showStatus(error && error.message ? error.message : 'This form could not be saved offline.', 'wait');
        }).then(function () {
            form.dataset.bisSaving = '';
        });
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
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            if (typeof form.reportValidity === 'function') {
                form.reportValidity();
            }
            showStatus('Complete the required fields. The request will then be saved on this device.', 'wait');
            return;
        }
        closeRequestModal(form);
        queueForm(form);
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (canQueueRole() && form.querySelector('input[type="password"]') && !navigator.onLine) {
            event.preventDefault();
            showStatus('Password changes need a connection. They stay on the form until you are back online.', 'wait');
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
            if (form.id === 'clearanceForm') {
                var chosenDocument = form.querySelector('input[name="document_type"]:checked:not(:disabled)');
                var purposeField = form.querySelector('[name="purpose"]');
                if (!chosenDocument || (purposeField && purposeField.value === '')) {
                    return;
                }
            }
            form.dataset.bisSaving = '1';
            var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
            window.setTimeout(function () {
                if (!submitButton || submitButton.disabled) {
                    return;
                }
                submitButton.disabled = true;
                if (submitButton.tagName === 'BUTTON') {
                    submitButton.textContent = 'Submitting...';
                }
            }, 0);
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        queueForm(form);
    }, true);

    window.addEventListener('online', function () {
        flushQueue();
    });

    window.addEventListener('offline', function () {
        if (!canQueueRole()) {
            return;
        }
        var count = readQueue().length;
        showStatus(count
            ? 'You are offline. ' + count + ' saved submission' + (count === 1 ? '' : 's') + ' will be sent when the connection returns.'
            : 'You are offline. New submissions on this page are saved on this device and sent when the connection returns.', 'wait');
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
    if (!navigator.onLine && canQueueRole()) {
        var waiting = readQueue().length;
        showStatus(waiting
            ? 'You are offline. ' + waiting + ' saved submission' + (waiting === 1 ? '' : 's') + ' will be sent when the connection returns.'
            : 'You are offline. Submissions are saved on this device and sent when the connection returns.', 'wait');
    } else {
        flushQueue();
    }
}());
