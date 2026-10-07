<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate to File Action — BL-<?= str_pad((string) $report['id'], 4, '0', STR_PAD_LEFT) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 16mm 16mm 16mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #111;
            background: <?= ($mode ?? 'screen') === 'pdf' ? '#fff' : '#e8ecf4' ?>;
        }

        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1d2448;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 999;
        }

        .toolbar span {
            color: rgba(255, 255, 255, .75);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            flex: 1;
        }

        .tb-btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 12.5px;
            font-weight: 700;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: Arial, Helvetica, sans-serif;
            text-decoration: none;
            color: #fff;
        }

        .tb-btn--print { background: #e67e22; }
        .tb-btn--download { background: #16c79a; }
        .tb-btn--back { background: rgba(255, 255, 255, .16); }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: <?= ($mode ?? 'screen') === 'pdf' ? '0 auto' : '78px auto 32px' ?>;
            background: #fff;
            padding: 16mm 16mm 14mm;
            box-shadow: <?= ($mode ?? 'screen') === 'pdf' ? 'none' : '0 8px 28px rgba(20,28,56,.12)' ?>;
            position: relative;
        }

        .header-table,
        .meta-table,
        .facts-table,
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }

        .seal {
            width: 78px;
            height: 78px;
            object-fit: contain;
        }

        .header-center {
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }

        .republic {
            font-size: 11pt;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .place-line {
            font-size: 11pt;
        }

        .brgy-name {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin: 2pt 0;
        }

        .office-bar {
            display: inline-block;
            margin-top: 6pt;
            padding: 3pt 14pt;
            border-top: 1.5px solid #111;
            border-bottom: 1.5px solid #111;
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: .6px;
        }

        .rule {
            border: none;
            border-top: 3px double #111;
            margin: 10pt 0 12pt;
        }

        .doc-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .doc-form {
            text-align: center;
            font-size: 10pt;
            font-style: italic;
            margin: 4pt 0 14pt;
        }

        .meta-table td {
            font-size: 11pt;
            padding: 2pt 0;
        }

        .body {
            font-size: 12pt;
            line-height: 1.85;
            text-align: justify;
        }

        .body p {
            margin-bottom: 10pt;
        }

        .indent {
            text-indent: 36pt;
        }

        .facts-wrap {
            border: 1.4px solid #111;
            padding: 8pt 10pt;
            margin: 12pt 0 14pt;
        }

        .facts-table td {
            font-size: 11pt;
            padding: 3pt 4pt;
            vertical-align: top;
        }

        .facts-table td:first-child {
            width: 130pt;
            font-weight: bold;
        }

        .note {
            font-size: 10.5pt;
            font-style: italic;
            border: 1px solid #444;
            padding: 8pt 10pt;
            margin: 8pt 0 16pt;
            line-height: 1.55;
        }

        .sig-table {
            margin-top: 28pt;
        }

        .sig-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 12pt;
        }

        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11.5pt;
            min-height: 16pt;
        }

        .sig-line {
            border-top: 1px solid #111;
            margin: 28pt 18pt 4pt;
        }

        .sig-title {
            font-size: 10pt;
            font-style: italic;
        }

        .footer {
            margin-top: 18pt;
            border-top: 1px solid #bbb;
            padding-top: 6pt;
            font-size: 9pt;
            text-align: center;
            color: #444;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                margin: 0;
                width: auto;
                min-height: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
<?php
$r = $report;
$mode = $mode ?? 'screen';
$isPdf = $mode === 'pdf';
$caseNo = 'BL-' . str_pad((string) $r['id'], 4, '0', STR_PAD_LEFT);
$today = date('F d, Y');
$day = date('j');
$monthYear = date('F, Y');
$incDate = ! empty($r['incident_date']) ? date('F d, Y', strtotime($r['incident_date'])) : 'an unspecified date';
$filedDate = ! empty($r['created_at']) ? date('F d, Y', strtotime($r['created_at'])) : '—';
$hearingDate = ! empty($r['hearing_date']) ? date('F d, Y', strtotime($r['hearing_date'])) : 'No hearing scheduled';
$complainant = esc($r['complainant_full_name'] ?? $r['complainant_name'] ?? '________________');
$respondent = esc($r['respondent_name'] ?? $r['persons_involved'] ?? '________________');
$incidentType = esc($r['incident_type'] ?? 'the stated dispute');
$location = esc($r['location'] ?? 'Barangay Bacolod, Bato, Camarines Sur');
$captainN = esc($captainName ?? 'PUNONG BARANGAY');
$secretaryN = esc($secretaryName ?? 'BARANGAY SECRETARY');
$barangaySeal = $barangaySeal ?? '/bacolod.png';
$municipalitySeal = $municipalitySeal ?? '/Picture1.png';
$downloadUrl = $downloadUrl ?? '#';
$viewUrl = $viewUrl ?? '#';
$role = $role ?? 'captain';
?>

<?php if (! $isPdf): ?>
    <div class="toolbar">
        <a class="tb-btn tb-btn--back" href="/<?= esc($role) ?>/blotter/<?= (int) $r['id'] ?>">&#8592; Back</a>
        <span>Certificate to File Action · <?= esc($caseNo) ?></span>
        <button class="tb-btn tb-btn--print" type="button" onclick="window.print()">PRINT</button>
        <a class="tb-btn tb-btn--download" href="<?= esc($downloadUrl) ?>">DOWNLOAD</a>
    </div>
<?php endif; ?>

    <div class="sheet">
        <table class="header-table">
            <tr>
                <td style="width:90px;text-align:center;vertical-align:middle;">
                    <?php if ($barangaySeal !== ''): ?>
                        <img class="seal" src="<?= esc($barangaySeal) ?>" alt="Barangay seal">
                    <?php endif; ?>
                </td>
                <td class="header-center">
                    <div class="republic">Republic of the Philippines</div>
                    <div class="place-line">Province of Camarines Sur</div>
                    <div class="place-line">Municipality of Bato</div>
                    <div class="brgy-name">Barangay Bacolod</div>
                    <div class="office-bar">Office of the Lupong Tagapamayapa</div>
                </td>
                <td style="width:90px;text-align:center;vertical-align:middle;">
                    <?php if ($municipalitySeal !== ''): ?>
                        <img class="seal" src="<?= esc($municipalitySeal) ?>" alt="Municipal seal">
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <hr class="rule">

        <div class="doc-title">Certification to File Action</div>
        <div class="doc-form">KP Form No. 20 &nbsp;·&nbsp; Pursuant to R.A. 7160, Katarungang Pambarangay</div>

        <table class="meta-table">
            <tr>
                <td><strong>Case No.:</strong> <?= esc($caseNo) ?></td>
                <td style="text-align:right;"><strong>Date Issued:</strong> <?= esc($today) ?></td>
            </tr>
        </table>

        <div class="body">
            <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
            <p class="indent">
                This is to certify that the complaint filed by <strong><?= $complainant ?></strong>
                against <strong><?= $respondent ?></strong>, involving a case of
                <strong><?= $incidentType ?></strong> that allegedly occurred on
                <strong><?= esc($incDate) ?></strong> at <strong><?= $location ?></strong>,
                has been brought before the <strong>Lupong Tagapamayapa</strong> of
                Barangay Bacolod, Bato, Camarines Sur, for conciliation under the Katarungang Pambarangay.
            </p>
        </div>

        <div class="facts-wrap">
            <table class="facts-table">
                <tr>
                    <td>Case No.</td>
                    <td><?= esc($caseNo) ?></td>
                </tr>
                <tr>
                    <td>Complainant</td>
                    <td><?= $complainant ?></td>
                </tr>
                <tr>
                    <td>Respondent</td>
                    <td><?= $respondent ?></td>
                </tr>
                <tr>
                    <td>Nature of Dispute</td>
                    <td><?= $incidentType ?></td>
                </tr>
                <tr>
                    <td>Date Filed</td>
                    <td><?= esc($filedDate) ?></td>
                </tr>
                <tr>
                    <td>Hearing / Confrontation</td>
                    <td><?= esc($hearingDate) ?></td>
                </tr>
            </table>
        </div>

        <div class="body">
            <p class="indent">
                Despite earnest efforts at mediation and conciliation, no amicable settlement was reached
                by the parties. In accordance with <strong>Section 412 of the Local Government Code
                (Republic Act No. 7160)</strong>, this Certification is issued to attest that the barangay
                has exhausted the Katarungang Pambarangay proceedings and that the corresponding complaint
                may now be filed with the proper court, prosecutor’s office, or law-enforcement authority,
                including the Police Station of Bato, Camarines Sur.
            </p>
            <p class="indent">
                Issued this <?= esc($day) ?> day of <?= esc($monthYear) ?>, at Barangay Bacolod,
                Bato, Camarines Sur, Philippines, upon request of the complainant and for whatever legal
                purpose it may serve.
            </p>
        </div>

        <div class="note">
            <strong>Note:</strong> This certification is valid for sixty (60) days from the date of issuance.
            Failure to file the appropriate action within that period shall require a new certification from
            this Barangay.
        </div>

        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-name"><?= $secretaryN ?></div>
                    <div class="sig-line"></div>
                    <div class="sig-title">Barangay Secretary<br>Lupon Secretary</div>
                </td>
                <td>
                    <div class="sig-name"><?= $captainN ?></div>
                    <div class="sig-line"></div>
                    <div class="sig-title">Punong Barangay<br>Lupon Chairman</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Barangay Bacolod, Bato, Camarines Sur &nbsp;·&nbsp; <?= esc($caseNo) ?> &nbsp;·&nbsp; Official Certificate to File Action
        </div>
    </div>
</body>

</html>
