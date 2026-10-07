<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Barangay Profile — CY <?= date('Y') ?></title>
    <style>
        @page {
            size: legal portrait;
            margin: 0.5in 0.6in 0.35in 0.6in;
        }

        * {
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            background: #fff;
        }

        @media screen {
            body {
                padding: 0.45in 0.55in 0.4in 0.55in;
            }
        }

        .page {
            page-break-after: always;
        }

        .page.last {
            page-break-after: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .letterhead {
            position: relative;
            width: 100%;
            min-height: 78px;
            text-align: center;
        }

        .letterhead .annex {
            position: absolute;
            top: 0;
            right: 0;
            width: 180px;
            text-align: right;
            font-size: 10px;
            line-height: 1.2;
            font-weight: 700;
        }

        .letterhead .annex small {
            display: block;
            font-size: 9px;
            font-weight: 600;
        }

        .letterhead .seals {
            text-align: center;
            padding-top: 2px;
        }

        .seal {
            width: 72px;
            height: 72px;
            margin: 0 7px;
        }

        .agency {
            text-align: center;
            line-height: 1.3;
            margin: 4px 0 0;
        }

        .agency .country {
            font-size: 11px;
        }

        .agency .dept {
            font-size: 11px;
            font-weight: 700;
        }

        .agency .office {
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: .3px;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .6px;
            margin: 8px 0 10px;
            text-transform: uppercase;
        }

        .id-grid td {
            font-size: 10.5px;
            vertical-align: bottom;
            padding: 1px 0 3px;
        }

        .id-grid .id-l {
            width: 1%;
            white-space: nowrap;
            padding-right: 10px;
        }

        .id-grid .id-c {
            width: 10px;
            font-weight: 700;
            padding-right: 8px;
        }

        .id-grid .id-v {
            font-weight: 700;
            text-decoration: underline;
        }

        .id-grid .id-gap {
            width: 7%;
        }

        .meta td {
            padding: 1px 4px 2px 0;
            font-size: 10.5px;
            vertical-align: top;
        }

        .meta .k {
            font-weight: 700;
            white-space: nowrap;
        }

        .section-head {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 11px 0 5px;
            letter-spacing: .2px;
        }

        .sub-head {
            font-size: 10.5px;
            font-weight: 800;
            margin: 6px 0 4px;
        }

        .form-row {
            margin: 2px 0 3px;
            line-height: 1.45;
        }

        .lbl {
            display: inline-block;
            min-width: 210px;
        }

        .chk {
            font-size: 11px;
            margin: 0 2px 0 7px;
        }

        .line {
            display: inline-block;
            min-width: 120px;
            border-bottom: 1px solid #111;
            padding: 0 4px;
            font-weight: 700;
        }

        .kv {
            width: 100%;
        }

        .kv td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 10.5px;
        }

        .kv .role {
            width: 1%;
            white-space: nowrap;
            font-weight: 700;
            padding-right: 6px;
        }

        .workers {
            margin: 4px 0 0 12px;
        }

        .workers div {
            line-height: 1.4;
        }

        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }

        .grid-2 td + td {
            padding-right: 0;
            padding-left: 12px;
        }

        .money td {
            padding: 1px 0;
            font-size: 10.5px;
        }

        .money .name {
            width: 62%;
        }

        .money .amt {
            width: 38%;
            text-align: right;
            font-weight: 700;
            border-bottom: 1px solid #111;
        }

        .formula {
            margin-top: 8px;
            font-size: 10.5px;
            line-height: 1.45;
        }

        .formula strong {
            font-weight: 800;
        }

        .defs {
            margin-top: 8px;
            font-size: 9px;
            line-height: 1.35;
            text-align: justify;
            font-style: italic;
        }

        .demo-line {
            margin: 2px 0 3px;
            font-size: 10.5px;
            line-height: 1.45;
        }

        .demo-line .v {
            display: inline-block;
            min-width: 70px;
            font-weight: 800;
            border-bottom: 1px solid #111;
            padding: 0 4px;
            text-align: left;
        }

        .sheet {
            width: 100%;
            border: 1px solid #111;
            margin-top: 6px;
        }

        .sheet th,
        .sheet td {
            border: 1px solid #111;
            padding: 4px 6px;
            font-size: 10px;
        }

        .sheet th {
            text-align: center;
            font-weight: 800;
            background: #fff;
        }

        .sheet td.num {
            text-align: center;
            width: 70px;
        }

        .sheet td.tot {
            text-align: center;
            font-weight: 800;
            width: 80px;
        }

        .sheet td.label {
            text-align: left;
        }

        .sign-table {
            margin-top: 28px;
        }

        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }

        .sign-line {
            border-bottom: 1px solid #111;
            height: 28px;
            margin-bottom: 4px;
        }

        .sign-name {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .sign-role {
            font-size: 9px;
        }

        .validated {
            margin-top: 28px;
            text-align: center;
        }

        .validated .title {
            font-weight: 800;
            margin-bottom: 22px;
        }
    </style>
</head>

<body>
<?php
$report = $reportFrontPage ?? [];
$assetUri = static function (string $filename): string {
    $path = FCPATH . $filename;
    if (! is_file($path)) {
        return '';
    }

    $mime = str_ends_with(strtolower($filename), '.jpg') || str_ends_with(strtolower($filename), '.jpeg')
        ? 'image/jpeg'
        : 'image/png';

    return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
};
$dilgLogo = $assetUri('dilg.png');
$barangayLogo = $assetUri('bacolod.png');

$cleanIdentity = static function (string $value, string $prefix): string {
    $value = strtoupper(trim($value));
    if ($prefix !== '' && str_starts_with($value, $prefix)) {
        return trim(substr($value, strlen($prefix)));
    }

    return $value;
};
$countryText = (string) ($report['country'] ?? 'Republic of the Philippines');
$barangay = $cleanIdentity((string) ($report['barangay_name'] ?? 'BACOLOD'), 'BARANGAY ');
$province = $cleanIdentity((string) ($report['province'] ?? 'CAMARINES SUR'), 'PROVINCE OF ');
$municipality = $cleanIdentity((string) ($report['municipality'] ?? 'BATO'), 'MUNICIPALITY OF ');
if (str_starts_with($municipality, 'CITY OF ')) {
    $municipality = trim(substr($municipality, 8));
}
$region = $cleanIdentity((string) ($report['region'] ?? 'V (BICOL)'), 'REGION ');
$district = strtoupper((string) ($report['report_front_district'] ?? 'V (RINCONADA)'));
$profileTitle = strtoupper((string) ($report['barangay_profile_title'] ?? 'BARANGAY PROFILE'));
$headerText = (string) ($report['report_front_header'] ?? 'Department of the Interior and Local Government');
$officeText = strtoupper((string) ($report['report_front_office'] ?? 'NATIONAL BARANGAY OPERATIONS OFFICE'));
$area = (string) ($report['report_front_area'] ?? '405.1240');
$category = (string) ($report['report_front_category'] ?? 'Urban');
$classification = (string) ($report['report_front_classification'] ?? 'Lowland');
$landLocation = (string) ($report['report_front_land_location'] ?? 'Tabing-ilog');
$economic = (string) ($report['report_front_economic'] ?? 'Agricultural');
$legalBasis = trim((string) ($report['report_front_legal_basis'] ?? ''));
$ratificationDate = trim((string) ($report['report_front_ratification_date'] ?? ''));
$precincts = (string) ($report['report_front_precincts'] ?? '2');
$fiscalYear = (string) ($report['report_front_fiscal_year'] ?? date('Y'));
$footerText = (string) ($report['report_front_footer'] ?? 'Annex A');
$footerNote = (string) ($report['report_front_footer_note'] ?? 'Barangay Profile DCF No. 1');
$annexNote = str_replace('BP DC No.', 'BP DCF No.', (string) ($report['report_front_annex_note'] ?? '(BP DCF No. 1 s. 2020)'));

$hasChoice = static function (string $stored, string $option): bool {
    $parts = preg_split('/[,;\/]+/', $stored) ?: [];
    foreach ($parts as $part) {
        if (strcasecmp(trim($part), trim($option)) === 0) {
            return true;
        }
    }
    if (strcasecmp(trim($stored), 'Tabing-bukid') === 0 && strcasecmp($option, 'Tabing-bundok') === 0) {
        return true;
    }
    if (strcasecmp(trim($stored), 'Populasyon') === 0 && strcasecmp($option, 'Poblacion') === 0) {
        return true;
    }

    return false;
};
$chk = static function (string $stored, string $option) use ($hasChoice): string {
    return $hasChoice($stored, $option) ? '☑' : '☐';
};

$money = static function (string $value): string {
    $clean = preg_replace('/[^\d.]/', '', $value) ?? '';
    if ($clean === '') {
        return $value !== '' ? $value : '';
    }

    return number_format((float) $clean, 2);
};

$officialLines = preg_split('/\r\n|\r|\n/', (string) ($report['report_front_officials'] ?? '')) ?: [];
$officialLines = array_values(array_filter(array_map('trim', $officialLines)));
$barangayOfficials = [];
$skOfficials = [];
foreach ($officialLines as $official) {
    [$position, $name] = array_pad(array_map('trim', explode(':', $official, 2)), 2, '');
    $row = ['role' => $position, 'name' => $name];
    if (stripos($position, 'SK') !== false) {
        $skOfficials[] = $row;
        if (stripos($position, 'Chair') !== false) {
            $barangayOfficials[] = $row;
        }
    } else {
        $barangayOfficials[] = $row;
    }
}

$workers = preg_split('/\r\n|\r|\n/', (string) ($report['report_front_workers'] ?? '')) ?: [];
$workers = array_values(array_filter(array_map('trim', $workers)));

$viewData = get_defined_vars();
$totalPop         = (int) ($viewData['totalPop'] ?? 0);
$totalHouseholds  = (int) ($viewData['totalHouseholds'] ?? 0);
$ageBrackets      = is_array($viewData['ageBrackets'] ?? null) ? $viewData['ageBrackets'] : [];
$sectorRows       = is_array($viewData['sectorRows'] ?? null) ? $viewData['sectorRows'] : [];
$waterRows        = is_array($viewData['waterRows'] ?? null) ? $viewData['waterRows'] : [];
$sanitationRows   = is_array($viewData['sanitationRows'] ?? null) ? $viewData['sanitationRows'] : [];
$registeredVoters = (int) ($viewData['registeredVoters'] ?? 0);
$totalFamilies    = (int) ($viewData['totalFamilies'] ?? 0);

$sections = $sections ?? [];
$showAll  = empty($sections);
if (! function_exists('showSec')) {
    function showSec(string $key, array $sections, bool $showAll): bool
    {
        return $showAll || isset($sections[$key]);
    }
}

$settingsModel = new \App\Models\BarangaySettingsModel();
$secretaryName = strtoupper($settingsModel->getLiveSecretaryName());
$captainName = strtoupper($settingsModel->getLiveCaptainName());
$treasurerName = strtoupper(trim((string) ($report['report_front_barangay_treasurer'] ?? '')));
$dilgOfficer = strtoupper(trim((string) ($report['report_front_dilg_officer'] ?? 'IVY S. RAMIREZ')));

$ira = $money((string) ($report['report_front_fiscal_ira'] ?? ''));
$donation = $money((string) ($report['report_front_fiscal_donation_grant'] ?? ''));
$wealth = $money((string) ($report['report_front_fiscal_national_wealth'] ?? ''));
$subsidy = $money((string) ($report['report_front_fiscal_external_subsidy'] ?? ''));
$rpt = $money((string) ($report['report_front_fiscal_rpt_share'] ?? ''));
$fees = $money((string) ($report['report_front_fiscal_fees_charges'] ?? ''));
$localOthers = $money((string) ($report['report_front_fiscal_local_others'] ?? ''));
$generalFund = trim((string) ($report['report_front_fiscal_general_fund'] ?? ''));
$skFund = trim((string) ($report['report_front_fiscal_sk_fund'] ?? ''));
$fiscalDefinitions = trim((string) ($report['report_front_fiscal_definitions'] ?? 'Indicate the Total Income for the period under review. External Source (Internal Revenue Allotment (IRA) Others (Share from National Wealth, subsidy etc. Local Source – (real property tax, fees and charges, share from E-VAT and others)'));
?>

<div class="page">
    <div class="letterhead">
        <div class="annex">
            <?= esc($footerText) ?><br>
            <?= esc($footerNote) ?><br>
            <small><?= esc($annexNote) ?></small>
        </div>
        <div class="seals">
            <?php if ($dilgLogo !== ''): ?>
                <img class="seal" src="<?= esc($dilgLogo) ?>" alt="DILG">
            <?php endif; ?>
            <?php if ($barangayLogo !== ''): ?>
                <img class="seal" src="<?= esc($barangayLogo) ?>" alt="Barangay seal">
            <?php endif; ?>
        </div>
    </div>
    <div class="agency">
        <div class="country"><?= esc($countryText) ?></div>
        <div class="dept"><?= esc($headerText) ?></div>
        <div class="office"><?= esc($officeText) ?></div>
    </div>
    <div class="doc-title"><?= esc($profileTitle) ?></div>

    <table class="id-grid">
        <tr>
            <td class="id-l">Barangay</td>
            <td class="id-c">:</td>
            <td class="id-v"><?= esc($barangay) ?></td>
            <td class="id-gap"></td>
            <td class="id-l">City/Municipality</td>
            <td class="id-c">:</td>
            <td class="id-v"><?= esc($municipality) ?></td>
        </tr>
        <tr>
            <td class="id-l">Province</td>
            <td class="id-c">:</td>
            <td class="id-v"><?= esc($province) ?></td>
            <td class="id-gap"></td>
            <td class="id-l">Region</td>
            <td class="id-c">:</td>
            <td class="id-v"><?= esc($region) ?></td>
        </tr>
        <tr>
            <td class="id-l">Congressional District</td>
            <td class="id-c">:</td>
            <td class="id-v" colspan="5"><?= esc($district) ?></td>
        </tr>
    </table>

    <div class="section-head">I. PHYSICAL INFORMATION</div>
    <div class="form-row"><span class="lbl">Total Land Area (in hectares) :</span> <span class="line"><?= esc($area) ?></span></div>
    <div class="form-row">
        <span class="lbl">Barangay Category (Urban, Rural) :</span>
        <span class="chk"><?= $chk($category, 'Urban') ?></span> Urban
        <span class="chk"><?= $chk($category, 'Rural') ?></span> Rural
    </div>
    <div class="form-row">
        <span class="lbl">Land Classification :</span>
        <span class="chk"><?= $chk($classification, 'Upland') ?></span> Upland
        <span class="chk"><?= $chk($classification, 'Lowland') ?></span> Lowland
        <span class="chk"><?= $chk($classification, 'Coastal') ?></span> Coastal
        <span class="chk"><?= $chk($classification, 'Landlocked') ?></span> Landlocked
    </div>
    <div class="form-row">
        <span class="lbl">Barangay Location :</span>
        <span class="chk"><?= $chk($landLocation, 'Tabing-ilog') ?></span> Tabing-ilog
        <span class="chk"><?= $chk($landLocation, 'Tabing-dagat') ?></span> Tabing-Dagat
        <span class="chk"><?= $chk($landLocation, 'Tabing-bundok') ?></span> Tabing-bundok
        <span class="chk"><?= $chk($landLocation, 'Poblacion') ?></span> Poblacion
    </div>
    <div class="form-row">
        <span class="lbl">Major Economic Source :</span>
        <span class="chk"><?= $chk($economic, 'Agricultural') ?></span> Agricultural
        <span class="chk"><?= $chk($economic, 'Fishing') ?></span> Fishing
        <span class="chk"><?= $chk($economic, 'Commercial') ?></span> Commercial
        <span class="chk"><?= $chk($economic, 'Industrial') ?></span> Industrial
    </div>

    <div class="section-head">II. POLITICAL INFORMATION</div>
    <table class="meta">
        <tr>
            <td style="width:50%;"><span class="k">Legal Basis of Creation</span> : <span class="line"><?= esc($legalBasis) ?></span></td>
            <td><span class="k">Date of Plebiscite/Ratification</span> : <span class="line"><?= esc($ratificationDate) ?></span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="k">Number of Precincts</span> : <span class="line"><?= esc($precincts) ?></span></td>
        </tr>
    </table>
    <div class="form-row" style="margin-top:6px;">
        <strong>Name of Barangay and SK Officials (2023-2025): <?= count($officialLines) ?></strong>
        <em> (to include Barangay and SK Secretaries and Treasurers and IPMRs)</em>
    </div>
    <table class="grid-2">
        <tr>
            <td>
                <table class="kv">
                    <?php foreach ($barangayOfficials as $official): ?>
                        <tr>
                            <td class="role"><?= esc($official['role']) ?> :</td>
                            <td><?= esc($official['name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
            <td>
                <table class="kv">
                    <?php foreach ($skOfficials as $official): ?>
                        <tr>
                            <td class="role"><?= esc($official['role']) ?> :</td>
                            <td><?= esc($official['name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        </tr>
    </table>
    <div class="sub-head">No. of Other Appointed Barangay Officials and Workers:</div>
    <div class="workers">
        <?php foreach ($workers as $worker): ?>
            <div>- <?= esc($worker) ?></div>
        <?php endforeach; ?>
    </div>
</div>

<div class="page">
    <div class="section-head">III. FISCAL INFORMATION – CY <?= esc($fiscalYear) ?></div>
    <table class="grid-2">
        <tr>
            <td>
                <div class="sub-head">A. External Sources- CY <?= esc($fiscalYear) ?></div>
                <table class="money">
                    <tr><td class="name">Internal Revenue Allotment</td><td class="amt"><?= esc($ira) ?></td></tr>
                    <tr><td class="name">Donation/Grant</td><td class="amt"><?= esc($donation) ?></td></tr>
                    <tr><td class="name">Share from national wealth</td><td class="amt"><?= esc($wealth) ?></td></tr>
                    <tr><td class="name">Others (External) Subsidy</td><td class="amt"><?= esc($subsidy) ?></td></tr>
                </table>
            </td>
            <td>
                <div class="sub-head">B. Local Sources</div>
                <table class="money">
                    <tr><td class="name">RPT Share</td><td class="amt"><?= esc($rpt) ?></td></tr>
                    <tr><td class="name">Fees and Charges</td><td class="amt"><?= esc($fees) ?></td></tr>
                    <tr><td class="name">Others (Local)</td><td class="amt"><?= esc($localOthers) ?></td></tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="formula">
        General Fund &nbsp;: <em>(External Sources + Local Sources)</em>- <span class="line"><?= esc($money($generalFund)) ?></span><br>
        SK Fund &nbsp;: <em>(10% of the General Fund)</em> <span class="line"><?= esc($money($skFund)) ?></span>
    </div>
    <div class="defs"><strong><em>Field Definitions:</em></strong> <?= esc($fiscalDefinitions) ?></div>

    <?php if (showSec('demographic', $sections, $showAll)): ?>
        <div class="section-head">IV. DEMOGRAPHIC INFORMATION - CY <?= esc($fiscalYear) ?></div>
        <div class="demo-line">A. No. of Registered Voters: <span class="v"><?= number_format($registeredVoters) ?></span></div>
        <div class="demo-line">B. No. of Population : <span class="v"><?= number_format($totalPop) ?></span></div>
        <div class="demo-line">
            C. With RBIs? <span class="chk">☐</span> Yes &nbsp; <span class="chk">☑</span> No
        </div>
        <div class="demo-line" style="padding-left:18px;font-size:9.5px;">
            if yes, No. of Inhabitants (RBI): &nbsp; 1<sup>st</sup> Sem.: <span class="line" style="min-width:70px;"></span>
            &nbsp; 2<sup>nd</sup> Sem.: <span class="line" style="min-width:70px;"></span>
        </div>
        <div class="demo-line">D. No. of Households: <span class="v"><?= number_format($totalHouseholds) ?></span></div>
        <div class="demo-line">E. No. of Families: <span class="v"><?= number_format($totalFamilies) ?></span></div>
    <?php endif; ?>

    <?php if (showSec('age_bracket', $sections, $showAll)): ?>
        <div class="sub-head">F. Population by Age Bracket</div>
        <table class="sheet">
            <thead>
                <tr>
                    <th rowspan="2" style="width:46%;">AGE</th>
                    <th colspan="2">S E X</th>
                    <th rowspan="2" style="width:16%;">TOTAL</th>
                </tr>
                <tr>
                    <th>Male</th>
                    <th>Female</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ageBrackets as $i => $row): ?>
                    <tr>
                        <td class="label"><?= ($i + 1) . '. ' . esc($row['label']) ?></td>
                        <td class="num"><?= number_format($row['male']) ?></td>
                        <td class="num"><?= number_format($row['female']) ?></td>
                        <td class="tot"><?= number_format($row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (showSec('sector', $sections, $showAll)): ?>
        <div class="sub-head">G. Population by Sector</div>
        <table class="sheet">
            <thead>
                <tr>
                    <th rowspan="2" style="width:46%;">SECTOR</th>
                    <th colspan="2">SEX</th>
                    <th rowspan="2" style="width:16%;">TOTAL</th>
                </tr>
                <tr>
                    <th>Male</th>
                    <th>Female</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sectorRows as $row): ?>
                    <tr>
                        <td class="label"><?= esc($row['label']) ?></td>
                        <td class="num"><?= number_format($row['male']) ?></td>
                        <td class="num"><?= number_format($row['female']) ?></td>
                        <td class="tot"><?= number_format($row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="page last">
    <div class="sub-head">d. Others</div>
    <table class="kv" style="margin-bottom:14px;">
        <tr>
            <td class="role">Largest Power Supply Distributor</td>
            <td>: <?= esc((string) ($report['report_front_power'] ?? 'CASURECO 3')) ?></td>
        </tr>
        <tr>
            <td class="role">Major Water Supply Level of Households</td>
            <td>: <?= esc((string) ($report['report_front_water_system'] ?? 'Barangay Water System')) ?></td>
        </tr>
        <tr>
            <td class="role">Existing Means of Transportation</td>
            <td>: <?= esc((string) ($report['report_front_transport'] ?? 'Habal-Habal, Tricycle, Private Vehicle, Van, Motorboat')) ?></td>
        </tr>
        <tr>
            <td class="role">Existing Means of Communication</td>
            <td>: <?= esc((string) ($report['report_front_communication'] ?? 'Mobile Phone, Internet and Television')) ?></td>
        </tr>
    </table>

    <div class="section-head">VI. AWARDS/RECOGNITION RECEIVED BY THE BARANGAY/BARANGAY OFFICIALS DURING YEAR UNDER REVIEW</div>
    <div style="margin:4px 0 8px;">• Specify the title of the Award/Recognition received during the year under review</div>
    <div class="form-row">National Level: <span class="line"><?= esc((string) ($report['report_front_award_national'] ?? 'N/A')) ?></span></div>
    <div class="form-row">Regional Level: <span class="line"><?= esc((string) ($report['report_front_award_regional'] ?? 'N/A')) ?></span></div>
    <div class="form-row">Local Level: <span class="line"><?= esc((string) ($report['report_front_award_local'] ?? 'N/A')) ?></span></div>

    <div class="sub-head" style="margin-top:28px;">Prepared By:</div>
    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-line"></div>
                <div class="sign-name"><?= esc($secretaryName) ?></div>
                <div class="sign-role">Barangay Secretary</div>
            </td>
            <td>
                <div class="sign-line"></div>
                <div class="sign-name"><?= esc($treasurerName) ?></div>
                <div class="sign-role">Barangay Treasurer</div>
            </td>
            <td>
                <div class="sign-line"></div>
                <div class="sign-name"><?= esc($captainName) ?></div>
                <div class="sign-role">Punong Barangay</div>
            </td>
        </tr>
    </table>

    <div class="validated">
        <div class="title">Validated By:</div>
        <div class="sign-line" style="width:240px;margin:0 auto 6px;"></div>
        <div class="sign-name"><?= esc($dilgOfficer) ?></div>
        <div class="sign-role">DILG Field Officer</div>
    </div>
</div>

</body>
</html>
