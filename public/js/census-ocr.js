/**
 * Census document OCR helpers.
 * Compares an uploaded ID / birth certificate against the name and birthdate
 * on the form, and blocks a complete save when they do not match.
 */
(function (window, document) {
    'use strict';

    const ocrLastSig = new WeakMap();
    const ocrInFlight = new WeakSet();

    function personScope(block) {
        return block.closest('[data-ocr-person]') || block.closest('form') || document;
    }

    function fieldValue(scope, attr, fallbackName) {
        const tagged = scope.querySelector('[' + attr + ']');
        if (tagged) {
            return (tagged.value || '').trim();
        }
        if (fallbackName) {
            const fallback = scope.querySelector('input[name="' + fallbackName + '"]');
            if (fallback) {
                return (fallback.value || '').trim();
            }
        }
        return '';
    }

    function ocrFields(block) {
        const scope = personScope(block);
        const first = fieldValue(scope, 'data-ocr-first', 'first_name');
        const middle = fieldValue(scope, 'data-ocr-middle', 'middle_name');
        const last = fieldValue(scope, 'data-ocr-last', 'last_name');
        const dob = fieldValue(scope, 'data-ocr-dob', 'date_of_birth');
        return {
            first: first,
            middle: middle,
            last: last,
            dob: dob,
            name: [first, middle, last].filter(Boolean).join(' ')
        };
    }

    function fileSignature(input) {
        const file = input && input.files && input.files[0];
        return file ? (file.name + '|' + file.size + '|' + (file.lastModified || 0)) : '';
    }

    function computeOcrSignature(block) {
        const fields = ocrFields(block);
        const front = block.querySelector('[data-ocr-front]');
        const back = block.querySelector('[data-ocr-back]');
        return [fields.first, fields.middle, fields.last, fields.dob, fileSignature(front), fileSignature(back)].join('::');
    }

    function hasOcrInputsChanged(block) {
        const prev = ocrLastSig.get(block);
        if (!prev) {
            return true;
        }
        return prev !== computeOcrSignature(block);
    }

    function setOcrStatus(block, state, message) {
        const statusEl = block.querySelector('[data-ocr-status]');
        if (!statusEl) {
            return;
        }
        statusEl.classList.remove('is-pending', 'is-ok', 'is-fail');
        if (state) {
            statusEl.classList.add(state);
        }
        const icon = state === 'is-ok'
            ? '<i class="fas fa-check-circle"></i>'
            : state === 'is-fail'
                ? '<i class="fas fa-exclamation-circle"></i>'
                : state === 'is-pending'
                    ? '<i class="fas fa-spinner fa-spin"></i>'
                    : '<i class="fas fa-fingerprint"></i>';
        statusEl.innerHTML = icon + ' <span></span>';
        const span = statusEl.querySelector('span');
        if (span) {
            span.textContent = message;
        }
    }

    function resetOcrStatus(block) {
        const flag = block.querySelector('[data-ocr-flag]');
        if (flag) {
            flag.value = '0';
        }
        ocrLastSig.delete(block);
        const single = block.dataset.ocrMode === 'single';
        setOcrStatus(
            block,
            null,
            single
                ? 'Fill in the name and birthdate, then upload the ID or birth certificate. OCR runs automatically.'
                : 'Fill in the name and birthdate, then upload the ID. Numbers or a month name both match.'
        );
    }

    function isOcrBlockActive(block) {
        let node = block;
        while (node && node !== document.body) {
            if (node.classList && node.classList.contains('pf-step')) {
                node = node.parentElement;
                continue;
            }
            if (node.style && node.style.display === 'none') {
                return false;
            }
            node = node.parentElement;
        }
        return true;
    }

    function hasChosenFile(input) {
        return !!(input && input.files && input.files[0]);
    }

    function fileLabelKept(input) {
        const nameEl = input && input.closest('.pf-file-drop') && input.closest('.pf-file-drop').querySelector('.pf-file-name');
        return !!(nameEl && /\(kept\)/i.test(nameEl.textContent || ''));
    }

    function blockLabel(block) {
        const label = block.querySelector('.pf-id-label');
        return ((label && label.textContent) || 'uploaded document').replace(/\s+/g, ' ').trim();
    }

    function unverifiedLabels(root) {
        const issues = [];
        (root || document).querySelectorAll('[data-id-block]').forEach(function (block) {
            if (!isOcrBlockActive(block)) {
                return;
            }
            const front = block.querySelector('[data-ocr-front]');
            if (!front) {
                return;
            }
            const hasNew = hasChosenFile(front) || fileLabelKept(front);
            if (!hasNew) {
                return;
            }
            const flag = block.querySelector('[data-ocr-flag]');
            if (flag && flag.value === '1') {
                return;
            }
            issues.push(blockLabel(block).replace(/\* REQUIRED/i, '').trim());
        });
        return issues;
    }

    async function runOcr(block) {
        if (ocrInFlight.has(block)) {
            return;
        }

        const front = block.querySelector('[data-ocr-front]');
        const back = block.querySelector('[data-ocr-back]');
        const runBtn = block.querySelector('[data-ocr-run]');
        const flag = block.querySelector('[data-ocr-flag]');
        const fields = ocrFields(block);
        const single = block.dataset.ocrMode === 'single';

        if (!hasChosenFile(front)) {
            setOcrStatus(block, 'is-fail', single ? 'Pick the ID or birth certificate first.' : 'Pick the front photo of the ID first.');
            return;
        }
        if (!fields.name) {
            setOcrStatus(block, 'is-fail', 'Type the full name first.');
            return;
        }
        if (!fields.dob) {
            setOcrStatus(block, null, 'Type the date of birth, then verify again.');
            return;
        }

        ocrInFlight.add(block);

        const form = new FormData();
        form.append('id_front', front.files[0]);
        if (back && back.files && back.files[0]) {
            form.append('id_back', back.files[0]);
        }
        form.append('full_name', fields.name);
        form.append('first_name', fields.first);
        form.append('middle_name', fields.middle);
        form.append('last_name', fields.last);
        form.append('date_of_birth', fields.dob);
        form.append('document_type', single ? 'birth_or_id' : 'id_card');

        setOcrStatus(
            block,
            'is-pending',
            single
                ? 'Reading the ID or birth certificate with OCR… (trying multiple orientations)'
                : 'Reading the ID with OCR… (trying multiple orientations)'
        );
        if (runBtn) {
            runBtn.disabled = true;
        }

        const rolePrefix = (location.pathname.match(/^\/(admin|secretary|captain|council)(?=\/)/) || [null, 'secretary'])[1];
        const ocrUrl = '/' + rolePrefix + '/ocr/verify-id';

        try {
            const response = await fetch(ocrUrl, {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const raw = await response.text();
            let data = null;
            try {
                data = raw ? JSON.parse(raw) : null;
            } catch (_) {
                data = null;
            }
            if (!response.ok || !data || data.ok === false) {
                let message;
                if (data && data.error) {
                    message = data.error;
                } else if (raw) {
                    const titleMatch = raw.match(/<title[^>]*>([^<]{1,200})<\/title>/i);
                    const h1Match = raw.match(/<h1[^>]*>([\s\S]{1,240}?)<\/h1>/i);
                    const bodyMatch = raw.match(/<p[^>]*>([\s\S]{5,240}?)<\/p>/i);
                    const summary = (titleMatch && titleMatch[1].trim())
                        || (h1Match && h1Match[1].replace(/<[^>]+>/g, ' ').trim())
                        || (bodyMatch && bodyMatch[1].replace(/<[^>]+>/g, ' ').trim())
                        || raw.replace(/<script[\s\S]*?<\/script>/gi, ' ').replace(/<style[\s\S]*?<\/style>/gi, ' ').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 240);
                    message = 'OCR service returned HTTP ' + response.status + '. ' + summary;
                } else {
                    message = 'OCR service returned HTTP ' + response.status + ' with an empty body.';
                }
                if (flag) {
                    flag.value = '0';
                }
                setOcrStatus(block, 'is-fail', message);
                return;
            }
            if (flag) {
                flag.value = data.verified ? '1' : '0';
            }
            const reason = data.reason || (data.verified
                ? 'Verified. The document matches the name and birthdate.'
                : 'The uploaded document does not match the name and birthdate on the form.');
            setOcrStatus(block, data.verified ? 'is-ok' : 'is-fail', reason);
            if (data.verified) {
                ocrLastSig.set(block, computeOcrSignature(block));
            } else {
                ocrLastSig.delete(block);
            }
        } catch (err) {
            if (flag) {
                flag.value = '0';
            }
            setOcrStatus(block, 'is-fail', 'Network error while verifying the ID: ' + (err && err.message ? err.message : err));
            ocrLastSig.delete(block);
        } finally {
            if (runBtn) {
                runBtn.disabled = false;
            }
            ocrInFlight.delete(block);
        }
    }

    function maybeRunOcr(block) {
        const front = block.querySelector('[data-ocr-front]');
        const back = block.querySelector('[data-ocr-back]');
        const hasFront = hasChosenFile(front);
        const hasBack = hasChosenFile(back);
        const fields = ocrFields(block);
        const single = block.dataset.ocrMode === 'single' || !back;
        if (hasFront && fields.name && fields.dob && (single || hasBack)) {
            runOcr(block);
        }
    }

    function refreshNameBlocks(target) {
        const person = target.closest('[data-ocr-person]');
        const blocks = person
            ? person.querySelectorAll('[data-id-block]')
            : Array.from(document.querySelectorAll('[data-id-block]')).filter(function (block) {
                return !block.closest('[data-ocr-person]');
            });
        blocks.forEach(function (block) {
            if (!hasOcrInputsChanged(block)) {
                return;
            }
            resetOcrStatus(block);
            maybeRunOcr(block);
        });
    }

    function updateFileName(input) {
        const name = input.files && input.files[0] ? input.files[0].name : 'No file selected';
        const nameEl = input.closest('.pf-file-drop') && input.closest('.pf-file-drop').querySelector('.pf-file-name');
        if (nameEl) {
            nameEl.textContent = name;
            nameEl.style.color = '';
            nameEl.style.fontWeight = '';
        }
    }

    function syncFlagsFromDom() {
        document.querySelectorAll('[data-id-block]').forEach(function (block) {
            const flag = block.querySelector('[data-ocr-flag]');
            if (flag && flag.value === '1') {
                setOcrStatus(block, 'is-ok', 'Verified. The document matches the name and birthdate.');
                ocrLastSig.set(block, computeOcrSignature(block));
            }
        });
    }

    document.addEventListener('change', function (e) {
        if (e.target.matches('.pf-file')) {
            updateFileName(e.target);
            const block = e.target.closest('[data-id-block]');
            if (block && hasOcrInputsChanged(block)) {
                resetOcrStatus(block);
                maybeRunOcr(block);
            }
        }

        if (e.target.matches('[data-ocr-first], [data-ocr-last], [data-ocr-middle], [data-ocr-dob], input[name="first_name"], input[name="last_name"], input[name="middle_name"], input[name="date_of_birth"]')) {
            refreshNameBlocks(e.target);
        }
    });

    document.addEventListener('click', function (e) {
        const runBtn = e.target.closest('[data-ocr-run]');
        if (!runBtn) {
            return;
        }
        e.preventDefault();
        const block = runBtn.closest('[data-id-block]');
        if (block) {
            runOcr(block);
        }
    });

    window.CensusOcr = {
        unverifiedLabels: unverifiedLabels,
        syncFlagsFromDom: syncFlagsFromDom,
        runOcr: runOcr,
        resetOcrStatus: resetOcrStatus
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncFlagsFromDom);
    } else {
        syncFlagsFromDom();
    }
})(window, document);
