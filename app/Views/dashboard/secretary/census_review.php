<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Census Record - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .cr-card { background:#fff; border:1px solid #e2e6ee; border-radius:14px; box-shadow:0 4px 20px rgba(29,36,72,.06); margin-bottom:18px; overflow:hidden; }
        .cr-head { padding:16px 22px; background:linear-gradient(135deg,#1d2448,#2e3a6e); color:#fff; font-size:14px; font-weight:700; display:flex; align-items:center; gap:10px; }
        .cr-body { padding:18px 22px; }
        .cr-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:12px 18px; }
        .cr-label { display:block; font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#6b7280; margin-bottom:3px; }
        .cr-value { font-size:14px; color:#1d2448; font-weight:600; }
        .cr-back { display:inline-flex; align-items:center; gap:7px; color:#4a5068; font-size:13px; text-decoration:none; margin-bottom:16px; }
        .cr-back:hover { color:#1d2448; }
        .cr-note { background:#fffbf0; border:1px solid #ffe08a; color:#7a4200; border-radius:10px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
        .cr-actions { display:flex; gap:12px; align-items:center; flex-wrap:wrap; padding:16px 22px; background:#f8f9fc; border-top:1px solid #e4e9f2; }
        .cr-check { display:flex; align-items:flex-start; gap:8px; font-size:13px; color:#1d2448; font-weight:600; flex:1; min-width:240px; }
        .cr-check input { margin-top:3px; }
        .cr-table { width:100%; border-collapse:collapse; font-size:13px; }
        .cr-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#4a5068; background:#f3f5fb; padding:10px 12px; }
        .cr-table td { padding:10px 12px; border-top:1px solid #f0f2f8; vertical-align:top; }
    </style>
</head>
<body class="db-body">
<?php
$role = session_role();
$active = 'census';
$pageTitle = 'Review Census Record';
include APPPATH . 'Views/dashboard/sidebar.php';

$h = $household ?? [];
$members = $members ?? [];
$recorderName = trim((string) ($recorderName ?? ''));
$status = (string) ($h['approval_status'] ?? 'pending');
$pending = $status === 'pending';
$show = static function ($value): string {
    $text = trim((string) ($value ?? ''));
    return $text === '' ? '—' : $text;
};
$yesNo = static function ($value): string {
    return (int) $value === 1 ? 'Yes' : 'No';
};
$fullName = trim($show($h['last_name']) . ', ' . $show($h['first_name']) . ' ' . trim((string) ($h['middle_name'] ?? '')));
$dob = ! empty($h['date_of_birth']) ? date('F j, Y', strtotime((string) $h['date_of_birth'])) : '—';
$recorded = ! empty($h['recorded_date']) ? date('F j, Y', strtotime((string) $h['recorded_date'])) : '—';
?>
<div class="db-main">
    <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
    <div class="db-content">
        <a href="/<?= esc($role) ?>/census" class="cr-back"><i class="fas fa-arrow-left"></i> Back to Census Records</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="db-alert db-alert--success" style="margin-bottom:16px;"><i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="db-alert db-alert--error" style="margin-bottom:16px;"><i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="cr-note">
            <i class="fas fa-user-check"></i>
            This census record cannot be approved until authorized barangay personnel review and verify the information below.
        </div>

        <div class="cr-card">
            <div class="cr-head"><i class="fas fa-home"></i> Household #<?= esc($show($h['household_no'])) ?></div>
            <div class="cr-body">
                <div class="cr-grid">
                    <div><span class="cr-label">Head of the family</span><span class="cr-value"><?= esc($fullName) ?></span></div>
                    <div><span class="cr-label">Status</span><span class="cr-value"><?= esc(ucfirst($status)) ?></span></div>
                    <div><span class="cr-label">Zone</span><span class="cr-value"><?= esc($show($h['zone'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Address</span><span class="cr-value"><?= esc($show($h['address'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Date of birth</span><span class="cr-value"><?= esc($dob) ?></span></div>
                    <div><span class="cr-label">Place of birth</span><span class="cr-value"><?= esc($show($h['place_of_birth'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Gender</span><span class="cr-value"><?= esc($show($h['gender'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Civil status</span><span class="cr-value"><?= esc($show($h['civil_status'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Nationality</span><span class="cr-value"><?= esc($show($h['nationality'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Religion</span><span class="cr-value"><?= esc($show($h['religion'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Occupation</span><span class="cr-value"><?= esc($show($h['occupation'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Monthly income</span><span class="cr-value">₱<?= esc(number_format((float) ($h['monthly_income'] ?? 0), 2)) ?></span></div>
                    <div><span class="cr-label">Contact number</span><span class="cr-value"><?= esc($show($h['contact_number'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Educational attainment</span><span class="cr-value"><?= esc($show($h['educational_attainment'] ?? '')) ?></span></div>
                    <div><span class="cr-label">PhilHealth no.</span><span class="cr-value"><?= esc($show($h['philhealth_no'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Years of residency</span><span class="cr-value"><?= esc($show($h['years_of_residency'] ?? '')) ?></span></div>
                    <div><span class="cr-label">House ownership</span><span class="cr-value"><?= esc($show($h['house_ownership'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Registered voter</span><span class="cr-value"><?= esc($yesNo($h['registered_voter'] ?? 0)) ?></span></div>
                    <div><span class="cr-label">4Ps</span><span class="cr-value"><?= esc($yesNo($h['is_4ps'] ?? 0)) ?></span></div>
                    <div><span class="cr-label">PWD</span><span class="cr-value"><?= esc(((int) ($h['is_pwd'] ?? 0) === 1) ? ('Yes' . (! empty($h['pwd_type']) ? ' — ' . $h['pwd_type'] : '')) : 'No') ?></span></div>
                    <div><span class="cr-label">Senior citizen</span><span class="cr-value"><?= esc($yesNo($h['is_senior_citizen'] ?? 0)) ?></span></div>
                    <div><span class="cr-label">Solo parent</span><span class="cr-value"><?= esc($yesNo($h['is_solo_parent'] ?? 0)) ?></span></div>
                    <div><span class="cr-label">Indigenous people</span><span class="cr-value"><?= esc($yesNo($h['is_indigenous'] ?? 0)) ?></span></div>
                    <div><span class="cr-label">Census year</span><span class="cr-value"><?= esc($show($h['census_year'] ?? '')) ?></span></div>
                    <div><span class="cr-label">Recorded</span><span class="cr-value"><?= esc($recorded) ?><?= $recorderName !== '' ? ' by ' . esc($recorderName) : '' ?></span></div>
                </div>
            </div>
        </div>

        <div class="cr-card">
            <div class="cr-head"><i class="fas fa-users"></i> Household members (<?= count($members) ?>)</div>
            <div class="cr-body" style="padding:0;">
                <?php if ($members === []): ?>
                    <p style="margin:0;padding:18px 22px;color:#6b7280;font-size:13px;">No other household members were recorded.</p>
                <?php else: ?>
                    <table class="cr-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>Date of birth</th>
                                <th>Gender</th>
                                <th>Civil status</th>
                                <th>Occupation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <?php
                                $memberName = trim(($member['last_name'] ?? '') . ', ' . ($member['first_name'] ?? '') . ' ' . ($member['middle_name'] ?? ''));
                                $memberDob = ! empty($member['date_of_birth']) ? date('M j, Y', strtotime((string) $member['date_of_birth'])) : '—';
                                ?>
                                <tr>
                                    <td><?= esc($memberName) ?></td>
                                    <td><?= esc(ucfirst((string) ($member['relationship'] ?? '—'))) ?></td>
                                    <td><?= esc($memberDob) ?></td>
                                    <td><?= esc($show($member['gender'] ?? '')) ?></td>
                                    <td><?= esc($show($member['marital_status'] ?? '')) ?></td>
                                    <td><?= esc($show($member['occupation'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php if ($pending): ?>
                <form method="post" action="/<?= esc($role) ?>/census/approve/<?= esc($h['household_no']) ?>" id="censusApproveForm">
                    <?= csrf_field() ?>
                    <div class="cr-actions">
                        <label class="cr-check">
                            <input type="checkbox" name="information_verified" value="1" id="verifyCensusInfo">
                            <span>I have reviewed and verified this census information.</span>
                        </label>
                        <button type="submit" class="db-btn db-btn--outline" formaction="/<?= esc($role) ?>/census/reject/<?= esc($h['household_no']) ?>" formnovalidate>
                            <i class="fas fa-times"></i> Reject
                        </button>
                        <button type="submit" class="db-btn db-btn--primary" id="approveCensusBtn" disabled>
                            <i class="fas fa-check"></i> Approve record
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="cr-actions">
                    <span style="font-size:13px;color:#4a5068;">This record is already <strong><?= esc($status) ?></strong>.</span>
                    <a href="/<?= esc($role) ?>/household/<?= esc($h['household_no']) ?>" class="db-btn db-btn--outline">Open household</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
(function () {
    var box = document.getElementById('verifyCensusInfo');
    var button = document.getElementById('approveCensusBtn');
    if (!box || !button) return;
    box.addEventListener('change', function () {
        button.disabled = !box.checked;
    });
})();
</script>
</body>
</html>
