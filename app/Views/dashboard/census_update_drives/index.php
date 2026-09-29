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

            <?php if ($open): ?>
                <div class="db-alert" style="margin-bottom:16px;background:#f4f6fd;border:1px solid #c5d0e8;color:#16325c;">
                    <i class="fas fa-bell"></i>
                    An update window is open until <strong><?= esc(date('F j, Y', strtotime($open['deadline']))) ?></strong>
                    — <?= esc($open['title']) ?>.
                </div>
            <?php endif; ?>

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
                                    No census update drives have been sent yet.
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
</body>

</html>
