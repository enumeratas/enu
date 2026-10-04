<?php
$zone = (isset($zone) && is_string($zone)) ? $zone : '';
$byZone = (isset($byZone) && is_array($byZone)) ? $byZone : [];
$activeFilters = (isset($activeFilters) && is_array($activeFilters)) ? $activeFilters : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Census Export<?= $zone ? ' — ' . esc($zone) : '' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000;
            background: #f0f2f8;
        }

        /* ── Toolbar (screen only) ── */
        #toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 52px;
            background: #1d2448;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .25);
        }

        #toolbar span {
            color: rgba(255, 255, 255, .75);
            font-size: 13px;
            font-family: Arial, sans-serif;
            flex: 1;
        }

        .tb-btn {
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: opacity .2s;
        }

        .tb-btn:hover {
            opacity: .88;
        }

        .tb-btn--primary {
            background: #16c79a;
            color: #fff;
        }

        .tb-btn--back {
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        #progress-wrap {
            display: none;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-size: 12px;
            font-family: Arial, sans-serif;
        }

        #progress-bar-track {
            width: 180px;
            height: 6px;
            background: rgba(255, 255, 255, .2);
            border-radius: 4px;
            overflow: hidden;
        }

        #progress-bar-fill {
            height: 100%;
            background: #16c79a;
            border-radius: 4px;
            width: 0%;
            transition: width .3s;
        }

        /* ── Content area ── */
        #content-area {
            margin-top: 60px;
            padding: 16px;
            overflow-x: auto;
        }

        .zone-page {
            background: #fff;
            width: 420mm;
            min-height: 185mm;
            margin: 0 auto 24px;
            padding: 8mm 6mm;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .12);
        }

        .form-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3mm;
        }

        .form-id {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            margin-bottom: 4mm;
            font-size: 9pt;
            line-height: 1.7;
        }

        .form-codes {
            text-align: center;
        }

        .meta-ul {
            border-bottom: 1px solid #000;
            min-width: 140px;
            display: inline-block;
        }

        /* ── Census table ── */
        table.ct {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.ct,
        table.ct th,
        table.ct td {
            font-family: Arial, Helvetica, sans-serif;
            font-style: normal;
            font-variant: normal;
            letter-spacing: normal;
            text-transform: none;
        }

        table.ct th,
        table.ct td {
            border: 1px solid #555;
            padding: 4px 5px;
            vertical-align: middle;
            word-break: normal;
            overflow-wrap: break-word;
        }

        table.ct thead th {
            background: #d9d9d9;
            font-size: 8.5pt;
            font-weight: 700;
            text-align: center;
            line-height: 1.25;
        }

        table.ct tbody td {
            font-size: 10pt;
            font-weight: 400;
            line-height: 1.35;
        }

        table.ct td.name-cell {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            font-weight: 400;
            text-align: left;
            letter-spacing: 0;
        }

        tr.row-head td {
            background: #f2f2f2;
            font-weight: 400;
        }

        tr.hh-spacer td {
            border: none;
            height: 3px;
            background: #fff;
            padding: 0;
        }

        table.ct col.c-zone { width: 4%; }
        table.ct col.c-hh { width: 4.5%; }
        table.ct col.c-count { width: 3.6%; }
        table.ct col.c-name { width: 14.5%; }
        table.ct col.c-rel { width: 5%; }
        table.ct col.c-bday { width: 6.5%; }
        table.ct col.c-age { width: 3%; }
        table.ct col.c-sex { width: 2.8%; }
        table.ct col.c-status { width: 4.5%; }
        table.ct col.c-occ { width: 6.5%; }
        table.ct col.c-inc { width: 4.5%; }
        table.ct col.c-religion { width: 4.5%; }
        table.ct col.c-educ { width: 5.5%; }
        table.ct col.c-phil { width: 5%; }
        table.ct col.c-cat { width: 4%; }
        table.ct col.c-4ps { width: 3%; }
        table.ct col.c-water { width: 4%; }
        table.ct col.c-flag { width: 3.8%; }
    </style>
</head>

<body>

    <!-- Toolbar -->
    <div id="toolbar">
        <button class="tb-btn tb-btn--back" onclick="history.back()">&#8592; Back</button>
        <span>Census Records Export<?= $zone ? ' — ' . esc($zone) : ' — All Zones' ?></span>
        <div id="progress-wrap">
            <div id="progress-bar-track">
                <div id="progress-bar-fill"></div>
            </div>
            <span id="progress-label">Generating PDF…</span>
        </div>
        <button class="tb-btn tb-btn--primary" id="downloadBtn" onclick="generatePdf()">
            &#8595; Download PDF
        </button>
    </div>

    <!-- Printable content -->
    <div id="content-area">
        <?php
        $cell = static function ($value): string {
            $value = trim((string) ($value ?? ''));
            return $value === '' ? '' : esc($value);
        };
        $pdfDate = static function ($value): string {
            if (empty($value)) {
                return '';
            }
            $stamp = strtotime((string) $value);
            return $stamp ? date('m/d/Y', $stamp) : '';
        };
        $pdfAge = static function ($value): string {
            if (empty($value)) {
                return '';
            }
            $born = date_create((string) $value);
            return $born ? (string) ((int) date_diff($born, date_create('today'))->y) : '';
        };
        $sexOf = static function ($gender): string {
            $gender = strtolower(trim((string) $gender));
            if ($gender === '') {
                return '';
            }
            return $gender === 'female' ? 'F' : 'M';
        };
        $money = static function ($value): string {
            $amount = (float) ($value ?? 0);
            return $amount > 0 ? number_format($amount, 0) : '';
        };
        $waterLevel = static function ($value): string {
            $value = strtoupper(trim((string) $value));
            if ($value === '') {
                return '';
            }
            return $value === 'NONE' ? 'No' : $value;
        };
        $yesNo = static function ($value): string {
            if ($value === null || $value === '') {
                return '';
            }
            if (is_string($value)) {
                $text = strtolower(trim($value));
                if ($text === 'yes' || $text === 'with') {
                    return 'Yes';
                }
                if ($text === 'no' || $text === 'without') {
                    return 'No';
                }
            }
            return ((int) $value === 1) ? 'Yes' : 'No';
        };
        $facility = static function ($value): string {
            $value = strtolower(trim((string) $value));
            if ($value === 'with') {
                return 'With';
            }
            if ($value === 'without') {
                return 'Without';
            }
            return '';
        };
        $personName = static function (array $person): string {
            $name = trim(
                ($person['last_name'] ?? '') . ', ' . ($person['first_name'] ?? '')
                . (! empty($person['middle_name']) ? ' ' . $person['middle_name'] : '')
                . (! empty($person['suffix']) ? ' ' . $person['suffix'] : '')
            );
            return trim($name, " \t\n\r\0\x0B,");
        };
        ?>
        <?php foreach ($byZone as $zoneName => $households): ?>
            <div class="zone-page" id="zone-<?= esc(preg_replace('/\s+/', '-', strtolower($zoneName))) ?>">

                <div class="form-title">Household and Health Profile</div>
                <div class="form-id">
                    <div>
                        <div>Municipality: <strong>BATO</strong></div>
                        <div>Barangay: <strong>BACOLOD</strong></div>
                    </div>
                    <div class="form-codes">
                        <div>NHFR Facility Code: <span class="meta-ul">&nbsp;</span></div>
                        <div>PSG Code: <strong>0501703002</strong></div>
                    </div>
                    <div style="text-align:right;">
                        <div>Region: <strong>V (Bicol)</strong></div>
                        <div>Province: <strong>CAMARINES SUR</strong></div>
                    </div>
                </div>

                <?php if (! empty($activeFilters ?? [])): ?>
                    <div style="font-size:6.5pt;color:#555;margin-bottom:3mm;padding:4px 8px;background:#f5f5f5;border-radius:3px;border:1px solid #ddd;">
                        <strong>Filters:</strong> <?= esc(implode(' &middot; ', $activeFilters)) ?>
                    </div>
                <?php endif; ?>

                <table class="ct">
                    <colgroup>
                        <col class="c-zone">
                        <col class="c-hh">
                        <col class="c-count">
                        <col class="c-count">
                        <col class="c-name">
                        <col class="c-rel">
                        <col class="c-bday">
                        <col class="c-age">
                        <col class="c-sex">
                        <col class="c-status">
                        <col class="c-occ">
                        <col class="c-inc">
                        <col class="c-religion">
                        <col class="c-educ">
                        <col class="c-phil">
                        <col class="c-cat">
                        <col class="c-4ps">
                        <col class="c-water">
                        <col class="c-flag">
                        <col class="c-flag">
                        <col class="c-flag">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2">Zone</th>
                            <th rowspan="2">HH No.</th>
                            <th rowspan="2">No. of<br>Family</th>
                            <th rowspan="2">No. of<br>HH<br>Members</th>
                            <th rowspan="2">Name of HH Member<br>(Surname, First Name Middle Name)</th>
                            <th rowspan="2">Relation to<br>Household Head</th>
                            <th rowspan="2">Birthday<br>(mm/dd/yyyy)</th>
                            <th rowspan="2">Age</th>
                            <th rowspan="2">Sex</th>
                            <th rowspan="2">Relation<br>Status</th>
                            <th rowspan="2">Occupation /<br>Source of Income</th>
                            <th rowspan="2">Average<br>Monthly<br>Income</th>
                            <th rowspan="2">Religion</th>
                            <th rowspan="2">Educational<br>Attainment</th>
                            <th colspan="2">Philhealth Number</th>
                            <th rowspan="2">4Ps</th>
                            <th colspan="2">Access to Safe Water</th>
                            <th colspan="2">Sanitation Services</th>
                        </tr>
                        <tr>
                            <th>Philhealth<br>Number</th>
                            <th>Philhealth<br>Category</th>
                            <th>Basic Safe<br>Source<br>(Level I, II, III)</th>
                            <th>Safety-<br>Managed</th>
                            <th>Basic<br>Sanitation<br>Facility</th>
                            <th>Using Safety-<br>Managed<br>Sanitation<br>Services</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($households as $hh):
                            $water = $waterLevel($hh['water_source_level'] ?? '');
                            $waterManaged = $yesNo($hh['water_safety_managed'] ?? null);
                            $sanitation = $facility($hh['sanitation_basic'] ?? '');
                            $sanitationManaged = $facility($hh['sanitation_managed'] ?? '');
                            $religion = $cell($hh['religion'] ?? '');
                            $familyCount = (string) max(1, (int) ($hh['num_families'] ?? 1));
                            $memberCount = (string) max(1, (int) ($hh['member_total'] ?? (1 + count($hh['members'] ?? []))));
                            $rows = [];
                            if (! isset($hh['show_head']) || $hh['show_head']) {
                                $rows[] = [
                                    'head' => true,
                                    'name' => $personName($hh),
                                    'relation' => 'Head',
                                    'birthday' => $pdfDate($hh['date_of_birth'] ?? null),
                                    'age' => $pdfAge($hh['date_of_birth'] ?? null),
                                    'sex' => $sexOf($hh['gender'] ?? ''),
                                    'status' => (string) ($hh['civil_status'] ?? ''),
                                    'occupation' => (string) ($hh['occupation'] ?? ''),
                                    'income' => $money($hh['monthly_income'] ?? 0),
                                    'education' => (string) ($hh['educational_attainment'] ?? ''),
                                    'philhealth' => (string) ($hh['philhealth_no'] ?? ''),
                                ];
                            }
                            foreach ($hh['members'] as $member) {
                                $occupation = trim((string) ($member['occupation'] ?? ''));
                                if ($occupation === '') {
                                    $occupation = trim((string) ($member['work_detail'] ?? ''));
                                }
                                $education = trim((string) ($member['grade_level'] ?? ''));
                                if ($education === '') {
                                    $education = trim((string) ($member['educational_attainment'] ?? ''));
                                }
                                $rows[] = [
                                    'head' => false,
                                    'name' => $personName($member),
                                    'relation' => (string) ($member['relationship'] ?? ''),
                                    'birthday' => $pdfDate($member['date_of_birth'] ?? null),
                                    'age' => $pdfAge($member['date_of_birth'] ?? null),
                                    'sex' => $sexOf($member['gender'] ?? ''),
                                    'status' => (string) ($member['marital_status'] ?? ''),
                                    'occupation' => $occupation,
                                    'income' => $money($member['monthly_income'] ?? 0),
                                    'education' => $education,
                                    'philhealth' => (string) ($member['philhealth_no'] ?? ''),
                                ];
                            }
                        ?>
                            <?php foreach ($rows as $index => $row): ?>
                                <tr class="<?= ! empty($row['head']) ? 'row-head' : '' ?>">
                                    <td style="text-align:center;"><?= $index === 0 ? $cell($hh['zone'] ?? $zoneName) : '' ?></td>
                                    <td style="text-align:center;"><?= $index === 0 ? $cell($hh['household_no'] ?? '') : '' ?></td>
                                    <td style="text-align:center;"><?= $index === 0 ? esc($familyCount) : '' ?></td>
                                    <td style="text-align:center;"><?= $index === 0 ? esc($memberCount) : '' ?></td>
                                    <td class="name-cell"><?= $cell($row['name']) ?></td>
                                    <td style="text-align:center;"><?= $cell($row['relation']) ?></td>
                                    <td style="text-align:center;"><?= esc($row['birthday']) ?></td>
                                    <td style="text-align:center;"><?= esc($row['age']) ?></td>
                                    <td style="text-align:center;"><?= esc($row['sex']) ?></td>
                                    <td style="text-align:center;"><?= $cell($row['status']) ?></td>
                                    <td><?= $cell($row['occupation']) ?></td>
                                    <td style="text-align:right;"><?= esc($row['income']) ?></td>
                                    <td style="text-align:center;"><?= $religion ?></td>
                                    <td><?= $cell($row['education']) ?></td>
                                    <td style="text-align:center;"><?= $cell($row['philhealth']) ?></td>
                                    <td></td>
                                    <td style="text-align:center;"><?= $index === 0 && ! empty($hh['is_4ps']) ? '&#10003;' : '' ?></td>
                                    <td style="text-align:center;"><?= esc($water) ?></td>
                                    <td style="text-align:center;"><?= esc($waterManaged) ?></td>
                                    <td style="text-align:center;"><?= esc($sanitation) ?></td>
                                    <td style="text-align:center;"><?= esc($sanitationManaged) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="hh-spacer">
                                <td colspan="21"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div><!-- end zone-page -->
        <?php endforeach; ?>
    </div><!-- end content-area -->

    <script>
        async function generatePdf() {
            const btn = document.getElementById('downloadBtn');
            const progWrap = document.getElementById('progress-wrap');
            const progFill = document.getElementById('progress-bar-fill');
            const progLbl = document.getElementById('progress-label');

            btn.disabled = true;
            btn.textContent = 'Generating…';
            progWrap.style.display = 'flex';

            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a3'
            });
            const pages = document.querySelectorAll('.zone-page');
            const W = pdf.internal.pageSize.getWidth();
            const H = pdf.internal.pageSize.getHeight();
            const margin = 6;

            for (let i = 0; i < pages.length; i++) {
                progFill.style.width = Math.round(((i) / pages.length) * 90) + '%';
                progLbl.textContent = `Rendering zone ${i + 1} of ${pages.length}…`;

                const canvas = await html2canvas(pages[i], {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                });

                const imgData = canvas.toDataURL('image/jpeg', 0.92);
                const imgW = W - margin * 2;
                const imgH = (canvas.height * imgW) / canvas.width;

                if (i > 0) pdf.addPage('a3', 'landscape');

                // If content taller than page, scale down to fit
                const finalH = Math.min(imgH, H - margin * 2);
                pdf.addImage(imgData, 'JPEG', margin, margin, imgW, finalH);
            }

            progFill.style.width = '100%';
            progLbl.textContent = 'Saving…';

            const zone = <?= json_encode($zone ?: 'all-zones') ?>;
            const filename = 'census-' + zone.toLowerCase().replace(/\s+/g, '-') + '-<?= date('Y-m-d') ?>.pdf';
            pdf.save(filename);

            btn.disabled = false;
            btn.textContent = '↓ Download PDF';
            progWrap.style.display = 'none';
            progFill.style.width = '0%';
        }
    </script>
</body>

</html>