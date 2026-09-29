<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Programs & Events - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .sk-requirement-checks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 10px;
            border: 1px solid #e1e5ef;
            border-radius: 8px;
            background: #fafbfe;
        }

        .sk-requirement-checks label {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 9px;
            border: 1px solid #e8ecf4;
            border-radius: 6px;
            background: #fff;
            color: #4a5068;
            font-size: 11.5px;
            cursor: pointer;
        }

        .sk-requirement-checks input {
            accent-color: #1d2448;
        }

        @media (max-width: 560px) {
            .sk-requirement-checks {
                grid-template-columns: 1fr;
            }
        }

        /* ── Form labels ── */
        .sk-form-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: .2px;
        }

        .sk-form-label i {
            font-size: 11px;
            color: #9aa0b4;
            width: 14px;
            text-align: center;
        }

        .sk-form-label .sk-required {
            color: #ef4444;
            font-size: 13px;
            line-height: 1;
        }

        /* ── Input wrapper with icon ── */
        .sk-input-wrap {
            position: relative;
        }

        .sk-input-wrap .sk-input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b8cc;
            font-size: 12px;
            pointer-events: none;
        }

        .sk-input-wrap .sk-form-input {
            padding-left: 32px;
        }

        .sk-input-wrap.sk-textarea-wrap .sk-input-icon {
            top: 12px;
            transform: none;
        }

        /* ── Inputs ── */
        .sk-form-input {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e5e7eb;
            border-radius: 9px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #1d2448;
            background: #fafbfc;
            box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }

        .sk-form-input:focus {
            border-color: #5b6fd6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(91, 111, 214, .10);
        }

        select.sk-form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239aa0b4' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        /* ── Grid rows ── */
        .sk-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .sk-form-row--full {
            grid-template-columns: 1fr;
        }

        .sk-form-field {
            display: flex;
            flex-direction: column;
        }

        /* ── Section divider ── */
        .sk-section-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 18px 0 16px;
        }

        .sk-section-divider span {
            font-size: 10.5px;
            font-weight: 700;
            color: #9aa0b4;
            letter-spacing: .8px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .sk-section-divider::before,
        .sk-section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eaecf4;
        }

        /* ── Hint text ── */
        .sk-form-hint {
            font-size: 10.5px;
            color: #9aa0b4;
            margin-top: 4px;
            font-weight: 400;
        }

        /* ── Modal override: wider & better spaced ── */
        #addProgramModal .db-modal,
        #editProgramModal .db-modal {
            max-width: 640px;
            width: min(92vw, 640px);
            border-radius: 16px;
            overflow: hidden;
        }

        #addProgramModal .db-modal-body,
        #editProgramModal .db-modal-body {
            padding: 22px 24px 10px;
            background: #fff;
        }

        #addProgramModal .db-modal-header,
        #editProgramModal .db-modal-header {
            padding: 18px 24px 16px;
            border-bottom: 1px solid #f0f2f8;
            background: #fff;
        }

        #addProgramModal .db-modal-footer,
        #editProgramModal .db-modal-footer {
            padding: 14px 24px 18px;
            border-top: 1px solid #f0f2f8;
            background: #fff;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        #editProgramForm {
            display: block;
            width: 100%;
        }

        #editProgramModal .db-modal-body .sk-form-row:last-child {
            margin-bottom: 0;
        }

        .db-action-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .prog-cat-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .prog-cat-sports {
            background: rgba(91, 111, 214, .12);
            color: #5b6fd6;
        }

        .prog-cat-livelihood {
            background: rgba(255, 193, 7, .12);
            color: #e6a800;
        }

        .prog-cat-health {
            background: rgba(220, 53, 69, .12);
            color: #dc3545;
        }

        .prog-cat-education {
            background: rgba(22, 199, 154, .12);
            color: #16c79a;
        }

        .prog-cat-environment {
            background: rgba(40, 167, 69, .12);
            color: #28a745;
        }

        .prog-cat-cultural {
            background: rgba(111, 66, 193, .12);
            color: #6f42c1;
        }

        .prog-cat-other {
            background: rgba(29, 36, 72, .08);
            color: #1d2448;
        }

        .db-badge--completed {
            background: rgba(108, 117, 125, .12);
            color: #6c757d;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .db-badge--cancelled {
            background: rgba(220, 53, 69, .1);
            color: #c0392b;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $sessionRole = strtolower((string) session()->get('role'));
    $role        = $sessionRole === 'council' ? 'council' : ($sessionRole === 'admin' ? 'admin' : 'sk');
    $active      = 'programs';
    $canManage   = in_array($role, ['sk', 'admin'], true);
    $pageTitle = 'Programs & Events';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $programs  = $programs  ?? [];
    $counts    = $counts    ?? ['total' => 0, 'Active' => 0, 'Upcoming' => 0, 'Completed' => 0, 'Cancelled' => 0];
    $search    = $search    ?? '';
    $catF      = $catF      ?? '';
    $statusF   = $statusF   ?? '';

    $catIcons = [
        'Sports'      => 'fa-futbol',
        'Livelihood'  => 'fa-briefcase',
        'Health'      => 'fa-heartbeat',
        'Education'   => 'fa-graduation-cap',
        'Environment' => 'fa-leaf',
        'Cultural'    => 'fa-music',
        'Other'       => 'fa-calendar',
    ];
    $badgeMap = [
        'Active'    => 'db-badge--approved',
        'Upcoming'  => 'db-badge--pending',
        'Completed' => 'db-badge--completed',
        'Cancelled' => 'db-badge--cancelled',
    ];
    $categories = ['Sports', 'Livelihood', 'Health', 'Education', 'Environment', 'Cultural', 'Other'];
    $statuses   = ['Upcoming', 'Active', 'Completed', 'Cancelled'];
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

            <!-- Stats -->
            <div class="db-stats" style="margin-bottom:24px;">
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,.15);color:#5b6fd6;"><i class="fas fa-calendar-alt"></i></div>
                    <div><span class="db-stat-num"><?= $counts['total'] ?></span><span class="db-stat-label">Total Programs</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,199,154,.15);color:#16c79a;"><i class="fas fa-play-circle"></i></div>
                    <div><span class="db-stat-num"><?= $counts['Active'] ?></span><span class="db-stat-label">Active</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(255,193,7,.15);color:#e6a800;"><i class="fas fa-clock"></i></div>
                    <div><span class="db-stat-num"><?= $counts['Upcoming'] ?></span><span class="db-stat-label">Upcoming</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(108,117,125,.15);color:#6c757d;"><i class="fas fa-check-double"></i></div>
                    <div><span class="db-stat-num"><?= $counts['Completed'] ?></span><span class="db-stat-label">Completed</span></div>
                </div>
            </div>

            <!-- Toolbar -->
            <form method="get" action="" id="filterForm">
                <div class="db-toolbar">
                    <div class="db-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search programs..."
                            value="<?= esc($search) ?>" onchange="this.form.submit()">
                    </div>
                    <div class="db-toolbar-actions">
                        <select name="category" class="db-filter-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $c): ?>
                                <option <?= $catF === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status" class="db-filter-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <?php foreach ($statuses as $s): ?>
                                <option <?= $statusF === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($search || $catF || $statusF): ?>
                            <a href="/sk/programs" class="db-btn db-btn--outline"><i class="fas fa-times"></i> Clear</a>
                        <?php endif; ?>
                        <?php if ($canManage): ?><a href="/<?= esc($role) ?>/programs/new" class="db-btn db-btn--primary">
                                <i class="fas fa-plus"></i> Add Program
                            </a><?php endif; ?>
                    </div>
                </div>
            </form>

            <!-- Table -->
            <div class="db-table-wrap">
                <table class="db-table" id="programsTable">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Category</th>
                            <th>Start Date</th>
                            <th>Conducted Date</th>
                            <th>Venue</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($programs)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center;padding:32px;color:#9aa0b4;">
                                    <i class="fas fa-calendar-times" style="font-size:28px;display:block;margin-bottom:10px;color:#d0d5e8;"></i>
                                    No programs yet. <?php if ($canManage): ?>Click <strong>Add Program</strong> to get started.<?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($programs as $p):
                                $catKey = strtolower($p['category']);
                                $icon   = $catIcons[$p['category']] ?? 'fa-calendar';
                                $badge  = $badgeMap[$p['status']] ?? 'db-badge--pending';
                                $dateStr = $p['start_date'] ? date('M d, Y', strtotime($p['start_date'])) : '—';
                                $conductedStr = !empty($p['conducted_date']) ? date('M d, Y', strtotime($p['conducted_date'])) : '—';
                                $targetParticipants = (int)($p['target_participants'] ?? 0);
                                $approvedParticipants = (int)($p['actual_participants'] ?? 0);
                            ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div class="prog-cat-icon prog-cat-<?= $catKey ?>">
                                                <i class="fas <?= $icon ?>"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:600;color:#1a1d2e;"><?= esc($p['name']) ?></div>
                                                <?php if (!empty($p['description'])): ?>
                                                    <div style="font-size:11.5px;color:#9aa0b4;"><?= esc(mb_strimwidth($p['description'], 0, 60, '…')) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= esc($p['category']) ?></td>
                                    <td><?= $dateStr ?></td>
                                    <td><?= $conductedStr ?></td>
                                    <td><?= esc($p['venue'] ?? '—') ?></td>
                                    <td><?= $targetParticipants > 0 ? $approvedParticipants . '/' . $targetParticipants : ($approvedParticipants ?: '—') ?></td>
                                    <td>
                                        <?php if ($canManage): ?>
                                            <form action="/sk/programs/status/<?= (int) $p['id'] ?>" method="post" style="margin:0;">
                                                <?= csrf_field() ?>
                                                <select name="status" class="db-filter-select" style="min-width:108px;padding:5px 8px;font-size:11px;" onchange="this.form.submit()" title="Update program status">
                                                    <?php foreach ($statuses as $status): ?><option value="<?= esc($status) ?>" <?= $p['status'] === $status ? 'selected' : '' ?>><?= esc($status) ?></option><?php endforeach; ?>
                                                </select>
                                            </form>
                                        <?php else: ?><span class="db-badge <?= $badge ?>"><?= esc($p['status']) ?></span><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="db-action-group">
                                            <?php if ($canManage): ?><a href="/sk/programs/registrations/<?= $p['id'] ?>"
                                                    class="db-icon-btn" title="View Registrations"
                                                    style="color:#5b6fd6;">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                <a href="/<?= esc($role) ?>/programs/edit/<?= (int) $p['id'] ?>" class="db-icon-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="/sk/programs/delete/<?= $p['id'] ?>" method="post" style="display:inline;"
                                                    onsubmit="return confirm('Delete \'<?= esc(addslashes($p['name'])) ?>\'? This cannot be undone.')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="db-icon-btn" style="color:#dc3545;" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <script>
        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>
