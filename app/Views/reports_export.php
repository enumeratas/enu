<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Barangay Report — CY <?= date('Y') ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 portrait;
            margin: 0.55in 0.55in 0.45in 0.55in;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            margin: 5px;
        }

        .report-export {
            width: 100%;
        }

        .cover-page {
            width: 100%;
            page-break-after: always;
            margin-bottom: 18px;
        }

        .cover-box {
            width: 100%;
            min-height: 760px;
            background: #f7f5ef;
            border: 1px solid #dfe6f1;
            padding: 18px 18px 12px;
            page-break-inside: avoid;
        }

        .cover-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .cover-seal {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 60px;
            border: 4px solid #edf1fb;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #d93d2f, #f0b74e);
            box-shadow: 0 2px 10px rgba(29, 36, 72, 0.12);
        }

        .cover-seal.alt {
            background: linear-gradient(135deg, #174b9f, #56b0d8);
        }

        .cover-center {
            text-align: center;
            line-height: 1.4;
            color: #1d2448;
        }

        .cover-center p {
            margin: 0;
            font-size: 11px;
            font-weight: 600;
        }

        .cover-center .office {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .cover-title {
            text-align: center;
            margin: 10px 0 12px;
            font-size: 18px;
            font-weight: 800;
            color: #1d2448;
            letter-spacing: .4px;
        }

        .cover-meta {
            display: block;
            font-size: 12px;
            color: #2a3247;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .cover-meta>div {
            display: inline-block;
            width: 49%;
            vertical-align: top;
            padding-right: 8px;
        }

        .cover-meta>div:last-child {
            width: 100%;
        }

        .cover-meta strong {
            color: #1d2448;
        }

        .cover-physical {
            padding-top: 12px;
            font-size: 12px;
            color: #1d2448;
            line-height: 1.8;
        }

        .cover-physical .head {
            font-weight: 800;
            margin-bottom: 4px;
        }

        .form-row {
            margin: 2px 0;
            line-height: 1.45;
        }

        .form-label {
            display: inline-block;
            min-width: 190px;
        }

        .form-option {
            display: inline-block;
            margin-right: 10px;
            white-space: nowrap;
        }

        .form-option::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 3px;
            border: 1px solid #555;
            vertical-align: -1px;
        }

        .form-option.selected::before {
            content: 'x';
            font-size: 8px;
            line-height: 7px;
            text-align: center;
        }

        .official-heading {
            margin-top: 8px;
            font-weight: 700;
            line-height: 1.35;
        }

        .worker-list {
            margin-top: 8px;
            margin-left: 10px;
            line-height: 1.45;
        }

        .worker-list div::before {
            content: '- ';
        }

        .cover-physical .row {
            display: table;
            width: 100%;
        }

        .cover-physical .row span,
        .cover-physical .row strong {
            display: table-cell;
        }

        .cover-physical .row span:first-child {
            min-width: 190px;
        }

        .political-grid {
            display: block;
            margin-top: 8px;
        }

        .political-col {
            display: inline-block;
            width: 49%;
            vertical-align: top;
            min-width: 0;
        }

        .political-official {
            display: table;
            width: 100%;
            margin: 2px 0;
            font-size: 11px;
            line-height: 1.5;
        }

        .political-official .role {
            display: table-cell;
            width: 138px;
            font-weight: 600;
            color: #1d2448;
        }

        .political-official>span:last-child {
            display: table-cell;
        }

        .cover-footer {
            text-align: right;
            font-size: 11px;
            color: #4b5563;
            font-style: italic;
            font-weight: 700;
        }

        .cover-footer small {
            display: block;
            font-size: 11px;
            font-style: normal;
            color: #5b647a;
        }

        .lydo-seal {
            width: 58px;
            height: 58px;
            margin: 0 2px;
        }

        .rpt-header {
            text-align: center;
            position: relative;
            padding-top: 20px;
        }

        .rpt-header .cover-footer {
            position: absolute;
            top: 0;
            right: 0;
        }

        .rpt-header h1 {
            font-size: 15px;
            font-weight: 700;
            color: #1d2448;
            letter-spacing: .5px;
            margin-bottom: 2px;
        }

        .rpt-header h4 {
            font-size: 10px;
            font-weight: 400;
            line-height: 1.25;
            color: #222;
        }

        .rpt-header h4.department {
            font-weight: 700;
        }

        .rpt-header p {
            font-size: 11px;
            color: #444;
            margin: 1px 0;
        }

        .summary-grid {
            display: table;
            width: 100%;
            border-spacing: 8px 0;
            margin-left: -8px;
            margin-bottom: 16px;
        }

        .summary-grid>* {
            display: table-cell;
            width: 33.333%;
        }

        .summary-cell {
            border: 1px solid #d0d5e8;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .summary-cell .num {
            font-size: 18px;
            font-weight: 700;
            color: #1d2448;
            line-height: 1.1;
        }

        .summary-cell .label {
            font-size: 10px;
            color: #555;
            margin-top: 1px;
        }

        .section {
            background: #fff;
            border: 1px solid #dfe6f1;
            border-radius: 12px;
            overflow: hidden;
            margin: 0 auto 16px;
            width: 96%;
            max-width: 980px;
            page-break-inside: avoid;
            box-shadow: 0 1px 4px rgba(22, 32, 74, 0.05);
        }

        .section-header {
            background: #f7f9fd;
            color: #1f2a44;
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
            border-bottom: 1px solid #e3e8f1;
        }

        .demo-body {
            padding: 8px 12px;
        }

        .demo-row {
            display: table;
            width: 100%;
            padding: 6px 0;
            border-bottom: 1px solid #f0f2f8;
            font-size: 11px;
        }

        .demo-row:last-child {
            border-bottom: none;
        }

        .demo-lbl {
            display: table-cell;
            font-weight: 700;
            color: #1d2448;
            width: 30px;
        }

        .demo-txt {
            display: table-cell;
            color: #333;
        }

        .demo-val {
            display: table-cell;
            font-weight: 700;
            background: #eef0fb;
            padding: 1px 8px;
            border-radius: 20px;
            width: 60px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
            background: #fff;
        }

        thead tr {
            background: #f7f9fd;
            border-bottom: 2px solid #2d6cdf;
        }

        thead th {
            padding: 9px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #1f2a44;
            text-transform: none;
            letter-spacing: 0;
            border-bottom: none;
            text-align: center;
            white-space: nowrap;
        }

        thead th:first-child {
            text-align: left;
        }

        .th-group {
            background: #f1f5fe;
        }

        .th-group th {
            font-size: 10px;
            color: #61708d;
            padding: 7px 12px;
            border-bottom: 1px solid #e3e8f1;
            text-align: center;
        }

        tbody tr {
            border-bottom: 1px solid #edf1f7;
            background: #fff;
        }

        tbody tr:nth-child(even) {
            background: #fbfcff;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 9px 12px;
            text-align: center;
            color: #2a3247;
            border-right: 1px solid #edf1f7;
            vertical-align: middle;
        }

        td:last-child {
            border-right: none;
        }

        td:first-child {
            text-align: left;
            font-weight: 500;
            color: #2d3250;
        }

        td.tot,
        th.tot {
            font-weight: 700;
            color: #1d2448;
            background: #f7f9fd;
        }

        tfoot tr {
            background: #f7f9fd;
            border-top: 1px solid #dfe6f1;
        }

        tfoot td {
            color: #1d2448;
            font-weight: 700;
            padding: 10px 12px;
            text-align: center;
            background: #f7f9fd;
        }

        tfoot td:first-child {
            text-align: left;
        }

        .two-col {
            display: table;
            width: 100%;
        }

        .two-col>div {
            display: table-cell;
            width: 50%;
        }

        .two-col>div:last-child {
            border-left: 1px solid #f0f2f8;
        }

        .sub-header {
            padding: 7px 10px 5px;
            font-size: 10px;
            font-weight: 700;
            color: #5b6fd6;
            text-transform: uppercase;
            letter-spacing: .4px;
            background: #eef0fb;
            border-bottom: 1px solid #d8dce8;
        }

        span.n {
            display: inline-block;
            width: 18px;
            color: #999;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <?php
    $report = $reportFrontPage ?? [];
    $assetUri = static function (string $filename): string {
        $path = FCPATH . $filename;
        if (! is_file($path)) {
            return '/' . $filename;
        }

        return 'data:image/png;base64,' . base64_encode((string) file_get_contents($path));
    };
    $dilgLogo = $assetUri('dilg.png');
    $barangayLogo = $assetUri('bacolod.png');
    $countryText = (string) ($report['country'] ?? 'Republic of the Philippines');
    $barangay = strtoupper((string) ($report['barangay_name'] ?? 'BACOLOD'));
    $province = strtoupper((string) ($report['province'] ?? 'CAMARINES SUR'));
    $municipality = strtoupper((string) ($report['municipality'] ?? 'BATO'));
    $region = strtoupper((string) ($report['region'] ?? 'V (BICOL)'));
    $district = strtoupper((string) ($report['report_front_district'] ?? 'V (RINCONADA)'));
    $profileTitle = strtoupper((string) ($report['barangay_profile_title'] ?? 'BARANGAY PROFILE'));
    $headerText = (string) ($report['report_front_header'] ?? 'Department of the Interior and Local Government');
    $departmentText = (string) ($report['report_front_department'] ?? 'Department of the Interior and Local Government');
    $officeText = strtoupper((string) ($report['report_front_office'] ?? 'NATIONAL BARANGAY OPERATIONS OFFICE'));
    $area = (string) ($report['report_front_area'] ?? '405.1240');
    $category = (string) ($report['report_front_category'] ?? 'Urban');
    $classification = (string) ($report['report_front_classification'] ?? 'Lowland');
    $landLocation = (string) ($report['report_front_land_location'] ?? 'Tabing-ilog');
    $economic = (string) ($report['report_front_economic'] ?? 'Agricultural');
    $isSelected = static fn(string $value, string $expected): string => strtolower(trim($value)) === strtolower(trim($expected)) ? 'selected' : '';
    $legalBasis = (string) ($report['report_front_legal_basis'] ?? '');
    $ratificationDate = (string) ($report['report_front_ratification_date'] ?? '');
    $precincts = (string) ($report['report_front_precincts'] ?? '2');
    $officialLines = preg_split('/\r\n|\r|\n/', (string) ($report['report_front_officials'] ?? '')) ?: [];
    $officialLines = array_values(array_filter(array_map('trim', $officialLines)));
    $officialColumns = $officialLines === [] ? [] : array_chunk($officialLines, (int) ceil(count($officialLines) / 2));
    $fiscalYear = (string) ($report['report_front_fiscal_year'] ?? date('Y'));
    $fiscalIra = (string) ($report['report_front_fiscal_ira'] ?? '');
    $fiscalDonationGrant = (string) ($report['report_front_fiscal_donation_grant'] ?? '');
    $fiscalNationalWealth = (string) ($report['report_front_fiscal_national_wealth'] ?? '');
    $fiscalExternalSubsidy = (string) ($report['report_front_fiscal_external_subsidy'] ?? '');
    $fiscalGeneralFund = (string) ($report['report_front_fiscal_general_fund'] ?? '');
    $fiscalSkFund = (string) ($report['report_front_fiscal_sk_fund'] ?? '');
    $fiscalRptShare = (string) ($report['report_front_fiscal_rpt_share'] ?? '');
    $fiscalFeesCharges = (string) ($report['report_front_fiscal_fees_charges'] ?? '');
    $fiscalLocalOthers = (string) ($report['report_front_fiscal_local_others'] ?? '');
    $fiscalDefinitions = (string) ($report['report_front_fiscal_definitions'] ?? '');

    $viewData = get_defined_vars();
    $totalPop         = (int) ($viewData['totalPop'] ?? 0);
    $totalMale        = (int) ($viewData['totalMale'] ?? 0);
    $totalFemale      = (int) ($viewData['totalFemale'] ?? 0);
    $totalHouseholds  = (int) ($viewData['totalHouseholds'] ?? 0);
    $totalClearances  = (int) ($viewData['totalClearances'] ?? 0);
    $avgHHSize        = $viewData['avgHHSize'] ?? 0;
    $ageBrackets      = is_array($viewData['ageBrackets'] ?? null) ? $viewData['ageBrackets'] : [];
    $sectorRows       = is_array($viewData['sectorRows'] ?? null) ? $viewData['sectorRows'] : [];
    $waterRows        = is_array($viewData['waterRows'] ?? null) ? $viewData['waterRows'] : [];
    $sanitationRows   = is_array($viewData['sanitationRows'] ?? null) ? $viewData['sanitationRows'] : [];
    $eduRows          = is_array($viewData['eduRows'] ?? null) ? $viewData['eduRows'] : [];
    $registeredVoters = (int) ($viewData['registeredVoters'] ?? 0);
    $totalFamilies    = (int) ($viewData['totalFamilies'] ?? 0);

    $populationFront = (string) ($report['report_front_population'] ?? number_format($totalPop));
    $householdsFront = (string) ($report['report_front_households'] ?? number_format($totalHouseholds));
    $familiesFront = (string) ($report['report_front_families'] ?? number_format($totalFamilies));
    $voterFront = (string) ($report['report_front_registered_voters'] ?? number_format($registeredVoters));
    $footerText = (string) ($report['report_front_footer'] ?? 'Annex A');
    $annexNote = (string) ($report['report_front_annex_note'] ?? '(BP DC No. 1 s. 2020)');

    $sections = $sections ?? [];
    $showAll  = empty($sections);
    function showSec(string $key, array $sections, bool $showAll): bool
    {
        return $showAll || isset($sections[$key]);
    }
    ?>

    <div class="report-export">
        <div class="rpt-header">
            <div class="cover-footer">
                <?= esc($footerText) ?>
                <small><?= esc($annexNote) ?></small>
            </div>
            <img class="lydo-seal" src="<?= esc($dilgLogo) ?>" alt="Department of the Interior and Local Government">
            <img class="lydo-seal" src="<?= esc($barangayLogo) ?>" alt="Barangay Bato seal">
            <h4><?= esc($countryText) ?></h4>
            <h4 class="department"><?= esc($departmentText) ?></h4>
            <h1><?= esc($officeText) ?></h1>
        </div>

        <div class="cover-title"><?= esc($profileTitle) ?></div>

        <div class="cover-meta">
            <div><strong>Barangay</strong> : <?= esc($barangay) ?></div>
            <div><strong>City/Municipality</strong> : <?= esc($municipality) ?></div>
            <div><strong>Province</strong> : <?= esc($province) ?></div>
            <div><strong>Region</strong> : <?= esc($region) ?></div>
            <div><strong>Congressional District</strong> : <?= esc($district) ?></div>
        </div>

        <div class="cover-physical">
            <div class="head">I. PHYSICAL INFORMATION</div>
            <div class="form-row"><span class="form-label">Total Land Area (in hectares) :</span><strong><?= esc($area) ?></strong></div>
            <div class="form-row"><span class="form-label">Barangay Category (Urban, Rural) :</span><span class="form-option <?= $isSelected($category, 'Urban') ?>">Urban</span><span class="form-option <?= $isSelected($category, 'Rural') ?>">Rural</span></div>
            <div class="form-row"><span class="form-label">Land Classification :</span><span class="form-option <?= $isSelected($classification, 'Upland') ?>">Upland</span><span class="form-option <?= $isSelected($classification, 'Lowland') ?>">Lowland</span><span class="form-option <?= $isSelected($classification, 'Coastal') ?>">Coastal</span><span class="form-option <?= $isSelected($classification, 'Landlocked') ?>">Landlocked</span></div>
            <div class="form-row"><span class="form-label">Barangay Location :</span><span class="form-option <?= $isSelected($landLocation, 'Tabing-ilog') ?>">Tabing-ilog</span><span class="form-option <?= $isSelected($landLocation, 'Tabing-dagat') ?>">Tabing-dagat</span><span class="form-option <?= $isSelected($landLocation, 'Tabing-bukid') ?>">Tabing-bukid</span><span class="form-option <?= $isSelected($landLocation, 'Populasyon') ?>">Populasyon</span></div>
            <div class="form-row"><span class="form-label">Major Economic Source :</span><span class="form-option <?= $isSelected($economic, 'Agricultural') ?>">Agricultural</span><span class="form-option <?= $isSelected($economic, 'Fishing') ?>">Fishing</span><span class="form-option <?= $isSelected($economic, 'Commercial') ?>">Commercial</span><span class="form-option <?= $isSelected($economic, 'Industrial') ?>">Industrial</span></div>
        </div>

        <div class="cover-physical">
            <div class="head">II. POLITICAL INFORMATION</div>
            <div class="row"><span>Legal Basis of Creation</span><strong><?= esc($legalBasis ?: 'Barangay Profile') ?></strong></div>
            <div class="row"><span>Date of Public/Ratification</span><strong><?= esc($ratificationDate ?: '________________') ?></strong></div>
            <div class="row"><span>Number of Precincts</span><strong><?= esc($precincts) ?></strong></div>

            <div class="official-heading">
                Name of Barangay and SK Officials (2023-2025): <?= count($officialLines) ?> (to include Barangay and SK Secretaries and Treasurers and IPMRs)
            </div>

            <div class="political-grid">
                <?php foreach ($officialColumns as $column): ?>
                    <div class="political-col">
                        <?php foreach ($column as $official): ?>
                            <?php [$position, $name] = array_pad(array_map('trim', explode(':', $official, 2)), 2, ''); ?>
                            <div class="political-official"><span class="role"><?= esc($position) ?>:</span><span><?= esc($name) ?></span></div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="official-heading">
                No. of Other Appointed Barangay Officials and Workers:
            </div>
            <div class="worker-list">
                <div><i>Lupon Member - 10</i></div>
                <div><i>Barangay Tanod - 14</i></div>
                <div><i>Barangay Health Worker - 7</i></div>
                <div><i>Barangay Nutrition Scholar - 2</i></div>
                <div><i>Day Care Worker - 1</i></div>
                <div><i>VAW Desk Officer - 1</i></div>
                <div><i>BADAC Cluster Leaders - 2</i></div>
            </div>
        </div>

        <div class="cover-physical">
            <div class="head">III. FISCAL INFORMATION</div>
            <div class="head">A. EXTERNAL SOURCES - CY <?= esc($fiscalYear) ?></div>
            <div class="row"><span>Internal Revenue Allotment</span><strong><?= esc($fiscalIra) ?></strong></div>
            <div class="row"><span>Donation / Grant</span><strong><?= esc($fiscalDonationGrant) ?></strong></div>
            <div class="row"><span>Share from National Wealth</span><strong><?= esc($fiscalNationalWealth) ?></strong></div>
            <div class="row"><span>Others (External) Subsidy</span><strong><?= esc($fiscalExternalSubsidy) ?></strong></div>

            <div class="head" style="margin-top:10px;">B. LOCAL SOURCES - CY <?= esc($fiscalYear) ?></div>
            <div class="row"><span>General Fund</span><strong><?= esc($fiscalGeneralFund) ?></strong></div>
            <div class="row"><span>SK Fund</span><strong><?= esc($fiscalSkFund) ?></strong></div>
            <div class="row"><span>RPT Share</span><strong><?= esc($fiscalRptShare) ?></strong></div>
            <div class="row"><span>Fees and Charges</span><strong><?= esc($fiscalFeesCharges) ?></strong></div>
            <div class="row"><span>Others (Local)</span><strong><?= esc($fiscalLocalOthers) ?></strong></div>

            <div style="margin-top: 10px; font-weight:700; font-size:11px; color:#1d2448;">
                <strong><i>Field Definitions:</i></strong> <?= esc($fiscalDefinitions) ?>
            </div>
        </div>
    </div>

    <div class="cover-physical">
        <div class="head">IV. Demographic Information</div>
        <?php if (showSec('demographic', $sections, $showAll)): ?>
            <div class="demo-body">
                <div class="row"><span>A. No. of Registered Voters:</span><?= esc(number_format($registeredVoters)) ?></div>
                <div class="row"><span>B. No. of Population:</span><?= esc(number_format($totalPop)) ?></div>
                <div class="row"><span>C. With RBIs? &nbsp; ☐ Yes &nbsp; ☑ No</span></div>
                <div class="row"><span>D. No. of Households:</span><?= esc(number_format($totalHouseholds)) ?></div>
                <div class="row"><span>E. No. of Families:</span><?= esc(number_format($totalFamilies)) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (showSec('age_bracket', $sections, $showAll)): ?>
        <div class="section">
            <div class="section-header">F. Population by Age Bracket</div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align:left;vertical-align:middle;">AGE GROUP</th>
                        <th colspan="2">S E X</th>
                        <th rowspan="2" class="tot" style="vertical-align:middle;">TOTAL</th>
                    </tr>
                    <tr class="th-group">
                        <th>Male</th>
                        <th>Female</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ageBrackets as $i => $row): ?>
                        <tr>
                            <td><span class="n"><?= $i + 1 ?>.</span><?= esc($row['label']) ?></td>
                            <td><?= number_format($row['male']) ?></td>
                            <td><?= number_format($row['female']) ?></td>
                            <td class="tot"><?= number_format($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTAL</td>
                        <td><?= number_format(array_sum(array_column($ageBrackets, 'male'))) ?></td>
                        <td><?= number_format(array_sum(array_column($ageBrackets, 'female'))) ?></td>
                        <td><?= number_format(array_sum(array_column($ageBrackets, 'total'))) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <?php if (showSec('sector', $sections, $showAll)): ?>
        <div class="section">
            <div class="section-header">G. Population by Sector</div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align:left;vertical-align:middle;">SECTOR</th>
                        <th colspan="2">S E X</th>
                        <th rowspan="2" class="tot" style="vertical-align:middle;">TOTAL</th>
                    </tr>
                    <tr class="th-group">
                        <th>Male</th>
                        <th>Female</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sectorRows as $row): ?>
                        <tr>
                            <td><?= esc($row['label']) ?></td>
                            <td><?= number_format($row['male']) ?></td>
                            <td><?= number_format($row['female']) ?></td>
                            <td class="tot"><?= number_format($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    </div>

</body>

</html>