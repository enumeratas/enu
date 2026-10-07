<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Clearances - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .clr-flash-alert.is-hidden {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .25s ease, transform .25s ease;
            pointer-events: none;
        }

        /* ── Empty state ── */
        .clr-empty {
            text-align: center;
            padding: 48px 20px;
            color: #9aa0b4;
        }

        .clr-empty i {
            font-size: 40px;
            display: block;
            margin-bottom: 12px;
            color: #d0d5e8;
        }

        /* ── Modal ── */
        .clr-modal {
            max-width: 560px;
            border-radius: 16px;
            overflow: hidden;
        }

        .clr-modal-header {
            background: linear-gradient(135deg, #1d2448 0%, #2e3a6e 100%);
            padding: 22px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .clr-modal-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .clr-modal-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
        }

        .clr-modal-header h3 {
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .clr-modal-header p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 12px;
            margin: 0;
        }

        .clr-modal-close {
            background: rgba(255, 255, 255, 0.12);
            border: none;
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }

        .clr-modal-close:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Form body */
        .clr-form-body {
            padding: 24px 28px;
            background: #f9fafb;
        }

        .clr-section {
            margin-bottom: 22px;
        }

        .clr-section-label {
            font-size: 11px;
            font-weight: 700;
            color: #9aa0b4;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .clr-section-label i {
            color: #1d2448;
        }

        /* Member pills */
        .clr-member-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .clr-member-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 2px solid #e2e5ef;
            border-radius: 12px;
            cursor: pointer;
            background: #fff;
            transition: all .2s;
            font-family: 'Poppins', sans-serif;
        }

        .clr-member-pill:hover {
            border-color: #1d2448;
            background: #f0f2ff;
        }

        .clr-member-pill.selected {
            border-color: #1d2448;
            background: #1d2448;
        }

        .clr-member-pill input[type="radio"] {
            display: none;
        }

        .clr-member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #eef0fb;
            color: #1d2448;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .clr-member-pill.selected .clr-member-avatar {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .clr-member-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #1a1d2e;
            line-height: 1.3;
        }

        .clr-member-rel {
            font-size: 11px;
            color: #9aa0b4;
        }

        .clr-member-pill.selected .clr-member-name,
        .clr-member-pill.selected .clr-member-rel {
            color: #fff;
        }

        /* Doc type cards */
        .clr-doc-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .clr-doc-card {
            border: 2px solid #e2e5ef;
            border-radius: 12px;
            padding: 16px 12px;
            text-align: center;
            cursor: pointer;
            background: #fff;
            transition: all .2s;
            font-family: 'Poppins', sans-serif;
            position: relative;
        }

        .clr-doc-card:hover {
            border-color: #1d2448;
            background: #f8f9ff;
            transform: translateY(-1px);
        }

        .clr-doc-card.selected {
            border-color: #1d2448;
            background: #1d2448;
        }

        .clr-doc-card input[type="radio"] {
            display: none;
        }

        .clr-doc-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #eef0fb;
            color: #1d2448;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 10px;
        }

        .clr-doc-card.selected .clr-doc-icon {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .clr-doc-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #1a1d2e;
            line-height: 1.4;
            margin-bottom: 4px;
        }

        .clr-doc-fee {
            font-size: 11px;
            color: #9aa0b4;
            font-weight: 600;
        }

        .clr-doc-card.selected .clr-doc-name,
        .clr-doc-card.selected .clr-doc-fee {
            color: #fff;
        }

        .clr-doc-check {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #fff;
        }

        .clr-doc-card.selected .clr-doc-check {
            display: flex;
        }

        /* Select & textarea */
        .clr-select,
        .clr-textarea {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #e2e5ef;
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            box-sizing: border-box;
        }

        .clr-select:focus,
        .clr-textarea:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, 0.08);
        }

        .clr-textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Info note */
        .clr-info-note {
            background: #f0f4ff;
            border: 1px solid #d0d8f5;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 12.5px;
            color: #4a5068;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .clr-info-note i {
            color: #5b6fd6;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Submit btn */
        .clr-submit-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity .2s, transform .15s;
        }

        .clr-submit-btn:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        /* Status badges */
        .clr-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
        }

        .clr-badge--pending {
            background: #fff8f0;
            color: #b7600a;
            border: 1px solid #fde8c8;
        }

        .clr-badge--approved {
            background: #f0faf6;
            color: #1a7a55;
            border: 1px solid #c3e8d8;
        }

        .clr-badge--rejected {
            background: #fff0f1;
            color: #c0392b;
            border: 1px solid #fad4d4;
        }

        .clr-badge--released {
            background: #eef0fb;
            color: #1d2448;
            border: 1px solid #d0d8f5;
        }

        /* No household warning */
        .clr-no-hh {
            background: #fff8f0;
            border: 1px solid #fde8c8;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 13px;
            color: #b7600a;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        /* Divider */
        .clr-divider {
            height: 1px;
            background: #f0f2f8;
            margin: 0 0 20px;
        }

        /* Indigency ineligible state */
        .clr-doc-card--ineligible {
            opacity: .6;
            cursor: not-allowed !important;
            pointer-events: none;
            border-color: #fad4d4 !important;
            background: #fff5f5 !important;
        }

        .clr-indig-warn {
            margin-top: 6px;
            font-size: 10px;
            font-weight: 700;
            color: #c0392b;
            background: #fff0f1;
            border: 1px solid #fad4d4;
            border-radius: 6px;
            padding: 3px 7px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Document choices in the request modal */
        .secretary-doc-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .secretary-doc-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 68px;
            padding: 10px 12px;
            border: 2px solid #e2e5ef;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s, background .18s, transform .18s;
            box-sizing: border-box;
        }

        .secretary-doc-card:hover,
        .secretary-doc-card.selected {
            border-color: #1d2448;
            background: #f0f2ff;
        }

        .secretary-doc-card:hover {
            transform: translateY(-1px);
        }

        .secretary-doc-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .secretary-doc-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .secretary-doc-name {
            color: #1a1d2e;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
        }

        .secretary-doc-fee {
            margin-top: 2px;
            color: #16a085;
            font-size: 11px;
            font-weight: 600;
        }

        .secretary-doc-card.ineligible {
            cursor: not-allowed;
            opacity: .62;
            border-color: #e2e5ef;
            background: #f8f9fc;
        }

        .secretary-doc-card.ineligible:hover {
            border-color: #e2e5ef;
            background: #f8f9fc;
            transform: none;
        }

        .secretary-doc-reason {
            display: none;
            margin-top: 4px;
            color: #b7600a;
            font-size: 10px;
            line-height: 1.35;
        }

        .secretary-doc-card.ineligible .secretary-doc-reason {
            display: block;
        }

        @media (max-width: 600px) {
            .secretary-doc-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Match the compact horizontal document cards used by official requests. */
        .clr-doc-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .clr-doc-card {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 68px;
            box-sizing: border-box;
            padding: 10px 12px;
            text-align: left;
            border: 2px solid #e2e5ef;
            border-radius: 10px;
            background: #fff;
        }

        .clr-doc-card:hover {
            border-color: #1d2448;
            background: #f8f9ff;
        }

        .clr-doc-card.selected {
            border-color: #1d2448;
            background: #f0f2ff;
            color: #1a1d2e;
        }

        .clr-doc-card .clr-doc-icon {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            margin: 0;
            border-radius: 9px;
            font-size: 14px;
        }

        .clr-doc-card .clr-doc-name {
            margin: 0 0 2px;
            font-size: 12px;
            line-height: 1.3;
        }

        .clr-doc-card .clr-doc-fee {
            margin: 0;
            font-size: 11px;
            color: #16a085;
            opacity: 1;
        }

        .clr-doc-card.selected .clr-doc-name,
        .clr-doc-card.selected .clr-doc-fee {
            color: #1a1d2e;
        }

        .clr-doc-card .clr-doc-check {
            top: 7px;
            right: 7px;
            width: 16px;
            height: 16px;
            background: #1d2448;
        }

        .clr-doc-card .clr-indig-warn {
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 4px;
            margin: 0;
            font-size: 9px;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $requestRole = in_array(session()->get('role'), ['sk', 'council'], true) ? session()->get('role') : 'resident';
    $role      = $requestRole;
    $active    = 'clearance';
    $pageTitle = 'My Clearances';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $requests = $requests ?? [];
    $members  = $members  ?? [];
    $user     = $user     ?? [];
    $flashSuccess = session()->getFlashdata('success');
    $flashError   = session()->getFlashdata('error') ?: session()->getFlashdata('blotter_error');
    $flashMessage = $flashSuccess ?: $flashError;
    $flashClass   = $flashSuccess ? 'db-alert--success' : 'db-alert--error';
    ?>


    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>

        <div class="db-content">

            <?php if ($flashMessage): ?>
                <div id="clearanceFlash" class="db-alert <?= $flashClass ?> clr-flash-alert" role="status">
                    <i class="fas <?= $flashSuccess ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= esc($flashMessage) ?>
                </div>
            <?php endif; ?>

            <div class="db-welcome">
                <div>
                    <h2>Hello, <?= esc(session()->get('full_name') ?? session()->get('username') ?? 'Resident') ?> 👋</h2>
                    <p>Barangay Bacolod, Bato, Camarines Sur — Barangay Information System</p>
                </div>
                <div class="db-welcome-icon"><i class="fas fa-users"></i></div>
            </div>

            <?php if (empty($members)): ?>
                <div class="clr-no-hh">
                    <i class="fas fa-exclamation-triangle" style="margin-top:2px;flex-shrink:0;"></i>
                    <div>Your account is not linked to a household in the census. You need to be verified and linked to a household before you can request documents. Please contact the barangay office.</div>
                </div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="db-toolbar">
                <form method="get" data-live-results="liveResults" class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search document, purpose, or date..." value="<?= esc($search ?? '') ?>">
                </form>
                <div class="db-toolbar-actions">
                    <?php if (! empty($members)): ?>
                        <button class="db-btn db-btn--primary" onclick="openModal('newModal')">
                            <i class="fas fa-plus"></i> New Request
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div id="liveResults">
            <!-- Requests table -->
            <div class="db-table-wrap">
                <table class="db-table" id="requestsTable">
                    <thead>
                        <tr>
                            <th>For</th>
                            <th>Document Type</th>
                            <th>Purpose</th>
                            <th>Date Filed</th>
                            <th>Est. Release</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="clr-empty">
                                        <i class="fas fa-file-alt"></i>
                                        <p><?= ($search ?? '') !== '' ? 'No requests match your search.' : 'No requests yet. Click <strong>New Request</strong> to get started.' ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $r):
                                $statusMap = [
                                    'pending'  => ['clr-badge--pending',  'fa-clock',        'Pending'],
                                    'approved' => ['clr-badge--approved', 'fa-check-circle', 'Approved'],
                                    'rejected' => ['clr-badge--rejected', 'fa-times-circle', 'Rejected'],
                                    'released' => ['clr-badge--released', 'fa-box-open',     'Released'],
                                ];
                                [$badgeClass, $icon, $label] = $statusMap[$r['status']] ?? $statusMap['pending'];
                                $filed   = date('M d, Y', strtotime($r['created_at']));
                                $release = $r['est_release_date'] ? date('M d, Y', strtotime($r['est_release_date'])) : '—';
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-size:13px;font-weight:600;color:#1a1d2e;"><?= esc($r['for_member']) ?></div>
                                        <div style="font-size:11px;color:#9aa0b4;"><?= esc($r['member_relationship'] ?? '') ?></div>
                                    </td>
                                    <td><?= esc($r['document_type']) ?></td>
                                    <td><?= esc($r['purpose']) ?></td>
                                    <td><?= $filed ?></td>
                                    <td><?= $release ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <span class="clr-badge <?= $badgeClass ?>">
                                                <i class="fas <?= $icon ?>"></i> <?= $label ?>
                                            </span>
                                            <?php if (($r['status'] ?? '') === 'pending'): ?>
                                                <button type="button"
                                                    class="db-btn db-btn--xs db-btn--danger"
                                                    onclick='confirmCancel(<?= (int) $r['id'] ?>, <?= json_encode($r['document_type'] ?? '') ?>)'>
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

        </div>
    </div>

    <!-- ══ CANCEL CONFIRMATION MODAL ══ -->
    <div class="db-modal-overlay" id="cancelModal">
        <div class="db-modal" style="max-width:400px;">
            <div class="db-modal-header" style="background:#fff0f1;border-bottom:1px solid #fad4d4;">
                <h3 style="color:#c0392b;font-size:14px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-exclamation-triangle"></i> Cancel Request
                </h3>
                <button class="db-modal-close" onclick="closeModal('cancelModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="db-modal-body" style="padding:24px;">
                <p style="font-size:13.5px;color:#4a5068;margin:0 0 6px;">Are you sure you want to cancel this request?</p>
                <p id="cancelDocType" style="font-size:14px;font-weight:700;color:#1a1d2e;margin:0 0 16px;"></p>
                <p style="font-size:12.5px;color:#9aa0b4;margin:0;">This action cannot be undone. The request will be permanently removed.</p>
            </div>
            <div class="db-modal-footer" style="gap:10px;">
                <button class="db-btn db-btn--outline" onclick="closeModal('cancelModal')" style="flex:1;">
                    Keep Request
                </button>
                <form id="cancelForm" method="post" style="flex:1;">
                    <?= csrf_field() ?>
                    <button type="submit" class="db-btn db-btn--danger" style="width:100%;justify-content:center;">
                        <i class="fas fa-times"></i> Yes, Cancel It
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="db-modal-overlay" id="newModal">
        <div class="db-modal clr-modal">

            <!-- Header -->
            <div class="clr-modal-header">
                <div class="clr-modal-header-left">
                    <div class="clr-modal-header-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3>New Document Request</h3>
                        <p>Fill in the details below to submit your request</p>
                    </div>
                </div>
                <button class="clr-modal-close" onclick="closeModal('newModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="/<?= $requestRole ?>/clearance/store" method="post" id="clearanceForm" data-offline-sync="clearance_request" data-offline-verified="1" novalidate>
                <p data-offline-sync-status aria-live="polite" style="font-size:12px;color:#667085;margin:0 0 12px;display:block;"></p>
                <?= csrf_field() ?>
                <div class="clr-form-body" style="max-height:72vh;overflow-y:auto;">

                    <!-- Step 1: Who is this for? -->
                    <div class="clr-section">
                        <div class="clr-section-label">
                            <i class="fas fa-user"></i> Who is this document for?
                        </div>
                        <?php if (empty($members)): ?>
                            <p style="font-size:12.5px;color:#b7600a;background:#fff8f0;padding:10px 14px;border-radius:8px;border:1px solid #fde8c8;">
                                <i class="fas fa-exclamation-triangle"></i>
                                No household members found. Please contact the barangay office.
                            </p>
                        <?php else: ?>
                            <p style="font-size:12px;color:#6b7280;margin:0 0 12px;line-height:1.6;">
                                <i class="fas fa-info-circle" style="color:#5b6fd6;margin-right:5px;"></i>
                                Document requests are for <strong>personal use only</strong>. Household head and spouse may also request on behalf of <strong>any minor household member</strong>, regardless of relationship.
                            </p>
                            <div class="clr-member-pills" id="memberPills">
                                <?php foreach ($members as $i => $m): ?>
                                    <label class="clr-member-pill <?= $i === 0 ? 'selected' : '' ?>"
                                        data-minor="<?= ! empty($m['is_minor']) ? '1' : '0' ?>"
                                        onclick="selectMember(this, '<?= esc($m['name']) ?>', '<?= esc($m['relationship']) ?>', <?= ! empty($m['is_minor']) ? 'true' : 'false' ?>)">
                                        <input type="radio" name="_member_ui" value="<?= esc($m['name']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                        <div class="clr-member-avatar"><?= strtoupper($m['name'][0] ?? '?') ?></div>
                                        <div>
                                            <div class="clr-member-name"><?= esc($m['name']) ?></div>
                                            <div class="clr-member-rel">
                                                <?= esc($m['relationship']) ?>
                                                <?php if (! empty($m['is_minor'])): ?>
                                                    <span style="margin-left:5px;background:rgba(22,160,133,.12);color:#16a085;font-size:10px;font-weight:700;padding:1px 7px;border-radius:100px;border:1px solid rgba(22,160,133,.2);">Minor</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="for_member" id="forMember" value="<?= esc($members[0]['name'] ?? '') ?>">
                            <input type="hidden" name="member_relationship" id="memberRel" value="<?= esc($members[0]['relationship'] ?? '') ?>">
                        <?php endif; ?>
                    </div>

                    <div class="clr-divider"></div>

                    <!-- Step 2: Document type -->
                    <div class="db-form-group db-form-group--full">
                        <label for="secretaryDocumentType">Document Type <span style="color:#c0392b;">*</span></label>
                        <div class="secretary-doc-grid" id="secretaryDocumentType">
                            <?php
                            $documentOptions = [
                                ['value' => 'Barangay Clearance', 'icon' => 'fa-file-alt', 'color' => '#5b6fd6', 'bg' => 'rgba(91,111,214,.12)'],
                                ['value' => 'Certificate of Residency', 'icon' => 'fa-home', 'color' => '#16c79a', 'bg' => 'rgba(22,199,154,.12)'],
                                ['value' => 'Certificate of Indigency', 'icon' => 'fa-hands-helping', 'color' => '#e6a800', 'bg' => 'rgba(255,193,7,.14)'],
                                ['value' => 'Certificate of Good Moral', 'icon' => 'fa-award', 'color' => '#7c5cbf', 'bg' => 'rgba(124,92,191,.12)'],
                                ['value' => 'First Time Job Seekers', 'icon' => 'fa-briefcase', 'color' => '#16a085', 'bg' => 'rgba(22,160,133,.12)'],
                                ['value' => 'Solo Parent Certificate', 'icon' => 'fa-child', 'color' => '#3a8fd9', 'bg' => 'rgba(58,143,217,.12)'],
                                ['value' => 'Business Permit Clearance', 'icon' => 'fa-store', 'color' => '#dc3545', 'bg' => 'rgba(220,53,69,.12)'],
                            ];
                            ?>
                            <?php foreach ($documentOptions as $document): ?>
                                <label class="secretary-doc-card" data-document="<?= esc($document['value']) ?>" data-adult-only="<?= \Config\ClearanceDocuments::isAdultOnly($document['value']) ? '1' : '0' ?>" onclick="selectDocument(this)">
                                    <input type="radio" name="document_type" value="<?= esc($document['value']) ?>" disabled required>
                                    <div class="secretary-doc-icon" style="background:<?= $document['bg'] ?>;color:<?= $document['color'] ?>;"><i class="fas <?= $document['icon'] ?>"></i></div>
                                    <div>
                                        <div class="secretary-doc-name"><?= esc($document['value']) ?></div>
                                        <div class="secretary-doc-fee"><?= esc(\Config\ClearanceDocuments::fee($document['value'])) ?></div>
                                        <div class="secretary-doc-reason" aria-live="polite"></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small id="secretaryEligibilityNote" style="color:#9aa0b4;">Only qualified document types will be selectable.</small>
                    </div>

                    <div class="clr-divider"></div>

                    <!-- Step 3: Purpose -->
                    <div class="clr-section">
                        <div class="clr-section-label">
                            <i class="fas fa-bullseye"></i> Purpose
                        </div>
                        <select name="purpose" class="clr-select" required>
                            <option value="">-- Select purpose --</option>
                            <optgroup label="Employment">
                                <option>Employment / Job Application</option>
                                <option>Business Permit</option>
                            </optgroup>
                            <optgroup label="Education">
                                <option>School Enrollment</option>
                                <option>Scholarship Application</option>
                            </optgroup>
                            <optgroup label="Government / Legal">
                                <option>Government Transaction</option>
                                <option>Travel / Passport</option>
                                <option>Bank / Financial Requirement</option>
                                <option>Court Requirement</option>
                            </optgroup>
                            <optgroup label="Health / Social">
                                <option>Medical Assistance</option>
                                <option>Social Welfare (DSWD / 4Ps)</option>
                                <option>PhilHealth / SSS / GSIS</option>
                            </optgroup>
                            <option value="Other">Other</option>
                        </select>
                        <div id="purposeOtherWrap" hidden style="margin-top:10px;">
                            <label for="purposeOther" class="clr-section-label" style="margin-bottom:6px;">
                                Specify purpose <span style="color:#c0392b;">*</span>
                            </label>
                            <input type="text" name="purpose_other" id="purposeOther" class="clr-select" maxlength="255"
                                placeholder="Type the exact purpose of this request"
                                value="<?= esc(old('purpose_other') ?? '') ?>">
                        </div>
                    </div>

                    <!-- Step 4: Notes -->
                    <div class="clr-section" style="margin-bottom:18px;">
                        <div class="clr-section-label">
                            <i class="fas fa-sticky-note"></i> Additional Notes
                            <span style="font-size:10px;color:#b0b6cc;font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span>
                        </div>
                        <textarea name="notes" class="clr-textarea"
                            placeholder="Any additional information the barangay should know..."></textarea>
                    </div>

                    <!-- Info note -->
                    <div class="clr-info-note">
                        <i class="fas fa-clock"></i>
                        <span>Processing takes <strong>1–2 business days</strong>. You will be notified once your document is ready for pickup at the barangay hall.</span>
                    </div>

                    <button type="submit" class="clr-submit-btn" id="clearanceSubmitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        setTimeout(function() {
            const alert = document.getElementById('clearanceFlash');
            if (!alert) return;
            alert.classList.add('is-hidden');
            setTimeout(() => alert.remove(), 350);
        }, 5000);

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function confirmCancel(id, docType) {
            document.getElementById('cancelDocType').textContent = docType;
            document.getElementById('cancelForm').action = '/<?= $requestRole ?>/clearance/cancel/' + id;
            openModal('cancelModal');
        }

        function selectMember(el, name, rel, isMinor) {
            document.querySelectorAll('.clr-member-pill').forEach(p => p.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('forMember').value = name;
            document.getElementById('memberRel').value = rel;
            applyDocumentRules(!!isMinor);
        }

        function selectDocument(card) {
            const input = card.querySelector('input[name="document_type"]');
            if (!input || input.disabled || card.classList.contains('ineligible')) return;

            document.querySelectorAll('.secretary-doc-card').forEach(item => item.classList.remove('selected'));
            card.classList.add('selected');
            input.checked = true;
        }

        const openRequestsByMember = <?= json_encode($openByMember ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const indigencyBlocked = <?= (float) ($householdTotalIncome ?? 0) > 12000 ? 'true' : 'false' ?>;
        const soloParentBlocked = <?= empty($isSoloParent) ? 'true' : 'false' ?>;
        const goodMoralCaseBlocked = <?= ! empty($goodMoralBlocked) ? 'true' : 'false' ?>;

        function applyDocumentRules(isMinor) {
            const memberName = (document.getElementById('forMember')?.value || '').trim().toLowerCase();
            const openDocs = openRequestsByMember[memberName] || [];
            const note = document.getElementById('secretaryEligibilityNote');

            document.querySelectorAll('.secretary-doc-card').forEach(card => {
                const input = card.querySelector('input[name="document_type"]');
                const reason = card.querySelector('.secretary-doc-reason');
                const type = card.dataset.document || '';
                if (!input) return;

                let message = '';
                if (card.dataset.adultOnly === '1' && isMinor) {
                    message = 'Not available for minors.';
                } else if (type === 'Certificate of Indigency' && indigencyBlocked) {
                    message = 'Household income exceeds the indigency limit.';
                } else if (type === 'Solo Parent Certificate' && soloParentBlocked) {
                    message = 'A validated Solo Parent record is required.';
                } else if (type === 'Certificate of Good Moral' && goodMoralCaseBlocked) {
                    message = 'Not available while a blotter case is filed for action.';
                } else if (openDocs.indexOf(type) !== -1) {
                    message = 'You already have an open request for this document.';
                }

                input.disabled = message !== '';
                card.classList.toggle('ineligible', message !== '');
                if (message !== '' && (card.classList.contains('selected') || input.checked)) {
                    card.classList.remove('selected');
                    input.checked = false;
                }
                if (reason) reason.textContent = message;
            });

            if (note) {
                if (isMinor) {
                    note.textContent = 'Documents that do not apply to minors are turned off.';
                } else if (indigencyBlocked) {
                    note.textContent = 'Certificate of Indigency is unavailable because household income exceeds the limit.';
                } else {
                    note.textContent = 'Select the document you want to request.';
                }
            }
        }

        applyDocumentRules(document.querySelector('.clr-member-pill.selected')?.dataset.minor === '1');

        const purposeSelect = document.querySelector('#clearanceForm [name="purpose"]');
        const purposeOtherWrap = document.getElementById('purposeOtherWrap');
        const purposeOther = document.getElementById('purposeOther');

        function togglePurposeOther() {
            const isOther = purposeSelect && purposeSelect.value === 'Other';
            if (purposeOtherWrap) purposeOtherWrap.hidden = !isOther;
            if (purposeOther) purposeOther.required = !!isOther;
        }
        if (purposeSelect) {
            purposeSelect.addEventListener('change', togglePurposeOther);
            togglePurposeOther();
        }

        const clearanceForm = document.getElementById('clearanceForm');
        if (clearanceForm) {
            clearanceForm.addEventListener('submit', function (event) {
                const note = clearanceForm.querySelector('[data-offline-sync-status]');
                const chosen = clearanceForm.querySelector('input[name="document_type"]:checked:not(:disabled)');
                const purpose = clearanceForm.querySelector('[name="purpose"]');
                const otherMissing = purpose && purpose.value === 'Other' && purposeOther && purposeOther.value.trim() === '';
                if (!chosen || (purpose && purpose.value === '') || otherMissing) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    clearanceForm.dataset.bisSaving = '';
                    const button = document.getElementById('clearanceSubmitBtn');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request';
                    }
                    if (note) {
                        note.style.color = '#b42318';
                        note.textContent = !chosen
                            ? 'Choose a document type before submitting.'
                            : (otherMissing
                                ? 'Specify the exact purpose of your request.'
                                : 'Choose a purpose before submitting.');
                    }
                    const target = !chosen ? document.getElementById('secretaryDocumentType') : (otherMissing ? purposeOther : purpose);
                    if (target && typeof target.scrollIntoView === 'function') {
                        target.scrollIntoView({ block: 'center' });
                    }
                    if (purpose && chosen && typeof purpose.reportValidity === 'function') {
                        purpose.reportValidity();
                    }
                    return;
                }
                if (note) {
                    note.style.color = '#16325c';
                    note.textContent = 'Submitting your request...';
                }
            }, true);
        }

        function filterRequests() {
            const input = document.getElementById('searchInput');
            if (!input) return;
            const q = input.value.toLowerCase();
            document.querySelectorAll('#requestsTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>