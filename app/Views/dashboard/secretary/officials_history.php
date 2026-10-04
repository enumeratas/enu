<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officials History — Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Filter tabs ── */
        .oh-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .oh-tab {
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #e2e5ef;
            background: #fff;
            color: #9aa0b4;
            font-family: 'Poppins', sans-serif;
            transition: all .18s;
        }

        .oh-tab.active   { background: #1d2448; color: #fff; border-color: #1d2448; }
        .oh-tab:hover:not(.active) { border-color: #1d2448; color: #1d2448; }

        /* ── Tenure card ── */
        .oh-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 8px rgba(29,36,72,.06);
            border: 1.5px solid #eef0f8;
            padding: 20px 22px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 12px;
            transition: box-shadow .18s;
        }

        .oh-card:hover { box-shadow: 0 4px 16px rgba(29,36,72,.10); }

        .oh-card--active { border-color: #b2e8d2; background: #f8fffc; }

        /* Avatar */
        .oh-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .oh-avatar--captain   { background: linear-gradient(135deg,#1d2448,#2e3a6e); }
        .oh-avatar--secretary { background: linear-gradient(135deg,#8e44ad,#9b59b6); }
        .oh-avatar--sk        { background: linear-gradient(135deg,#16a085,#1abc9c); }

        /* Body */
        .oh-body { flex: 1; min-width: 0; }

        .oh-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0 0 5px;
        }

        .oh-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12.5px;
            color: #6b7280;
        }

        .oh-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .oh-meta-item i { color: #b0b6cc; font-size: 11px; }

        /* Duration badge */
        .oh-duration {
            font-size: 12px;
            font-weight: 600;
            background: #f0f2f8;
            color: #4a5068;
            padding: 4px 12px;
            border-radius: 100px;
            margin-top: 8px;
            display: inline-block;
        }

        /* Right: role badge + status */
        .oh-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            flex-shrink: 0;
        }

        .oh-role-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 100px;
        }

        .oh-role-badge--captain   { background: rgba(29,36,72,.1);  color: #1d2448; }
        .oh-role-badge--secretary { background: rgba(142,68,173,.12); color: #8e44ad; }
        .oh-role-badge--sk        { background: rgba(22,160,133,.12); color: #16a085; }

        .oh-status-active {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
            background: #e6f9f1;
            color: #0e9464;
            border: 1px solid #b2e8d2;
        }

        .oh-status-past {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
            background: #f3f4f8;
            color: #6b7280;
            border: 1px solid #e2e5ef;
        }

        /* Empty state */
        .oh-empty {
            text-align: center;
            padding: 60px 20px;
            color: #9aa0b4;
        }

        .oh-empty i { font-size: 44px; display: block; margin-bottom: 14px; opacity: .25; }
        .oh-empty p { font-size: 14px; font-weight: 600; color: #6b7280; margin: 0 0 6px; }
        .oh-empty span { font-size: 13px; color: #b0b6cc; }

        /* Year divider */
        .oh-year-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 12px;
        }

        .oh-year-divider span {
            font-size: 12px;
            font-weight: 700;
            color: #9aa0b4;
            text-transform: uppercase;
            letter-spacing: .8px;
            white-space: nowrap;
        }

        .oh-year-divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid #eef0f8;
            margin: 0;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $active    = 'create_account';
    $pageTitle = 'Officials History';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $grouped = $grouped ?? [];
    $role    = $role    ?? 'secretary';

    // Helper: format duration between two dates
    function tenureDuration(?string $from, ?string $to = null): string {
        if (! $from) return '';
        $start = new DateTime($from);
        $end   = $to ? new DateTime($to) : new DateTime('today');
        $diff  = $start->diff($end);
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' yr' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) $parts[] = $diff->m . ' mo' . ($diff->m > 1 ? 's' : '');
        if (! $parts && $diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        return $parts ? implode(' ', $parts) : 'Less than a day';
    }

    $roleColors = [
        'captain'   => 'captain',
        'secretary' => 'secretary',
        'sk'        => 'sk',
    ];

    $roleLabels = ['captain' => 'Captain', 'secretary' => 'Secretary', 'sk' => 'SK'];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <!-- Back link -->
            <a href="/<?= esc($role) ?>/create-account" class="cr-back-link" style="display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .15s;" onmouseover="this.style.color='#1d2448'" onmouseout="this.style.color='#6b7280'">
                <i class="fas fa-arrow-left"></i> Back to Create Official
            </a>

            <!-- Page heading -->
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
                <div>
                    <h2 style="margin:0 0 4px;font-size:20px;font-weight:700;color:#1a1d2e;">Officials History</h2>
                    <p style="margin:0;font-size:13px;color:#9aa0b4;">
                        Complete record of appointed Captains, Secretaries, and SK officials with their tenure dates.
                    </p>
                </div>
                <div style="font-size:12.5px;color:#9aa0b4;background:#f5f7ff;border:1px solid #dde2f5;border-radius:8px;padding:7px 14px;display:flex;align-items:center;gap:7px;">
                    <i class="fas fa-info-circle" style="color:#5b6fd6;"></i>
                    <?= count($grouped) ?> tenure record<?= count($grouped) !== 1 ? 's' : '' ?> found
                </div>
            </div>

            <form method="get" data-live-results="ohList" style="margin-bottom:16px;">
                <div class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search official name or date..." value="<?= esc($search ?? '') ?>">
                </div>
            </form>

            <!-- Filter tabs -->
            <div class="oh-tabs">
                <button class="oh-tab active" onclick="filterRole('all',this)">All Officials</button>
                <button class="oh-tab" onclick="filterRole('captain',this)">
                    <i class="fas fa-user-tie" style="margin-right:5px;font-size:10px;"></i>Captain
                </button>
                <button class="oh-tab" onclick="filterRole('secretary',this)">
                    <i class="fas fa-user-shield" style="margin-right:5px;font-size:10px;"></i>Secretary
                </button>
                <button class="oh-tab" onclick="filterRole('sk',this)">
                    <i class="fas fa-users" style="margin-right:5px;font-size:10px;"></i>SK
                </button>
                <button class="oh-tab" onclick="filterRole('active',this)" style="margin-left:auto;">
                    <i class="fas fa-circle" style="margin-right:5px;font-size:8px;color:#16c79a;"></i>Currently Active
                </button>
            </div>

            <!-- Cards -->
            <div id="ohList">
                <?php if (empty($grouped)): ?>
                    <div class="oh-empty">
                        <i class="fas fa-history"></i>
                        <p><?= ($search ?? '') !== '' ? 'No officials match your search.' : 'No history yet' ?></p>
                        <span>Appointment and revocation records will appear here once officials are assigned or revoked.</span>
                    </div>
                <?php else: ?>
                    <?php
                    $lastYear = null;
                    foreach ($grouped as $rec):
                        $year = date('Y', strtotime($rec['appointed_at']));
                        $rc   = $roleColors[$rec['role']] ?? 'captain';
                        $rl   = $roleLabels[$rec['role']] ?? ucfirst($rec['role']);
                        $apptDate  = date('F d, Y', strtotime($rec['appointed_at']));
                        $revokeDate = $rec['revoked_at'] ? date('F d, Y', strtotime($rec['revoked_at'])) : null;
                        $duration  = tenureDuration($rec['appointed_at'], $rec['revoked_at']);
                        $initial   = strtoupper($rec['full_name'][0] ?? '?');
                    ?>
                        <?php if ($year !== $lastYear): ?>
                            <div class="oh-year-divider" data-role="<?= esc($rec['role']) ?>" data-active="<?= $rec['still_active'] ? '1' : '0' ?>">
                                <span><?= $year ?></span>
                                <hr>
                            </div>
                            <?php $lastYear = $year; ?>
                        <?php endif; ?>

                        <div class="oh-card <?= $rec['still_active'] ? 'oh-card--active' : '' ?>"
                             data-role="<?= esc($rec['role']) ?>"
                             data-active="<?= $rec['still_active'] ? '1' : '0' ?>">

                            <!-- Avatar -->
                            <div class="oh-avatar oh-avatar--<?= $rc ?>">
                                <?= $initial ?>
                            </div>

                            <!-- Body -->
                            <div class="oh-body">
                                <div class="oh-name"><?= esc($rec['full_name']) ?></div>

                                <div class="oh-meta">
                                    <!-- Appointed date -->
                                    <div class="oh-meta-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Appointed: <strong><?= $apptDate ?></strong></span>
                                    </div>

                                    <!-- Revoke date or still serving -->
                                    <?php if ($rec['still_active']): ?>
                                        <div class="oh-meta-item">
                                            <i class="fas fa-calendar-times"></i>
                                            <span>Until: <strong>Present</strong></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="oh-meta-item">
                                            <i class="fas fa-calendar-times"></i>
                                            <span>Until: <strong><?= $revokeDate ?></strong></span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Who appointed -->
                                    <?php if (! empty($rec['appointed_by']) && $rec['appointed_by'] !== 'System (back-fill)'): ?>
                                        <div class="oh-meta-item">
                                            <i class="fas fa-user-edit"></i>
                                            <span>By: <?= esc($rec['appointed_by']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Duration pill -->
                                <span class="oh-duration">
                                    <i class="fas fa-hourglass-half" style="margin-right:5px;font-size:10px;color:#9aa0b4;"></i>
                                    <?= $duration ?><?= $rec['still_active'] ? ' (and counting)' : '' ?>
                                </span>
                            </div>

                            <!-- Right: role badge + status -->
                            <div class="oh-right">
                                <span class="oh-role-badge oh-role-badge--<?= $rc ?>">
                                    <?= $rl ?>
                                </span>
                                <?php if ($rec['still_active']): ?>
                                    <span class="oh-status-active">
                                        <i class="fas fa-circle" style="font-size:7px;"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="oh-status-past">
                                        <i class="fas fa-history" style="font-size:9px;"></i> Past
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div><!-- /#ohList -->

        </div>
    </div>

    <script>
        function filterRole(filter, btn) {
            document.querySelectorAll('.oh-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('#ohList .oh-card, #ohList .oh-year-divider').forEach(el => {
                const role   = el.dataset.role   || '';
                const active = el.dataset.active  || '0';

                let show = false;
                if (filter === 'all')    show = true;
                else if (filter === 'active') show = active === '1';
                else show = role === filter;

                el.style.display = show ? '' : 'none';
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>
