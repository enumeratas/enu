(function (window) {
    function fileRequirement(requirement) {
        const match = String(requirement || '').match(/^(document|photo|image)\s*:/i);
        if (!match) {
            return null;
        }
        const kind = match[1].toLowerCase() === 'document' ? 'document' : 'image';
        const label = String(requirement).replace(/^(document|photo|image)\s*:/i, '').trim();

        return {
            kind: kind,
            label: label || String(requirement),
            display: (kind === 'image' ? 'Image' : 'Document') + ': ' + (label || String(requirement)),
            accept: kind === 'image' ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,application/pdf'
        };
    }

    function requirementRow(type, label) {
        const row = document.createElement('div');
        row.className = 'sk-req-row';
        row.innerHTML =
            '<select class="sk-form-input" name="req_type[]" aria-label="File type">' +
                '<option value="document"' + (type === 'image' ? '' : ' selected') + '>Document</option>' +
                '<option value="image"' + (type === 'image' ? ' selected' : '') + '>Image</option>' +
            '</select>' +
            '<input class="sk-form-input" name="req_label[]" type="text" maxlength="120" list="reqLabelSuggestions" placeholder="e.g. Birth Certificate, Valid ID" value="">' +
            '<button type="button" class="db-btn db-btn--outline db-btn--sm" data-req-remove aria-label="Remove requirement">&times;</button>';
        const input = row.querySelector('input[name="req_label[]"]');
        if (input) {
            input.value = label || '';
        }
        return row;
    }

    function bindRequirements(rootId) {
        const root = document.getElementById(rootId);
        if (!root) {
            return;
        }
        const list = root.querySelector('[data-req-list]');
        const addBtn = root.querySelector('[data-req-add]');
        if (!list) {
            return;
        }
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                list.appendChild(requirementRow('document', ''));
            });
        }
        list.addEventListener('click', function (event) {
            const button = event.target.closest('[data-req-remove]');
            if (!button) {
                return;
            }
            const rows = list.querySelectorAll('.sk-req-row');
            if (rows.length <= 1) {
                const first = rows[0];
                const type = first && first.querySelector('[name="req_type[]"]');
                const label = first && first.querySelector('[name="req_label[]"]');
                if (type) {
                    type.value = 'document';
                }
                if (label) {
                    label.value = '';
                }
                return;
            }
            button.closest('.sk-req-row').remove();
        });
    }

    function bindCleanup(config) {
        const category = document.getElementById(config.categoryId);
        const title = document.getElementById(config.titleId) || document.querySelector(config.titleSelector || '');
        const requirements = document.getElementById(config.requirementsId);
        const dates = document.getElementById(config.datesId);
        const note = document.getElementById(config.noteId);
        const start = document.getElementById(config.startId);
        const end = document.getElementById(config.endId);

        function isCleanup() {
            const name = title && title.value || '';
            return (category && category.value === 'Clean-up Drive') || /clean[\s-]*up\s+drive/i.test(name);
        }

        function syncCleanup() {
            const cleanup = isCleanup();
            if (requirements) {
                requirements.hidden = cleanup;
                requirements.querySelectorAll('input, select, button, textarea').forEach(function (input) {
                    input.disabled = cleanup;
                });
            }
            if (dates) {
                dates.classList.toggle('is-disabled', cleanup);
                dates.querySelectorAll('input').forEach(function (input) {
                    input.disabled = cleanup;
                });
            }
            if (start) {
                start.required = !cleanup;
                start.disabled = cleanup;
            }
            if (end) {
                end.disabled = cleanup;
            }
            if (note) {
                note.style.display = cleanup ? '' : 'none';
            }
        }

        category && category.addEventListener('change', syncCleanup);
        title && title.addEventListener('input', syncCleanup);
        syncCleanup();
    }

    function bindEligibility() {
        const otherBox = document.querySelector('[data-eligibility-other-toggle]');
        const wrap = document.getElementById('eligibilityOtherWrap');
        const input = document.getElementById('eligibilityOtherInput');

        function sync() {
            const on = !!(otherBox && otherBox.checked);
            if (wrap) {
                wrap.hidden = !on;
            }
            if (input) {
                input.disabled = !on;
                input.required = on;
            }
        }

        otherBox && otherBox.addEventListener('change', sync);
        sync();
    }

    function renderJoinRequirements(reqs, reqList, uploadList, reqWrap, uploadWrap) {
        reqList.innerHTML = '';
        uploadList.innerHTML = '';
        reqWrap.style.display = 'none';
        uploadWrap.style.display = 'none';
        (reqs || []).forEach(function (requirement) {
            const file = fileRequirement(requirement);
            if (file) {
                uploadWrap.style.display = '';
                const label = document.createElement('label');
                label.style.cssText = 'display:block;padding:8px 0;font-size:12.5px;color:#1c2b45;';
                label.textContent = file.display;
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'attachments[]';
                input.required = true;
                input.accept = file.accept;
                input.style.cssText = 'display:block;margin-top:5px;width:100%;font-size:12px;';
                label.appendChild(input);
                uploadList.appendChild(label);
                return;
            }
            reqWrap.style.display = '';
            const label = document.createElement('label');
            label.style.cssText = 'display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;cursor:pointer;';
            const box = document.createElement('input');
            box.type = 'checkbox';
            box.name = 'requirements[]';
            box.value = requirement;
            label.appendChild(box);
            label.appendChild(document.createTextNode(' ' + requirement));
            reqList.appendChild(label);
        });
    }

    window.ActivityForm = {
        fileRequirement: fileRequirement,
        bindRequirements: bindRequirements,
        bindCleanup: bindCleanup,
        bindEligibility: bindEligibility,
        renderJoinRequirements: renderJoinRequirements
    };
})(window);
