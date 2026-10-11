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
            flex: 1;
        }

        .brgy-empty {
            background: #fff;
            border: 1px solid #e2e6ee;
            text-align: center;
            padding: 48px 20px;
            color: #6b7689;
        }

        .brgy-foot {
            padding: 14px 18px;
            border-top: 1px solid #e2e6ee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            background: #f9fafc;
        }

        .reg-stat {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 20px;
        }

        .reg-stat--approved {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .reg-stat--pending {
            background: #fff8ee;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .reg-stat--total {
            background: #f3f5fb;
            color: #1d2448;
            border: 1px solid #dde4f0;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $active    = 'activities';
    $pageTitle = 'Brgy Activities';
    $activities = $activities ?? [];
    $zone       = $zone ?? '';
    $search     = $search ?? '';
    include APPPATH . 'Views/dashboard/sidebar.php';
    ?>
    <div class="db-main">
        <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
        <div class="db-content">

            <div class="dash-welcome" style="margin-bottom:16px;">
                <div>
                    <h2>Barangay Activities</h2>
                    <p>
                        View all barangay activities and manage registrations from residents in your zone
                        <?= $zone !== '' ? '(<strong>' . esc($zone) . '</strong>)' : '' ?>.
                    </p>
                </div>
                <div class="dash-welcome-icon"><i class="fas fa-bullhorn"></i></div>
            </div>

            <form method="get" data-live-results="liveResults" style="margin-bottom:16px;">
                <div class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off"
                        placeholder="Search activities or dates..." value="<?= esc($search) ?>">
                </div>
            </form>

            <div id="liveResults">
                <?php if ($activities === []): ?>
                    <div class="brgy-empty">
                        <i class="fas fa-bullhorn" style="font-size:36px;color:#c5cdd8;display:block;margin-bottom:12px;"></i>
                        <p style="margin:0;font-weight:600;color:#1c2b45;">
                            <?= $search !== '' ? 'No activities match your search.' : 'No activities posted yet.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="brgy-grid">
                        <?php foreach ($activities as $activity):
                            $conducted = $activity['conducted_date'] ?? $activity['activity_date'] ?? '';
                            $closed    = ! empty($activity['end_date']) && $activity['end_date'] < date('Y-m-d');
                            $status    = $closed ? 'Closed' : ($activity['status'] ?? 'Upcoming');
                            $rc        = $activity['reg_counts'] ?? [];
                            $total     = (int) ($activity['registration_count'] ?? 0);
                            $approved  = (int) ($rc['approved'] ?? 0);
                            $pending   = (int) ($rc['pending']  ?? 0);
                            $slots     = (int) ($activity['target_participants'] ?? 0);
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
                                            <span><i class="fas fa-calendar"></i>
                                                <?= $conducted !== '' ? esc(date('M j, Y', strtotime($conducted))) : 'Date TBA' ?>
                                            </span>
                                            <?php if (! empty($activity['venue'])): ?>
                                                <span><i class="fas fa-map-marker-alt"></i> <?= esc($activity['venue']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="dbadge <?= $status === 'Active' ? 'dbadge--approved' : 'dbadge--resolved' ?>">
                                        <?= esc($status) ?>
                                    </span>
                                </div>

                                <div class="brgy-body">
                                    <?= nl2br(esc($activity['description'] ?: 'Details will be announced by the barangay.')) ?>

                                    <!-- Registration summary -->
                                    <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                                        <span class="reg-stat reg-stat--total">
                                            <i class="fas fa-users"></i>
                                            <?= $total ?> registered<?= $slots > 0 ? ' / ' . $slots . ' slots' : '' ?>
                                        </span>
                                        <?php if ($approved > 0): ?>
                                            <span class="reg-stat reg-stat--approved">
                                                <i class="fas fa-check-circle"></i> <?= $approved ?> approved
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($pending > 0): ?>
                                            <span class="reg-stat reg-stat--pending">
                                                <i class="fas fa-clock"></i> <?= $pending ?> pending
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="brgy-foot">
                                    <span style="font-size:12px;color:#6b7689;">
                                        <?= $zone !== '' ? 'Showing registrants from <strong>' . esc($zone) . '</strong>' : 'All registrants' ?>
                                    </span>
                                    <a href="/council/activities/registrations/<?= (int) $activity['id'] ?>"
                                        class="db-btn db-btn--primary db-btn--sm">
                                        <i class="fas fa-list-ul"></i>
                                        Manage Registrations
                                        <?php if ($pending > 0): ?>
                                            <span style="background:#e0b32a;color:#1d2448;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:4px;">
                                                <?= $pending ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>

</html>