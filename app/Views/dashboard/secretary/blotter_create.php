<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blotter Report - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Autocomplete dropdown ── */
        .bl-ac-wrap {
            position: relative;
        }

        .bl-ac-list {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #d0d7e8;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .bl-ac-list.open {
            display: block;
        }

        .bl-ac-item {
            padding: 9px 14px;
            cursor: pointer;
            font-size: 13.5px;
            color: #2d3a58;
            border-bottom: 1px solid #f0f3fa;
            line-height: 1.4;
        }

        .bl-ac-item:last-child {
            border-bottom: none;
        }

        .bl-ac-item:hover,
        .bl-ac-item.active {
            background: #eef1fb;
        }

        .bl-ac-item em {
            font-style: normal;
            color: #1a56db;
            font-weight: 600;
        }

        .bl-ac-badge {
            display: inline-block;
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 6px;
            vertical-align: middle;
            background: #e3f0ff;
            color: #1a56db;
        }

        /* Read-only auto-filled inputs */
        .bl-input[readonly] {
            background: #f0f2f8;
            color: #555;
            cursor: default;
        }

        /* Inline validation error */
        .bl-field-error {
            color: #c0392b;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .bl-field-error.show {
            display: block;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role   = 'secretary';
    $active = 'blotter';
    $pageTitle = 'Create Blotter Report';
    include(APPPATH . 'Views/dashboard/sidebar.php');
    $residents    = $residents    ?? [];
    $residentData = $residentData ?? [];
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="hh-breadcrumb" style="margin-bottom:20px;">
                <a href="/secretary/blotter" class="hh-back"><i class="fas fa-arrow-left"></i> Back to Blotter Reports</a>
                <span class="hh-bc-sep">/</span><span>Create Report</span>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="bl-card" style="width:100%;">
                <h3 class="bl-card-title"><i class="fas fa-file-signature"></i> Secretary-Created Blotter Report</h3>

                <form id="blotterForm" action="/secretary/blotter/store" method="post"
                    enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>

                    <!-- ══════════════════════════════════════════════════════
                         COMPLAINANT
                    ═══════════════════════════════════════════════════════ -->
                    <fieldset style="border:none;padding:0;margin:0 0 20px;">
                        <legend style="font-weight:600;color:#2d3a58;font-size:14px;margin-bottom:10px;">
                            <i class="fas fa-user" style="color:#1a56db;margin-right:6px;"></i>Complainant
                        </legend>

                        <!-- hidden resident id -->
                        <input type="hidden" name="complainant_user_id" id="complainantUserId"
                            value="<?= esc(old('complainant_user_id')) ?>">

                        <!-- Name (doubles as search) -->
                        <div class="bl-form-group">
                            <label>Complainant Name <span style="color:#c0392b;">*</span>
                                <span style="font-weight:400;color:#7a8aaa;font-size:12px;">
                                    — type to search registered residents, or enter any name
                                </span>
                            </label>
                            <div class="bl-ac-wrap" style="display:flex;gap:8px;align-items:flex-start;">
                                <div style="flex:1;position:relative;">
                                    <input type="text" id="complainantName" name="complainant_name"
                                        class="bl-input"
                                        placeholder="Start typing a name..."
                                        autocomplete="off" required
                                        value="<?= esc(old('complainant_name')) ?>">
                                    <div class="bl-ac-list" id="complainantAcList"></div>
                                </div>
                                <button type="button" id="complainantClearBtn"
                                    style="display:none;white-space:nowrap;padding:8px 12px;font-size:12px;border-radius:7px;border:1px solid #d0d7e8;background:#fff;color:#c0392b;cursor:pointer;font-family:inherit;"
                                    title="Clear selection and search again">
                                    <i class="fas fa-times"></i> Change
                                </button>
                            </div>
                            <div class="bl-field-error" id="complainantSameError">
                                The complainant and respondent cannot be the same person.
                            </div>
                        </div>

                        <!-- Email + Address -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="bl-form-group">
                                <label>Email</label>
                                <input type="email" name="complainant_email" id="complainantEmail"
                                    class="bl-input"
                                    placeholder="Email address"
                                    value="<?= esc(old('complainant_email')) ?>">
                                <div class="bl-field-error" id="complainantEmailError">
                                    The complainant and respondent cannot share the same email.
                                </div>
                            </div>
                            <div class="bl-form-group">
                                <label>Address</label>
                                <input type="text" name="complainant_address" id="complainantAddress"
                                    class="bl-input"
                                    placeholder="Address"
                                    value="<?= esc(old('complainant_address')) ?>">
                            </div>
                        </div>
                    </fieldset>

                    <div class="bl-form-group">
                        <label>Complainant Narrative <span style="color:#c0392b;">*</span></label>
                        <textarea name="complainant_narrative" class="bl-textarea" rows="5" required><?= esc(old('complainant_narrative')) ?></textarea>
                    </div>

                    <hr style="border:none;border-top:1px solid #e8ecf4;margin:8px 0 20px;">

                    <!-- ══════════════════════════════════════════════════════
                         RESPONDENT
                    ═══════════════════════════════════════════════════════ -->
                    <fieldset style="border:none;padding:0;margin:0 0 20px;">
                        <legend style="font-weight:600;color:#2d3a58;font-size:14px;margin-bottom:10px;">
                            <i class="fas fa-user-slash" style="color:#c0392b;margin-right:6px;"></i>Respondent
                        </legend>

                        <input type="hidden" name="respondent_user_id" id="respondentUserId"
                            value="<?= esc(old('respondent_user_id')) ?>">

                        <div class="bl-form-group">
                            <label>Respondent Name <span style="color:#c0392b;">*</span>
                                <span style="font-weight:400;color:#7a8aaa;font-size:12px;">
                                    — type to search registered residents, or enter any name
                                </span>
                            </label>
                            <div class="bl-ac-wrap" style="display:flex;gap:8px;align-items:flex-start;">
                                <div style="flex:1;position:relative;">
                                    <input type="text" id="respondentName" name="respondent_name"
                                        class="bl-input"
                                        placeholder="Start typing a name..."
                                        autocomplete="off" required
                                        value="<?= esc(old('respondent_name')) ?>">
                                    <div class="bl-ac-list" id="respondentAcList"></div>
                                </div>
                                <button type="button" id="respondentClearBtn"
                                    style="display:none;white-space:nowrap;padding:8px 12px;font-size:12px;border-radius:7px;border:1px solid #d0d7e8;background:#fff;color:#c0392b;cursor:pointer;font-family:inherit;"
                                    title="Clear selection and search again">
                                    <i class="fas fa-times"></i> Change
                                </button>
                            </div>
                            <div class="bl-field-error" id="respondentSameError">
                                The respondent and complainant cannot be the same person.
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="bl-form-group">
                                <label>Email</label>
                                <input type="email" name="respondent_email" id="respondentEmail"
                                    class="bl-input"
                                    placeholder="Email address"
                                    value="<?= esc(old('respondent_email')) ?>">
                                <div class="bl-field-error" id="respondentEmailError">
                                    The complainant and respondent cannot share the same email.
                                </div>
                            </div>
                            <div class="bl-form-group">
                                <label>Address</label>
                                <input type="text" name="respondent_address" id="respondentAddress"
                                    class="bl-input"
                                    placeholder="Address"
                                    value="<?= esc(old('respondent_address')) ?>">
                            </div>
                        </div>
                    </fieldset>

                    <div class="bl-form-group">
                        <label>Respondent Narrative
                            <span style="font-weight:400;color:#7a8aaa;font-size:12px;">— optional, can be filled in later</span>
                        </label>
                        <textarea name="respondent_narrative" class="bl-textarea" rows="5"
                            placeholder="Record the respondent's account of the incident (optional)..."><?= esc(old('respondent_narrative')) ?></textarea>
                    </div>

                    <hr style="border:none;border-top:1px solid #e8ecf4;margin:8px 0 20px;">

                    <!-- ══════════════════════════════════════════════════════
                         INCIDENT DETAILS
                    ═══════════════════════════════════════════════════════ -->
                    <div class="bl-form-group">
                        <label>Incident Type <span style="color:#c0392b;">*</span></label>
                        <input name="incident_type" class="bl-input"
                            value="<?= esc(old('incident_type')) ?>" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="bl-form-group">
                            <label>Incident Date</label>
                            <input type="date" name="incident_date" class="bl-input"
                                max="<?= date('Y-m-d') ?>"
                                value="<?= esc(old('incident_date')) ?>">
                        </div>
                        <div class="bl-form-group">
                            <label>Incident Time</label>
                            <input type="time" name="incident_time" class="bl-input"
                                value="<?= esc(old('incident_time')) ?>">
                        </div>
                    </div>
                    <div class="bl-form-group">
                        <label>Location</label>
                        <input name="location" class="bl-input" value="<?= esc(old('location')) ?>">
                    </div>
                    <div class="bl-form-group">
                        <label>Other Persons Involved</label>
                        <textarea name="persons_involved" class="bl-textarea" rows="3"><?= esc(old('persons_involved')) ?></textarea>
                    </div>

                    <hr style="border:none;border-top:1px solid #e8ecf4;margin:8px 0 20px;">

                    <!-- ══════════════════════════════════════════════════════
                         EVIDENCE PHOTOS (optional)
                    ═══════════════════════════════════════════════════════ -->
                    <div class="bl-form-group">
                        <label style="display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-camera" style="color:#1a56db;"></i>
                            Evidence Photos
                            <span style="font-weight:400;color:#7a8aaa;font-size:12px;">— optional, up to 5 images (JPG/PNG/WEBP, max 5 MB each)</span>
                        </label>

                        <!-- Drop zone -->
                        <div id="evidenceDropZone"
                            style="border:2px dashed #c8d0e8;border-radius:10px;padding:28px 20px;text-align:center;cursor:pointer;background:#f8f9fc;transition:border-color .2s,background .2s;"
                            onclick="document.getElementById('evidenceInput').click()"
                            ondragover="event.preventDefault();this.style.borderColor='#1a56db';this.style.background='#eef1fb';"
                            ondragleave="this.style.borderColor='#c8d0e8';this.style.background='#f8f9fc';"
                            ondrop="handleDrop(event)">
                            <i class="fas fa-cloud-upload-alt" style="font-size:28px;color:#b0bacc;display:block;margin-bottom:8px;"></i>
                            <p style="margin:0;font-size:13px;color:#7a8aaa;">
                                Click or drag &amp; drop images here
                            </p>
                        </div>

                        <!-- Hidden real input -->
                        <input type="file" id="evidenceInput" name="evidence_photos[]"
                            multiple accept="image/jpeg,image/png,image/webp,image/gif"
                            style="display:none;"
                            onchange="previewPhotos(this.files)">

                        <!-- Preview grid -->
                        <div id="evidencePreview"
                            style="display:none;margin-top:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;">
                        </div>
                        <p id="evidenceCount" style="font-size:12px;color:#7a8aaa;margin-top:6px;display:none;"></p>
                    </div>

                    <button type="submit" class="bl-btn bl-btn--primary">
                        <i class="fas fa-save"></i> Create Blotter Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            /* ── Resident data loaded after render (not present in page HTML) ─ */
            let RESIDENTS = [];
            const RESIDENT_DATA = {};
            fetch('/secretary/pii/directory/residents', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(rows => {
                RESIDENTS = Array.isArray(rows) ? rows : [];
            }).catch(() => { RESIDENTS = []; });

            /* ── Party state (complainant / respondent) ─────────────────────── */
            const party = {
                complainant: {
                    residentId: null
                },
                respondent: {
                    residentId: null
                },
            };

            /* ── DOM refs ───────────────────────────────────────────────────── */
            function $(id) {
                return document.getElementById(id);
            }

            const cfg = {
                complainant: {
                    nameInput: $('complainantName'),
                    emailInput: $('complainantEmail'),
                    addrInput: $('complainantAddress'),
                    userIdInput: $('complainantUserId'),
                    acList: $('complainantAcList'),
                    clearBtn: $('complainantClearBtn'),
                    sameErr: $('complainantSameError'),
                    emailErr: $('complainantEmailError'),
                },
                respondent: {
                    nameInput: $('respondentName'),
                    emailInput: $('respondentEmail'),
                    addrInput: $('respondentAddress'),
                    userIdInput: $('respondentUserId'),
                    acList: $('respondentAcList'),
                    clearBtn: $('respondentClearBtn'),
                    sameErr: $('respondentSameError'),
                    emailErr: $('respondentEmailError'),
                },
            };

            /* ── Autocomplete ───────────────────────────────────────────────── */
            function highlight(text, query) {
                if (!query) return esc(text);
                const idx = text.toLowerCase().indexOf(query.toLowerCase());
                if (idx === -1) return esc(text);
                return esc(text.slice(0, idx)) +
                    '<em>' + esc(text.slice(idx, idx + query.length)) + '</em>' +
                    esc(text.slice(idx + query.length));
            }

            function esc(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function openList(type, query) {
                const c = cfg[type];
                const q = query.trim().toLowerCase();
                const list = c.acList;

                if (!q) {
                    closeList(type);
                    return;
                }

                const matches = RESIDENTS.filter(r =>
                    r.name.toLowerCase().includes(q) ||
                    r.display.toLowerCase().includes(q) ||
                    r.email.toLowerCase().includes(q)
                ).slice(0, 10);

                if (!matches.length) {
                    closeList(type);
                    return;
                }

                list.innerHTML = matches.map((r, i) =>
                    `<div class="bl-ac-item" data-id="${r.id}" data-idx="${i}">
                    ${highlight(r.display, query.trim())}
                    <span class="bl-ac-badge"><i class="fas fa-user"></i> Resident</span>
                    <br><small style="color:#7a8aaa;">${esc(r.email)}</small>
                </div>`
                ).join('');

                list.querySelectorAll('.bl-ac-item').forEach(item => {
                    item.addEventListener('mousedown', e => {
                        e.preventDefault();
                        selectResident(type, parseInt(item.dataset.id, 10));
                    });
                });

                list.classList.add('open');
            }

            function closeList(type) {
                cfg[type].acList.classList.remove('open');
            }

            /* ── Select a resident ──────────────────────────────────────────── */
            async function selectResident(type, resId) {
                const c = cfg[type];
                if (!RESIDENT_DATA[resId]) {
                    try {
                        const res = await fetch('/secretary/pii/resident/' + resId, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!res.ok) return;
                        RESIDENT_DATA[resId] = await res.json();
                    } catch (e) {
                        return;
                    }
                }
                const data = RESIDENT_DATA[resId];
                if (!data) return;

                party[type].residentId = resId;

                c.userIdInput.value = resId;
                c.nameInput.value = data.name;
                c.nameInput.readOnly = true;

                c.emailInput.value = data.email;
                c.emailInput.readOnly = true;

                c.addrInput.value = data.address;
                c.addrInput.readOnly = false;

                // Show the Change button
                c.clearBtn.style.display = '';

                closeList(type);
                runValidation();
            }

            /* ── Clear resident link when user edits the name manually ──────── */
            function clearResident(type) {
                const c = cfg[type];
                party[type].residentId = null;
                c.userIdInput.value = 'external';

                // Unlock all fields and reset
                c.nameInput.value = '';
                c.nameInput.readOnly = false;
                c.nameInput.style.background = '';
                c.nameInput.style.color = '';

                c.emailInput.value = '';
                c.emailInput.readOnly = false;
                c.emailInput.style.background = '';
                c.emailInput.style.color = '';

                c.addrInput.value = '';
                c.addrInput.readOnly = false;

                // Hide the Change button
                c.clearBtn.style.display = 'none';

                // Focus the name input so secretary can type immediately
                c.nameInput.focus();

                runValidation();
            }

            /* ── Validation ─────────────────────────────────────────────────── */
            function runValidation() {
                const cId = party.complainant.residentId;
                const rId = party.respondent.residentId;
                const cEmail = cfg.complainant.emailInput.value.trim().toLowerCase();
                const rEmail = cfg.respondent.emailInput.value.trim().toLowerCase();

                // Same person check
                const samePerson = cId !== null && rId !== null && cId === rId;
                cfg.complainant.sameErr.classList.toggle('show', samePerson);
                cfg.respondent.sameErr.classList.toggle('show', samePerson);

                // Same email check
                const sameEmail = cEmail !== '' && rEmail !== '' && cEmail === rEmail;
                cfg.complainant.emailErr.classList.toggle('show', sameEmail);
                cfg.respondent.emailErr.classList.toggle('show', sameEmail);

                return !samePerson && !sameEmail;
            }

            /* ── Wire up inputs ─────────────────────────────────────────────── */
            ['complainant', 'respondent'].forEach(type => {
                const c = cfg[type];

                // Clear/Change button — resets the selection so secretary can search again
                c.clearBtn.addEventListener('click', function() {
                    clearResident(type);
                });

                c.nameInput.addEventListener('input', function() {
                    // If the name field is read-only, block direct editing —
                    // they must use the Change button instead.
                    if (this.readOnly) return;
                    // If user had a resident selected and somehow bypassed readOnly, detach
                    if (party[type].residentId !== null) {
                        clearResident(type);
                    }
                    openList(type, this.value);
                });

                c.nameInput.addEventListener('blur', function() {
                    // Small delay so mousedown on list item fires first
                    setTimeout(() => closeList(type), 150);
                });

                c.nameInput.addEventListener('keydown', function(e) {
                    const items = c.acList.querySelectorAll('.bl-ac-item');
                    const active = c.acList.querySelector('.bl-ac-item.active');
                    let idx = active ? parseInt(active.dataset.idx, 10) : -1;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        idx = Math.min(idx + 1, items.length - 1);
                        items.forEach(i => i.classList.remove('active'));
                        if (items[idx]) items[idx].classList.add('active');
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        idx = Math.max(idx - 1, 0);
                        items.forEach(i => i.classList.remove('active'));
                        if (items[idx]) items[idx].classList.add('active');
                    } else if (e.key === 'Enter' && active) {
                        e.preventDefault();
                        selectResident(type, parseInt(active.dataset.id, 10));
                    } else if (e.key === 'Escape') {
                        closeList(type);
                    }
                });

                c.emailInput.addEventListener('blur', runValidation);
            });

            /* ── Form submit guard ──────────────────────────────────────────── */
            $('blotterForm').addEventListener('submit', function(e) {
                if (!runValidation()) {
                    e.preventDefault();
                    // Scroll to first visible error
                    const first = document.querySelector('.bl-field-error.show');
                    if (first) first.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });

            /* ── Restore state on old() repopulation ────────────────────────── */
            ['complainant', 'respondent'].forEach(type => {
                const c = cfg[type];
                const id = parseInt(c.userIdInput.value, 10);
                if (id > 0 && RESIDENT_DATA[id]) {
                    party[type].residentId = id;
                    c.nameInput.readOnly = true;
                    c.emailInput.readOnly = true;
                    c.clearBtn.style.display = ''; // show Change button
                }
            });

            runValidation();

        })();
        /* ── Evidence photo preview ─────────────────────────────────────── */

        const MAX_PHOTOS = 5;
        const MAX_BYTES = 5 * 1024 * 1024;
        let photoFiles = []; // DataTransfer accumulator

        function previewPhotos(fileList) {
            const grid = document.getElementById('evidencePreview');
            const count = document.getElementById('evidenceCount');

            Array.from(fileList).forEach(file => {
                if (photoFiles.length >= MAX_PHOTOS) return;
                if (!file.type.startsWith('image/')) return;
                if (file.size > MAX_BYTES) {
                    alert(file.name + ' exceeds the 5 MB limit and was skipped.');
                    return;
                }
                photoFiles.push(file);
            });

            // Rebuild hidden input's FileList via DataTransfer
            const dt = new DataTransfer();
            photoFiles.forEach(f => dt.items.add(f));
            document.getElementById('evidenceInput').files = dt.files;

            // Render previews
            grid.innerHTML = '';
            photoFiles.forEach((file, idx) => {
                const url = URL.createObjectURL(file);
                const wrap = document.createElement('div');
                wrap.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;aspect-ratio:1;background:#eee;';
                wrap.innerHTML = `
                    <img src="${url}" style="width:100%;height:100%;object-fit:cover;" alt="">
                    <button type="button"
                        onclick="removePhoto(${idx})"
                        style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,.55);color:#fff;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;font-size:12px;line-height:22px;text-align:center;padding:0;"
                        title="Remove">&#x2715;</button>`;
                grid.appendChild(wrap);
            });

            grid.style.display = photoFiles.length ? 'grid' : 'none';
            count.style.display = photoFiles.length ? 'block' : 'none';
            count.textContent = photoFiles.length + ' photo' + (photoFiles.length !== 1 ? 's' : '') + ' selected (max ' + MAX_PHOTOS + ')';
        }

        function removePhoto(idx) {
            photoFiles.splice(idx, 1);
            previewPhotos([]); // re-render with current list (no new files)
        }

        function handleDrop(e) {
            e.preventDefault();
            const zone = document.getElementById('evidenceDropZone');
            zone.style.borderColor = '#c8d0e8';
            zone.style.background = '#f8f9fc';
            previewPhotos(e.dataTransfer.files);
        }
    </script>
</body>

</html>