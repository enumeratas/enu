<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($editMode ?? false) ? 'Edit' : 'Add' ?> Youth Profile - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .db-content {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 18px 18px 0;
        }

        .kk-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(29, 36, 72, .05);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .kk-card-header {
            background: #1d2448;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kk-card-header h4 {
            color: #fff;
            font-size: 12.5px;
            font-weight: 600;
            margin: 0;
            letter-spacing: .2px;
        }

        .kk-card-header i {
            color: rgba(255, 255, 255, .7);
            font-size: 12px;
        }

        .kk-card-body {
            padding: 16px 18px;
        }

        .kk-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .kk-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .kk-grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .kk-full {
            margin-bottom: 12px;
        }

        .kk-label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: #4a5068;
            margin-bottom: 5px;
        }

        .kk-input {
            width: 100%;
            padding: 8px 10px;
            border: 1.5px solid #e2e5ef;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            box-sizing: border-box;
        }

        .kk-input:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, .08);
        }

        .kk-input[readonly] {
            background: #f5f7fb;
            color: #9aa0b4;
        }

        .kk-check-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .kk-check {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #1a1d2e;
            cursor: pointer;
        }

        .kk-check input {
            width: 15px;
            height: 15px;
            accent-color: #1d2448;
            cursor: pointer;
        }

        .kk-org-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kk-org-table th {
            background: #f0f2f8;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 8px 12px;
            border: 1px solid #e2e5ef;
        }

        .kk-org-table td {
            padding: 6px 8px;
            border: 1px solid #e2e5ef;
        }

        .kk-org-table td input {
            width: 100%;
            border: none;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            color: #1a1d2e;
            background: transparent;
            padding: 4px;
        }

        .kk-section-note {
            font-size: 11.5px;
            color: #9aa0b4;
            margin-bottom: 10px;
        }

        .kk-photo-preview {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 12px;
        }

        .kk-photo-box {
            width: 2in;
            height: 2in;
            flex: 0 0 2in;
            border: 1px solid #1d2448;
            background: #f7f8fc;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9aa0b4;
            font-size: 12px;
            text-align: center;
            box-sizing: border-box;
        }

        .kk-photo-box img {
            width: 2in;
            height: 2in;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .kk-profile-choice-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .kk-profile-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 2px solid #dfe5f3;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
        }

        .kk-profile-option.selected,
        .kk-profile-option:has(input:checked) {
            border-color: #1d2448;
            box-shadow: 0 4px 14px rgba(29, 36, 72, .08);
            background: #f7f8fc;
        }

        .kk-profile-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .kk-profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #1d2448;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .kk-profile-avatar.minor {
            background: #d9f6f0;
            color: #1d8f77;
        }

        .kk-profile-meta {
            min-width: 0;
        }

        .kk-profile-name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1d2e;
            margin-bottom: 2px;
        }

        .kk-profile-role {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: #4a5068;
        }

        .kk-minor-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(22, 160, 133, .12);
            color: #16a085;
            font-weight: 700;
            font-size: 10px;
        }

        @media(max-width:700px) {
            .kk-profile-choice-grid {
                grid-template-columns: 1fr;
            }

            .kk-grid-2,
            .kk-grid-3,
            .kk-grid-4 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $viewRole  = session()->get('role');
    $role      = in_array($viewRole, ['resident', 'council'], true) ? $viewRole : 'sk';
    $active    = $role === 'sk' ? 'profiling' : 'sk_profiling';
    $pageTitle = ($editMode ?? false) ? 'Edit Youth Profile' : 'Add Youth Profile';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $y       = $youth ?? [];
    $edit    = $editMode ?? false;
    $residentMode = $residentMode ?? false;
    $profileOptions = $profileOptions ?? [];
    $youthAccounts = $youthAccounts ?? [];
    $selectedAccountId = $selectedAccountId ?? null;
    $successMessage = $successMessage ?? null;
    $selectedProfileOption = $selectedProfileOption ?? null;
    if ($selectedProfileOption === null && $profileOptions !== []) {
        $selectedProfileOption = $profileOptions[0]['id'];
    }
    $residentEdit = $residentMode && ! empty($y['id']);
    $returnUrl = $residentMode
        ? ($role === 'council' ? '/council/sk-profiling' : '/resident/sk-profiling')
        : '/sk/profiling';
    $action  = $residentMode
        ? ($role === 'council' ? '/council/sk-profiling/store' : '/resident/sk-profiling/store')
        : ($edit ? '/sk/profiling/update/' . ($y['id'] ?? '') : '/sk/profiling/store');

    if (! empty($y['date_of_birth'])) {
        $y['date_of_birth'] = substr((string) $y['date_of_birth'], 0, 10);
    }
    $normalizedGender = \App\Models\SkYouthModel::normalizeChoice($y['gender'] ?? '', \App\Models\SkYouthModel::GENDERS);
    if ($normalizedGender !== null) {
        $y['gender'] = $normalizedGender;
    }
    $normalizedEducation = \App\Models\SkYouthModel::educationChoice($y['educational_background'] ?? null);
    if ($normalizedEducation !== null) {
        $y['educational_background'] = $normalizedEducation;
    }
    $optionsWithCurrent = static function (array $options, string $current): array {
        $current = trim($current);
        if ($current === '') {
            return $options;
        }
        foreach ($options as $option) {
            if (strcasecmp((string) $option, $current) === 0) {
                return $options;
            }
        }
        $options[] = $current;

        return $options;
    };
    $decodeList = static function ($value): array {
        if (is_array($value)) {
            return $value;
        }
        if (! is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    };
    $orgs = $decodeList($y['organizations'] ?? null);
    while (count($orgs) < 4) {
        $orgs[] = [];
    }
    $orgs = array_slice($orgs, 0, 4);
    $health = $decodeList($y['health_concerns'] ?? null);
    $social = $decodeList($y['social_inclusion'] ?? null);
    $advocacyStored = (string) ($y['advocacy'] ?? '');
    $volunteerStored = (string) ($y['volunteer'] ?? '');
    $choiceOn = static fn(string $stored, string $choice): bool => $stored !== '' && stripos($stored, $choice) !== false;
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php
            $flashSuccess = session()->getFlashdata('success');
            $flashError = session()->getFlashdata('error');
            $postedSuccess = (isset($successMessage) && is_string($successMessage)) ? $successMessage : '';
            $confirmMessage = $postedSuccess !== '' ? $postedSuccess : (is_string($flashSuccess) ? $flashSuccess : '');
            ?>
            <?php if ($confirmMessage !== ''): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:18px;">
                    <i class="fas fa-check-circle"></i> <?= esc($confirmMessage) ?>
                </div>
            <?php endif; ?>
            <?php if (is_string($flashError) && $flashError !== ''): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:18px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc($flashError) ?>
                </div>
            <?php endif; ?>

            <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;">
                <div>
                    <h2 style="font-size:16px;font-weight:700;color:#1d2448;margin:0;">
                        <?= ($edit || $residentEdit) ? 'Edit Youth Profile' : ($residentMode ? 'SK Youth Profiling' : 'New Youth Profile') ?>
                    </h2>
                    <p style="font-size:12px;color:#9aa0b4;margin:0;">
                        KK Profiling Form — LYDO Form 1 · Same fields for every youth age 15–30
                    </p>
                </div>
            </div>

            <?php if ($residentMode): ?>
                <div class="db-alert db-alert--info" style="margin-bottom:18px;">
                    <i class="fas fa-info-circle"></i>
                    Youth ages 15 to 30 use this same KK Profiling Form. A parent may also complete it for a minor child. Saving updates the youth’s SK account.
                </div>
                <div class="kk-card" style="margin-bottom:18px;">
                    <div class="kk-card-header"><i class="fas fa-user-check"></i>
                        <h4>Who is this profile for?</h4>
                    </div>
                    <div class="kk-card-body">
                        <div class="kk-profile-choice-grid">
                            <?php foreach ($profileOptions as $idx => $option): ?>
                                <?php $isSelected = $selectedProfileOption !== null ? $selectedProfileOption === $option['id'] : $idx === 0; ?>
                                <label class="kk-profile-option <?= $isSelected ? 'selected' : '' ?>" data-option-id="<?= esc($option['id']) ?>">
                                    <input type="radio" name="profile_option" value="<?= esc($option['id']) ?>" <?= $isSelected ? 'checked' : '' ?>>
                                    <div class="kk-profile-avatar <?= $option['is_minor'] ? 'minor' : '' ?>"><?= strtoupper(substr(trim((string) $option['full_name']), 0, 1)) ?: '?' ?></div>
                                    <div class="kk-profile-meta">
                                        <div class="kk-profile-name"><?= esc($option['full_name']) ?></div>
                                        <div class="kk-profile-role"><?= $option['is_minor'] ? 'Minor Child' : 'Resident' ?> <?= $option['is_minor'] ? '<span class="kk-minor-badge">Minor</span>' : '' ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php elseif (! $edit): ?>
                <div class="kk-card" style="margin-bottom:18px;">
                    <div class="kk-card-header"><i class="fas fa-link"></i>
                        <h4>Link this profile to a youth account</h4>
                    </div>
                    <div class="kk-card-body">
                        <input type="text" class="kk-input" id="youthAccountSearch" list="youthAccountOptions" placeholder="Type a name or email to search..." autocomplete="off">
                        <datalist id="youthAccountOptions">
                            <?php foreach ($youthAccounts as $account): ?>
                                <option value="<?= esc($account['label']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <p style="font-size:12px;color:#9aa0b4;margin:8px 0 0;">Selecting an account fills the standard form. Every field stays editable, and saving updates that youth’s SK account.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="kkForm">
                <?= csrf_field() ?>
                <?php if (! $residentMode): ?>
                    <input type="hidden" name="user_id" id="youthAccountId" value="<?= $selectedAccountId ? (int) $selectedAccountId : (int) ($y['user_id'] ?? 0) ?>">
                <?php else: ?>
                    <input type="hidden" name="profile_option" id="formProfileOption" value="<?= esc($selectedProfileOption ?? 'self') ?>">
                <?php endif; ?>

                <!-- ── Section 1: Personal Information ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-user"></i>
                        <h4>Section 1 — Personal Information</h4>
                    </div>
                    <div class="kk-card-body">
                        <div class="kk-grid-4">
                            <div><label class="kk-label">Last Name *</label><input type="text" class="kk-input" name="last_name" value="<?= esc($y['last_name'] ?? '') ?>" placeholder="DELA CRUZ" required></div>
                            <div><label class="kk-label">First Name *</label><input type="text" class="kk-input" name="first_name" value="<?= esc($y['first_name'] ?? '') ?>" placeholder="JUAN" required></div>
                            <div><label class="kk-label">Middle Name</label><input type="text" class="kk-input" name="middle_name" value="<?= esc($y['middle_name'] ?? '') ?>" placeholder="SANTOS"></div>
                            <div><label class="kk-label">Suffix</label>
                                <select class="kk-input" name="suffix">
                                    <option value="">— None —</option>
                                    <?php foreach (['Jr', 'Sr', 'II', 'III', 'IV'] as $s): ?>
                                        <option <?= ($y['suffix'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="kk-grid-3">
                            <div><label class="kk-label">Date of Birth *</label><input type="date" class="kk-input" name="date_of_birth" id="dob" value="<?= esc($y['date_of_birth'] ?? '') ?>" oninput="calcAge()" required></div>
                            <div><label class="kk-label">Age (auto)</label><input type="number" class="kk-input" id="ageOut" readonly value="<?= isset($y['age']) ? $y['age'] : '' ?>" placeholder="—"></div>
                            <div><label class="kk-label">Sex *</label>
                                <select class="kk-input" name="gender" required>
                                    <option value="">— Select —</option>
                                    <option value="Male" <?= ($y['gender'] ?? '') === 'Male'   ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($y['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="kk-grid-3">
                            <div><label class="kk-label">Place of Birth</label><input type="text" class="kk-input" name="place_of_birth" value="<?= esc($y['place_of_birth'] ?? '') ?>" placeholder="Bato, Camarines Sur"></div>
                            <div><label class="kk-label">Religion</label><input type="text" class="kk-input" name="religion" value="<?= esc($y['religion'] ?? '') ?>" placeholder="Roman Catholic"></div>
                            <div><label class="kk-label">Citizenship</label><input type="text" class="kk-input" name="citizenship" value="<?= esc($y['citizenship'] ?? 'Filipino') ?>"></div>
                        </div>
                        <div class="kk-grid-2">
                            <div><label class="kk-label">Contact Number</label><input type="tel" class="kk-input js-contact-number" name="contact_number" value="<?= esc($y['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)"></div>
                            <div><label class="kk-label">Email Address</label><input type="email" class="kk-input" name="email" value="<?= esc($y['email'] ?? '') ?>" placeholder="email@example.com"></div>
                        </div>
                        <div class="kk-full"><label class="kk-label">Complete Address</label><input type="text" class="kk-input" name="address" value="<?= esc($y['address'] ?? '') ?>" placeholder="House No., Street, Barangay, Municipality, Province"></div>
                        <div class="kk-grid-2">
                            <div><label class="kk-label">Zone / Purok</label>
                                <select class="kk-input" name="zone">
                                    <option value="">— Select —</option>
                                    <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'] as $z): ?>
                                        <option <?= ($y['zone'] ?? '') === $z ? 'selected' : '' ?>><?= $z ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div><label class="kk-label">No. of Months/Years in Brgy.</label><input type="text" class="kk-input" name="months_in_brgy" value="<?= esc($y['months_in_brgy'] ?? '') ?>" placeholder="e.g. 5 years"></div>
                        </div>
                        <div class="kk-grid-2">
                            <div><label class="kk-label">Talent / Skills</label><input type="text" class="kk-input" name="skills" value="<?= esc($y['skills'] ?? '') ?>" placeholder="e.g. Drawing, Singing"></div>
                            <div><label class="kk-label">Interests / Hobbies</label><input type="text" class="kk-input" name="hobbies" value="<?= esc($y['hobbies'] ?? '') ?>" placeholder="e.g. Reading, Sports"></div>
                        </div>
                        <div class="kk-grid-2">
                            <div><label class="kk-label">Mother's Maiden Name</label><input type="text" class="kk-input" name="mother_name" value="<?= esc($y['mother_name'] ?? '') ?>"></div>
                            <div><label class="kk-label">Mother's Occupation</label><input type="text" class="kk-input" name="mother_occupation" value="<?= esc($y['mother_occupation'] ?? '') ?>"></div>
                        </div>
                        <div class="kk-grid-2">
                            <div><label class="kk-label">Father's Name</label><input type="text" class="kk-input" name="father_name" value="<?= esc($y['father_name'] ?? '') ?>"></div>
                            <div><label class="kk-label">Father's Occupation</label><input type="text" class="kk-input" name="father_occupation" value="<?= esc($y['father_occupation'] ?? '') ?>"></div>
                        </div>
                    </div>
                </div>

                <!-- ── Section 2: Organization Membership ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-sitemap"></i>
                        <h4>Section 2 — Membership in Organization</h4>
                    </div>
                    <div class="kk-card-body">
                        <table class="kk-org-table">
                            <thead>
                                <tr>
                                    <th>Name of Organization</th>
                                    <th>Position</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($r = 0; $r < 4; $r++): ?>
                                    <tr>
                                        <td><input type="text" name="org_name[]" value="<?= esc($orgs[$r]['name']     ?? '') ?>" placeholder="Organization name"></td>
                                        <td><input type="text" name="org_position[]" value="<?= esc($orgs[$r]['position'] ?? '') ?>" placeholder="Position held"></td>
                                        <td><input type="text" name="org_year[]" value="<?= esc($orgs[$r]['year']     ?? '') ?>" placeholder="Year"></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── Section 3 & 4: Age Group & Civil Status ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-id-card"></i>
                        <h4>Section 3 & 4 — Age Group & Civil Status</h4>
                    </div>
                    <div class="kk-card-body">
                        <div class="kk-grid-2">
                            <div>
                                <label class="kk-label">Age Group</label>
                                <select class="kk-input" name="age_group">
                                    <option value="">— Auto from DOB —</option>
                                    <?php foreach (\App\Models\SkYouthModel::AGE_GROUPS as $v => $l): ?>
                                        <option value="<?= esc($v) ?>" <?= ($y['age_group'] ?? '') === $v ? 'selected' : '' ?>><?= esc($l) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="kk-label">Civil Status</label>
                                <select class="kk-input" name="civil_status">
                                    <option value="">— Select —</option>
                                    <?php foreach ($optionsWithCurrent(\App\Models\SkYouthModel::CIVIL_STATUSES, (string) ($y['civil_status'] ?? '')) as $cs): ?>
                                        <option value="<?= esc($cs) ?>" <?= strcasecmp((string) ($y['civil_status'] ?? ''), $cs) === 0 ? 'selected' : '' ?>><?= esc($cs) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Section 5: Educational Background ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-graduation-cap"></i>
                        <h4>Section 5 — Educational Background</h4>
                    </div>
                    <div class="kk-card-body">
                        <div class="kk-grid-2">
                            <div>
                                <label class="kk-label">Educational Background</label>
                                <select class="kk-input" name="educational_background">
                                    <option value="">— Select —</option>
                                    <?php foreach ($optionsWithCurrent(\App\Models\SkYouthModel::EDUCATION_OPTIONS, (string) ($y['educational_background'] ?? '')) as $e): ?>
                                        <option value="<?= esc($e) ?>" <?= strcasecmp((string) ($y['educational_background'] ?? ''), $e) === 0 ? 'selected' : '' ?>><?= esc($e) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="kk-label">School Type</label>
                                <select class="kk-input" name="school_type">
                                    <option value="">— Select —</option>
                                    <option value="Public School" <?= ($y['school_type'] ?? '') === 'Public School'  ? 'selected' : '' ?>>Public School</option>
                                    <option value="Private School" <?= ($y['school_type'] ?? '') === 'Private School' ? 'selected' : '' ?>>Private School</option>
                                </select>
                            </div>
                        </div>
                        <div class="kk-full"><label class="kk-label">Grade Level / Year Level / Course / Specialization</label><input type="text" class="kk-input" name="school_detail" value="<?= esc($y['school_detail'] ?? '') ?>" placeholder="e.g. Grade 11 / 2nd Year / BS Nursing"></div>
                    </div>
                </div>

                <!-- ── Section 6 & 9: Governance & Economic Status ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-briefcase"></i>
                        <h4>Section 6 & 9 — Governance & Economic Status</h4>
                    </div>
                    <div class="kk-card-body">
                        <div class="kk-grid-3">
                            <div>
                                <label class="kk-label">Governance</label>
                                <select class="kk-input" name="governance">
                                    <option value="">— Select —</option>
                                    <?php foreach (\App\Models\SkYouthModel::GOVERNANCE_OPTIONS as $gov): ?>
                                        <option value="<?= esc($gov) ?>" <?= strcasecmp((string) ($y['governance'] ?? ''), $gov) === 0 ? 'selected' : '' ?>><?= esc($gov) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="kk-label">Economic Status</label>
                                <select class="kk-input" name="economic_status">
                                    <option value="">— Select —</option>
                                    <?php foreach ($optionsWithCurrent(\App\Models\SkYouthModel::ECONOMIC_STATUSES, (string) ($y['economic_status'] ?? '')) as $es): ?>
                                        <option value="<?= esc($es) ?>" <?= strcasecmp((string) ($y['economic_status'] ?? ''), $es) === 0 ? 'selected' : '' ?>><?= esc($es) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="kk-label">Monthly Income</label>
                                <select class="kk-input" name="monthly_income">
                                    <option value="">— Select —</option>
                                    <?php foreach ($optionsWithCurrent(\App\Models\SkYouthModel::INCOME_BRACKETS, (string) ($y['monthly_income'] ?? '')) as $inc): ?>
                                        <option value="<?= esc($inc) ?>" <?= strcasecmp((string) ($y['monthly_income'] ?? ''), $inc) === 0 ? 'selected' : '' ?>><?= esc($inc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Section 7 & 8: Health & Social Inclusion ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-heartbeat"></i>
                        <h4>Section 7 & 8 — Health Concerns & Social Inclusion</h4>
                    </div>
                    <div class="kk-card-body">
                        <label class="kk-label" style="margin-bottom:10px;">Physical & Mental Health (check all that apply)</label>
                        <div class="kk-check-row" style="margin-bottom:20px;">
                            <?php foreach (\App\Models\SkYouthModel::HEALTH_OPTIONS as $h): ?>
                                <label class="kk-check">
                                    <input type="checkbox" name="health[]" value="<?= $h ?>" <?= in_array($h, $health) ? 'checked' : '' ?>>
                                    <?= $h ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="kk-label" style="margin-bottom:10px;">Social Inclusion & Equity (check all that apply)</label>
                        <div class="kk-check-row">
                            <?php foreach (\App\Models\SkYouthModel::SOCIAL_OPTIONS as $s): ?>
                                <label class="kk-check">
                                    <input type="checkbox" name="social[]" value="<?= $s ?>" <?= in_array($s, $social) ? 'checked' : '' ?>>
                                    <?= $s ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Section 10–12: Advocacy, Volunteer, Issues ── -->
                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-hands-helping"></i>
                        <h4>Section 10–12 — Advocacy, Volunteer & Issues</h4>
                    </div>
                    <div class="kk-card-body">
                        <label class="kk-label" style="margin-bottom:10px;">Center of Youth Participation / Advocacy</label>
                        <div class="kk-check-row" style="margin-bottom:16px;">
                            <?php foreach (\App\Models\SkYouthModel::ADVOCACY_OPTIONS as $item): ?>
                                <label class="kk-check">
                                    <input type="checkbox" name="advocacy[]" value="<?= esc($item) ?>" <?= $choiceOn($advocacyStored, $item) ? 'checked' : '' ?>>
                                    <?= esc($item) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="kk-label" style="margin-bottom:10px;">Volunteer Interests</label>
                        <div class="kk-check-row" style="margin-bottom:16px;">
                            <?php foreach (\App\Models\SkYouthModel::VOLUNTEER_OPTIONS as $item): ?>
                                <label class="kk-check">
                                    <input type="checkbox" name="volunteer[]" value="<?= esc($item) ?>" <?= $choiceOn($volunteerStored, $item) ? 'checked' : '' ?>>
                                    <?= esc($item) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="kk-grid-3">
                            <div><label class="kk-label">Issue / Concern #1</label><input type="text" class="kk-input" name="issue_1" value="<?= esc($y['issue_1'] ?? '') ?>" placeholder="e.g. Unemployment"></div>
                            <div><label class="kk-label">Issue / Concern #2</label><input type="text" class="kk-input" name="issue_2" value="<?= esc($y['issue_2'] ?? '') ?>" placeholder="e.g. Drug Abuse"></div>
                            <div><label class="kk-label">Issue / Concern #3</label><input type="text" class="kk-input" name="issue_3" value="<?= esc($y['issue_3'] ?? '') ?>" placeholder="e.g. Lack of Programs"></div>
                        </div>
                        <div class="kk-full">
                            <label class="kk-label">Suggested Programs / Projects / Activities</label>
                            <textarea class="kk-input" name="suggestions" rows="3" placeholder="Describe your suggestions..."><?= esc($y['suggestions'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="kk-card">
                    <div class="kk-card-header"><i class="fas fa-camera"></i>
                        <h4>Profile Photo</h4>
                    </div>
                    <div class="kk-card-body">
                        <label class="kk-label">Profile photo</label>
                        <input type="file" class="kk-input" name="profile_photo" id="profilePhotoInput" accept="image/jpeg,image/png,image/webp">
                        <small style="display:block;color:#9aa0b4;margin-top:5px;">A 2 × 2 inch photo is part of profiling. You can still save the other information if a photo is not available yet. JPG, PNG, or WEBP. Maximum 5 MB.</small>
                        <div class="kk-photo-preview" id="photoPreview">
                            <div class="kk-photo-box" id="photoBox">
                                <?php if (! empty($y['photo_path'])): ?>
                                    <img id="photoPreviewImage" src="<?= esc('/uploads/' . ltrim((string) $y['photo_path'], '/')) ?>" alt="2 by 2 inch profile photo">
                                <?php else: ?>
                                    <img id="photoPreviewImage" alt="2 by 2 inch profile photo" hidden>
                                    <span id="photoBoxLabel">2 × 2 in</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <small id="photoPreviewLabel" style="display:<?= empty($y['photo_path']) ? 'none' : 'block' ?>;color:#1a7a55;">
                                    <i class="fas fa-check-circle"></i> Current photo
                                </small>
                                <button type="button" id="removePhotoButton" class="db-btn db-btn--outline" style="margin-top:5px;padding:4px 8px;font-size:11px;">Clear selection</button>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="kk-section-note" style="margin:0 0 12px;">Saving stores this profile on the youth’s SK account. A missing photo does not block the other fields.</p>
                <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:24px;">
                    <a href="<?= esc($returnUrl) ?>" class="db-btn db-btn--outline" onclick="return goBack(event, '<?= esc($returnUrl) ?>');">Cancel</a>
                    <button type="submit" class="db-btn db-btn--primary">
                        <i class="fas fa-save"></i> <?= ($edit || $residentEdit) ? 'Save Changes' : 'Save Profile' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        const residentProfileData = <?= json_encode($profileOptions ?? []) ?>;
        const youthAccountData = <?= json_encode($youthAccounts ?? []) ?>;
        const selectedAccountId = <?= $selectedAccountId ? (int) $selectedAccountId : 'null' ?>;

        function goBack(event, fallbackUrl) {
            event.preventDefault();
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = fallbackUrl;
            }
            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('profilePhotoInput');
            const previewImage = document.getElementById('photoPreviewImage');
            const previewLabel = document.getElementById('photoPreviewLabel');
            const photoBoxLabel = document.getElementById('photoBoxLabel');
            const removeButton = document.getElementById('removePhotoButton');
            if (!input || !previewImage) return;

            function showPhoto(src) {
                previewImage.hidden = false;
                previewImage.src = src;
                if (photoBoxLabel) photoBoxLabel.hidden = true;
                if (previewLabel) previewLabel.style.display = 'block';
            }

            function showEmptyBox() {
                previewImage.hidden = true;
                previewImage.removeAttribute('src');
                if (photoBoxLabel) photoBoxLabel.hidden = false;
                if (previewLabel) previewLabel.style.display = 'none';
            }

            input.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;
                if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
                    this.value = '';
                    return;
                }
                showPhoto(URL.createObjectURL(file));
                if (previewLabel) previewLabel.innerHTML = '<i class="fas fa-check-circle"></i> New photo selected';
            });

            if (removeButton) removeButton.addEventListener('click', function() {
                input.value = '';
                <?php if (! empty($y['photo_path'])): ?>
                    showPhoto(<?= json_encode('/uploads/' . ltrim($y['photo_path'], '/')) ?>);
                    if (previewLabel) previewLabel.innerHTML = '<i class="fas fa-check-circle"></i> Current photo';
                <?php else: ?>
                    showEmptyBox();
                <?php endif; ?>
            });
        });

        function parseChoiceList(value) {
            if (Array.isArray(value)) return value;
            if (!value) return [];
            if (typeof value === 'string') {
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed)) return parsed;
                } catch (error) {}
                return value.split(',').map(function (item) { return item.trim(); }).filter(Boolean);
            }
            return [];
        }

        function setChecks(name, values) {
            const wanted = parseChoiceList(values).map(function (item) { return String(item).toLowerCase(); });
            document.querySelectorAll('input[name="' + name + '"]').forEach(function (box) {
                box.checked = wanted.some(function (item) {
                    return item === box.value.toLowerCase() || item.indexOf(box.value.toLowerCase()) !== -1;
                });
            });
        }

        function setOrganizations(raw) {
            let rows = [];
            if (typeof raw === 'string' && raw) {
                try { rows = JSON.parse(raw) || []; } catch (error) { rows = []; }
            } else if (Array.isArray(raw)) {
                rows = raw;
            }
            const names = document.querySelectorAll('[name="org_name[]"]');
            const positions = document.querySelectorAll('[name="org_position[]"]');
            const years = document.querySelectorAll('[name="org_year[]"]');
            names.forEach(function (field, index) {
                field.value = (rows[index] && rows[index].name) || '';
                if (positions[index]) positions[index].value = (rows[index] && rows[index].position) || '';
                if (years[index]) years[index].value = (rows[index] && rows[index].year) || '';
            });
        }

        function showStoredPhoto(path) {
            const previewImage = document.getElementById('photoPreviewImage');
            const previewLabel = document.getElementById('photoPreviewLabel');
            const photoBoxLabel = document.getElementById('photoBoxLabel');
            const input = document.getElementById('profilePhotoInput');
            if (input) input.value = '';
            if (!previewImage) return;
            if (path) {
                previewImage.hidden = false;
                previewImage.src = '/uploads/' + String(path).replace(/^\/+/, '');
                if (photoBoxLabel) photoBoxLabel.hidden = true;
                if (previewLabel) {
                    previewLabel.style.display = 'block';
                    previewLabel.innerHTML = '<i class="fas fa-check-circle"></i> Current photo';
                }
                return;
            }
            previewImage.hidden = true;
            previewImage.removeAttribute('src');
            if (photoBoxLabel) photoBoxLabel.hidden = false;
            if (previewLabel) previewLabel.style.display = 'none';
        }

        function fillYouthFields(data) {
            document.querySelectorAll('[data-auto-filled="true"]').forEach(function (field) {
                field.readOnly = false;
                field.style.pointerEvents = '';
                field.tabIndex = 0;
                field.removeAttribute('aria-readonly');
                field.removeAttribute('data-auto-filled');
            });
            if (!data) return;
            const fieldMap = {
                last_name: data.last_name || '',
                first_name: data.first_name || '',
                middle_name: data.middle_name || '',
                suffix: data.suffix || '',
                date_of_birth: data.date_of_birth ? String(data.date_of_birth).slice(0, 10) : '',
                place_of_birth: data.place_of_birth || '',
                gender: data.gender || '',
                religion: data.religion || '',
                citizenship: data.citizenship || 'Filipino',
                contact_number: data.contact_number || '',
                email: data.email || '',
                address: data.address || '',
                zone: data.zone || '',
                months_in_brgy: data.months_in_brgy || '',
                skills: data.skills || '',
                hobbies: data.hobbies || '',
                mother_name: data.mother_name || '',
                mother_occupation: data.mother_occupation || '',
                father_name: data.father_name || '',
                father_occupation: data.father_occupation || '',
                governance: data.governance || '',
                age_group: data.age_group || '',
                civil_status: data.civil_status || '',
                educational_background: data.educational_background || '',
                school_type: data.school_type || '',
                school_detail: data.school_detail || '',
                economic_status: data.economic_status || '',
                monthly_income: data.monthly_income || '',
                issue_1: data.issue_1 || '',
                issue_2: data.issue_2 || '',
                issue_3: data.issue_3 || '',
                suggestions: data.suggestions || ''
            };
            Object.entries(fieldMap).forEach(function (entry) {
                const field = document.querySelector('[name="' + entry[0] + '"]');
                if (!field) return;
                const value = entry[1];
                if (field.tagName === 'SELECT') {
                    const match = Array.from(field.options).find(function (option) {
                        return (option.value || '').toLowerCase() === String(value).toLowerCase();
                    });
                    field.value = match ? match.value : '';
                } else {
                    field.value = value;
                }
                field.readOnly = false;
                field.style.pointerEvents = '';
                field.tabIndex = 0;
            });
            setChecks('health[]', data.health_concerns || data.health || []);
            setChecks('social[]', data.social_inclusion || data.social || []);
            setChecks('advocacy[]', data.advocacy || []);
            setChecks('volunteer[]', data.volunteer || []);
            setOrganizations(data.organizations || []);
            showStoredPhoto(data.photo_path || '');
            calcAge();
        }

        function applyProfileData(data) {
            fillYouthFields(data);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const accountSearch = document.getElementById('youthAccountSearch');
            const accountId = document.getElementById('youthAccountId');
            if (accountSearch && selectedAccountId) {
                const selectedAccount = youthAccountData.find(item => Number(item.id) === selectedAccountId);
                if (selectedAccount) {
                    accountSearch.value = selectedAccount.label;
                    accountId.value = selectedAccount.id;
                    applyProfileData(selectedAccount.data);
                }
            }
            if (accountSearch) accountSearch.addEventListener('input', function() {
                const value = this.value.trim().toLowerCase();
                const account = youthAccountData.find(item => item.label.toLowerCase() === value);
                accountId.value = account ? account.id : '';
                applyProfileData(account ? account.data : null);
            });
        });

        function markProfileOption(optionId) {
            document.querySelectorAll('.kk-profile-option').forEach(card => {
                const on = card.dataset.optionId === optionId;
                card.classList.toggle('selected', on);
                const radio = card.querySelector('input[type="radio"]');
                if (radio) radio.checked = on;
            });
        }

        function applyResidentProfileChoice(optionId) {
            markProfileOption(optionId);
            const option = residentProfileData.find(item => item.id === optionId);
            if (!option || !option.data) return;
            fillYouthFields(option.data);
            markProfileOption(optionId);
        }

        function calcAge() {
            const dob = document.getElementById('dob').value;
            if (!dob) {
                document.getElementById('ageOut').value = '';
                return;
            }
            const today = new Date();
            const birth = new Date(dob);
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
            document.getElementById('ageOut').value = age >= 0 ? age : '';

            if (document.getElementById('kkForm') && <?= $residentMode ? 'true' : 'false' ?>) {
                const field = document.getElementById('ageOut');
                const selected = document.querySelector('input[name="profile_option"]:checked');
                const option = selected ? residentProfileData.find(item => item.id === selected.value) : null;
                const allowMinor = option && option.is_minor;
                if (!allowMinor && field.value && (Number(field.value) < 15 || Number(field.value) > 30)) {
                    field.style.borderColor = '#d94a4a';
                    field.title = 'Age must be between 15 and 30 years old.';
                } else {
                    field.style.borderColor = '';
                    field.title = '';
                }
            }
        }

        if (<?= $residentMode ? 'true' : 'false' ?>) {
            document.addEventListener('DOMContentLoaded', function() {
                const radios = document.querySelectorAll('input[name="profile_option"]');
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const hidden = document.getElementById('formProfileOption');
                        if (hidden) hidden.value = this.value;
                        applyResidentProfileChoice(this.value);
                    });
                });
            });
        }

        calcAge(); // run on load for edit mode

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>