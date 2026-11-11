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

    var BLOTTER_OPERATIONS = [
        'blotter_submission',
        'blotter_status',
        'blotter_narrative',
        'blotter_hearing',
        'blotter_summons',
        'blotter_reschedule'
    ];

    function operationForForm(form) {
        if (!(form instanceof HTMLFormElement)) {
            return '';
        }
        var marked = String(form.getAttribute('data-offline-sync') || '');
        if (marked === 'clearance_request' || BLOTTER_OPERATIONS.indexOf(marked) !== -1) {
            return marked;
        }
        var action = String(form.getAttribute('action') || '');
        if (/\/clearance\/store(?:-secretary)?(?:\/|$|\?)/i.test(action) || form.id === 'clearanceForm' || form.id === 'secretaryClearanceForm') {
            return 'clearance_request';
        }
        if (/\/blotter\/store(?:\/|$|\?)/i.test(action) || form.id === 'blotterForm') {
            return 'blotter_submission';
        }
        if (/\/blotter\/hearing-narrative\/\d+/i.test(action)) {
            return 'blotter_hearing';
        }
        if (/\/blotter\/narrative\/\d+/i.test(action)) {
            return 'blotter_narrative';
        }
        if (/\/blotter\/status\/\d+/i.test(action)) {
            return 'blotter_status';
        }
        if (/\/blotter\/summons\/\d+/i.test(action)) {
            return 'blotter_summons';
        }
        if (/\/blotter\/reschedule\/\d+/i.test(action)) {
            return 'blotter_reschedule';
        }
        return '';
    }

    function allowedForRole(operation) {
        if (role === 'resident') {
            return operation === 'clearance_request';
        }
        if (role === 'admin' || role === 'secretary') {
            return operation === 'clearance_request' || BLOTTER_OPERATIONS.indexOf(operation) !== -1;
        }
        return false;
    }

    function keepsFormValues(operation) {
        return operation !== 'clearance_request' && operation !== 'blotter_submission';
    }

    function savedCopy(operation) {
        var copies = {
            clearance_request: 'Saved on this device. The request will be sent when the internet returns.',
            blotter_submission: 'Saved on this device. The report will be sent when the internet returns.',
            blotter_status: 'Status saved on this device. It will be updated when the connection returns.',
            blotter_narrative: 'Narratives saved on this device. They will be updated when the connection returns.',
            blotter_hearing: 'Hearing notes saved on this device. They will be updated when the connection returns.',
            blotter_summons: 'Hearing schedule saved on this device. The summons will be sent when the connection returns.',
            blotter_reschedule: 'New hearing schedule saved on this device. It will be updated when the connection returns.'
        };
        return copies[operation] || 'Saved on this device. It will be sent when the connection returns.';
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
        if (form.id === 'secretaryClearanceForm') {
            var resident = form.querySelector('[name="resident_user_id"]');
            var documentType = form.querySelector('input[name="document_type"]:checked:not(:disabled)');
            var staffPurpose = form.querySelector('[name="purpose"]');
            if (!resident || String(resident.value || '').trim() === '' || !documentType || !staffPurpose || staffPurpose.value.trim() === '') {
                showStatus(
                    !resident || String(resident.value || '').trim() === ''
                        ? 'Choose a resident before saving this request.'
                        : (!documentType ? 'Choose a document type before saving this request.' : 'Enter a purpose before saving this request.'),
                    'wait'
                );
                return false;
            }
        }
        if (/\/blotter\/hearing-narrative\/\d+/i.test(String(form.getAttribute('action') || ''))) {
            var hearingComplainant = form.querySelector('[name="hearing_complainant_narrative"]');
            var hearingRespondent = form.querySelector('[name="hearing_respondent_narrative"]');
            var hearingComplainantText = hearingComplainant ? hearingComplainant.value.trim() : '';
            var hearingRespondentText = hearingRespondent ? hearingRespondent.value.trim() : '';
            if (hearingComplainantText === '' && hearingRespondentText === '') {
                showStatus('Record at least one hearing narrative before saving.', 'wait');
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
                name: fieldText(fields, 'for_member') || fieldText(fields, 'resident_name') || 'Resident',
                relationship: fieldText(fields, 'member_relationship'),
                document: fieldText(fields, 'document_type') || 'Document request',
                purpose: fieldText(fields, 'purpose') || '—'
            };
        }
        if (operation === 'blotter_status') {
            return {
                name: 'Status update',
                document: 'blotter_status',
                purpose: fieldText(fields, 'status') || 'pending',
                relationship: ''
            };
        }
        return {
            name: fieldText(fields, 'complainant_name') || fieldText(fields, 'respondent_name') || 'Complainant',
            document: fieldText(fields, 'incident_type') || operation || 'Blotter report',
            purpose: fieldText(fields, 'incident_date') || fieldText(fields, 'hearing_date') || '—',
            relationship: fieldText(fields, 'respondent_name') || fieldText(fields, 'persons_involved') || '—'
        };
    }

    function statusLabel(value) {
        var labels = {
            pending: 'Pending',
            under_investigation: 'Under Investigation',
            file_to_action: 'File to Action',
            resolved: 'Resolved',
            dismissed: 'Dismissed'
        };
        return labels[value] || 'Waiting to send';
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
        var path = window.location.pathname.replace(/\/index\.php/, '');
        var staffBody = document.querySelector('#clearanceTable tbody');
        if (staffBody && /^\/(admin|secretary)\/clearance\/?$/.test(path)) {
            if (staffBody.querySelector('td[colspan]')) {
                staffBody.innerHTML = '';
            }
            var staffRow = document.createElement('tr');
            staffRow.setAttribute('data-offline-pending', '1');
            staffRow.innerHTML = '<td></td><td></td><td>1 request</td><td><span class="db-badge db-badge--pending">Waiting to send</span></td><td>Saved on this device</td><td>—</td>';
            staffRow.cells[0].innerHTML = '<div style="font-weight:600;font-size:13px;"></div>';
            staffRow.cells[0].querySelector('div').textContent = summary.name || 'Resident';
            staffRow.cells[1].textContent = summary.document || 'Document request';
            staffBody.insertBefore(staffRow, staffBody.firstChild);
            return;
        }
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

    function paintPendingBlotterUpdate(entry) {
        var match = String(entry && entry.url || '').match(/\/blotter\/(?:status|narrative|hearing-narrative|summons|reschedule)\/(\d+)/i);
        if (!match) {
            return;
        }
        var link = document.querySelector('a[href$="/blotter/' + match[1] + '"], a[href$="/blotter/' + match[1] + '/"]');
        if (!link) {
            return;
        }
        var row = link.closest('tr');
        if (!row || !row.cells || row.cells.length < 6) {
            return;
        }
        row.setAttribute('data-offline-pending', '1');
        if (entry.operation === 'blotter_status') {
            var cell = row.cells[5];
            cell.textContent = '';
            var badge = document.createElement('span');
            badge.className = 'db-badge db-badge--pending';
            var icon = document.createElement('i');
            icon.className = 'fas fa-clock';
            badge.appendChild(icon);
            badge.appendChild(document.createTextNode(' ' + statusLabel(entry.summary && entry.summary.purpose) + ' · waiting to send'));
            cell.appendChild(badge);
            return;
        }
        if (row.getAttribute('data-offline-note') === '1') {
            return;
        }
        row.setAttribute('data-offline-note', '1');
        var note = document.createElement('div');
        note.style.fontSize = '11px';
        note.style.color = '#a66000';
        note.style.marginTop = '4px';
        note.textContent = 'Update waiting to send';
        row.cells[5].appendChild(note);
    }

    function showSavedRequest(form, fields, operation) {
        var note = form.querySelector('[data-offline-sync-status]');
        var message = savedCopy(operation);
        if (note) {
            note.textContent = message;
        }
        if (!keepsFormValues(operation)) {
            closeRequestModal(form);
        }
        var summary = entrySummary(operation, fields);
        if (operation === 'clearance_request') {
            showSuccessFlash('Request saved on this device. It will be sent when you are back online. Estimated release: ' + releaseLabel());
            paintPendingClearance(summary);
        } else if (operation === 'blotter_submission') {
            showSuccessFlash('Blotter report saved on this device. It will be sent automatically when the connection returns.');
            paintPendingBlotter(summary);
        } else {
            showSuccessFlash(message);
            paintPendingBlotterUpdate({
                url: form.action || '',
                operation: operation,
                summary: summary
            });
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
        var sawRejection = false;
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
                if (response.status === 401 || response.status === 419 || /\/login(?:$|[?#])/i.test(response.url || '')) {
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
                        sawRejection = true;
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
        if (!remaining.length && navigator.onLine && sentAny && !sawRejection) {
            showStatus('Saved submissions were sent.', 'ok');
            hideStatusLater();
            if (/\/(clearance|blotter)(?:\/|$)/.test(window.location.pathname)) {
                clearBlotterDraft();
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
                showStatus(savedCopy(operation), 'wait');
                if (!keepsFormValues(operation)) {
                    form.reset();
                    clearBlotterDraft();
                } else {
                    saveBlotterDraft();
                }
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
            window.setTimeout(function () {
                if (event.defaultPrevented) {
                    form.dataset.bisSaving = '';
                }
            }, 0);
            window.setTimeout(function () {
                if (form.isConnected) {
                    form.dataset.bisSaving = '';
                }
            }, 3000);
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
        tellOnlineState();
        flushQueue();
    });

    function offlineHint(count) {
        if (count) {
            return 'You are offline. ' + count + ' saved submission' + (count === 1 ? '' : 's') + ' will be sent when the connection returns.';
        }
        if ((role === 'secretary' || role === 'admin') && /\/blotter(?:\/|$)/.test(window.location.pathname)) {
            return 'You are offline. Blotter reports and case updates on this page are saved on this device and sent when the connection returns.';
        }
        return 'You are offline. Submissions on this page are saved on this device and sent when the connection returns.';
    }

    window.addEventListener('offline', function () {
        tellOnlineState();
        if (!canUseOffline()) {
            return;
        }
        readQueue().then(function (queue) {
            showStatus(offlineHint(queue.length), 'wait');
            syncOfflineBlotterList();
        });
    });

    function isBlotterIndex() {
        var path = window.location.pathname.replace(/\/index\.php/, '').replace(/\/+$/, '');
        return /\/(?:admin|secretary)\/blotter$/.test(path);
    }

    function filterBlotterRows(search, status) {
        var query = String(search || '').trim().toLowerCase();
        var wanted = String(status || '').trim().toLowerCase().replace(/_/g, ' ');
        var visible = 0;
        var dataRows = 0;
        var body = document.querySelector('.db-table tbody');
        document.querySelectorAll('.db-table tbody tr').forEach(function (row) {
            if (row.id === 'blotterOfflineEmpty' || row.querySelector('td[colspan]')) {
                return;
            }
            var badge = row.querySelector('.db-badge');
            var badgeText = badge ? badge.textContent.toLowerCase() : '';
            var pending = row.getAttribute('data-offline-pending') === '1';
            var statusOk = wanted === '' || badgeText.indexOf(wanted) !== -1 || (pending && wanted === 'pending');
            var text = row.textContent.toLowerCase();
            var show = statusOk && (query === '' || text.indexOf(query) !== -1);
            row.style.display = show ? '' : 'none';
            dataRows += 1;
            if (show) {
                visible += 1;
            }
        });
        var placeholder = document.getElementById('blotterOfflineEmpty');
        if (placeholder) {
            placeholder.remove();
        }
        if (body && dataRows > 0 && visible === 0) {
            var emptyRow = document.createElement('tr');
            emptyRow.id = 'blotterOfflineEmpty';
            var cell = document.createElement('td');
            cell.colSpan = 7;
            cell.style.textAlign = 'center';
            cell.style.padding = '32px';
            cell.style.color = '#9aa0b4';
            cell.textContent = 'No blotter reports match this search on this device.';
            emptyRow.appendChild(cell);
            body.appendChild(emptyRow);
        }
    }

    function syncOfflineBlotterList() {
        if (!isBlotterIndex() || navigator.onLine || (role !== 'secretary' && role !== 'admin')) {
            return;
        }
        var form = document.querySelector('form[data-live-results]');
        var params = new URLSearchParams(window.location.search);
        var searchValue = params.get('search') || '';
        var statusValue = params.get('status') || '';
        if (form) {
            var searchInput = form.querySelector('[name="search"]');
            var statusInput = form.querySelector('[name="status"]');
            if (searchInput && searchValue) {
                searchInput.value = searchValue;
            }
            if (statusInput && params.has('status')) {
                statusInput.value = statusValue;
            }
            searchValue = searchInput ? searchInput.value : searchValue;
            statusValue = statusInput ? statusInput.value : statusValue;
        }
        filterBlotterRows(searchValue, statusValue);
    }

    document.addEventListener('change', function (event) {
        var select = event.target;
        if (!select || select.name !== 'status' || !select.form || navigator.onLine || !isBlotterIndex()) {
            return;
        }
        if (role !== 'secretary' && role !== 'admin') {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        var searchInput = select.form.querySelector('[name="search"]');
        filterBlotterRows(searchInput ? searchInput.value : '', select.value);
    }, true);

    document.addEventListener('input', function (event) {
        var input = event.target;
        if (!input || input.name !== 'search' || !input.form || navigator.onLine || !isBlotterIndex()) {
            return;
        }
        if (role !== 'secretary' && role !== 'admin') {
            return;
        }
        var statusInput = input.form.querySelector('[name="status"]');
        filterBlotterRows(input.value, statusInput ? statusInput.value : '');
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || navigator.onLine || !isBlotterIndex()) {
            return;
        }
        var method = String(form.getAttribute('method') || form.method || 'get').toUpperCase();
        if (method !== 'GET') {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        var searchInput = form.querySelector('[name="search"]');
        var statusInput = form.querySelector('[name="status"]');
        filterBlotterRows(searchInput ? searchInput.value : '', statusInput ? statusInput.value : '');
    }, true);

    function isoDate(date) {
        var month = String(date.getMonth() + 1);
        var dayNum = String(date.getDate());
        if (month.length < 2) {
            month = '0' + month;
        }
        if (dayNum.length < 2) {
            dayNum = '0' + dayNum;
        }
        return date.getFullYear() + '-' + month + '-' + dayNum;
    }

    function offlinePages() {
        var pages = [];
        if (role === 'resident') {
            pages.push('/resident/clearance');
        }
        if (role === 'admin' || role === 'secretary') {
            pages.push('/' + role + '/clearance');
            pages.push('/' + role + '/blotter');
            pages.push('/' + role + '/blotter/new');
            document.querySelectorAll('a[href]').forEach(function (link) {
                var path = String(link.getAttribute('href') || '').split('?')[0];
                if (/^\/(?:admin|secretary)\/blotter\/(?:new|\d+)\/?$/.test(path)) {
                    pages.push(path.replace(/\/+$/, ''));
                }
            });
            var here = window.location.pathname.replace(/\/index\.php/, '').replace(/\/+$/, '');
            if (/^\/(?:admin|secretary)\/blotter\/\d+$/.test(here)) {
                pages.push(here);
            }
            if (/\/blotter(?:\/|$)/.test(window.location.pathname)) {
                pages.push('/' + role + '/pii/directory/residents');
                pages.push('/public/blotter/busy-dates');
                var today = new Date();
                for (var day = 0; day < 21; day += 1) {
                    var date = new Date(today.getFullYear(), today.getMonth(), today.getDate() + day);
                    pages.push('/public/blotter/busy-slots?date=' + isoDate(date));
                }
            }
        }
        return pages.filter(function (page, index, all) {
            return page && all.indexOf(page) === index;
        });
    }

    function prefetchOfflinePages() {
        if (!canUseOffline() || !navigator.onLine || !nativeFetch) {
            return;
        }
        var pages = offlinePages();
        var chain = Promise.resolve();
        pages.forEach(function (page) {
            chain = chain.then(function () {
                return nativeFetch(page, {
                    credentials: 'same-origin',
                    cache: 'reload'
                }).catch(function () {});
            });
        });
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'bis-prefetch',
                userId: String(userId),
                role: role,
                pages: pages
            });
        }
    }

    function tellServiceWorker() {
        if (!('serviceWorker' in navigator) || !userId) {
            return;
        }
        var message = { type: 'bis-user', userId: String(userId), role: role, pages: offlinePages() };
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
        prefetchOfflinePages();
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

    function blotterPath() {
        return window.location.pathname.replace(/\/index\.php/, '').replace(/\/+$/, '') || '/';
    }

    function onSecretaryBlotter() {
        return (role === 'secretary' || role === 'admin') && /\/blotter(?:\/|$)/.test(window.location.pathname);
    }

    function blotterForms() {
        return Array.prototype.filter.call(document.forms, function (form) {
            return /\/blotter\//i.test(String(form.getAttribute('action') || ''));
        });
    }

    function draftStorageKey() {
        return 'bis_blotter_draft_' + blotterPath();
    }

    function saveBlotterDraft() {
        if (!onSecretaryBlotter()) {
            return;
        }
        var forms = blotterForms();
        if (!forms.length) {
            return;
        }
        var saved = {};
        forms.forEach(function (form) {
            var action = form.getAttribute('action') || '';
            Array.prototype.forEach.call(form.elements, function (field) {
                if (!field.name || field.type === 'file' || field.type === 'password' || field.type === 'submit' || field.type === 'button') {
                    return;
                }
                var key = action + '|' + field.name;
                if (field.type === 'radio') {
                    if (field.checked) {
                        saved[key] = field.value;
                    }
                    return;
                }
                if (field.type === 'checkbox') {
                    saved[key] = field.checked ? '1' : '0';
                    return;
                }
                saved[key] = field.value;
            });
        });
        try {
            sessionStorage.setItem(draftStorageKey(), JSON.stringify(saved));
        } catch (error) {
            // The draft is optional. Navigation still works without it.
        }
    }

    function restoreBlotterDraft() {
        if (!onSecretaryBlotter()) {
            return;
        }
        var fromCache = Boolean(document.querySelector('meta[name="bis-offline-snapshot"]'));
        if (navigator.onLine && !fromCache) {
            return;
        }
        var raw = '';
        try {
            raw = sessionStorage.getItem(draftStorageKey()) || '';
        } catch (error) {
            raw = '';
        }
        if (!raw) {
            return;
        }
        var saved = {};
        try {
            saved = JSON.parse(raw);
        } catch (error) {
            return;
        }
        blotterForms().forEach(function (form) {
            var action = form.getAttribute('action') || '';
            Array.prototype.forEach.call(form.elements, function (field) {
                if (!field.name || !Object.prototype.hasOwnProperty.call(saved, action + '|' + field.name)) {
                    return;
                }
                var value = saved[action + '|' + field.name];
                if (field.type === 'radio') {
                    field.checked = field.value === value;
                    return;
                }
                if (field.type === 'checkbox') {
                    field.checked = value === '1';
                    return;
                }
                field.value = value;
            });
        });
    }

    function clearBlotterDraft() {
        try {
            sessionStorage.removeItem(draftStorageKey());
        } catch (error) {
            // Ignore storage failures.
        }
    }

    function rememberBlotterTrail() {
        if (!onSecretaryBlotter()) {
            return;
        }
        try {
            sessionStorage.removeItem('bis_offline_nav_guard');
        } catch (error) {
            // Ignore storage failures.
        }
        var path = blotterPath();
        var trail = [];
        try {
            trail = JSON.parse(sessionStorage.getItem('bis_blotter_nav_trail') || '[]');
        } catch (error) {
            trail = [];
        }
        if (!Array.isArray(trail)) {
            trail = [];
        }
        var at = trail.lastIndexOf(path);
        if (at === -1) {
            trail.push(path);
        } else {
            trail = trail.slice(0, at + 1);
        }
        if (trail.length > 40) {
            trail = trail.slice(-40);
        }
        try {
            sessionStorage.setItem('bis_blotter_nav_trail', JSON.stringify(trail));
        } catch (error) {
            // Back can still use the browser history.
        }
    }

    function tellOnlineState() {
        if (!('serviceWorker' in navigator)) {
            return;
        }
        var message = { type: 'bis-network', online: navigator.onLine ? '1' : '0' };
        var send = function (worker) {
            if (worker) {
                worker.postMessage(message);
            }
        };
        send(navigator.serviceWorker.controller);
        navigator.serviceWorker.ready.then(function (registration) {
            send(registration.active);
        }).catch(function () {});
    }

    document.addEventListener('input', function (event) {
        var field = event.target;
        if (!field || !field.form || !onSecretaryBlotter()) {
            return;
        }
        if (!/\/blotter\//i.test(String(field.form.getAttribute('action') || ''))) {
            return;
        }
        saveBlotterDraft();
    }, true);

    document.addEventListener('change', function (event) {
        var field = event.target;
        if (!field || !field.form || !onSecretaryBlotter()) {
            return;
        }
        if (!/\/blotter\//i.test(String(field.form.getAttribute('action') || ''))) {
            return;
        }
        saveBlotterDraft();
    }, true);

    document.addEventListener('click', function (event) {
        var link = event.target && event.target.closest ? event.target.closest('a.hh-back, a[href*="/blotter"]') : null;
        if (!link || !onSecretaryBlotter()) {
            return;
        }
        saveBlotterDraft();
    }, true);

    window.addEventListener('pagehide', function () {
        saveBlotterDraft();
        tellOnlineState();
    });

    function bootOffline() {
        tellOnlineState();
        rememberBlotterTrail();
        restoreBlotterDraft();
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
                } else if (BLOTTER_OPERATIONS.indexOf(entry.operation) !== -1) {
                    paintPendingBlotterUpdate(entry);
                }
            });
            if (!navigator.onLine && canUseOffline()) {
                showStatus(offlineHint(queue.length), 'wait');
                syncOfflineBlotterList();
            } else {
                flushQueue();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootOffline);
    } else {
        bootOffline();
    }

    window.addEventListener('pageshow', function (event) {
        tellOnlineState();
        if (!event.persisted) {
            return;
        }
        rememberBlotterTrail();
        if (!navigator.onLine && canUseOffline()) {
            syncOfflineBlotterList();
            return;
        }
        if (navigator.onLine) {
            flushQueue();
        }
    });
}());
