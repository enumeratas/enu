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
        .frontpage-wrap {
            width: 100%;
            max-width: none;
        }

        .frontpage-preview {
            background: #fff;
            border: 1px solid #e6ebf5;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(19, 26, 52, 0.05);
            padding: 24px 28px 18px;
        }

        .frontpage-preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .frontpage-preview-header h2 {
            margin: 0;
            font-size: 18px;
            color: #1a1d2e;
        }

        .frontpage-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #f0f2ff;
            color: #4d5fd6;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .frontpage-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .db-form-group {
            margin: 0;
        }

        .db-form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .db-form-group input,
        .db-form-group textarea {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13.5px;
            color: #1a1d2e;
            background: #fff;
            resize: vertical;
        }

        .db-form-group textarea {
            min-height: 90px;
        }

        .frontpage-sheet {
            border: 1px solid #dfe6f0;
            border-radius: 14px;
            background: #f8f8f4;
            padding: 18px 20px 12px;
            margin-top: 18px;
        }

        .frontpage-sheet-inner {
            border: 1px solid #d9dfe9;
            border-radius: 10px;
            padding: 18px 24px 10px;
            background: #f9f7f1;
            min-height: 100px;
        }

        .frontpage-sheet-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin: 6px 0 12px;
        }

        .frontpage-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #d13a2d, #e4b33d);
            border: 4px solid #e6eaf8;
            box-shadow: 0 2px 12px rgba(29, 36, 72, 0.12);
            font-size: 18px;
            letter-spacing: 1px;
        }

        .frontpage-sheet-header-center {
            text-align: center;
            line-height: 1.35;
            color: #1d2448;
        }

        .frontpage-sheet-header-center p {
            margin: 0;
            font-weight: 600;
            font-size: 12px;
        }

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
            gap: 8px 18px;
            font-size: 12px;
            color: #2f3a5a;
            margin-top: 8px;
        }

        .frontpage-meta strong {
            color: #1a1d2e;
        }

        .frontpage-footer {
            margin-top: 18px;
            text-align: right;
            font-size: 11px;
            color: #4b5563;
            font-style: italic;
            font-weight: 600;
        }

        @media (max-width: 700px) {
            .frontpage-preview {
                padding: 16px 14px;
            }

            .frontpage-sheet-inner {
                padding: 14px 12px;
            }

            .frontpage-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = 'secretary';
    $active    = 'reports';
    $pageTitle = 'Edit Front Page';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $report = $reportFrontPage ?? [];
    $barangay = strtoupper((string) ($report['barangay_name'] ?? 'BACOLOD'));
    $province = strtoupper((string) ($report['province'] ?? 'CAMARINES SUR'));
    $municipality = strtoupper((string) ($report['municipality'] ?? 'BATO'));
    $region = strtoupper((string) ($report['region'] ?? 'V (BICOL)'));
    $district = strtoupper((string) ($report['report_front_district'] ?? 'V (RINCONADA)'));
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="db-page-title">
                <h1>Report Front Page</h1>
                <p class="db-page-subtitle">Edit the official barangay profile cover page, matching the format shown in the sample document.</p>
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
                <a href="<?= site_url('secretary/reports') ?>" class="db-btn db-btn--outline" style="display:inline-flex;align-items:center;gap:8px;">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>

            <div class="frontpage-wrap">
                <div class="frontpage-preview">
                    <div class="frontpage-preview-header">
                        <h2>Barangay Profile Cover</h2>
                        <span class="frontpage-badge"><i class="fas fa-file-alt"></i> Front Page</span>
                    </div>

                    <form method="post" action="<?= site_url('secretary/reports/front-page/save') ?>">
                        <?= csrf_field() ?>

                        <div class="frontpage-form-grid">
                            <div class="db-form-group">
                                <label for="barangay_name">Barangay</label>
                                <input id="barangay_name" name="barangay_name" type="text" value="<?= esc($report['barangay_name'] ?? 'BACOLOD') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="province">Province</label>
                                <input id="province" name="province" type="text" value="<?= esc($report['province'] ?? 'Camarines Sur') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="municipality">City/Municipality</label>
                                <input id="municipality" name="municipality" type="text" value="<?= esc($report['municipality'] ?? 'BATO') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="region">Region</label>
                                <input id="region" name="region" type="text" value="<?= esc($report['region'] ?? 'V (Bicol)') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_district">Congressional District</label>
                                <input id="report_front_district" name="report_front_district" type="text" value="<?= esc($report['report_front_district'] ?? 'V (Rinconada)') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_header">Department Line</label>
                                <input id="report_front_header" name="report_front_header" type="text" value="<?= esc($report['report_front_header'] ?? 'Department of the Interior and Local Government') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_office">Office</label>
                                <input id="report_front_office" name="report_front_office" type="text" value="<?= esc($report['report_front_office'] ?? 'NATIONAL BARANGAY OPERATIONS OFFICE') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="barangay_profile_title">Profile Title</label>
                                <input id="barangay_profile_title" name="barangay_profile_title" type="text" value="<?= esc($report['barangay_profile_title'] ?? 'BARANGAY PROFILE') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_footer">Annex Label</label>
                                <input id="report_front_footer" name="report_front_footer" type="text" value="<?= esc($report['report_front_footer'] ?? 'Annex A') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_annex_note">Annex Note</label>
                                <input id="report_front_annex_note" name="report_front_annex_note" type="text" value="<?= esc($report['report_front_annex_note'] ?? '(BP DC No. 1 s. 2020)') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_legal_basis">Legal Basis of Creation</label>
                                <input id="report_front_legal_basis" name="report_front_legal_basis" type="text" value="<?= esc($report['report_front_legal_basis'] ?? '') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_ratification_date">Date of Public/Ratification</label>
                                <input id="report_front_ratification_date" name="report_front_ratification_date" type="text" value="<?= esc($report['report_front_ratification_date'] ?? '') ?>">
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_precincts">Number of Precincts</label>
                                <input id="report_front_precincts" name="report_front_precincts" type="text" value="<?= esc($report['report_front_precincts'] ?? '2') ?>">
                            </div>
                            <div class="db-form-group" style="grid-column:1/-1;">
                                <label for="report_front_officials">Appointed Barangay and SK Officials</label>
                                <textarea id="report_front_officials" rows="9" readonly><?= esc($report['report_front_officials'] ?? '') ?></textarea>
                                <small style="display:block;margin-top:5px;color:#8b93aa;">Names are fetched automatically from active appointed accounts.</small>
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_barangay_treasurer">Barangay Treasurer</label>
                                <input id="report_front_barangay_treasurer" name="report_front_barangay_treasurer" type="text" value="<?= esc($report['report_front_barangay_treasurer'] ?? '') ?>" placeholder="Full name">
                                <small style="display:block;margin-top:5px;color:#8b93aa;">Managed manually by the secretary/admin.</small>
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_sk_councilors">SK Councilors</label>
                                <textarea id="report_front_sk_councilors" name="report_front_sk_councilors" rows="4" placeholder="One name per line"><?= esc($report['report_front_sk_councilors'] ?? '') ?></textarea>
                                <small style="display:block;margin-top:5px;color:#8b93aa;">Enter one councilor name per line. Managed manually by the secretary/admin.</small>
                            </div>
                            <div class="db-form-group">
                                <label for="report_front_fiscal_year">Fiscal Year</label>
                                <input id="report_front_fiscal_year" name="report_front_fiscal_year" type="text" value="<?= esc($report['report_front_fiscal_year'] ?? '2025') ?>" placeholder="2025">
                            </div>
                            <div class="db-form-group" style="grid-column:1/-1;"></div>
                            <?php foreach (
                                [
                                    'report_front_fiscal_ira' => 'Internal Revenue Allotment',
                                    'report_front_fiscal_donation_grant' => 'Donation / Grant',
                                    'report_front_fiscal_national_wealth' => 'Share from National Wealth',
                                    'report_front_fiscal_external_subsidy' => 'Others (External) Subsidy',
                                    'report_front_fiscal_general_fund' => 'General Fund',
                                    'report_front_fiscal_sk_fund' => 'SK Fund',
                                    'report_front_fiscal_rpt_share' => 'RPT Share',
                                    'report_front_fiscal_fees_charges' => 'Fees and Charges',
                                    'report_front_fiscal_local_others' => 'Others (Local)',
                                ] as $field => $label
                            ): ?>
                                <div class="db-form-group">
                                    <label for="<?= esc($field) ?>"><?= esc($label) ?></label>
                                    <input id="<?= esc($field) ?>" name="<?= esc($field) ?>" type="text" value="<?= esc($report[$field] ?? '') ?>" placeholder="0.00">
                                </div>
                            <?php endforeach; ?>
                            <div class="db-form-group" style="grid-column:1/-1;">
                                <label for="report_front_fiscal_definitions">Fiscal Field Definitions</label>
                                <textarea id="report_front_fiscal_definitions" name="report_front_fiscal_definitions" rows="3"><?= esc($report['report_front_fiscal_definitions'] ?? '') ?></textarea>
                            </div>
                            <div class="db-form-group" style="grid-column:1/-1;">
                                <label for="report_front_profile_text">Profile Summary</label>
                                <textarea id="report_front_profile_text" name="report_front_profile_text"><?= esc($report['report_front_profile_text'] ?? "Barangay : BACOLOD
Province : CAMARINES SUR
City/Municipality : BATO
Region : V (BICOL)
Congressional District : V (RINCONADA)") ?></textarea>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                            <button type="submit" class="db-btn db-btn--primary">
                                <i class="fas fa-save"></i> Save Front Page
                            </button>
                            <a href="<?= site_url('secretary/reports') ?>" class="db-btn db-btn--outline">Cancel</a>
                        </div>
                    </form>

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

                            <div class="frontpage-sheet-title">
                                <?= esc(strtoupper($report['barangay_profile_title'] ?? 'BARANGAY PROFILE')) ?>
                            </div>

                            <div class="frontpage-meta">
                                <div><strong>Barangay</strong> : <?= esc($barangay) ?></div>
                                <div><strong>City/Municipality</strong> : <?= esc($municipality) ?></div>
                                <div><strong>Province</strong> : <?= esc($province) ?></div>
                                <div><strong>Region</strong> : <?= esc($region) ?></div>
                                <div style="grid-column:1 / -1;"><strong>Congressional District</strong> : <?= esc($district) ?></div>
                            </div>

                            <div style="margin-top:18px; font-size:13px; color:#1f2a44; line-height:1.9;">
                                <div style="font-weight:700; margin-bottom:6px;"><strong>I. PHYSICAL INFORMATION</strong></div>
                                <div>Total Land Area (in hectares) : <strong><?= esc($report['report_front_area'] ?? '405.1240') ?></strong></div>
                                <div>Barangay Category (Urban, Rural): <strong><?= esc($report['report_front_category'] ?? 'Urban') ?></strong></div>
                                <div>Land Classification: <strong><?= esc($report['report_front_classification'] ?? 'Lowland') ?></strong></div>
                                <div>Barangay Location: <strong><?= esc($report['report_front_land_location'] ?? 'Tabing-ilog') ?></strong></div>
                                <div>Major Economic Source: <strong><?= esc($report['report_front_economic'] ?? 'Agricultural') ?></strong></div>
                            </div>

                            <div class="frontpage-footer">
                                <?= esc($report['report_front_footer'] ?? 'Annex A') ?>
                                <div style="margin-top:4px; font-size:12px; color:#5a6278;">
                                    <?= esc($report['report_front_annex_note'] ?? '(BP DC No. 1 s. 2020)') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>