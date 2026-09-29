<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Page ── */
        .rpt-wrap {
            max-width: 100%;
        }

        /* ── Summary stat cards ── */
        .rpt-summary {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            gap: 14px;
            margin-bottom: 28px;
        }

        .rpt-stat {
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 1px 8px rgba(29, 36, 72, .06);
            border: 1px solid #eef0f8;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow .18s, transform .14s;
        }

        .rpt-stat:hover {
            box-shadow: 0 4px 16px rgba(29, 36, 72, .10);
            transform: translateY(-2px);
        }

        .rpt-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .rpt-stat-num {
            font-size: 22px;
            font-weight: 700;
            color: #1a1d2e;
            line-height: 1.1;
        }

        .rpt-stat-label {
            font-size: 11px;
            color: #9aa0b4;
            font-weight: 500;
            margin-top: 3px;
        }

        /* ── Toolbar ── */
        .rpt-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        /* ── Download options panel ── */
        .rpt-filter-panel {
            background: #fff;
            border: 1.5px solid #e2e5ef;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(29, 36, 72, .10);
            padding: 22px 24px;
            margin-bottom: 20px;
            display: none;
            animation: rptFadeIn .18s ease;
        }

        .rpt-filter-panel.open {
            display: block;
        }

        @keyframes rptFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rpt-filter-title {
            font-size: 13px;
            font-weight: 700;
            color: #1a1d2e;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rpt-filter-title i {
            color: #5b6fd6;
        }

        .rpt-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 18px;
        }

        .rpt-filter-check {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border: 1.5px solid #e2e5ef;
            border-radius: 9px;
            cursor: pointer;
            font-size: 13px;
            color: #374151;
            transition: border-color .15s, background .15s;
            user-select: none;
        }

        .rpt-filter-check:hover {
            border-color: #1d2448;
            background: #f8f9ff;
        }

        .rpt-filter-check input[type="checkbox"] {
            accent-color: #1d2448;
            width: 15px;
            height: 15px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .rpt-filter-check.checked {
            border-color: #1d2448;
            background: #f0f2ff;
            color: #1d2448;
            font-weight: 600;
        }

        .rpt-filter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 14px;
            border-top: 1px solid #f0f2f8;
        }

        .rpt-filter-hint {
            font-size: 12px;
            color: #9aa0b4;
            flex: 1;
        }

        /* ── Stacked report sections ── */
        .rpt-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            align-items: start;
            margin-bottom: 20px;
        }

        .rpt-grid--table-pair {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (max-width: 960px) {

            .rpt-grid,
            .rpt-grid--table-pair {
                grid-template-columns: 1fr;
            }
        }

        /* ── Section cards ── */
        .rpt-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(22, 32, 74, 0.05);
            border: 1px solid #dfe6f1;
            overflow: hidden;
            width: 100%;
            margin: 0;
        }

        .rpt-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            background: #f7f9fd;
            border-bottom: 1px solid #e3e8f1;
        }

        .rpt-card-header h4 {
            color: #1f2a44;
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            letter-spacing: .2px;
        }

        .rpt-card-header i {
            color: #2a5bd7;
            font-size: 13px;
        }

        /* ── Demographic info rows ── */
        .rpt-card-body {
            padding: 18px 22px;
        }

        .demo-row {
            display: flex;
            align-items: baseline;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #f5f6fb;
            font-size: 13.5px;
        }

        .demo-row:last-child {
            border-bottom: none;
        }

        .demo-label {
            font-weight: 700;
            color: #1d2448;
            min-width: 26px;
            flex-shrink: 0;
            font-size: 12.5px;
        }

        .demo-text {
            flex: 1;
            color: #4a5068;
            font-size: 13px;
        }

        .demo-val {
            font-weight: 700;
            color: #1d2448;
            background: #eef0fb;
            padding: 2px 12px;
            border-radius: 100px;
            font-size: 12.5px;
            min-width: 52px;
            text-align: center;
        }

        /* ── Tables ── */
        .rpt-card .rpt-table {
            display: table;
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: #fff;
        }

        .rpt-table thead tr {
            background: #f7f9fd;
            border-bottom: 2px solid #2d6cdf;
        }

        .rpt-table thead th {
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #1f2a44;
            text-transform: none;
            letter-spacing: 0;
            border-bottom: none;
            text-align: left;
            white-space: nowrap;
            vertical-align: middle;
            background: #f7f9fd;
        }

        .rpt-table thead th:first-child {
            text-align: left;
        }

        .rpt-table .th-group th {
            font-size: 10px;
            color: #61708d;
            padding: 8px 14px;
            background: #f1f5fe;
            border-bottom: 1px solid #e3e8f1;
        }

        .rpt-table tbody tr {
            border-bottom: 1px solid #edf1f7;
            transition: background .12s;
            height: auto;
            background: #fff;
        }

        .rpt-table tbody tr:nth-child(even) {
            background: #fbfcff;
        }

        .rpt-table tbody tr:last-child {
            border-bottom: none;
        }

        .rpt-table tbody tr:hover {
            background: #f4f8ff;
        }

        .rpt-table td {
            padding: 9px 12px;
            color: #2a3247;
            text-align: center;
            vertical-align: middle;
            font-variant-numeric: tabular-nums;
            overflow-wrap: anywhere;
            line-height: 1.3;
            font-size: 12px;
            border-right: 1px solid #edf1f7;
        }

        .rpt-table td:last-child {
            border-right: none;
        }

        .rpt-table td:first-child {
            text-align: left;
            font-weight: 500;
            color: #2d3250;
            line-height: 1.4;
            vertical-align: middle;
        }

        .rpt-table th:not(:first-child),
        .rpt-table td:not(:first-child) {
            text-align: center;
            white-space: nowrap;
        }

        .rpt-table td:first-child span.rpt-num {
            display: inline-block;
            width: 18px;
            color: #a5afc5;
            font-weight: 500;
            font-size: 11.5px;
        }

        .rpt-table td.rpt-total,
        .rpt-table th.rpt-total {
            font-weight: 700;
            color: #1d2448;
            background: #f7f9fd;
        }

        .rpt-table tfoot tr {
            background: #f7f9fd;
            border-top: 1px solid #dfe6f1;
        }

        .rpt-table tfoot td {
            color: #1d2448;
            font-weight: 700;
            font-size: 12px;
            padding: 10px 14px;
            text-align: center;
            font-variant-numeric: tabular-nums;
            vertical-align: middle;
            background: #f7f9fd;
        }

        .rpt-table tfoot td:first-child {
            text-align: left;
        }

        .rpt-card>.rpt-table {
            min-width: 0;
            width: 100%;
        }

        #sec-age_bracket .rpt-table th:first-child,
        #sec-age_bracket .rpt-table td:first-child {
            width: 56%;
        }

        #sec-sector .rpt-table th:first-child,
        #sec-sector .rpt-table td:first-child,
        #sec-education .rpt-table th:first-child,
        #sec-education .rpt-table td:first-child,
        #sec-water .rpt-table td:first-child {
            width: 72%;
        }

        #sec-sector .rpt-table th:last-child,
        #sec-sector .rpt-table td:last-child,
        #sec-education .rpt-table th:last-child,
        #sec-education .rpt-table td:last-child,
        #sec-water .rpt-table td:last-child {
            width: 28%;
        }

        .rpt-card:has(> .rpt-table) {
            overflow-x: auto;
        }

        @media (max-width: 600px) {
            .rpt-card:has(> .rpt-table) {
                overflow-x: auto;
            }

            .rpt-card>.rpt-table {
                min-width: 520px;
            }

            .rpt-table thead th,
            .rpt-table td,
            .rpt-table tfoot td {
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        @media (max-width: 960px) {
            .rpt-grid--table-pair {
                grid-template-columns: 1fr;
            }
        }

        /* ── Water & sanitation sub-headers ── */
        .rpt-sub-head {
            padding: 8px 16px 6px;
            font-size: 10.5px;
            font-weight: 700;
            color: #5b6fd6;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #eef0fb;
            border-bottom: 1px solid #d8dce8;
        }

        /* ── Print ── */
        @media print {

            .db-sidebar,
            .db-topbar,
            .rpt-toolbar,
            .rpt-filter-panel {
                display: none !important;
            }

            .db-main {
                margin: 0 !important;
            }

            .rpt-card {
                box-shadow: none;
                border: 1px solid #ccc;
            }

            body {
                background: #fff;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = 'secretary';
    $active    = 'reports';
    $pageTitle = 'Reports & Analytics';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $totalPop         = $totalPop         ?? 0;
    $totalMale        = $totalMale        ?? 0;
    $totalFemale      = $totalFemale      ?? 0;
    $totalHouseholds  = $totalHouseholds  ?? 0;
    $totalClearances  = $totalClearances  ?? 0;
    $ageBrackets      = $ageBrackets      ?? [];
    $sectorRows       = $sectorRows       ?? [];
    $waterRows        = $waterRows        ?? [];
    $sanitationRows   = $sanitationRows   ?? [];
    $eduRows          = $eduRows          ?? [];
    $registeredVoters = $registeredVoters ?? 0;
    $totalFamilies    = $totalFamilies    ?? 0;
    $currentYear      = date('Y');

    // Available sections for filtering
    $allSections = [
        'demographic' => 'IV. Demographic Information',
        'age_bracket' => 'F. Population by Age Bracket',
        'sector'      => 'G. Population by Sector',
    ];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <!-- ── Toolbar ── -->
            <div class="rpt-toolbar">
                <a href="/secretary/reports/front-page" class="db-btn db-btn--outline">
                    <i class="fas fa-edit"></i> Edit Front Page
                </a>
                <button class="db-btn db-btn--outline" id="filterToggleBtn" onclick="toggleFilterPanel()">
                    <i class="fas fa-sliders-h"></i> Download Options
                </button>
                <button class="db-btn db-btn--primary" id="downloadBtn"
                    onclick="doDownload()">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>

            <!-- ── Download filter panel ── -->
            <div class="rpt-filter-panel" id="filterPanel">
                <div class="rpt-filter-title">
                    <i class="fas fa-sliders-h"></i>
                    Choose which sections to include in the downloaded PDF
                </div>
                <div class="rpt-filter-grid">
                    <?php foreach ($allSections as $key => $label): ?>
                        <label class="rpt-filter-check checked" id="fc-<?= $key ?>" onclick="toggleSection('<?= $key ?>', this)">
                            <input type="checkbox" id="cb-<?= $key ?>" value="<?= $key ?>" checked onchange="syncCheck('<?= $key ?>')">
                            <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="rpt-filter-actions">
                    <span class="rpt-filter-hint"><i class="fas fa-info-circle" style="margin-right:4px;"></i>Only checked sections will appear in the downloaded file.</span>
                    <button type="button" class="db-btn db-btn--xs db-btn--outline" onclick="selectAll()">Select All</button>
                    <button type="button" class="db-btn db-btn--xs db-btn--outline" onclick="clearAll()">Clear All</button>
                    <button type="button" class="db-btn db-btn--xs db-btn--primary" onclick="doDownload()">
                        <i class="fas fa-download"></i> Apply &amp; Download
                    </button>
                </div>
            </div>

            <!-- ── Report content ── -->
            <div class="rpt-wrap" id="reportContent">

                <!-- Row 1: Demographic + Age Bracket -->
                <div class="rpt-grid">

                    <!-- IV. Demographic Information -->
                    <div class="rpt-card" id="sec-demographic">
                        <div class="rpt-card-header">
                            <i class="fas fa-info-circle"></i>
                            <h4>IV. &nbsp;Demographic Information — CY <?= $currentYear ?></h4>
                        </div>
                        <div class="rpt-card-body">
                            <div class="demo-row">
                                <span class="demo-label">A.</span>
                                <span class="demo-text">No. of Registered Voters</span>
                                <span class="demo-val"><?= number_format($registeredVoters) ?></span>
                            </div>
                            <div class="demo-row">
                                <span class="demo-label">B.</span>
                                <span class="demo-text">No. of Population</span>
                                <span class="demo-val"><?= number_format($totalPop) ?></span>
                            </div>
                            <div class="demo-row" style="flex-direction:column;align-items:flex-start;gap:5px;">
                                <div style="display:flex;align-items:center;gap:10px;width:100%;">
                                    <span class="demo-label">C.</span>
                                    <span class="demo-text">With RBIs?</span>
                                    <label style="display:flex;align-items:center;gap:5px;font-size:12.5px;color:#4a5068;cursor:default;">
                                        <input type="checkbox" disabled> Yes
                                    </label>
                                    <label style="display:flex;align-items:center;gap:5px;font-size:12.5px;color:#4a5068;cursor:default;">
                                        <input type="checkbox" checked disabled> No
                                    </label>
                                </div>
                                <div style="padding-left:36px;font-size:11.5px;color:#b0b6cc;">
                                    If yes, No. of Inhabitants (RBI):
                                    &nbsp;1<sup>st</sup> Sem.: <span style="border-bottom:1px solid #ccc;display:inline-block;min-width:52px;">&nbsp;</span>
                                    &nbsp;2<sup>nd</sup> Sem.: <span style="border-bottom:1px solid #ccc;display:inline-block;min-width:52px;">&nbsp;</span>
                                </div>
                            </div>
                            <div class="demo-row">
                                <span class="demo-label">D.</span>
                                <span class="demo-text">No. of Households</span>
                                <span class="demo-val"><?= number_format($totalHouseholds) ?></span>
                            </div>
                            <div class="demo-row">
                                <span class="demo-label">E.</span>
                                <span class="demo-text">No. of Families</span>
                                <span class="demo-val"><?= number_format($totalFamilies) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- F. Population by Age Bracket -->
                    <div class="rpt-card" id="sec-age_bracket">
                        <div class="rpt-card-header">
                            <i class="fas fa-table"></i>
                            <h4>F. &nbsp;Population by Age Bracket</h4>
                        </div>
                        <table class="rpt-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="text-align:left;vertical-align:middle;">AGE</th>
                                    <th colspan="2">S E X</th>
                                    <th rowspan="2" class="rpt-total" style="vertical-align:middle;">TOTAL</th>
                                </tr>
                                <tr class="th-group">
                                    <th>Male</th>
                                    <th>Female</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ageBrackets as $i => $row): ?>
                                    <tr>
                                        <td><span class="rpt-num"><?= $i + 1 ?>.</span><?= esc($row['label']) ?></td>
                                        <td><?= number_format($row['male']) ?></td>
                                        <td><?= number_format($row['female']) ?></td>
                                        <td class="rpt-total"><?= number_format($row['total']) ?></td>
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

                    <div class="rpt-card" id="sec-age_bracket">
                        <div class="rpt-card-header">
                            <i class="fas fa-table"></i>
                            <h4>G. &nbsp;Popuplation by Sector</h4>
                        </div>
                        <table class="rpt-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="text-align:left;vertical-align:middle;">SECTOR</th>
                                    <th colspan="2">S E X</th>
                                    <th rowspan="2" class="rpt-total" style="vertical-align:middle;">TOTAL</th>
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
                                        <td class="rpt-total"><?= number_format($row['total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div><!-- /.rpt-grid row 1 -->

                <!-- H. Water & Sanitation (full width) -->
                <div class="rpt-card" id="sec-water">
                    <div class="rpt-card-header">
                        <i class="fas fa-tint"></i>
                        <h4>H. &nbsp;Water Source &amp; Sanitation</h4>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
                        <div>
                            <div class="rpt-sub-head">Water Source Level</div>
                            <table class="rpt-table">
                                <tbody>
                                    <?php foreach ($waterRows as $row): ?>
                                        <tr>
                                            <td><?= esc($row['label']) ?></td>
                                            <td class="rpt-total"><?= number_format($row['total']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="border-left:1px solid #f0f2f8;">
                            <div class="rpt-sub-head">Sanitation</div>
                            <table class="rpt-table">
                                <tbody>
                                    <?php foreach ($sanitationRows as $row): ?>
                                        <tr>
                                            <td><?= esc($row['label']) ?></td>
                                            <td class="rpt-total"><?= number_format($row['total']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /.rpt-wrap -->
        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <script>
        // ── Filter panel toggle ────────────────────────────────────────────────
        function toggleFilterPanel() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('open');
            const btn = document.getElementById('filterToggleBtn');
            if (panel.classList.contains('open')) {
                btn.innerHTML = '<i class="fas fa-times"></i> Close Options';
            } else {
                btn.innerHTML = '<i class="fas fa-sliders-h"></i> Download Options';
            }
        }

        // Keep label style in sync when checkbox changes via click on the input directly
        function syncCheck(key) {
            const cb = document.getElementById('cb-' + key);
            const label = document.getElementById('fc-' + key);
            label.classList.toggle('checked', cb.checked);
        }

        // Click on the label div toggles the checkbox
        function toggleSection(key, labelEl) {
            // Only toggle when the click is on the label div itself, not on the input
            // (input's own change will fire syncCheck)
            const cb = document.getElementById('cb-' + key);
            // The input click bubbles up — prevent double-toggle
            if (event && event.target === cb) return;
            cb.checked = !cb.checked;
            labelEl.classList.toggle('checked', cb.checked);
        }

        function selectAll() {
            document.querySelectorAll('.rpt-filter-check input[type="checkbox"]').forEach(cb => {
                cb.checked = true;
                document.getElementById('fc-' + cb.value).classList.add('checked');
            });
        }

        function clearAll() {
            document.querySelectorAll('.rpt-filter-check input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
                document.getElementById('fc-' + cb.value).classList.remove('checked');
            });
        }

        // ── Download with selected sections ───────────────────────────────────
        function doDownload() {
            const selected = [];
            document.querySelectorAll('.rpt-filter-check input[type="checkbox"]').forEach(cb => {
                if (cb.checked) selected.push(cb.value);
            });
            const sections = selected.length > 0 ? selected.join(',') : 'all';
            const url = '/secretary/reports/export?sections=' + encodeURIComponent(sections);
            const downloadUrl = '/secretary/reports/download?sections=' + encodeURIComponent(sections);

            // ── Confirmation dialog ───────────────────────────────────────────
            const overlay = document.createElement('div');
            overlay.id = 'dlConfirmOverlay';
            overlay.style.cssText =
                'position:fixed;inset:0;background:rgba(15,17,30,.5);backdrop-filter:blur(3px);' +
                'z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;' +
                'animation:rptFadeIn .18s ease;';

            overlay.innerHTML = `
                <div style="background:#fff;border-radius:18px;max-width:980px;width:100%;height:min(88vh,820px);
                            padding:20px;box-shadow:0 16px 48px rgba(0,0,0,.2);
                            animation:rptFadeIn .2s ease;display:flex;flex-direction:column;">
                    <div style="width:58px;height:58px;border-radius:50%;background:rgba(91,111,214,.12);
                                color:#5b6fd6;font-size:24px;display:flex;align-items:center;
                                justify-content:center;margin:0 auto 16px;">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3 style="font-size:16px;font-weight:700;color:#1a1d2e;margin:0 0 12px;text-align:center;">
                        Report Preview
                    </h3>
                    <iframe src="${url}" title="Report preview"
                        style="width:100%;flex:1;min-height:0;border:1px solid #e2e5ef;border-radius:10px;background:#f5f6fa;"></iframe>
                    <div style="display:flex;gap:10px;margin-top:14px;">
                        <button id="dlCancelBtn"
                            style="flex:1;padding:11px;border-radius:9px;border:1.5px solid #e2e5ef;
                                   background:#f0f2f8;font-size:13.5px;font-weight:600;
                                   font-family:'Poppins',sans-serif;cursor:pointer;color:#4a5068;">
                            Close
                        </button>
                        <button id="dlConfirmBtn"
                            style="flex:2;padding:11px;border-radius:9px;border:none;
                                   background:linear-gradient(135deg,#1d2448,#2e3a6e);
                                   color:#fff;font-size:13.5px;font-weight:600;
                                   font-family:'Poppins',sans-serif;cursor:pointer;">
                            <i class="fas fa-download" style="margin-right:7px;"></i>Yes, Download
                        </button>
                    </div>
                </div>`;

            document.body.appendChild(overlay);

            // Close on backdrop click or Cancel
            function closeOverlay() {
                overlay.remove();
                document.removeEventListener('keydown', escHandler);
            }

            function escHandler(e) {
                if (e.key === 'Escape') closeOverlay();
            }
            document.addEventListener('keydown', escHandler);
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeOverlay();
            });
            document.getElementById('dlCancelBtn').addEventListener('click', closeOverlay);

            // Confirm → download the same report shown in the preview
            document.getElementById('dlConfirmBtn').addEventListener('click', function() {
                closeOverlay();

                // Show a brief "preparing" toast
                const toast = document.createElement('div');
                toast.style.cssText =
                    'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);' +
                    'background:#1d2448;color:#fff;font-size:13px;font-weight:600;' +
                    'padding:11px 22px;border-radius:10px;z-index:9999;' +
                    'font-family:\'Poppins\',sans-serif;box-shadow:0 4px 16px rgba(0,0,0,.2);' +
                    'display:flex;align-items:center;gap:9px;';
                toast.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing report…';
                document.body.appendChild(toast);

                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                setTimeout(() => toast.remove(), 1200);
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>