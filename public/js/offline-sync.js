    const form = event.target.closest('form[data-offline-sync="clearance_request"], form[data-offline-sync="blotter_submission"]');
(function () {
    'use strict';

    const DB_NAME = 'bis-offline-sync';
    const STORE_NAME = 'outbox';
    function storageKey(prefix) {
        const userId = Number((window.BIS_SESSION && window.BIS_SESSION.userId) || 0);
        return userId ? prefix + '_' + userId : prefix + '_guest';
    }
    const DRAFT_KEY = storageKey('bis-offline-drafts');
    const SNAPSHOT_KEY = storageKey('bis-offline-page-cache');
    const SYNC_URL = '/api/offline-sync';
    let syncing = false;

    function readDrafts() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (error) {
            return {};
        }
    }

    function writeDrafts(drafts) {
        try {
            localStorage.setItem(DRAFT_KEY, JSON.stringify(drafts));
        } catch (error) {
            // noop for storage limitations
        }
    }

    function saveDraft(form) {
        if (! form || ! form.id) return;
        const drafts = readDrafts();
        const payload = Object.fromEntries(new FormData(form).entries());
        const csrfField = form.querySelector('input[name^="csrf"]');
        if (csrfField) delete payload[csrfField.name];
        drafts[form.id] = payload;
        writeDrafts(drafts);
    }

    function restoreDraft(form) {
        if (! form || ! form.id) return;
        const drafts = readDrafts();
        const draft = drafts[form.id];
        if (! draft) return;
        for (const [name, value] of Object.entries(draft)) {
            const field = form.elements.namedItem(name);
            if (! field) continue;
            if (field instanceof RadioNodeList) {
                field.value = value;
                continue;
            }
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = String(field.value) === String(value);
            } else if (field.type === 'file') {
                // Ignore file values for security and browser constraints.
            } else {
                field.value = value;
            }
        }
    }

    function readPageSnapshots() {
        try {
            const raw = localStorage.getItem(SNAPSHOT_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (error) {
            return {};
        }
    }

    function writePageSnapshots(snapshots) {
        try {
            localStorage.setItem(SNAPSHOT_KEY, JSON.stringify(snapshots));
        } catch (error) {
            // noop
        }
    }

    function snapshotCurrentPage() {
        const path = location.pathname;
        const table = document.querySelector('#requestsTable tbody') || document.querySelector('table.db-table tbody');
        if (! table) return;
        const snapshots = readPageSnapshots();
        snapshots[path] = {
            bodyHtml: table.innerHTML,
            savedAt: Date.now()
        };
        writePageSnapshots(snapshots);
    }

    function restorePageSnapshot() {
        if (navigator.onLine) return;
        const path = location.pathname;
        const snapshots = readPageSnapshots();
        const entry = snapshots[path];
        if (! entry) return;

        const table = document.querySelector('#requestsTable tbody') || document.querySelector('table.db-table tbody');
        if (! table) return;

        table.innerHTML = entry.bodyHtml || '';
        const statusText = document.querySelector('[data-offline-sync-status]');
        if (statusText) {
            statusText.textContent = 'Offline mode: showing the last synced data saved on this device.';
        }
    }

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 1);
            request.onupgradeneeded = () => request.result.createObjectStore(STORE_NAME, { keyPath: 'operation_id' });
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function put(item) {
        const db = await openDatabase();
        return new Promise((resolve, reject) => {
            const request = db.transaction(STORE_NAME, 'readwrite').objectStore(STORE_NAME).put(item);
            request.onsuccess = resolve;
            request.onerror = () => reject(request.error);
        });
    }

    async function all() {
        const db = await openDatabase();
        return new Promise((resolve, reject) => {
            const request = db.transaction(STORE_NAME, 'readonly').objectStore(STORE_NAME).getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function remove(operationId) {
        const db = await openDatabase();
        return new Promise((resolve, reject) => {
            const request = db.transaction(STORE_NAME, 'readwrite').objectStore(STORE_NAME).delete(operationId);
            request.onsuccess = resolve;
            request.onerror = () => reject(request.error);
        });
    }

    function setStatus(text) {
        const status = document.querySelector('[data-offline-sync-status]');
        if (status) status.textContent = text;
    }

    function operationId() {
        return window.crypto && crypto.randomUUID
            ? crypto.randomUUID()
            : Date.now().toString(16) + '-' + Math.random().toString(16).slice(2);
    }

    async function sync() {
        if (syncing || ! navigator.onLine) return;
        syncing = true;
        const items = await all();
        if (items.length) setStatus('Syncing ' + items.length + ' pending item(s)...');
        for (const item of items) {
            try {
                const response = await fetch(SYNC_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(item),
                });
                const result = await response.json();
                if (response.ok && result.success) await remove(item.operation_id);
                else if (response.status >= 400 && response.status < 500) await remove(item.operation_id);
                else break;
            } catch (error) {
                break;
            }
        }
        syncing = false;
        const remaining = await all();
        setStatus(remaining.length ? remaining.length + ' item(s) pending sync' : 'All data synced');
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-offline-sync="clearance_request"], form[data-offline-sync="blotter_submission"]');
        if (! form) return;
        if (navigator.onLine) return;
        event.preventDefault();
        if (! form.reportValidity()) return;
        if (form.dataset.offlineVerified !== '1') {
            setStatus('Verify your email before saving this form offline');
            return;
        }
        const data = Object.fromEntries(new FormData(form).entries());
        const csrfField = form.querySelector('input[name^="csrf"]');
        if (csrfField) delete data[csrfField.name];
        await put({ operation_id: operationId(), operation: form.dataset.offlineSync, data, queued_at: new Date().toISOString() });
        saveDraft(form);
        form.reset();
        setStatus('Saved offline; will sync automatically when online');
    });

    window.addEventListener('online', sync);
    window.addEventListener('offline', () => setStatus('Offline; new submissions will be saved locally'));
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-offline-sync]').forEach((form) => {
            restoreDraft(form);
            if (!navigator.onLine) {
                setStatus('Offline mode active: saved drafts and queued requests will sync when internet returns.');
            }
        });

        if (navigator.onLine) {
            snapshotCurrentPage();
        } else {
            restorePageSnapshot();
        }

        sync();
    });
}());