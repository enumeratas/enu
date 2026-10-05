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

        /* ── Header ── */
        .notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .notif-header-left h3 {
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

        .notif-header-left p {
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

        /* ── Individual notification cards ── */
        .notif-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(29, 36, 72, .06);
            overflow: hidden;
            margin-bottom: 10px;
            border: 1.5px solid transparent;
            transition: border-color .2s, box-shadow .2s;
            cursor: pointer;
            position: relative;
        }

        .notif-card.unread {
            border-color: #e8ecf4;
            background: #f8f9ff;
        }

        .notif-card.unread:hover {
            border-color: #1d2448;
            box-shadow: 0 4px 16px rgba(29, 36, 72, .1);
        }

        .notif-card.read {
            border-color: #f0f2f8;
            opacity: .78;
        }

        .notif-card.read:hover {
            opacity: 1;
            border-color: #e2e5ef;
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

        .notif-icon-wrap.success {
            background: rgba(22, 199, 154, .12);
            color: #16c79a;
        }

        .notif-icon-wrap.danger {
            background: rgba(220, 53, 69, .12);
            color: #dc3545;
        }

        .notif-icon-wrap.warning {
            background: rgba(255, 193, 7, .12);
            color: #e6a817;
        }

        .notif-icon-wrap.info {
            background: rgba(91, 111, 214, .12);
            color: #5b6fd6;
        }

        .notif-icon-wrap.event {
            background: rgba(29, 36, 72, .08);
            color: #1d2448;
        }

        .notif-icon-wrap.announce {
            background: rgba(29, 36, 72, .08);
            color: #1d2448;
        }

        .notif-icon-wrap.calendar {
            background: rgba(124, 92, 191, .12);
            color: #7c5cbf;
        }

        .notif-icon-wrap.pending {
            background: rgba(255, 193, 7, .13);
            color: #b07a00;
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

        .notif-card.read .notif-title {
            font-weight: 500;
            color: #4a5068;
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

        .notif-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #1d2448;
            flex-shrink: 0;
            margin-top: 6px;
            transition: opacity .3s;
        }

        .notif-card.read .notif-dot {
            opacity: 0;
        }

        /* ── Action button inside card ── */
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

        /* ── Stat cards ── */
        .notif-summary {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .notif-stat {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(29, 36, 72, .06);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            transition: box-shadow .2s, transform .15s;
        }

        .notif-stat:hover {
            box-shadow: 0 6px 20px rgba(29, 36, 72, .12);
            transform: translateY(-2px);
        }

        .notif-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .notif-stat-num {
            font-size: 20px;
            font-weight: 700;
            color: #1a1d2e;
            line-height: 1.1;
        }

        .notif-stat-label {
            font-size: 11px;
            color: #9aa0b4;
            font-weight: 500;
            margin-top: 2px;
        }

        .notif-badge-zero {
            opacity: .4;
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
    </style>
</head>

<body class="db-body">
    <?php
    $role      = 'sk';
    $active    = 'notifications';
    $pageTitle = 'Notifications';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $db       = \Config\Database::connect();
    $skUserId = (int) session()->get('user_id');
    $personalNotifications = array_values(array_filter(
        $personalNotifications ?? [],
        static fn($notification) => ! in_array($notification['type'] ?? '', ['youth_profiling', 'sk_join'], true)
    ));

    // ── Stat counts ───────────────────────────────────────────────────────────
    $pendingRegistrations = (int) $db->table('sk_program_registrations r')
        ->join('sk_programs p', 'p.id = r.program_id')
        ->where('p.created_by', $skUserId)
        ->where('r.status', 'pending')
        ->countAllResults();

    $approvedThisWeek = (int) $db->table('sk_program_registrations r')
        ->join('sk_programs p', 'p.id = r.program_id')
        ->where('p.created_by', $skUserId)
        ->where('r.status', 'approved')
        ->where('r.updated_at >=', date('Y-m-d', strtotime('monday this week')))
        ->countAllResults();

    $upcomingCount = (int) $db->table('sk_programs')
        ->where('created_by', $skUserId)
        ->whereIn('status', ['Active', 'Upcoming'])
        ->where('start_date >=', date('Y-m-d'))
        ->where('start_date <=', date('Y-m-d', strtotime('+7 days')))
        ->countAllResults();

    // ── Unified feed ──────────────────────────────────────────────────────────
    $feedItems = $db->table('sk_program_registrations r')
        ->select('r.id, r.status, r.created_at, r.requirements_submitted,
                  p.name AS program_name, p.id AS program_id,
                  CONCAT(u.first_name," ",u.last_name) AS resident_name')
        ->join('sk_programs p', 'p.id = r.program_id')
        ->join('users u', 'u.id = r.user_id', 'left')
        ->where('p.created_by', $skUserId)
        ->where('r.created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
        ->orderBy('r.created_at', 'DESC')
        ->limit(50)
        ->get()->getResultArray();

    $upcomingPrograms = $db->table('sk_programs')
        ->where('created_by', $skUserId)
        ->whereIn('status', ['Active', 'Upcoming'])
        ->where('start_date >=', date('Y-m-d'))
        ->where('start_date <=', date('Y-m-d', strtotime('+7 days')))
        ->orderBy('start_date', 'ASC')
        ->limit(10)
        ->get()->getResultArray();

    $profilingNotifications = $db->table('notifications')
        ->where('user_id', $skUserId)
        ->where('type', 'youth_profiling')
        ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
        ->orderBy('created_at', 'DESC')
        ->limit(50)
        ->get()->getResultArray();

    // Build unified feed
    $feed = [];
    foreach ($feedItems as $r) {
        $feed[] = ['type' => 'registration', 'timestamp' => $r['created_at'], 'data' => $r];
    }
    foreach ($upcomingPrograms as $p) {
        $feed[] = ['type' => 'program', 'timestamp' => (string) ($p['created_at'] ?? ($p['start_date'] . ' 00:00:00')), 'data' => $p];
    }
    foreach ($profilingNotifications as $notification) {
        $feed[] = ['type' => 'profiling', 'timestamp' => $notification['created_at'], 'data' => $notification];
    }
    foreach ($personalNotifications as $notification) {
        $feed[] = ['type' => 'personal', 'timestamp' => $notification['created_at'], 'data' => $notification];
    }
    usort($feed, static function (array $left, array $right): int {
        $leftTime = strtotime((string) ($left['timestamp'] ?? '')) ?: 0;
        $rightTime = strtotime((string) ($right['timestamp'] ?? '')) ?: 0;

        return $rightTime <=> $leftTime;
    });

    $total = $pendingRegistrations + $upcomingCount;
    $totalFeed = count($feed);
    $unreadDirect = (new \App\Models\NotificationModel())->countUnread((int) $skUserId, false);

    function skTimeStr(string $datetime): string
    {
        return notification_time($datetime);
    }

    $statusMap = [
        'pending'  => ['style' => 'pending', 'icon' => 'fas fa-user-clock'],
        'approved' => ['style' => 'success', 'icon' => 'fas fa-check-circle'],
        'rejected' => ['style' => 'danger',  'icon' => 'fas fa-times-circle'],
    ];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="notif-wrap">

                <!-- ── Stat cards ── -->
                <div class="notif-summary">
                    <a href="/sk/programs" class="notif-stat <?= $pendingRegistrations === 0 ? 'notif-badge-zero' : '' ?>">
                        <div class="notif-stat-icon" style="background:rgba(255,193,7,.13);color:#b07a00;">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <div class="notif-stat-num"><?= $pendingRegistrations ?></div>
                            <div class="notif-stat-label">Pending Approval</div>
                        </div>
                    </a>
                    <a href="/sk/programs" class="notif-stat">
                        <div class="notif-stat-icon" style="background:rgba(22,199,154,.13);color:#0e9464;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <div class="notif-stat-num"><?= $approvedThisWeek ?></div>
                            <div class="notif-stat-label">Approved This Week</div>
                        </div>
                    </a>
                    <a href="/sk/programs" class="notif-stat <?= $upcomingCount === 0 ? 'notif-badge-zero' : '' ?>">
                        <div class="notif-stat-icon" style="background:rgba(124,92,191,.12);color:#7c5cbf;">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <div class="notif-stat-num"><?= $upcomingCount ?></div>
                            <div class="notif-stat-label">Upcoming (7 days)</div>
                        </div>
                    </a>
                </div>

                <!-- ── Header ── -->
                <div class="notif-header">
                    <div class="notif-header-left">
                        <h3>
                            Notifications
                            <?php if ($unreadDirect > 0): ?>
                                <span class="notif-count-badge"><?= $unreadDirect ?></span>
                            <?php endif; ?>
                        </h3>
                        <p><?= $totalFeed ?> total · <?= $pendingRegistrations ?> pending approval · <?= $unreadDirect ?> unread</p>
                    </div>
                </div>

                <!-- ── Tabs ── -->
                <div class="notif-tabs">
                    <button class="notif-tab active" onclick="filterFeed('all',this)">All</button>
                    <button class="notif-tab" onclick="filterFeed('pending',this)">Pending</button>
                    <button class="notif-tab" onclick="filterFeed('program',this)">Programs</button>
                    <button class="notif-tab" onclick="filterFeed('profiling',this)">Profiling</button>
                </div>

                <!-- ── Feed ── -->
                <div id="feedList">
                    <?php if (empty($feed)): ?>
                        <div class="notif-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>No recent activity. Check back later.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feed as $item):
                            if ($item['type'] === 'registration'):
                                $r    = $item['data'];
                                $sm   = $statusMap[$r['status']] ?? $statusMap['pending'];
                                $isPending = $r['status'] === 'pending';
                                $resName   = esc($r['resident_name'] ?? 'Unknown');
                                $progName  = esc($r['program_name']);
                                $timeStr   = skTimeStr($r['created_at']);
                                $title     = $isPending
                                    ? $resName . ' wants to join ' . $progName
                                    : 'Registration ' . ucfirst($r['status']) . ' — ' . $progName;
                                $desc      = $isPending
                                    ? 'Awaiting your approval for the "' . $progName . '" program.'
                                    : $resName . '\'s registration for "' . $progName . '" has been ' . $r['status'] . '.';
                                if (! empty($r['requirements_submitted'])) {
                                    $desc .= ' Requirements: ' . mb_strimwidth($r['requirements_submitted'], 0, 60, '…');
                                }
                        ?>
                                <div class="notif-card <?= $isPending ? 'unread' : 'read' ?>"
                                    data-filter="<?= $isPending ? 'pending' : 'activity' ?>">
                                    <div class="notif-card-inner">
                                        <div class="notif-icon-wrap <?= $sm['style'] ?>">
                                            <i class="<?= $sm['icon'] ?>"></i>
                                        </div>
                                        <div class="notif-body">
                                            <div class="notif-title"><?= $title ?></div>
                                            <div class="notif-desc"><?= esc($desc) ?></div>
                                            <div class="notif-meta">
                                                <i class="fas fa-clock"></i> <time class="js-local-notification-time" datetime="<?= esc(notification_time_iso($r['created_at'] ?? null)) ?>"><?= esc($timeStr) ?></time>
                                                <?php if ($isPending): ?>
                                                    <span style="color:#b07a00;font-weight:600;">· Pending Approval</span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="/sk/programs/registrations/<?= (int)$r['program_id'] ?>"
                                                class="notif-action-btn <?= $isPending ? 'primary' : '' ?>"
                                                onclick="event.stopPropagation();">
                                                <i class="fas <?= $isPending ? 'fa-check' : 'fa-eye' ?>"></i>
                                                <?= $isPending ? 'Review' : 'View' ?>
                                            </a>
                                        </div>
                                        <div class="notif-dot" style="<?= $isPending ? '' : 'opacity:0;' ?>"></div>
                                    </div>
                                </div>

                            <?php elseif ($item['type'] === 'program'):
                                $p         = $item['data'];
                                $daysUntil = (int) floor((strtotime($p['start_date']) - strtotime(date('Y-m-d'))) / 86400);
                                $regCount  = (int) $db->table('sk_program_registrations')->where('program_id', $p['id'])->countAllResults();
                                $countdown = $daysUntil === 0 ? 'Today' : ($daysUntil === 1 ? 'Tomorrow' : 'In ' . $daysUntil . ' days');
                                $desc      = 'Starts ' . date('F d, Y', strtotime($p['start_date']));
                                if (! empty($p['venue'])) $desc .= ' at ' . $p['venue'];
                                $desc .= '. ' . $regCount . ' registered.';
                            ?>
                                <div class="notif-card read" data-filter="program">
                                    <div class="notif-card-inner">
                                        <div class="notif-icon-wrap calendar">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="notif-body">
                                            <div class="notif-title">Upcoming: <?= esc($p['name']) ?></div>
                                            <div class="notif-desc"><?= esc($desc) ?></div>
                                            <div class="notif-meta">
                                                <i class="fas fa-clock"></i>
                                                <?= $countdown ?>
                                                <span style="color:#7c5cbf;font-weight:600;">· <?= $daysUntil === 0 ? 'Today!' : ($daysUntil === 1 ? 'Tomorrow!' : 'Upcoming') ?></span>
                                            </div>
                                            <a href="/sk/programs/registrations/<?= (int)$p['id'] ?>"
                                                class="notif-action-btn"
                                                onclick="event.stopPropagation();">
                                                <i class="fas fa-list"></i> Registrations
                                            </a>
                                        </div>
                                        <div class="notif-dot" style="opacity:0;"></div>
                                    </div>
                                </div>
                            <?php elseif ($item['type'] === 'profiling'):
                                $notification = $item['data'];
                                $isUnread = empty($notification['read_at']);
                            ?>
                                <div class="notif-card <?= $isUnread ? 'unread' : 'read' ?>" data-filter="profiling">
                                    <div class="notif-card-inner">
                                        <div class="notif-icon-wrap info"><i class="fas fa-user-edit"></i></div>
                                        <div class="notif-body">
                                            <div class="notif-title"><?= esc($notification['title']) ?></div>
                                            <div class="notif-desc"><?= esc($notification['body']) ?></div>
                                            <div class="notif-meta"><i class="fas fa-clock"></i> <time class="js-local-notification-time" datetime="<?= esc(notification_time_iso($notification['created_at'] ?? null)) ?>"><?= esc(skTimeStr($notification['created_at'])) ?></time></div>
                                            <a href="/sk/profiling" class="notif-action-btn primary" onclick="markNotificationRead(event, <?= (int) $notification['id'] ?>, this.href);"><i class="fas fa-eye"></i> View Profiling</a>
                                        </div>
                                        <div class="notif-dot" style="<?= $isUnread ? '' : 'opacity:0;' ?>"></div>
                                    </div>
                                </div>
                            <?php elseif ($item['type'] === 'personal'):
                                $notification = $item['data'];
                                $isUnread = empty($notification['read_at']);
                                $actionHref = $notification['link'] ?: '/sk/notifications';
                                if (str_starts_with((string) ($notification['type'] ?? ''), 'clearance_')) {
                                    $actionHref = '/sk/clearance';
                                }
                            ?>
                                <div class="notif-card <?= $isUnread ? 'unread' : 'read' ?>" data-filter="activity">
                                    <div class="notif-card-inner">
                                        <div class="notif-icon-wrap event"><i class="fas fa-bell"></i></div>
                                        <div class="notif-body">
                                            <div class="notif-title"><?= esc($notification['title']) ?></div>
                                            <?php if (! empty($notification['body'])): ?>
                                                <div class="notif-desc"><?= esc($notification['body']) ?></div>
                                            <?php endif; ?>
                                            <div class="notif-meta"><i class="fas fa-clock"></i> <time class="js-local-notification-time" datetime="<?= esc(notification_time_iso($notification['created_at'] ?? null)) ?>"><?= esc(skTimeStr($notification['created_at'])) ?></time></div>
                                            <a href="<?= esc($actionHref) ?>" class="notif-action-btn <?= $isUnread ? 'primary' : '' ?>" onclick="markNotificationRead(event, <?= (int) $notification['id'] ?>, this.href);"><i class="fas fa-eye"></i> View</a>
                                        </div>
                                        <div class="notif-dot" style="<?= $isUnread ? '' : 'opacity:0;' ?>"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script>
        function markNotificationRead(event, id, link) {
            event.preventDefault();
            fetch('<?= esc(site_url('sk/notifications/read/')) ?>' + id, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).finally(() => { window.location.href = link; });
        }

        function filterFeed(filter, btn) {
            document.querySelectorAll('.notif-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('#feedList .notif-card').forEach(card => {
                const f = card.dataset.filter;
                card.style.display = filter === 'all' ? '' :
                    filter === 'pending' ? (f === 'pending' ? '' : 'none') :
                    filter === 'program' ? (f === 'program' ? '' : 'none') :
                    filter === 'profiling' ? (f === 'profiling' ? '' : 'none') : '';
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>