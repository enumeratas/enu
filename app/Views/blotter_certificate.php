<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate to File Action — BL-<?= str_pad($report['id'], 4, '0', STR_PAD_LEFT) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 18mm 20mm 18mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
        }

        /* ── Screen toolbar ── */
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
            color: rgba(255, 255, 255, .7);
            font-family: Arial, sans-serif;
            font-size: 13px;
            flex: 1;
        }

        .tb-btn {
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .tb-btn--primary {
            background: #e67e22;
            color: #fff;
        }

        .tb-btn--back {
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        .tb-tip {
            position: fixed;
            top: 52px;
            left: 0;
            right: 0;
            background: #1d2448;
            border-bottom: 3px solid #e67e22;
            padding: 10px 20px;
            display: none;
            align-items: center;
            gap: 12px;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #fff;
            z-index: 998;
        }

        .tb-tip strong {
            color: #e67e22;
        }

        @media print {

            .toolbar,
            .tb-tip {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }

        /* ── Certificate wrapper ── */
        .cert {
            max-width: 170mm;
            margin: 110px auto 40px;
        }

        @media print {
            .cert {
                margin: 0;
                max-width: 100%;
            }
        }

        /* ── Header ── */
        .cert-header {
            text-align: center;
            margin-bottom: 18pt;
            border-bottom: 3px double #000;
            padding-bottom: 10pt;
        }

        .cert-header .republic {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3pt;
        }

        .cert-header .brgy-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cert-header .brgy-address {
            font-size: 10pt;
            margin-top: 2pt;
        }

        .cert-header .office-line {
            font-size: 10pt;
            font-style: italic;
            margin-top: 3pt;
            color: #333;
        }

        /* ── Doc number + date ── */
        .cert-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18pt;
            font-size: 11pt;
        }

        .cert-meta .case-no {
            font-weight: bold;
        }

        /* ── Title ── */
        .cert-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 6pt;
            text-decoration: underline;
        }

        .cert-subtitle {
            text-align: center;
            font-size: 10pt;
            font-style: italic;
            color: #444;
            margin-bottom: 20pt;
        }

        /* ── Body ── */
        .cert-body {
            font-size: 12pt;
            line-height: 2;
            text-align: justify;
            margin-bottom: 14pt;
        }

        .cert-body p {
            margin-bottom: 12pt;
        }

        .cert-indent {
            text-indent: 3em;
        }

        /* ── Detail box ── */
        .cert-detail-box {
            border: 1.5px solid #000;
            padding: 10pt 14pt;
            margin: 16pt 0;
            background: #f9f9f9;
        }

        .cert-detail-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .cert-detail-box td {
            padding: 3pt 6pt;
            font-size: 11pt;
            vertical-align: top;
        }

        .cert-detail-box td:first-child {
            font-weight: bold;
            width: 120pt;
            color: #333;
        }

        /* ── Warning box ── */
        .cert-warning {
            font-size: 11pt;
            font-style: italic;
            margin: 14pt 0;
            padding: 8pt 12pt;
            border-left: 4px solid #000;
            background: #f5f5f5;
            line-height: 1.6;
        }

        /* ── Signature block ── */
        .cert-sig {
            margin-top: 36pt;
            display: flex;
            justify-content: space-between;
            gap: 30pt;
        }

        .sig-col {
            flex: 1;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 4pt;
        }

        .sig-name {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .sig-title {
            font-size: 10pt;
            font-style: italic;
            color: #444;
        }

        /* ── Footer ── */
        .cert-footer {
            margin-top: 20pt;
            border-top: 1px solid #ccc;
            padding-top: 6pt;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- Screen toolbar -->
    <div class="toolbar">
        <button class="tb-btn tb-btn--back" onclick="history.back()">&#8592; Back</button>
        <button class="tb-btn tb-btn--primary" onclick="printCert()">&#128438; Save as PDF / Print</button>
    </div>
    <div class="tb-tip" id="tbTip">
        <span>&#128161;</span>
        <span>Click <strong>Save as PDF / Print</strong> → set <strong>Destination</strong> to <strong>"Save as PDF"</strong> → click <strong>Save</strong>.</span>
    </div>

    <?php
    $r          = $report;
    $caseNo     = str_pad($r['id'], 2, '0', STR_PAD_LEFT);
    $today      = date('F d, Y');
    $incDate    = ! empty($r['incident_date']) ? date('F d, Y', strtotime($r['incident_date'])) : 'an unspecified date';
    $complainant = esc($r['complainant_full_name'] ?? $r['complainant_name'] ?? '—');
    $respondent  = esc($r['respondent_name'] ?? $r['persons_involved'] ?? '___________________________');
    $incidentType = esc($r['incident_type'] ?? '—');
    $location    = esc($r['location'] ?? 'Barangay Bacolod');
    $captainN    = esc($captainName   ?? 'PUNONG BARANGAY');
    $secretaryN  = esc($secretaryName ?? 'BARANGAY SECRETARY');
    ?>

    <div class="cert">

        <!-- Header -->
        <div class="cert-header">
            <div class="republic">Republic of the Philippines</div>
            <div class="brgy-name">Barangay Bacolod</div>
            <div class="brgy-address">Bato, Camarines Sur, Philippines</div>
            <div class="office-line">Office of the Punong Barangay</div>
        </div>

        <!-- Meta row -->
        <div class="cert-meta">
            <div class="case-no">Case No.: BL-<?= $caseNo ?></div>
            <div><?= $today ?></div>
        </div>

        <!-- Title -->
        <div class="cert-title">Certification to File Action</div>
        <div class="cert-subtitle">(Pursuant to Republic Act No. 7160 — Katarungang Pambarangay Law)</div>

        <!-- Body -->
        <div class="cert-body">
            <p><strong>TO WHOM IT MAY CONCERN:</strong></p>

            <p class="cert-indent">
                This is to certify that the complaint filed by <strong><?= $complainant ?></strong>
                against <strong><?= $respondent ?></strong> involving a case of
                <strong><?= $incidentType ?></strong>
                that allegedly occurred on <strong><?= $incDate ?></strong> at <strong><?= $location ?></strong>
                has been the subject of barangay conciliation proceedings before the
                <strong>Lupong Tagapamayapa</strong> of Barangay Bacolod, Bato, Camarines Sur.
            </p>
        </div>

        <!-- Detail box -->
        <div class="cert-detail-box">
            <table>
                <tr>
                    <td>Case No.:</td>
                    <td><strong><?= $caseNo ?></strong></td>
                </tr>
                <tr>
                    <td>Complainant:</td>
                    <td><?= $complainant ?></td>
                </tr>
                <tr>
                    <td>Respondent:</td>
                    <td><?= $respondent ?></td>
                </tr>
                <tr>
                    <td>Incident Type:</td>
                    <td><?= $incidentType ?></td>
                </tr>
                <tr>
                    <td>Date Filed:</td>
                    <td><?= ! empty($r['created_at']) ? date('F d, Y', strtotime($r['created_at'])) : '—' ?></td>
                </tr>
                <tr>
                    <td>Hearing Date:</td>
                    <td><?= ! empty($r['hearing_date']) ? date('F d, Y', strtotime($r['hearing_date'])) : 'No hearing scheduled' ?></td>
                </tr>
            </table>
        </div>

        <div class="cert-body">
            <p class="cert-indent">
                Despite efforts at amicable settlement, the dispute between the parties has
                <strong>not been resolved</strong> at the barangay level. In accordance with
                Section 412 of the Local Government Code (R.A. 7160), this Certification is
                issued to certify that the barangay has <strong>exhausted all means of conciliation</strong>
                and that the parties are now free to bring the matter before the appropriate government office
                or court of law, including referral to the <strong>Police Station</strong> of Bato, Camarines Sur.
            </p>

            <p class="cert-indent">
                This Certification is issued upon request of the complainant and for whatever legal
                purpose it may serve.
            </p>
        </div>

        <!-- Warning -->
        <div class="cert-warning">
            <strong>Note:</strong> This certificate shall have a validity period of sixty (60) days from the
            date of issuance. Failure to file the appropriate action within the validity period shall
            require a new certification from the Barangay.
        </div>

        <!-- Signature block -->
        <div class="cert-sig">
            <div class="sig-col">
                <div class="sig-name"><?= $captainN ?></div>
                <div class="sig-line"></div>
                <div class="sig-title">Punong Barangay<br>Barangay Bacolod, Bato, Camarines Sur</div>
            </div>
            <div class="sig-col">
                <div class="sig-name"><?= $secretaryN ?></div>
                <div class="sig-line"></div>
                <div class="sig-title">Barangay Secretary<br>Barangay Bacolod, Bato, Camarines Sur</div>
            </div>
        </div>

    </div>

    <script>
        function printCert() {
            document.getElementById('tbTip').style.display = 'flex';
            setTimeout(() => window.print(), 300);
        }

        window.addEventListener('load', () => setTimeout(printCert, 700));
    </script>
</body>

</html>