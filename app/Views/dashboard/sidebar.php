<?php
$role   = $role   ?? (session()->get('role') ?: 'resident');
$active = $active ?? 'dashboard';

$menus = [
    'captain' => [
        ['divider' => 'Overview'],
        ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard',                        'key' => 'dashboard',        'href' => '/captain/dashboard'],
        ['icon' => 'fas fa-calendar-alt',   'label' => 'Calendar',                         'key' => 'calendar',         'href' => '/captain/calendar'],
        ['icon' => 'fas fa-bullhorn',       'label' => 'Brgy Activities',                  'key' => 'activities',       'href' => '/captain/activities'],
        ['divider' => 'Census'],
        ['icon' => 'fas fa-users',           'label' => 'Census Records',                  'key' => 'census',           'href' => '/captain/census'],
        ['icon' => 'fas fa-search',          'label' => 'Household Finder',                'key' => 'household_finder', 'href' => '/captain/household-finder'],
        ['icon' => 'fas fa-exchange-alt',    'label' => 'Household Moves',                 'key' => 'moves',            'href' => '/captain/moves'],
        ['icon' => 'fas fa-user-slash',      'label' => 'Deceased Accounts',               'key' => 'deceased_accounts','href' => '/captain/deceased-accounts'],
        ['icon' => 'fas fa-bell',            'label' => 'Census Update Drive',             'key' => 'census_updates',   'href' => '/captain/census-updates'],
        ['divider' => 'Services'],
        ['icon' => 'fas fa-file-alt',        'label' => 'Clearance',                       'key' => 'clearance',        'href' => '/captain/clearance'],
        ['icon' => 'fas fa-book',            'label' => 'Blotter Reports',                 'key' => 'blotter',          'href' => '/captain/blotter'],
        ['icon' => 'fas fa-chart-bar',       'label' => 'Reports',                         'key' => 'reports',          'href' => '/captain/reports'],
        ['icon' => 'fas fa-comments',        'label' => 'Appointment / Concerns Management', 'key' => 'concerns',        'href' => '/captain/concerns'],
        ['icon' => 'fas fa-headset',         'label' => 'Customer Service',                'key' => 'customer_service', 'href' => '/captain/customer-service'],
        ['divider' => 'Account'],
        ['icon' => 'fas fa-user-plus',       'label' => 'Appoint Secretary',               'key' => 'create_account',   'href' => '/captain/create-account'],
        ['icon' => 'fas fa-cog',             'label' => 'Settings',                        'key' => 'settings',         'href' => '/captain/settings'],
        ['icon' => 'fas fa-sign-out-alt',    'label' => 'Logout',                          'key' => 'logout',          'href' => '/logout'],
    ],
    'secretary' => [
        ['divider' => 'Overview'],
        ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard',                        'key' => 'dashboard',        'href' => '/secretary/dashboard'],
        ['icon' => 'fas fa-calendar-alt',   'label' => 'Calendar',                         'key' => 'calendar',         'href' => '/secretary/calendar'],
        ['icon' => 'fas fa-bullhorn',       'label' => 'Brgy Activities',                  'key' => 'activities',       'href' => '/secretary/activities'],
        ['divider' => 'Census'],
        ['icon' => 'fas fa-users',           'label' => 'Census Records',                  'key' => 'census',           'href' => '/secretary/census'],
        ['icon' => 'fas fa-search',          'label' => 'Household Finder',                'key' => 'household_finder', 'href' => '/secretary/household-finder'],
        ['icon' => 'fas fa-exchange-alt',    'label' => 'Household Moves',                 'key' => 'moves',            'href' => '/secretary/moves'],
        ['icon' => 'fas fa-user-slash',      'label' => 'Deceased Accounts',               'key' => 'deceased_accounts','href' => '/secretary/deceased-accounts'],
        ['icon' => 'fas fa-bell',            'label' => 'Census Update Drive',             'key' => 'census_updates',   'href' => '/secretary/census-updates'],
        ['icon' => 'fas fa-id-card',         'label' => 'Residents',                       'key' => 'residents',        'href' => '/secretary/residents'],
        ['divider' => 'Services'],
        ['icon' => 'fas fa-file-alt',        'label' => 'Clearance',                       'key' => 'clearance',        'href' => '/secretary/clearance'],
        ['icon' => 'fas fa-file-invoice-dollar',       'label' => 'Document Fees',                   'key' => 'barangay_settings', 'href' => '/secretary/barangay-settings'],
        ['icon' => 'fas fa-book',            'label' => 'Blotter Reports',                 'key' => 'blotter',          'href' => '/secretary/blotter'],
        ['icon' => 'fas fa-chart-bar',       'label' => 'Reports',                         'key' => 'reports',          'href' => '/secretary/reports'],
        ['icon' => 'fas fa-comments',        'label' => 'Appointment / Concerns Management', 'key' => 'concerns',        'href' => '/secretary/concerns'],
        ['icon' => 'fas fa-headset',         'label' => 'Customer Service',                'key' => 'customer_service', 'href' => '/secretary/customer-service'],
        ['icon' => 'fas fa-ticket-alt',      'label' => 'Support Tickets',                 'key' => 'support_tickets',  'href' => '/secretary/support-tickets'],
        ['divider' => 'Account'],
        ['icon' => 'fas fa-user-plus',       'label' => 'Create Resident Account',         'key' => 'create_account',   'href' => '/secretary/create-account'],
        ['icon' => 'fas fa-sign-out-alt',    'label' => 'Logout',                          'key' => 'logout',          'href' => '/logout'],
    ],
    'admin' => [
        ['divider' => 'Overview'],
        ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard',                        'key' => 'dashboard',        'href' => '/admin/dashboard'],
        ['icon' => 'fas fa-calendar-alt',   'label' => 'Calendar',                         'key' => 'calendar',         'href' => '/admin/calendar'],
        ['icon' => 'fas fa-bullhorn',       'label' => 'Brgy Activities',                  'key' => 'activities',       'href' => '/admin/activities'],
        ['divider' => 'Census'],
        ['icon' => 'fas fa-users',           'label' => 'Census Records',                  'key' => 'census',           'href' => '/admin/census'],
        ['icon' => 'fas fa-search',          'label' => 'Household Finder',                'key' => 'household_finder', 'href' => '/admin/household-finder'],
        ['icon' => 'fas fa-id-card',         'label' => 'Residents',                       'key' => 'residents',        'href' => '/admin/residents'],
        ['icon' => 'fas fa-exchange-alt',    'label' => 'Household Moves',                 'key' => 'moves',            'href' => '/admin/moves'],
        ['icon' => 'fas fa-user-slash',      'label' => 'Deceased Accounts',               'key' => 'deceased_accounts','href' => '/admin/deceased-accounts'],
        ['icon' => 'fas fa-bell',            'label' => 'Census Update Drive',             'key' => 'census_updates',   'href' => '/admin/census-updates'],
        ['divider' => 'Services'],
        ['icon' => 'fas fa-file-alt',        'label' => 'Clearance',                       'key' => 'clearance',        'href' => '/admin/clearance'],
        ['icon' => 'fas fa-file-invoice-dollar',       'label' => 'Document Fees',                   'key' => 'barangay_settings', 'href' => '/admin/barangay-settings'],
        ['icon' => 'fas fa-book',            'label' => 'Blotter Reports',                 'key' => 'blotter',          'href' => '/admin/blotter'],
        ['icon' => 'fas fa-chart-bar',       'label' => 'Reports',                         'key' => 'reports',          'href' => '/admin/reports'],
        ['icon' => 'fas fa-comments',        'label' => 'Appointment / Concerns Management', 'key' => 'concerns',      'href' => '/admin/concerns'],
        ['icon' => 'fas fa-headset',         'label' => 'Customer Service',                'key' => 'customer_service', 'href' => '/admin/customer-service'],
        ['icon' => 'fas fa-ticket-alt',      'label' => 'Support Tickets',                 'key' => 'support_tickets',  'href' => '/admin/support-tickets'],
        ['divider' => 'Youth'],
        ['icon' => 'fas fa-id-card',         'label' => 'SK Profiling',                    'key' => 'profiling',        'href' => '/admin/profiling'],
        ['icon' => 'fas fa-calendar-alt',    'label' => 'Programs & Events',               'key' => 'programs',         'href' => '/admin/programs'],
        ['icon' => 'fas fa-chart-bar',       'label' => 'SK Reports',                      'key' => 'sk_reports',       'href' => '/admin/sk-reports'],
        ['icon' => 'fas fa-star',            'label' => 'SK Activities',                   'key' => 'sk_activities',    'href' => '/admin/sk-activities'],
        ['divider' => 'Account'],
        ['icon' => 'fas fa-users-cog',       'label' => 'Users',                           'key' => 'users',            'href' => '/admin/users'],
        ['icon' => 'fas fa-user-plus',       'label' => 'Create Official Account',         'key' => 'create_account',   'href' => '/admin/create-account'],
        ['icon' => 'fas fa-cog',             'label' => 'Settings',                        'key' => 'settings',         'href' => '/admin/settings'],
        ['icon' => 'fas fa-sign-out-alt',    'label' => 'Logout',                          'key' => 'logout',           'href' => '/logout'],
    ],
    'resident' => [
        ['divider' => 'Overview'],
        ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard',       'key' => 'dashboard',      'href' => '/resident/dashboard'],
        ['icon' => 'fas fa-bullhorn',        'label' => 'Brgy Activities', 'key' => 'activities',     'href' => '/resident/activities'],
        ['divider' => 'SK'],
        ['icon' => 'fas fa-star',            'label' => 'SK Activities',   'key' => 'sk_activities',  'href' => '/resident/sk-activities'],
        ['icon' => 'fas fa-star',            'label' => 'SK Profiling',   'key' => 'sk_profiling',  'href' => '/resident/sk-profiling'],
        ['divider' => 'Services'],
        ['icon' => 'fas fa-file-alt',        'label' => 'Document Request', 'key' => 'document_request', 'href' => '/resident/clearance'],
        ['icon' => 'fas fa-comments',        'label' => 'Appointment / Concern', 'key' => 'concerns',  'href' => '/resident/concerns'],
        ['icon' => 'fas fa-comment-dots',    'label' => 'Chatbot', 'key' => 'chatbot', 'href' => '/resident/chatbot'],
        ['divider' => 'Account'],
        ['icon' => 'fas fa-sign-out-alt',    'label' => 'Logout',          'key' => 'logout',         'href' => '/logout'],
    ],
    'sk' => [
        ['divider' => 'Overview'],
        ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard',         'key' => 'dashboard',      'href' => '/sk/dashboard'],
        ['divider' => 'Youth'],
        ['icon' => 'fas fa-id-card',         'label' => 'SK Profiling',     'key' => 'profiling',      'href' => '/sk/profiling'],
        ['icon' => 'fas fa-bullhorn',        'label' => 'Brgy Activities',  'key' => 'activities',     'href' => '/sk/activities'],
        ['icon' => 'fas fa-calendar-alt',    'label' => 'Programs & Events', 'key' => 'programs',      'href' => '/sk/programs'],
        ['icon' => 'fas fa-chart-bar',       'label' => 'Reports',           'key' => 'reports',       'href' => '/sk/reports'],
        ['divider' => 'Services'],
        ['icon' => 'fas fa-file-alt',        'label' => 'Document Request',  'key' => 'clearance',     'href' => '/sk/clearance'],
        ['icon' => 'fas fa-comments',        'label' => 'Appointment / Concern', 'key' => 'concerns',  'href' => '/sk/concerns'],
        ['divider' => 'Account'],
        ['icon' => 'fas fa-sign-out-alt',    'label' => 'Logout',            'key' => 'logout',        'href' => '/logout'],
    ],
    'council' => [
        ['divider' => 'Overview'],
        ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard',      'key' => 'dashboard', 'href' => '/council/dashboard'],
        ['icon' => 'fas fa-bullhorn',       'label' => 'Brgy Activities', 'key' => 'activities', 'href' => '/council/activities'],
        ['icon' => 'fas fa-file-alt',       'label' => 'Document Request', 'key' => 'clearance', 'href' => '/council/clearance'],
        ['divider' => 'Census'],
        ['icon' => 'fas fa-users',          'label' => 'Census Records', 'key' => 'census',    'href' => '/council/census'],
        ['icon' => 'fas fa-search',         'label' => 'Household Finder', 'key' => 'household_finder', 'href' => '/council/household-finder'],
        ['divider' => 'Youth'],
        ['icon' => 'fas fa-calendar-alt',    'label' => 'Programs & Events', 'key' => 'programs', 'href' => '/council/programs'],
        ['icon' => 'fas fa-star',           'label' => 'SK Profiling',   'key' => 'sk_profiling', 'href' => '/council/sk-profiling'],
        ['divider' => 'Account'],
        ['icon' => 'fas fa-bell',           'label' => 'Notifications',  'key' => 'notifications', 'href' => '/council/notifications'],
        ['icon' => 'fas fa-cog',            'label' => 'Settings',       'key' => 'settings',  'href' => '/council/settings'],
        ['icon' => 'fas fa-sign-out-alt',   'label' => 'Logout',          'key' => 'logout',        'href' => '/logout'],
    ],
];

if (strtolower((string) (session()->get('role') ?? '')) === 'admin') {
    $role = 'admin';
}
$roleMenu = $menus[strtolower($role)] ?? $menus['resident'];
$roleMenu = array_values(array_filter(
    $roleMenu,
    static fn(array $item): bool => ($item['key'] ?? '') !== 'logout'
));
$visibleMenu = [];
foreach ($roleMenu as $index => $item) {
    if (! empty($item['divider'])) {
        $next = $roleMenu[$index + 1] ?? null;
        if ($next === null || ! empty($next['divider'])) {
            continue;
        }
    }
    $visibleMenu[] = $item;
}
$roleMenu = $visibleMenu;
$requestPath = trim((string) service('request')->getUri()->getPath(), '/');
$requestPath = preg_replace('#^index\.php/?#', '', $requestPath) ?? $requestPath;
$pathParts = $requestPath === '' ? [] : explode('/', $requestPath);
$section = strtolower((string) ($pathParts[1] ?? ''));
$menuKeys = [];
$activeKey = '';
$bestLength = -1;
foreach ($roleMenu as $item) {
    $itemKey = (string) ($item['key'] ?? '');
    $itemHref = (string) ($item['href'] ?? '');
    if ($itemKey === '' || $itemKey === 'logout' || $itemHref === '' || ! empty($item['divider'])) {
        continue;
    }
    $menuKeys[$itemKey] = true;
    $hrefPath = trim($itemHref, '/');
    if ($requestPath === $hrefPath || str_starts_with($requestPath, $hrefPath . '/')) {
        if (strlen($hrefPath) > $bestLength) {
            $bestLength = strlen($hrefPath);
            $activeKey = $itemKey;
        }
    }
}
if ($activeKey === '' && $section !== '') {
    $sectionKeys = [
        'concern' => 'concerns',
        'household' => strtolower((string) $role) === 'sk' ? 'profiling' : 'census',
        'households' => 'census',
        'officials-history' => 'create_account',
        'document-templates' => 'clearance',
        'pending-accounts' => 'settings',
    ];
    if (strtolower((string) $role) === 'resident' && $section === 'clearance') {
        $sectionKeys['clearance'] = 'document_request';
    }
    $candidate = $sectionKeys[$section] ?? str_replace('-', '_', $section);
    if (isset($menuKeys[$candidate])) {
        $activeKey = $candidate;
    }
}
if ($activeKey === '' && isset($menuKeys[(string) $active])) {
    $activeKey = (string) $active;
}
$active = $activeKey;
$roleLabel = ucfirst($role);
$roleIcons = ['admin' => 'fas fa-user-shield', 'captain' => 'fas fa-user-tie', 'secretary' => 'fas fa-user-edit', 'resident' => 'fas fa-users', 'sk' => 'fas fa-star', 'council' => 'fas fa-users'];
$roleIcon = $roleIcons[strtolower($role)] ?? 'fas fa-user';
?>

<link rel="stylesheet" href="/dashboard-theme.css?v=20261004b">
<script src="/pii-protect.js?v=20260927b" defer></script>

<aside class="db-sidebar" id="sidebar">
    <button
        class="db-menu-toggle db-sidebar-toggle"
        type="button"
        onclick="toggleDashboardSidebar()"
        aria-label="Hide sidebar"
        title="Hide sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="db-sidebar-brand">
        <div class="db-brand-logo">
            <img src="/bacolod.png" alt="Logo" width="26" height="26">
        </div>
        <div class="db-brand-text">
            <span class="db-brand-name">BISync</span>
            <span class="db-brand-role"><i class="<?= $roleIcon ?>"></i> <?= $roleLabel ?></span>
        </div>
    </div>

    <nav class="db-nav">
        <?php foreach ($roleMenu as $item): ?>
            <?php if (! empty($item['divider'])): ?>
                <div class="db-nav-section" aria-hidden="true">
                    <span class="db-nav-section-title"><?= esc($item['divider']) ?></span>
                    <span class="db-nav-section-line"></span>
                </div>
            <?php else: ?>
                <a href="<?= $item['href'] ?>" class="db-nav-item <?= $active === $item['key'] ? 'active' : '' ?>" <?= $active === $item['key'] ? 'aria-current="page"' : '' ?> <?= $item['key'] === 'logout' ? ' onclick="return openLogoutModal(event, this.href);"' : '' ?>>
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- <a href="/logout" class="db-logout">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>-->
</aside>
<div class="db-sidebar-backdrop" id="sidebarBackdrop" onclick="closeDashboardSidebar()" aria-hidden="true"></div>

<div class="db-modal-overlay" id="logoutModal" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle" aria-hidden="true">
    <div class="db-modal" role="document">
        <div class="db-modal-header">
            <h3 id="logoutModalTitle"><i class="fas fa-sign-out-alt"></i> Log out</h3>
            <button type="button" class="db-modal-close" onclick="closeLogoutModal()" aria-label="Close logout confirmation">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="db-modal-body">
            <p style="margin:0;color:#6b7280;font-size:14px;line-height:1.6;">Are you sure you want to log out of your BISync account?</p>
        </div>
        <div class="db-modal-footer">
            <button type="button" class="db-btn db-btn--outline" onclick="closeLogoutModal()">Cancel</button>
            <a id="confirmLogoutButton" href="/logout" class="db-btn db-btn--danger"><i class="fas fa-sign-out-alt"></i> Log out</a>
        </div>
    </div>
</div>

<script>
    (function() {
        const current = document.querySelector('.db-nav-item.active');
        const nav = document.querySelector('.db-nav');
        if (current && nav) {
            const itemTop = current.offsetTop;
            const itemBottom = itemTop + current.offsetHeight;
            if (itemTop < nav.scrollTop || itemBottom > nav.scrollTop + nav.clientHeight) {
                nav.scrollTop = Math.max(0, itemTop - 48);
            }
        }

        const modal = document.getElementById('logoutModal');
        const confirmButton = document.getElementById('confirmLogoutButton');

        window.openLogoutModal = function(event, logoutUrl) {
            event.preventDefault();
            confirmButton.href = logoutUrl;
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            confirmButton.focus();
            return false;
        };

        window.closeLogoutModal = function() {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        };

        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                window.closeLogoutModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('active')) {
                window.closeLogoutModal();
            }
        });
    }());
</script>