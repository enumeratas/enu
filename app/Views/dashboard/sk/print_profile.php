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
if (! is_array($organizations)) {
    $organizations = [];
}
$health = ! empty($profile['health_concerns']) ? (array) json_decode($profile['health_concerns'], true) : [];
$social = ! empty($profile['social_inclusion']) ? (array) json_decode($profile['social_inclusion'], true) : [];
$fullName = trim(preg_replace('/\s+/', ' ', ($profile['last_name'] ?? '') . ', ' . ($profile['first_name'] ?? '') . ' ' . ($profile['middle_name'] ?? '') . ' ' . ($profile['suffix'] ?? '')));
$photoUrl = ! empty($profile['photo_path']) ? '/uploads/' . ltrim($profile['photo_path'], '/') : '';
if ($ageGroup === '' && $age !== null) {
    $ageGroup = (string) \App\Models\SkYouthModel::ageGroupFor($profile['date_of_birth'] ?? null);
}
$schoolDetail = trim((string) ($profile['school_detail'] ?? ''));
$educationText = strtolower((string) $education);
$hasEdu = static function (string ...$needles) use ($educationText): bool {
    foreach ($needles as $needle) {
        if ($needle === 'als') {
            if (preg_match('/\bals\b/', $educationText) === 1) {
                return true;
            }
            continue;
        }
        if (str_contains($educationText, $needle)) {
            return true;
        }
    }

    return false;
};
$isJunior = $hasEdu('junior');
$isSenior = $hasEdu('senior');
$isCollege = $hasEdu('college', 'bachelor');
$mark = static fn(bool $on): string => $on ? 'checked' : '';
$checked = static fn(array $values, string $value): string => in_array($value, $values, true) ? 'checked' : '';
$ageChecked = static fn(string $value): string => $ageGroup === $value ? 'checked' : '';
$civilKey = static function (string $value): string {
    return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
};
$officialCivil = ['Single', 'Married', 'With live in partner', 'Teenage Mom/Dad', 'Solo Parent'];
$civilMatch = $civilKey((string) $civilStatus);
$civilIsOfficial = false;
foreach ($officialCivil as $civilOption) {
    if ($civilMatch !== '' && $civilMatch === $civilKey($civilOption)) {
        $civilIsOfficial = true;
        break;
    }
}
$civilChecked = static function (string $value) use ($civilMatch, $civilKey): string {
    return $civilMatch !== '' && $civilMatch === $civilKey($value) ? 'checked' : '';
};
$civilOther = $civilIsOfficial ? '' : trim((string) $civilStatus);
$schoolChecked = static fn(string $value): string => strcasecmp((string) ($profile['school_type'] ?? ''), $value) === 0 ? 'checked' : '';
$governanceChecked = static fn(string $value): string => strcasecmp((string) ($profile['governance'] ?? ''), $value) === 0 ? 'checked' : '';
$economicRaw = strtolower(trim((string) ($profile['economic_status'] ?? '')));
$economicChecked = static function (string $value) use ($economicRaw): string {
    $aliases = [
        'Employed' => ['employed'],
        'Unemployed' => ['unemployed'],
        'Engage in Business' => ['engage in business', 'self-employed', 'self employed'],
        'Engage in Agriculture' => ['engage in agriculture'],
        'Working outside Municipality' => ['working outside municipality'],
    ];
    if ($value === 'Employed' && in_array($economicRaw, ['self-employed', 'self employed', 'engage in business'], true)) {
        return '';
    }

    return in_array($economicRaw, $aliases[$value] ?? [strtolower($value)], true) ? 'checked' : '';
};
$economicKnown = ['employed', 'unemployed', 'self-employed', 'self employed', 'engage in business', 'engage in agriculture', 'working outside municipality', 'student', 'out-of-school', '—', '-'];
$economicOther = ($economicRaw !== '' && ! in_array($economicRaw, $economicKnown, true)) ? (string) ($profile['economic_status'] ?? '') : '';
$moneyKey = static function (string $value): string {
    $value = strtolower($value);
    $value = str_replace(['₱', 'php', ',', ' ', '.00'], '', $value);
    $value = str_replace(['–', '—'], '-', $value);
    $value = preg_replace('/p(?=\d)/', '', $value) ?? $value;

    return $value;
};
$incomeKey = $moneyKey((string) $income);
$incomeChecked = static function (string $value) use ($incomeKey, $moneyKey): string {
    $aliases = [
        'Below P5,000.00' => ['below5000', 'below₱5000'],
        'P5,000.00 - P10,000.00' => ['5000-10000', '5000–10000'],
        'P10,000.00 - P20,000.00' => ['10000-20000'],
        'P20,000.00 - 35,000.00' => ['20000-35000', '20000-40000'],
        'P35,000.00 - 60,000.00' => ['35000-60000'],
        'P60,000.00 and above' => ['60000andabove', 'above40000', 'above60000'],
    ];
    $current = $incomeKey;
    if ($current === '') {
        return '';
    }
    if ($current === $moneyKey($value)) {
        return 'checked';
    }
    foreach ($aliases[$value] ?? [] as $alias) {
        if ($current === $moneyKey($alias)) {
            return 'checked';
        }
    }

    return '';
};
$healthOptions = ['Engage in Smoking', 'Engage in Alcohol', 'Experience Depression', 'Attempt Suicide', 'Health Problem', 'HIV/AIDS'];
$healthOther = [];
foreach ($health as $item) {
    if (! is_string($item) || in_array($item, $healthOptions, true)) {
        continue;
    }
    $healthOther[] = $item;
}
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
    <style id="sk-print-page">
        @media print {
            @page { size: 21.59cm 33.02cm; margin: 6mm; }
        }
    </style>
    <script>
        (function () {
            var saved = 'portrait';
            try {
                saved = sessionStorage.getItem('skKkPrintOrientation') || 'portrait';
            } catch (error) {}
            if (saved !== 'landscape') {
                saved = 'portrait';
            }
            document.documentElement.classList.add(saved === 'landscape' ? 'print-landscape' : 'print-portrait');
            if (saved === 'landscape') {
                document.getElementById('sk-print-page').textContent = '@media print { @page { size: 33.02cm 21.59cm; margin: 5mm; } }';
            }
            window.__skPrintOrientation = saved;
        })();
    </script>

</head>

<body class="db-body">
    <?php include(APPPATH . 'Views/dashboard/sidebar.php'); ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (session()->getFlashdata('success')): ?><div class="db-alert db-alert--success" style="margin:0 0 16px;"><i class="fas fa-check-circle"></i><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="db-alert db-alert--error" style="margin:0 0 16px;"><i class="fas fa-exclamation-circle"></i><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
            <div class="print-toolbar">
                <div class="print-orientation" role="group" aria-label="Page orientation">
                    <span>Orientation</span>
                    <button type="button" class="db-btn db-btn--primary" data-orientation="portrait" aria-pressed="true" onclick="setKkPrintOrientation('portrait')">Portrait</button>
                    <button type="button" class="db-btn db-btn--outline" data-orientation="landscape" aria-pressed="false" onclick="setKkPrintOrientation('landscape')">Landscape</button>
                </div>
                <a href="/sk/profiling" class="db-btn db-btn--outline"><i class="fas fa-arrow-left"></i> Back</a>
                <?php if (! empty($youthProfile['id'])): ?>
                    <a href="/sk/profiling/edit/<?= (int) $youthProfile['id'] ?>" class="db-btn db-btn--outline"><i class="fas fa-edit"></i> Edit Profile / Attach Photo</a>
                <?php else: ?>
                    <a href="/sk/profiling/add?household_no=<?= urlencode($householdId) ?>&source=<?= urlencode($memberSource ?? '') ?>&member_id=<?= (int) ($memberId ?? 0) ?>" class="db-btn db-btn--outline"><i class="fas fa-edit"></i> Edit Profile / Attach Photo</a>
                <?php endif; ?>
                <button type="button" class="db-btn db-btn--primary" onclick="window.print()"><i class="fas fa-print"></i> Print Form</button>
            </div>
            <main class="lydo-sheet">
                <div class="lydo-form-code">LYDO FORM 1</div>
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
                        <td class="lydo-label">Age:<div class="lydo-value"><?= esc((string) ($age ?? '')) ?></div>
                        </td>
                        <td class="lydo-label">Sex:<div class="lydo-value"><?= esc($profile['gender'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label">Date of Birth:<div class="lydo-value"><?= ! empty($profile['date_of_birth']) ? esc(date('m/d/Y', strtotime($profile['date_of_birth']))) : '' ?></div>
                        </td>
                        <td class="lydo-label">Place of Birth:<div class="lydo-value"><?= esc($profile['place_of_birth'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label">Religion:<div class="lydo-value"><?= esc($profile['religion'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label">Citizenship:<div class="lydo-value"><?= esc($profile['citizenship'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">Contact No.:<div class="lydo-value"><?= esc($profile['contact_number'] ?? '') ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">Email Address:<div class="lydo-value" style="text-transform:none;"><?= esc($profile['email'] ?? '') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="lydo-label" colspan="2">Complete Address:<div class="lydo-value"><?= esc($completeAddress) ?></div>
                        </td>
                        <td class="lydo-label" colspan="2">No. of months/years in the Brgy.:<div class="lydo-value"><?= esc($profile['months_in_brgy'] ?? '') ?></div>
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
                    <?php for ($i = 0; $i < 3; $i++): ?><tr>
                            <td><?= esc($organizations[$i]['name'] ?? '') ?></td>
                            <td><?= esc($organizations[$i]['position'] ?? '') ?></td>
                            <td colspan="2"><?= esc($organizations[$i]['year'] ?? '') ?></td>
                        </tr><?php endfor; ?>
                    <tr>
                        <td colspan="4" class="lydo-checks"><span class="lydo-inline-label">AGE DISAGGREGATION</span> <label><input type="checkbox" <?= $ageChecked('15-17') ?>> Child Youth (15-17 y/o)</label><label><input type="checkbox" <?= $ageChecked('18-21') ?>> 18-21 y/o</label><label><input type="checkbox" <?= $ageChecked('22-24') ?>> 22-24 y/o</label><label><input type="checkbox" <?= $ageChecked('25-30') ?>> Adult Youth (25-30 y/o)</label></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><span class="lydo-inline-label">CIVIL STATUS</span> <?php foreach ($officialCivil as $value): ?><label><input type="checkbox" <?= $civilChecked($value) ?>> <?= esc($value) ?></label><?php endforeach; ?> <span class="lydo-inline-label">Others:</span> <span class="lydo-blank"><?= esc($civilOther) ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">EDUCATIONAL BACKGROUND</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks">
                            <label><input type="checkbox" <?= $mark($hasEdu('technical', 'vocational')) ?>> Technical/Vocational</label>
                            <label><input type="checkbox" <?= $mark($isJunior) ?>> Junior HS, Grade Level: <span class="lydo-blank"><?= $isJunior ? esc($schoolDetail) : '' ?></span></label>
                            <label><input type="checkbox" <?= $mark($isSenior) ?>> Senior HS, Grade Level: <span class="lydo-blank"><?= $isSenior ? esc($schoolDetail) : '' ?></span></label>
                            <label><input type="checkbox" <?= $mark($isCollege) ?>> College, Year Level: <span class="lydo-blank"><?= $isCollege ? esc($schoolDetail) : '' ?></span></label>
                            <span class="lydo-inline-label">Specialization:</span> <span class="lydo-blank"><?= (! $isJunior && ! $isSenior && ! $isCollege) ? esc($schoolDetail) : '' ?></span>
                            <span class="lydo-inline-label">Course:</span> <span class="lydo-blank"></span>
                            <label><input type="checkbox" <?= $schoolChecked('Private School') ?>> Private School</label>
                            <label><input type="checkbox" <?= $schoolChecked('Public School') ?>> Public School</label>
                            <label><input type="checkbox" <?= $mark($hasEdu('alternative', 'als')) ?>> Alternative Learning System</label>
                            <label><input type="checkbox" <?= $mark($hasEdu('out-of-school', 'out of school')) ?>> Out-of-School Youth</label>
                            <label><input type="checkbox" <?= $mark($hasEdu('young professional', 'professional')) ?>> Young Professional</label>
                        </td>
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
                        <td colspan="4" class="lydo-checks"><?php foreach ($healthOptions as $value): ?><label><input type="checkbox" <?= $checked($health, $value) ?>> <?= esc($value) ?></label><?php endforeach; ?> <span class="lydo-inline-label">Others:</span> <span class="lydo-blank"><?= esc(implode(', ', $healthOther)) ?></span></td>
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
                        <td colspan="4" class="lydo-checks">
                            <label><input type="checkbox" <?= $economicChecked('Employed') ?>> Employed</label>
                            <label><input type="checkbox" <?= $economicChecked('Unemployed') ?>> Unemployed</label>
                            <label><input type="checkbox" <?= $economicChecked('Engage in Business') ?>> Engage in Business</label>
                            <label><input type="checkbox" <?= $economicChecked('Engage in Agriculture') ?>> Engage in Agriculture</label>
                            <label><input type="checkbox" <?= $economicChecked('Working outside Municipality') ?>> Working outside Municipality</label>
                            <span class="lydo-inline-label">Others:</span> <span class="lydo-blank"><?= esc($economicOther) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><span class="lydo-inline-label">MONTHLY INCOME</span>
                            <label><input type="checkbox" <?= $incomeChecked('Below P5,000.00') ?>> Below P5,000.00</label>
                            <label><input type="checkbox" <?= $incomeChecked('P5,000.00 - P10,000.00') ?>> P5,000.00 - P10,000.00</label>
                            <label><input type="checkbox" <?= $incomeChecked('P10,000.00 - P20,000.00') ?>> P10,000.00 - P20,000.00</label>
                            <label><input type="checkbox" <?= $incomeChecked('P20,000.00 - 35,000.00') ?>> P20,000.00 - 35,000.00</label>
                            <label><input type="checkbox" <?= $incomeChecked('P35,000.00 - 60,000.00') ?>> P35,000.00 - 60,000.00</label>
                            <label><input type="checkbox" <?= $incomeChecked('P60,000.00 and above') ?>> P60,000.00 and above</label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">CENTER OF YOUTH PARTICIPATION YOU WOULD LIKE TO ADVOCATE</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><?php foreach ($advocacyOptions as $value): ?><label><input type="checkbox" <?= stripos((string) ($profile['advocacy'] ?? ''), $value) !== false ? 'checked' : '' ?>> <?= esc($value) ?></label><?php endforeach; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">DO YOU WANT TO BE A VOLUNTEER?</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-checks"><?php foreach ($volunteerOptions as $value): ?><label><input type="checkbox" <?= stripos((string) ($profile['volunteer'] ?? ''), $value) !== false ? 'checked' : '' ?>> <?= esc($value) ?></label><?php endforeach; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-section">WHAT ARE TOP 3 ISSUES OR CONCERNS FACING THE YOUTH TODAY?</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-lines">1. <?= esc($profile['issue_1'] ?? '') ?><br>2. <?= esc($profile['issue_2'] ?? '') ?><br>3. <?= esc($profile['issue_3'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-lines">
                            <div class="lydo-suggest">
                                <div class="lydo-suggest-text"><span class="lydo-label">WHAT PROGRAM/S, PROJECT/S, ACTIVITY/IES WOULD YOU SUGGEST TO THE SK OFFICIALS?</span><br><?= nl2br(esc($profile['suggestions'] ?? '')) ?></div>
                                <div class="lydo-photo"><?php if ($photoUrl): ?><img src="<?= esc($photoUrl) ?>" alt="Youth profile photo"><?php else: ?>Photo<?php endif; ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="lydo-certification">I hereby certify that all information stated above are all true and correct to the best of my knowledge and belief.</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="lydo-signature">
                            <div class="lydo-sign-space"></div>
                            <div class="lydo-sign-caption">Name &amp; Signature Over Printed Name</div>
                            <div class="lydo-sign-space"></div>
                            <div class="lydo-sign-caption">Date</div>
                        </td>
                        <td colspan="2" class="lydo-signature">
                            <div class="lydo-attested">Attested: <span id="skChairpersonStatus" class="lydo-chair-status"></span></div>
                            <div class="lydo-sign-space">
                                <input id="skChairpersonName" class="lydo-chair-name" type="text" maxlength="120" autocomplete="name" spellcheck="false" aria-label="Sangguniang Kabataan Chairperson" value="<?= esc($chairpersonName ?? \App\Models\BarangaySettingsModel::SK_CHAIRPERSON_DEFAULT) ?>">
                            </div>
                            <div class="lydo-sign-caption">Name &amp; Signature Over Printed Name of<br>Sangguniang Kabataan Chairperson</div>
                        </td>
                    </tr>
                </table>
            </main>
        </div>
    </div>
    <script>
        function setKkPrintOrientation(orientation) {
            var next = orientation === 'landscape' ? 'landscape' : 'portrait';
            document.documentElement.classList.toggle('print-landscape', next === 'landscape');
            document.documentElement.classList.toggle('print-portrait', next === 'portrait');
            var size = next === 'landscape' ? '33.02cm 21.59cm' : '21.59cm 33.02cm';
            var margin = next === 'landscape' ? '5mm' : '6mm';
            document.getElementById('sk-print-page').textContent = '@media print { @page { size: ' + size + '; margin: ' + margin + '; } }';
            document.querySelectorAll('[data-orientation]').forEach(function (button) {
                var active = button.getAttribute('data-orientation') === next;
                button.classList.toggle('db-btn--primary', active);
                button.classList.toggle('db-btn--outline', !active);
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            try {
                sessionStorage.setItem('skKkPrintOrientation', next);
            } catch (error) {}
        }

        setKkPrintOrientation(window.__skPrintOrientation || 'portrait');

        (function () {
            var input = document.getElementById('skChairpersonName');
            var status = document.getElementById('skChairpersonStatus');
            if (!input) {
                return;
            }
            var saved = input.value.replace(/\s+/g, ' ').trim();
            var timer = null;

            function showStatus(text) {
                if (status) {
                    status.textContent = text;
                }
            }

            function saveChairperson() {
                var value = input.value.replace(/\s+/g, ' ').trim();
                input.value = value;
                if (value === saved) {
                    return;
                }
                var body = new URLSearchParams();
                body.set('sk_chairperson_name', value);
                body.set(<?= json_encode(csrf_token()) ?>, <?= json_encode(csrf_hash()) ?>);
                fetch(<?= json_encode($chairpersonSaveUrl ?? '/sk/profiling/chairperson') ?>, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString()
                }).then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                }).then(function (result) {
                    if (result.ok && result.data && result.data.success) {
                        saved = typeof result.data.name === 'string' ? result.data.name : value;
                        input.value = saved;
                        showStatus('Saved');
                        return;
                    }
                    showStatus('Not saved');
                }).catch(function () {
                    showStatus('Not saved');
                });
            }

            input.addEventListener('input', function () {
                showStatus('');
                clearTimeout(timer);
                timer = setTimeout(saveChairperson, 500);
            });
            input.addEventListener('change', saveChairperson);
            input.addEventListener('blur', saveChairperson);
            window.addEventListener('beforeprint', saveChairperson);
        })();
    </script>
</body>

</html>