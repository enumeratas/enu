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
        .activity-card {
            background: #fff;
            border: 1px solid #e2e6ee;
            margin-bottom: 18px;
        }

        .activity-card-head {
            padding: 14px 18px;
            border-bottom: 1px solid #e2e6ee;
            border-top: 3px solid #e0b32a;
        }

        .activity-card-head h2 {
            margin: 0 0 4px;
            font-size: 16px;
            color: #1c2b45;
        }

        .activity-card-head p {
            margin: 0;
            font-size: 12px;
            color: #6b7689;
        }

        .activity-form {
            padding: 18px;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
            gap: 14px;
        }

        .activity-span {
            grid-column: 1 / -1;
        }

        .activity-form label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        .activity-form input,
        .activity-form textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #d7dce6;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #1a1d2e;
            background: #fff;
        }

        .activity-form textarea {
            min-height: 110px;
            resize: vertical;
        }

        .activity-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 14px;
        }

        .activity-when {
            color: #5c677a;
            font-size: 12.5px;
            white-space: nowrap;
        }

        .activity-hint {
            margin: 6px 0 0;
            font-size: 12px;
            color: #6b7689;
        }

        .banner-preview {
            margin-top: 10px;
            max-width: 480px;
        }

        .banner-preview img,
        .activity-thumb {
            display: block;
            width: 100%;
            height: 150px;
            object-fit: cover;
            border: 1px solid #e2e6ee;
            background: #f4f6fa;
        }

        .activity-thumb {
            width: 96px;
            height: 54px;
            margin-bottom: 8px;
        }

        .sk-requirement-checks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 10px;
            border: 1px solid #e1e5ef;
            background: #fafbfe;
        }

        .sk-requirement-checks label {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 9px;
            border: 1px solid #e8ecf4;
            background: #fff;
            color: #4a5068;
            font-size: 11.5px;
            cursor: pointer;
        }

        .sk-form-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .sk-form-label .sk-required { color: #ef4444; }

        .sk-form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e5e7eb;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #1d2448;
            background: #fff;
            box-sizing: border-box;
        }

        .sk-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .sk-form-row--full { grid-template-columns: 1fr; }

        .sk-form-hint { margin: 6px 0 0; font-size: 12px; color: #6b7689; }

        .sk-section-divider {
            margin: 8px 0 16px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #9aa0b4;
        }

        @media (max-width: 640px) {
            .sk-form-row, .sk-requirement-checks { grid-template-columns: 1fr; }
        }

        @media (max-width: 900px) {
            .activity-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .activity-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role       = $role ?? (session()->get('role') ?: 'secretary');
    $active     = 'activities';
    $pageTitle  = 'Brgy Activities';
    $activities = $activities ?? [];
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

            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0;font-size:18px;color:#1c2b45;">Barangay Activities</h2>
                    <p style="margin:4px 0 0;font-size:12px;color:#6b7689;">Create an activity the same way SK activities are created. Residents can join from Brgy Activities.</p>
                </div>
                <a href="/<?= esc($role) ?>/activities/new" class="db-btn db-btn--primary">
                    <i class="fas fa-plus"></i> Add Activity
                </a>
            </div>

            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Category</th>
                            <th>Conducted</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($activities === []): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;color:#6b7689;padding:28px 16px;">
                                    No barangay activities yet. Use Add Activity to create one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $activity):
                                $conducted = $activity['conducted_date'] ?? $activity['activity_date'] ?? '';
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($activity['title']) ?></strong>
                                        <div style="margin-top:4px;color:#6b7689;font-size:12px;">
                                            <?= (int) ($activity['registration_count'] ?? 0) ?> registered
                                        </div>
                                    </td>
                                    <td><?= esc($activity['category'] ?? 'Other') ?></td>
                                    <td class="activity-when"><?= $conducted !== '' ? esc(date('M j, Y', strtotime($conducted))) : '—' ?></td>
                                    <td><?= esc($activity['venue'] ?: '—') ?></td>
                                    <td><?= esc($activity['status'] ?? 'Upcoming') ?></td>
                                    <td style="white-space:nowrap;">
                                        <a class="db-btn db-btn--outline db-btn--sm" href="/<?= esc($role) ?>/activities/registrations/<?= (int) $activity['id'] ?>">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a class="db-btn db-btn--outline db-btn--sm" href="/<?= esc($role) ?>/activities/edit/<?= (int) $activity['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="/<?= esc($role) ?>/activities/delete/<?= (int) $activity['id'] ?>" method="post" style="display:inline;"
                                            onsubmit="return confirm('Remove this activity?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="db-btn db-btn--outline db-btn--sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
