<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .users-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 16px;
        }

        .users-filters .db-search-wrap {
            flex: 1 1 260px;
            margin: 0;
        }

        .users-filters select {
            min-width: 160px;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #d7dce6;
            background: #fff;
            color: #1c2b45;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
        }

        .users-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .users-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d7dce6;
            background: #fff;
            color: #16325c;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
            cursor: pointer;
        }

        .users-tab.is-active {
            background: #16325c;
            border-color: #16325c;
            color: #fff;
        }

        .users-tab-count {
            min-width: 18px;
            padding: 1px 6px;
            background: #eef2f8;
            color: #16325c;
            font-size: 11px;
            text-align: center;
        }

        .users-tab.is-active .users-tab-count {
            background: #e0b32a;
            color: #16325c;
        }

        .users-panel[hidden] {
            display: none !important;
        }

        .users-card {
            background: #fff;
            border: 1px solid #e2e6ee;
        }

        .users-card-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 14px 18px;
            border-bottom: 1px solid #e2e6ee;
            border-top: 3px solid #e0b32a;
        }

        .users-card-head h2 {
            margin: 0;
            font-size: 16px;
            color: #1c2b45;
        }

        .users-card-head p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #6b7689;
        }

        .users-empty {
            text-align: center;
            padding: 40px 16px;
            color: #6b7689;
        }

        .users-name {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .users-sub {
            font-size: 12px;
            color: #6b7689;
        }

        .users-you {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 6px;
            background: #16325c;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .users-role {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            background: #eef2f8;
            color: #16325c;
        }

        .users-role--admin { background: #16325c; color: #fff; }
        .users-role--captain { background: #f8efd0; color: #8a6a10; }
        .users-role--secretary { background: #efe8f8; color: #6d3d91; }
        .users-role--sk { background: #e5f6f2; color: #0f766e; }
        .users-role--council { background: #e8eefc; color: #274690; }
        .users-role--resident { background: #f3f4f8; color: #4b5568; }

        .users-table td { vertical-align: middle; }

        .users-table .db-action-group {
            flex-wrap: nowrap;
        }

        .users-table .db-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .users-detail {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px 14px;
            margin: 0;
        }

        .users-detail dt {
            margin: 0;
            color: #6b7689;
            font-size: 12px;
            font-weight: 600;
        }

        .users-detail dd {
            margin: 0;
            color: #1c2b45;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $active = 'users';
    $pageTitle = 'Users';
    $users = (isset($users) && is_array($users)) ? $users : [];
    $roleCounts = (isset($roleCounts) && is_array($roleCounts)) ? $roleCounts : [];
    $roleFilter = (isset($roleFilter) && is_string($roleFilter)) ? $roleFilter : '';
    $statusFilter = (isset($statusFilter) && is_string($statusFilter)) ? $statusFilter : '';
    $search = (isset($search) && is_string($search)) ? $search : '';
    $totalAccounts = (isset($totalAccounts) && is_numeric($totalAccounts)) ? (int) $totalAccounts : 0;
    $activeAccounts = (isset($activeAccounts) && is_numeric($activeAccounts)) ? (int) $activeAccounts : 0;
    $pendingAccounts = (isset($pendingAccounts) && is_numeric($pendingAccounts)) ? (int) $pendingAccounts : 0;
    $currentUserId = (isset($currentUserId) && is_numeric($currentUserId)) ? (int) $currentUserId : 0;
    $officialAccounts = (int) ($roleCounts['admin'] ?? 0)
        + (int) ($roleCounts['captain'] ?? 0)
        + (int) ($roleCounts['secretary'] ?? 0)
        + (int) ($roleCounts['council'] ?? 0)
        + (int) ($roleCounts['sk'] ?? 0);
    $roleOptions = [
        '' => 'All roles',
        'admin' => 'Admin',
        'captain' => 'Captain',
        'secretary' => 'Secretary',
        'council' => 'Council',
        'sk' => 'SK',
        'resident' => 'Resident',
    ];
    $statusOptions = [
        '' => 'All statuses',
        'active' => 'Active',
        'pending' => 'Pending',
        'unverified' => 'Unverified',
        'rejected' => 'Rejected',
        'deceased' => 'Deceased',
    ];
    $statusClass = [
        'active' => 'db-badge db-badge--approved',
        'pending' => 'db-badge db-badge--pending',
        'rejected' => 'db-badge db-badge--rejected',
        'deceased' => 'db-badge db-badge--rejected',
        'unverified' => 'db-badge',
    ];
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="db-stats">
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,50,92,.12);color:#16325c;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($totalAccounts) ?></span>
                        <span class="db-stat-label">All accounts</span>
                    </div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(14,148,100,.15);color:#0e9464;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($activeAccounts) ?></span>
                        <span class="db-stat-label">Active</span>
                    </div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(255,193,7,.18);color:#b07a00;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($pendingAccounts) ?></span>
                        <span class="db-stat-label">Pending</span>
                    </div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(224,179,42,.2);color:#8a6a10;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($officialAccounts) ?></span>
                        <span class="db-stat-label">Officials</span>
                    </div>
                </div>
            </div>

            <form method="get" class="users-filters" data-live-results="liveResults">
                <div class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search name, username, email, or date..." value="<?= esc($search) ?>">
                </div>
                <select name="status" onchange="this.form.submit()">
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php
            $roleTabs = $roleOptions;
            unset($roleTabs['']);
            $usersByRole = [];
            foreach ($roleTabs as $roleKey => $roleLabel) {
                $usersByRole[$roleKey] = [];
            }
            foreach ($users as $user) {
                if (! is_array($user)) {
                    continue;
                }
                $roleName = (isset($user['role']) && is_string($user['role'])) ? $user['role'] : '';
                if (! isset($usersByRole[$roleName])) {
                    $usersByRole[$roleName] = [];
                    $roleTabs[$roleName] = ucfirst($roleName);
                }
                $usersByRole[$roleName][] = $user;
            }
            $activeRole = ($roleFilter !== '' && isset($usersByRole[$roleFilter])) ? $roleFilter : 'admin';
            ?>
            <div id="liveResults">
                <div class="users-tabs" role="tablist" aria-label="User roles">
                    <?php foreach ($roleTabs as $roleKey => $roleLabel): ?>
                        <button
                            type="button"
                            class="users-tab<?= $activeRole === $roleKey ? ' is-active' : '' ?>"
                            role="tab"
                            data-role="<?= esc($roleKey) ?>"
                            aria-selected="<?= $activeRole === $roleKey ? 'true' : 'false' ?>"
                            aria-controls="users-panel-<?= esc($roleKey) ?>">
                            <?= esc($roleLabel) ?>
                            <span class="users-tab-count"><?= number_format(count($usersByRole[$roleKey] ?? [])) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($roleTabs as $roleKey => $roleLabel):
                    $roleUsers = $usersByRole[$roleKey] ?? [];
                ?>
                    <div
                        class="users-card users-panel"
                        id="users-panel-<?= esc($roleKey) ?>"
                        role="tabpanel"
                        data-role-panel="<?= esc($roleKey) ?>"
                        <?= $activeRole === $roleKey ? '' : 'hidden' ?>>
                        <div class="users-card-head">
                            <div>
                                <h2><?= esc($roleLabel) ?> accounts</h2>
                                <p>Newest <?= esc($roleLabel) ?> accounts are listed first.</p>
                            </div>
                            <strong style="color:#16325c;font-size:13px;"><?= number_format(count($roleUsers)) ?></strong>
                        </div>
                        <?php if ($roleUsers === []): ?>
                            <div class="users-empty">
                                <i class="fas fa-user-slash" style="font-size:32px;color:#c5cdd8;display:block;margin-bottom:10px;"></i>
                                <?= $search !== '' || $statusFilter !== '' ? 'No ' . esc($roleLabel) . ' accounts match these filters.' : 'No ' . esc($roleLabel) . ' accounts yet.' ?>
                            </div>
                        <?php else: ?>
                            <div class="db-table-wrap">
                                <table class="db-table users-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Status</th>
                                            <?php if ($roleKey === 'council'): ?>
                                                <th>Zone</th>
                                            <?php endif; ?>
                                            <th>Household</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($roleUsers as $user):
                                            $first = (isset($user['first_name']) && is_string($user['first_name'])) ? $user['first_name'] : '';
                                            $middle = (isset($user['middle_name']) && is_string($user['middle_name'])) ? $user['middle_name'] : '';
                                            $last = (isset($user['last_name']) && is_string($user['last_name'])) ? $user['last_name'] : '';
                                            $fullName = trim($first . ' ' . $middle . ' ' . $last);
                                            $username = (isset($user['username']) && is_string($user['username'])) ? $user['username'] : '';
                                            $email = (isset($user['email']) && is_string($user['email'])) ? $user['email'] : '';
                                            $contact = (isset($user['contact_number']) && is_string($user['contact_number'])) ? $user['contact_number'] : '';
                                            $statusName = (isset($user['status']) && is_string($user['status'])) ? $user['status'] : '';
                                            $householdNo = (isset($user['household_no']) && is_string($user['household_no'])) ? $user['household_no'] : '';
                                            $zone = (isset($user['council_zone']) && is_string($user['council_zone'])) ? $user['council_zone'] : '';
                                            $createdAt = (isset($user['created_at']) && is_string($user['created_at'])) ? $user['created_at'] : '';
                                            $userId = (isset($user['id']) && is_numeric($user['id'])) ? (int) $user['id'] : 0;
                                            $statusLabel = $statusOptions[$statusName] ?? ucfirst($statusName);
                                            $joinedLabel = notification_time($createdAt !== '' ? $createdAt : null);
                                            $verified = ! empty($user['email_verified']) ? 'Yes' : 'No';
                                            $isSelf = $userId === $currentUserId && $userId > 0;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="users-name">
                                                        <strong>
                                                            <?= $fullName !== '' ? pii($fullName) : '—' ?>
                                                            <?php if ($userId === $currentUserId && $userId > 0): ?>
                                                                <span class="users-you">YOU</span>
                                                            <?php endif; ?>
                                                        </strong>
                                                        <?php if ($email !== ''): ?>
                                                            <span class="users-sub"><?= pii($email) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="users-name">
                                                        <span><?= esc($username) ?></span>
                                                        <?php if ($contact !== ''): ?>
                                                            <span class="users-sub"><?= pii($contact) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><span class="<?= esc($statusClass[$statusName] ?? 'db-badge') ?>"><?= esc($statusLabel) ?></span></td>
                                                <?php if ($roleKey === 'council'): ?>
                                                    <td><?= $zone !== '' ? esc($zone) : '—' ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <?php if ($householdNo !== ''): ?>
                                                        <a href="/admin/household/<?= rawurlencode($householdNo) ?>"><?= esc($householdNo) ?></a>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= esc($joinedLabel) ?></td>
                                                <td>
                                                    <div class="db-action-group">
                                                        <button
                                                            type="button"
                                                            class="db-btn db-btn--outline db-btn--sm js-user-view"
                                                            data-name="<?= esc($fullName !== '' ? $fullName : '—', 'attr') ?>"
                                                            data-username="<?= esc($username !== '' ? $username : '—', 'attr') ?>"
                                                            data-email="<?= esc($email !== '' ? $email : '—', 'attr') ?>"
                                                            data-contact="<?= esc($contact !== '' ? $contact : '—', 'attr') ?>"
                                                            data-role="<?= esc($roleLabel, 'attr') ?>"
                                                            data-status="<?= esc($statusLabel !== '' ? $statusLabel : '—', 'attr') ?>"
                                                            data-household="<?= esc($householdNo !== '' ? $householdNo : '—', 'attr') ?>"
                                                            data-zone="<?= esc($zone !== '' ? $zone : '—', 'attr') ?>"
                                                            data-joined="<?= esc($joinedLabel !== '' ? $joinedLabel : '—', 'attr') ?>"
                                                            data-verified="<?= esc($verified, 'attr') ?>">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <?php if ($isSelf): ?>
                                                            <button type="button" class="db-btn db-btn--danger db-btn--sm" disabled title="You cannot delete your own account">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        <?php else: ?>
                                                            <form method="post" action="/admin/users/delete/<?= $userId ?>" onsubmit="return confirm('Delete <?= esc($fullName !== '' ? $fullName : $username, 'js') ?>? This cannot be undone. Clearance, blotter, and program records linked to this account will also be removed.');">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="db-btn db-btn--danger db-btn--sm">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="db-modal-overlay" id="userViewModal">
                <div class="db-modal" style="max-width:480px;">
                    <div class="db-modal-header">
                        <h3><i class="fas fa-user" style="margin-right:8px;"></i> Account details</h3>
                        <button type="button" class="db-modal-close" id="userViewClose" aria-label="Close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="db-modal-body" style="padding:20px 24px;">
                        <dl class="users-detail">
                            <dt>Name</dt><dd id="userViewName">—</dd>
                            <dt>Role</dt><dd id="userViewRole">—</dd>
                            <dt>Username</dt><dd id="userViewUsername">—</dd>
                            <dt>Email</dt><dd id="userViewEmail">—</dd>
                            <dt>Contact</dt><dd id="userViewContact">—</dd>
                            <dt>Status</dt><dd id="userViewStatus">—</dd>
                            <dt>Email verified</dt><dd id="userViewVerified">—</dd>
                            <dt>Household</dt><dd id="userViewHousehold">—</dd>
                            <dt>Zone</dt><dd id="userViewZone">—</dd>
                            <dt>Joined</dt><dd id="userViewJoined">—</dd>
                        </dl>
                    </div>
                    <div class="db-modal-footer">
                        <button type="button" class="db-btn db-btn--outline" id="userViewDone">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let usersActiveRole = document.querySelector('.users-tab.is-active')?.dataset.role || 'admin';

        function showUsersRole(role) {
            const tabs = Array.from(document.querySelectorAll('.users-tab'));
            const known = tabs.some(function (tab) { return tab.dataset.role === role; });
            if (!known && tabs[0]) {
                role = tabs[0].dataset.role;
            }
            usersActiveRole = role;
            tabs.forEach(function (tab) {
                const selected = tab.dataset.role === role;
                tab.classList.toggle('is-active', selected);
                tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
            document.querySelectorAll('[data-role-panel]').forEach(function (panel) {
                panel.hidden = panel.dataset.rolePanel !== role;
            });
        }

        function setUserDetail(id, value) {
            const node = document.getElementById(id);
            if (node) {
                node.textContent = value || '—';
            }
        }

        function openUserView(button) {
            setUserDetail('userViewName', button.dataset.name);
            setUserDetail('userViewRole', button.dataset.role);
            setUserDetail('userViewUsername', button.dataset.username);
            setUserDetail('userViewEmail', button.dataset.email);
            setUserDetail('userViewContact', button.dataset.contact);
            setUserDetail('userViewStatus', button.dataset.status);
            setUserDetail('userViewVerified', button.dataset.verified);
            setUserDetail('userViewHousehold', button.dataset.household);
            setUserDetail('userViewZone', button.dataset.zone);
            setUserDetail('userViewJoined', button.dataset.joined);
            const modal = document.getElementById('userViewModal');
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeUserView() {
            const modal = document.getElementById('userViewModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        document.addEventListener('click', function (event) {
            const viewButton = event.target.closest('.js-user-view');
            if (viewButton) {
                openUserView(viewButton);
                return;
            }

            if (event.target.closest('#userViewClose') || event.target.closest('#userViewDone')) {
                closeUserView();
                return;
            }

            const modal = document.getElementById('userViewModal');
            if (modal && event.target === modal) {
                closeUserView();
                return;
            }

            const tab = event.target.closest('.users-tab');
            if (!tab) {
                return;
            }
            showUsersRole(tab.dataset.role);
        });

        document.addEventListener('bis-live-results', function (event) {
            if (event.detail && event.detail.id === 'liveResults') {
                showUsersRole(usersActiveRole);
            }
        });
    </script>
</body>

</html>
