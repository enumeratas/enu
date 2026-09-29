<?php
$role = 'sk';
$active = 'profiling';
$pageTitle = 'KK Profiling Form';

$profile = array_merge($youthMember ?? [], $youthProfile ?? []);
$household = $household ?? [];
$age = ! empty($profile['date_of_birth']) ? (int) date_diff(date_create($profile['date_of_birth']), date_create('today'))->y : null;
$ageGroup = $profile['age_group'] ?? '';
$civilStatus = $profile['civil_status'] ?? ($profile['marital_status'] ?? '');
$education = $profile['educational_background'] ?? ($profile['educational_attainment'] ?? '');
$address = $profile['address'] ?? ($household['address'] ?? '');
$zone = $profile['zone'] ?? ($household['zone'] ?? '');
$completeAddress = trim($address . ($zone !== '' && stripos($address, $zone) === false ? ', ' . $zone : '') . (stripos($address, 'Bacolod, Bato, Camarines Sur') === false ? ', Bacolod, Bato, Camarines Sur' : ''), ' ,');
$income = $profile['monthly_income'] ?? '';
$organizations = ! empty($profile['organizations']) ? json_decode($profile['organizations'], true) : [];
$health = ! empty($profile['health_concerns']) ? (array) json_decode($profile['health_concerns'], true) : [];
$social = ! empty($profile['social_inclusion']) ? (array) json_decode($profile['social_inclusion'], true) : [];
$fullName = trim(preg_replace('/\s+/', ' ', ($profile['last_name'] ?? '') . ', ' . ($profile['first_name'] ?? '') . ' ' . ($profile['middle_name'] ?? '') . ' ' . ($profile['suffix'] ?? '')));
$photoUrl = ! empty($profile['photo_path']) ? '/uploads/' . ltrim($profile['photo_path'], '/') : '';
$checked = static fn(array $values, string $value): string => in_array($value, $values, true) ? 'checked' : '';
$ageChecked = static fn(string $value): string => $ageGroup === $value ? 'checked' : '';
$civilChecked = static fn(string $value): string => $civilStatus === $value ? 'checked' : '';
$educationChecked = static fn(string $value): string => stripos((string) $education, $value) !== false ? 'checked' : '';
$schoolChecked = static fn(string $value): string => ($profile['school_type'] ?? '') === $value ? 'checked' : '';
$governanceChecked = static fn(string $value): string => ($profile['governance'] ?? '') === $value ? 'checked' : '';
$economicChecked = static fn(string $value): string => ($profile['economic_status'] ?? '') === $value ? 'checked' : '';
$incomeChecked = static fn(string $value): string => ($income ?? '') === $value ? 'checked' : '';
$healthOptions = ['Engage in Smoking', 'Engage in Alcohol', 'Experience Depression', 'Attempt Suicide', 'Health Problem', 'HIV/AIDS'];
$socialOptions = ['4Ps Beneficiary', 'UCT Beneficiary', 'IP', 'PWD', 'Solo Parent', 'LGBTQ+'];
$advocacyOptions = ['Health', 'Education', 'Economic Empowerment', 'Social Inclusion & Equity', 'Governance', 'Active Citizenship', 'Peace Building & Security', 'Environment', 'Global Mobility'];
$volunteerOptions = ['Health', 'Education', 'Environment', 'Disaster Preparedness', 'Agriculture', 'LGU Activities', 'Barangay Activity', 'SK Activities', 'Parish Activities'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KK Profiling Form - Bacolod BIS</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body class="db-body">
    <?php include(APPPATH . 'Views/dashboard/sidebar.php'); ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (session()->getFlashdata('success')): ?><div class="db-alert db-alert--success" style="margin:0 0 16px;"><i class="fas fa-check-circle"></i><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="db-alert db-alert--error" style="margin:0 0 16px;"><i class="fas fa-exclamation-circle"></i><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
            <div class="print-toolbar">
                <a href="/sk/profiling" class="db-btn db-btn--outline"><i class="fas fa-arrow-left"></i> Back</a>
                <?php if (! empty($youthProfile['id'])): ?>
                    <a href="/sk/profiling/edit/<?= (int) $youthProfile['id'] ?>" class="db-btn db-btn--outline"><i class="fas fa-edit"></i> Edit Profile / Attach Photo</a>
                <?php else: ?>
                    <a href="/sk/profiling/add?household_no=<?= urlencode($householdId) ?>&source=<?= urlencode($memberSource ?? '') ?>&member_id=<?= (int) ($memberId ?? 0) ?>" class="db-btn db-btn--outline"><i class="fas fa-edit"></i> Edit Profile / Attach Photo</a>
                <?php endif; ?>
                <button type="button" class="db-btn db-btn--primary" onclick="window.print()"><i class="fas fa-print"></i> Print Form</button>
            </div>
            <main class="lydo-sheet">
                <header class="lydo-header">
                    <img class="lydo-seal" src="/Picture1.png" alt="Barangay Bato seal" width="70" height="64">
                    <img class="lydo-banner" src="/skheader.png" alt="Pambayang Ng Pederasyon ng mga SK" width="267" height="64">
                    <img class="lydo-office" src="/logo2.png" alt="Local Youth Development Office" width="76" height="64">
                </header>
                <div class="lydo-title">KATIPUNAN NG KABATAAN PROFILING FORM</div>
                <table class="lydo-table">
                    <tr>
                        <td colspan="4" class="lydo-section">PERSONAL INFORMATION</td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">Complete Name (Last, First, M.I.):<div class="lydo-value"><?= esc($fullName) ?></div>
                        </td>
                        <td class="lydo-label">Age:<div class="lydo-value"><?= esc((string)($age ?? '')) ?></div>
                        </td>
                        <td class="lydo-label">Sex:<div class="lydo-value"><?= esc($profile['gender'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label">Date of Birth:<div class="lydo-value"><?= !empty($profile['date_of_birth']) ? esc(date('m/d/Y', strtotime($profile['date_of_birth']))) : '' ?></div>
                        </td>
                        <td class="lydo-label">Place of Birth:<div class="lydo-value"><?= esc($profile['place_of_birth'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label">Religion:<div class="lydo-value"><?= esc($profile['religion'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label">Citizenship:<div class="lydo-value"><?= esc($profile['citizenship'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label">Contact No.:<div class="lydo-value"><?= esc($profile['contact_number'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label">Email Address:<div class="lydo-value" style="text-transform:none;"><?= esc($profile['email'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">Complete Address:<div class="lydo-value"><?= esc($completeAddress) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">No. of months/years in the Brgy.:<div class="lydo-value"><?= esc($profile['months_in_brgy'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">Zone/Purok:<div class="lydo-value"><?= esc($zone) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">Talent/Skills:<div class="lydo-value"><?= esc($profile['skills'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">Interests/Hobbies:<div class="lydo-value"><?= esc($profile['hobbies'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">Mother's Maiden Name:<div class="lydo-value"><?= esc($profile['mother_name'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">Occupation:<div class="lydo-value"><?= esc($profile['mother_occupation'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">Father's Name:<div class="lydo-value"><?= esc($profile['father_name'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">Occupation:<div class="lydo-value"><?= esc($profile['father_occupation'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">MEMBERSHIP IN ORGANIZATION</td>
                    </tr>
                    <tr>
                        <th>Name of Organization</th>
                        <th>Position</th>
                        <th colspan="2">Year</th>
                    </tr>
                    <?php for ($i = 0; $i < 4; $i++): ?><tr>
                            <td><?= esc($organizations[$i]['name'] ?? '') ?></td>
                            <td><?= esc($organizations[$i]['position'] ?? '') ?></td>
                            <td colspan="2"><?= esc($organizations[$i]['year'] ?? '') ?></td>
                        </tr><?php endfor; ?>
                    <tr>
                        <td colspan="4" class="lydo-section">AGE DISAGGREGATION</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><label><input type="checkbox" <?= $ageChecked('15-17') ?>> Child Youth (15-17 y/o)</label><label><input type="checkbox" <?= $ageChecked('18-21') ?>> 18-21 y/o</label><label><input type="checkbox" <?= $ageChecked('22-24') ?>> 22-24 y/o</label><label><input type="checkbox" <?= $ageChecked('25-30') ?>> Adult Youth (25-30 y/o)</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><span class="lydo-label">CIVIL STATUS</span> <?php foreach (['Single', 'Married', 'With live-in partner', 'Teenage Mom/Dad', 'Solo Parent'] as $value): ?><label><input type="checkbox" <?= $civilChecked($value) ?>> <?= esc($value) ?></label><?php endforeach; ?> <label>Others: __________</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">EDUCATIONAL BACKGROUND</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><label><input type="checkbox" <?= $educationChecked('Technical/Vocational') ?>> Technical/Vocational</label><label><input type="checkbox" <?= $educationChecked('Junior') ?>> Junior HS, Grade Level: ______</label><label><input type="checkbox" <?= $educationChecked('Senior') ?>> Senior HS, Grade Level: ______</label><label><input type="checkbox" <?= $educationChecked('College') ?>> College, Year Level: ______</label><label><input type="checkbox" <?= $educationChecked('Out-of-School') ?>> Out-of-School Youth</label><label><input type="checkbox" <?= $educationChecked('Young Professional') ?>> Young Professional</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks">Specialization: ____________________ &nbsp;&nbsp; Course: ____________________ &nbsp;&nbsp; <label><input type="checkbox" <?= $schoolChecked('Private School') ?>> Private School</label> <label><input type="checkbox" <?= $schoolChecked('Public School') ?>> Public School</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">GOVERNANCE</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><label><input type="checkbox" <?= $governanceChecked('COMELEC Registered') ?>> COMELEC Registered</label><label><input type="checkbox" <?= $governanceChecked('COMELEC Non-Registered') ?>> COMELEC Non-Registered</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">PHYSICAL &amp; MENTAL HEALTH</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><?php foreach ($healthOptions as $value): ?><label><input type="checkbox" <?= $checked($health, $value) ?>> <?= esc($value) ?></label><?php endforeach; ?><label>Others: __________</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">SOCIAL INCLUSION &amp; EQUITY</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><?php foreach ($socialOptions as $value): ?><label><input type="checkbox" <?= $checked($social, $value) ?>> <?= esc($value) ?></label><?php endforeach; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">ECONOMIC EMPOWERMENT</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><label><input type="checkbox" <?= $economicChecked('Student') ?>> Student</label><label><input type="checkbox" <?= $economicChecked('Employed') ?>> Employed</label><label><input type="checkbox" <?= $economicChecked('Unemployed') ?>> Unemployed</label><label><input type="checkbox" <?= $economicChecked('Out-of-School') ?>> Out-of-School</label><label><input type="checkbox" <?= $economicChecked('Self-Employed') ?>> Self-Employed</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><span class="lydo-label">MONTHLY INCOME</span> <?php foreach (['Below ₱5,000', '₱5,000 – ₱10,000', '₱10,000 – ₱20,000', '₱20,000 – ₱40,000', 'Above ₱40,000'] as $value): ?><label><input type="checkbox" <?= $incomeChecked($value) ?>> <?= esc($value) ?></label><?php endforeach; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">CENTER OF YOUTH PARTICIPATION / ADVOCACY</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><?php foreach ($advocacyOptions as $value): ?><label><input type="checkbox" <?= stripos((string)($profile['advocacy'] ?? ''), $value) !== false ? 'checked' : '' ?>> <?= esc($value) ?></label><?php endforeach; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">DO YOU WANT TO BE A VOLUNTEER?</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><?php foreach ($volunteerOptions as $value): ?><label><input type="checkbox" <?= stripos((string)($profile['volunteer'] ?? ''), $value) !== false ? 'checked' : '' ?>> <?= esc($value) ?></label><?php endforeach; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">WHAT ARE THE TOP 3 ISSUES OR CONCERNS FACING THE YOUTH TODAY?</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-lines">1. <?= esc($profile['issue_1'] ?? '') ?><br>2. <?= esc($profile['issue_2'] ?? '') ?><br>3. <?= esc($profile['issue_3'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="lydo-lines"><span class="lydo-label">WHAT PROGRAM/S, PROJECT/S, ACTIVITY/IES WOULD YOU SUGGEST TO THE SK OFFICIALS?</span><br><?= nl2br(esc($profile['suggestions'] ?? '')) ?></td>
                        <td class="lydo-photo"><?php if ($photoUrl): ?><img src="<?= esc($photoUrl) ?>" alt="Youth profile photo" style="display:block;max-width:120px;max-height:100px;width:auto;height:auto;object-fit:contain;margin:0 auto;"><?php else: ?>Photo<?php endif; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-certification">I hereby certify that all information stated above are all true and correct to the best of my knowledge and belief.</td>
                    </tr>
                    <tr>
                        <td colspan="1" class="lydo-signature"><br>Date</td>
                        <td colspan="3" class="lydo-signature"><span>KRISTINE MAE C. COLICO</span>Name &amp; Signature Over Printed Name of<br>Sangguniang Kabataan Chairperson</td>
                    </tr>
                </table>
            </main>
        </div>
    </div>
</body>

</html>