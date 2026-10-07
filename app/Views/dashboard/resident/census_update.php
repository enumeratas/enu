<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Update Authorization</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        body { background: #f4f7fb; }

        .census-update-shell {
            width: 100%;
            max-width: none;
            margin: 24px 0;
            padding: 0 24px 40px;
            box-sizing: border-box;
        }

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

        .census-header h1 { margin: 0; font-size: 28px; color: #1d2448; }
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

        .form-group input[disabled],
        .form-group select[disabled],
        .form-group textarea[disabled],
        .form-group input[readonly] {
            background: #f3f5fb;
            color: #6b7280;
            cursor: not-allowed;
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
        }

        .db-btn {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .db-btn--primary { background: linear-gradient(135deg, #1d2448, #2e3a6e); color: #fff; }
        .db-btn--outline { background: #fff; color: #1d2448; border: 1.5px solid #dfe5f2; }

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
    </style>
</head>

<body class="bis-dash">
    <?php
    $portalRole = session()->get('role') === 'council' ? 'council' : 'resident';
    $canEditHousehold = (bool) ($householdAccess['can_edit_household'] ?? false);
    $canEditPersonal  = (bool) ($householdAccess['can_edit_personal'] ?? false);

    // Short helpers to compare the stored value against a candidate option.
    $sel = static function ($value, $candidate): string {
        return (string) $value === (string) $candidate ? 'selected' : '';
    };
    $chk = static function ($value, $candidate = '1'): string {
        return (string) $value === (string) $candidate ? 'checked' : '';
    };
    $dis = $canEditHousehold ? '' : 'disabled';

    $household = $household ?? [];
    ?>
    <div class="census-update-shell">
        <div class="census-card">
            <div class="census-header">
                <h1><i class="fas fa-user-edit"></i> Household Head Update Form</h1>
                <p>Please review and update the household details below. This form is intended for the household head and will be reviewed by the barangay secretary.</p>
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

                <?php if (! $canEditHousehold): ?>
                    <div class="cu-info">
                        <strong>Access Notice:</strong> You are linked as a household member, not the household head. Household-level fields are shown here read-only. Household-level changes must be submitted by the household head and approved by the secretary.
                    </div>
                <?php endif; ?>

                <form method="post" action="/<?= esc($portalRole) ?>/census-update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">

                    <!-- ───────── 1. Household Head Personal Information ───────── -->
                    <div class="cu-section">
                        <div class="cu-section-bar"><i class="fas fa-user"></i> Household Head — Personal Information</div>
                        <div class="cu-section-body">
                            <div class="census-grid cu-grid-4">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input id="last_name" name="last_name" type="text" value="<?= esc($household['last_name'] ?? '') ?>" placeholder="DELA CRUZ" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input id="first_name" name="first_name" type="text" value="<?= esc($household['first_name'] ?? '') ?>" placeholder="JUAN" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="middle_name">Middle Name</label>
                                    <input id="middle_name" name="middle_name" type="text" value="<?= esc($household['middle_name'] ?? '') ?>" placeholder="SANTOS" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="suffix">Suffix</label>
                                    <select id="suffix" name="suffix" <?= $dis ?>>
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
                                    <input id="date_of_birth" name="date_of_birth" type="date" value="<?= esc($household['date_of_birth'] ?? '') ?>" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="place_of_birth">Place of Birth</label>
                                    <input id="place_of_birth" name="place_of_birth" type="text" value="<?= esc($household['place_of_birth'] ?? '') ?>" placeholder="City/Municipality" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender" <?= $dis ?>>
                                        <?php foreach (['Male', 'Female'] as $g): ?>
                                            <option value="<?= esc($g) ?>" <?= $sel($household['gender'] ?? '', $g) ?>><?= esc($g) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="civil_status">Civil Status</label>
                                    <select id="civil_status" name="civil_status" <?= $dis ?>>
                                        <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $cs): ?>
                                            <option value="<?= esc($cs) ?>" <?= $sel($household['civil_status'] ?? '', $cs) ?>><?= esc($cs) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                <div class="form-group">
                                    <label for="nationality">Nationality</label>
                                    <input id="nationality" name="nationality" type="text" value="<?= esc($household['nationality'] ?? 'Filipino') ?>" placeholder="Filipino" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="religion">Religion</label>
                                    <input id="religion" name="religion" type="text" value="<?= esc($household['religion'] ?? '') ?>" placeholder="e.g. Roman Catholic" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="occupation">Occupation</label>
                                    <input id="occupation" name="occupation" type="text" value="<?= esc($household['occupation'] ?? '') ?>" placeholder="e.g. Farmer" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="monthly_income">Monthly Income (₱)</label>
                                    <input id="monthly_income" name="monthly_income" type="number" step="0.01" min="0" value="<?= esc($household['monthly_income'] ?? '0') ?>" placeholder="0.00" <?= $dis ?>>
                                </div>
                            </div>

                            <div class="census-grid cu-grid-3" style="margin-top:16px;">
                                <div class="form-group">
                                    <label for="contact_number">Contact Number</label>
                                    <input id="contact_number" name="contact_number" type="tel" inputmode="numeric" maxlength="11" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)" value="<?= esc($household['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="educational_attainment">Educational Attainment</label>
                                    <select id="educational_attainment" name="educational_attainment" <?= $dis ?>>
                                        <option value="">— Select —</option>
                                        <?php foreach ([
                                            'No Formal Education',
                                            'Elementary Level',
                                            'Elementary Graduate',
                                            'High School Level',
                                            'High School Graduate',
                                            'College Level',
                                            'College Graduate',
                                            'Vocational / Tech-Voc',
                                            'Post Graduate',
                                        ] as $ed): ?>
                                            <option value="<?= esc($ed) ?>" <?= $sel($household['educational_attainment'] ?? '', $ed) ?>><?= esc($ed) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="philhealth_no">PhilHealth Number</label>
                                    <input id="philhealth_no" name="philhealth_no" type="text" inputmode="numeric" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)" value="<?= esc($household['philhealth_no'] ?? '') ?>" placeholder="00000000000" <?= $dis ?>>
                                </div>
                            </div>

                            <div style="margin-top:16px;">
                                <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">Registered Voter?</label>
                                <div class="cu-radio-row">
                                    <label class="cu-radio"><input type="radio" name="registered_voter" value="1" <?= $chk($household['registered_voter'] ?? '', '1') ?> <?= $dis ?>> <span>Yes</span></label>
                                    <label class="cu-radio"><input type="radio" name="registered_voter" value="0" <?= $chk($household['registered_voter'] ?? '', '0') ?> <?= $dis ?>> <span>No</span></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ───────── 2. Household Information ───────── -->
                    <div class="cu-section">
                        <div class="cu-section-bar"><i class="fas fa-home"></i> Household Information</div>
                        <div class="cu-section-body">
                            <div class="census-grid">
                                <div class="form-group cu-span-all">
                                    <label for="address">Household Address</label>
                                    <textarea id="address" name="address" placeholder="House No./Street/Purok/Sitio, Zone, Barangay Bacolod" <?= $dis ?>><?= esc($household['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                <div class="form-group">
                                    <label for="household_no">Household Number</label>
                                    <input id="household_no" type="text" value="<?= esc($household['household_no'] ?? '') ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="zone">Zone / Purok</label>
                                    <select id="zone" name="zone" <?= $dis ?>>
                                        <option value="">— Select —</option>
                                        <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'] as $z): ?>
                                            <option value="<?= esc($z) ?>" <?= $sel($household['zone'] ?? '', $z) ?>><?= esc($z) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="years_of_residency">Years of Residency</label>
                                    <input id="years_of_residency" name="years_of_residency" type="number" min="0" step="1" value="<?= esc($household['years_of_residency'] ?? '0') ?>" placeholder="0" <?= $dis ?>>
                                </div>
                                <div class="form-group">
                                    <label for="house_ownership">House Ownership</label>
                                    <select id="house_ownership" name="house_ownership" <?= $dis ?>>
                                        <?php foreach (['Owned', 'Rented', 'Shared', 'Informal Settler'] as $ho): ?>
                                            <option value="<?= esc($ho) ?>" <?= $sel($household['house_ownership'] ?? '', $ho) ?>><?= esc($ho) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="census-grid cu-grid-4" style="margin-top:16px;">
                                <div class="form-group">
                                    <label for="num_families">Number of Families Sharing This Household</label>
                                    <input id="num_families" name="num_families" type="number" min="1" max="20" step="1" value="<?= esc($household['num_families'] ?? '1') ?>" placeholder="1" <?= $dis ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ───────── 3. Household Classification ───────── -->
                    <div class="cu-section">
                        <div class="cu-section-bar"><i class="fas fa-tags"></i> Household Classification</div>
                        <div class="cu-section-body">
                            <!--
                                We submit `0` first then let the checkbox submit `1` if checked.
                                This way an unchecked box still comes through to the server as `0`
                                instead of being omitted (which would leave the stored value unchanged).
                            -->
                            <div class="cu-check-row">
                                <input type="hidden" name="is_4ps" value="0">
                                <label class="cu-check"><input type="checkbox" name="is_4ps" value="1" <?= $chk($household['is_4ps'] ?? 0) ?> <?= $dis ?>> <span>4Ps Beneficiary</span></label>

                                <input type="hidden" name="is_senior_citizen" value="0">
                                <label class="cu-check"><input type="checkbox" name="is_senior_citizen" value="1" <?= $chk($household['is_senior_citizen'] ?? 0) ?> <?= $dis ?>> <span>Senior Citizen</span></label>

                                <input type="hidden" name="is_solo_parent" value="0">
                                <label class="cu-check"><input type="checkbox" name="is_solo_parent" value="1" <?= $chk($household['is_solo_parent'] ?? 0) ?> <?= $dis ?>> <span>Solo Parent</span></label>

                                <input type="hidden" name="is_indigenous" value="0">
                                <label class="cu-check"><input type="checkbox" name="is_indigenous" value="1" <?= $chk($household['is_indigenous'] ?? 0) ?> <?= $dis ?>> <span>Indigenous People</span></label>

                                <input type="hidden" name="is_pwd" value="0">
                                <label class="cu-check"><input type="checkbox" id="is_pwd" name="is_pwd" value="1" <?= $chk($household['is_pwd'] ?? 0) ?> <?= $dis ?> onchange="document.getElementById('pwd_type_wrap').style.display = this.checked ? 'block' : 'none';"> <span>PWD Member</span></label>
                            </div>

                            <div id="pwd_type_wrap" style="margin-top:14px; <?= ($household['is_pwd'] ?? 0) ? '' : 'display:none;' ?>">
                                <div class="form-group">
                                    <label for="pwd_type">Specify Disability</label>
                                    <input id="pwd_type" name="pwd_type" type="text" maxlength="120" value="<?= esc($household['pwd_type'] ?? '') ?>" placeholder="e.g. Visual, Hearing, Physical, Intellectual…" <?= $dis ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ───────── 4. Water & Sanitation ───────── -->
                    <div class="cu-section">
                        <div class="cu-section-bar"><i class="fas fa-tint"></i> Access to Safe Water &amp; Sanitation Facility</div>
                        <div class="cu-section-body">

                            <div class="cu-sub-label"><i class="fas fa-water"></i> Access to Safe Water</div>
                            <div class="census-grid cu-grid-2">
                                <div>
                                    <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">1. Basic Safe Water Source</label>
                                    <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                        <label class="cu-radio"><input type="radio" name="water_source_level" value="I" <?= $chk($household['water_source_level'] ?? '', 'I') ?> <?= $dis ?>> <span>Level I — Point Source (e.g. protected well, spring)</span></label>
                                        <label class="cu-radio"><input type="radio" name="water_source_level" value="II" <?= $chk($household['water_source_level'] ?? '', 'II') ?> <?= $dis ?>> <span>Level II — Communal Faucet / Stand Post</span></label>
                                        <label class="cu-radio"><input type="radio" name="water_source_level" value="III" <?= $chk($household['water_source_level'] ?? '', 'III') ?> <?= $dis ?>> <span>Level III — Individual House Connection (piped water)</span></label>
                                        <label class="cu-radio"><input type="radio" name="water_source_level" value="none" <?= $chk($household['water_source_level'] ?? '', 'none') ?> <?= $dis ?>> <span>No Safe Water Source</span></label>
                                    </div>
                                </div>
                                <div>
                                    <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">2. Using Safely Managed Water Service</label>
                                    <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                        <label class="cu-radio"><input type="radio" name="water_safety_managed" value="1" <?= $chk($household['water_safety_managed'] ?? '', '1') ?> <?= $dis ?>> <span>Yes — Water is safely managed</span></label>
                                        <label class="cu-radio"><input type="radio" name="water_safety_managed" value="0" <?= $chk($household['water_safety_managed'] ?? '', '0') ?> <?= $dis ?>> <span>No — Not safely managed</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="cu-sub-label" style="margin-top:18px;border-top:1px solid #f0f2f8;padding-top:16px;"><i class="fas fa-toilet"></i> Sanitation Facility</div>
                            <div class="census-grid cu-grid-2">
                                <div>
                                    <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">1. Basic Sanitation Facility</label>
                                    <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                        <label class="cu-radio"><input type="radio" name="sanitation_basic" value="with" <?= $chk($household['sanitation_basic'] ?? '', 'with') ?> <?= $dis ?>> <span>With Basic Sanitation Facility</span></label>
                                        <label class="cu-radio"><input type="radio" name="sanitation_basic" value="without" <?= $chk($household['sanitation_basic'] ?? '', 'without') ?> <?= $dis ?>> <span>Without Basic Sanitation Facility</span></label>
                                    </div>
                                </div>
                                <div>
                                    <label style="font-size:12px;font-weight:700;color:#4a5068;letter-spacing:.4px;text-transform:uppercase;">2. Using Safely Managed Sanitation Services</label>
                                    <div class="cu-radio-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                        <label class="cu-radio"><input type="radio" name="sanitation_managed" value="with" <?= $chk($household['sanitation_managed'] ?? '', 'with') ?> <?= $dis ?>> <span>With Safely Managed Sanitation</span></label>
                                        <label class="cu-radio"><input type="radio" name="sanitation_managed" value="without" <?= $chk($household['sanitation_managed'] ?? '', 'without') ?> <?= $dis ?>> <span>Without Safely Managed Sanitation</span></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="census-note">
                        <strong>Note:</strong> This household update request is submitted for review by the barangay secretary. Supporting documents may be required for address changes, household separation, or classification (4Ps / Senior / Solo Parent / PWD) requests. Only fields you have changed will be applied to the record after approval.
                    </div>

                    <div class="census-actions">
                        <a href="/<?= esc($portalRole) ?>/dashboard" class="db-btn db-btn--outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                        <button type="submit" class="db-btn db-btn--primary" <?= $canEditPersonal ? '' : 'disabled' ?>><i class="fas fa-paper-plane"></i> Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
