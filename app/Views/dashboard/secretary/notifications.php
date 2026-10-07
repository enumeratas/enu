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
        }

        .notif-card.unread {
            border-color: #e8ecf4;
            background: #f8f9ff;
        }

        .notif-card.read {
            opacity: .92;
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

        .notif-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .notif-mark-read-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            border: 1.5px solid #e2e5ef;
            background: #fff;
            color: #667085;
            cursor: pointer;
            transition: all .15s;
        }

        .notif-mark-read-btn:hover {
            border-color: #1d2448;
            color: #1d2448;
        }

        .notif-mark-all {
            font-size: 12.5px;
            font-weight: 600;
            color: #1d2448;
            background: none;
            border: 1.5px solid #1d2448;
            border-radius: 7px;
            padding: 7px 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .2s, color .2s;
            font-family: 'Poppins', sans-serif;
        }

        .notif-mark-all:hover {
            background: #1d2448;
            color: #fff;
        }

        .notif-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #1d2448;
            flex: 0 0 auto;
            margin-top: 8px;
        }

        .notif-card.read .notif-dot {
            opacity: 0;
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
    $unreadCount = (int) ($unreadCount ?? 0);
    $totalItems  = (int) ($totalItems ?? (count($feedItems) + count($personalNotifications)));
    $bellUnreadCount = (new \App\Models\NotificationModel())->countUnread(
        (int) session()->get('user_id'),
        false
    );

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
        'move'      => ['icon' => 'fa-exchange-alt',       'color' => '#0e7c66', 'bg' => 'rgba(14,124,102,.12)',  'label' => 'Household Move'],
        'household_move' => ['icon' => 'fa-exchange-alt',  'color' => '#0e7c66', 'bg' => 'rgba(14,124,102,.12)',  'label' => 'Household Move'],
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
                        <span class="notif-count-badge" id="unreadBadge"
                            style="<?= $unreadCount === 0 ? 'display:none;' : '' ?>">
                            <?= $unreadCount ?>
                        </span>
                    </h3>
                    <p id="notifSubtitle">
                        <?= $totalItems ?> total · <?= $unreadCount ?> unread
                    </p>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <button class="notif-mark-all" id="markAllBtn" type="button" onclick="markAllRead()"
                        style="<?= $unreadCount === 0 ? 'display:none;' : '' ?>">
                        <i class="fas fa-check-double"></i> Mark all as read
                    </button>
                    <button class="db-btn db-btn--outline" type="button" onclick="location.reload()"
                        style="display:inline-flex;align-items:center;gap:7px;font-size:12.5px;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- ── Filter tabs ── -->
            <div class="notif-tabs">
                <button class="notif-tab active" onclick="filterNotifs('all',this)">All</button>
                <button class="notif-tab" onclick="filterNotifs('account',this)">Accounts</button>
                <button class="notif-tab" onclick="filterNotifs('clearance',this)">Clearances</button>
                <button class="notif-tab" onclick="filterNotifs('blotter',this)">Blotter</button>
                <button class="notif-tab" onclick="filterNotifs('concern',this)">Concerns</button>
                <button class="notif-tab" onclick="filterNotifs('schedule',this)">Events</button>
                <button class="notif-tab" onclick="filterNotifs('move',this)">Moves</button>
            </div>

            <?php
            $notificationRows = [];
            foreach ($personalNotifications as $notification) {
                $notificationRows[] = [
                    'kind' => 'personal',
                    'sort' => strtotime((string) ($notification['created_at'] ?? '')) ?: 0,
                    'id'   => (int) ($notification['id'] ?? 0),
                    'notification' => $notification,
                ];
            }
            foreach ($feedItems as $item) {
                $notificationRows[] = [
                    'kind' => 'feed',
                    'sort' => strtotime((string) ($item['created_at'] ?? '')) ?: 0,
                    'id'   => (int) ($item['ref_id'] ?? 0),
                    'item' => $item,
                ];
            }
            usort($notificationRows, static function (array $left, array $right): int {
                $byTime = $right['sort'] <=> $left['sort'];

                return $byTime !== 0 ? $byTime : ($right['id'] <=> $left['id']);
            });
            ?>
            <?php if ($notificationRows === []): ?>
                <div class="notif-wrap" id="notifList">
                    <div class="notif-empty">
                        <i class="fas fa-check-double" style="color:#16c79a;"></i>
                        <p>Everything is up to date. No pending items.</p>
                    </div>
                </div>
            <?php else: ?>
            <div class="notif-wrap" id="notifList">
            <?php foreach ($notificationRows as $row): ?>
            <?php if ($row['kind'] === 'personal'):
                $notification = $row['notification'];
            ?>
                    <?php
                        $isUnread = empty($notification['read_at']);
                        $personalType = (string) ($notification['type'] ?? 'personal');
                        $cfg = $typeConfig[$personalType] ?? $typeConfig['personal'];
                        $actionHref = notification_href_for_role($notification['link'] ?? '', (string) $role);
                        $filterType = $personalType === 'household_move' ? 'move' : 'personal';
                    ?>
                        <div class="notif-card <?= $isUnread ? 'unread' : 'read' ?>" data-type="<?= esc($filterType) ?>"
                            data-unread="<?= $isUnread ? '1' : '0' ?>"
                            id="notif-personal-<?= (int) $notification['id'] ?>">
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
                                        <?php if ($isUnread): ?>
                                            <span style="color:#1d2448;font-weight:600;">· Unread</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-card-actions">
                                        <?php if ($isUnread): ?>
                                            <button type="button" class="notif-mark-read-btn"
                                                onclick="markPersonalRead(<?= (int) $notification['id'] ?>)">
                                                <i class="fas fa-check"></i> Mark as read
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= esc($actionHref) ?>" class="notif-action-btn <?= $isUnread ? 'primary' : '' ?>"
                                            onclick="markPersonalRead(<?= (int) $notification['id'] ?>, this.href); return false;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="notif-dot"></div>
                            </div>
                        </div>
            <?php else:
                $item = $row['item'];
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
                        } elseif ($item['type'] === 'move') {
                            $actionHref = ! empty($item['household_no'])
                                ? '/' . $role . '/household/' . rawurlencode((string) $item['household_no'])
                                : '/' . $role . '/moves?status=pending';
                            $actionLabel = 'Review';
                            $isPrimary   = true;
                        }
                    ?>
                        <?php $isUnread = empty($item['is_read']); ?>
                        <div class="notif-card <?= $isUnread ? 'unread' : 'read' ?>"
                            data-type="<?= esc($item['type']) ?>"
                            data-unread="<?= $isUnread ? '1' : '0' ?>"
                            id="notif-feed-<?= esc($item['type']) ?>-<?= esc((string) ($item['ref_id'] ?? '')) ?>">
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
                                        <?php if ($isUnread): ?>
                                            <span style="color:#1d2448;font-weight:600;">· Unread</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-card-actions">
                                        <?php if ($isUnread): ?>
                                            <button type="button" class="notif-mark-read-btn"
                                                onclick="markFeedRead('<?= esc($item['type'], 'js') ?>', '<?= esc((string) ($item['ref_id'] ?? ''), 'js') ?>')">
                                                <i class="fas fa-check"></i> Mark as read
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= esc($actionHref) ?>"
                                            class="notif-action-btn <?= $isPrimary ? 'primary' : '' ?>"
                                            onclick="event.stopPropagation();">
                                            <i class="fas <?= $isPrimary ? 'fa-arrow-right' : 'fa-eye' ?>"></i>
                                            <?= $actionLabel ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="notif-dot"></div>
                            </div>
                        </div>
            <?php endif; ?>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <script>
        const CSRF_NAME = '<?= csrf_token() ?>';
        const CSRF_HASH = '<?= csrf_hash() ?>';
        let unreadCount = <?= (int) $unreadCount ?>;
        let bellUnreadCount = <?= (int) $bellUnreadCount ?>;
        const totalCount = <?= (int) $totalItems ?>;
        const readNotificationUrl = '<?= esc(site_url($role . '/notifications/read/')) ?>';
        const readAllNotificationsUrl = '<?= esc(site_url($role . '/notifications/read-all')) ?>';
        const dismissFeedUrl = '<?= esc(site_url($role . '/notifications/dismiss-feed')) ?>';

        function csrfHeaders() {
            const headers = {'Content-Type': 'application/x-www-form-urlencoded'};
            headers[CSRF_NAME] = CSRF_HASH;
            return headers;
        }

        function csrfBody(extra) {
            const params = new URLSearchParams(extra || {});
            params.set(CSRF_NAME, CSRF_HASH);
            return params.toString();
        }

        function markCardVisual(card) {
            if (!card || card.dataset.unread !== '1') {
                return false;
            }

            card.classList.remove('unread');
            card.classList.add('read');
            card.dataset.unread = '0';

            const unreadLabel = card.querySelector('.notif-meta span[style*="Unread"]');
            if (unreadLabel) {
                unreadLabel.remove();
            }

            const markBtn = card.querySelector('.notif-mark-read-btn');
            if (markBtn) {
                markBtn.remove();
            }

            const viewBtn = card.querySelector('.notif-action-btn.primary');
            if (viewBtn) {
                viewBtn.classList.remove('primary');
            }

            return true;
        }

        function markCardRead(card, affectBell) {
            if (!markCardVisual(card)) {
                return;
            }

            unreadCount = Math.max(0, unreadCount - 1);
            if (affectBell) {
                bellUnreadCount = Math.max(0, bellUnreadCount - 1);
            }
            updateBadge();
        }

        function syncBellUnreadCount(count) {
            bellUnreadCount = Math.max(0, Number(count) || 0);
            window.__bisNotifPollMutedUntil = Date.now() + 120000;
            updateBadge();
        }

        function markPersonalRead(id, link) {
            const card = document.getElementById('notif-personal-' + id);
            if (card && card.dataset.unread === '1') {
                markCardRead(card, true);
                fetch(readNotificationUrl + id, {
                    method: 'POST',
                    headers: csrfHeaders(),
                    body: csrfBody(),
                    credentials: 'same-origin',
                    keepalive: true,
                }).catch(function () {});
            }

            if (link) {
                window.location.assign(link);
            }
        }

        function markFeedRead(type, ref) {
            const card = document.getElementById('notif-feed-' + type + '-' + ref);
            markCardRead(card, false);

            fetch(dismissFeedUrl, {
                method: 'POST',
                headers: csrfHeaders(),
                body: csrfBody({type: type, ref: ref}),
                credentials: 'same-origin',
            }).catch(function () {});
        }

        function markAllRead() {
            const btn = document.getElementById('markAllBtn');
            if (btn) {
                btn.disabled = true;
            }

            fetch(readAllNotificationsUrl, {
                method: 'POST',
                headers: csrfHeaders(),
                body: csrfBody(),
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    document.querySelectorAll('.notif-card.unread').forEach(function (card) {
                        markCardVisual(card);
                    });
                    unreadCount = 0;
                    syncBellUnreadCount(data && typeof data.unread === 'number' ? data.unread : 0);
                })
                .catch(function () {
                    if (btn) {
                        btn.disabled = false;
                    }
                });
        }

        function updateBadge() {
            const badge = document.getElementById('unreadBadge');
            if (badge) {
                badge.textContent = unreadCount;
                badge.style.display = unreadCount > 0 ? '' : 'none';
            }

            const btn = document.getElementById('markAllBtn');
            if (btn) {
                btn.style.display = unreadCount > 0 ? '' : 'none';
            }

            const sub = document.getElementById('notifSubtitle');
            if (sub) {
                sub.textContent = totalCount + ' total · ' + unreadCount + ' unread';
            }

            const topBell = document.getElementById('topbarUnreadCount');
            if (topBell) {
                topBell.textContent = bellUnreadCount > 9 ? '9+' : String(bellUnreadCount);
                topBell.classList.toggle('is-empty', bellUnreadCount <= 0);
                topBell.hidden = bellUnreadCount <= 0;
            }

            const topDot = document.getElementById('topbarNotifDot');
            if (topDot) {
                topDot.hidden = bellUnreadCount <= 0;
            }
        }

        function filterNotifs(type, btn) {
            document.querySelectorAll('.notif-tab').forEach(function (tab) {
                tab.classList.remove('active');
            });
            btn.classList.add('active');
            document.querySelectorAll('.notif-wrap .notif-card, #notifList .notif-card').forEach(function (card) {
                card.style.display = type === 'all' || card.dataset.type === type ? '' : 'none';
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(function (item) {
            item.addEventListener('click', function () {
                document.getElementById('sidebar').classList.remove('open');
            });
        });
    </script>


</body>

</html>