<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File a Household Move - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .mv-card {
            background: #fff;
            border: 1px solid #e2e6ee;
            padding: 18px 20px;
            margin-bottom: 18px;
        }

        .mv-card h2 {
            margin: 0 0 4px;
            font-size: 15px;
            color: #1c2b45;
            border-top: 3px solid #e0b32a;
            padding-top: 10px;
        }

        .mv-card .mv-hint {
            font-size: 12px;
            color: #6b7689;
            margin: 0 0 12px;
        }

        .mv-field {
            display: block;
            margin-bottom: 12px;
        }

        .mv-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .mv-field input[type="text"],
        .mv-field select,
        .mv-field textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #d7dce6;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #1a1d2e;
            background: #fff;
        }

        .mv-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .mv-radio-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .mv-radio {
            flex: 1 1 220px;
            padding: 12px 14px;
            border: 1px solid #d7dce6;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .mv-radio.is-active {
            border-color: #16325c;
            background: #f4f6fd;
        }

        .mv-people {
            border: 1px solid #e2e6ee;
        }

        .mv-people-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-bottom: 1px solid #eef1f6;
        }

        .mv-people-row:last-child {
            border-bottom: none;
        }

        .mv-people-row .role-tag {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            background: #eef1f6;
            color: #4b556a;
            margin-right: 6px;
        }

        .mv-suggest {
            position: relative;
        }

        .mv-suggest-list {
            position: absolute;
            top: calc(100% + 2px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #d7dce6;
            max-height: 260px;
            overflow-y: auto;
            z-index: 20;
            display: none;
        }

        .mv-suggest-item {
            padding: 8px 12px;
            font-size: 12.5px;
            cursor: pointer;
            border-bottom: 1px solid #eef1f6;
        }

        .mv-suggest-item:hover {
            background: #f4f6fd;
        }

        @media (max-width: 700px) {
            .mv-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role   = $role ?? (session()->get('role') ?: 'secretary');
    $active = 'moves';
    $pageTitle = 'File a Household Move';
    $sourceHousehold = $sourceHousehold ?? null;
    $sourceMembers   = $sourceMembers ?? [];
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="mv-card">
                <h2>1. Pick the source household</h2>
                <p class="mv-hint">Type a household number or the head's name. The list will show matches.</p>
                <form method="get" action="/<?= esc($role) ?>/moves/new">
                    <div class="mv-suggest">
                        <div class="mv-field" style="margin-bottom:0;">
                            <label>Household number or head's name</label>
                            <input type="text" name="source" id="sourceInput" value="<?= esc($sourceHousehold['household_no'] ?? '') ?>" autocomplete="off" placeholder="e.g. 78874 or DELA CRUZ">
                        </div>
                        <div class="mv-suggest-list" id="sourceSuggest"></div>
                    </div>
                    <button class="db-btn db-btn--outline db-btn--sm" type="submit" style="margin-top:10px;"><i class="fas fa-search"></i> Load household</button>
                </form>
            </div>

            <?php if (! $sourceHousehold): ?>
                <div class="mv-card">
                    <p style="margin:0;color:#6b7689;font-size:13px;">Load a household above to pick who is moving.</p>
                </div>
            <?php else: ?>
                <form method="post" action="/<?= esc($role) ?>/moves/store">
                    <?= csrf_field() ?>
                    <input type="hidden" name="source_household_no" value="<?= esc($sourceHousehold['household_no']) ?>">

                    <div class="mv-card">
                        <h2>2. Who is moving?</h2>
                        <p class="mv-hint">Source household #<?= esc($sourceHousehold['household_no']) ?> at <?= esc($sourceHousehold['address'] ?? '—') ?></p>
                        <div class="mv-people">
                            <label class="mv-people-row">
                                <input type="checkbox" name="include_head" value="1" id="includeHead" data-name="<?= esc(trim(($sourceHousehold['first_name'] ?? '') . ' ' . ($sourceHousehold['last_name'] ?? '')), 'attr') ?>">
                                <span>
                                    <span class="role-tag">Head</span>
                                    <strong><?= esc(trim(($sourceHousehold['first_name'] ?? '') . ' ' . ($sourceHousehold['middle_name'] ?? '') . ' ' . ($sourceHousehold['last_name'] ?? ''))) ?></strong>
                                    <?= ! empty($sourceHousehold['date_of_birth']) ? '<span style="color:#6b7689;font-size:12px;"> - born ' . esc(date('M j, Y', strtotime($sourceHousehold['date_of_birth']))) . '</span>' : '' ?>
                                </span>
                            </label>
                            <?php if ($sourceMembers === []): ?>
                                <div class="mv-people-row"><span style="color:#6b7689;font-size:12px;">No other members on record.</span></div>
                            <?php else: ?>
                                <?php foreach ($sourceMembers as $m): ?>
                                    <label class="mv-people-row">
                                        <input type="checkbox" name="member_ids[]" value="<?= (int) $m['id'] ?>" class="member-check" data-id="<?= (int) $m['id'] ?>" data-name="<?= esc(trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? ''))) ?>">
                                        <span>
                                            <span class="role-tag"><?= esc(ucfirst($m['relationship'] ?? 'member')) ?></span>
                                            <strong><?= esc(trim(($m['first_name'] ?? '') . ' ' . ($m['middle_name'] ?? '') . ' ' . ($m['last_name'] ?? ''))) ?></strong>
                                            <?= ! empty($m['date_of_birth']) ? '<span style="color:#6b7689;font-size:12px;"> - born ' . esc(date('M j, Y', strtotime($m['date_of_birth']))) . '</span>' : '' ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mv-card" id="replacementCard" style="display:none;">
                        <h2>3. Pick the replacement head</h2>
                        <p class="mv-hint">The current head is moving. Choose a member who stays behind to become the new head of household #<?= esc($sourceHousehold['household_no']) ?>. If everyone moves, this can stay empty and the source household will be removed.</p>
                        <div class="mv-field" style="margin-bottom:0;">
                            <label>Replacement head</label>
                            <select name="replacement_head_member_id" id="replacementSelect">
                                <option value="">— Nobody stays (remove source household) —</option>
                                <?php foreach ($sourceMembers as $m): ?>
                                    <option value="<?= (int) $m['id'] ?>" data-id="<?= (int) $m['id'] ?>">
                                        <?= esc(trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? ''))) ?> (<?= esc(ucfirst($m['relationship'] ?? 'member')) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mv-card">
                        <h2>4. Reason</h2>
                        <div class="mv-grid">
                            <div class="mv-field">
                                <label>Reason for the move</label>
                                <select name="move_reason">
                                    <option value="Change of Residence">Change of Residence</option>
                                    <option value="Marriage">Marriage</option>
                                    <option value="Family Separation">Family Separation</option>
                                    <option value="Own House Now">Own House Now</option>
                                    <option value="Merged with another household">Merged with another household</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mv-field">
                                <label>Notes (optional)</label>
                                <input type="text" name="notes" maxlength="255" placeholder="Anything else the captain should know">
                            </div>
                        </div>
                    </div>

                    <div class="mv-card">
                        <h2>5. Destination</h2>
                        <div class="mv-radio-row" id="destinationRadios">
                            <label class="mv-radio is-active" data-dest="new">
                                <input type="radio" name="destination_type" value="new" checked>
                                <span>
                                    <strong>Create a new household</strong>
                                    <div style="font-size:12px;color:#6b7689;margin-top:2px;">A new household number will be generated.</div>
                                </span>
                            </label>
                            <label class="mv-radio" data-dest="existing">
                                <input type="radio" name="destination_type" value="existing">
                                <span>
                                    <strong>Move into an existing household</strong>
                                    <div style="font-size:12px;color:#6b7689;margin-top:2px;">Pick a household that is already on record.</div>
                                </span>
                            </label>
                        </div>

                        <div id="newDestFields" style="margin-top:14px;">
                            <div class="mv-grid">
                                <div class="mv-field">
                                    <label>New zone</label>
                                    <select name="new_zone">
                                        <option value="">— Select —</option>
                                        <?php for ($i = 1; $i <= 7; $i++): ?>
                                            <option value="Zone <?= $i ?>">Zone <?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mv-field">
                                    <label>New address</label>
                                    <input type="text" name="new_address" maxlength="255" placeholder="Purok / Street / Sitio">
                                </div>
                            </div>
                        </div>

                        <div id="existingDestFields" style="margin-top:14px;display:none;">
                            <div class="mv-suggest">
                                <div class="mv-field" style="margin-bottom:0;">
                                    <label>Destination household number</label>
                                    <input type="text" name="destination_household_no" id="destInput" autocomplete="off" placeholder="Type a household number or a head name">
                                </div>
                                <div class="mv-suggest-list" id="destSuggest"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mv-card" id="designatedHeadCard">
                        <h2>6. Who will serve as the household head?</h2>
                        <p class="mv-hint" id="designatedHeadHint">Choose the person who will be recorded as the household head after this move.</p>
                        <div class="mv-people" id="designatedHeadOptions"></div>
                    </div>

                    <?php if (! in_array($role, ['captain', 'admin'], true)): ?>
                        <div class="db-alert" style="margin-bottom:16px;background:#fff8e6;color:#8a5b00;border:1px solid #f1d89d;">
                            <i class="fas fa-hourglass-half"></i>
                            This move will be saved as <strong>Pending</strong>. The barangay captain must approve it before household numbers change.
                        </div>
                    <?php endif; ?>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <a href="/<?= esc($role) ?>/moves" class="db-btn db-btn--outline">Cancel</a>
                        <button type="submit" class="db-btn db-btn--primary">
                            <i class="fas fa-check"></i>
                            <?= in_array($role, ['captain', 'admin'], true) ? 'Save move and apply' : 'File as pending' ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        (function () {
            const includeHead = document.getElementById('includeHead');
            const replacementCard = document.getElementById('replacementCard');
            const replacementSelect = document.getElementById('replacementSelect');

            function refreshReplacement() {
                if (!includeHead) return;
                if (includeHead.checked) {
                    replacementCard.style.display = 'block';
                } else {
                    replacementCard.style.display = 'none';
                    if (replacementSelect) replacementSelect.value = '';
                }
                // Hide options for members that are moving.
                if (replacementSelect) {
                    const moving = new Set(Array.from(document.querySelectorAll('.member-check')).filter(el => el.checked).map(el => String(el.dataset.id)));
                    Array.from(replacementSelect.options).forEach(opt => {
                        if (!opt.value) return;
                        opt.hidden = moving.has(String(opt.value));
                        if (opt.hidden && replacementSelect.value === opt.value) replacementSelect.value = '';
                    });
                }
            }

            const designatedOptions = document.getElementById('designatedHeadOptions');
            const designatedHint = document.getElementById('designatedHeadHint');

            function destinationKind() {
                const checked = document.querySelector('#destinationRadios input[name="destination_type"]:checked');
                return checked ? checked.value : 'new';
            }

            function refreshDesignatedHead() {
                if (!designatedOptions) return;
                const previous = (designatedOptions.querySelector('input[name="designated_head"]:checked') || {}).value || '';
                const people = [];
                if (includeHead && includeHead.checked) {
                    people.push({ value: 'source_head', tag: 'Moving head', name: includeHead.dataset.name || 'Current household head' });
                }
                document.querySelectorAll('.member-check').forEach(el => {
                    if (!el.checked) return;
                    people.push({ value: 'member-' + el.dataset.id, tag: 'Moving member', name: el.dataset.name || 'Member' });
                });
                const existing = destinationKind() === 'existing';
                designatedOptions.innerHTML = '';
                if (existing) {
                    const keep = document.createElement('label');
                    keep.className = 'mv-people-row';
                    keep.innerHTML = '<input type="radio" name="designated_head" value="keep"> <span><span class="role-tag">Current head</span> <strong>Keep the head of the destination household</strong></span>';
                    designatedOptions.appendChild(keep);
                }
                people.forEach(person => {
                    const row = document.createElement('label');
                    row.className = 'mv-people-row';
                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'designated_head';
                    input.value = person.value;
                    const text = document.createElement('span');
                    const tag = document.createElement('span');
                    tag.className = 'role-tag';
                    tag.textContent = person.tag;
                    const name = document.createElement('strong');
                    name.textContent = person.name;
                    text.appendChild(tag);
                    text.appendChild(document.createTextNode(' '));
                    text.appendChild(name);
                    row.appendChild(input);
                    row.appendChild(text);
                    designatedOptions.appendChild(row);
                });
                const radios = designatedOptions.querySelectorAll('input[name="designated_head"]');
                let chosen = false;
                radios.forEach(radio => {
                    if (radio.value === previous) {
                        radio.checked = true;
                        chosen = true;
                    }
                });
                if (!chosen && radios.length) {
                    const preferred = existing
                        ? 'keep'
                        : (people.some(person => person.value === 'source_head') ? 'source_head' : people[0] && people[0].value);
                    radios.forEach(radio => {
                        if (radio.value === preferred) radio.checked = true;
                    });
                    if (!designatedOptions.querySelector('input[name="designated_head"]:checked')) {
                        radios[0].checked = true;
                    }
                }
                if (designatedHint) {
                    designatedHint.textContent = existing
                        ? 'Keep the destination household\'s current head, or choose one of the people who are moving to take that place.'
                        : 'Choose which moving person will be recorded as the head of the new household.';
                }
            }

            if (includeHead) includeHead.addEventListener('change', () => { refreshReplacement(); refreshDesignatedHead(); });
            document.querySelectorAll('.member-check').forEach(el => el.addEventListener('change', () => { refreshReplacement(); refreshDesignatedHead(); }));
            refreshDesignatedHead();
            const moveForm = designatedOptions ? designatedOptions.closest('form') : null;
            if (moveForm) {
                moveForm.addEventListener('submit', function (event) {
                    refreshDesignatedHead();
                    if (!moveForm.querySelector('input[name="designated_head"]:checked')) {
                        event.preventDefault();
                        alert('Choose who will serve as the household head.');
                    }
                });
            }

            // Destination radio behaviour.
            const radios = document.querySelectorAll('#destinationRadios .mv-radio');
            const newFields = document.getElementById('newDestFields');
            const existingFields = document.getElementById('existingDestFields');
            radios.forEach(label => label.addEventListener('click', () => {
                const value = label.dataset.dest;
                const input = label.querySelector('input[name="destination_type"]');
                if (input) input.checked = true;
                radios.forEach(l => l.classList.toggle('is-active', l === label));
                if (value === 'existing') {
                    existingFields.style.display = 'block';
                    newFields.style.display = 'none';
                } else {
                    existingFields.style.display = 'none';
                    newFields.style.display = 'block';
                }
                refreshDesignatedHead();
            }));

            // Autocomplete helpers.
            function attachAutocomplete(inputId, listId, onPick) {
                const input = document.getElementById(inputId);
                const list = document.getElementById(listId);
                if (!input || !list) return;
                let timer = null;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    const q = input.value.trim();
                    if (!q) { list.style.display = 'none'; list.innerHTML = ''; return; }
                    timer = setTimeout(async () => {
                        try {
                            const rolePrefix = (location.pathname.match(/^\/(admin|secretary|captain|council)(?=\/)/) || [null, 'secretary'])[1];
                            const response = await fetch('/' + rolePrefix + '/moves/search?q=' + encodeURIComponent(q), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            if (!response.ok) return;
                            const rows = await response.json();
                            list.innerHTML = '';
                            if (!rows || rows.length === 0) {
                                list.style.display = 'none';
                                return;
                            }
                            rows.forEach(row => {
                                const item = document.createElement('div');
                                item.className = 'mv-suggest-item';
                                item.innerHTML = '<strong>#' + row.household_no + '</strong> ' + (row.head || '') + '<div style="color:#6b7689;font-size:11.5px;">' + (row.zone || '') + ' — ' + (row.address || '') + '</div>';
                                item.addEventListener('click', () => {
                                    onPick(row);
                                    list.style.display = 'none';
                                });
                                list.appendChild(item);
                            });
                            list.style.display = 'block';
                        } catch (e) { /* silent */ }
                    }, 250);
                });
                document.addEventListener('click', ev => {
                    if (!list.contains(ev.target) && ev.target !== input) list.style.display = 'none';
                });
            }

            attachAutocomplete('sourceInput', 'sourceSuggest', row => {
                document.getElementById('sourceInput').value = row.household_no;
            });
            attachAutocomplete('destInput', 'destSuggest', row => {
                document.getElementById('destInput').value = row.household_no;
            });
        })();
    </script>
</body>

</html>
