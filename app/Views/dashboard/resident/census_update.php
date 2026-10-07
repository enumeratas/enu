<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Census Update') ?> - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .census-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(29, 36, 72, .08);
            border: 1px solid #eaedf7;
            overflow: hidden;
        }

        .census-header {
            padding: 28px 28px 18px;
            border-bottom: 1px solid #edf1f7;
            background: linear-gradient(135deg, #eff4ff, #f9fbff);
        }

        .census-header h1 { margin: 0; font-size: 24px; color: #1d2448; }
        .census-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }

        .census-body { padding: 28px; }

        .cu-section {
            margin-bottom: 22px;
            border: 1px solid #e4e9f2;
            border-radius: 12px;
            overflow: hidden;
        }

        .cu-section-bar {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
            padding: 12px 18px;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: .3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cu-section-body {
            padding: 20px 22px;
            background: #fff;
        }

        .census-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .cu-grid-2 { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .cu-grid-3 { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .cu-grid-4 { grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .cu-span-all { grid-column: 1 / -1; }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #4a5068;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            border: 1.5px solid #dde4f0;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            box-sizing: border-box;
        }

        .form-group input[readonly] {
            background: #f3f5fb;
            color: #6b7280;
        }

        .form-group textarea {
            min-height: 90px;
            resize: vertical;
        }

        .cu-radio-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            padding: 6px 0;
        }

        .cu-radio {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #1a1d2e;
            cursor: pointer;
        }

        .cu-radio input { accent-color: #1d2448; }

        .cu-check-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 20px;
            padding: 4px 0;
        }

        .cu-check {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #1a1d2e;
            padding: 8px 12px;
            border: 1.5px solid #dde4f0;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
        }

        .cu-check input { accent-color: #16c79a; }
        .cu-check:hover { border-color: #c9d4ea; }

        .cu-sub-label {
            margin: 0 0 10px;
            padding: 10px 0 6px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #4a5068;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cu-sub-label i { color: #1d2448; }

        .census-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .db-btn--confirm { background: #16c79a; color: #fff; }

        .census-note {
            margin-top: 18px;
            padding: 14px 16px;
            background: #fff8ee;
            border: 1px solid #f6d7a8;
            border-radius: 10px;
            color: #7a4200;
            font-size: 12.5px;
        }

        .cu-info {
            margin: 0 0 18px;
            padding: 12px 14px;
            background: #eef5ff;
            border: 1px solid #cfe0ff;
            border-radius: 10px;
            color: #21446f;
            font-size: 12.5px;
        }

        .cu-member-card {
            border: 1px solid #e4e9f2;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            background: #fbfcff;
        }

        .cu-member-title {
            font-weight: 700;
            color: #1d2448;
            margin: 0 0 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cu-pending {
            margin: 0 0 12px;
            padding: 10px 12px;
            background: #fff8ee;
            border: 1px solid #f6d7a8;
            border-radius: 8px;
            color: #7a4200;
            font-size: 12.5px;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $portalRole = ($role ?? '') === 'council' ? 'council' : 'resident';
    $active = 'census_update';
    $pageTitle = $pageTitle ?? 'Census Update';
    $canEditHousehold = (bool) ($householdAccess['can_edit_household'] ?? false);
    $canEditPersonal  = (bool) ($householdAccess['can_edit_personal'] ?? false);
    $user = $user ?? [];
    $household = $household ?? [];
    $members = $members ?? [];
    $pendingMemberRequests = $pendingMemberRequests ?? [];
    $auth = $auth ?? [];
    $own = $householdAccess['member'] ?? null;

    $sel = static function ($value, $candidate): string {
        return (string) $value === (string) $candidate ? 'selected' : '';
    };
    $chk = static function ($value, $candidate = '1'): string {
        return (string) $value === (string) $candidate ? 'checked' : '';
    };

    $educationOptions = [
        'No Formal Education',
        'Elementary Level',
        'Elementary Graduate',
        'High School Level',
        'High School Graduate',
        'College Level',
        'College Graduate',
        'Vocational / Tech-Voc',
        'Post Graduate',
    ];
    $relationshipOptions = [
        'spouse' => 'Spouse',
        'child' => 'Child',
        'father' => 'Father',
        'mother' => 'Mother',
        'sibling' => 'Sibling',
        'grandparent' => 'Grandparent',
        'grandchild' => 'Grandchild',
        'aunt_uncle' => 'Aunt/Uncle',
        'cousin' => 'Cousin',
        'other_relative' => 'Other Relative',
        'non_relative' => 'Non-relative',
        'former_head' => 'Former Head',
    ];
    $pendingByMember = [];
    foreach ($pendingMemberRequests as $pendingRequest) {
        $memberId = (int) ($pendingRequest['member_id'] ?? 0);
        if ($memberId > 0) {
            $pendingByMember[$memberId][] = $pendingRequest['request_type'] ?? '';
        }
    }

    include APPPATH . 'Views/dashboard/sidebar.php';
    ?>
    <div class="db-main">
        <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
        <div class="db-content">
            <div class="census-card">
                <div class="census-header">
                    <?php if ($canEditHousehold): ?>
                        <h1><i class="fas fa-user-edit"></i> Census Update Form</h1>
                        <p>Review and update the complete household record. Adding, moving, or removing a member needs Captain or Secretary approval before it takes effect.</p>
                    <?php else: ?>
                        <h1><i class="fas fa-user-edit"></i> My Census Information</h1>
                        <p>You can view and update only your own personal information. If nothing has changed, confirm that your record is correct.</p>
                    <?php endif; ?>
                </div>
                <div class="census-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (($auth['status'] ?? '') === 'submitted'): ?>
                        <div class="cu-info">
                            Your latest census update is waiting for Captain or Secretary review.
                            <?= ! empty($auth['submitted_at']) ? ' Submitted ' . esc(date('M j, Y g:i A', strtotime((string) $auth['submitted_at']))) . '.' : '' ?>
                        </div>
                    <?php endif; ?>

                    <?php if (! $canEditHousehold): ?>
                        <div class="cu-info">
                            Household-wide details can only be changed by the household head. You may update or confirm your own information below.
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/<?= esc($portalRole) ?>/census-update">
                        <?= csrf_field() ?>
                        <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">

                        <?php if ($canEditHousehold): ?>
                            <div class="cu-section">
                                <div class="cu-section-bar"><i class="fas fa-user"></i> Household Head — Personal Information</div>
                                <div class="cu-section-body">
                                    <div class="census-grid cu-grid-4">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input id="last_name" name="last_name" type="text" value="<?= esc($household['last_name'] ?? '') ?>" placeholder="DELA CRUZ">
                                        </div>
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input id="first_name" name="first_name" type="text" value="<?= esc($household['first_name'] ?? '') ?>" placeholder="JUAN">
                                        </div>
                                        <div class="form-group">
                                            <label for="middle_name">Middle Name</label>
                                            <input id="middle_name" name="middle_name" type="text" value="<?= esc($household['middle_name'] ?? '') ?>" placeholder="SANTOS">
                                        </div>
                                        <div class="form-group">
                                            <label for="suffix">Suffix</label>
                                            <select id="suffix" name="suffix">
                                                <option value="">— None —</option>
                                                <?php foreach (['Jr', 'Sr', 'II', 'III', 'IV'] as $sfx): ?>
                                                    <option value="<?= esc($sfx) ?>" <?= $sel($household['suffix'] ?? '', $sfx) ?>><?= esc($sfx) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="date_of_birth">Date of Birth</label>
                                            <input id="date_of_birth" name="date_of_birth" type="date" value="<?= esc($household['date_of_birth'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="place_of_birth">Place of Birth</label>
                                            <input id="place_of_birth" name="place_of_birth" type="text" value="<?= esc($household['place_of_birth'] ?? '') ?>" placeholder="City/Municipality">
                                        </div>
                                        <div class="form-group">
                                            <label for="gender">Gender</label>
                                            <select id="gender" name="gender">
                                                <?php foreach (['Male', 'Female'] as $g): ?>
                                                    <option value="<?= esc($g) ?>" <?= $sel($household['gender'] ?? '', $g) ?>><?= esc($g) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="civil_status">Civil Status</label>
                                            <select id="civil_status" name="civil_status">
                                                <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $cs): ?>
                                                    <option value="<?= esc($cs) ?>" <?= $sel($household['civil_status'] ?? '', $cs) ?>><?= esc($cs) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="nationality">Nationality</label>
                                            <input id="nationality" name="nationality" type="text" value="<?= esc($household['nationality'] ?? 'Filipino') ?>" placeholder="Filipino">
                                        </div>
                                        <div class="form-group">
                                            <label for="religion">Religion</label>
                                            <input id="religion" name="religion" type="text" value="<?= esc($household['religion'] ?? '') ?>" placeholder="e.g. Roman Catholic">
                                        </div>
                                        <div class="form-group">
                                            <label for="occupation">Occupation</label>
                                            <input id="occupation" name="occupation" type="text" value="<?= esc($household['occupation'] ?? '') ?>" placeholder="e.g. Farmer">
                                        </div>
                                        <div class="form-group">
                                            <label for="monthly_income">Monthly Income (₱)</label>
                                            <input id="monthly_income" name="monthly_income" type="number" step="0.01" min="0" value="<?= esc($household['monthly_income'] ?? '0') ?>" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="census-grid cu-grid-3" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="contact_number">Contact Number</label>
                                            <input id="contact_number" name="contact_number" type="tel" inputmode="numeric" maxlength="11" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)" value="<?= esc($household['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX">
                                        </div>
                                        <div class="form-group">
                                            <label for="educational_attainment">Educational Attainment</label>
                                            <select id="educational_attainment" name="educational_attainment">
                                                <option value="">— Select —</option>
                                                <?php foreach ($educationOptions as $ed): ?>
                                                    <option value="<?= esc($ed) ?>" <?= $sel($household['educational_attainment'] ?? '', $ed) ?>><?= esc($ed) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="philhealth_no">PhilHealth Number</label>
                                            <input id="philhealth_no" name="philhealth_no" type="text" inputmode="numeric" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)" value="<?= esc($household['philhealth_no'] ?? '') ?>" placeholder="00000000000">
                                        </div>
                                    </div>

                                    <div style="margin-top:16px;">
                                        <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">Registered Voter?</label>
                                        <div class="cu-radio-row">
                                            <label class="cu-radio"><input type="radio" name="registered_voter" value="1" <?= $chk($household['registered_voter'] ?? '', '1') ?>> <span>Yes</span></label>
                                            <label class="cu-radio"><input type="radio" name="registered_voter" value="0" <?= $chk($household['registered_voter'] ?? '', '0') ?>> <span>No</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cu-section">
                                <div class="cu-section-bar"><i class="fas fa-home"></i> Household Information</div>
                                <div class="cu-section-body">
                                    <div class="census-grid">
                                        <div class="form-group cu-span-all">
                                            <label for="address">Household Address</label>
                                            <textarea id="address" name="address" placeholder="House No./Street/Purok/Sitio, Zone, Barangay Bacolod"><?= esc($household['address'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="household_no">Household Number</label>
                                            <input id="household_no" type="text" value="<?= esc($household['household_no'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="zone">Zone / Purok</label>
                                            <select id="zone" name="zone">
                                                <option value="">— Select —</option>
                                                <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'] as $z): ?>
                                                    <option value="<?= esc($z) ?>" <?= $sel($household['zone'] ?? '', $z) ?>><?= esc($z) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="years_of_residency">Years of Residency</label>
                                            <input id="years_of_residency" name="years_of_residency" type="number" min="0" step="1" value="<?= esc($household['years_of_residency'] ?? '0') ?>" placeholder="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="house_ownership">House Ownership</label>
                                            <select id="house_ownership" name="house_ownership">
                                                <?php foreach (['Owned', 'Rented', 'Shared', 'Informal Settler'] as $ho): ?>
                                                    <option value="<?= esc($ho) ?>" <?= $sel($household['house_ownership'] ?? '', $ho) ?>><?= esc($ho) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="num_families">Number of Families Sharing This Household</label>
                                            <input id="num_families" name="num_families" type="number" min="1" max="20" step="1" value="<?= esc($household['num_families'] ?? '1') ?>" placeholder="1">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cu-section">
                                <div class="cu-section-bar"><i class="fas fa-tags"></i> Household Classification</div>
                                <div class="cu-section-body">
                                    <div class="cu-check-row">
                                        <input type="hidden" name="is_4ps" value="0">
                                        <label class="cu-check"><input type="checkbox" name="is_4ps" value="1" <?= $chk($household['is_4ps'] ?? 0) ?>> <span>4Ps Beneficiary</span></label>
                                        <input type="hidden" name="is_senior_citizen" value="0">
                                        <label class="cu-check"><input type="checkbox" name="is_senior_citizen" value="1" <?= $chk($household['is_senior_citizen'] ?? 0) ?>> <span>Senior Citizen</span></label>
                                        <input type="hidden" name="is_solo_parent" value="0">
                                        <label class="cu-check"><input type="checkbox" name="is_solo_parent" value="1" <?= $chk($household['is_solo_parent'] ?? 0) ?>> <span>Solo Parent</span></label>
                                        <input type="hidden" name="is_indigenous" value="0">
                                        <label class="cu-check"><input type="checkbox" name="is_indigenous" value="1" <?= $chk($household['is_indigenous'] ?? 0) ?>> <span>Indigenous People</span></label>
                                        <input type="hidden" name="is_pwd" value="0">
                                        <label class="cu-check"><input type="checkbox" id="is_pwd" name="is_pwd" value="1" <?= $chk($household['is_pwd'] ?? 0) ?> onchange="document.getElementById('pwd_type_wrap').style.display = this.checked ? 'block' : 'none';"> <span>PWD Member</span></label>
                                    </div>
                                    <div id="pwd_type_wrap" style="margin-top:14px; <?= ($household['is_pwd'] ?? 0) ? '' : 'display:none;' ?>">
                                        <div class="form-group">
                                            <label for="pwd_type">Specify Disability</label>
                                            <input id="pwd_type" name="pwd_type" type="text" maxlength="120" value="<?= esc($household['pwd_type'] ?? '') ?>" placeholder="e.g. Visual, Hearing, Physical, Intellectual…">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cu-section">
                                <div class="cu-section-bar"><i class="fas fa-tint"></i> Access to Safe Water &amp; Sanitation Facility</div>
                                <div class="cu-section-body">
                                    <div class="cu-sub-label"><i class="fas fa-water"></i> Access to Safe Water</div>
                                    <div class="census-grid cu-grid-2">
                                        <div>
                                            <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">1. Basic Safe Water Source</label>
                                            <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                                <label class="cu-radio"><input type="radio" name="water_source_level" value="I" <?= $chk($household['water_source_level'] ?? '', 'I') ?>> <span>Level I — Point Source (e.g. protected well, spring)</span></label>
                                                <label class="cu-radio"><input type="radio" name="water_source_level" value="II" <?= $chk($household['water_source_level'] ?? '', 'II') ?>> <span>Level II — Communal Faucet / Stand Post</span></label>
                                                <label class="cu-radio"><input type="radio" name="water_source_level" value="III" <?= $chk($household['water_source_level'] ?? '', 'III') ?>> <span>Level III — Individual House Connection (piped water)</span></label>
                                                <label class="cu-radio"><input type="radio" name="water_source_level" value="none" <?= $chk($household['water_source_level'] ?? '', 'none') ?>> <span>No Safe Water Source</span></label>
                                            </div>
                                        </div>
                                        <div>
                                            <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">2. Using Safely Managed Water Service</label>
                                            <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                                <label class="cu-radio"><input type="radio" name="water_safety_managed" value="1" <?= $chk($household['water_safety_managed'] ?? '', '1') ?>> <span>Yes — Water is safely managed</span></label>
                                                <label class="cu-radio"><input type="radio" name="water_safety_managed" value="0" <?= $chk($household['water_safety_managed'] ?? '', '0') ?>> <span>No — Not safely managed</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cu-sub-label" style="margin-top:18px;border-top:1px solid #f0f2f8;padding-top:16px;"><i class="fas fa-toilet"></i> Sanitation Facility</div>
                                    <div class="census-grid cu-grid-2">
                                        <div>
                                            <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">1. Basic Sanitation Facility</label>
                                            <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                                <label class="cu-radio"><input type="radio" name="sanitation_basic" value="with" <?= $chk($household['sanitation_basic'] ?? '', 'with') ?>> <span>With Basic Sanitation Facility</span></label>
                                                <label class="cu-radio"><input type="radio" name="sanitation_basic" value="without" <?= $chk($household['sanitation_basic'] ?? '', 'without') ?>> <span>Without Basic Sanitation Facility</span></label>
                                            </div>
                                        </div>
                                        <div>
                                            <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">2. Using Safely Managed Sanitation Services</label>
                                            <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                                <label class="cu-radio"><input type="radio" name="sanitation_managed" value="with" <?= $chk($household['sanitation_managed'] ?? '', 'with') ?>> <span>With Safely Managed Sanitation</span></label>
                                                <label class="cu-radio"><input type="radio" name="sanitation_managed" value="without" <?= $chk($household['sanitation_managed'] ?? '', 'without') ?>> <span>Without Safely Managed Sanitation</span></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cu-section">
                                <div class="cu-section-bar"><i class="fas fa-users"></i> Household Members</div>
                                <div class="cu-section-body">
                                    <?php if ($pendingMemberRequests !== []): ?>
                                        <?php foreach ($pendingMemberRequests as $pendingRequest): ?>
                                            <?php
                                            $pendingName = trim(($pendingRequest['mem_first'] ?? '') . ' ' . ($pendingRequest['mem_last'] ?? ''));
                                            if ($pendingName === '' && ($pendingRequest['request_type'] ?? '') === 'add') {
                                                $addPayload = json_decode((string) ($pendingRequest['payload'] ?? ''), true) ?: [];
                                                $pendingName = trim(($addPayload['first_name'] ?? '') . ' ' . ($addPayload['last_name'] ?? ''));
                                            }
                                            ?>
                                            <div class="cu-pending">
                                                Pending <?= esc($pendingRequest['request_type'] ?? 'member') ?> request
                                                <?= $pendingName !== '' ? 'for ' . esc($pendingName) : '' ?>
                                                <?= ! empty($pendingRequest['destination_household_no']) ? ' → household #' . esc($pendingRequest['destination_household_no']) : '' ?>
                                                is waiting for Captain or Secretary approval.
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if ($members === []): ?>
                                        <p style="margin:0 0 16px;color:#6b7280;font-size:13px;">No other household members are recorded yet.</p>
                                    <?php endif; ?>

                                    <?php foreach ($members as $member): ?>
                                        <?php
                                        $memberId = (int) ($member['id'] ?? 0);
                                        $pendingTypes = $pendingByMember[$memberId] ?? [];
                                        $memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
                                        ?>
                                        <div class="cu-member-card">
                                            <div class="cu-member-title">
                                                <span><?= esc($memberName !== '' ? $memberName : 'Household member') ?></span>
                                                <span style="font-weight:500;color:#6b7280;"><?= esc($relationshipOptions[$member['relationship'] ?? ''] ?? ucwords(str_replace('_', ' ', (string) ($member['relationship'] ?? 'Member')))) ?></span>
                                            </div>
                                            <div class="census-grid cu-grid-4">
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input name="existing_members[<?= $memberId ?>][last_name]" type="text" value="<?= esc($member['last_name'] ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input name="existing_members[<?= $memberId ?>][first_name]" type="text" value="<?= esc($member['first_name'] ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Middle Name</label>
                                                    <input name="existing_members[<?= $memberId ?>][middle_name]" type="text" value="<?= esc($member['middle_name'] ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Relationship</label>
                                                    <select name="existing_members[<?= $memberId ?>][relationship]">
                                                        <?php foreach ($relationshipOptions as $relKey => $relLabel): ?>
                                                            <option value="<?= esc($relKey) ?>" <?= $sel($member['relationship'] ?? '', $relKey) ?>><?= esc($relLabel) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Date of Birth</label>
                                                    <input name="existing_members[<?= $memberId ?>][date_of_birth]" type="date" value="<?= esc($member['date_of_birth'] ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Gender</label>
                                                    <select name="existing_members[<?= $memberId ?>][gender]">
                                                        <?php foreach (['Male', 'Female'] as $g): ?>
                                                            <option value="<?= esc($g) ?>" <?= $sel($member['gender'] ?? '', $g) ?>><?= esc($g) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Occupation</label>
                                                    <input name="existing_members[<?= $memberId ?>][occupation]" type="text" value="<?= esc($member['occupation'] ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Monthly Income (₱)</label>
                                                    <input name="existing_members[<?= $memberId ?>][monthly_income]" type="number" step="0.01" min="0" value="<?= esc($member['monthly_income'] ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Educational Attainment</label>
                                                    <select name="existing_members[<?= $memberId ?>][educational_attainment]">
                                                        <option value="">— Select —</option>
                                                        <?php foreach ($educationOptions as $ed): ?>
                                                            <option value="<?= esc($ed) ?>" <?= $sel($member['educational_attainment'] ?? '', $ed) ?>><?= esc($ed) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php if (! in_array('move', $pendingTypes, true)): ?>
                                                <div class="census-grid cu-grid-2" style="margin-top:14px;">
                                                    <div class="form-group">
                                                        <label>Request move to household #</label>
                                                        <input name="move_member[<?= $memberId ?>]" type="text" maxlength="5" placeholder="Leave blank if not moving">
                                                    </div>
                                                    <?php if (! in_array('remove', $pendingTypes, true)): ?>
                                                        <div class="form-group">
                                                            <label>Request removal (reason)</label>
                                                            <input name="remove_member[<?= $memberId ?>]" type="text" maxlength="255" placeholder="Leave blank to keep this member">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif (! in_array('remove', $pendingTypes, true)): ?>
                                                <div class="form-group" style="margin-top:14px;">
                                                    <label>Request removal (reason)</label>
                                                    <input name="remove_member[<?= $memberId ?>]" type="text" maxlength="255" placeholder="Leave blank to keep this member">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="cu-member-card" style="background:#f4f8ff;">
                                        <div class="cu-member-title">Add a new household member</div>
                                        <p style="margin:0 0 12px;color:#6b7280;font-size:12.5px;">This request stays pending until the Captain or Secretary approves it.</p>
                                        <div class="census-grid cu-grid-4">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input name="new_member[last_name]" type="text">
                                            </div>
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input name="new_member[first_name]" type="text">
                                            </div>
                                            <div class="form-group">
                                                <label>Middle Name</label>
                                                <input name="new_member[middle_name]" type="text">
                                            </div>
                                            <div class="form-group">
                                                <label>Relationship</label>
                                                <select name="new_member[relationship]">
                                                    <?php foreach ($relationshipOptions as $relKey => $relLabel): ?>
                                                        <?php if ($relKey === 'former_head') continue; ?>
                                                        <option value="<?= esc($relKey) ?>"><?= esc($relLabel) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Date of Birth</label>
                                                <input name="new_member[date_of_birth]" type="date">
                                            </div>
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select name="new_member[gender]">
                                                    <option value="">— Select —</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Occupation</label>
                                                <input name="new_member[occupation]" type="text">
                                            </div>
                                            <div class="form-group">
                                                <label>Reason / notes</label>
                                                <input name="new_member[reason]" type="text" maxlength="255" placeholder="e.g. Newborn, newly married">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="census-note">
                                <strong>Note:</strong> Household and personal changes are reviewed by the Captain or Secretary before they are applied. Add, move, and remove requests are filed separately and also need official approval.
                            </div>
                            <div class="census-actions">
                                <a href="/<?= esc($portalRole) ?>/dashboard" class="db-btn db-btn--outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                                <button type="submit" name="submit_action" value="update" class="db-btn db-btn--primary" <?= $canEditPersonal ? '' : 'disabled' ?>>
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                            </div>
                        <?php else: ?>
                            <?php
                            $ownLast = $own['last_name'] ?? ($user['last_name'] ?? '');
                            $ownFirst = $own['first_name'] ?? ($user['first_name'] ?? '');
                            $ownMiddle = $own['middle_name'] ?? ($user['middle_name'] ?? '');
                            $ownSuffix = $own['suffix'] ?? '';
                            $ownDob = $own['date_of_birth'] ?? '';
                            $ownGender = $own['gender'] ?? '';
                            $ownCivil = $own['marital_status'] ?? '';
                            $ownOccupation = $own['occupation'] ?? '';
                            $ownIncome = $own['monthly_income'] ?? '';
                            $ownEducation = $own['educational_attainment'] ?? '';
                            $ownPhilhealth = $own['philhealth_no'] ?? '';
                            ?>
                            <div class="cu-section">
                                <div class="cu-section-bar"><i class="fas fa-id-card"></i> My Personal Information</div>
                                <div class="cu-section-body">
                                    <div class="census-grid cu-grid-4">
                                        <div class="form-group">
                                            <label for="member_last_name">Last Name</label>
                                            <input id="member_last_name" name="member_last_name" type="text" value="<?= esc($ownLast) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="member_first_name">First Name</label>
                                            <input id="member_first_name" name="member_first_name" type="text" value="<?= esc($ownFirst) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="member_middle_name">Middle Name</label>
                                            <input id="member_middle_name" name="member_middle_name" type="text" value="<?= esc($ownMiddle) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="member_suffix">Suffix</label>
                                            <select id="member_suffix" name="member_suffix">
                                                <option value="">— None —</option>
                                                <?php foreach (['Jr', 'Sr', 'II', 'III', 'IV'] as $sfx): ?>
                                                    <option value="<?= esc($sfx) ?>" <?= $sel($ownSuffix, $sfx) ?>><?= esc($sfx) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="member_date_of_birth">Date of Birth</label>
                                            <input id="member_date_of_birth" name="member_date_of_birth" type="date" value="<?= esc($ownDob) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="member_gender">Gender</label>
                                            <select id="member_gender" name="member_gender">
                                                <?php foreach (['Male', 'Female'] as $g): ?>
                                                    <option value="<?= esc($g) ?>" <?= $sel($ownGender, $g) ?>><?= esc($g) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="member_civil_status">Civil Status</label>
                                            <select id="member_civil_status" name="member_civil_status">
                                                <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $cs): ?>
                                                    <option value="<?= esc($cs) ?>" <?= $sel($ownCivil, $cs) ?>><?= esc($cs) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="member_occupation">Occupation</label>
                                            <input id="member_occupation" name="member_occupation" type="text" value="<?= esc($ownOccupation) ?>">
                                        </div>
                                    </div>
                                    <div class="census-grid cu-grid-3" style="margin-top:16px;">
                                        <div class="form-group">
                                            <label for="member_monthly_income">Monthly Income (₱)</label>
                                            <input id="member_monthly_income" name="member_monthly_income" type="number" step="0.01" min="0" value="<?= esc($ownIncome) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="member_educational_attainment">Educational Attainment</label>
                                            <select id="member_educational_attainment" name="member_educational_attainment">
                                                <option value="">— Select —</option>
                                                <?php foreach ($educationOptions as $ed): ?>
                                                    <option value="<?= esc($ed) ?>" <?= $sel($ownEducation, $ed) ?>><?= esc($ed) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="member_philhealth_no">PhilHealth Number</label>
                                            <input id="member_philhealth_no" name="member_philhealth_no" type="text" inputmode="numeric" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)" value="<?= esc($ownPhilhealth) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="census-note">
                                <strong>Note:</strong> Your personal update is reviewed by the Captain or Secretary. If your information is already correct, use <em>Confirm information is correct</em> instead of submitting changes.
                            </div>
                            <div class="census-actions">
                                <a href="/<?= esc($portalRole) ?>/dashboard" class="db-btn db-btn--outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                                <button type="submit" name="submit_action" value="confirm" class="db-btn db-btn--confirm" <?= $canEditPersonal ? '' : 'disabled' ?>>
                                    <i class="fas fa-check"></i> Confirm information is correct
                                </button>
                                <button type="submit" name="submit_action" value="update" class="db-btn db-btn--primary" <?= $canEditPersonal ? '' : 'disabled' ?>>
                                    <i class="fas fa-paper-plane"></i> Submit my changes
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
