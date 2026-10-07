<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Request - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php
    $role      = $role ?? 'captain';
    $active    = 'clearance';
    $pageTitle = 'Clearance Request';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $backUrl  = '/' . $role . '/clearance';
    $user     = $user     ?? [];
    $household = $household ?? null;
    $requests = $requests ?? [];
    $censusRecord = $censusRecord ?? null;
    $canEditDocument = in_array($role, ['secretary', 'captain', 'admin'], true);

    // Build resident display info from census
    $resName    = esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Unknown');
    $initial    = strtoupper(($user['first_name'] ?? 'U')[0]);

    // Preferred source: matched censusRecord (correct person, head or member)
    // Fallback: household head (legacy behaviour when matching unavailable)
    $src = $censusRecord ?? $household;

    $address    = $src ? esc($src['address'] ?? ($src['zone'] ?? '—')) : '—';
    $contact    = $src ? esc($src['contact_number'] ?? '—') : '—';
    $gender     = $src ? esc($src['gender'] ?? '—') : '—';
    $civil      = $src ? esc($src['civil_status'] ?? '—') : '—';
    $occupation = $src ? esc($src['occupation'] ?? '—') : '—';
    $age        = ($src && !empty($src['date_of_birth']))
        ? (int)date_diff(date_create($src['date_of_birth']), date_create('today'))->y
        : '—';
    $zone       = $src ? esc($src['zone'] ?? '') : '';

    // Currently selected request (first pending, or first overall)
    $activeReq  = null;
    foreach ($requests as $r) {
        if ($r['status'] === 'pending') {
            $activeReq = $r;
            break;
        }
    }
    if (! $activeReq && ! empty($requests)) $activeReq = $requests[0];

    $docKeyMap = [
        'Barangay Clearance'       => 'clearance',
        'Certificate of Residency' => 'residency',
        'Certificate of Indigency' => 'indigency',
        'Certificate of Good Moral' => 'good_moral',
        'First Time Job Seekers'   => 'first_time_job_seeker',
        'First Time Job Seeker'    => 'first_time_job_seeker',
        'Solo Parent Certificate'  => 'solo_parent',
        'Business Permit Clearance' => 'business_permit',
        'Other Document'           => 'other_document',
    ];
    $feeMap = \Config\ClearanceDocuments::feeMap();
    $badgeMap = [
        'pending'  => 'db-badge--pending',
        'approved' => 'db-badge--approved',
        'released' => 'db-badge--info',
        'rejected' => 'db-badge--rejected',
    ];

    $requestsByDate = [];
    foreach ($requests as $request) {
        $dateKey = (new DateTimeImmutable($request['created_at'], new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Manila'))
            ->format('Y-m-d');
        $requestsByDate[$dateKey][] = $request;
    }
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--danger" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <!-- Breadcrumb -->
            <div class="hh-breadcrumb" style="margin-bottom:20px;">
                <a href="<?= $backUrl ?>" class="hh-back"><i class="fas fa-arrow-left"></i> Back to Clearance</a>
                <span class="hh-bc-sep">/</span>
                <span><?= $resName ?></span>
            </div>

            <!-- Resident header card -->
            <div class="cld-header-card">
                <div class="cld-avatar"><?= $initial ?></div>
                <div class="cld-header-info">
                    <h2><?= $resName ?></h2>
                    <div class="cld-header-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?= $address ?></span>
                        <span><i class="fas fa-phone"></i> <?= $contact ?></span>
                        <span><i class="fas fa-venus-mars"></i> <?= $gender ?>, <?= $age ?> yrs</span>
                        <span><i class="fas fa-heart"></i> <?= $civil ?></span>
                        <span><i class="fas fa-briefcase"></i> <?= $occupation ?></span>
                    </div>
                </div>
                <div class="cld-header-right">
                    <span style="font-size:12px;color:#9aa0b4;"><?= count($requests) ?> total request<?= count($requests) != 1 ? 's' : '' ?></span>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="cld-grid">

                <!-- LEFT: All requests list -->
                <div class="cld-col">
                    <div class="cld-card">
                        <h4 class="cld-card-title"><i class="fas fa-list-alt"></i> All Requests</h4>

                        <?php if (empty($requests)): ?>
                            <p style="color:#9aa0b4;font-size:13px;">No requests found for this resident.</p>
                            <?php else: foreach ($requestsByDate as $dateKey => $dateRequests):
                                $dateLabel = (new DateTimeImmutable($dateKey, new DateTimeZone('Asia/Manila')))
                                    ->format('F j, Y');
                                $groupId = 'request-date-' . str_replace('-', '', $dateKey);
                            ?>
                                <div class="request-date-group">
                                    <button type="button" class="request-date-toggle" onclick="toggleRequestDate('<?= $groupId ?>', this)" aria-expanded="<?= $dateKey === array_key_first($requestsByDate) ? 'true' : 'false' ?>">
                                        <span><i class="fas fa-calendar-day"></i> <?= esc($dateLabel) ?> <small><?= count($dateRequests) ?> request<?= count($dateRequests) === 1 ? '' : 's' ?></small></span>
                                        <i class="fas fa-chevron-down request-date-chevron"></i>
                                    </button>
                                    <div id="<?= $groupId ?>" class="request-date-list" <?= $dateKey === array_key_first($requestsByDate) ? '' : 'hidden' ?>>
                                        <?php foreach ($dateRequests as $r):
                                            $rBadge   = $badgeMap[$r['status']] ?? 'db-badge--pending';
                                            $rFiled   = notification_time($r['created_at'] ?? null);
                                            $rRelease = $r['est_release_date'] ? date('M d, Y', strtotime($r['est_release_date'])) : '—';
                                            $rDocKey  = $docKeyMap[$r['document_type']] ?? 'clearance';
                                            $rFee     = esc($feeMap[$r['document_type']] ?? '—');
                                            $isActive = $activeReq && $activeReq['id'] === $r['id'];
                                        ?>
                                            <div class="req-item <?= $isActive ? 'req-item--active' : '' ?>"
                                                onclick="selectRequest(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>, '<?= $rDocKey ?>', '<?= $rFee ?>')">
                                                <div class="req-item-top">
                                                    <strong>#<?= str_pad((int)($r['daily_request_number'] ?? 0), 3, '0', STR_PAD_LEFT) ?></strong>
                                                    <span class="db-badge <?= $rBadge ?>"><?= ucfirst($r['status']) ?></span>
                                                </div>
                                                <div class="req-item-doc"><?= esc($r['document_type']) ?></div>
                                                <div class="req-item-meta">
                                                    <span><i class="fas fa-user"></i> <?= esc($r['for_member']) ?> (<?= esc($r['member_relationship'] ?? '') ?>)</span>
                                                    <span><i class="fas fa-bullseye"></i> <?= esc($r['purpose']) ?></span>
                                                    <span><i class="fas fa-calendar"></i> Filed: <?= $rFiled ?></span>
                                                </div>
                                                <?php if ($r['status'] === 'pending'): ?>
                                                    <div class="req-item-actions" onclick="event.stopPropagation()">
                                                        <form action="/<?= $role ?>/clearance/approve/<?= $r['id'] ?>" method="post" style="display:inline;">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="db-btn db-btn--xs db-btn--success"><i class="fas fa-check"></i> Approve</button>
                                                        </form>
                                                        <button class="db-btn db-btn--xs db-btn--danger"
                                                            onclick="openRejectModal(<?= $r['id'] ?>, '<?= $role ?>')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </div>
                                                <?php elseif ($r['status'] === 'approved'): ?>
                                                    <div class="req-item-actions" onclick="event.stopPropagation()">
                                                        <form action="/<?= $role ?>/clearance/release/<?= $r['id'] ?>" method="post" style="display:inline;">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="db-btn db-btn--xs db-btn--success"><i class="fas fa-box-open"></i> Release</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- RIGHT: Document preview (auto-filled) -->
                <div class="cld-col">
                    <div class="cld-card">
                        <div class="cld-card-title-row">
                            <h4 class="cld-card-title" style="margin:0;"><i class="fas fa-file-contract"></i> Document Preview</h4>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <button class="db-btn db-btn--sm db-btn--outline" onclick="downloadCurrentDoc()">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="db-btn db-btn--sm db-btn--primary" onclick="printCurrentDoc()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                        <div id="docInfo" style="background:#f8f9fc;border:1px solid #e8ecf4;border-radius:8px;padding:12px 14px;margin-bottom:12px;font-size:12.5px;color:#4a5068;">
                            <?php if ($activeReq): ?>
                                <strong><?= esc($activeReq['document_type']) ?></strong> for
                                <strong><?= esc($activeReq['for_member']) ?></strong> —
                                Purpose: <?= esc($activeReq['purpose']) ?> —
                                Fee: <?= esc($feeMap[$activeReq['document_type']] ?? '—') ?>
                            <?php else: ?>
                                Select a request on the left to preview the document.
                            <?php endif; ?>
                        </div>
                        <?php if ($canEditDocument): ?>
                            <div class="cld-editor cld-editor--hidden" id="documentEditor">
                                <div class="cld-editor-head">
                                    <div>
                                        <span><i class="fas fa-edit"></i> Edit document content</span>
                                        <small>Saved wording and formatting appear on preview, print, and download.</small>
                                    </div>
                                    <button type="button" class="cld-editor-toggle" id="documentEditorToggle" onclick="toggleDocumentEditor()">
                                        <i class="fas fa-eye"></i> View editor
                                    </button>
                                </div>
                                <div class="cld-editor-body" id="documentEditorBody">
                                    <label>Document title<input id="editDocTitle" type="text"></label>
                                    <label>Salutation<input id="editDocSalutation" type="text"></label>
                                    <label>Document body
                                        <?php include APPPATH . 'Views/dashboard/partials/document_editor_toolbar.php'; ?>
                                        <div id="editDocBody" class="doc-editor-area" contenteditable="true"></div>
                                    </label>
                                    <div class="cld-editor-grid">
                                        <label>Signature label<input id="editDocSignatureLabel" type="text"></label>
                                        <label>Signature title<input id="editDocSignatureTitle" type="text"></label>
                                    </div>
                                    <div class="cld-editor-actions">
                                        <button type="button" class="db-btn db-btn--sm db-btn--outline" onclick="resetDocumentEditor()"><i class="fas fa-undo"></i> Reset</button>
                                        <button type="button" class="db-btn db-btn--sm db-btn--primary" onclick="applyDocumentEdits()"><i class="fas fa-check"></i> Save &amp; apply</button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="cld-doc-preview" id="docPreviewArea">
                            <?php if ($activeReq):
                                $docKey = $docKeyMap[$activeReq['document_type']] ?? 'clearance';
                                $snapshotCaptain = addslashes($activeReq['issued_captain_name'] ?? '');
                                $snapshotDate    = addslashes($activeReq['issued_date'] ?? '');
                                echo '<script>document.addEventListener("DOMContentLoaded",function(){ renderCurrentDoc(); loadDocumentEditor(); });</script>';
                            endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="db-modal-overlay" id="rejectModal">
        <div class="db-modal" style="max-width:420px;">
            <div class="db-modal-header">
                <h3><i class="fas fa-times-circle"></i> Reject Request</h3>
                <button class="db-modal-close" onclick="document.getElementById('rejectModal').classList.remove('active')"><i class="fas fa-times"></i></button>
            </div>
            <form id="rejectForm" method="post">
                <?= csrf_field() ?>
                <div class="db-modal-body">
                    <div class="db-form-group db-form-group--full">
                        <label>Reason for Rejection <span style="color:#dc3545;">*</span></label>
                        <textarea name="remarks" rows="3" placeholder="State the reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="db-modal-footer">
                    <button type="button" class="db-btn db-btn--outline" onclick="document.getElementById('rejectModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="db-btn db-btn--danger"><i class="fas fa-times"></i> Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* ── Request items ── */
        .req-item {
            border: 1.5px solid #e8ecf4;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }

        .req-item:hover {
            border-color: #1d2448;
            background: #f8f9ff;
        }

        .req-item--active {
            border-color: #1d2448;
            background: #f0f2ff;
        }

        .req-item-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .req-item-doc {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a1d2e;
            margin-bottom: 6px;
        }

        .req-item-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
            font-size: 11.5px;
            color: #9aa0b4;
        }

        .req-item-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .req-item-meta i {
            font-size: 10px;
        }

        .req-item-actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
        }

        .request-date-group {
            margin-bottom: 12px;
        }

        .request-date-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #e8ecf4;
            border-radius: 8px;
            background: #f8f9fc;
            color: #1d2448;
            padding: 9px 11px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
        }

        .request-date-toggle small {
            color: #9aa0b4;
            font-weight: 500;
            margin-left: 5px;
        }

        .request-date-chevron {
            transition: transform .2s ease;
        }

        .request-date-toggle[aria-expanded="true"] .request-date-chevron {
            transform: rotate(180deg);
        }

        .request-date-list {
            padding-top: 8px;
        }

        /* ── Header card ── */
        .cld-header-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 14px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .cld-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #1d2448;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cld-header-info {
            flex: 1;
            min-width: 200px;
        }

        .cld-header-info h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0 0 8px;
        }

        .cld-header-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12px;
            color: #6b7280;
        }

        .cld-header-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cld-header-meta i {
            color: #b0b6cc;
            font-size: 11px;
        }

        .cld-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        /* ── Grid ── */
        .cld-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 20px;
            align-items: start;
        }

        .cld-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── Cards ── */
        .cld-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 14px;
            padding: 20px 22px;
        }

        .cld-card-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1d2e;
            margin: 0 0 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cld-card-title i {
            color: #9aa0b4;
        }

        .cld-card-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        /* ── Detail list ── */
        .cld-detail-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .cld-detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 9px 0;
            border-bottom: 1px solid #f5f6fa;
        }

        .cld-detail-item span:first-child {
            font-size: 10px;
            font-weight: 600;
            color: #9aa0b4;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .cld-detail-item strong {
            font-size: 13px;
            color: #1a1d2e;
        }

        /* ── Notes ── */
        .cld-notes {
            font-size: 13px;
            color: #555;
            line-height: 1.75;
            margin: 0;
            background: #f8f9fc;
            border: 1px solid #e8ecf4;
            border-radius: 8px;
            padding: 12px;
        }

        /* ── Action card ── */
        .cld-action-row {
            display: flex;
            gap: 10px;
        }

        /* ── Document preview ── */
        .cld-doc-preview {
            background: #f0f0f0;
            border-radius: 8px;
            overflow: auto;
            max-height: 680px;
            padding: 12px 0;
        }

        .cld-editor {
            background: #f8f9fc;
            border: 1px solid #e2e5ef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .cld-editor-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            color: #1d2448;
            font-size: 12px;
            font-weight: 700;
        }

        .cld-editor-head>div {
            min-width: 0;
        }

        .cld-editor-head small {
            color: #9aa0b4;
            font-weight: 400;
            margin-left: 8px;
        }

        .cld-editor-toggle {
            border: 0;
            background: transparent;
            color: #5b6fd6;
            cursor: pointer;
            font: 600 11px 'Poppins', sans-serif;
            white-space: nowrap;
        }

        .cld-editor-toggle:hover {
            color: #1d2448;
        }

        .cld-editor-body {
            margin-top: 10px;
        }

        .cld-editor--hidden .cld-editor-body {
            display: none;
        }

        .cld-editor label {
            display: block;
            color: #4a5068;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .cld-editor input,
        .cld-editor textarea {
            display: block;
            width: 100%;
            box-sizing: border-box;
            margin-top: 4px;
            padding: 7px 9px;
            border: 1px solid #dfe3ee;
            border-radius: 6px;
            color: #1d2448;
            background: #fff;
            font: 12px/1.45 inherit;
            text-transform: none !important;
            resize: vertical;
        }

        .cld-editor textarea {
            min-height: 54px;
        }

        .cld-editor-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .cld-editor-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 4px;
        }

        /* ── Breadcrumb ── */
        .hh-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #9aa0b4;
        }

        .hh-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #1d2448;
            text-decoration: none;
            font-weight: 500;
            transition: opacity .2s;
        }

        .hh-back:hover {
            opacity: .7;
        }

        .hh-bc-sep {
            color: #d0d5e8;
        }

        /* ── Print ── */
        @media print {

            .db-sidebar,
            .db-topbar,
            .hh-breadcrumb,
            .cld-header-right,
            .cld-col:first-child,
            .db-modal-overlay {
                display: none !important;
            }

            .db-main {
                margin-left: 0 !important;
            }

            .cld-grid {
                grid-template-columns: 1fr;
            }

            .cld-doc-preview {
                max-height: none;
                background: #fff;
                padding: 0;
            }
        }

        @media (max-width: 1024px) {
            .cld-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script src="/js/doc-templates.js"></script>
    <script src="/js/document-editor.js"></script>
    <script>
        // Per-request census map (request_id → census fields for the for_member person).
        // Ensures the document preview uses the correct civil status, zone, age etc.
        // for the specific member the request is for — not always the account holder.
        const _requestCensus = <?= json_encode($requestCensus ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const _requestContents = <?= json_encode($requestContents ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const _typeContents = <?= json_encode($typeContents ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const _contentSaveUrl = <?= json_encode(site_url(($role ?? 'captain') . '/clearance/content/')) ?>;
        const _csrfName = <?= json_encode(csrf_token()) ?>;
        const _csrfHash = <?= json_encode(csrf_hash()) ?>;

        function applyRequestCensus(requestId) {
            if (_requestCensus && _requestCensus[requestId]) {
                BisDoc.setCensus(_requestCensus[requestId]);
            }
        }

        // Set census data for the template engine (defaults: the resident account holder)
        BisDoc.setCensus({
            name: '<?= addslashes($resName) ?>',
            age: '<?= $age ?>',
            gender: '<?= addslashes($gender) ?>',
            civil: '<?= addslashes($civil) ?>',
            address: '<?= addslashes($address) ?>',
            zone: '<?= addslashes($zone) ?>',
            occupation: '<?= addslashes($occupation) ?>',
        });
        // Set the active Punong Barangay name
        BisDoc.setCaptain(<?= json_encode($captainName ?? '') ?>);
        BisDoc.setTypeContents(_typeContents);
        // Set dynamic barangay identity (from barangay_settings table)
        BisDoc.setBarangay(<?= json_encode([
                                'barangay_name' => $barangaySettings['barangay_name'] ?? 'BARANGAY BACOLOD',
                                'municipality'  => $barangaySettings['municipality']  ?? 'Municipality of Bato',
                                'province'      => $barangaySettings['province']      ?? 'Province of Camarines Sur',
                                'region'        => $barangaySettings['region']        ?? 'Region V',
                                'country'       => $barangaySettings['country']       ?? 'Republic of the Philippines',
                                'full_address'  => $barangaySettings['full_address']  ?? 'Barangay Bacolod, Bato, Camarines Sur',
                                'office_header' => $barangaySettings['office_header'] ?? 'OFFICE OF THE PUNONG BARANGAY',
                                'captain_name'  => $captainName ?? ($barangaySettings['captain_name'] ?? ''),
                                'secretary_name'=> $barangaySettings['secretary_name'] ?? '',
                                'captain_title' => $barangaySettings['captain_title'] ?? 'Punong Barangay',
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);

        // Track currently displayed doc for print
        let _currentDoc = {
            key: '<?= $docKeyMap[$activeReq['document_type'] ?? 'Barangay Clearance'] ?? 'clearance' ?>',
            id: <?= (int)($activeReq['id'] ?? 0) ?>,
            member: '<?= addslashes($activeReq['for_member'] ?? $resName) ?>',
            purpose: '<?= addslashes($activeReq['purpose'] ?? '') ?>',
            snapshotCaptain: '<?= addslashes($activeReq['issued_captain_name'] ?? '') ?>',
            snapshotDate: '<?= addslashes($activeReq['issued_date'] ?? '') ?>',
        };

        function getDocumentDate() {
            const source = _currentDoc.snapshotDate ? new Date(_currentDoc.snapshotDate + 'T00:00:00') : new Date();
            return {
                day: source.getDate(),
                month: source.toLocaleString('default', {
                    month: 'long'
                }),
                year: source.getFullYear(),
            };
        }

        function applyDocSnapshot() {
            if (_currentDoc.snapshotDate) {
                BisDoc.setSnapshot(_currentDoc.snapshotCaptain, _currentDoc.snapshotDate);
            } else {
                BisDoc.setSnapshot(null, null);
            }
        }

        function collectEditorContent() {
            return {
                title: document.getElementById('editDocTitle').value,
                salutation: document.getElementById('editDocSalutation').value,
                body_html: DocumentEditor.html(document.getElementById('editDocBody')),
                signature_label: document.getElementById('editDocSignatureLabel').value,
                signature_title: document.getElementById('editDocSignatureTitle').value,
            };
        }

        function renderCurrentDoc() {
            BisDoc.setTypeContents(_typeContents);
            BisDoc.setContent(_requestContents[_currentDoc.id] || null);
            applyDocSnapshot();
            document.getElementById('docPreviewArea').innerHTML = BisDoc.build(_currentDoc.key, _currentDoc.member, _currentDoc.purpose);
        }

        function saveDocumentContent(content, reset) {
            if (!_currentDoc.id) return Promise.resolve();
            const body = new URLSearchParams();
            body.set(_csrfName, _csrfHash);
            if (reset) {
                body.set('reset', '1');
            } else {
                Object.entries(content).forEach(([key, value]) => body.set(key, value || ''));
            }
            return fetch(_contentSaveUrl + _currentDoc.id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body,
            }).then(response => response.json()).catch(() => ({ ok: false }));
        }

        function loadDocumentEditor() {
            const editor = document.getElementById('documentEditor');
            if (!editor || !_currentDoc) return;

            applyDocSnapshot();
            const content = _requestContents[_currentDoc.id] || BisDoc.editableContent(
                _currentDoc.key,
                _currentDoc.member,
                (_requestCensus?.[_currentDoc.id]?.civil || 'Single'),
                (_requestCensus?.[_currentDoc.id]?.zone || ''),
                _currentDoc.purpose,
                getDocumentDate()
            );
            document.getElementById('editDocTitle').value = content.title || '';
            document.getElementById('editDocSalutation').value = content.salutation || '';
            document.getElementById('editDocSignatureLabel').value = content.signature_label || content.signatureLabel || '';
            document.getElementById('editDocSignatureTitle').value = content.signature_title || content.signatureTitle || '';
            DocumentEditor.setHtml(document.getElementById('editDocBody'), content.body_html || '');
        }

        function toggleDocumentEditor() {
            const editor = document.getElementById('documentEditor');
            const toggle = document.getElementById('documentEditorToggle');
            if (!editor || !toggle) return;

            const hidden = editor.classList.toggle('cld-editor--hidden');
            toggle.innerHTML = hidden ?
                '<i class="fas fa-eye"></i> View editor' :
                '<i class="fas fa-eye-slash"></i> Hide editor';
        }

        function applyDocumentEdits() {
            const editor = document.getElementById('documentEditor');
            if (!editor) return;
            const content = collectEditorContent();
            _requestContents[_currentDoc.id] = content;
            renderCurrentDoc();
            saveDocumentContent(content, false);
        }

        function resetDocumentEditor() {
            _requestContents[_currentDoc.id] = null;
            BisDoc.setContent(null);
            applyDocSnapshot();
            loadDocumentEditor();
            renderCurrentDoc();
            saveDocumentContent({}, true);
        }

        // Immediately override with the active request's census data so the
        // initial document preview (and print) uses the correct member's info.
        <?php if ($activeReq): ?>
            applyRequestCensus(<?= (int)$activeReq['id'] ?>);
        <?php endif; ?>

        function selectRequest(r, docKey, fee) {
            document.querySelectorAll('.req-item').forEach(el => el.classList.remove('req-item--active'));
            event.currentTarget.classList.add('req-item--active');
            document.getElementById('docInfo').innerHTML =
                `<strong>${r.document_type}</strong> for <strong>${r.for_member}</strong> — Purpose: ${r.purpose} — Fee: ${fee}`;
            _currentDoc = {
                key: docKey,
                member: r.for_member,
                purpose: r.purpose,
                snapshotCaptain: r.issued_captain_name || '',
                snapshotDate: r.issued_date || '',
            };
            // Switch census context to the specific person this request is for
            applyRequestCensus(r.id);
            _currentDoc.id = r.id;
            renderCurrentDoc();
            loadDocumentEditor();
        }

        function toggleRequestDate(groupId, button) {
            const group = document.getElementById(groupId);
            if (!group) return;
            const isOpen = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            group.hidden = isOpen;
        }

        function prepareCurrentDoc() {
            if (_currentDoc && typeof _currentDoc.key !== 'undefined') {
                const reqId = Object.keys(_requestCensus || {}).find(id => {
                    const rc = _requestCensus[id];
                    return rc.name && rc.name.toLowerCase() === String(_currentDoc.member || '').toLowerCase();
                });
                if (reqId) applyRequestCensus(reqId);
            }
            if (document.getElementById('editDocTitle')) {
                _requestContents[_currentDoc.id] = collectEditorContent();
            }
            BisDoc.setTypeContents(_typeContents);
            BisDoc.setContent(_requestContents[_currentDoc.id] || null);
            applyDocSnapshot();
        }

        function printCurrentDoc() {
            prepareCurrentDoc();
            BisDoc.print(_currentDoc.key, _currentDoc.member, _currentDoc.purpose);
        }

        function downloadCurrentDoc() {
            prepareCurrentDoc();
            BisDoc.download(_currentDoc.key, _currentDoc.member, _currentDoc.purpose);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('editDocBody');
            const toolbar = document.querySelector('#documentEditor .doc-editor-toolbar');
            if (editor && toolbar) {
                DocumentEditor.bindToolbar(toolbar, editor);
            }
        });

        function openRejectModal(id, role) {
            document.getElementById('rejectForm').action = '/' + role + '/clearance/reject/' + id;
            document.getElementById('rejectModal').classList.add('active');
        }

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>