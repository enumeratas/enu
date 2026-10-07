<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Status badges ── */
        .rl-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 100px;
            white-space: nowrap;
        }

        .rl-badge--active {
            background: #e6f9f1;
            color: #0e9464;
            border: 1px solid #b2e8d2;
        }

        .rl-badge--pending {
            background: #fff8e6;
            color: #b07a00;
            border: 1px solid #ffe08a;
        }

        .rl-badge--none {
            background: #f3f4f8;
            color: #9aa0b4;
            border: 1px solid #e2e5ef;
        }

        .rl-badge--head {
            background: #eef0fb;
            color: #1d2448;
            border: 1px solid #d0d8f5;
        }

        .rl-badge--member {
            background: #f3f4f8;
            color: #6b7280;
            border: 1px solid #e2e5ef;
        }

        .rl-badge--minor {
            background: #fff0f3;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }

        .rl-table th,
        .rl-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .rl-stats {
            align-items: stretch;
        }

        .rl-stats .db-stat-card {
            min-height: 80px;
        }

        .rl-empty {
            text-align: center;
            padding: 40px;
            color: #9aa0b4;
        }

        .rl-empty i {
            font-size: 28px;
            display: block;
            margin-bottom: 10px;
        }

        /* ── Gear button ── */
        .rl-gear-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1.5px solid #e2e5ef;
            background: #f8f9ff;
            color: #6b7280;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .18s;
        }

        .rl-gear-btn:hover {
            background: #1d2448;
            border-color: #1d2448;
            color: #fff;
            transform: rotate(30deg);
        }

        /* ── Drawer overlay ── */
        .rl-drawer-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 17, 30, .45);
            backdrop-filter: blur(2px);
            z-index: 1100;
        }

        .rl-drawer-overlay.open {
            display: block;
        }

        /* ── Drawer panel ── */
        .rl-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 400px;
            max-width: 100vw;
            background: #fff;
            box-shadow: -8px 0 40px rgba(15, 17, 30, .14);
            z-index: 1200;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            transform: translateX(100%);
            transition: transform .28s cubic-bezier(.4, 0, .2, 1);
        }

        .rl-drawer.open {
            transform: translateX(0);
        }

        /* ── Drawer header ── */
        .rl-drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 22px 16px;
            border-bottom: 1px solid #f0f2f8;
            flex-shrink: 0;
            gap: 14px;
        }

        .rl-drawer-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .rl-drawer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rl-drawer-name {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a1d2e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rl-drawer-meta {
            font-size: 12px;
            color: #9aa0b4;
            margin-top: 2px;
        }

        .rl-drawer-close {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1.5px solid #e2e5ef;
            background: #f8f9ff;
            color: #6b7280;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .15s;
        }

        .rl-drawer-close:hover {
            background: #c0392b;
            border-color: #c0392b;
            color: #fff;
        }

        /* ── Pills row ── */
        .rl-drawer-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 14px 22px;
        }

        .rl-drawer-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
            background: #f0f2f8;
            color: #4a5068;
            border: 1px solid #e2e5ef;
        }

        .rl-drawer-pill--active {
            background: #e6f9f1;
            color: #0e9464;
            border-color: #b2e8d2;
        }

        .rl-drawer-pill--pending {
            background: #fff8e6;
            color: #b07a00;
            border-color: #ffe08a;
        }

        .rl-drawer-pill--none {
            background: #f3f4f8;
            color: #9aa0b4;
            border-color: #e2e5ef;
        }

        /* ── Divider ── */
        .rl-drawer-divider {
            height: 1px;
            background: #f0f2f8;
            margin: 0;
            flex-shrink: 0;
        }

        /* ── Sections ── */
        .rl-drawer-section {
            padding: 20px 22px;
            flex-shrink: 0;
        }

        .rl-drawer-section-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .9px;
            text-transform: uppercase;
            color: #9aa0b4;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .rl-drawer-section-title i {
            font-size: 12px;
            color: #b0b6cc;
        }

        .rl-drawer-section-title--danger,
        .rl-drawer-section-title--danger i {
            color: #c0392b;
        }

        /* ── Field ── */
        .rl-drawer-field-wrap {
            margin-bottom: 14px;
        }

        .rl-drawer-label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .rl-drawer-current {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a1d2e;
            background: #f5f7ff;
            border: 1.5px solid #e2e5ef;
            border-radius: 9px;
            padding: 9px 13px;
        }

        .rl-drawer-input-wrap {
            position: relative;
        }

        .rl-drawer-input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b6cc;
            font-size: 12px;
            pointer-events: none;
        }

        .rl-drawer-input {
            width: 100%;
            padding: 10px 40px 10px 35px;
            border: 1.5px solid #e2e5ef;
            border-radius: 9px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            box-sizing: border-box;
            transition: border-color .18s, box-shadow .18s;
        }

        .rl-drawer-input:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, .08);
        }

        .rl-drawer-input::placeholder {
            color: #c0c6d8;
        }

        .rl-drawer-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b0b6cc;
            cursor: pointer;
            font-size: 12px;
            padding: 4px;
            line-height: 1;
            transition: color .15s;
        }

        .rl-drawer-eye:hover {
            color: #1d2448;
        }

        .rl-drawer-hint {
            display: block;
            font-size: 11px;
            color: #b0b6cc;
            margin-top: 5px;
        }

        .rl-drawer-hint--warn {
            color: #b07a00;
            background: #fff8e6;
            border: 1px solid #ffe08a;
            border-radius: 6px;
            padding: 6px 10px;
            gap: 6px;
        }

        .rl-drawer-hint--success {
            color: #0e7a55;
            background: #e6f9f1;
            border: 1px solid #b2e8d2;
            border-radius: 6px;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
        }

        /* OTP code input */
        .rl-drawer-input--otp {
            letter-spacing: 6px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            padding-left: 14px !important;
        }

        /* OTP info banner */
        .rl-drawer-otp-info {
            background: #f0fbf8;
            border: 1px solid #b2e8d2;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12.5px;
            color: #0e7a55;
            line-height: 1.6;
        }

        /* ── Buttons ── */
        .rl-drawer-btn {
            width: 100%;
            padding: 11px 16px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
            transition: opacity .18s, transform .14s;
        }

        .rl-drawer-btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .rl-drawer-btn--primary {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
        }

        .rl-drawer-btn--warn {
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: #fff;
        }

        .rl-drawer-btn--teal {
            background: linear-gradient(135deg, #16a085, #1abc9c);
            color: #fff;
        }

        .rl-drawer-btn--ghost {
            background: #f0f2f8;
            color: #4a5068;
            border: 1.5px solid #e2e5ef;
        }

        .rl-drawer-btn--ghost:hover {
            background: #e2e5ef;
            opacity: 1;
        }

        .rl-drawer-btn--danger {
            background: #fff;
            color: #c0392b;
            border: 1.5px solid #fad4d4;
        }

        .rl-drawer-btn--danger:hover {
            background: #c0392b;
            color: #fff;
            border-color: #c0392b;
            opacity: 1;
        }

        /* ── Danger zone ── */
        .rl-drawer-danger-zone {
            background: #fff8f8;
            border-top: 1px solid #fde8e8;
        }

        .rl-drawer-danger-text {
            font-size: 12.5px;
            color: #7a3030;
            line-height: 1.6;
            margin: 0 0 14px;
        }

        @media (max-width: 480px) {
            .rl-drawer {
                width: 100vw;
            }
        }

        /* ── Pending approvals panel ── */
        .pa-panel {
            background: #fffbf0;
            border: 1.5px solid #ffe08a;
            border-radius: 12px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .pa-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            cursor: pointer;
            user-select: none;
            gap: 12px;
        }

        .pa-panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #7a4200;
        }

        .pa-panel-title i {
            color: #e67e22;
        }

        .pa-panel-count {
            background: #e67e22;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            border-radius: 100px;
            padding: 2px 8px;
        }

        .pa-panel-chevron {
            color: #b07a00;
            transition: transform .2s;
        }

        .pa-panel-chevron.open {
            transform: rotate(180deg);
        }

        .pa-panel-body {
            border-top: 1px solid #ffe08a;
        }

        /* ── Confirmation modals ── */
        .pa-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 17, 30, 0.55);
            backdrop-filter: blur(3px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .pa-overlay.active {
            display: flex;
        }

        .pa-modal {
            background: #fff;
            border-radius: 18px;
            width: 100%;
            max-width: 420px;
            margin: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
            overflow: hidden;
            animation: pa-pop .18s ease;
        }

        @keyframes pa-pop {
            from {
                transform: scale(.94);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .pa-modal-header {
            padding: 24px 24px 0;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .pa-modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .pa-modal-icon--approve {
            background: rgba(22, 199, 154, .12);
            color: #16c79a;
        }

        .pa-modal-icon--reject {
            background: rgba(192, 57, 43, .10);
            color: #c0392b;
        }

        .pa-modal-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0 0 4px;
        }

        .pa-modal-sub {
            font-size: 13px;
            color: #9aa0b4;
            margin: 0;
            line-height: 1.5;
        }

        .pa-modal-body {
            padding: 18px 24px 20px;
        }

        .pa-user-card {
            background: #f8f9ff;
            border: 1px solid #e2e5ef;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pa-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pa-user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1d2e;
        }

        .pa-user-meta {
            font-size: 12px;
            color: #9aa0b4;
            margin-top: 2px;
        }

        .pa-reject-note {
            margin-top: 12px;
            background: #fff8f0;
            border: 1px solid #fde8c8;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12.5px;
            color: #7a4200;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .pa-reject-note i {
            color: #e67e22;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .pa-modal-footer {
            padding: 0 24px 24px;
            display: flex;
            gap: 10px;
        }

        .pa-btn {
            flex: 1;
            padding: 11px 16px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: opacity .2s, transform .15s;
        }

        .pa-btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .pa-btn--cancel {
            background: #f0f2f8;
            color: #4a5068;
        }

        .pa-btn--approve {
            background: #16c79a;
            color: #fff;
        }

        .pa-btn--reject {
            background: #c0392b;
            color: #fff;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = 'secretary';
    $active    = 'residents';
    $pageTitle = 'Residents';
    $hideGlobalSearch = true;
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- ── Pending Account Approvals panel ────────────────────────── -->
            <?php $pendingUsers = $pendingUsers ?? []; ?>
            <?php if (! empty($pendingUsers)): ?>
                <div class="pa-panel">
                    <div class="pa-panel-header" onclick="togglePending()">
                        <div class="pa-panel-title">
                            <i class="fas fa-user-clock"></i>
                            Pending Account Approvals
                            <span class="pa-panel-count"><?= count($pendingUsers) ?></span>
                        </div>
                        <i class="fas fa-chevron-down pa-panel-chevron open" id="paChevron"></i>
                    </div>
                    <div class="pa-panel-body" id="paBody">
                        <div class="db-table-wrap" style="margin:0;border-radius:0;border:none;box-shadow:none;">
                            <table class="db-table" style="border-radius:0;">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingUsers as $u):
                                        $displayName = trim($u['first_name'] . ' ' . $u['last_name']);
                                        $initial     = strtoupper(substr($u['first_name'], 0, 1));
                                    ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:10px;">
                                                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#1d2448,#2e3a6e);color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                        <?= $initial ?>
                                                    </div>
                                                    <span style="font-weight:600;color:#1a1d2e;"><?= esc($displayName) ?></span>
                                                </div>
                                            </td>
                                            <td><span style="color:#6b7280;">@<?= esc($u['username']) ?></span></td>
                                            <td><?= esc($u['email']) ?></td>
                                            <td>
                                                <span class="db-badge db-badge--<?= $u['role'] === 'sk' ? 'info' : 'default' ?>">
                                                    <?= strtoupper(esc($u['role'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                            <td>
                                                <div class="db-action-group">
                                                    <button type="button" class="db-btn db-btn--success db-btn--sm"
                                                        onclick="openApprove(<?= $u['id'] ?>, '<?= esc($displayName, 'js') ?>', '<?= esc($u['username'], 'js') ?>', '<?= strtoupper(esc($u['role'], 'js')) ?>')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="db-btn db-btn--danger db-btn--sm"
                                                        onclick="openReject(<?= $u['id'] ?>, '<?= esc($displayName, 'js') ?>', '<?= esc($u['username'], 'js') ?>', '<?= strtoupper(esc($u['role'], 'js')) ?>')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── Stats ──────────────────────────────────────────────────── -->
            <div class="db-stats rl-stats" style="margin-bottom:24px;">
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,199,154,0.15);color:#16c79a;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($totalPop) ?></span>
                        <span class="db-stat-label">Total Population</span>
                    </div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(14,148,100,0.15);color:#0e9464;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($activeAccts) ?></span>
                        <span class="db-stat-label">Active Accounts</span>
                    </div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(255,193,7,0.15);color:#b07a00;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($pendingAccts) ?></span>
                        <span class="db-stat-label">Pending Accounts</span>
                    </div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(192,57,43,0.15);color:#c0392b;">
                        <i class="fas fa-child"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($totalMinors) ?></span>
                        <span class="db-stat-label">Minors (under 18)</span>
                    </div>
                </div>
            </div>

            <!-- ── Filter toolbar ─────────────────────────────────────────── -->
            <form method="get" action="" id="filterForm" data-live-results="liveResults">
                <div class="db-toolbar" style="margin-bottom:16px;">
                    <div class="db-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" data-live-query autocomplete="off"
                            placeholder="Search by name, household #, or birth date..."
                            value="<?= esc($search ?? '') ?>">
                    </div>
                    <div class="db-toolbar-actions">
                        <select name="zone" class="db-filter-select" onchange="this.form.submit()" style="min-width:120px;">
                            <option value="">All Zones</option>
                            <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'] as $z): ?>
                                <option <?= ($filterZone ?? '') === $z ? 'selected' : '' ?>><?= $z ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select name="account" class="db-filter-select" onchange="this.form.submit()" style="min-width:160px;">
                            <option value="">All Account Status</option>
                            <option value="active" <?= ($filterAcct ?? '') === 'active'  ? 'selected' : '' ?>>Active Account</option>
                            <option value="pending" <?= ($filterAcct ?? '') === 'pending' ? 'selected' : '' ?>>Pending / Unverified</option>
                            <option value="none" <?= ($filterAcct ?? '') === 'none'    ? 'selected' : '' ?>>No Account</option>
                        </select>

                        <select name="age_group" class="db-filter-select" onchange="this.form.submit()" style="min-width:130px;">
                            <option value="">All Ages</option>
                            <option value="minor" <?= ($filterAge ?? '') === 'minor' ? 'selected' : '' ?>>Minor (under 18)</option>
                            <option value="adult" <?= ($filterAge ?? '') === 'adult' ? 'selected' : '' ?>>Adult (18+)</option>
                        </select>

                        <button type="button" class="db-btn db-btn--outline" onclick="selectAllCensusAuth()">
                            <i class="fas fa-check-square"></i> Select All
                        </button>
                        <button type="button" class="db-btn db-btn--primary" onclick="submitBulkCensusAuth()">
                            <i class="fas fa-envelope"></i> Send Update Authorization
                        </button>

                        <?php if (($search ?? '') !== '' || ($householdNo ?? '') !== '' || ($filterZone ?? '') !== '' || ($filterAcct ?? '') !== '' || ($filterAge ?? '') !== ''): ?>
                            <a href="/secretary/residents" class="db-btn db-btn--outline">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <form id="bulkCensusAuthForm" method="post" action="/secretary/resident/send-census-update" style="display:none;">
                <?= csrf_field() ?>
            </form>

            <div id="liveResults">
            <!-- ── Residents table ───────────────────────────────────────── -->
            <div class="db-table-wrap">
                <table class="db-table rl-table">
                    <thead>
                        <tr>
                            <th style="width:38px;"><input type="checkbox" id="authSelectAll" title="Select all" onclick="toggleAllCensusAuth(this.checked)"></th>
                            <th>Name</th>
                            <th>Household #</th>
                            <th>Relationship</th>
                            <th>Zone</th>
                            <th>Minor</th>
                            <th>Account Status</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($residents ?? [])): ?>
                            <tr>
                                <td colspan="9" class="rl-empty">
                                    <i class="fas fa-search"></i>
                                    No residents match the current filters.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $cutoff = (new DateTime('today'))->modify('-18 years');

                            foreach ($residents as $r):
                                $fullName = esc(strtoupper($r['last_name'])) . ', ' . esc(ucwords(strtolower($r['first_name'])));
                                if (! empty($r['middle_name'])) $fullName .= ' ' . esc(strtoupper($r['middle_name'][0])) . '.';
                                if (! empty($r['suffix']))      $fullName .= ' ' . esc($r['suffix']);
                                $initial = strtoupper($r['first_name'][0] ?? '?');

                                $dob     = ! empty($r['date_of_birth']) ? new DateTime($r['date_of_birth']) : null;
                                $isMinor = $dob ? ($dob > $cutoff) : null;

                                $acctStatus = $r['account_status'] ?? null;
                                $isHead     = ($r['relationship'] === 'Household Head');
                            ?>
                                <tr>
                                    <td style="text-align:center;">
                                        <?php if (! empty($r['user_id'])): ?>
                                            <input type="checkbox" class="census-auth-select" value="<?= (int) $r['user_id'] ?>" aria-label="Select resident for census update authorization">
                                        <?php else: ?>
                                            <span style="color:#d0d4df;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="db-resident-name">
                                            <div class="db-avatar-sm" style="<?= $isHead ? '' : 'background:#6b7280;width:28px;height:28px;font-size:11px;' ?>">
                                                <?= $initial ?>
                                            </div>
                                            <span style="font-weight:<?= $isHead ? '600' : '400' ?>;"><?= $fullName ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isHead): ?>
                                            <a href="/secretary/household/<?= esc($r['household_no']) ?>"
                                                style="font-weight:700;color:#1d2448;text-decoration:none;">
                                                <?= esc($r['household_no']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#9aa0b4;font-size:12px;padding-left:6px;">
                                                └ <?= esc($r['household_no']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="rl-badge <?= $isHead ? 'rl-badge--head' : 'rl-badge--member' ?>">
                                            <?= $isHead
                                                ? '<i class="fas fa-home"></i> Head'
                                                : '<i class="fas fa-user"></i> ' . esc(ucfirst($r['relationship'])) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($r['zone'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($isMinor === null): ?>
                                            <span style="color:#9aa0b4;font-size:12px;">—</span>
                                        <?php elseif ($isMinor): ?>
                                            <span class="rl-badge rl-badge--minor"><i class="fas fa-child"></i> Minor</span>
                                        <?php else: ?>
                                            <span style="color:#9aa0b4;font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($acctStatus === 'active'): ?>
                                            <span class="rl-badge rl-badge--active"><i class="fas fa-check-circle"></i> Active</span>
                                        <?php elseif ($acctStatus === 'pending'): ?>
                                            <span class="rl-badge rl-badge--pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                                        <?php elseif ($acctStatus === 'unverified'): ?>
                                            <span class="rl-badge rl-badge--pending"><i class="fas fa-envelope"></i> Unverified</span>
                                        <?php elseif ($acctStatus === 'rejected'): ?>
                                            <span class="rl-badge" style="background:#fde8e8;color:#c0392b;border:1px solid #f5c6cb;"><i class="fas fa-times-circle"></i> Rejected</span>
                                        <?php else: ?>
                                            <span class="rl-badge rl-badge--none"><i class="fas fa-user-slash"></i> No Account</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:#6b7280;font-size:12.5px;">
                                        <?= $r['username'] ? esc($r['username']) : '<span style="color:#d0d4df;">—</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($r['user_id']): ?>
                                            <div class="db-action-group" style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                                <button type="button" class="db-btn db-btn--sm db-btn--outline" style="padding:7px 9px;" title="Send authorization email" onclick="queueSingleCensusAuth(<?= (int)$r['user_id'] ?>)">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                                <button type="button"
                                                    class="rl-gear-btn"
                                                    title="Manage Account"
                                                    onclick="openDrawer(
                                                        <?= (int)$r['user_id'] ?>,
                                                        '<?= esc($fullName, 'js') ?>',
                                                        '<?= esc($r['username'] ?? '', 'js') ?>',
                                                        '<?= esc($r['email'] ?? '', 'js') ?>',
                                                        '<?= esc($acctStatus ?? '', 'js') ?>',
                                                        '<?= esc($r['zone'] ?? '', 'js') ?>',
                                                        '<?= esc($r['household_no'] ?? '', 'js') ?>'
                                                    )">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#d0d4df;font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ─────────────────────────────────────────────── -->
            <?php
            $totalPages = (int) ceil($total / $perPage);
            $start      = $total > 0 ? ($currentPage - 1) * $perPage + 1 : 0;
            $end        = min($currentPage * $perPage, $total);
            $qs = http_build_query(array_filter([
                'search'    => $search     ?? '',
                'household_no' => $householdNo ?? '',
                'zone'      => $filterZone ?? '',
                'account'   => $filterAcct ?? '',
                'age_group' => $filterAge  ?? '',
            ], fn($v) => $v !== ''));
            $qs = $qs ? '&' . $qs : '';
            ?>
            <?php if ($total > 0): ?>
                <div class="db-pagination">
                    <span class="db-page-info">
                        Showing <?= $start ?>–<?= $end ?> of <?= number_format($total) ?> resident<?= $total !== 1 ? 's' : '' ?>
                    </span>
                    <div class="db-page-btns">
                        <a href="?page=<?= max(1, $currentPage - 1) ?><?= $qs ?>"
                            class="db-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php
                        $rangeStart = max(1, $currentPage - 3);
                        $rangeEnd   = min($totalPages, $currentPage + 3);
                        if ($rangeStart > 1): ?>
                            <a href="?page=1<?= $qs ?>" class="db-page-btn">1</a>
                            <?php if ($rangeStart > 2): ?><span class="db-page-btn" style="cursor:default;">…</span><?php endif; ?>
                        <?php endif; ?>
                        <?php for ($p = $rangeStart; $p <= $rangeEnd; $p++): ?>
                            <a href="?page=<?= $p ?><?= $qs ?>"
                                class="db-page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($rangeEnd < $totalPages): ?>
                            <?php if ($rangeEnd < $totalPages - 1): ?><span class="db-page-btn" style="cursor:default;">…</span><?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $qs ?>" class="db-page-btn"><?= $totalPages ?></a>
                        <?php endif; ?>
                        <a href="?page=<?= min($totalPages, $currentPage + 1) ?><?= $qs ?>"
                            class="db-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            </div>

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <!-- ── Approve modal ──────────────────────────────────────────────────── -->
    <div class="pa-overlay" id="approveModal">
        <div class="pa-modal">
            <div class="pa-modal-header">
                <div class="pa-modal-icon pa-modal-icon--approve"><i class="fas fa-user-check"></i></div>
                <div>
                    <div class="pa-modal-title">Approve Account</div>
                    <p class="pa-modal-sub">This account will be activated and the user can log in immediately.</p>
                </div>
            </div>
            <div class="pa-modal-body">
                <div class="pa-user-card">
                    <div class="pa-user-avatar" id="approveInitial">?</div>
                    <div>
                        <div class="pa-user-name" id="approveName">—</div>
                        <div class="pa-user-meta" id="approveMeta">—</div>
                    </div>
                </div>
            </div>
            <div class="pa-modal-footer">
                <button class="pa-btn pa-btn--cancel" onclick="closeModal('approveModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form id="approveForm" method="post" style="flex:1;display:flex;">
                    <?= csrf_field() ?>
                    <button type="submit" class="pa-btn pa-btn--approve" style="flex:1;">
                        <i class="fas fa-check"></i> Yes, Approve
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Reject modal ───────────────────────────────────────────────────── -->
    <div class="pa-overlay" id="rejectModal">
        <div class="pa-modal">
            <div class="pa-modal-header">
                <div class="pa-modal-icon pa-modal-icon--reject"><i class="fas fa-user-times"></i></div>
                <div>
                    <div class="pa-modal-title">Reject Account</div>
                    <p class="pa-modal-sub">This registration will be declined.</p>
                </div>
            </div>
            <div class="pa-modal-body">
                <div class="pa-user-card">
                    <div class="pa-user-avatar" id="rejectInitial">?</div>
                    <div>
                        <div class="pa-user-name" id="rejectName">—</div>
                        <div class="pa-user-meta" id="rejectMeta">—</div>
                    </div>
                </div>
                <div class="pa-reject-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>The user will not be able to log in. This action can be reviewed later if needed.</span>
                </div>
            </div>
            <div class="pa-modal-footer">
                <button class="pa-btn pa-btn--cancel" onclick="closeModal('rejectModal')">
                    <i class="fas fa-arrow-left"></i> Cancel
                </button>
                <form id="rejectForm" method="post" style="flex:1;display:flex;">
                    <?= csrf_field() ?>
                    <button type="submit" class="pa-btn pa-btn--reject" style="flex:1;">
                        <i class="fas fa-times"></i> Yes, Reject
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ── Pending panel toggle ──────────────────────────────────────────
        function togglePending() {
            const body = document.getElementById('paBody');
            const chevron = document.getElementById('paChevron');
            if (!body) return;
            const hidden = body.style.display === 'none';
            body.style.display = hidden ? '' : 'none';
            chevron.classList.toggle('open', hidden);
        }

        // ── Approve / Reject modals ───────────────────────────────────────
        function openApprove(id, name, username, role) {
            document.getElementById('approveName').textContent = name;
            document.getElementById('approveMeta').textContent = '@' + username + ' · ' + role;
            document.getElementById('approveInitial').textContent = name.charAt(0).toUpperCase();
            document.getElementById('approveForm').action = '/secretary/approve-account/' + id;
            document.getElementById('approveModal').classList.add('active');
        }

        function openReject(id, name, username, role) {
            document.getElementById('rejectName').textContent = name;
            document.getElementById('rejectMeta').textContent = '@' + username + ' · ' + role;
            document.getElementById('rejectInitial').textContent = name.charAt(0).toUpperCase();
            document.getElementById('rejectForm').action = '/secretary/reject-account/' + id;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.querySelectorAll('.pa-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });

        // ── Drawer ────────────────────────────────────────────────────────

        const STATUS_PILL_CLASS = {
            active: 'rl-drawer-pill--active',
            pending: 'rl-drawer-pill--pending',
            unverified: 'rl-drawer-pill--pending',
            rejected: 'rl-drawer-pill--none',
        };

        const STATUS_LABEL = {
            active: 'Active',
            pending: 'Pending',
            unverified: 'Unverified',
            rejected: 'Rejected',
        };

        function openDrawer(userId, name, username, email, status, zone, hh) {
            // Header
            document.getElementById('dr-avatar').textContent = name.charAt(0).toUpperCase();
            document.getElementById('dr-name').textContent = name;
            document.getElementById('dr-meta').textContent = email || '—';

            // Pills
            document.getElementById('dr-zone').textContent = zone || '—';
            document.getElementById('dr-hh').textContent = hh || '—';

            const statusPill = document.getElementById('dr-status-pill');
            statusPill.className = 'rl-drawer-pill ' + (STATUS_PILL_CLASS[status] || 'rl-drawer-pill--none');
            document.getElementById('dr-status').textContent = STATUS_LABEL[status] || 'No Account';

            // Username section
            document.getElementById('dr-current-username').textContent = username ? '@' + username : '(none)';
            document.getElementById('dr-new-username').value = username || '';

            // Email section — populate current email and reset to step A
            document.getElementById('dr-current-email').textContent = email || '(none)';
            document.getElementById('dr-new-email').value = '';
            drResetEmailStep();

            // Clear password fields
            document.getElementById('dr-pw-new').value = '';
            document.getElementById('dr-pw-confirm').value = '';
            document.getElementById('dr-pw-mismatch').style.display = 'none';

            // Wire form actions
            document.getElementById('dr-username-form').action = '/secretary/change-username/' + userId;
            document.getElementById('dr-password-form').action = '/secretary/reset-password/' + userId;
            document.getElementById('dr-delete-form').action = '/secretary/delete-account/' + userId;

            // Store userId for email fetch calls
            document.getElementById('accountDrawer').dataset.userId = userId;

            // Open drawer
            document.getElementById('drawerOverlay').classList.add('open');
            document.getElementById('accountDrawer').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            document.getElementById('drawerOverlay').classList.remove('open');
            document.getElementById('accountDrawer').classList.remove('open');
            document.body.style.overflow = '';
        }

        // ── Email change — two-step fetch flow ────────────────────────────

        function drResetEmailStep() {
            document.getElementById('dr-email-step-a').style.display = '';
            document.getElementById('dr-email-step-b').style.display = 'none';
            document.getElementById('dr-email-step-a-err').style.display = 'none';
            document.getElementById('dr-email-step-b-err').style.display = 'none';
            document.getElementById('dr-email-success').style.display = 'none';
            if (document.getElementById('dr-email-otp')) {
                document.getElementById('dr-email-otp').value = '';
            }
        }

        function drSetEmailBusy(btn, busy) {
            btn.disabled = busy;
            btn.style.opacity = busy ? '.6' : '1';
        }

        async function drSendEmailOtp(isResend = false) {
            const userId = document.getElementById('accountDrawer').dataset.userId;
            const emailVal = document.getElementById('dr-new-email').value.trim();
            const btn = document.getElementById('dr-send-otp-btn');
            const errWrap = document.getElementById('dr-email-step-a-err');
            const errText = document.getElementById('dr-email-step-a-err-text');

            if (!isResend && !emailVal) {
                errText.textContent = 'Please enter a new email address.';
                errWrap.style.display = 'flex';
                return;
            }

            drSetEmailBusy(btn, true);
            errWrap.style.display = 'none';

            try {
                const fd = new FormData();
                fd.append('new_email', isResend ?
                    document.getElementById('dr-otp-info-text').dataset.email :
                    emailVal);

                // Grab CSRF token from any existing form in the page
                const csrfInput = document.querySelector('input[name^="csrf_"]');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);

                const res = await fetch('/secretary/change-email/' + userId, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();

                if (!data.success) {
                    errText.textContent = data.message || 'Failed to send code.';
                    errWrap.style.display = 'flex';
                    drSetEmailBusy(btn, false);
                    return;
                }

                // Move to step B
                const infoEl = document.getElementById('dr-otp-info-text');
                const sentTo = isResend ? infoEl.dataset.email : emailVal;
                infoEl.dataset.email = sentTo;
                infoEl.textContent = '✉ A 6-digit code was sent to ' + sentTo + '. Enter it below to confirm.';

                document.getElementById('dr-email-step-a').style.display = 'none';
                document.getElementById('dr-email-step-b').style.display = '';
                document.getElementById('dr-email-step-b-err').style.display = 'none';
                document.getElementById('dr-email-success').style.display = 'none';
                document.getElementById('dr-email-otp').value = '';
                document.getElementById('dr-email-otp').focus();

            } catch (e) {
                errText.textContent = 'Network error. Please try again.';
                errWrap.style.display = 'flex';
            }

            drSetEmailBusy(btn, false);
        }

        async function drVerifyEmailOtp() {
            const userId = document.getElementById('accountDrawer').dataset.userId;
            const otp = document.getElementById('dr-email-otp').value.trim();
            const errWrap = document.getElementById('dr-email-step-b-err');
            const errText = document.getElementById('dr-email-step-b-err-text');
            const sucWrap = document.getElementById('dr-email-success');
            const sucText = document.getElementById('dr-email-success-text');

            errWrap.style.display = 'none';
            sucWrap.style.display = 'none';

            if (!otp || otp.length !== 6) {
                errText.textContent = 'Please enter the 6-digit code.';
                errWrap.style.display = 'flex';
                return;
            }

            try {
                const fd = new FormData();
                fd.append('otp', otp);

                const csrfInput = document.querySelector('input[name^="csrf_"]');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);

                const res = await fetch('/secretary/verify-email-otp/' + userId, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();

                if (!data.success) {
                    errText.textContent = data.message || 'Verification failed.';
                    errWrap.style.display = 'flex';
                    return;
                }

                // Success — update UI
                sucText.textContent = data.message;
                sucWrap.style.display = 'flex';

                // Update the header meta and "current email" display
                document.getElementById('dr-meta').textContent = data.new_email;
                document.getElementById('dr-current-email').textContent = data.new_email;

                // Reset back to step A after a short delay
                setTimeout(drResetEmailStep, 2200);

            } catch (e) {
                errText.textContent = 'Network error. Please try again.';
                errWrap.style.display = 'flex';
            }
        }

        function toggleDrawerPw(inputId, iconId) {
            const inp = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inp.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function validatePwDrawer() {
            const p1 = document.getElementById('dr-pw-new').value;
            const p2 = document.getElementById('dr-pw-confirm').value;
            const hint = document.getElementById('dr-pw-mismatch');
            if (p1 !== p2) {
                hint.style.display = 'flex';
                return false;
            }
            hint.style.display = 'none';
            return true;
        }

        function confirmDrawerDelete() {
            const name = document.getElementById('dr-name').textContent;
            document.getElementById('del-name').textContent = name;
            document.getElementById('del-form').action =
                document.getElementById('dr-delete-form').action;
            document.getElementById('deleteModal').classList.add('active');
        }

        function toggleAllCensusAuth(checked) {
            document.querySelectorAll('.census-auth-select').forEach(cb => cb.checked = checked);
            document.getElementById('authSelectAll').checked = checked;
        }

        function selectAllCensusAuth() {
            toggleAllCensusAuth(true);
        }

        function queueSingleCensusAuth(userId) {
            const form = document.getElementById('bulkCensusAuthForm');
            form.innerHTML = '<input type="hidden" name="csrf_test_name" value="' + (document.querySelector('input[name^="csrf_"]')?.value || '') + '"><input type="hidden" name="user_ids[]" value="' + userId + '">';
            form.submit();
        }

        function submitBulkCensusAuth() {
            const form = document.getElementById('bulkCensusAuthForm');
            const selected = [...document.querySelectorAll('.census-auth-select:checked')];
            if (!selected.length) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'send_all';
                input.value = '1';
                form.appendChild(input);
                form.submit();
                return;
            }

            form.innerHTML = '<input type="hidden" name="csrf_test_name" value="' + (document.querySelector('input[name^="csrf_"]')?.value || '') + '">';
            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            form.submit();
        }

        // Escape key closes drawer too
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDrawer();
                document.querySelectorAll('.pa-overlay.active').forEach(m => m.classList.remove('active'));
            }
        });
    </script>

    <!-- ══════════════════════════════════════════════════════════════════════
         ACCOUNT MANAGEMENT DRAWER
         ══════════════════════════════════════════════════════════════════════ -->
    <div class="rl-drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

    <aside class="rl-drawer" id="accountDrawer" aria-label="Manage Account">

        <!-- Drawer Header -->
        <div class="rl-drawer-header">
            <div class="rl-drawer-header-left">
                <div class="rl-drawer-avatar" id="dr-avatar">M</div>
                <div>
                    <div class="rl-drawer-name" id="dr-name">—</div>
                    <div class="rl-drawer-meta" id="dr-meta">—</div>
                </div>
            </div>
            <button type="button" class="rl-drawer-close" onclick="closeDrawer()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Info pills -->
        <div class="rl-drawer-pills">
            <span class="rl-drawer-pill">
                <i class="fas fa-map-marker-alt"></i> <span id="dr-zone">—</span>
            </span>
            <span class="rl-drawer-pill">
                <i class="fas fa-home"></i> HH&nbsp;<span id="dr-hh">—</span>
            </span>
            <span class="rl-drawer-pill rl-drawer-pill--status" id="dr-status-pill">
                <i class="fas fa-circle" id="dr-status-icon"></i>
                <span id="dr-status">—</span>
            </span>
        </div>

        <div class="rl-drawer-divider"></div>

        <!-- ── Section 1: Change Username ── -->
        <div class="rl-drawer-section">
            <div class="rl-drawer-section-title">
                <i class="fas fa-at"></i> Username
            </div>
            <form id="dr-username-form" method="post">
                <?= csrf_field() ?>
                <div class="rl-drawer-field-wrap">
                    <label class="rl-drawer-label">Current username</label>
                    <div class="rl-drawer-current" id="dr-current-username">—</div>
                </div>
                <div class="rl-drawer-field-wrap">
                    <label class="rl-drawer-label" for="dr-new-username">New username</label>
                    <div class="rl-drawer-input-wrap">
                        <i class="fas fa-at rl-drawer-input-icon"></i>
                        <input
                            type="text"
                            id="dr-new-username"
                            name="new_username"
                            class="rl-drawer-input"
                            placeholder="e.g. juandelacruz"
                            minlength="4"
                            pattern="[a-zA-Z0-9_.]+"
                            required>
                    </div>
                    <span class="rl-drawer-hint">Letters, numbers, underscores and dots only.</span>
                </div>
                <button type="submit" class="rl-drawer-btn rl-drawer-btn--primary">
                    <i class="fas fa-save"></i> Save Username
                </button>
            </form>
        </div>

        <div class="rl-drawer-divider"></div>

        <!-- ── Section 2: Change Email ── -->
        <div class="rl-drawer-section" id="dr-email-section">
            <div class="rl-drawer-section-title">
                <i class="fas fa-envelope"></i> Email Address
            </div>

            <!-- Step A: enter new email -->
            <div id="dr-email-step-a">
                <div class="rl-drawer-field-wrap">
                    <label class="rl-drawer-label">Current email</label>
                    <div class="rl-drawer-current" id="dr-current-email">—</div>
                </div>
                <div class="rl-drawer-field-wrap">
                    <label class="rl-drawer-label" for="dr-new-email">New email address</label>
                    <div class="rl-drawer-input-wrap">
                        <i class="fas fa-envelope rl-drawer-input-icon"></i>
                        <input
                            type="email"
                            id="dr-new-email"
                            class="rl-drawer-input"
                            placeholder="e.g. juan@email.com"
                            required>
                    </div>
                </div>
                <div class="rl-drawer-hint rl-drawer-hint--warn" id="dr-email-step-a-err" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="dr-email-step-a-err-text"></span>
                </div>
                <button type="button" class="rl-drawer-btn rl-drawer-btn--teal" id="dr-send-otp-btn" onclick="drSendEmailOtp()">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </div>

            <!-- Step B: enter OTP (hidden until OTP sent) -->
            <div id="dr-email-step-b" style="display:none;">
                <div class="rl-drawer-otp-info" id="dr-otp-info-text"></div>
                <div class="rl-drawer-field-wrap" style="margin-top:14px;">
                    <label class="rl-drawer-label" for="dr-email-otp">Verification code</label>
                    <div class="rl-drawer-input-wrap">
                        <i class="fas fa-key rl-drawer-input-icon"></i>
                        <input
                            type="text"
                            id="dr-email-otp"
                            class="rl-drawer-input rl-drawer-input--otp"
                            placeholder="6-digit code"
                            maxlength="6"
                            inputmode="numeric"
                            pattern="\d{6}"
                            autocomplete="one-time-code">
                    </div>
                </div>
                <div class="rl-drawer-hint rl-drawer-hint--warn" id="dr-email-step-b-err" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="dr-email-step-b-err-text"></span>
                </div>
                <div class="rl-drawer-hint rl-drawer-hint--success" id="dr-email-success" style="display:none;">
                    <i class="fas fa-check-circle"></i>
                    <span id="dr-email-success-text"></span>
                </div>
                <div style="display:flex;gap:8px;margin-top:4px;">
                    <button type="button" class="rl-drawer-btn rl-drawer-btn--ghost" onclick="drResetEmailStep()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="rl-drawer-btn rl-drawer-btn--teal" style="flex:1;" onclick="drVerifyEmailOtp()">
                        <i class="fas fa-check"></i> Confirm Change
                    </button>
                </div>
                <button type="button" class="rl-drawer-btn rl-drawer-btn--ghost" style="margin-top:6px;" onclick="drSendEmailOtp(true)">
                    <i class="fas fa-redo"></i> Resend Code
                </button>
            </div>
        </div>

        <div class="rl-drawer-divider"></div>

        <!-- ── Section 3: Reset Password ── -->
        <div class="rl-drawer-section">
            <div class="rl-drawer-section-title">
                <i class="fas fa-key"></i> Reset Password
            </div>
            <form id="dr-password-form" method="post" onsubmit="return validatePwDrawer()">
                <?= csrf_field() ?>
                <div class="rl-drawer-field-wrap">
                    <label class="rl-drawer-label" for="dr-pw-new">New password</label>
                    <div class="rl-drawer-input-wrap">
                        <i class="fas fa-lock rl-drawer-input-icon"></i>
                        <input
                            type="password"
                            id="dr-pw-new"
                            name="new_password"
                            class="rl-drawer-input"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            required>
                        <button type="button" class="rl-drawer-eye" onclick="toggleDrawerPw('dr-pw-new','dr-eye1')" aria-label="Toggle">
                            <i class="fas fa-eye" id="dr-eye1"></i>
                        </button>
                    </div>
                </div>
                <div class="rl-drawer-field-wrap">
                    <label class="rl-drawer-label" for="dr-pw-confirm">Confirm password</label>
                    <div class="rl-drawer-input-wrap">
                        <i class="fas fa-lock rl-drawer-input-icon"></i>
                        <input
                            type="password"
                            id="dr-pw-confirm"
                            name="confirm_password"
                            class="rl-drawer-input"
                            placeholder="Repeat new password"
                            minlength="8"
                            required>
                        <button type="button" class="rl-drawer-eye" onclick="toggleDrawerPw('dr-pw-confirm','dr-eye2')" aria-label="Toggle">
                            <i class="fas fa-eye" id="dr-eye2"></i>
                        </button>
                    </div>
                    <span class="rl-drawer-hint rl-drawer-hint--warn" id="dr-pw-mismatch" style="display:none;">
                        <i class="fas fa-exclamation-triangle"></i> Passwords do not match.
                    </span>
                </div>
                <button type="submit" class="rl-drawer-btn rl-drawer-btn--warn">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
        </div>

        <div class="rl-drawer-divider"></div>

        <!-- ── Section 3: Danger Zone ── -->
        <div class="rl-drawer-section rl-drawer-danger-zone">
            <div class="rl-drawer-section-title rl-drawer-section-title--danger">
                <i class="fas fa-exclamation-triangle"></i> Danger Zone
            </div>
            <p class="rl-drawer-danger-text">
                Permanently deletes this account. The census record is kept. This cannot be undone.
            </p>
            <form id="dr-delete-form" method="post">
                <?= csrf_field() ?>
                <button type="button" class="rl-drawer-btn rl-drawer-btn--danger" onclick="confirmDrawerDelete()">
                    <i class="fas fa-trash"></i> Delete Account
                </button>
            </form>
        </div>

    </aside>

    <!-- ── Delete Account Modal ───────────────────────────────────────────── -->
    <div class="pa-overlay" id="deleteModal">
        <div class="pa-modal" style="max-width:420px;">
            <div class="pa-modal-header">
                <div class="pa-modal-icon pa-modal-icon--reject"><i class="fas fa-trash"></i></div>
                <div>
                    <div class="pa-modal-title">Delete Account</div>
                    <p class="pa-modal-sub">This action is permanent and cannot be undone.</p>
                </div>
            </div>
            <div class="pa-modal-body">
                <div class="pa-user-card">
                    <div class="pa-user-avatar" style="background:#c0392b;"><i class="fas fa-user-slash"></i></div>
                    <div>
                        <div class="pa-user-name" id="del-name">—</div>
                        <div class="pa-user-meta">Resident account</div>
                    </div>
                </div>
                <div class="pa-reject-note" style="margin-top:12px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>The account will be <strong>permanently deleted</strong>. The resident's census record will remain. They can re-register if needed.</span>
                </div>
            </div>
            <div class="pa-modal-footer">
                <button class="pa-btn pa-btn--cancel" onclick="closeModal('deleteModal')">
                    <i class="fas fa-arrow-left"></i> Cancel
                </button>
                <form id="del-form" method="post" style="flex:1;display:flex;">
                    <?= csrf_field() ?>
                    <button type="submit" class="pa-btn pa-btn--reject" style="flex:1;">
                        <i class="fas fa-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>