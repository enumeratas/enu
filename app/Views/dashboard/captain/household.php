<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php
    $ownershipDocumentPath = \App\Controllers\HouseholdUploadController::normalizeUploadPath($head['ownership_document_path'] ?? null);
    $ownershipDocumentUrl = '';
    if ($ownershipDocumentPath !== '') {
        $ownershipRoutePath = preg_replace('#^uploads/#i', '', ltrim($ownershipDocumentPath, '/'));
        $ownershipDocumentUrl = site_url('household-files/' . implode('/', array_map('rawurlencode', explode('/', $ownershipRoutePath))));
    }
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Members - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php
    $role        = $role ?? 'captain';
    $active      = 'census';
    $pageTitle   = 'Household Members';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $householdId = $householdId ?? '—';
    $head        = $household   ?? [];
    $headName    = trim(($head['first_name'] ?? '') . ' ' . ($head['last_name'] ?? ''));
    $headAddress = $head['address'] ?? ($head['zone'] ?? '—');
    $headContact = $head['contact_number'] ?? '—';
    $headGender  = $head['gender'] ?? '—';
    $headDob     = !empty($head['date_of_birth']) ? date('M d, Y', strtotime($head['date_of_birth'])) : '—';
    $headDobVal  = $head['date_of_birth'] ?? '';
    $headAge     = !empty($head['date_of_birth']) ? (int)date_diff(date_create($head['date_of_birth']), date_create('today'))->y : '—';
    $headCivil   = $head['civil_status'] ?? '—';
    $members     = $members ?? [];
    $hasSpouse   = $hasSpouse ?? count(array_filter($members, static fn($member) => strtolower((string) ($member['relationship'] ?? '')) === 'spouse')) > 0;

    // Education options
    $eduOptions = ['No Formal Education', 'Elementary Level', 'Elementary Graduate', 'High School Level', 'High School Graduate', 'College Level', 'College Graduate', 'Vocational / Tech-Voc', 'Post Graduate'];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <div class="hh-breadcrumb">
                <a href="/<?= esc($role) ?>/census" class="hh-back"><i class="fas fa-arrow-left"></i> Back to Census Records</a>
                <span class="hh-bc-sep">/</span>
                <span>Household #<?= esc($householdId) ?></span>
            </div>
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

            <!-- Head card -->
            <div class="hh-head-card <?= !empty($head['is_deceased']) ? 'hh-head-card--deceased' : '' ?>">
                <div class="hh-head-avatar" style="<?= !empty($head['is_deceased']) ? 'background:#9aa0b4;' : '' ?>"><?= strtoupper($head['first_name'][0] ?? '?') ?></div>
                <div class="hh-head-info">
                    <h2>
                        <?= esc($headName) ?>
                        <span class="hh-head-badge">Household Head</span>
                        <?php if (!empty($head['is_deceased'])): ?>
                            <span style="background:#f8d7da;color:#842029;border:1px solid #f5c2c7;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;margin-left:4px;">
                                <i class="fas fa-cross" style="font-size:9px;"></i>
                                Deceased<?= !empty($head['year_of_death']) ? ' (' . esc($head['year_of_death']) . ')' : '' ?>
                            </span>
                        <?php endif; ?>
                    </h2>
                    <div class="hh-head-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?= esc($head['zone'] ?? '—') ?></span>
                        <span><i class="fas fa-phone"></i> <?= esc($headContact) ?></span>
                        <span><i class="fas fa-venus-mars"></i> <?= esc($headGender) ?>, <?= $headAge ?> yrs</span>
                        <span><i class="fas fa-heart"></i> <?= esc($headCivil) ?></span>
                        <span><i class="fas fa-birthday-cake"></i> <?= $headDob ?></span>
                        <span><i class="fas fa-clock"></i> <?= (int) current_years_of_residency($head) ?> years in the barangay</span>
                    </div>
                </div>
                <div class="hh-head-actions">
                    <button class="db-btn db-btn--outline db-btn--sm" onclick="openModal('editHeadModal')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="db-btn db-btn--primary db-btn--sm" onclick="openModal('addMemberModal')">
                        <i class="fas fa-user-plus"></i> Add Member
                    </button>
                    <?php if (empty($head['is_deceased'])): ?>
                        <button class="db-btn db-btn--sm"
                            style="background:#fff0f0;color:#842029;border:1.5px solid #f5c2c7;"
                            onclick="openDeceasedHeadModal()">
                            <i class="fas fa-cross" style="font-size:10px;"></i> Mark Deceased
                        </button>
                    <?php else: ?>
                        <form method="post" action="/<?= esc($role) ?>/census/head/undeceased/<?= esc($householdId) ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="db-btn db-btn--sm"
                                style="background:#f0fff4;color:#166534;border:1.5px solid #bbf7d0;"
                                onclick="return confirm('Clear deceased status for this household head?')">
                                <i class="fas fa-undo"></i> Clear Deceased
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (($head['record_status'] ?? 'complete') === 'draft'): ?>
                <div style="background:#fff8e8;border:1px solid #f3d48a;color:#8a5a00;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:13px;">
                    <i class="fas fa-file-alt"></i>
                    This household is saved as a draft. Each member still needs one ID or birth certificate before the record is complete.
                </div>
            <?php endif; ?>

            <!-- Ownership info strip + pending change alert + history (secretary only) -->
            <?php
            $currentOwnership = $head['house_ownership'] ?? 'Owned';
            $ownershipBadgeColor = match ($currentOwnership) {
                'Owned'            => '#16a085',
                'Rented'           => '#e67e22',
                'Shared'           => '#8e44ad',
                default            => '#4a5068',
            };
            ?>
            <?php $sharedFamilies = $sharedFamilies ?? []; ?>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:14px;">
                <span style="font-size:12px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;">House Ownership</span>
                <span style="background:<?= $ownershipBadgeColor ?>18;border:1px solid <?= $ownershipBadgeColor ?>44;color:<?= $ownershipBadgeColor ?>;font-size:12.5px;font-weight:700;padding:3px 12px;border-radius:20px;">
                    <?= esc($currentOwnership) ?>
                </span>
                <?php if ($currentOwnership === 'Shared'): ?>
                    <span style="font-size:12px;color:#8e44ad;font-weight:600;">
                        <i class="fas fa-users" style="font-size:11px;"></i>
                        <?= esc($head['num_families'] ?? 1) ?> <?= ($head['num_families'] ?? 1) == 1 ? 'family' : 'families' ?> sharing
                    </span>
                    <span style="background:#8e44ad18;border:1px solid #8e44ad44;color:#8e44ad;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                        Family <?= esc($head['family_number'] ?? 1) ?>
                    </span>
                    <span style="font-size:11px;color:#9aa0b4;font-family:monospace;">
                        <?= esc($head['shared_address_group'] ?? '') ?>
                    </span>
                    <?php if (! empty($head['linked_household_no'])): ?>
                        <span style="font-size:12px;font-weight:700;color:#6b21a8;">
                            Linked to household <?= esc($head['linked_household_no']) ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($head['ownership_notes'])): ?>
                    <span style="font-size:11.5px;color:#6b7280;font-style:italic;">
                        <i class="fas fa-sticky-note" style="margin-right:3px;"></i><?= esc($head['ownership_notes']) ?>
                    </span>
                <?php endif; ?>
                <?php if ($ownershipDocumentUrl !== ''): ?>
                    <a href="<?= esc($ownershipDocumentUrl) ?>"
                        target="_blank"
                        style="font-size:11.5px;color:#5b6fd6;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                        <i class="fas fa-paperclip"></i> View Proof Document
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($currentOwnership === 'Shared' && !empty($sharedFamilies)): ?>
                <div style="background:#fdf5ff;border:1.5px solid #c9a0e8;border-radius:12px;padding:14px 18px;margin-bottom:16px;">
                    <div style="font-size:12.5px;font-weight:700;color:#6b21a8;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-home"></i>
                        Other Families at this Address
                        <span style="background:#8e44ad;color:#fff;font-size:11px;font-weight:700;padding:1px 8px;border-radius:10px;"><?= count($sharedFamilies) ?></span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        <?php foreach ($sharedFamilies as $fam): ?>
                            <a href="/<?= esc($role) ?>/household/<?= esc($fam['household_no']) ?>"
                                style="display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid #c9a0e8;border-radius:10px;padding:10px 14px;text-decoration:none;color:#1a1d2e;"
                                onmouseover="this.style.boxShadow='0 2px 8px rgba(142,68,173,.2)'"
                                onmouseout="this.style.boxShadow=''">
                                <span style="background:#8e44ad;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;">
                                    Family <?= esc($fam['family_number'] ?? '?') ?>
                                </span>
                                <div>
                                    <div style="font-size:13px;font-weight:600;"><?= esc($fam['first_name'] . ' ' . $fam['last_name']) ?></div>
                                    <div style="font-size:11px;color:#9aa0b4;">HH #<?= esc($fam['household_no']) ?><?= !empty($fam['zone']) ? ' · ' . esc($fam['zone']) : '' ?></div>
                                </div>
                                <i class="fas fa-chevron-right" style="font-size:10px;color:#c9a0e8;margin-left:auto;"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($currentOwnership === 'Shared' && empty($sharedFamilies)): ?>
                <div style="background:#fdf5ff;border:1.5px dashed #c9a0e8;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12.5px;color:#8e44ad;">
                    <i class="fas fa-info-circle" style="margin-right:5px;"></i>
                    No other families linked yet.
                    <?php if ($role === 'secretary'): ?>
                        Register others using Group Code: <strong style="font-family:monospace;"><?= esc($head['shared_address_group'] ?? '—') ?></strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($ownershipChanges)): ?>
                <!-- Pending change alert -->
                <?php
                $pending = array_filter($ownershipChanges, fn($c) => $c['status'] === 'pending');
                $pending = array_values($pending);
                ?>
                <?php if (!empty($pending)): ?>
                    <div style="background:#fff8e6;border:1.5px solid #fde8a0;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px;">
                        <i class="fas fa-clock" style="color:#e67e22;margin-top:2px;"></i>
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:700;color:#7a4200;">Pending Ownership Change</div>
                            <div style="font-size:12.5px;color:#7a4200;margin-top:2px;">
                                A change from <strong><?= esc($pending[0]['old_ownership']) ?></strong>
                                to <strong><?= esc($pending[0]['new_ownership']) ?></strong> is awaiting secretary approval.
                                <?php if ($role === 'secretary'): ?>
                                    <form method="post" action="/secretary/census/ownership-change/approve/<?= $pending[0]['id'] ?>" style="display:inline;margin-left:8px;">
                                        <?= csrf_field() ?>
                                        <button type="submit" style="background:#16a085;color:#fff;border:none;border-radius:6px;padding:3px 10px;font-size:11.5px;font-weight:600;cursor:pointer;">Approve</button>
                                    </form>
                                    <form method="post" action="/secretary/census/ownership-change/reject/<?= $pending[0]['id'] ?>" style="display:inline;margin-left:4px;">
                                        <?= csrf_field() ?>
                                        <button type="submit" style="background:#c0392b;color:#fff;border:none;border-radius:6px;padding:3px 10px;font-size:11.5px;font-weight:600;cursor:pointer;">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Ownership change history (secretary only) -->
                <?php if ($role === 'secretary'): ?>
                    <details style="margin-bottom:16px;">
                        <summary style="font-size:12.5px;font-weight:700;color:#4a5068;cursor:pointer;padding:8px 12px;background:#f8f9fc;border:1px solid #e8ecf4;border-radius:8px;list-style:none;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-history" style="color:#9aa0b4;"></i>
                            Ownership Change History
                            <span style="background:#e8ecf4;color:#4a5068;font-size:11px;padding:1px 7px;border-radius:10px;margin-left:4px;"><?= count($ownershipChanges) ?></span>
                        </summary>
                        <div style="border:1px solid #e8ecf4;border-top:none;border-radius:0 0 8px 8px;overflow:hidden;">
                            <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                                <thead>
                                    <tr style="background:#f8f9fc;">
                                        <th style="padding:8px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:1px solid #e8ecf4;">Date</th>
                                        <th style="padding:8px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:1px solid #e8ecf4;">From</th>
                                        <th style="padding:8px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:1px solid #e8ecf4;">To</th>
                                        <th style="padding:8px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:1px solid #e8ecf4;">Changed By</th>
                                        <th style="padding:8px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:1px solid #e8ecf4;">Status</th>
                                        <th style="padding:8px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:1px solid #e8ecf4;">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ownershipChanges as $chg): ?>
                                        <tr style="border-bottom:1px solid #f0f2f8;">
                                            <td style="padding:8px 12px;color:#4a5068;">
                                                <?= !empty($chg['created_at']) ? date('M d, Y g:i a', strtotime($chg['created_at'])) : '—' ?>
                                            </td>
                                            <td style="padding:8px 12px;color:#4a5068;"><?= esc($chg['old_ownership'] ?? '—') ?></td>
                                            <td style="padding:8px 12px;font-weight:600;color:#1a1d2e;"><?= esc($chg['new_ownership']) ?></td>
                                            <td style="padding:8px 12px;color:#4a5068;"><?= esc($chg['changer_name'] ?? '—') ?></td>
                                            <td style="padding:8px 12px;">
                                                <?php
                                                $sc = match ($chg['status']) {
                                                    'approved'      => '#16a085',
                                                    'rejected'      => '#c0392b',
                                                    'pending'       => '#e67e22',
                                                    'auto_approved' => '#5b6fd6',
                                                    default         => '#9aa0b4',
                                                };
                                                ?>
                                                <span style="background:<?= $sc ?>18;color:<?= $sc ?>;border:1px solid <?= $sc ?>44;border-radius:20px;padding:2px 8px;font-size:11px;font-weight:700;">
                                                    <?= ucfirst(str_replace('_', ' ', $chg['status'])) ?>
                                                </span>
                                            </td>
                                            <td style="padding:8px 12px;color:#6b7280;max-width:200px;white-space:pre-wrap;"><?= esc($chg['notes'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            $householdUploads = [];
            $addHouseholdUpload = static function (array &$uploads, string $person, string $document, ?string $path): void {
                $normalizedPath = \App\Controllers\HouseholdUploadController::normalizeUploadPath($path);
                if ($normalizedPath === '') {
                    return;
                }

                $routePath = preg_replace('#^uploads/#i', '', ltrim($normalizedPath, '/'));
                $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
                $uploads[] = [
                    'person'     => $person,
                    'document'   => $document,
                    'path'       => site_url('household-files/' . implode('/', array_map('rawurlencode', explode('/', $routePath)))),
                    'is_image'   => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true),
                    'extension'  => strtoupper($extension ?: 'FILE'),
                ];
            };
            $addHouseholdUpload($householdUploads, $headName ?: 'Household Head', '4Ps Beneficiary ID', $head['id_4ps_path'] ?? null);
            $addHouseholdUpload($householdUploads, $headName ?: 'Household Head', 'Senior Citizen ID', $head['id_senior_path'] ?? null);
            $addHouseholdUpload($householdUploads, $headName ?: 'Household Head', 'PWD ID', $head['id_pwd_path'] ?? null);
            $addHouseholdUpload($householdUploads, $headName ?: 'Household Head', 'Solo Parent ID', $head['id_solo_parent_path'] ?? null);
            $addHouseholdUpload($householdUploads, $headName ?: 'Household Head', 'Ownership document', $head['ownership_document_path'] ?? null);
            $addHouseholdUpload($householdUploads, $headName ?: 'Household Head', 'ID or Birth Certificate', $head['supporting_doc_path'] ?? null);
            foreach ($members as $member) {
                $memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?: 'Household Member';
                $addHouseholdUpload($householdUploads, $memberName, 'PWD ID', $member['id_pwd_path'] ?? null);
                $addHouseholdUpload($householdUploads, $memberName, 'Senior Citizen ID', $member['id_senior_path'] ?? null);
                $addHouseholdUpload($householdUploads, $memberName, 'ID or Birth Certificate', $member['supporting_doc_path'] ?? null);
            }
            ?>
            <section class="hh-upload-gallery" aria-labelledby="hh-upload-gallery-title">
                <div class="hh-upload-gallery__header">
                    <div>
                        <h3 id="hh-upload-gallery-title"><i class="fas fa-images"></i> Household Documents &amp; Photos</h3>
                        <p>Uploaded IDs and supporting files for this household.</p>
                    </div>
                    <span class="hh-upload-gallery__count"><?= count($householdUploads) ?> file<?= count($householdUploads) === 1 ? '' : 's' ?></span>
                </div>
                <?php if (empty($householdUploads)): ?>
                    <div class="hh-upload-gallery__empty">
                        <i class="far fa-images"></i>
                        <span>No uploaded photos or documents for this household yet.</span>
                    </div>
                <?php else: ?>
                    <div class="hh-upload-grid">
                        <?php foreach ($householdUploads as $upload): ?>
                            <a class="hh-upload-card" href="<?= esc($upload['path']) ?>" target="_blank" rel="noopener">
                                <div class="hh-upload-preview">
                                    <?php if ($upload['is_image']): ?>
                                        <img src="<?= esc($upload['path']) ?>" alt="<?= esc($upload['document']) ?> for <?= esc($upload['person']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <i class="fas fa-file-pdf"></i>
                                        <span><?= esc($upload['extension']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="hh-upload-card__body">
                                    <strong><?= esc($upload['document']) ?></strong>
                                    <span><?= esc($upload['person']) ?></span>
                                    <small><i class="fas fa-external-link-alt"></i> View file</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Members table -->
            <div class="hh-members-header">
                <h3 class="db-section-title" style="margin:0;">
                    <i class="fas fa-users" style="color:#1d2448;margin-right:6px;"></i>
                    Household Members <span class="hh-count"><?= count($members) ?></span>
                </h3>
            </div>

            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Relationship</th>
                            <th>Birthday</th>
                            <th>Age</th>
                            <th>Occupation</th>
                            <th>Grade Level</th>
                            <th>Monthly Income</th>
                            <th>Education</th>
                            <th>PhilHealth #</th>
                            <th>PWD</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="11" style="text-align:center;padding:24px;color:#9aa0b4;">
                                    No household members recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($members as $m):
                                $mName   = trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? ''));
                                $mInit   = strtoupper($m['first_name'][0] ?? '?');
                                $mDob    = !empty($m['date_of_birth']) ? date('M d, Y', strtotime($m['date_of_birth'])) : '—';
                                $mAge    = !empty($m['date_of_birth']) ? (int)date_diff(date_create($m['date_of_birth']), date_create('today'))->y : '—';
                                $mId     = (int) $m['id'];
                            ?>
                                <tr <?= !empty($m['is_deceased']) ? 'style="opacity:.65;"' : '' ?>>
                                    <td>
                                        <div class="db-resident-name">
                                            <div class="db-avatar-sm" style="<?= !empty($m['is_deceased']) ? 'background:#9aa0b4;' : '' ?>"><?= $mInit ?></div>
                                            <div>
                                                <span><?= pii($mName, 'name') ?></span>
                                                <?php if (!empty($m['is_deceased'])): ?>
                                                    <span style="display:inline-block;background:#f8d7da;color:#842029;border:1px solid #f5c2c7;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;margin-left:4px;">
                                                        <i class="fas fa-cross" style="font-size:9px;"></i>
                                                        Deceased<?= !empty($m['year_of_death']) ? ' ' . esc($m['year_of_death']) : '' ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $relLabels = [
                                            'former_head'    => 'Former Head',
                                            'spouse'         => 'Spouse',
                                            'child'          => 'Child',
                                            'father'         => 'Father',
                                            'mother'         => 'Mother',
                                            'sibling'        => 'Sibling',
                                            'grandparent'    => 'Grandparent',
                                            'grandchild'     => 'Grandchild',
                                            'aunt_uncle'     => 'Aunt/Uncle',
                                            'cousin'         => 'Cousin',
                                            'other_relative' => 'Other Relative',
                                            'non_relative'   => 'Non-relative',
                                        ];
                                        $relKey   = strtolower($m['relationship'] ?? '');
                                        $relLabel = $relLabels[$relKey] ?? ucwords(str_replace('_', ' ', $relKey));
                                        $isFormerHead = $relKey === 'former_head';
                                        ?>
                                        <span class="hh-rel-badge<?= $isFormerHead ? ' hh-rel-badge--former' : '' ?>">
                                            <?= esc($relLabel) ?>
                                        </span>
                                    </td>
                                    <td><?= $mDob ?></td>
                                    <td><?= $mAge ?></td>
                                    <td>
                                        <?= esc($m['occupation'] ?? '—') ?>
                                        <?php if (! empty($m['work_detail'])): ?>
                                            <div style="font-size:11px;color:#4a5068;margin-top:2px;"><?= esc($m['work_detail']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc(($m['grade_level'] ?? '') !== '' ? $m['grade_level'] : '—') ?></td>
                                    <td><?= $m['monthly_income'] ? '₱' . number_format($m['monthly_income'], 2) : '₱0.00' ?></td>
                                    <td><?= esc($m['educational_attainment'] ?? '—') ?></td>
                                    <td><code class="hh-philhealth"><?= esc($m['philhealth_no'] ?? '—') ?></code></td>
                                    <td>
                                        <?php if (!empty($m['is_pwd'])): ?>
                                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fff0f5;border:1px solid #fad4e4;color:#c0392b;border-radius:20px;padding:2px 9px;font-size:11px;font-weight:700;">
                                                <i class="fas fa-wheelchair" style="font-size:10px;"></i> PWD
                                            </span>
                                            <?php if (!empty($m['pwd_type'])): ?>
                                                <div style="font-size:11px;color:#7a4040;margin-top:2px;"><?= esc($m['pwd_type']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#c8cdd8;font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="db-action-group">
                                            <button class="db-icon-btn db-icon-btn--edit" title="Edit"
                                                onclick="openEditMember(<?= $mId ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="db-icon-btn" title="Separate Household"
                                                style="color:#e67e22;"
                                                onclick="openSeparateModal(<?= $mId ?>)">
                                                <i class="fas fa-home"></i>
                                            </button>
                                            <?php if ($relKey === 'child' && ($m['marital_status'] ?? 'Single') === 'Married' && empty($m['is_deceased'])): ?>
                                                <button class="db-icon-btn" title="Add Family"
                                                    style="color:#16a085;"
                                                    onclick="openAddFamilyModal(<?= $mId ?>)">
                                                    <i class="fas fa-users"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (empty($m['is_deceased'])): ?>
                                                <button class="db-icon-btn" title="Mark as Deceased"
                                                    style="color:#842029;"
                                                    onclick="openDeceasedMemberModal(<?= $mId ?>)">
                                                    <i class="fas fa-cross"></i>
                                                </button>
                                            <?php else: ?>
                                                <form action="/<?= $role ?>/census/member/undeceased/<?= $m['id'] ?>" method="post" style="display:inline;"
                                                    onsubmit="return confirm('Clear deceased status for this member?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="household_no" value="<?= esc($householdId) ?>">
                                                    <button type="submit" class="db-icon-btn" title="Clear Deceased" style="color:#166534;">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="/<?= $role ?>/census/member/delete/<?= $m['id'] ?>" method="post" style="display:inline;"
                                                onsubmit="return confirm('Remove this member?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="household_no" value="<?= esc($householdId) ?>">
                                                <button type="submit" class="db-icon-btn db-icon-btn--del" title="Remove"><i class="fas fa-trash"></i></button>
                                            </form>
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

    <!-- ══ EDIT HOUSEHOLD HEAD MODAL ══ -->
    <div class="db-modal-overlay" id="editHeadModal">
        <div class="db-modal pf-modal">
            <form action="/<?= $role ?>/census/update/<?= esc($householdId) ?>" method="post" id="editHeadForm" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>

                <div class="pf-modal-header">
                    <div class="pf-modal-title-wrap">
                        <div class="pf-modal-logo"><img src="/bacolod.png" alt="Seal"></div>
                        <div>
                            <div class="pf-modal-republic">Republic of the Philippines</div>
                            <div class="pf-modal-barangay">Barangay Bacolod, Bato, Camarines Sur</div>
                            <div class="pf-modal-formtitle">EDIT HOUSEHOLD HEAD — CENSUS FORM</div>
                        </div>
                    </div>
                    <button type="button" class="pf-close-btn" onclick="closeModal('editHeadModal')"><i class="fas fa-times"></i></button>
                </div>

                <div class="pf-body">
                    <div class="pf-section">
                        <div class="pf-section-bar"><i class="fas fa-user"></i> PERSONAL INFORMATION</div>
                        <div class="pf-field-row pf-cols-4">
                            <div class="pf-field">
                                <div class="pf-field-label">Last Name</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="last_name" value="<?= esc($head['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">First Name</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="first_name" value="<?= esc($head['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Middle Name</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="middle_name" value="<?= esc($head['middle_name'] ?? '') ?>">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Suffix</div>
                                <select class="pf-input" name="suffix">
                                    <option value="">— NONE —</option>
                                    <?php foreach (['Jr', 'Sr', 'II', 'III', 'IV'] as $s): ?>
                                        <option <?= ($head['suffix'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-4">
                            <div class="pf-field">
                                <div class="pf-field-label">Date of Birth</div>
                                <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="date_of_birth" value="<?= esc($headDobVal) ?>"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Place of Birth</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="place_of_birth" value="<?= esc($head['place_of_birth'] ?? '') ?>">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Gender</div>
                                <select class="pf-input" name="gender">
                                    <option <?= ($head['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option <?= ($head['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Civil Status</div>
                                <select class="pf-input" name="civil_status">
                                    <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $cs): ?>
                                        <option <?= ($head['civil_status'] ?? '') === $cs ? 'selected' : '' ?>><?= $cs ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-4">
                            <div class="pf-field">
                                <div class="pf-field-label">Nationality</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="nationality" value="<?= esc($head['nationality'] ?? 'FILIPINO') ?>">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Religion</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="religion" value="<?= esc($head['religion'] ?? '') ?>">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Occupation</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="occupation" value="<?= esc($head['occupation'] ?? '') ?>">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Monthly Income (₱)</div>
                                <input type="number" class="pf-input" name="monthly_income" value="<?= esc($head['monthly_income'] ?? '0') ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-3">
                            <div class="pf-field">
                                <div class="pf-field-label">Contact Number</div>
                                <input type="tel" class="pf-input js-contact-number" name="contact_number" value="<?= esc($head['contact_number'] ?? '') ?>" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Educational Attainment</div>
                                <?php $savedEdu = trim((string) ($head['educational_attainment'] ?? '')); $eduMatched = false; ?>
                                <select class="pf-input" name="educational_attainment">
                                    <option value="">— Select —</option>
                                    <?php foreach ($eduOptions as $e): ?>
                                        <?php $eduSelected = strcasecmp($savedEdu, $e) === 0; if ($eduSelected) { $eduMatched = true; } ?>
                                        <option value="<?= esc($e) ?>" <?= $eduSelected ? 'selected' : '' ?>><?= esc($e) ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($savedEdu !== '' && ! $eduMatched): ?>
                                        <option value="<?= esc($savedEdu) ?>" selected><?= esc($savedEdu) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">PhilHealth Number</div>
                                <input type="text" class="pf-input pf-philhealth" name="philhealth_no" value="<?= esc($head['philhealth_no'] ?? '') ?>" maxlength="12" inputmode="numeric">
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-1">
                            <div class="pf-field">
                                <div class="pf-field-label">Registered Voter?</div>
                                <div class="pf-radio-row" style="flex-direction:row;gap:20px;padding:8px 10px;">
                                    <label class="pf-radio">
                                        <input type="radio" name="registered_voter" value="1" <?= !empty($head['registered_voter']) ? 'checked' : '' ?>>
                                        <span>Yes</span>
                                    </label>
                                    <label class="pf-radio">
                                        <input type="radio" name="registered_voter" value="0" <?= empty($head['registered_voter']) ? 'checked' : '' ?>>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-1">
                            <div class="pf-field">
                                <div class="pf-field-label">Complete Address</div>
                                <input type="text" class="pf-input pf-upper" name="address" value="<?= esc($head['address'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="pf-section">
                        <div class="pf-section-bar"><i class="fas fa-tags"></i> HOUSEHOLD CLASSIFICATION</div>
                        <div class="pf-field-row pf-cols-4">
                            <div class="pf-field">
                                <div class="pf-field-label">Zone / Purok</div>
                                <select class="pf-input" name="zone">
                                    <option value="">— Select —</option>
                                    <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7',] as $z): ?>
                                        <option <?= ($head['zone'] ?? '') === $z ? 'selected' : '' ?>><?= $z ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Years of Residency</div>
                                <input type="number" class="pf-input" name="years_of_residency" value="<?= current_years_of_residency($head) ?>" min="0">
                                <div style="font-size:11px;color:#9aa0b4;margin-top:3px;">This count increases by 1 every January.</div>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">House Ownership</div>
                                <select class="pf-input" name="house_ownership" id="edit_house_ownership"
                                    onchange="toggleEditNumFamilies(this.value)">
                                    <?php foreach (['Owned', 'Rented', 'Shared'] as $ho): ?>
                                        <option <?= ($head['house_ownership'] ?? '') === $ho ? 'selected' : '' ?>><?= $ho ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Shared address group fields — visible only when Shared -->
                        <div id="edit_shared_group_row" style="display:<?= ($head['house_ownership'] ?? '') === 'Shared' ? 'block' : 'none' ?>;margin-top:10px;padding:12px 14px;background:#f5f7ff;border:1px solid #dde2f5;border-radius:9px;">
                            <div style="font-size:11.5px;font-weight:700;color:#4a5068;margin-bottom:8px;">
                                <i class="fas fa-link" style="color:#8e44ad;margin-right:5px;"></i>Shared Dwelling Group
                            </div>
                            <div class="pf-field" style="margin-bottom:8px;">
                                <div class="pf-field-label">Shared Address Group Code</div>
                                <input type="text" class="pf-input" name="shared_address_group"
                                    value="<?= esc($head['shared_address_group'] ?? '') ?>"
                                    placeholder="e.g. SHR-12345 — must match all families at same address"
                                    maxlength="20">
                                <div style="font-size:11px;color:#9aa0b4;margin-top:3px;">All families sharing this dwelling must have the same group code.</div>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Family Number at this Address</div>
                                <input type="number" class="pf-input" name="family_number"
                                    value="<?= esc($head['family_number'] ?? 1) ?>" min="1" max="20"
                                    style="max-width:80px;text-align:center;font-weight:700;">
                                <div style="font-size:11px;color:#9aa0b4;margin-top:3px;">e.g. 1 = first family registered at this address, 2 = second, etc.</div>
                            </div>
                            <div class="pf-field" style="margin-top:8px;">
                                <div class="pf-field-label">Household number this family belongs to</div>
                                <input type="text" class="pf-input" name="linked_household_no" value="<?= esc($head['linked_household_no'] ?? '') ?>" maxlength="5" inputmode="numeric" placeholder="e.g. 12345" style="max-width:160px;">
                            </div>
                        </div>

                        <!-- Supporting document + notes for ownership classification -->
                        <div style="margin-top:14px;padding:14px;background:#f8f9ff;border:1px solid #dde2f5;border-radius:10px;">
                            <div style="font-size:11.5px;font-weight:700;color:#4a5068;margin-bottom:10px;">
                                <i class="fas fa-paperclip" style="color:#9aa0b4;margin-right:5px;"></i>
                                Supporting Documents (Optional)
                            </div>
                            <div class="pf-field" style="margin-bottom:10px;">
                                <div class="pf-field-label">Attach Proof of Ownership / Tenancy</div>
                                <input type="file" name="ownership_document"
                                    class="pf-input" style="padding:6px;"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <?php if (!empty($head['ownership_document_path'])): ?>
                                    <div style="font-size:11.5px;color:#16a085;margin-top:4px;">
                                        <i class="fas fa-file"></i>
                                        Current: <a href="/household-files/ownership/<?= esc(rawurlencode(basename($head['ownership_document_path']))) ?>"
                                            target="_blank" style="color:#16a085;">View attached file</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Notes / Remarks</div>
                                <textarea class="pf-input" name="ownership_notes" rows="2"
                                    placeholder="e.g. Renting from Brgy. Bacolod since 2018, lease renewed annually…"
                                    style="resize:vertical;font-family:inherit;font-size:13px;"><?= esc($head['ownership_notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="pf-check-row">
                            <span class="pf-check-label">Belongs to:</span>
                            <label class="pf-check"><input type="checkbox" name="is_4ps" value="1" id="head_edit_4ps"
                                    <?= !empty($head['is_4ps']) ? 'checked' : '' ?>
                                    onchange="toggleIdUpload('head_edit_4ps','head_edit_id_4ps_wrap')"> <span>4Ps</span></label>
                            <label class="pf-check"><input type="checkbox" name="is_senior_citizen" value="1" id="head_edit_senior"
                                    <?= !empty($head['is_senior_citizen']) ? 'checked' : '' ?>
                                    onchange="toggleIdUpload('head_edit_senior','head_edit_id_senior_wrap')"> <span>Senior Citizen</span></label>
                            <label class="pf-check"><input type="checkbox" name="is_solo_parent" value="1" id="head_edit_solo"
                                    <?= !empty($head['is_solo_parent']) ? 'checked' : '' ?>
                                    onchange="toggleIdUpload('head_edit_solo','head_edit_id_solo_wrap')"> <span>Solo Parent</span></label>
                            <label class="pf-check"><input type="checkbox" name="is_indigenous" value="1" <?= !empty($head['is_indigenous']) ? 'checked' : '' ?>> <span>Indigenous</span></label>
                        </div>

                        <!-- Conditional ID uploads for head classification -->
                        <?php
                        $idFields = [
                            ['wrap' => 'head_edit_id_4ps_wrap',    'field' => 'id_4ps',         'path' => $head['id_4ps_path'] ?? null,         'label' => '4Ps Beneficiary ID',    'checked' => !empty($head['is_4ps'])],
                            ['wrap' => 'head_edit_id_senior_wrap', 'field' => 'id_senior',      'path' => $head['id_senior_path'] ?? null,      'label' => 'Senior Citizen ID',     'checked' => !empty($head['is_senior_citizen'])],
                            ['wrap' => 'head_edit_id_solo_wrap',   'field' => 'id_solo_parent', 'path' => $head['id_solo_parent_path'] ?? null, 'label' => 'Solo Parent ID',        'checked' => !empty($head['is_solo_parent'])],
                        ];
                        foreach ($idFields as $idf):
                        ?>
                            <div id="<?= $idf['wrap'] ?>" class="pf-id-upload" style="display:<?= $idf['checked'] ? 'block' : 'none' ?>;margin-top:8px;">
                                <label class="pf-id-label" style="display:flex;align-items:center;gap:6px;font-weight:600;font-size:13px;color:#2a3148;">
                                    <i class="fas fa-id-card" style="color:#5b6fd6;"></i>
                                    <?= $idf['label'] ?>
                                    <?php if ($idf['field'] !== 'id_senior'): ?><span class="pf-req" style="color:#c0392b;font-weight:700;">* REQUIRED</span><?php else: ?><span style="color:#6b7291;font-size:11px;font-weight:500;">(Optional)</span><?php endif; ?>
                                    <?php if (!empty($idf['path'])): ?>
                                        <span style="margin-left:auto;font-weight:500;font-size:12px;color:#3a8f61;">
                                            <i class="fas fa-check-circle"></i>
                                            Existing on file
                                            <a href="/<?= esc($idf['path']) ?>" target="_blank" style="color:#5b6fd6;text-decoration:underline;margin-left:6px;">(View)</a>
                                        </span>
                                    <?php endif; ?>
                                </label>
                                <input type="file" name="<?= $idf['field'] ?>" accept="image/*,application/pdf" class="pf-file" style="margin-top:4px;width:100%;padding:6px;font-size:13px;">
                                <div style="font-size:11.5px;color:#6b7291;margin-top:3px;">Upload scanned ID (PDF, JPG, PNG — max 5 MB).<?= !empty($idf['path']) ? ' Leave blank to keep existing file.' : '' ?></div>
                            </div>
                        <?php endforeach; ?>

                        <div style="margin-top:8px;">
                            <label class="pf-check"><input type="checkbox" name="is_pwd" value="1" id="head_edit_is_pwd"
                                    <?= !empty($head['is_pwd']) ? 'checked' : '' ?>
                                    onchange="togglePwdType(this,'head_edit_pwd_wrap',true);toggleIdUpload('head_edit_is_pwd','head_edit_id_pwd_wrap','checkbox');"> <span>PWD Member</span></label>
                            <div id="head_edit_pwd_wrap" style="display:<?= !empty($head['is_pwd']) ? 'block' : 'none' ?>;margin-top:6px;margin-left:4px;">
                                <input type="text" class="pf-input" name="pwd_type" placeholder="Specify disability (e.g. Visual, Hearing, Physical…)" maxlength="120"
                                    value="<?= esc($head['pwd_type'] ?? '') ?>">
                            </div>
                            <div id="head_edit_id_pwd_wrap" class="pf-id-upload" style="display:<?= !empty($head['is_pwd']) ? 'block' : 'none' ?>;margin-top:8px;margin-left:4px;">
                                <label class="pf-id-label" style="display:flex;align-items:center;gap:6px;font-weight:600;font-size:13px;color:#2a3148;">
                                    <i class="fas fa-id-card" style="color:#5b6fd6;"></i>
                                    PWD ID Card
                                    <span class="pf-req" style="color:#c0392b;font-weight:700;">* REQUIRED</span>
                                    <?php if (!empty($head['id_pwd_path'])): ?>
                                        <span style="margin-left:auto;font-weight:500;font-size:12px;color:#3a8f61;">
                                            <i class="fas fa-check-circle"></i>
                                            Existing on file
                                            <a href="/household-files/ids/<?= esc(rawurlencode(basename($head['id_pwd_path']))) ?>" target="_blank" style="color:#5b6fd6;text-decoration:underline;margin-left:6px;">(View)</a>
                                        </span>
                                    <?php endif; ?>
                                </label>
                                <input type="file" name="id_pwd" accept="image/*,application/pdf" class="pf-file" style="margin-top:4px;width:100%;padding:6px;font-size:13px;">
                                <div style="font-size:11.5px;color:#6b7291;margin-top:3px;">Upload scanned PWD ID (PDF, JPG, PNG — max 5 MB).<?= !empty($head['id_pwd_path']) ? ' Leave blank to keep existing file.' : '' ?></div>
                            </div>
                        </div>

                        <div class="pf-field-row pf-cols-2" style="margin-top:12px;">
                            <div class="pf-field">
                                <div class="pf-field-label">No. of Families in Household</div>
                                <input type="number" class="pf-input" name="num_families" id="edit_num_families"
                                    value="<?= esc($head['num_families'] ?? 1) ?>" min="1" max="20"
                                    style="max-width:120px;">
                            </div>
                        </div>
                    </div>

                    <?php
                    $waterSource = (string) ($head['water_source_level'] ?? '');
                    $waterManaged = $head['water_safety_managed'] ?? null;
                    $sanitationBasic = (string) ($head['sanitation_basic'] ?? '');
                    $sanitationManaged = (string) ($head['sanitation_managed'] ?? '');
                    ?>
                    <div class="pf-section">
                        <div class="pf-section-bar"><i class="fas fa-tint"></i> Access to Safe Water &amp; Sanitation Facility</div>
                        <div class="pf-field-row pf-cols-2">
                            <div class="pf-field">
                                <div class="pf-field-label">Basic Safe Water Source</div>
                                <div class="pf-radio-row">
                                    <label class="pf-radio"><input type="radio" name="water_source" value="I" <?= $waterSource === 'I' ? 'checked' : '' ?>> <span>Level I — Point Source (e.g. protected well, spring)</span></label>
                                    <label class="pf-radio"><input type="radio" name="water_source" value="II" <?= $waterSource === 'II' ? 'checked' : '' ?>> <span>Level II — Communal Faucet / Stand Post</span></label>
                                    <label class="pf-radio"><input type="radio" name="water_source" value="III" <?= $waterSource === 'III' ? 'checked' : '' ?>> <span>Level III — Individual House Connection (piped water)</span></label>
                                    <label class="pf-radio"><input type="radio" name="water_source" value="none" <?= $waterSource === 'none' ? 'checked' : '' ?>> <span>No Safe Water Source</span></label>
                                </div>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Using Safety-Managed Water Service</div>
                                <div class="pf-radio-row">
                                    <label class="pf-radio"><input type="radio" name="water_managed" value="yes" <?= ($waterManaged === 1 || $waterManaged === '1') ? 'checked' : '' ?>> <span>Yes — Water is safely managed</span></label>
                                    <label class="pf-radio"><input type="radio" name="water_managed" value="no" <?= ($waterManaged === 0 || $waterManaged === '0') ? 'checked' : '' ?>> <span>No — Not safely managed</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-2">
                            <div class="pf-field">
                                <div class="pf-field-label">Basic Sanitation Facility</div>
                                <div class="pf-radio-row">
                                    <label class="pf-radio"><input type="radio" name="sanitation_basic" value="with" <?= $sanitationBasic === 'with' ? 'checked' : '' ?>> <span>With Basic Sanitation Facility</span></label>
                                    <label class="pf-radio"><input type="radio" name="sanitation_basic" value="without" <?= $sanitationBasic === 'without' ? 'checked' : '' ?>> <span>Without Basic Sanitation Facility</span></label>
                                </div>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Safely Managed Sanitation Services</div>
                                <div class="pf-radio-row">
                                    <label class="pf-radio"><input type="radio" name="sanitation_managed" value="with" <?= $sanitationManaged === 'with' ? 'checked' : '' ?>> <span>With Safely Managed Sanitation</span></label>
                                    <label class="pf-radio"><input type="radio" name="sanitation_managed" value="without" <?= $sanitationManaged === 'without' ? 'checked' : '' ?>> <span>Without Safely Managed Sanitation</span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pf-footer">
                    <div style="flex:1;"></div>
                    <button type="button" class="pf-btn pf-btn--outline" onclick="closeModal('editHeadModal')">Cancel</button>
                    <button type="submit" class="pf-btn pf-btn--primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ ADD MEMBER MODAL ══ -->
    <div class="db-modal-overlay" id="addMemberModal">
        <div class="db-modal pf-modal">
            <form action="/<?= $role ?>/census/members/add/<?= esc($householdId) ?>" method="post" id="addMemberForm">
                <?= csrf_field() ?>

                <div class="pf-modal-header">
                    <div class="pf-modal-title-wrap">
                        <div class="pf-modal-logo"><img src="/bacolod.png" alt="Seal"></div>
                        <div>
                            <div class="pf-modal-republic">Republic of the Philippines</div>
                            <div class="pf-modal-barangay">Barangay Bacolod, Bato, Camarines Sur</div>
                            <div class="pf-modal-formtitle">ADD HOUSEHOLD MEMBER — Household #<?= esc($householdId) ?></div>
                        </div>
                    </div>
                    <button type="button" class="pf-close-btn" onclick="closeModal('addMemberModal')"><i class="fas fa-times"></i></button>
                </div>

                <div class="pf-body">

                    <!-- Spouse -->
                    <fieldset class="pf-section" <?= $hasSpouse ? 'disabled' : '' ?> style="border:0;padding:0;margin:0 0 18px;min-width:0;">
                        <div class="pf-section-bar"><i class="fas fa-ring"></i> SPOUSE</div>
                        <?php if ($hasSpouse): ?><div style="padding:8px 12px;background:#fff8f0;color:#b7600a;font-size:12px;border:1px solid #fde8c8;">A spouse is already recorded for this household.</div><?php endif; ?>
                        <div class="pf-family-card">
                            <div class="pf-field-row pf-cols-4">
                                <div class="pf-field">
                                    <div class="pf-field-label">Last Name</div>
                                    <input type="text" class="pf-input pf-upper pf-alpha" name="spouse_last_name" placeholder="LAST NAME">
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">First Name</div>
                                    <input type="text" class="pf-input pf-upper pf-alpha" name="spouse_first_name" placeholder="FIRST NAME">
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">Middle Name</div>
                                    <input type="text" class="pf-input pf-upper pf-alpha" name="spouse_middle_name" placeholder="MIDDLE NAME">
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">Suffix</div>
                                    <select class="pf-input" name="spouse_suffix">
                                        <option value="">— NONE —</option>
                                        <option>Jr</option>
                                        <option>Sr</option>
                                        <option>II</option>
                                        <option>III</option>
                                    </select>
                                </div>
                            </div>
                            <div class="pf-field-row pf-cols-4">
                                <div class="pf-field">
                                    <div class="pf-field-label">Date of Birth</div>
                                    <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="spouse_dob"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">Gender</div>
                                    <select class="pf-input" name="spouse_gender">
                                        <option value="">— Select —</option>
                                        <option>Male</option>
                                        <option>Female</option>
                                    </select>
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">Occupation</div>
                                    <input type="text" class="pf-input pf-upper pf-alpha" name="spouse_occupation" placeholder="E.G. HOUSEWIFE, TEACHER">
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">Monthly Income (₱)</div>
                                    <input type="number" class="pf-input" name="spouse_income" placeholder="0.00" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="pf-field-row pf-cols-3">
                                <div class="pf-field">
                                    <div class="pf-field-label">Educational Attainment</div>
                                    <select class="pf-input" name="spouse_educational_attainment">
                                        <option value="">— Select —</option>
                                        <?php foreach ($eduOptions as $e): ?><option><?= $e ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">PhilHealth Number</div>
                                    <input type="text" class="pf-input pf-philhealth" name="spouse_philhealth" placeholder="00000000000" maxlength="12" inputmode="numeric">
                                </div>
                                <div class="pf-field">
                                    <div class="pf-field-label">Registered Voter?</div>
                                    <div class="pf-radio-row" style="flex-direction:row;gap:20px;padding:8px 10px;">
                                        <label class="pf-radio"><input type="radio" name="spouse_registered_voter" value="1"> <span>Yes</span></label>
                                        <label class="pf-radio"><input type="radio" name="spouse_registered_voter" value="0" checked> <span>No</span></label>
                                    </div>
                                </div>
                                <div class="pf-check-row">
                                    <label class="pf-check"><input type="checkbox" name="is_pwd" value="1" id="head_modal_is_pwd"
                                            <?= !empty($head['is_pwd']) ? 'checked' : '' ?>
                                            onchange="togglePwdType(this,'head_modal_pwd_wrap',true)"> <span>PWD</span></label>
                                    <div id="head_modal_pwd_wrap" style="display:<?= !empty($head['is_pwd']) ? 'block' : 'none' ?>;margin-top:4px;margin-left:4px;">
                                        <input type="text" class="pf-input" name="pwd_type" placeholder="Specify disability (e.g. Visual, Hearing…)" maxlength="120"
                                            value="<?= esc($head['pwd_type'] ?? '') ?>">
                                    </div>
                                    <label class="pf-check"><input type="checkbox" name="is_senior_citizen" value="1" <?= !empty($head['is_senior_citizen']) ? 'checked' : '' ?>> <span>Senior Citizen</span></label>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Children -->
                    <div class="pf-section">
                        <div class="pf-section-bar" style="display:flex;align-items:center;justify-content:space-between;">
                            <span><i class="fas fa-child"></i> CHILD(REN)</span>
                            <button type="button" class="pf-add-row-btn" onclick="addChildRowModal()">
                                <i class="fas fa-plus"></i> Add Row
                            </button>
                        </div>
                        <div id="modalChildrenRows">
                            <div class="pf-member-card">
                                <div class="pf-member-card-inner">
                                    <div class="pf-member-fields">
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">Last Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="child_last_name[]" placeholder="LAST NAME">
                                        </div>
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">First Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="child_first_name[]" placeholder="FIRST NAME">
                                        </div>
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">Middle Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="child_middle_name[]" placeholder="MIDDLE NAME">
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--xs">
                                            <div class="pf-field-label">Suffix</div>
                                            <select class="pf-input" name="child_suffix[]">
                                                <option value="">—NONE—</option>
                                                <option>Jr</option>
                                                <option>Sr</option>
                                                <option>II</option>
                                                <option>III</option>
                                            </select>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Date of Birth</div>
                                            <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="child_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--xs">
                                            <div class="pf-field-label">Gender</div>
                                            <select class="pf-input" name="child_gender[]">
                                                <option value="">—Select—</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Civil Status</div>
                                            <input type="hidden" name="child_marital_status[]" value="Single">
                                            <label class="pf-married-toggle"><input type="checkbox" onchange="toggleChildMarried(this)"><span>Married</span></label>
                                        </div>
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">Occupation</div>
                                            <div class="pf-occ-wrap">
                                                <div class="pf-occ-pills">
                                                    <button type="button" class="pf-occ-pill" data-val="STUDENT" onclick="setOcc(this,'STUDENT')">Student</button>
                                                    <button type="button" class="pf-occ-pill" data-val="OUT OF SCHOOL" onclick="setOcc(this,'OUT OF SCHOOL')">Out of School</button>
                                                </div>
                                                <input type="text" class="pf-input pf-upper pf-alpha" name="child_occupation[]" placeholder="OR TYPE EXACT OCCUPATION">
                                                <div class="pf-grade-wrap">
                                                    <div class="pf-grade-label">Current Grade / Year</div>
                                                    <select class="pf-input" name="child_grade[]">
                                                        <option value="">— Select Grade / Year —</option>
                                                        <optgroup label="Elementary">
                                                            <option>Grade 1</option>
                                                            <option>Grade 2</option>
                                                            <option>Grade 3</option>
                                                            <option>Grade 4</option>
                                                            <option>Grade 5</option>
                                                            <option>Grade 6</option>
                                                        </optgroup>
                                                        <optgroup label="Junior High School">
                                                            <option>Grade 7</option>
                                                            <option>Grade 8</option>
                                                            <option>Grade 9</option>
                                                            <option>Grade 10</option>
                                                        </optgroup>
                                                        <optgroup label="Senior High School">
                                                            <option>Grade 11</option>
                                                            <option>Grade 12</option>
                                                        </optgroup>
                                                        <optgroup label="College / University">
                                                            <option>1st Year College</option>
                                                            <option>2nd Year College</option>
                                                            <option>3rd Year College</option>
                                                            <option>4th Year College</option>
                                                            <option>5th Year College</option>
                                                        </optgroup>
                                                        <optgroup label="Vocational / Technical">
                                                            <option>1st Year Tech-Voc</option>
                                                            <option>2nd Year Tech-Voc</option>
                                                        </optgroup>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Monthly Income (₱)</div><input type="number" class="pf-input" name="child_income[]" placeholder="0.00" min="0" step="0.01">
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">PhilHealth No.</div><input type="text" class="pf-input pf-philhealth" name="child_philhealth[]" placeholder="00000000000" maxlength="12" inputmode="numeric">
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Voter?</div>
                                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                                <label class="pf-radio"><input type="radio" name="child_voter[0]" value="1"> <span>Yes</span></label>
                                                <label class="pf-radio"><input type="radio" name="child_voter[0]" value="0" checked> <span>No</span></label>
                                            </div>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">PWD?</div>
                                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                                <label class="pf-radio"><input type="radio" name="child_pwd[0]" value="1" onchange="togglePwdType(this,'child_pwd_wrap_0')"> <span>Yes</span></label>
                                                <label class="pf-radio"><input type="radio" name="child_pwd[0]" value="0" checked onchange="togglePwdType(this,'child_pwd_wrap_0')"> <span>No</span></label>
                                            </div>
                                            <div id="child_pwd_wrap_0" style="display:none;margin-top:4px;">
                                                <input type="text" class="pf-input" name="child_pwd_type[]" placeholder="Specify disability…" maxlength="120">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Household Members -->
                    <div class="pf-section">
                        <div class="pf-section-bar" style="display:flex;align-items:center;justify-content:space-between;">
                            <span><i class="fas fa-users"></i> OTHER HOUSEHOLD MEMBERS <em style="font-weight:400;">(other than Spouse/Children)</em></span>
                            <button type="button" class="pf-add-row-btn" onclick="addOtherRowModal()">
                                <i class="fas fa-plus"></i> Add Row
                            </button>
                        </div>
                        <div id="modalOtherRows">
                            <div class="pf-member-card">
                                <div class="pf-member-card-inner">
                                    <div class="pf-member-fields">
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">Last Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="other_last_name[]" placeholder="LAST NAME">
                                        </div>
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">First Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="other_first_name[]" placeholder="FIRST NAME">
                                        </div>
                                        <div class="pf-field pf-field--inline">
                                            <div class="pf-field-label">Middle Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="other_middle_name[]" placeholder="MIDDLE NAME">
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--xs">
                                            <div class="pf-field-label">Suffix</div>
                                            <select class="pf-input" name="other_suffix[]">
                                                <option value="">—NONE—</option>
                                                <option>Jr</option>
                                                <option>Sr</option>
                                                <option>II</option>
                                                <option>III</option>
                                            </select>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Date of Birth</div>
                                            <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="other_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--xs">
                                            <div class="pf-field-label">Gender</div>
                                            <select class="pf-input" name="other_gender[]">
                                                <option value="">—Select—</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Relationship</div>
                                            <select class="pf-input" name="other_relationship[]">
                                                <option value="">— Select —</option>
                                                <option>Father</option>
                                                <option>Mother</option>
                                                <option>Sibling</option>
                                                <option>Grandparent</option>
                                                <option>Grandchild</option>
                                                <option>Aunt</option>
                                                <option>Uncle</option>
                                                <option>Cousin</option>
                                                <option>Niece</option>
                                                <option>Nephew</option>
                                                <option>Son in Law</option>
                                                <option>Daughter in Law</option>
                                                <option>Non-relative</option>
                                            </select>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">Voter?</div>
                                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                                <label class="pf-radio"><input type="radio" name="other_voter[0]" value="1"> <span>Yes</span></label>
                                                <label class="pf-radio"><input type="radio" name="other_voter[0]" value="0" checked> <span>No</span></label>
                                            </div>
                                        </div>
                                        <div class="pf-field pf-field--inline pf-field--sm">
                                            <div class="pf-field-label">PWD?</div>
                                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                                <label class="pf-radio"><input type="radio" name="other_pwd[0]" value="1" onchange="togglePwdType(this,'other_pwd_wrap_0')"> <span>Yes</span></label>
                                                <label class="pf-radio"><input type="radio" name="other_pwd[0]" value="0" checked onchange="togglePwdType(this,'other_pwd_wrap_0')"> <span>No</span></label>
                                            </div>
                                            <div id="other_pwd_wrap_0" style="display:none;margin-top:4px;">
                                                <input type="text" class="pf-input" name="other_pwd_type[]" placeholder="Specify disability…" maxlength="120">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Certification -->
                    <div class="pf-cert-box">
                        <p>I hereby certify that the information provided above is true and correct to the best of my knowledge.</p>
                        <div class="pf-cert-date-only">
                            <div class="pf-cert-recorded-label">Date of Transaction</div>
                            <div class="pf-date-wrap" style="border-bottom:1px solid #333;display:inline-flex;min-width:180px;">
                                <input type="date" class="pf-input pf-date-input pf-cert-date-input" name="recorded_date" style="background:transparent;border:none;padding:4px 32px 4px 0;font-size:13px;" value="<?= date('Y-m-d') ?>">
                                <i class="fas fa-calendar-alt pf-date-icon" style="color:#1d2448;"></i>
                            </div>
                        </div>
                    </div>

                </div><!-- /.pf-body -->

                <div class="pf-footer">
                    <div style="flex:1;"></div>
                    <button type="button" class="pf-btn pf-btn--outline" onclick="closeModal('addMemberModal')">Cancel</button>
                    <button type="submit" class="pf-btn pf-btn--primary"><i class="fas fa-save"></i> Save Members</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ EDIT MEMBER MODAL ══ -->
    <div class="db-modal-overlay" id="editMemberModal">
        <div class="db-modal pf-modal">
            <form action="/<?= $role ?>/census/member/update/0" method="post" id="editMemberForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="household_no" value="<?= esc($householdId) ?>">

                <div class="pf-modal-header">
                    <div class="pf-modal-title-wrap">
                        <div class="pf-modal-logo"><img src="/bacolod.png" alt="Seal"></div>
                        <div>
                            <div class="pf-modal-republic">Republic of the Philippines</div>
                            <div class="pf-modal-barangay">Barangay Bacolod, Bato, Camarines Sur</div>
                            <div class="pf-modal-formtitle">EDIT HOUSEHOLD MEMBER</div>
                        </div>
                    </div>
                    <button type="button" class="pf-close-btn" onclick="closeModal('editMemberModal')"><i class="fas fa-times"></i></button>
                </div>

                <div class="pf-body">
                    <div class="pf-section">
                        <div class="pf-section-bar"><i class="fas fa-user-edit"></i> MEMBER INFORMATION</div>
                        <div class="pf-field-row pf-cols-4">
                            <div class="pf-field">
                                <div class="pf-field-label">Last Name</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="last_name" id="em_last_name" required>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">First Name</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="first_name" id="em_first_name" required>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Middle Name</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="middle_name" id="em_middle_name">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Suffix</div>
                                <select class="pf-input" name="suffix" id="em_suffix">
                                    <option value="">— NONE —</option>
                                    <option>Jr</option>
                                    <option>Sr</option>
                                    <option>II</option>
                                    <option>III</option>
                                </select>
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-3">
                            <div class="pf-field">
                                <div class="pf-field-label">Relationship to Head</div>
                                <select class="pf-input" name="relationship" id="em_relationship" required>
                                    <option value="">— Select —</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="child">Child</option>
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="grandparent">Grandparent</option>
                                    <option value="grandchild">Grandchild</option>
                                    <option value="aunt_uncle">Aunt/Uncle</option>
                                    <option value="cousin">Cousin</option>
                                    <option value="other_relative">Other Relative</option>
                                    <option value="non_relative">Non-relative</option>
                                    <option value="former_head">Former Head</option>
                                </select>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Date of Birth</div>
                                <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="date_of_birth" id="em_dob"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Occupation</div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="occupation" id="em_occupation">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Work, if working student</div>
                                <input type="text" class="pf-input pf-upper" name="work_detail" id="em_work" maxlength="120" placeholder="WHAT WORK DO THEY DO?">
                            </div>
                        </div>
                        <div class="pf-field-row pf-cols-3">
                            <div class="pf-field">
                                <div class="pf-field-label">Monthly Income (₱)</div>
                                <input type="number" class="pf-input" name="monthly_income" id="em_income" value="0" min="0" step="0.01">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">Educational Attainment</div>
                                <select class="pf-input" name="educational_attainment" id="em_education">
                                    <option value="">— Select —</option>
                                    <?php foreach ($eduOptions as $e): ?>
                                        <option><?= $e ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">PhilHealth Number</div>
                                <input type="text" class="pf-input pf-philhealth" name="philhealth_no" id="em_philhealth" maxlength="12" inputmode="numeric">
                            </div>
                            <div class="pf-field">
                                <div class="pf-field-label">PWD?</div>
                                <div style="display:flex;gap:14px;align-items:center;padding:8px 0;">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                        <input type="radio" name="is_pwd" id="em_pwd_yes" value="1" onchange="toggleMemberPwdId(true)"> Yes
                                    </label>
                                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                        <input type="radio" name="is_pwd" id="em_pwd_no" value="0" checked onchange="toggleMemberPwdId(false)"> No
                                    </label>
                                </div>
                                <div id="em_pwd_type_wrap" style="display:none;">
                                    <input type="text" class="pf-input" name="pwd_type" id="em_pwd_type" placeholder="Specify disability (e.g. Visual, Hearing, Physical…)" maxlength="120">
                                </div>
                                <div id="em_id_pwd_wrap" class="pf-id-upload" style="display:none;margin-top:8px;">
                                    <label class="pf-id-label" style="display:flex;align-items:center;gap:6px;font-weight:600;font-size:13px;color:#2a3148;">
                                        <i class="fas fa-id-card" style="color:#5b6fd6;"></i>
                                        PWD ID Card
                                        <span class="pf-req" style="color:#c0392b;font-weight:700;">* REQUIRED</span>
                                        <span id="em_id_pwd_existing" style="margin-left:auto;font-weight:500;font-size:12px;color:#3a8f61;display:none;">
                                            <i class="fas fa-check-circle"></i>
                                            Existing on file
                                            <a id="em_id_pwd_link" href="" target="_blank" style="color:#5b6fd6;text-decoration:underline;margin-left:6px;">(View)</a>
                                        </span>
                                    </label>
                                    <input type="file" name="id_pwd" id="em_id_pwd_file" accept="image/*,application/pdf" class="pf-file" style="margin-top:4px;width:100%;padding:6px;font-size:13px;">
                                    <div id="em_id_pwd_hint" style="font-size:11.5px;color:#6b7291;margin-top:3px;">Upload scanned PWD ID (PDF, JPG, PNG — max 5 MB).</div>
                                </div>
                                <label class="pf-check" style="display:flex;margin-top:10px;">
                                    <input type="checkbox" name="is_senior_citizen" id="em_senior_yes" value="1" onchange="toggleMemberSeniorId(this.checked)">
                                    <span>Senior Citizen (60+)</span>
                                </label>
                                <div id="em_id_senior_wrap" class="pf-id-upload" style="display:none;margin-top:8px;">
                                    <label class="pf-id-label" style="display:flex;align-items:center;gap:6px;font-weight:600;font-size:13px;color:#2a3148;">
                                        <i class="fas fa-id-card" style="color:#5b6fd6;"></i>
                                        Senior Citizen ID <span style="color:#6b7291;font-size:11px;font-weight:500;">(Optional)</span>
                                        <span id="em_id_senior_existing" style="margin-left:auto;font-weight:500;font-size:12px;color:#3a8f61;display:none;">
                                            <i class="fas fa-check-circle"></i> Existing on file
                                            <a id="em_id_senior_link" href="" target="_blank" style="color:#5b6fd6;text-decoration:underline;margin-left:6px;">(View)</a>
                                        </span>
                                    </label>
                                    <input type="file" name="id_senior" id="em_id_senior_file" accept="image/*,application/pdf" class="pf-file" style="margin-top:4px;width:100%;padding:6px;font-size:13px;">
                                    <div id="em_id_senior_hint" style="font-size:11.5px;color:#6b7291;margin-top:3px;">Upload scanned ID (PDF, JPG, PNG — max 5 MB). Optional.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pf-footer">
                    <div style="flex:1;"></div>
                    <button type="button" class="pf-btn pf-btn--outline" onclick="closeModal('editMemberModal')">Cancel</button>
                    <button type="submit" class="pf-btn pf-btn--primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* ── Paper Form Modal (shared with census) ── */
        #modalChildrenRows .pf-member-fields {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 10px;
            align-items: start;
        }

        #modalChildrenRows .pf-member-fields>* {
            min-width: 0;
        }

        #modalChildrenRows .pf-member-fields> :nth-child(1),
        #modalChildrenRows .pf-member-fields> :nth-child(2),
        #modalChildrenRows .pf-member-fields> :nth-child(3) {
            grid-column: span 3;
        }

        #modalChildrenRows .pf-member-fields> :nth-child(4) {
            grid-column: span 1;
        }

        #modalChildrenRows .pf-member-fields> :nth-child(5),
        #modalChildrenRows .pf-member-fields> :nth-child(6),
        #modalChildrenRows .pf-member-fields> :nth-child(7) {
            grid-column: span 2;
        }

        #modalChildrenRows .pf-member-fields> :nth-child(8) {
            grid-column: span 4;
        }

        #modalChildrenRows .pf-member-fields> :nth-child(9),
        #modalChildrenRows .pf-member-fields> :nth-child(10),
        #modalChildrenRows .pf-member-fields> :nth-child(11),
        #modalChildrenRows .pf-member-fields> :nth-child(12) {
            grid-column: span 2;
        }

        @media (max-width: 700px) {
            #modalChildrenRows .pf-member-fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            #modalChildrenRows .pf-member-fields>* {
                grid-column: span 1 !important;
            }

            #modalChildrenRows .pf-member-fields> :nth-child(1),
            #modalChildrenRows .pf-member-fields> :nth-child(2),
            #modalChildrenRows .pf-member-fields> :nth-child(3),
            #modalChildrenRows .pf-member-fields> :nth-child(8) {
                grid-column: span 2 !important;
            }
        }

        .pf-modal {
            max-width: 860px;
            width: 96%;
            border-radius: 4px;
            padding: 0;
            display: flex;
            flex-direction: column;
            max-height: 92vh;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
        }

        .pf-modal>form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-height: 92vh;
            min-height: 0;
        }

        .pf-modal select,
        .pf-modal option {
            text-transform: none !important;
            max-width: none !important;
        }

        .pf-modal-header {
            background: #1d2448;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .pf-modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pf-modal-logo img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .pf-modal-republic {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .pf-modal-barangay {
            font-size: 12px;
            color: #fff;
            font-weight: 600;
        }

        .pf-modal-formtitle {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .pf-close-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }

        .pf-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .pf-body {
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
            background: #f9f9f7;
            padding: 0;
        }

        .pf-section {
            background: #fff;
            border: 1px solid #d0d5e0;
            margin: 14px 16px;
            border-radius: 2px;
        }

        .pf-section-bar {
            background: #1d2448;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            padding: 7px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pf-field-row {
            display: grid;
            gap: 0;
            border-bottom: 1px solid #e8eaf0;
        }

        .pf-field-row:last-child {
            border-bottom: none;
        }

        .pf-cols-1 {
            grid-template-columns: 1fr;
        }

        .pf-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .pf-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .pf-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .pf-field {
            border-right: 1px solid #e8eaf0;
            padding: 0;
        }

        .pf-field:last-child {
            border-right: none;
        }

        .pf-field-label {
            font-size: 9.5px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 5px 10px 2px;
            background: #fafbfd;
            border-bottom: 1px solid #eee;
        }

        .pf-input {
            width: 100%;
            border: none;
            outline: none;
            padding: 7px 10px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            color: #1a1d2e;
            background: #fff;
            box-sizing: border-box;
            transition: background .15s;
        }

        .pf-input:focus {
            background: #f0f4ff;
        }

        select.pf-input {
            appearance: auto;
            cursor: pointer;
        }

        .pf-date-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .pf-date-wrap .pf-date-input {
            padding-right: 32px;
            cursor: pointer;
        }

        .pf-date-icon {
            position: absolute;
            right: 10px;
            color: #9aa0b4;
            font-size: 13px;
            pointer-events: none;
        }

        .pf-date-input::-webkit-calendar-picker-indicator {
            opacity: 0;
            position: absolute;
            right: 0;
            width: 32px;
            height: 100%;
            cursor: pointer;
        }

        .pf-check-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding: 10px 14px;
            font-size: 12px;
            color: #4a5068;
            border-top: 1px solid #eee;
        }

        .pf-check-label {
            font-weight: 700;
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .pf-check {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .pf-check input[type="checkbox"] {
            width: 13px;
            height: 13px;
            cursor: pointer;
        }

        .pf-married-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 6px 10px;
            padding: 6px 10px;
            border: 1px solid #d0d5e0;
            border-radius: 5px;
            color: #4a5068;
            font-size: 12px;
            cursor: pointer;
            background: #fff;
        }

        .pf-married-toggle:has(input:checked) {
            background: #1d2448;
            border-color: #1d2448;
            color: #fff;
        }

        .pf-married-toggle input {
            accent-color: #1d2448;
        }

        .pf-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #f0f2f8;
            border-top: 2px solid #1d2448;
            flex-shrink: 0;
        }

        .pf-btn {
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all .2s;
            font-family: 'Poppins', sans-serif;
        }

        .pf-btn--primary {
            background: #1d2448;
            color: #fff;
            border: 2px solid #1d2448;
        }

        .pf-btn--primary:hover {
            background: #2e3a6e;
            border-color: #2e3a6e;
        }

        .pf-btn--outline {
            background: #fff;
            color: #1d2448;
            border: 2px solid #1d2448;
        }

        .pf-btn--outline:hover {
            background: #f0f2f8;
        }

        .pf-upper {
            text-transform: uppercase;
        }

        .pf-upper::placeholder {
            text-transform: uppercase;
        }

        /* Household head card */
        .hh-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #9aa0b4;
            margin-bottom: 20px;
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

        .hh-head-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 14px;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .hh-head-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #1d2448;
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .hh-head-info {
            flex: 1;
            min-width: 200px;
        }

        .hh-head-info h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hh-head-badge {
            font-size: 11px;
            font-weight: 600;
            background: #eef0fb;
            color: #1d2448;
            padding: 3px 10px;
            border-radius: 100px;
        }

        .hh-head-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 13px;
            color: #6b7280;
        }

        .hh-head-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .hh-head-meta i {
            color: #9aa0b4;
            font-size: 12px;
        }

        .hh-head-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .hh-upload-gallery {
            margin: 0 0 22px;
            padding: 16px;
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 12px;
        }

        .hh-upload-gallery__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .hh-upload-gallery__header h3 {
            margin: 0;
            color: #1d2448;
            font-size: 15px;
        }

        .hh-upload-gallery__header h3 i {
            color: #5b6fd6;
            margin-right: 6px;
        }

        .hh-upload-gallery__header p {
            margin: 4px 0 0;
            color: #9aa0b4;
            font-size: 12px;
        }

        .hh-upload-gallery__count {
            padding: 4px 10px;
            color: #5b6fd6;
            background: #eef0fb;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .hh-upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }

        .hh-upload-card {
            overflow: hidden;
            color: #1a1d2e;
            background: #fafbfe;
            border: 1px solid #e8ecf4;
            border-radius: 9px;
            text-decoration: none;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .hh-upload-card:hover {
            border-color: #aeb8ed;
            box-shadow: 0 4px 14px rgba(29, 36, 72, .1);
            transform: translateY(-1px);
        }

        .hh-upload-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 112px;
            color: #5b6fd6;
            background: #f1f3fb;
        }

        .hh-upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hh-upload-preview>i {
            font-size: 30px;
        }

        .hh-upload-preview>span {
            margin-left: 7px;
            font-size: 12px;
            font-weight: 700;
        }

        .hh-upload-card__body {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding: 9px 10px 10px;
        }

        .hh-upload-card__body strong {
            overflow: hidden;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .hh-upload-card__body span {
            overflow: hidden;
            color: #6b7280;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .hh-upload-card__body small {
            margin-top: 3px;
            color: #5b6fd6;
            font-size: 10.5px;
            font-weight: 600;
        }

        .hh-upload-gallery__empty {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 76px;
            color: #9aa0b4;
            border: 1px dashed #dfe3ef;
            border-radius: 8px;
            font-size: 12px;
        }

        .hh-upload-gallery__empty i {
            color: #b5bdd2;
            font-size: 20px;
        }

        .hh-members-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .hh-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef0fb;
            color: #1d2448;
            font-size: 12px;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            margin-left: 6px;
        }

        .hh-rel-badge {
            font-size: 11px;
            font-weight: 600;
            background: #f5f6fa;
            color: #4a5068;
            padding: 3px 10px;
            border-radius: 100px;
            border: 1px solid #e2e5ef;
        }

        .hh-rel-badge--former {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .hh-philhealth {
            font-family: monospace;
            font-size: 12px;
            background: #f5f6fa;
            padding: 2px 6px;
            border-radius: 4px;
            color: #4a5068;
        }
    </style>

    <script>
        function toggleEditNumFamilies(val) {
            const input = document.getElementById('edit_num_families');
            const grpRow = document.getElementById('edit_shared_group_row');
            if (val === 'Shared') {
                if (grpRow) grpRow.style.display = 'block';
                if (input && parseInt(input.value, 10) < 2) input.value = 2;
            } else {
                if (grpRow) grpRow.style.display = 'none';
                if (input) input.value = 1;
            }
        }

        function togglePwdType(input, wrapId, isCheckbox) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;
            const show = isCheckbox ? input.checked : (input.value === '1');
            wrap.style.display = show ? 'block' : 'none';
            if (!show) {
                const inp = wrap.querySelector('input[type="text"]');
                if (inp) inp.value = '';
            }
            if (show) {
                const inp = wrap.querySelector('input[type="text"]');
                if (inp) inp.focus();
            }
        }

        function toggleIdUpload(inputId, wrapId, mode) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;
            let show;
            if (mode === 'checkbox' || mode === undefined) {
                const cb = document.getElementById(inputId);
                show = cb && cb.checked;
            } else {
                const rb = document.getElementById(inputId);
                show = rb && rb.value === '1';
            }
            wrap.style.display = show ? 'block' : 'none';
            if (!show) {
                const fileEl = wrap.querySelector('input[type="file"]');
                if (fileEl) fileEl.value = '';
            }
        }

        function toggleMemberPwdId(show) {
            document.getElementById('em_pwd_type_wrap').style.display = show ? 'block' : 'none';
            document.getElementById('em_id_pwd_wrap').style.display = show ? 'block' : 'none';
            if (!show) {
                document.getElementById('em_pwd_type').value = '';
                const fileEl = document.getElementById('em_id_pwd_file');
                if (fileEl) fileEl.value = '';
            }
        }

        function toggleMemberSeniorId(show) {
            const wrap = document.getElementById('em_id_senior_wrap');
            if (!wrap) return;
            wrap.style.display = show ? 'block' : 'none';
            if (!show) {
                const fileEl = document.getElementById('em_id_senior_file');
                if (fileEl) fileEl.value = '';
            }
        }

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        async function loadHouseholdMember(id) {
            const res = await fetch('/<?= esc($role) ?>/pii/member/' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) {
                alert('Unable to load this household member.');
                throw new Error('member fetch failed');
            }
            return res.json();
        }

        const usePlainNames = <?= json_encode(\App\Libraries\PiiGuard::showsPlainText()) ?>;

        function memberPlainName(member) {
            return [member.first_name, member.middle_name, member.last_name]
                .map(function (part) { return String(part || '').trim(); })
                .filter(Boolean)
                .join(' ');
        }

        function piiSet(el, src, plainText) {
            if (!el) return;
            if (usePlainNames) {
                el.textContent = plainText || '—';
                return;
            }
            el.textContent = '';
            if (!src) {
                el.textContent = '—';
                return;
            }
            const img = document.createElement('img');
            img.className = 'pii-text pii-text--name';
            img.alt = '';
            img.draggable = false;
            img.src = src;
            el.appendChild(img);
        }

        async function openEditMember(id) {
            const m = await loadHouseholdMember(id);
            // Set form action with member id
            document.getElementById('editMemberForm').action =
                '/<?= $role ?>/census/member/update/' + m.id;
            // Fill fields
            document.getElementById('em_last_name').value = m.last_name || '';
            document.getElementById('em_first_name').value = m.first_name || '';
            document.getElementById('em_middle_name').value = m.middle_name || '';
            document.getElementById('em_suffix').value = m.suffix || '';
            document.getElementById('em_relationship').value = m.relationship || '';
            document.getElementById('em_dob').value = m.date_of_birth || '';
            document.getElementById('em_occupation').value = m.occupation || '';
            const emWork = document.getElementById('em_work');
            if (emWork) emWork.value = m.work_detail || '';
            document.getElementById('em_income').value = m.monthly_income || 0;
            const emEducation = document.getElementById('em_education');
            const savedEducation = m.educational_attainment || '';
            if (emEducation && savedEducation && !Array.from(emEducation.options).some(function(opt) { return opt.value === savedEducation || opt.text === savedEducation; })) {
                emEducation.add(new Option(savedEducation, savedEducation));
            }
            if (emEducation) emEducation.value = savedEducation;
            document.getElementById('em_philhealth').value = m.philhealth_no || '';
            // PWD fields
            const isPwd = m.is_pwd == 1;
            document.getElementById('em_pwd_yes').checked = isPwd;
            document.getElementById('em_pwd_no').checked = !isPwd;
            document.getElementById('em_pwd_type_wrap').style.display = isPwd ? 'block' : 'none';
            document.getElementById('em_pwd_type').value = m.pwd_type || '';
            // PWD ID existing file info
            const idWrap = document.getElementById('em_id_pwd_wrap');
            const idExisting = document.getElementById('em_id_pwd_existing');
            const idLink = document.getElementById('em_id_pwd_link');
            const idHint = document.getElementById('em_id_pwd_hint');
            const idFile = document.getElementById('em_id_pwd_file');
            idWrap.style.display = isPwd ? 'block' : 'none';
            if (isPwd && m.id_pwd_path) {
                idExisting.style.display = 'inline-flex';
                idExisting.style.alignItems = 'center';
                idLink.href = '/household-files/' + m.id_pwd_path.replace(/^uploads\//, '').split('/').map(encodeURIComponent).join('/');
                idHint.textContent = 'Upload scanned PWD ID (PDF, JPG, PNG — max 5 MB). Leave blank to keep existing file.';
            } else {
                idExisting.style.display = 'none';
                idLink.href = '';
                idHint.textContent = 'Upload scanned PWD ID (PDF, JPG, PNG — max 5 MB).';
            }
            if (idFile) idFile.value = '';
            const isSenior = m.date_of_birth && new Date(m.date_of_birth).toString() !== 'Invalid Date' ?
                ((Date.now() - new Date(m.date_of_birth).getTime()) / 31557600000 >= 60) :
                false;
            const seniorCheck = document.getElementById('em_senior_yes');
            const seniorWrap = document.getElementById('em_id_senior_wrap');
            const seniorExisting = document.getElementById('em_id_senior_existing');
            const seniorLink = document.getElementById('em_id_senior_link');
            const seniorHint = document.getElementById('em_id_senior_hint');
            if (seniorCheck) {
                seniorCheck.checked = isSenior;
                toggleMemberSeniorId(isSenior);
            }
            if (seniorExisting && seniorLink && seniorHint) {
                seniorExisting.style.display = m.id_senior_path ? 'inline-flex' : 'none';
                seniorLink.href = m.id_senior_path ? '/' + m.id_senior_path : '';
                seniorHint.textContent = m.id_senior_path ?
                    'Upload scanned ID (PDF, JPG, PNG — max 5 MB). Leave blank to keep existing file.' :
                    'Upload scanned ID (PDF, JPG, PNG — max 5 MB). Optional.';
            }
            const seniorFile = document.getElementById('em_id_senior_file');
            if (seniorFile) seniorFile.value = '';
            openModal('editMemberModal');
        }

        // ── Edit Head Form submit validation ─────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            const editHeadForm = document.getElementById('editHeadForm');
            if (editHeadForm) {
                editHeadForm.addEventListener('submit', function(e) {
                    const idChecks = [{
                            cb: 'input[name="is_4ps"]',
                            file: 'input[name="id_4ps"]',
                            wrap: 'head_edit_id_4ps_wrap',
                            label: '4Ps Beneficiary ID'
                        },
                        {
                            cb: 'input[name="is_solo_parent"]',
                            file: 'input[name="id_solo_parent"]',
                            wrap: 'head_edit_id_solo_wrap',
                            label: 'Solo Parent ID'
                        },
                        {
                            cb: 'input[name="is_pwd"]',
                            file: 'input[name="id_pwd"]',
                            wrap: 'head_edit_id_pwd_wrap',
                            label: 'PWD ID'
                        },
                    ];
                    let idErrors = [];
                    idChecks.forEach(cfg => {
                        const cbEl = editHeadForm.querySelector(cfg.cb);
                        if (cbEl && cbEl.checked) {
                            const fileEl = editHeadForm.querySelector(cfg.file);
                            const hasFile = fileEl && fileEl.files && fileEl.files.length > 0;
                            const existingOnLabel = editHeadForm.querySelector('#' + cfg.wrap + ' a[target="_blank"]');
                            const hasExisting = existingOnLabel !== null;
                            if (!hasFile && !hasExisting) {
                                idErrors.push(cfg.label);
                                const wrapEl = document.getElementById(cfg.wrap);
                                if (wrapEl) {
                                    wrapEl.style.outline = '2px solid #c0392b';
                                    wrapEl.style.borderRadius = '8px';
                                    setTimeout(() => {
                                        wrapEl.style.outline = '';
                                    }, 3000);
                                }
                            }
                        }
                    });
                    if (idErrors.length > 0) {
                        e.preventDefault();
                        const msg = idErrors.length === 1 ?
                            '<strong>' + idErrors[0] + '</strong> upload is required (no existing ID on file).' :
                            'Please upload the following required IDs (no existing files): <strong>' + idErrors.join(', ') + '</strong>.';
                        alert(msg.replace(/<[^>]*>/g, ''));
                    }
                });
            }

            // ── Edit Member Form submit validation ─────────────────────────────
            const editMemberForm = document.getElementById('editMemberForm');
            if (editMemberForm) {
                editMemberForm.addEventListener('submit', function(e) {
                    const pwdYes = document.getElementById('em_pwd_yes');
                    if (pwdYes && pwdYes.checked) {
                        const fileEl = document.getElementById('em_id_pwd_file');
                        const hasFile = fileEl && fileEl.files && fileEl.files.length > 0;
                        const existingEl = document.getElementById('em_id_pwd_existing');
                        const hasExisting = existingEl && existingEl.style.display !== 'none';
                        if (!hasFile && !hasExisting) {
                            e.preventDefault();
                            alert('PWD ID upload is required for this member (no existing ID on file).');
                            const wrapEl = document.getElementById('em_id_pwd_wrap');
                            if (wrapEl) {
                                wrapEl.style.outline = '2px solid #c0392b';
                                wrapEl.style.borderRadius = '8px';
                                setTimeout(() => {
                                    wrapEl.style.outline = '';
                                }, 3000);
                            }
                        }
                    }
                });
            }
        });

        // Live uppercase + alpha-only + philhealth-only
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('pf-upper')) {
                const pos = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(pos, pos);
            }
            if (e.target.classList.contains('pf-alpha')) {
                const pos = e.target.selectionStart;
                e.target.value = e.target.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s\-'.]/g, '');
                e.target.setSelectionRange(pos, pos);
            }
            if (e.target.classList.contains('pf-philhealth')) {
                e.target.value = e.target.value.replace(/\D/g, '');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('pf-alpha')) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                const allowed = [8, 9, 13, 27, 32, 37, 38, 39, 40, 35, 36, 45, 46];
                if (allowed.includes(e.keyCode)) return;
                if (e.key >= '0' && e.key <= '9') e.preventDefault();
            }
            if (e.target.classList.contains('pf-philhealth')) {
                const allowed = [8, 9, 13, 27, 46, 37, 38, 39, 40, 35, 36];
                if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].includes(e.keyCode)) return;
                if (allowed.includes(e.keyCode)) return;
                if (e.key < '0' || e.key > '9') e.preventDefault();
            }
        });

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );

        // ── Add Member Modal row helpers ───────────────────────────────────────
        let _mChildIdx = 1;
        let _mOtherIdx = 1;

        function modalChildRowHTML() {
            const i = _mChildIdx++;
            return `<div class="pf-member-card">
                <div class="pf-member-card-inner">
                    <div class="pf-member-fields">
                        <div class="pf-field pf-field--inline"><div class="pf-field-label">Last Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="child_last_name[]" placeholder="LAST NAME"></div>
                        <div class="pf-field pf-field--inline"><div class="pf-field-label">First Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="child_first_name[]" placeholder="FIRST NAME"></div>
                        <div class="pf-field pf-field--inline"><div class="pf-field-label">Middle Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="child_middle_name[]" placeholder="MIDDLE NAME"></div>
                        <div class="pf-field pf-field--inline pf-field--xs">
                            <div class="pf-field-label">Suffix</div>
                            <select class="pf-input" name="child_suffix[]"><option value="">—NONE—</option><option>Jr</option><option>Sr</option><option>II</option><option>III</option></select>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">Date of Birth</div>
                            <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="child_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--xs">
                            <div class="pf-field-label">Gender</div>
                            <select class="pf-input" name="child_gender[]"><option value="">—Select—</option><option>Male</option><option>Female</option></select>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">Civil Status</div>
                            <input type="hidden" name="child_marital_status[]" value="Single">
                            <label class="pf-married-toggle"><input type="checkbox" onchange="toggleChildMarried(this)"><span>Married</span></label>
                        </div>
                        <div class="pf-field pf-field--inline">
                            <div class="pf-field-label">Occupation</div>
                            <div class="pf-occ-wrap">
                                <div class="pf-occ-pills">
                                    <button type="button" class="pf-occ-pill" data-val="STUDENT" onclick="setOcc(this,'STUDENT')">Student</button>
                                    <button type="button" class="pf-occ-pill" data-val="OUT OF SCHOOL" onclick="setOcc(this,'OUT OF SCHOOL')">Out of School</button>
                                </div>
                                <input type="text" class="pf-input pf-upper pf-alpha" name="child_occupation[]" placeholder="OR TYPE EXACT OCCUPATION">
                                <div class="pf-grade-wrap">
                                    <div class="pf-grade-label">Current Grade / Year</div>
                                    <select class="pf-input" name="child_grade[]">
                                        <option value="">— Select Grade / Year —</option>
                                        <optgroup label="Elementary"><option>Grade 1</option><option>Grade 2</option><option>Grade 3</option><option>Grade 4</option><option>Grade 5</option><option>Grade 6</option></optgroup>
                                        <optgroup label="Junior High School"><option>Grade 7</option><option>Grade 8</option><option>Grade 9</option><option>Grade 10</option></optgroup>
                                        <optgroup label="Senior High School"><option>Grade 11</option><option>Grade 12</option></optgroup>
                                        <optgroup label="College / University"><option>1st Year College</option><option>2nd Year College</option><option>3rd Year College</option><option>4th Year College</option><option>5th Year College</option></optgroup>
                                        <optgroup label="Vocational / Technical"><option>1st Year Tech-Voc</option><option>2nd Year Tech-Voc</option></optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm"><div class="pf-field-label">Monthly Income (₱)</div><input type="number" class="pf-input" name="child_income[]" placeholder="0.00" min="0" step="0.01"></div>
                        <div class="pf-field pf-field--inline pf-field--sm"><div class="pf-field-label">PhilHealth No.</div><input type="text" class="pf-input pf-philhealth" name="child_philhealth[]" placeholder="00000000000" maxlength="12" inputmode="numeric"></div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">Voter?</div>
                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                <label class="pf-radio"><input type="radio" name="child_voter[${i}]" value="1"> <span>Yes</span></label>
                                <label class="pf-radio"><input type="radio" name="child_voter[${i}]" value="0" checked> <span>No</span></label>
                            </div>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">PWD?</div>
                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                <label class="pf-radio"><input type="radio" name="child_pwd[${i}]" value="1" onchange="togglePwdType(this,'child_pwd_wrap_${i}')"> <span>Yes</span></label>
                                <label class="pf-radio"><input type="radio" name="child_pwd[${i}]" value="0" checked onchange="togglePwdType(this,'child_pwd_wrap_${i}')"> <span>No</span></label>
                            </div>
                            <div id="child_pwd_wrap_${i}" style="display:none;margin-top:4px;">
                                <input type="text" class="pf-input" name="child_pwd_type[]" placeholder="Specify disability…" maxlength="120">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                </div>
            </div>`;
        }

        function modalOtherRowHTML() {
            const i = _mOtherIdx++;
            return `<div class="pf-member-card">
                <div class="pf-member-card-inner">
                    <div class="pf-member-fields">
                        <div class="pf-field pf-field--inline"><div class="pf-field-label">Last Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="other_last_name[]" placeholder="LAST NAME"></div>
                        <div class="pf-field pf-field--inline"><div class="pf-field-label">First Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="other_first_name[]" placeholder="FIRST NAME"></div>
                        <div class="pf-field pf-field--inline"><div class="pf-field-label">Middle Name</div><input type="text" class="pf-input pf-upper pf-alpha" name="other_middle_name[]" placeholder="MIDDLE NAME"></div>
                        <div class="pf-field pf-field--inline pf-field--xs">
                            <div class="pf-field-label">Suffix</div>
                            <select class="pf-input" name="other_suffix[]"><option value="">—NONE—</option><option>Jr</option><option>Sr</option><option>II</option><option>III</option></select>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">Date of Birth</div>
                            <div class="pf-date-wrap"><input type="date" class="pf-input pf-date-input" name="other_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--xs">
                            <div class="pf-field-label">Gender</div>
                            <select class="pf-input" name="other_gender[]"><option value="">—Select—</option><option>Male</option><option>Female</option></select>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">Relationship</div>
                            <select class="pf-input" name="other_relationship[]">
                                <option value="">— Select —</option>
                                <option>Father</option><option>Mother</option><option>Sibling</option><option>Grandparent</option>
                                <option>Grandchild</option><option>Aunt/Uncle</option><option>Cousin</option>
                                <option>Other Relative</option><option>Non-relative</option>
                            </select>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">Voter?</div>
                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                <label class="pf-radio"><input type="radio" name="other_voter[${i}]" value="1"> <span>Yes</span></label>
                                <label class="pf-radio"><input type="radio" name="other_voter[${i}]" value="0" checked> <span>No</span></label>
                            </div>
                        </div>
                        <div class="pf-field pf-field--inline pf-field--sm">
                            <div class="pf-field-label">PWD?</div>
                            <div style="display:flex;gap:12px;padding:7px 10px;">
                                <label class="pf-radio"><input type="radio" name="other_pwd[${i}]" value="1" onchange="togglePwdType(this,'other_pwd_wrap_${i}')"> <span>Yes</span></label>
                                <label class="pf-radio"><input type="radio" name="other_pwd[${i}]" value="0" checked onchange="togglePwdType(this,'other_pwd_wrap_${i}')"> <span>No</span></label>
                            </div>
                            <div id="other_pwd_wrap_${i}" style="display:none;margin-top:4px;">
                                <input type="text" class="pf-input" name="other_pwd_type[]" placeholder="Specify disability…" maxlength="120">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                </div>
            </div>`;
        }

        function addChildRowModal() {
            document.getElementById('modalChildrenRows').insertAdjacentHTML('beforeend', modalChildRowHTML());
        }

        function addOtherRowModal() {
            document.getElementById('modalOtherRows').insertAdjacentHTML('beforeend', modalOtherRowHTML());
        }

        function removeRow(btn) {
            btn.closest('.pf-member-card').remove();
        }

        function toggleChildMarried(checkbox) {
            const row = checkbox.closest('.pf-member-card');
            const status = row ? row.querySelector('input[name="child_marital_status[]"]') : null;
            if (status) status.value = checkbox.checked ? 'Married' : 'Single';
        }

        // Occupation quick-pick: toggle grade field visibility
        function setOcc(btn, value) {
            const wrap = btn.closest('.pf-occ-wrap');
            const input = wrap.querySelector('input[name="child_occupation[]"]');
            const pills = wrap.querySelectorAll('.pf-occ-pill');
            const alreadyActive = btn.classList.contains('active');
            pills.forEach(p => p.classList.remove('active'));
            if (alreadyActive) {
                input.value = '';
            } else {
                btn.classList.add('active');
                input.value = value;
            }
            input.dispatchEvent(new Event('input'));
            toggleGradeField(wrap, input.value);
        }

        function toggleGradeField(wrap, occValue) {
            const gradeWrap = wrap.querySelector('.pf-grade-wrap');
            if (!gradeWrap) return;
            gradeWrap.classList.toggle('visible', occValue.trim().toUpperCase() === 'STUDENT');
        }

        // Sync pill active state + grade field when typing manually
        document.addEventListener('input', function(e) {
            if (e.target.name === 'child_occupation[]') {
                const wrap = e.target.closest('.pf-occ-wrap');
                if (!wrap) return;
                const val = e.target.value.trim().toUpperCase();
                wrap.querySelectorAll('.pf-occ-pill').forEach(p => {
                    p.classList.toggle('active', p.dataset.val === val);
                });
                toggleGradeField(wrap, val);
            }
        });

        // ── Deceased Head modal ───────────────────────────────────────────────
        function openDeceasedHeadModal() {
            openModal('deceasedHeadModal');
        }

        // ── Deceased Member modal ─────────────────────────────────────────────
        async function openDeceasedMemberModal(id) {
            const m = await loadHouseholdMember(id);
            document.getElementById('dm-avatar').textContent = '?';
            piiSet(document.getElementById('dm-name'), m.name_img || m.full_name_img, memberPlainName(m));
            document.getElementById('dm-rel').textContent =
                m.relationship ? m.relationship.charAt(0).toUpperCase() + m.relationship.slice(1) : '—';
            document.getElementById('dm-year').value = '';
            document.getElementById('deceasedMemberForm').action =
                '/' + '<?= esc($role) ?>' + '/census/member/deceased/' + m.id;
            openModal('deceasedMemberModal');
        }

        // ── Separate Household modal ─────────────────────────────────────────
        async function openSeparateModal(id) {
            const m = typeof id === 'object' ? id : await loadHouseholdMember(id);
            piiSet(document.getElementById('sep-member-name'), m.full_name_img || m.name_img, memberPlainName(m));
            document.getElementById('sep-member-rel').textContent = m.relationship || '—';
            document.getElementById('separateForm').action =
                '/<?= $role ?>/census/member/separate/' + m.id;
            document.querySelector('#separateModal h3').innerHTML = '<i class="fas fa-home"></i> Separate Household';
            document.getElementById('sep_reason').value = '';
            document.getElementById('sep_civil_status').value = '';
            document.getElementById('sep_add_family').value = '0';
            document.getElementById('sep_new_address').value = document.getElementById('sep_current_address').value;
            document.getElementById('sep_destination_household_no').value = '';
            document.getElementById('sep_destination_household_no').required = false;
            document.getElementById('destination-household-wrap').style.display = 'none';
            // Reset type pills
            document.getElementById('pill-separate').style.borderColor = '#e67e22';
            document.getElementById('pill-transfer').style.borderColor = '#e2e5ef';
            document.getElementById('sep_type').value = 'Separate Household';
            openModal('separateModal');
        }

        async function openAddFamilyModal(id) {
            const m = await loadHouseholdMember(id);
            if ((m.relationship || '').toLowerCase() !== 'child' || m.marital_status !== 'Married') {
                alert('Only a child marked as Married can add a family.');
                return;
            }
            await openSeparateModal(m);
            document.querySelector('#separateModal h3').innerHTML = '<i class="fas fa-users"></i> Add Family';
            document.getElementById('sep_add_family').value = '1';
            document.getElementById('sep_reason').value = 'Married';
        }
    </script>

    <!-- ══ SEPARATE HOUSEHOLD MODAL ══ -->
    <div class="db-modal-overlay" id="separateModal" onclick="if(event.target===this)closeModal('separateModal')">
        <div class="db-modal" style="max-width:540px;width:96%;">
            <div class="db-modal-header" style="background:linear-gradient(135deg,#e67e22,#ca6f1e);">
                <h3 style="color:#fff;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-home"></i> Separate Household
                </h3>
                <button class="db-modal-close" style="background:rgba(255,255,255,.15);color:#fff;"
                    onclick="closeModal('separateModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="db-modal-body" style="max-height:65vh;overflow-y:auto;">

                <!-- Member being separated -->
                <div style="background:#fff8f0;border:1px solid #fde8c8;border-radius:10px;padding:12px 16px;margin-bottom:18px;">
                    <div style="font-size:11px;font-weight:700;color:#b07a00;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">
                        <i class="fas fa-user"></i> Member Being Separated
                    </div>
                    <div style="font-size:14px;font-weight:700;color:#1a1d2e;" id="sep-member-name">—</div>
                    <div style="font-size:12px;color:#9aa0b4;margin-top:2px;">
                        Relationship: <span id="sep-member-rel">—</span>
                        &nbsp;·&nbsp; From Household #<?= esc($householdId) ?>
                    </div>
                </div>

                <form id="separateForm" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="add_family" id="sep_add_family" value="0">
                    <input type="hidden" id="sep_current_zone" value="<?= esc($household['zone'] ?? '') ?>">
                    <input type="hidden" id="sep_current_address" value="<?= esc($household['address'] ?? '') ?>">

                    <!-- Separation type -->
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:6px;">
                            Separation Type <span style="color:#dc3545;">*</span>
                        </label>
                        <div style="display:flex;gap:10px;">
                            <div id="pill-separate"
                                style="flex:1;border:1.5px solid #e67e22;border-radius:9px;padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;transition:border-color .2s;background:#fff;"
                                onclick="document.getElementById('sep_type').value='Separate Household';
                                         document.getElementById('pill-separate').style.borderColor='#e67e22';
                                         document.getElementById('pill-transfer').style.borderColor='#e2e5ef';
                                         document.getElementById('destination-household-wrap').style.display='none';
                                         document.getElementById('sep_destination_household_no').required = false;
                                         document.getElementById('sep_destination_household_no').value = '';">
                                <i class="fas fa-house-user" style="color:#e67e22;"></i> Separate Household
                            </div>
                            <div id="pill-transfer"
                                style="flex:1;border:1.5px solid #e2e5ef;border-radius:9px;padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;transition:border-color .2s;background:#fff;"
                                onclick="document.getElementById('sep_type').value='Transfer Member';
                                         document.getElementById('pill-transfer').style.borderColor='#e67e22';
                                         document.getElementById('pill-separate').style.borderColor='#e2e5ef';
                                         document.getElementById('destination-household-wrap').style.display='block';
                                         document.getElementById('sep_destination_household_no').required = true;">
                                <i class="fas fa-exchange-alt" style="color:#e67e22;"></i> Transfer Member
                            </div>
                        </div>
                        <input type="hidden" name="separation_type" id="sep_type" value="Separate Household">
                    </div>

                    <!-- New zone + civil status -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:5px;">New Zone</label>
                            <select name="new_zone" id="sep_zone" onchange="if(this.value === document.getElementById('sep_current_zone').value){ document.querySelector('input[name=\'new_address\']').value = document.getElementById('sep_current_address').value; }"
                                style="width:100%;padding:9px 12px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
                                <option value="<?= esc($household['zone'] ?? '') ?>">— Same as current —</option>
                                <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'] as $z): ?>
                                    <option value="<?= esc($z) ?>"><?= $z ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:5px;">New Civil Status</label>
                            <select name="new_civil_status" id="sep_civil_status"
                                style="width:100%;padding:9px 12px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
                                <option value="">— Unchanged —</option>
                                <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $cs): ?>
                                    <option><?= $cs ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- New address -->
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:5px;">New Address</label>
                        <input type="text" name="new_address" id="sep_new_address"
                            placeholder="e.g. Purok 3, Zone 4, Barangay Bacolod"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:inherit;outline:none;box-sizing:border-box;">
                    </div>

                    <!-- Destination household -->
                    <div id="destination-household-wrap" style="display:none;margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:5px;">
                            Destination Household <span style="color:#dc3545;">*</span>
                        </label>
                        <select name="destination_household_no" id="sep_destination_household_no"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:inherit;outline:none;box-sizing:border-box;">
                            <option value="">Select destination household</option>
                            <?php foreach ($transferHouseholds ?? [] as $transferHousehold): ?>
                                <option value="<?= esc($transferHousehold['household_no']) ?>">
                                    Household #<?= esc($transferHousehold['household_no']) ?>
                                    · <?= esc(($transferHousehold['first_name'] ?? '') . ' ' . ($transferHousehold['last_name'] ?? '')) ?>
                                    <?php if (! empty($transferHousehold['address'])): ?>
                                        · <?= esc($transferHousehold['address']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Change reason pills + free text -->
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:5px;">
                            Change Reason <span style="color:#dc3545;">*</span>
                        </label>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;" id="sep-reason-pills">
                            <?php foreach (['Married', 'Employment Relocation', 'Family Dispute', 'Independent Living', 'Other'] as $reason): ?>
                                <button type="button"
                                    style="padding:4px 12px;border:1.5px solid #e2e5ef;border-radius:20px;font-size:12px;background:#fff;cursor:pointer;font-family:inherit;transition:all .15s;"
                                    onclick="document.getElementById('sep_reason').value='<?= $reason ?>';
                                             document.querySelectorAll('#sep-reason-pills button').forEach(b=>b.style.borderColor='#e2e5ef');
                                             this.style.borderColor='#e67e22';">
                                    <?= $reason ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="change_reason" id="sep_reason" required
                            placeholder="e.g. Married — or type a custom reason"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:inherit;outline:none;box-sizing:border-box;">
                    </div>

                    <!-- Proof verification checkbox -->
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:#4a5068;cursor:pointer;margin-bottom:18px;">
                        <input type="checkbox" name="proof_verified" value="1" style="width:16px;height:16px;">
                        New address &amp; proof of separation verified
                    </label>

                    <!-- Info note -->
                    <div style="background:#f5f7ff;border:1px solid #dde2f5;border-radius:9px;padding:11px 14px;font-size:12px;color:#4a5068;display:flex;gap:8px;align-items:flex-start;">
                        <i class="fas fa-info-circle" style="color:#5b6fd6;margin-top:1px;flex-shrink:0;"></i>
                        <span>This creates a <strong>pending separation request</strong> for the secretary to review and approve.
                            For a transfer, the member is linked to the selected destination household instead of creating a new one.
                            For a household split, the system generates a new household number, promotes the member to household head,
                            and logs the change in the audit trail.</span>
                    </div>
                </form>
            </div>
            <div class="db-modal-footer">
                <button class="db-btn db-btn--outline" onclick="closeModal('separateModal')">Cancel</button>
                <button class="db-btn" style="background:#e67e22;color:#fff;display:flex;align-items:center;gap:6px;"
                    onclick="document.getElementById('separateForm').submit()">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </div>
    </div>

    <!-- ══ MARK HEAD DECEASED MODAL ══ -->
    <div class="db-modal-overlay" id="deceasedHeadModal" onclick="if(event.target===this)closeModal('deceasedHeadModal')">
        <div class="dec-modal">
            <!-- Danger banner -->
            <div class="dec-modal__banner">
                <div class="dec-modal__banner-icon">
                    <i class="fas fa-cross"></i>
                </div>
                <div class="dec-modal__banner-text">
                    <div class="dec-modal__banner-title">Mark as Deceased</div>
                    <div class="dec-modal__banner-sub">Household Head Record</div>
                </div>
                <button class="dec-modal__close" onclick="closeModal('deceasedHeadModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="/<?= esc($role) ?>/census/head/deceased/<?= esc($householdId) ?>" method="post" id="deceasedHeadForm">
                <?= csrf_field() ?>
                <div class="dec-modal__body">

                    <!-- Person card -->
                    <div class="dec-modal__person-card">
                        <div class="dec-modal__person-avatar">
                            <?= strtoupper($head['first_name'][0] ?? '?') ?>
                        </div>
                        <div class="dec-modal__person-info">
                            <div class="dec-modal__person-name"><?= esc($headName) ?></div>
                            <div class="dec-modal__person-role">
                                <span class="dec-modal__person-badge">Household Head</span>
                                <span class="dec-modal__person-hh">#<?= esc($householdId) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Warning note -->
                    <div class="dec-modal__warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>This action will mark the household head as deceased and update the census record accordingly.</span>
                    </div>

                    <!-- Year of death -->
                    <div class="dec-modal__field">
                        <label class="dec-modal__label">
                            <i class="fas fa-calendar-alt"></i> Year of Death
                            <span class="dec-modal__label-opt">optional</span>
                        </label>
                        <input type="number" name="year_of_death"
                            class="dec-modal__input dec-modal__input--short"
                            min="1900" max="<?= date('Y') ?>"
                            placeholder="<?= date('Y') ?>">
                        <div class="dec-modal__hint">Leave blank if the year is unknown.</div>
                    </div>

                    <!-- Reassign head -->
                    <?php if (!empty($members)): ?>
                        <div class="dec-modal__divider"></div>
                        <div class="dec-modal__field">
                            <label class="dec-modal__label">
                                <i class="fas fa-user-shield"></i> Reassign Household Head
                                <span class="dec-modal__label-opt">optional</span>
                            </label>
                            <div class="dec-modal__hint" style="margin-bottom:8px;">
                                Choose a current member to take over as household head. The deceased head will be kept in the member list, marked as deceased.
                            </div>
                            <div class="dec-modal__select-wrap">
                                <select name="new_head_member_id" class="dec-modal__select">
                                    <option value="">— Do not reassign (head remains with deceased status) —</option>
                                    <?php foreach ($members as $mem): ?>
                                        <?php
                                        $memberBirthDate = ! empty($mem['date_of_birth']) ? date_create($mem['date_of_birth']) : null;
                                        $memberIsAdult = $memberBirthDate && $memberBirthDate->diff(new DateTimeImmutable('today'))->y >= 18;
                                        ?>
                                        <?php if (empty($mem['is_deceased']) && $memberIsAdult): ?>
                                            <option value="<?= esc($mem['id']) ?>">
                                                <?= esc($mem['first_name'] . ' ' . $mem['last_name']) ?>
                                                <?= !empty($mem['relationship']) ? ' · ' . ucfirst($mem['relationship']) : '' ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down dec-modal__select-icon"></i>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="dec-modal__footer">
                    <button type="button" class="dec-modal__btn dec-modal__btn--cancel" onclick="closeModal('deceasedHeadModal')">
                        Cancel
                    </button>
                    <button type="submit" class="dec-modal__btn dec-modal__btn--confirm"
                        onclick="return confirm('Mark <?= esc(addslashes($headName)) ?> as deceased?')">
                        <i class="fas fa-cross"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ MARK MEMBER DECEASED MODAL ══ -->
    <div class="db-modal-overlay" id="deceasedMemberModal" onclick="if(event.target===this)closeModal('deceasedMemberModal')">
        <div class="dec-modal">
            <!-- Danger banner -->
            <div class="dec-modal__banner">
                <div class="dec-modal__banner-icon">
                    <i class="fas fa-cross"></i>
                </div>
                <div class="dec-modal__banner-text">
                    <div class="dec-modal__banner-title">Mark as Deceased</div>
                    <div class="dec-modal__banner-sub">Household Member Record</div>
                </div>
                <button class="dec-modal__close" onclick="closeModal('deceasedMemberModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="/<?= esc($role) ?>/census/member/deceased/0" method="post" id="deceasedMemberForm">
                <?= csrf_field() ?>
                <input type="hidden" name="household_no" value="<?= esc($householdId) ?>">
                <div class="dec-modal__body">

                    <!-- Person card -->
                    <div class="dec-modal__person-card">
                        <div class="dec-modal__person-avatar" id="dm-avatar">?</div>
                        <div class="dec-modal__person-info">
                            <div class="dec-modal__person-name" id="dm-name">—</div>
                            <div class="dec-modal__person-role">
                                <span class="dec-modal__person-badge dec-modal__person-badge--member" id="dm-rel">—</span>
                                <span class="dec-modal__person-hh">#<?= esc($householdId) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Warning note -->
                    <div class="dec-modal__warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>This will mark the member as deceased in the household census record.</span>
                    </div>

                    <!-- Year of death -->
                    <div class="dec-modal__field">
                        <label class="dec-modal__label">
                            <i class="fas fa-calendar-alt"></i> Year of Death
                            <span class="dec-modal__label-opt">optional</span>
                        </label>
                        <input type="number" name="year_of_death" id="dm-year"
                            class="dec-modal__input dec-modal__input--short"
                            min="1900" max="<?= date('Y') ?>"
                            placeholder="<?= date('Y') ?>">
                        <div class="dec-modal__hint">Leave blank if the year is unknown.</div>
                    </div>

                </div>

                <div class="dec-modal__footer">
                    <button type="button" class="dec-modal__btn dec-modal__btn--cancel" onclick="closeModal('deceasedMemberModal')">
                        Cancel
                    </button>
                    <button type="submit" class="dec-modal__btn dec-modal__btn--confirm">
                        <i class="fas fa-cross"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* ── Deceased Modal ─────────────────────────────────────────────────── */
        .dec-modal {
            background: #fff;
            border-radius: 0;
            width: 96%;
            max-width: 460px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .22), 0 4px 16px rgba(0, 0, 0, .12);
            overflow: hidden;
            animation: dec-modal-in .22s cubic-bezier(.22, 1, .36, 1);
        }

        @keyframes dec-modal-in {
            from {
                opacity: 0;
                transform: scale(.95) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Banner */
        .dec-modal__banner {
            background: #16325c;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
        }

        .dec-modal__banner-icon {
            width: 46px;
            height: 46px;
            border-radius: 0;
            background: rgba(255, 255, 255, .15);
            border: 1.5px solid rgba(255, 255, 255, .25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }

        .dec-modal__banner-text {
            flex: 1;
        }

        .dec-modal__banner-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .dec-modal__banner-sub {
            font-size: 11.5px;
            color: rgba(255, 255, 255, .65);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .dec-modal__close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(255, 255, 255, .15);
            border: none;
            color: rgba(255, 255, 255, .8);
            width: 30px;
            height: 30px;
            border-radius: 0;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, color .15s;
        }

        .dec-modal__close:hover {
            background: rgba(255, 255, 255, .28);
            color: #fff;
        }

        /* Body */
        .dec-modal__body {
            padding: 22px 22px 8px;
        }

        /* Person card */
        .dec-modal__person-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fafbfc;
            border: 1.5px solid #e8ecf4;
            border-radius: 0;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .dec-modal__person-avatar {
            width: 46px;
            height: 46px;
            border-radius: 0;
            background: #16325c;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
            letter-spacing: -1px;
        }

        .dec-modal__person-name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1d2e;
        }

        .dec-modal__person-role {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }

        .dec-modal__person-badge {
            font-size: 11px;
            font-weight: 600;
            background: #eef0fb;
            color: #1d2448;
            padding: 2px 9px;
            border-radius: 20px;
        }

        .dec-modal__person-badge--member {
            background: #f0f2f8;
            color: #4a5068;
        }

        .dec-modal__person-hh {
            font-size: 11.5px;
            color: #9aa0b4;
            font-family: monospace;
        }

        /* Warning */
        .dec-modal__warning {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fff7ed;
            border: 1.5px solid #fed7aa;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 12.5px;
            color: #7c2d12;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .dec-modal__warning i {
            color: #ea580c;
            margin-top: 1px;
            flex-shrink: 0;
            font-size: 13px;
        }

        /* Field */
        .dec-modal__field {
            margin-bottom: 18px;
        }

        .dec-modal__label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 8px;
        }

        .dec-modal__label i {
            color: #9aa0b4;
            font-size: 11px;
        }

        .dec-modal__label-opt {
            font-size: 10.5px;
            font-weight: 500;
            color: #9aa0b4;
            text-transform: none;
            letter-spacing: 0;
            background: #f5f6fa;
            padding: 1px 7px;
            border-radius: 10px;
            border: 1px solid #e2e5ef;
        }

        .dec-modal__input {
            width: 100%;
            border: 1.5px solid #e2e5ef;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            box-sizing: border-box;
        }

        .dec-modal__input:focus {
            border-color: #16325c;
            box-shadow: 0 0 0 3px rgba(22, 50, 92, .12);
        }

        .dec-modal__input--short {
            max-width: 160px;
        }

        .dec-modal__hint {
            font-size: 11.5px;
            color: #9aa0b4;
            margin-top: 5px;
        }

        /* Divider */
        .dec-modal__divider {
            height: 1px;
            background: #f0f2f8;
            margin: 4px 0 18px;
        }

        /* Select */
        .dec-modal__select-wrap {
            position: relative;
        }

        .dec-modal__select {
            width: 100%;
            border: 1.5px solid #e2e5ef;
            border-radius: 0;
            padding: 10px 36px 10px 14px;
            font-size: 13px;
            font-family: inherit;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            appearance: none;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }

        .dec-modal__select:focus {
            border-color: #16325c;
            box-shadow: 0 0 0 3px rgba(22, 50, 92, .12);
        }

        .dec-modal__select-icon {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa0b4;
            font-size: 11px;
            pointer-events: none;
        }

        /* Footer */
        .dec-modal__footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 22px 20px;
            border-top: 1px solid #f0f2f8;
        }

        .dec-modal__btn {
            padding: 10px 22px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
            transition: all .18s;
            font-family: inherit;
        }

        .dec-modal__btn--cancel {
            background: #f5f6fa;
            color: #4a5068;
            border: 1.5px solid #e2e5ef;
        }

        .dec-modal__btn--cancel:hover {
            background: #e8ecf4;
            color: #1a1d2e;
        }

        .dec-modal__btn--confirm {
            background: #16325c;
            color: #fff;
            box-shadow: none;
        }

        .dec-modal__btn--confirm:hover {
            background: #122848;
            box-shadow: none;
            transform: none;
        }

        .dec-modal__btn--confirm:active {
            transform: none;
            box-shadow: none;
        }
    </style>

    <script>
        function markRequiredFields() {
            document.querySelectorAll('[required]').forEach(field => {
                let parent = field.parentElement;
                for (let level = 0; level < 4 && parent; level++, parent = parent.parentElement) {
                    const label = parent.querySelector('.pf-label, .pf-field-label');
                    if (!label) continue;
                    if (!label.querySelector('[data-required-marker]')) {
                        const marker = document.createElement('span');
                        marker.dataset.requiredMarker = 'true';
                        marker.textContent = ' *';
                        marker.style.cssText = 'color:#c0392b;font-weight:700;';
                        label.appendChild(marker);
                    }
                    break;
                }
            });
        }

        markRequiredFields();
        new MutationObserver(markRequiredFields).observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
</body>

</html>