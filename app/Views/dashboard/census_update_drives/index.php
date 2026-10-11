<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Update Drive - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Diff modal ── */
        .pa-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 17, 30, .55);
            backdrop-filter: blur(3px);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .pa-overlay.active {
            display: flex;
        }

        .pa-modal {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        }

        .cud-diff-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .cud-diff-table th {
            background: #f3f5fb;
            padding: 9px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #4a5068;
            border-bottom: 2px solid #e4e9f2;
        }

        .cud-diff-table td {
            padding: 9px 12px;
            border-bottom: 1px solid #f0f2f8;
            vertical-align: top;
        }

        .cud-diff-table tr:last-child td {
            border-bottom: none;
        }

        .cud-diff-old {
            color: #6b7280;
            text-decoration: line-through;
        }

        .cud-diff-new {
            color: #1d6f42;
            font-weight: 600;
        }

        .cud-diff-unchanged {
            color: #9ca3af;
            font-style: italic;
            font-size: 12px;
        }

        .cud-proof-box {
            margin: 14px 0;
            padding: 12px 14px;
            background: #f4f6fd;
            border: 1px solid #ccd5e8;
            border-radius: 10px;
        }

        .cud-proof-box p {
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: 700;
            color: #1d2448;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .cud-proof-box a {
            color: #2563eb;
            font-size: 13px;
        }

        .cud-proof-thumb {
            max-width: 100%;
            max-height: 220px;
            border-radius: 8px;
            display: block;
            margin-top: 8px;
            border: 1px solid #dde4f0;
        }

        .cud-confirm-info {
            padding: 14px 16px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            color: #166534;
            font-size: 13px;
        }

        .cud-card {
            background: #fff;
            border: 1px solid #e2e6ee;
            margin-bottom: 18px;
        }

        .cud-head {
            padding: 14px 18px;
            border-bottom: 1px solid #e2e6ee;
            border-top: 3px solid #e0b32a;
        }

        .cud-head h2 {
            margin: 0 0 4px;
            font-size: 16px;
            color: #1c2b45;
        }

        .cud-head p {
            margin: 0;
            font-size: 12px;
            color: #6b7689;
        }

        .cud-form {
            padding: 18px;
        }

        .cud-form label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        .cud-form input,
        .cud-form textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #d7dce6;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
        }

        .cud-form textarea {
            min-height: 110px;
            resize: vertical;
        }

        .cud-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 14px;
        }

        .cud-span {
            grid-column: 1 / -1;
        }

        @media (max-width: 700px) {
            .cud-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role    = $role ?? (session()->get('role') ?: 'secretary');
    $active  = 'census_updates';
    $pageTitle = 'Census Update Drive';
    $drives  = $drives ?? [];
    $open    = $open ?? null;
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php
            $submittedUpdates = $submittedUpdates ?? [];
            $pendingMemberRequests = $pendingMemberRequests ?? [];
            ?>
            <?php if ($open): ?>
                <div class="db-alert" style="margin-bottom:16px;background:#f4f6fd;border:1px solid #c5d0e8;color:#16325c;">
                    <i class="fas fa-bell"></i>
                    An update window is open until <strong><?= esc(date('F j, Y', strtotime($open['deadline']))) ?></strong>
                    — <?= esc($open['title']) ?>.
                </div>
            <?php endif; ?>

            <div class="cud-card">
                <div class="cud-head">
                    <h2>Submitted census updates</h2>
                    <p>Approve or reject household and personal updates from residents. Changes take effect only after Captain or Secretary approval.</p>
                </div>
                <div class="db-table-wrap">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Resident</th>
                                <th>Household</th>
                                <th>Request</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($submittedUpdates === []): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#6b7689;padding:28px 16px;">No census updates are waiting for review.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($submittedUpdates as $update): ?>
                                    <?php
                                    $notes = json_decode((string) ($update['notes'] ?? ''), true);
                                    $scope = is_array($notes) ? ($notes['scope'] ?? 'head') : 'head';
                                    $confirmOnly = is_array($notes) && ! empty($notes['confirm_only']);
                                    $who = trim(($update['first_name'] ?? '') . ' ' . ($update['last_name'] ?? '')) ?: 'Resident';
                                    $summary = $confirmOnly
                                        ? 'Confirmed existing information'
                                        : ($scope === 'member' ? 'Personal information update' : 'Household information update');
                                    ?>
                                    <tr>
                                        <td><strong><?= esc($who) ?></strong></td>
                                        <td>#<?= esc($update['household_no'] ?? '—') ?></td>
                                        <td><?= esc($summary) ?></td>
                                        <td style="font-size:12px;color:#6b7689;white-space:nowrap;">
                                            <?= ! empty($update['submitted_at']) ? esc(date('M j, Y g:i A', strtotime((string) $update['submitted_at']))) : '—' ?>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <a href="/<?= esc($role) ?>/resident/census-update-review/<?= (int) $update['id'] ?>"
                                                class="db-btn db-btn--outline db-btn--sm" style="margin-right:6px;">
                                                <i class="fas fa-eye"></i> View Changes
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cud-card">
                <div class="cud-head">
                    <h2>Household member requests</h2>
                    <p>Add, move, and remove requests from household heads stay pending until the Captain or Secretary approves them.</p>
                </div>
                <div class="db-table-wrap">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Household</th>
                                <th>Details</th>
                                <th>Requested by</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pendingMemberRequests === []): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#6b7689;padding:28px 16px;">No member add, move, or remove requests are pending.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingMemberRequests as $request): ?>
                                    <?php
                                    $reqName = trim(($request['req_first'] ?? '') . ' ' . ($request['req_last'] ?? '')) ?: 'Household head';
                                    $memName = trim(($request['mem_first'] ?? '') . ' ' . ($request['mem_last'] ?? ''));
                                    $addPayload = json_decode((string) ($request['payload'] ?? ''), true) ?: [];
                                    if ($memName === '' && ($request['request_type'] ?? '') === 'add') {
                                        $memName = trim(($addPayload['first_name'] ?? '') . ' ' . ($addPayload['last_name'] ?? ''));
                                    }
                                    $detail = ucfirst((string) ($request['request_type'] ?? 'change'));
                                    if ($memName !== '') {
                                        $detail .= ' ' . $memName;
                                    }
                                    if (! empty($request['destination_household_no'])) {
                                        $detail .= ' → #' . $request['destination_household_no'];
                                    }
                                    if (! empty($request['reason'])) {
                                        $detail .= ' — ' . $request['reason'];
                                    }
                                    ?>
                                    <tr>
                                        <td><span class="dbadge dbadge--pending"><?= esc(ucfirst((string) ($request['request_type'] ?? 'change'))) ?></span></td>
                                        <td>#<?= esc($request['household_no'] ?? '—') ?></td>
                                        <td><?= esc($detail) ?></td>
                                        <td><?= esc($reqName) ?></td>
                                        <td style="white-space:nowrap;">
                                            <form method="post" action="/<?= esc($role) ?>/member-request/approve/<?= (int) $request['id'] ?>" style="display:inline;" onsubmit="return confirm('Apply this member request now?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="db-btn db-btn--primary db-btn--sm"><i class="fas fa-check"></i> Approve</button>
                                            </form>
                                            <form method="post" action="/<?= esc($role) ?>/member-request/reject/<?= (int) $request['id'] ?>" style="display:inline-flex;gap:6px;align-items:center;margin-left:6px;">
                                                <?= csrf_field() ?>
                                                <input type="text" name="remarks" placeholder="Reason (optional)" style="width:160px;padding:6px 8px;border:1px solid #d7dce6;font-size:12px;">
                                                <button type="submit" class="db-btn db-btn--outline db-btn--sm"><i class="fas fa-times"></i> Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cud-card">
                <div class="cud-head">
                    <h2>Ask residents to update their records</h2>
                    <p>Set a deadline and notify every active resident. They can update personal details and add family members, including newborns.</p>
                </div>
                <form class="cud-form" method="post" action="/<?= esc($role) ?>/census-updates/store">
                    <?= csrf_field() ?>
                    <div class="cud-grid">
                        <div>
                            <label for="driveTitle">Title</label>
                            <input id="driveTitle" name="title" type="text" maxlength="200" required
                                placeholder="Example: 2026 household update — add newborns">
                        </div>
                        <div>
                            <label for="driveDeadline">Deadline</label>
                            <input id="driveDeadline" name="deadline" type="date" required min="<?= esc(date('Y-m-d')) ?>">
                        </div>
                        <div class="cud-span">
                            <label for="driveMessage">Message to residents</label>
                            <textarea id="driveMessage" name="message" maxlength="2000"
                                placeholder="Please review your household information and add any new family members, such as a newborn, before the deadline."></textarea>
                        </div>
                    </div>
                    <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                        <button type="submit" class="db-btn db-btn--primary">
                            <i class="fas fa-paper-plane"></i> Set date and notify residents
                        </button>
                    </div>
                </form>
            </div>

            <form method="get" data-live-results="liveResults" style="margin-bottom:16px;">
                <div class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search notice or date..." value="<?= esc($search ?? '') ?>">
                </div>
            </form>
            <div id="liveResults">
                <div class="db-table-wrap">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Notice</th>
                                <th>Deadline</th>
                                <th>Residents notified</th>
                                <th>Sent by</th>
                                <th>Filed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($drives === []): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#6b7689;padding:28px 16px;">
                                        <?= ($search ?? '') !== '' ? 'No notices match your search.' : 'No census update drives have been sent yet.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($drives as $drive):
                                    $poster = trim(($drive['first_name'] ?? '') . ' ' . ($drive['last_name'] ?? ''));
                                    $isOpen = ($drive['deadline'] ?? '') >= date('Y-m-d');
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($drive['title']) ?></strong>
                                            <?php if (! empty($drive['message'])): ?>
                                                <div style="margin-top:4px;color:#6b7689;font-size:12px;"><?= esc($drive['message']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= esc(date('M j, Y', strtotime($drive['deadline']))) ?>
                                            <div style="margin-top:4px;">
                                                <span class="dbadge <?= $isOpen ? 'dbadge--pending' : 'dbadge--resolved' ?>">
                                                    <?= $isOpen ? 'Open' : 'Closed' ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td><?= (int) $drive['notified_count'] ?></td>
                                        <td><?= esc($poster !== '' ? $poster : 'Barangay office') ?></td>
                                        <td style="font-size:12px;color:#6b7689;white-space:nowrap;">
                                            <?= ! empty($drive['created_at']) ? esc(date('M j, Y g:i A', strtotime($drive['created_at']))) : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Census Update Diff Modal ──────────────────────────────────── -->
    <div class="pa-overlay" id="cudDiffOverlay" style="z-index:1050;">
        <div class="pa-modal" style="max-width:700px;width:96%;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;border-radius:14px;">
            <div style="padding:18px 22px;background:linear-gradient(135deg,#1d2448,#2e3a6e);color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-shrink:0;">
                <div>
                    <div style="font-size:15px;font-weight:700;"><i class="fas fa-file-alt"></i> Census Update — Review Changes</div>
                    <div id="cudDiffMeta" style="font-size:12px;opacity:.8;margin-top:4px;"></div>
                </div>
                <button onclick="closeCudDiff()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1;padding:0;" title="Close">&times;</button>
            </div>
            <div style="padding:22px;overflow-y:auto;flex:1;">
                <div id="cudDiffSpinner" style="display:none;text-align:center;padding:28px;color:#6b7280;font-size:13px;">
                    <i class="fas fa-circle-notch fa-spin"></i> Loading…
                </div>
                <div id="cudDiffContent"></div>
            </div>
            <div id="cudDiffFoot" style="display:none;padding:16px 22px;border-top:1px solid #edf1f7;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="text" id="cudRejectReason" placeholder="Rejection reason (optional)"
                    style="flex:1;min-width:160px;padding:8px 10px;border:1px solid #d7dce6;border-radius:8px;font-family:'Poppins',sans-serif;font-size:12px;">
                <button class="db-btn db-btn--outline db-btn--sm" onclick="submitCudAction('reject')">
                    <i class="fas fa-times"></i> Reject
                </button>
                <button class="db-btn db-btn--primary db-btn--sm" onclick="submitCudAction('approve')">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var currentAuthId = null;
            var rolePrefix = <?= json_encode($role ?? 'secretary') ?>;
            var csrfName = <?= json_encode(csrf_token()) ?>;
            var csrfHash = <?= json_encode(csrf_hash()) ?>;

            function escHtml(str) {
                return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            window.openCudDiff = function(authId, residentName, submittedAt) {
                currentAuthId = authId;
                document.getElementById('cudDiffMeta').textContent =
                    residentName + (submittedAt ? ' — submitted ' + submittedAt : '');
                document.getElementById('cudDiffContent').innerHTML = '';
                document.getElementById('cudDiffFoot').style.display = 'none';
                document.getElementById('cudDiffSpinner').style.display = 'block';
                document.getElementById('cudDiffOverlay').classList.add('active');
                document.getElementById('cudRejectReason').value = '';

                fetch('/' + rolePrefix + '/resident/census-update-diff/' + authId, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        document.getElementById('cudDiffSpinner').style.display = 'none';
                        if (!data.ok) {
                            document.getElementById('cudDiffContent').innerHTML =
                                '<div style="color:#b91c1c;padding:12px;">' + escHtml(data.message || 'Could not load changes.') + '</div>';
                            return;
                        }
                        renderCudDiff(data);
                        document.getElementById('cudDiffFoot').style.display = 'flex';
                    })
                    .catch(function() {
                        document.getElementById('cudDiffSpinner').style.display = 'none';
                        document.getElementById('cudDiffContent').innerHTML =
                            '<div style="color:#b91c1c;padding:12px;">Network error — could not load changes.</div>';
                    });
            };

            function renderCudDiff(data) {
                var html = '';

                if (data.confirm_only) {
                    html += '<div class="cud-confirm-info"><i class="fas fa-check-circle"></i> ' +
                        'Resident confirmed that their information is correct — no changes were proposed.</div>';
                } else if (!data.diff || data.diff.length === 0) {
                    html += '<div class="cud-confirm-info" style="background:#fff8ee;border-color:#f6d7a8;color:#7a4200;">' +
                        '<i class="fas fa-info-circle"></i> No field-level changes were detected in the submitted data.</div>';
                } else {
                    html += '<table class="cud-diff-table">' +
                        '<thead><tr><th>Field</th><th>Current Value</th><th>Proposed Change</th></tr></thead><tbody>';
                    data.diff.forEach(function(row) {
                        var oldVal = (row.old !== null && row.old !== '') ?
                            '<span class="cud-diff-old">' + escHtml(row.old) + '</span>' :
                            '<span class="cud-diff-unchanged">(empty)</span>';
                        html += '<tr>' +
                            '<td style="font-weight:600;color:#1d2448;white-space:nowrap;">' + escHtml(row.label) + '</td>' +
                            '<td>' + oldVal + '</td>' +
                            '<td><span class="cud-diff-new">' + escHtml(row.new) + '</span></td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                }

                // Proof / supporting document
                if (data.proof_url) {
                    html += '<div class="cud-proof-box">' +
                        '<p><i class="fas fa-paperclip"></i> Uploaded Proof / Supporting Document</p>';
                    if (data.proof_is_image) {
                        html += '<img src="' + escHtml(data.proof_url) + '" class="cud-proof-thumb" alt="Proof document">' +
                            '<div style="margin-top:10px;"><a href="' + escHtml(data.proof_url) + '" target="_blank" rel="noopener">' +
                            'Open supporting document</a></div>';
                    } else {
                        html += '<a href="' + escHtml(data.proof_url) + '" target="_blank" rel="noopener">' +
                            '<i class="fas fa-file-pdf"></i> Open supporting document</a>';
                    }
                    html += '</div>';
                }

                document.getElementById('cudDiffContent').innerHTML = html;
            }

            window.submitCudAction = function(action) {
                if (!currentAuthId) return;
                var reason = document.getElementById('cudRejectReason').value.trim();
                var url = '/' + rolePrefix + '/resident/' +
                    (action === 'approve' ? 'approve' : 'reject') +
                    '-census-update/' + currentAuthId;

                if (action === 'approve' && !confirm('Apply this census update to the database now?')) return;

                var formData = new FormData();
                formData.append(csrfName, csrfHash);
                if (action === 'reject') {
                    formData.append('remarks', reason);
                }

                fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function() {
                        window.location.reload();
                    })
                    .catch(function() {
                        window.location.reload();
                    });
            };

            window.closeCudDiff = function() {
                document.getElementById('cudDiffOverlay').classList.remove('active');
                currentAuthId = null;
            };

            // Close on backdrop click
            document.getElementById('cudDiffOverlay').addEventListener('click', function(e) {
                if (e.target === this) closeCudDiff();
            });
        })();
    </script>
</body>

</html>