<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .notif-wrap {
            max-width: 100%;
        }

        /* ── Stat cards ── */
        .notif-summary {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .notif-stat {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(29, 36, 72, .06);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: box-shadow .2s, transform .15s;
            text-decoration: none;
        }

        .notif-stat:hover {
            box-shadow: 0 6px 20px rgba(29, 36, 72, .12);
            transform: translateY(-2px);
        }

        .notif-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .notif-stat-num {
            font-size: 18px;
            font-weight: 700;
            color: #1a1d2e;
            line-height: 1.1;
        }

        .notif-stat-label {
            font-size: 11px;
            color: #9aa0b4;
            font-weight: 500;
            margin-top: 1px;
        }

        .notif-badge-zero {
            opacity: .45;
        }

        /* ── Individual notification cards ── */
        .notif-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(29, 36, 72, .06);
            overflow: hidden;
            margin-bottom: 10px;
            border: 1.5px solid #f0f2f8;
            transition: border-color .2s, box-shadow .2s;
            cursor: pointer;
        }

        .notif-card:hover {
            border-color: #1d2448;
            box-shadow: 0 4px 16px rgba(29, 36, 72, .1);
        }

        .notif-card-inner {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 18px;
        }

        .notif-icon-wrap {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .notif-body {
            flex: 1;
            min-width: 0;
        }

        .notif-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a1d2e;
            margin: 0 0 4px;
            line-height: 1.5;
        }

        .notif-desc {
            font-size: 12.5px;
            color: #6b7280;
            margin: 0 0 6px;
            line-height: 1.6;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11.5px;
            color: #b0b6cc;
            flex-wrap: wrap;
        }

        .notif-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            border: 1.5px solid #e2e5ef;
            background: #fff;
            color: #1d2448;
            transition: all .15s;
        }

        .notif-action-btn:hover {
            background: #1d2448;
            color: #fff;
            border-color: #1d2448;
        }

        .notif-action-btn.primary {
            background: #1d2448;
            color: #fff;
            border-color: #1d2448;
        }

        .notif-action-btn.primary:hover {
            opacity: .85;
        }

        /* ── Header ── */
        .notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .notif-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0 0 2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1d2448;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 100px;
            padding: 0 6px;
        }

        .notif-header p {
            font-size: 12.5px;
            color: #9aa0b4;
            margin: 0;
        }

        /* ── Tabs ── */
        .notif-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
        }

        .notif-tab {
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #e2e5ef;
            background: #fff;
            color: #9aa0b4;
            transition: all .2s;
            font-family: 'Poppins', sans-serif;
        }

        .notif-tab.active {
            background: #1d2448;
            color: #fff;
            border-color: #1d2448;
        }

        .notif-tab:hover:not(.active) {
            border-color: #1d2448;
            color: #1d2448;
        }

        /* ── Empty ── */
        .notif-empty {
            text-align: center;
            padding: 48px 20px;
            color: #9aa0b4;
        }

        .notif-empty i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
            color: #d0d5e8;
        }

        .notif-empty p {
            font-size: 14px;
            margin: 0;
        }

        @media (max-width: 640px) {
            .notif-summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = $role      ?? 'secretary';
    $active    = 'notifications';
    $pageTitle = 'Notifications';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    // Unified feed (sorted newest-first by controller)
    $feedItems  = $feedItems  ?? [];
    $personalNotifications = $personalNotifications ?? [];
    $unreadPersonal = count(array_filter($personalNotifications, static fn($notification) => empty($notification['read_at'])));

    // Individual counts for stat cards
    $pendingAccounts   = $pendingAccounts   ?? 0;
    $pendingClearances = $pendingClearances ?? 0;
    $newBlotters       = $newBlotters       ?? 0;
    $upcomingHearings  = $upcomingHearings  ?? 0;
    $upcomingSchedules = $upcomingSchedules ?? 0;
    $pendingConcerns   = $pendingConcerns   ?? 0;

    $total = $pendingAccounts + $pendingClearances + $newBlotters
        + $upcomingHearings + $upcomingSchedules + $pendingConcerns + $unreadPersonal;

    function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)    return 'just now';
        if ($diff < 3600)  return floor($diff / 60)   . 'm ago';
        if ($diff < 86400) return floor($diff / 3600)  . 'h ago';
        return floor($diff / 86400) . 'd ago';
    }

    // Per-type display config
    $typeConfig = [
        'account'   => ['icon' => 'fa-user-clock',        'color' => '#e6a800', 'bg' => 'rgba(255,193,7,.14)',   'label' => 'Pending Account'],
        'clearance' => ['icon' => 'fa-file-alt',           'color' => '#5b6fd6', 'bg' => 'rgba(91,111,214,.12)',  'label' => 'Document Request'],
        'blotter'   => ['icon' => 'fa-exclamation-circle', 'color' => '#dc3545', 'bg' => 'rgba(220,53,69,.12)',   'label' => 'Blotter Report'],
        'concern'   => ['icon' => 'fa-comments',           'color' => '#e67e22', 'bg' => 'rgba(230,126,34,.12)',  'label' => 'Concern / Inquiry'],
        'schedule'  => ['icon' => 'fa-calendar-alt',       'color' => '#7c5cbf', 'bg' => 'rgba(124,92,191,.12)',  'label' => 'Upcoming Event'],
        'personal'  => ['icon' => 'fa-bell',               'color' => '#1d2448', 'bg' => 'rgba(29,36,72,.08)',    'label' => 'Direct Update'],
    ];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <!-- ── Header ── -->
            <div class="notif-header">
                <div>
                    <h3>
                        Notifications
                        <?php if ($total > 0): ?>
                            <span class="notif-count-badge"><?= $total ?></span>
                        <?php endif; ?>
                    </h3>
                    <p>
                        <?= $total > 0
                            ? $total . ' item' . ($total !== 1 ? 's' : '') . ' need your attention'
                            : 'Everything is up to date' ?>
                    </p>
                </div>
                <button class="db-btn db-btn--outline" onclick="location.reload()"
                    style="display:inline-flex;align-items:center;gap:7px;font-size:12.5px;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <!-- ── Filter tabs ── -->
            <div class="notif-tabs">
                <button class="notif-tab active" onclick="filterNotifs('all',this)">All</button>
                <button class="notif-tab" onclick="filterNotifs('account',this)">Accounts</button>
                <button class="notif-tab" onclick="filterNotifs('clearance',this)">Clearances</button>
                <button class="notif-tab" onclick="filterNotifs('blotter',this)">Blotter</button>
                <button class="notif-tab" onclick="filterNotifs('concern',this)">Concerns</button>
                <button class="notif-tab" onclick="filterNotifs('schedule',this)">Events</button>
            </div>

            <?php if (! empty($personalNotifications)): ?>
                <div class="notif-wrap" style="margin-bottom:20px;">
                    <?php foreach ($personalNotifications as $notification):
                        $isUnread = empty($notification['read_at']);
                        $cfg = $typeConfig['personal'];
                        $actionHref = $notification['link'] ?: '/' . $role . '/notifications';
                    ?>
                        <div class="notif-card <?= $isUnread ? 'unread' : 'read' ?>" data-type="personal">
                            <div class="notif-card-inner">
                                <div class="notif-icon-wrap" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                                    <i class="fas <?= $cfg['icon'] ?>"></i>
                                </div>
                                <div class="notif-body">
                                    <div class="notif-title"><?= esc($notification['title']) ?></div>
                                    <?php if (! empty($notification['body'])): ?>
                                        <div class="notif-desc"><?= esc($notification['body']) ?></div>
                                    <?php endif; ?>
                                    <div class="notif-meta">
                                        <i class="fas fa-clock"></i>
                                        <time class="js-local-notification-time" datetime="<?= esc(notification_time_iso($notification['created_at'] ?? null)) ?>"><?= esc(notification_time($notification['created_at'] ?? null)) ?></time>
                                        <span style="font-weight:600;color:<?= $cfg['color'] ?>;">· <?= $cfg['label'] ?></span>
                                    </div>
                                    <a href="<?= esc($actionHref) ?>" class="notif-action-btn <?= $isUnread ? 'primary' : '' ?>" onclick="markNotificationRead(event, <?= (int) $notification['id'] ?>, this.href);">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                                <div class="notif-dot" style="<?= $isUnread ? '' : 'opacity:0;' ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ── Individual cards ── -->
            <div class="notif-wrap" id="notifList">
                <?php if (empty($feedItems)): ?>
                    <div class="notif-empty">
                        <i class="fas fa-check-double" style="color:#16c79a;"></i>
                        <p>Everything is up to date. No pending items.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedItems as $item):
                        $cfg  = $typeConfig[$item['type']] ?? $typeConfig['schedule'];
                        $time = notification_time($item['created_at'] ?? null);

                        // Build a readable description line
                        $desc = $item['sub'] ?? '';
                        if (! empty($item['email']))    $desc .= ($desc ? ' · ' : '') . $item['email'];
                        if (! empty($item['appt_date'])) $desc .= ' · Appt: ' . date('M d, Y', strtotime($item['appt_date']));

                        // Determine action
                        $actionHref  = '#';
                        $actionLabel = 'View';
                        $isPrimary   = false;
                        if ($item['type'] === 'account') {
                            $actionHref  = '/' . $role . '/pending-accounts#pending-account-' . (int)($item['user_id'] ?? 0);
                            $actionLabel = 'Review';
                            $isPrimary   = true;
                        } elseif ($item['type'] === 'clearance') {
                            $actionHref = '/' . $role . '/clearance/request/' . (int)($item['user_id'] ?? 0);
                        } elseif ($item['type'] === 'blotter') {
                            $actionHref = '/' . $role . '/blotter/' . (int)($item['blotter_id'] ?? 0);
                        } elseif ($item['type'] === 'concern') {
                            $actionHref  = '/' . $role . '/concern/' . (int)($item['concern_id'] ?? 0);
                            $actionLabel = 'Review';
                            $isPrimary   = true;
                        } elseif ($item['type'] === 'schedule') {
                            $actionHref = '/' . $role . '/calendar/view/' . (int)($item['schedule_id'] ?? 0);
                            $actionLabel = 'Calendar';
                        }
                    ?>
                        <div class="notif-card" data-type="<?= esc($item['type']) ?>">
                            <div class="notif-card-inner">
                                <div class="notif-icon-wrap" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                                    <i class="fas <?= $cfg['icon'] ?>"></i>
                                </div>
                                <div class="notif-body">
                                    <div class="notif-title"><?= esc($item['title']) ?></div>
                                    <?php if ($desc): ?>
                                        <div class="notif-desc"><?= esc($desc) ?></div>
                                    <?php endif; ?>
                                    <div class="notif-meta">
                                        <i class="fas fa-clock"></i> <time class="js-local-notification-time" datetime="<?= esc(notification_time_iso($item['created_at'] ?? null)) ?>"><?= esc($time) ?></time>
                                        <span style="font-weight:600;color:<?= $cfg['color'] ?>;">
                                            · <?= $cfg['label'] ?>
                                        </span>
                                    </div>
                                    <a href="<?= esc($actionHref) ?>"
                                        class="notif-action-btn <?= $isPrimary ? 'primary' : '' ?>"
                                        onclick="event.stopPropagation();">
                                        <i class="fas <?= $isPrimary ? 'fa-arrow-right' : 'fa-eye' ?>"></i>
                                        <?= $actionLabel ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div><!-- /.notif-wrap -->

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <script>
        function markNotificationRead(event, id, link) {
            event.preventDefault();
            fetch('<?= esc(site_url($role . '/notifications/read/')) ?>' + id, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).finally(() => { window.location.href = link; });
        }

        function filterNotifs(type, btn) {
            document.querySelectorAll('.notif-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('#notifList .notif-card').forEach(card => {
                card.style.display = type === 'all' || card.dataset.type === type ? '' : 'none';
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>


</body>

</html>