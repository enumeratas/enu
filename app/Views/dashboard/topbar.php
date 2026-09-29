<?php

/**
 * ============================================================
 * DASHBOARD TOPBAR + BIS AI CHATBOT
 * ============================================================
 */
$notificationRole = (string) (session()->get('role') ?? 'resident');
$notificationUrl = site_url($notificationRole . '/notifications');
$notificationUserId = (int) session()->get('user_id');
$notificationUnreadCount = $notificationUserId > 0
    ? (new \App\Models\NotificationModel())->countUnread($notificationUserId)
    : 0;
$actionToast = null;
$actionToastType = 'success';
foreach (
    [
        ['key' => 'success',             'type' => 'success'],
        ['key' => 'error',               'type' => 'error'],
        ['key' => 'warning',             'type' => 'warning'],
        ['key' => 'info',                'type' => 'info'],
        ['key' => 'cal_success',         'type' => 'success'],
        ['key' => 'cal_error',           'type' => 'error'],
        ['key' => 'concern_error',       'type' => 'error'],
        ['key' => 'concern_form_error',  'type' => 'error'],
        ['key' => 'blotter_error',       'type' => 'error'],
    ] as $toastKey
) {
    $message = session()->getFlashdata($toastKey['key']);
    if ($message !== null && $message !== '') {
        $actionToast = strip_tags(is_array($message) ? implode(' ', $message) : (string) $message);
        $actionToastType = $toastKey['type'];
        break;
    }
}
?>
<?php if ($actionToast !== null): ?>
    <div class="db-alert db-alert--<?= esc($actionToastType) ?> bis-action-toast" role="status" aria-live="polite">
        <i class="fas <?= $actionToastType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
        <span><?= esc($actionToast) ?></span>
        <button type="button" class="bis-action-toast__close" aria-label="Close message">&times;</button>
    </div>
<?php endif; ?>
<!-- ============================================================
     DASHBOARD TOPBAR
     ============================================================ -->

<header class="db-topbar">

    <button
        class="db-menu-toggle db-mobile-menu-toggle"
        type="button"
        onclick="toggleDashboardSidebar()"
        aria-label="Open menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Page Title -->
    <div class="db-topbar-title">

        <h1>
            <?= $pageTitle ?? 'Dashboard' ?>
        </h1>

        <span>
            <?= date('l, F j, Y') ?>
        </span>

    </div>

    <?php $searchRole = strtolower((string) (session()->get('role') ?? 'resident')); ?>
    <div class="db-global-search">
        <i class="fas fa-search" aria-hidden="true"></i>
        <input
            id="globalSearchInput"
            type="search"
            placeholder="Search pages, people, and records"
            autocomplete="off"
            aria-label="Search the system"
            aria-controls="globalSearchResults"
            aria-expanded="false">
        <div id="globalSearchResults" class="db-global-search-results" hidden></div>
    </div>


    <!-- Right Side -->
    <div class="db-topbar-right">

        <!-- ====================================================
             NOTIFICATIONS
             ==================================================== -->

        <button
            class="db-notif-btn"
            onclick="window.location.href='<?= esc($notificationUrl) ?>'"
            title="Notifications"
            aria-label="Notifications">

            <i class="fas fa-bell"></i>


            <span
                class="db-notif-dot"
                id="topbarNotifDot"
                style="display:none;">
            </span>


            <span
                id="topbarUnreadCount"
                class="db-notif-count"
                style="display:<?= $notificationUnreadCount > 0 ? 'flex' : 'none' ?>;">
                <?= $notificationUnreadCount > 9 ? '9+' : $notificationUnreadCount ?>
            </span>

        </button>


        <!-- ====================================================
             AVATAR
             ==================================================== -->

        <div
            class="db-avatar"
            onclick="window.location.href='/<?= esc((string)(session()->get('role') ?? 'resident')) ?>/<?= session()->get('role') === 'resident' ? 'profile' : 'settings' ?>'"
            style="cursor:pointer;">

            <?php

            $avatarFile =
                session()->get('avatar');

            if (
                $avatarFile &&
                file_exists(
                    FCPATH .
                        'uploads/avatars/' .
                        $avatarFile
                )
            ):

            ?>

                <img
                    src="/uploads/avatars/<?= esc($avatarFile) ?>"
                    alt="Avatar"
                    style="
                        width:100%;
                        height:100%;
                        object-fit:cover;
                        border-radius:50%;
                    ">

            <?php else: ?>

                <i class="fas fa-user"></i>

            <?php endif; ?>

        </div>


        <!-- ====================================================
             USERNAME
             ==================================================== -->

        <span class="db-username">

            <?= esc(
                (string)(
                    session()->get('username')
                    ?? 'User'
                )
            ) ?>

        </span>

        <?php $logoutUrl = '/logout?role=' . rawurlencode(strtolower((string) (session()->get('role') ?? 'resident'))); ?>
        <a class="db-topbar-logout" href="<?= esc($logoutUrl) ?>" onclick="return window.openLogoutModal ? openLogoutModal(event, this.href) : true;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>

    </div>

</header>

<script>
(function () {
    const input = document.getElementById('globalSearchInput');
    const panel = document.getElementById('globalSearchResults');
    if (!input || !panel) return;

    const endpoint = <?= json_encode('/' . $searchRole . '/search') ?>;
    let timer = null;
    let requestId = 0;
    let active = -1;

    function text(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function closePanel() {
        panel.hidden = true;
        panel.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        active = -1;
    }

    function items() {
        return Array.from(panel.querySelectorAll('.db-search-item'));
    }

    function markActive(index) {
        const links = items();
        links.forEach(function (link, i) {
            link.classList.toggle('is-active', i === index);
        });
        active = index;
        if (links[index]) links[index].scrollIntoView({ block: 'nearest' });
    }

    function render(rows) {
        if (!rows.length) {
            panel.innerHTML = '<div class="db-search-empty">No matches</div>';
            panel.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            active = -1;
            return;
        }

        let html = '';
        let group = '';
        rows.forEach(function (row) {
            if (row.group !== group) {
                group = row.group || '';
                html += '<div class="db-search-group">' + text(group) + '</div>';
            }
            let title = '';
            if (row.label) title += '<span>' + text(row.label) + '</span>';
            if (row.image) {
                title += '<img class="pii-text" alt="" draggable="false" src="' + text(row.image) + '">';
            }
            html += '<a class="db-search-item" href="' + text(row.href) + '">'
                + '<span class="db-search-title">' + title + '</span>'
                + (row.hint ? '<span class="db-search-hint">' + text(row.hint) + '</span>' : '')
                + '</a>';
        });
        panel.innerHTML = html;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        active = -1;
    }

    input.addEventListener('input', function () {
        const query = input.value.trim();
        window.clearTimeout(timer);
        if (query.length < 2) {
            closePanel();
            return;
        }
        if (!navigator.onLine) {
            panel.innerHTML = '<div class="db-search-empty">Search needs a connection.</div>';
            panel.hidden = false;
            return;
        }
        timer = window.setTimeout(function () {
            const current = ++requestId;
            fetch(endpoint + '?q=' + encodeURIComponent(query), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            }).then(function (response) {
                if (!response.ok) throw new Error('search failed');
                return response.json();
            }).then(function (data) {
                if (current !== requestId) return;
                render(Array.isArray(data.results) ? data.results : []);
            }).catch(function () {
                if (current !== requestId) return;
                panel.innerHTML = '<div class="db-search-empty">Search needs a connection.</div>';
                panel.hidden = false;
            });
        }, 250);
    });

    input.addEventListener('keydown', function (event) {
        const links = items();
        if (event.key === 'Escape') {
            closePanel();
            return;
        }
        if (!links.length || panel.hidden) return;
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            markActive(Math.min(active + 1, links.length - 1));
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            markActive(Math.max(active - 1, 0));
        } else if (event.key === 'Enter' && active >= 0 && links[active]) {
            event.preventDefault();
            window.location.href = links[active].href;
        }
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.db-global-search')) closePanel();
    });
})();
</script>

<script>
    (function() {
        const collapsed = localStorage.getItem('bisSidebarCollapsed') === '1';
        if (collapsed && window.innerWidth > 900) {
            document.body.classList.add('db-sidebar-collapsed');
        }

        const initialToggle = document.querySelector('.db-sidebar-toggle');
        if (initialToggle && collapsed && window.innerWidth > 900) {
            initialToggle.setAttribute('aria-label', 'Show sidebar');
            initialToggle.title = 'Show sidebar';
        }

        window.toggleDashboardSidebar = function() {
            if (window.innerWidth <= 900) {
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('sidebarBackdrop');
                const isOpen = sidebar?.classList.toggle('open') || false;
                backdrop?.classList.toggle('open', isOpen);
                document.body.classList.toggle('db-menu-open', isOpen);
                return;
            }

            const collapsedNow = document.body.classList.toggle('db-sidebar-collapsed');
            localStorage.setItem('bisSidebarCollapsed', collapsedNow ? '1' : '0');
            const toggle = document.querySelector('.db-sidebar-toggle');
            if (toggle) {
                toggle.setAttribute('aria-label', collapsedNow ? 'Show sidebar' : 'Hide sidebar');
                toggle.title = collapsedNow ? 'Show sidebar' : 'Hide sidebar';
            }
        };

        window.closeDashboardSidebar = function() {
            document.getElementById('sidebar')?.classList.remove('open');
            document.getElementById('sidebarBackdrop')?.classList.remove('open');
            document.body.classList.remove('db-menu-open');
        };

        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) {
                window.closeDashboardSidebar();
            }
        });
    })();
</script>


<!-- ============================================================
     RESIDENT NOTIFICATIONS
     ============================================================ -->

<?php if (session()->get('role') === 'resident'): ?>

    <style>
        .db-notif-count {
            position: absolute;

            top: 2px;

            right: 2px;

            min-width: 17px;

            height: 17px;

            background: #c0392b;

            color: #fff;

            font-size: 10px;

            font-weight: 700;

            border-radius: 100px;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 0 4px;

            pointer-events: none;
        }
    </style>


    <script>
        (function() {

            function pollUnread() {

                fetch(
                        '<?= esc(site_url('resident/notifications/poll')) ?>', {
                            credentials: 'same-origin'
                        }
                    )

                    .then(
                        function(r) {
                            return r.json();
                        }
                    )

                    .then(
                        function(data) {

                            const count =
                                data.unread || 0;


                            const dot =
                                document.getElementById(
                                    'topbarNotifDot'
                                );


                            const badge =
                                document.getElementById(
                                    'topbarUnreadCount'
                                );


                            if (dot) {

                                dot.style.display =
                                    count > 0 ?
                                    '' :
                                    'none';

                            }


                            if (badge) {

                                badge.textContent =
                                    count > 9 ?
                                    '9+' :
                                    count;


                                badge.style.display =
                                    count > 0 ?
                                    'flex' :
                                    'none';

                            }

                        }
                    )

                    .catch(
                        function() {}
                    );

            }


            pollUnread();


            setInterval(
                pollUnread,
                30000
            );

        })();
    </script>


    <!-- ============================================================
     SECRETARY / CAPTAIN NOTIFICATIONS
     ============================================================ -->

<?php elseif (
    in_array(
        session()->get('role'),
        ['secretary', 'captain', 'council', 'sk']
    )
): ?>

    <style>
        .db-notif-count {
            position: absolute;

            top: 2px;

            right: 2px;

            min-width: 17px;

            height: 17px;

            background: #e6a800;

            color: #fff;

            font-size: 10px;

            font-weight: 700;

            border-radius: 100px;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 0 4px;

            pointer-events: none;
        }
    </style>


    <script>
        (function() {

            function pollAdmin() {

                fetch(
                        '<?= esc(site_url($notificationRole . '/notifications/poll')) ?>', {
                            credentials: 'same-origin'
                        }
                    )

                    .then(
                        function(r) {
                            return r.json();
                        }
                    )

                    .then(
                        function(data) {

                            const count =
                                data.unread || 0;


                            const dot =
                                document.getElementById(
                                    'topbarNotifDot'
                                );


                            const badge =
                                document.getElementById(
                                    'topbarUnreadCount'
                                );


                            if (dot) {

                                dot.style.display =
                                    count > 0 ?
                                    '' :
                                    'none';

                            }


                            if (badge) {

                                badge.textContent =
                                    count > 9 ?
                                    '9+' :
                                    count;


                                badge.style.display =
                                    count > 0 ?
                                    'flex' :
                                    'none';

                            }

                        }
                    )

                    .catch(
                        function() {}
                    );

            }


            pollAdmin();


            setInterval(
                pollAdmin,
                60000
            );

        })();
    </script>
<?php endif; ?>

<script>
    window.BIS_USER_ID = <?= (int) (session()->get('user_id') ?? 0) ?>;
    window.BIS_USER_ROLE = <?= json_encode((string) (session()->get('role') ?? '')) ?>;
</script>
<script src="/js/resident-offline.js?v=4"></script>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js?v=8', {
                updateViaCache: 'none'
            }).catch(function() {
                // Ignore registration failures; the app still works online.
            });
        });
    }
</script>



<script>
    (function() {
        function sortTableRows(table) {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (rows.length < 2) return;

            rows.sort(function(a, b) {
                const aText = (a.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
                const bText = (b.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
                return aText.localeCompare(bText);
            });

            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        }

        function applyLiveTableFilter(input) {
            const form = input.closest('form');
            const table = form ? form.querySelector('table') : null;
            if (!table) return;

            sortTableRows(table);

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const query = (input.value || '').trim().toLowerCase();

            rows.forEach(function(row) {
                const text = (row.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
                const matches = !query || text.includes(query);
                row.style.display = matches ? '' : 'none';
            });
        }

        function wireLiveTableFilters() {
            const searchInputs = document.querySelectorAll('input[type="text"][name="search"], input[type="search"], input[placeholder*="Search"], input[placeholder*="search"]');

            searchInputs.forEach(function(input) {
                if (input.dataset.bisTableLiveFilterBound === '1') {
                    return;
                }

                input.dataset.bisTableLiveFilterBound = '1';
                input.addEventListener('input', function() {
                    applyLiveTableFilter(input);
                });
                input.addEventListener('focus', function() {
                    applyLiveTableFilter(input);
                });
                const table = input.closest('form') ? input.closest('form').querySelector('table') : null;
                if (table) sortTableRows(table);
                applyLiveTableFilter(input);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            wireLiveTableFilters();
        });
    })();
</script>

<script>
    (function() {
        const CURRENT_USER_ID = Number(<?php echo json_encode((int) (session()->get('user_id') ?? 0), JSON_THROW_ON_ERROR); ?>);
        const STORAGE_KEY = 'bis_offline_session_snapshot_' + (CURRENT_USER_ID || 'guest');
        const sessionData = <?php echo json_encode(\App\Controllers\AuthController::sessionSnapshot(session()->get() ?? []), JSON_THROW_ON_ERROR); ?>;

        if (sessionData && sessionData.user_id) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                ...sessionData,
                expires_at: new Date(Date.now() + (7 * 24 * 60 * 60 * 1000)).toISOString()
            }));
        }
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.js-local-notification-time[datetime]').forEach(function(time) {
            const date = new Date(time.getAttribute('datetime'));
            if (Number.isNaN(date.getTime())) return;
            time.textContent = date.toLocaleString(undefined, {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        });

        const noticeSelectors = [
            '.db-alert--success',
            '.db-alert--error',
            '.db-alert--danger',
            '.alert--success',
            '.alert--error',
            '.ca-alert--success',
            '.ca-alert--error',
            '.st-alert--success',
            '.st-alert--error',
            '.cr-alert--error'
        ];
        const noticeCandidates = Array.from(document.querySelectorAll(noticeSelectors.join(',')));
        const seenNotices = new Set();
        const notices = noticeCandidates.filter(function(notice) {
            const textCopy = notice.cloneNode(true);
            textCopy.querySelectorAll('button').forEach(function(button) {
                button.remove();
            });
            const message = (textCopy.textContent || '').replace(/\s+/g, ' ').trim();
            const type = notice.className.match(/(?:db-alert|alert|ca-alert|st-alert|cr-alert)--([a-z]+)/);
            const key = (type ? type[1] : '') + '|' + message;
            if (!message || seenNotices.has(key)) {
                notice.remove();
                return false;
            }
            seenNotices.add(key);
            return true;
        });
        if (!notices.length) return;

        const container = document.createElement('div');
        container.className = 'bis-toast-container';
        document.body.appendChild(container);

        notices.forEach(function(notice) {
            notice.classList.add('bis-toast');
            container.appendChild(notice);
            const closeButton = notice.querySelector('.bis-action-toast__close');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    notice.classList.add('is-hiding');
                    window.setTimeout(function() {
                        notice.remove();
                    }, 250);
                });
            }
            window.setTimeout(function() {
                notice.classList.add('is-hiding');
                window.setTimeout(function() {
                    notice.remove();
                }, 250);
            }, 5000);
        });
    });
</script>