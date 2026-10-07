<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Front Page - BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .bset-page-title h1 { margin: 0 0 6px; font-size: 22px; color: #1a1d2e; }
        .bset-page-title p { margin: 0; color: #6b7280; font-size: 13.5px; }
        .bset-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 14px;
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .bset-card-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f0f2fa;
        }
        .bset-card-header .icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f0f2ff;
            color: #5b6fd6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .bset-card-header h3 { margin: 0; font-size: 15px; font-weight: 600; color: #1a1d2e; }
        .bset-card-header p { margin: 3px 0 0; font-size: 12px; color: #9aa0b4; }
        .bset-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
        }
        .bset-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .span-all { grid-column: 1 / -1; }
        @media (max-width: 860px) {
            .bset-grid, .bset-grid-3 { grid-template-columns: 1fr; }
        }
        .bset-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .45px;
            margin-bottom: 6px;
        }
        .bset-field input,
        .bset-field select,
        .bset-field textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1a1d2e;
            outline: none;
            background: #fff;
            font-family: inherit;
        }
        .bset-field textarea { min-height: 92px; resize: vertical; }
        .bset-field input:focus,
        .bset-field select:focus,
        .bset-field textarea:focus { border-color: #5b6fd6; }
        .bset-hint { display: block; margin-top: 5px; color: #8b93aa; font-size: 11.5px; }
        .bset-checks { display: flex; flex-wrap: wrap; gap: 8px 18px; padding-top: 4px; }
        .bset-checks label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            text-transform: none;
            letter-spacing: 0;
            font-size: 13.5px;
            font-weight: 600;
            color: #1a1d2e;
        }
        .bset-checks input { width: auto; }
        .frontpage-sheet {
            border: 1px solid #dfe6f0;
            border-radius: 14px;
            background: #f8f8f4;
            padding: 18px 20px 12px;
        }
        .frontpage-sheet-inner {
            border: 1px solid #d9dfe9;
            border-radius: 10px;
            padding: 18px 24px 14px;
            background: #f9f7f1;
        }
        .frontpage-sheet-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 6px 0 12px;
        }
        .frontpage-logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            border: 4px solid #e6eaf8;
            font-size: 16px;
            flex-shrink: 0;
        }
        .frontpage-sheet-header-center { text-align: center; line-height: 1.35; color: #1d2448; flex: 1; }
        .frontpage-sheet-header-center p { margin: 0; font-weight: 600; font-size: 12px; }
        .frontpage-sheet-title {
            text-align: center;
            margin: 10px 0 14px;
            color: #1d2448;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .frontpage-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 18px;
            font-size: 12px;
            color: #2f3a5a;
        }
        .preview-block { margin-top: 14px; font-size: 12.5px; color: #1f2a44; line-height: 1.8; }
        .preview-block strong { display: block; margin-bottom: 4px; }
        .frontpage-footer { margin-top: 16px; text-align: right; font-size: 11px; color: #4b5563; font-style: italic; font-weight: 600; }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = session()->get('role') === 'admin' ? 'admin' : 'secretary';
    $active    = 'reports';
    $pageTitle = 'Edit Front Page';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $report = $reportFrontPage ?? [];
    $sel = static function ($value, $candidate): string {
        return strcasecmp((string) $value, (string) $candidate) === 0 ? 'selected' : '';
    };
    $hasOpt = static function (string $stored, string $option): string {
        foreach (preg_split('/[,;\/]+/', $stored) ?: [] as $part) {
            if (strcasecmp(trim($part), $option) === 0) {
                return 'checked';
            }
        }
        return '';
    };
    $locationValue = (string) ($report['report_front_land_location'] ?? 'Tabing-ilog');
    if (strcasecmp($locationValue, 'Tabing-bukid') === 0) {
        $locationValue = 'Tabing-bundok';
    }
    if (strcasecmp($locationValue, 'Populasyon') === 0) {
        $locationValue = 'Poblacion';
    }
    $economicStored = (string) ($report['report_front_economic'] ?? 'Agricultural');
    $barangay = strtoupper((string) ($report['barangay_name'] ?? 'BACOLOD'));
    $province = strtoupper((string) ($report['province'] ?? 'CAMARINES SUR'));
    $municipality = strtoupper((string) ($report['municipality'] ?? 'BATO'));
    $region = strtoupper((string) ($report['region'] ?? 'V (BICOL)'));
    $district = strtoupper((string) ($report['report_front_district'] ?? 'V (RINCONADA)'));
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="bset-page-title" style="margin-bottom:20px;">
                <h1>Report Front Page</h1>
                <p>Edit the official Barangay Profile cover (Annex A). The layout below follows the same DILG sample used in the downloaded report.</p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:18px;">
                    <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--danger" style="margin-bottom:18px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom:20px;">
                <a href="<?= site_url($role . '/reports') ?>" class="db-btn db-btn--outline" style="display:inline-flex;align-items:center;gap:8px;">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>

            <form method="post" action="<?= site_url($role . '/reports/front-page/save') ?>">
                <?= csrf_field() ?>

                <div class="bset-card">
                    <div class="bset-card-header">
                        <div class="icon"><i class="fas fa-university"></i></div>
                        <div>
                            <h3>Cover Header</h3>
                            <p>Department line, office title, and barangay identity printed at the top of Annex A.</p>
                        </div>
                    </div>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="barangay_name">Barangay</label>
                            <input id="barangay_name" name="barangay_name" type="text" value="<?= esc($report['barangay_name'] ?? 'BACOLOD') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="municipality">City / Municipality</label>
                            <input id="municipality" name="municipality" type="text" value="<?= esc($report['municipality'] ?? 'BATO') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="province">Province</label>
                            <input id="province" name="province" type="text" value="<?= esc($report['province'] ?? 'Camarines Sur') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="region">Region</label>
                            <input id="region" name="region" type="text" value="<?= esc($report['region'] ?? 'V (Bicol)') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_district">Congressional District</label>
                            <input id="report_front_district" name="report_front_district" type="text" value="<?= esc($report['report_front_district'] ?? 'V (Rinconada)') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="barangay_profile_title">Profile Title</label>
                            <input id="barangay_profile_title" name="barangay_profile_title" type="text" value="<?= esc($report['barangay_profile_title'] ?? 'BARANGAY PROFILE') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_header">Department Line</label>
                            <input id="report_front_header" name="report_front_header" type="text" value="<?= esc($report['report_front_header'] ?? 'Department of the Interior and Local Government') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_office">Office</label>
                            <input id="report_front_office" name="report_front_office" type="text" value="<?= esc($report['report_front_office'] ?? 'NATIONAL BARANGAY OPERATIONS OFFICE') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_footer">Annex Label</label>
                            <input id="report_front_footer" name="report_front_footer" type="text" value="<?= esc($report['report_front_footer'] ?? 'Annex A') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_footer_note">Form Title</label>
                            <input id="report_front_footer_note" name="report_front_footer_note" type="text" value="<?= esc($report['report_front_footer_note'] ?? 'Barangay Profile DCF No. 1') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_annex_note">Annex Note</label>
                            <input id="report_front_annex_note" name="report_front_annex_note" type="text" value="<?= esc($report['report_front_annex_note'] ?? '(BP DCF No. 1 s. 2020)') ?>">
                        </div>
                    </div>
                </div>

                <div class="bset-card">
                    <div class="bset-card-header">
                        <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
                        <div>
                            <h3>I. Physical Information</h3>
                            <p>Land area, category, classification, location, and economic source — same choices as the official sample form.</p>
                        </div>
                    </div>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="report_front_area">Total Land Area (hectares)</label>
                            <input id="report_front_area" name="report_front_area" type="text" value="<?= esc($report['report_front_area'] ?? '405.1240') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_category">Barangay Category</label>
                            <select id="report_front_category" name="report_front_category">
                                <?php foreach (['Urban', 'Rural'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $sel($report['report_front_category'] ?? 'Urban', $opt) ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="bset-field">
                            <label for="report_front_classification">Land Classification</label>
                            <select id="report_front_classification" name="report_front_classification">
                                <?php foreach (['Upland', 'Lowland', 'Coastal', 'Landlocked'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $sel($report['report_front_classification'] ?? 'Lowland', $opt) ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="bset-field">
                            <label for="report_front_land_location">Barangay Location</label>
                            <select id="report_front_land_location" name="report_front_land_location">
                                <?php foreach (['Tabing-ilog', 'Tabing-dagat', 'Tabing-bundok', 'Poblacion'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $sel($locationValue, $opt) ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="bset-field span-all">
                            <label>Major Economic Source</label>
                            <div class="bset-checks">
                                <?php foreach (['Agricultural', 'Fishing', 'Commercial', 'Industrial'] as $opt): ?>
                                    <label>
                                        <input type="checkbox" name="report_front_economic[]" value="<?= $opt ?>" <?= $hasOpt($economicStored, $opt) ?>>
                                        <?= $opt ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <span class="bset-hint">Check all that apply. The official form can mark more than one source.</span>
                        </div>
                    </div>
                </div>

                <div class="bset-card">
                    <div class="bset-card-header">
                        <div class="icon"><i class="fas fa-balance-scale"></i></div>
                        <div>
                            <h3>II. Political Information</h3>
                            <p>Creation details and appointed officials. Treasurer and SK councilors are entered here; other names come from active accounts.</p>
                        </div>
                    </div>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="report_front_legal_basis">Legal Basis of Creation</label>
                            <input id="report_front_legal_basis" name="report_front_legal_basis" type="text" value="<?= esc($report['report_front_legal_basis'] ?? '') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_ratification_date">Date of Plebiscite / Ratification</label>
                            <input id="report_front_ratification_date" name="report_front_ratification_date" type="text" value="<?= esc($report['report_front_ratification_date'] ?? '') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_precincts">Number of Precincts</label>
                            <input id="report_front_precincts" name="report_front_precincts" type="text" value="<?= esc($report['report_front_precincts'] ?? '2') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_barangay_treasurer">Barangay Treasurer</label>
                            <input id="report_front_barangay_treasurer" name="report_front_barangay_treasurer" type="text" value="<?= esc($report['report_front_barangay_treasurer'] ?? '') ?>" placeholder="Full name">
                        </div>
                        <div class="bset-field span-all">
                            <label for="report_front_officials">Appointed Barangay and SK Officials</label>
                            <textarea id="report_front_officials" rows="7" readonly><?= esc($report['report_front_officials'] ?? '') ?></textarea>
                            <span class="bset-hint">Fetched automatically from active appointed accounts.</span>
                        </div>
                        <div class="bset-field">
                            <label for="report_front_sk_secretary">SK Secretary</label>
                            <input id="report_front_sk_secretary" name="report_front_sk_secretary" type="text" value="<?= esc($report['report_front_sk_secretary'] ?? '') ?>" placeholder="Full name">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_sk_treasurer">SK Treasurer</label>
                            <input id="report_front_sk_treasurer" name="report_front_sk_treasurer" type="text" value="<?= esc($report['report_front_sk_treasurer'] ?? '') ?>" placeholder="Full name">
                        </div>
                        <div class="bset-field span-all">
                            <label for="report_front_sk_councilors">SK Councilors</label>
                            <textarea id="report_front_sk_councilors" name="report_front_sk_councilors" rows="4" placeholder="One name per line"><?= esc($report['report_front_sk_councilors'] ?? '') ?></textarea>
                            <span class="bset-hint">Enter one councilor name per line.</span>
                        </div>
                        <div class="bset-field span-all">
                            <label for="report_front_workers">Other Appointed Officials and Workers</label>
                            <textarea id="report_front_workers" name="report_front_workers" rows="7"><?= esc($report['report_front_workers'] ?? '') ?></textarea>
                            <span class="bset-hint">One line each, for example: Lupon Member - 10</span>
                        </div>
                    </div>
                </div>

                <div class="bset-card">
                    <div class="bset-card-header">
                        <div class="icon"><i class="fas fa-coins"></i></div>
                        <div>
                            <h3>III. Fiscal Information</h3>
                            <p>External and local sources for the year under review. These print on the downloaded report, not on Document Fees.</p>
                        </div>
                    </div>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="report_front_fiscal_year">Fiscal Year</label>
                            <input id="report_front_fiscal_year" name="report_front_fiscal_year" type="text" value="<?= esc($report['report_front_fiscal_year'] ?? date('Y')) ?>">
                        </div>
                    </div>
                    <p style="margin:16px 0 10px;font-size:12px;font-weight:700;color:#1d2448;">A. External Sources</p>
                    <div class="bset-grid">
                        <?php foreach ([
                            'report_front_fiscal_ira' => 'Internal Revenue Allotment',
                            'report_front_fiscal_donation_grant' => 'Donation / Grant',
                            'report_front_fiscal_national_wealth' => 'Share from National Wealth',
                            'report_front_fiscal_external_subsidy' => 'Others (External) Subsidy',
                        ] as $field => $label): ?>
                            <div class="bset-field">
                                <label for="<?= esc($field) ?>"><?= esc($label) ?></label>
                                <input id="<?= esc($field) ?>" name="<?= esc($field) ?>" type="text" value="<?= esc($report[$field] ?? '') ?>" placeholder="0.00">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin:18px 0 10px;font-size:12px;font-weight:700;color:#1d2448;">B. Local Sources</p>
                    <div class="bset-grid">
                        <?php foreach ([
                            'report_front_fiscal_rpt_share' => 'RPT Share',
                            'report_front_fiscal_fees_charges' => 'Fees and Charges',
                            'report_front_fiscal_local_others' => 'Others (Local)',
                        ] as $field => $label): ?>
                            <div class="bset-field">
                                <label for="<?= esc($field) ?>"><?= esc($label) ?></label>
                                <input id="<?= esc($field) ?>" name="<?= esc($field) ?>" type="text" value="<?= esc($report[$field] ?? '') ?>" placeholder="0.00">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin:18px 0 10px;font-size:12px;font-weight:700;color:#1d2448;">General Fund / SK Fund</p>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="report_front_fiscal_general_fund">General Fund</label>
                            <input id="report_front_fiscal_general_fund" name="report_front_fiscal_general_fund" type="text" value="<?= esc($report['report_front_fiscal_general_fund'] ?? '') ?>" placeholder="0.00">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_fiscal_sk_fund">SK Fund</label>
                            <input id="report_front_fiscal_sk_fund" name="report_front_fiscal_sk_fund" type="text" value="<?= esc($report['report_front_fiscal_sk_fund'] ?? '') ?>" placeholder="0.00">
                        </div>
                        <div class="bset-field span-all">
                            <label for="report_front_fiscal_definitions">Field Definitions</label>
                            <textarea id="report_front_fiscal_definitions" name="report_front_fiscal_definitions" rows="3"><?= esc($report['report_front_fiscal_definitions'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="bset-card">
                    <div class="bset-card-header">
                        <div class="icon"><i class="fas fa-broadcast-tower"></i></div>
                        <div>
                            <h3>d. Others</h3>
                            <p>Power, water, transportation, and communication printed on page 3 of the official form.</p>
                        </div>
                    </div>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="report_front_power">Largest Power Supply Distributor</label>
                            <input id="report_front_power" name="report_front_power" type="text" value="<?= esc($report['report_front_power'] ?? 'CASURECO 3') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_water_system">Major Water Supply Level of Households</label>
                            <input id="report_front_water_system" name="report_front_water_system" type="text" value="<?= esc($report['report_front_water_system'] ?? 'Barangay Water System') ?>">
                        </div>
                        <div class="bset-field span-all">
                            <label for="report_front_transport">Existing Means of Transportation</label>
                            <input id="report_front_transport" name="report_front_transport" type="text" value="<?= esc($report['report_front_transport'] ?? 'Habal-Habal, Tricycle, Private Vehicle, Van, Motorboat') ?>">
                        </div>
                        <div class="bset-field span-all">
                            <label for="report_front_communication">Existing Means of Communication</label>
                            <input id="report_front_communication" name="report_front_communication" type="text" value="<?= esc($report['report_front_communication'] ?? 'Mobile Phone, Internet and Television') ?>">
                        </div>
                    </div>
                </div>

                <div class="bset-card">
                    <div class="bset-card-header">
                        <div class="icon"><i class="fas fa-award"></i></div>
                        <div>
                            <h3>VI. Awards / Recognition</h3>
                            <p>Titles received during the year under review, plus the DILG field officer who validates the form.</p>
                        </div>
                    </div>
                    <div class="bset-grid">
                        <div class="bset-field">
                            <label for="report_front_award_national">National Level</label>
                            <input id="report_front_award_national" name="report_front_award_national" type="text" value="<?= esc($report['report_front_award_national'] ?? 'N/A') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_award_regional">Regional Level</label>
                            <input id="report_front_award_regional" name="report_front_award_regional" type="text" value="<?= esc($report['report_front_award_regional'] ?? 'N/A') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_award_local">Local Level</label>
                            <input id="report_front_award_local" name="report_front_award_local" type="text" value="<?= esc($report['report_front_award_local'] ?? 'N/A') ?>">
                        </div>
                        <div class="bset-field">
                            <label for="report_front_dilg_officer">DILG Field Officer</label>
                            <input id="report_front_dilg_officer" name="report_front_dilg_officer" type="text" value="<?= esc($report['report_front_dilg_officer'] ?? 'IVY S. RAMIREZ') ?>">
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:18px;">
                    <button type="submit" class="db-btn db-btn--primary">
                        <i class="fas fa-save"></i> Save Front Page
                    </button>
                    <a href="<?= site_url($role . '/reports') ?>" class="db-btn db-btn--outline">Cancel</a>
                </div>
            </form>

            <div class="bset-card">
                <div class="bset-card-header">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <div>
                        <h3>Cover Preview</h3>
                        <p>This is how the first page of the downloaded report will look.</p>
                    </div>
                </div>
                <div class="frontpage-sheet">
                    <div class="frontpage-sheet-inner">
                        <div class="frontpage-sheet-header">
                            <div class="frontpage-logo" style="background:linear-gradient(135deg,#d13b2d,#f0b44c);">DILG</div>
                            <div class="frontpage-sheet-header-center">
                                <p>Republic of the Philippines</p>
                                <p><?= esc($report['report_front_header'] ?? 'Department of the Interior and Local Government') ?></p>
                                <p><strong><?= esc(strtoupper($report['report_front_office'] ?? 'NATIONAL BARANGAY OPERATIONS OFFICE')) ?></strong></p>
                            </div>
                            <div class="frontpage-logo" style="background:linear-gradient(135deg,#1e3f8a,#5cb7d8);">B</div>
                        </div>
                        <div class="frontpage-sheet-title"><?= esc(strtoupper($report['barangay_profile_title'] ?? 'BARANGAY PROFILE')) ?></div>
                        <div class="frontpage-meta">
                            <div><strong>Barangay</strong> : <?= esc($barangay) ?></div>
                            <div><strong>City/Municipality</strong> : <?= esc($municipality) ?></div>
                            <div><strong>Province</strong> : <?= esc($province) ?></div>
                            <div><strong>Region</strong> : <?= esc($region) ?></div>
                            <div style="grid-column:1 / -1;"><strong>Congressional District</strong> : <?= esc($district) ?></div>
                        </div>
                        <div class="preview-block">
                            <strong>I. PHYSICAL INFORMATION</strong>
                            Total Land Area (in hectares) : <?= esc($report['report_front_area'] ?? '405.1240') ?><br>
                            Barangay Category : <?= esc($report['report_front_category'] ?? 'Urban') ?><br>
                            Land Classification : <?= esc($report['report_front_classification'] ?? 'Lowland') ?><br>
                            Barangay Location : <?= esc($report['report_front_land_location'] ?? 'Tabing-ilog') ?><br>
                            Major Economic Source : <?= esc($report['report_front_economic'] ?? 'Agricultural') ?>
                        </div>
                        <div class="frontpage-footer">
                            <?= esc($report['report_front_footer'] ?? 'Annex A') ?>
                            <div style="margin-top:4px;font-size:12px;color:#5a6278;font-style:normal;">
                                <?= esc($report['report_front_annex_note'] ?? '(BP DCF No. 1 s. 2020)') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
