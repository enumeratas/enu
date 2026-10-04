<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Registrations - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php
    $role          = $role ?? (session()->get('role') ?: 'secretary');
    $active        = 'activities';
    $pageTitle     = 'Registrations';
    $activity      = $activity ?? [];
    $registrations = $registrations ?? [];
    $reqList       = $reqList ?? [];
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $badgeMap = [
        'pending'  => 'db-badge--pending',
        'approved' => 'db-badge--approved',
        'rejected' => 'db-badge--danger',
    ];
    $conducted = $activity['conducted_date'] ?? $activity['activity_date'] ?? '';
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
                <div class="db-alert db-alert--danger" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#9aa0b4;margin-bottom:16px;">
                <a href="/<?= esc($role) ?>/activities" style="color:#16325c;text-decoration:none;font-weight:600;">
                    <i class="fas fa-arrow-left"></i> Brgy Activities
                </a>
                <span>›</span>
                <span><?= esc($activity['title'] ?? 'Registrations') ?></span>
            </div>

            <div style="background:#fff;border:1px solid #e2e6ee;padding:18px 22px;margin-bottom:22px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0 0 4px;font-size:17px;color:#1c2b45;"><?= esc($activity['title'] ?? '') ?></h2>
                    <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:#6b7689;margin-top:4px;">
                        <span><i class="fas fa-tag"></i> <?= esc($activity['category'] ?? 'Other') ?></span>
                        <?php if ($conducted !== ''): ?>
                            <span><i class="fas fa-calendar"></i> <?= esc(date('M d, Y', strtotime($conducted))) ?></span>
                        <?php endif; ?>
                        <?php if (! empty($activity['venue'])): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?= esc($activity['venue']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($reqList !== []): ?>
                        <div style="margin-top:8px;font-size:12px;color:#9aa0b4;">
                            Requirements: <?= esc(implode(', ', $reqList)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="text-align:center;background:#f4f6fb;padding:10px 18px;min-width:90px;">
                    <div style="font-size:22px;font-weight:700;color:#16325c;"><?= count($registrations) ?></div>
                    <div style="font-size:11px;color:#9aa0b4;">Registered</div>
                </div>
            </div>

            <form method="get" data-live-results="liveResults" style="margin-bottom:16px;">
                <div class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search residents or dates..." value="<?= esc($search ?? '') ?>">
                </div>
            </form>
            <div id="liveResults">
            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Requirements submitted</th>
                            <th>Notes</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($registrations === []): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:32px;color:#9aa0b4;">
                                    <?= ($search ?? '') !== '' ? 'No registrations match your search.' : 'No one has joined this activity yet.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $row):
                                $badge = $badgeMap[$row['status']] ?? 'db-badge--pending';
                                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                                $submitted = array_filter(array_map('trim', explode(',', $row['requirements_submitted'] ?? '')));
                                $attachments = ! empty($row['attachments']) ? json_decode($row['attachments'], true) : [];
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;color:#1c2b45;"><?= esc($name !== '' ? $name : 'Resident') ?></div>
                                        <div style="font-size:11.5px;color:#9aa0b4;">@<?= esc($row['username'] ?? '') ?> · <?= esc($row['email'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <?php if ($submitted !== []): ?>
                                            <?php foreach ($submitted as $item): ?>
                                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;background:#edfaf5;color:#0f7a62;border:1px solid #a8e6d5;padding:2px 8px;margin:2px 2px 2px 0;">
                                                    <i class="fas fa-check"></i><?= esc($item) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color:#9aa0b4;font-size:12px;">None submitted</span>
                                        <?php endif; ?>
                                        <?php if (is_array($attachments) && $attachments !== []): ?>
                                            <div style="margin-top:6px;">
                                                <?php foreach ($attachments as $attachment): ?>
                                                    <a href="/uploads/<?= esc($attachment['path'] ?? '') ?>" target="_blank" style="font-size:11px;margin-right:6px;">
                                                        <i class="fas fa-paperclip"></i> <?= esc($attachment['requirement'] ?? 'File') ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:12.5px;color:#6b7689;"><?= esc($row['notes'] ?: '—') ?></td>
                                    <td style="font-size:12px;color:#9aa0b4;"><?= ! empty($row['created_at']) ? esc(date('M d, Y', strtotime($row['created_at']))) : '—' ?></td>
                                    <td><span class="db-badge <?= $badge ?>"><?= esc(ucfirst((string) $row['status'])) ?></span></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                <form action="/<?= esc($role) ?>/activities/registrations/update/<?= (int) $row['id'] ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="db-btn db-btn--sm db-btn--primary">Approve</button>
                                                </form>
                                                <form action="/<?= esc($role) ?>/activities/registrations/update/<?= (int) $row['id'] ?>" method="post" class="registration-reject-form" style="display:inline-flex;flex-wrap:wrap;gap:5px;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="button" class="db-btn db-btn--sm db-btn--outline registration-reject-toggle">Reject</button>
                                                    <div class="registration-reject-reason" hidden style="display:none;width:100%;align-items:center;gap:5px;">
                                                        <input type="text" name="rejection_reason" placeholder="Reason for rejection" required style="min-width:140px;padding:6px 8px;border:1px solid #e2e5ef;">
                                                        <button type="submit" class="db-btn db-btn--sm db-btn--outline">Confirm</button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-size:12px;color:#9aa0b4;">Processed</span>
                                            <?php if ($row['status'] === 'rejected' && ! empty($row['rejection_reason'])): ?>
                                                <div style="max-width:180px;margin-top:5px;font-size:11px;color:#b02a37;"><strong>Reason:</strong> <?= esc($row['rejection_reason']) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
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
    <script>
        function bindRegistrationRejects() {
            document.querySelectorAll('.registration-reject-toggle').forEach(function (button) {
                if (button.dataset.bound === '1') return;
                button.dataset.bound = '1';
                button.addEventListener('click', function () {
                    const form = button.closest('.registration-reject-form');
                    const reason = form.querySelector('.registration-reject-reason');
                    reason.hidden = false;
                    reason.style.display = 'flex';
                    button.style.display = 'none';
                    reason.querySelector('input').focus();
                });
            });
        }
        bindRegistrationRejects();
        document.addEventListener('bis-live-results', bindRegistrationRejects);
    </script>
</body>

</html>
