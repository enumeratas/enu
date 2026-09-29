<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brgy Activities - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .brgy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 16px;
        }

        .brgy-card {
            background: #fff;
            border: 1px solid #e2e6ee;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
        }

        .brgy-banner {
            display: block;
            width: 100%;
            height: 190px;
            object-fit: cover;
            background: #eef1f6;
            border-bottom: 1px solid #e2e6ee;
        }

        .brgy-card-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 16px 18px 12px;
            border-top: 3px solid #e0b32a;
            border-bottom: 1px solid #e2e6ee;
        }

        .brgy-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #16325c;
            color: #fff;
            flex-shrink: 0;
        }

        .brgy-card h2 {
            margin: 0 0 6px;
            font-size: 16px;
            color: #1c2b45;
        }

        .brgy-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: #6b7689;
            font-size: 12px;
        }

        .brgy-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .brgy-body {
            padding: 14px 18px 18px;
            color: #3d4658;
            font-size: 13px;
            line-height: 1.6;
        }

        .brgy-empty {
            background: #fff;
            border: 1px solid #e2e6ee;
            text-align: center;
            padding: 48px 20px;
            color: #6b7689;
        }

        .brgy-reqs { margin-top: 12px; }
        .brgy-reqs p { margin: 0 0 6px; font-size: 11px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #9aa0b4; }
        .brgy-req { display: flex; gap: 6px; align-items: center; font-size: 12.5px; color: #4a5068; padding: 2px 0; }
        .brgy-req i { color: #16325c; font-size: 11px; }
        .brgy-foot { padding: 0 18px 16px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
        .brgy-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; }
        .brgy-chip--pending { background: #fff8ee; color: #a07900; border: 1px solid #f5d88a; }
        .brgy-chip--approved { background: #edfaf5; color: #0f7a62; border: 1px solid #a8e6d5; }
        .brgy-chip--rejected { background: #fff0f0; color: #c0392b; border: 1px solid #f5c0c0; }
    </style>
</head>

<body class="db-body">
    <?php
    $role       = 'resident';
    $active     = 'activities';
    $pageTitle  = 'Brgy Activities';
    $activities = $activities ?? [];
    $formatTime = static function (?string $start, ?string $end): string {
        $fmt = static function (?string $time): string {
            if (! $time) {
                return '';
            }
            $stamp = strtotime($time);
            return $stamp ? date('g:i A', $stamp) : '';
        };
        $startLabel = $fmt($start);
        $endLabel   = $fmt($end);
        if ($startLabel !== '' && $endLabel !== '') {
            return $startLabel . ' – ' . $endLabel;
        }
        return $startLabel !== '' ? $startLabel : ($endLabel !== '' ? $endLabel : 'Time to be announced');
    };
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="dash-welcome" style="margin-bottom:16px;">
                <div>
                    <h2>Barangay Activities</h2>
                    <p>Activities posted by the barangay office. Join one and submit the requirements they ask for.</p>
                </div>
                <div class="dash-welcome-icon"><i class="fas fa-bullhorn"></i></div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--danger" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if ($activities === []): ?>
                <div class="brgy-empty">
                    <i class="fas fa-bullhorn" style="font-size:36px;color:#c5cdd8;display:block;margin-bottom:12px;"></i>
                    <p style="margin:0;font-weight:600;color:#1c2b45;">No activities posted yet</p>
                    <p style="margin:6px 0 0;font-size:13px;">New barangay activities will appear here after they are posted.</p>
                </div>
            <?php else: ?>
                <div class="brgy-grid">
                    <?php foreach ($activities as $activity):
                        $conducted = $activity['conducted_date'] ?? $activity['activity_date'] ?? '';
                        $closed = ! empty($activity['end_date']) && $activity['end_date'] < date('Y-m-d');
                        $status = $closed ? 'Closed' : ($activity['status'] ?? 'Upcoming');
                        $reqs = $activity['requirements_list'] ?? [];
                        $reg = $activity['registration'] ?? null;
                        $minAge = $activity['min_age'] ?? null;
                        $maxAge = $activity['max_age'] ?? null;
                    ?>
                        <article class="brgy-card">
                            <?php $banner = trim((string) ($activity['banner_path'] ?? '')); ?>
                            <?php if ($banner !== ''): ?>
                                <img class="brgy-banner" src="/uploads/<?= esc(ltrim($banner, '/')) ?>" alt="">
                            <?php endif; ?>
                            <div class="brgy-card-head">
                                <div class="brgy-icon"><i class="fas fa-bullhorn"></i></div>
                                <div style="min-width:0;flex:1;">
                                    <h2><?= esc($activity['title']) ?></h2>
                                    <div class="brgy-meta">
                                        <span><i class="fas fa-tag"></i> <?= esc($activity['category'] ?? 'Other') ?></span>
                                        <span><i class="fas fa-calendar"></i> <?= $conducted !== '' ? esc(date('M j, Y', strtotime($conducted))) : 'Date to be announced' ?></span>
                                        <span><i class="fas fa-map-marker-alt"></i> <?= esc($activity['venue'] ?: 'Venue to be announced') ?></span>
                                        <?php if ($minAge !== null && $minAge !== '' || $maxAge !== null && $maxAge !== ''): ?>
                                            <span><i class="fas fa-user"></i>
                                                <?= $minAge !== null && $minAge !== '' ? (int) $minAge : '0' ?>–<?= $maxAge !== null && $maxAge !== '' ? (int) $maxAge : 'any' ?> years
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="dbadge <?= $status === 'Active' ? 'dbadge--approved' : 'dbadge--resolved' ?>"><?= esc($status) ?></span>
                            </div>
                            <div class="brgy-body">
                                <?= nl2br(esc($activity['description'] ?: 'Details will be announced by the barangay.')) ?>
                                <?php if ($reqs !== []): ?>
                                    <div class="brgy-reqs">
                                        <p>Requirements</p>
                                        <?php foreach ($reqs as $req): ?>
                                            <div class="brgy-req"><i class="fas fa-check-circle"></i><?= esc($req) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top:12px;font-size:12px;color:#6b7689;">
                                    <?= (int) ($activity['registration_count'] ?? 0) ?> registered
                                    <?php if ((int) ($activity['target_participants'] ?? 0) > 0): ?>
                                        of <?= (int) $activity['target_participants'] ?> slots
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="brgy-foot">
                                <?php if ($reg): ?>
                                    <span class="brgy-chip brgy-chip--<?= esc($reg['status'], 'attr') ?>">
                                        <?php if ($reg['status'] === 'pending'): ?>
                                            <i class="fas fa-clock"></i> Pending approval
                                        <?php elseif ($reg['status'] === 'approved'): ?>
                                            <i class="fas fa-check-circle"></i> Approved
                                        <?php else: ?>
                                            <i class="fas fa-times-circle"></i> Not approved
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($reg['status'] === 'pending'): ?>
                                        <form action="/resident/activities/unjoin/<?= (int) $activity['id'] ?>" method="post" class="unjoin-form" data-name="<?= esc($activity['title'], 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="db-btn db-btn--outline db-btn--sm">Cancel</button>
                                        </form>
                                    <?php elseif ($reg['status'] === 'rejected' && ! empty($reg['rejection_reason'])): ?>
                                        <span style="font-size:12px;color:#b02a37;"><strong>Reason:</strong> <?= esc($reg['rejection_reason']) ?></span>
                                    <?php endif; ?>
                                <?php elseif ($closed || ($activity['status'] ?? '') === 'Completed'): ?>
                                    <span style="font-size:12px;color:#6b7689;">This activity has ended.</span>
                                <?php else: ?>
                                    <button type="button" class="db-btn db-btn--primary db-btn--sm"
                                        onclick="openJoinModal(<?= htmlspecialchars(json_encode(['id' => (int) $activity['id'], 'name' => $activity['title'], 'reqs' => $reqs]), ENT_QUOTES) ?>)">
                                        <i class="fas fa-hand-paper"></i> Join Activity
                                    </button>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="db-modal-overlay" id="joinModal">
        <div class="db-modal">
            <div class="db-modal-header">
                <h3><i class="fas fa-hand-paper"></i> <span id="joinModalTitle">Join Activity</span></h3>
                <button type="button" class="db-modal-close" onclick="closeJoinModal()">&times;</button>
            </div>
            <form id="joinForm" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="db-modal-body">
                    <div id="joinReqsWrap" style="display:none;margin-bottom:16px;">
                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:#16325c;">Confirm requirements</p>
                        <div id="joinReqsList"></div>
                    </div>
                    <div id="joinUploadsWrap" style="display:none;margin-bottom:16px;">
                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:#16325c;">Required attachments</p>
                        <div id="joinUploadsList"></div>
                    </div>
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#4a5068;">Additional notes <span style="font-weight:400;color:#9aa0b4;">(optional)</span></label>
                    <textarea name="notes" rows="3" style="width:100%;margin-top:6px;padding:10px;border:1.5px solid #e5e7eb;font-family:inherit;" placeholder="Any notes for the barangay office"></textarea>
                    <p style="margin:14px 0 0;font-size:12px;color:#6b7689;">The barangay office will review your registration. You will be notified when it is approved.</p>
                </div>
                <div class="db-modal-footer">
                    <button type="button" class="db-btn db-btn--outline" onclick="closeJoinModal()">Cancel</button>
                    <button type="submit" class="db-btn db-btn--primary">Submit registration</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeJoinModal() {
            document.getElementById('joinModal').classList.remove('active');
        }
        function openJoinModal(activity) {
            document.getElementById('joinModalTitle').textContent = 'Join: ' + activity.name;
            document.getElementById('joinForm').action = '/resident/activities/join/' + activity.id;
            const reqWrap = document.getElementById('joinReqsWrap');
            const reqList = document.getElementById('joinReqsList');
            const uploadWrap = document.getElementById('joinUploadsWrap');
            const uploadList = document.getElementById('joinUploadsList');
            reqList.innerHTML = '';
            uploadList.innerHTML = '';
            reqWrap.style.display = 'none';
            uploadWrap.style.display = 'none';
            (activity.reqs || []).forEach(function (requirement) {
                const uploadMatch = requirement.match(/^(document|photo)\s*:/i);
                if (uploadMatch) {
                    uploadWrap.style.display = '';
                    const label = document.createElement('label');
                    label.style.cssText = 'display:block;padding:8px 0;font-size:12.5px;color:#1c2b45;';
                    label.textContent = requirement.replace(/^(document|photo)\s*:/i, uploadMatch[1].toUpperCase() + ':');
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.name = 'attachments[]';
                    input.required = true;
                    input.accept = uploadMatch[1].toLowerCase() === 'photo' ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,application/pdf';
                    input.style.cssText = 'display:block;margin-top:5px;width:100%;font-size:12px;';
                    label.appendChild(input);
                    uploadList.appendChild(label);
                } else {
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
                }
            });
            document.getElementById('joinModal').classList.add('active');
        }
        document.getElementById('joinModal').addEventListener('click', function (event) {
            if (event.target === this) closeJoinModal();
        });
        document.querySelectorAll('.unjoin-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!confirm('Cancel your registration for "' + (form.dataset.name || 'this activity') + '"?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
