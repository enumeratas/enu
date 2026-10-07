<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SK Report — CY <?= (int) ($filterYear ?? date('Y')) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 0.55in 0.5in 0.5in 0.5in; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #1d2448; padding-bottom: 10px; margin-bottom: 16px; }
        .seals { margin-bottom: 6px; }
        .seals img { width: 52px; height: 52px; margin: 0 10px; }
        .header p { margin: 1px 0; font-size: 11px; }
        .header h1 { font-size: 16px; margin: 6px 0 2px; color: #1d2448; }
        .meta { font-size: 10px; color: #4a5068; margin-bottom: 14px; }
        .stats { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .stats td { width: 20%; border: 1px solid #dfe6f1; padding: 8px; text-align: center; background: #f7f9fd; }
        .stats .num { display: block; font-size: 16px; font-weight: 700; color: #1d2448; }
        .stats .lbl { display: block; font-size: 9px; color: #555; margin-top: 2px; }
        h2 { font-size: 13px; color: #1d2448; margin: 14px 0 8px; border-left: 3px solid #5b6fd6; padding-left: 8px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data th, table.data td { border: 1px solid #dfe6f1; padding: 6px 7px; text-align: center; }
        table.data th { background: #f7f9fd; color: #1f2a44; font-size: 10px; }
        table.data td:first-child, table.data th:first-child { text-align: left; }
        table.data tfoot td { background: #f7f9fd; font-weight: 700; }
        .empty { text-align: center; color: #777; padding: 16px; border: 1px solid #eee; }
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
    $barangay = strtoupper((string) ($report['barangay_name'] ?? 'BACOLOD'));
    $municipality = strtoupper((string) ($report['municipality'] ?? 'BATO'));
    $province = strtoupper((string) ($report['province'] ?? 'CAMARINES SUR'));
    $year = (int) ($filterYear ?? date('Y'));
    $demographics = $demographics ?? [];
    $totals = $totals ?? ['total' => 0, 'male' => 0, 'female' => 0, 'student' => 0, 'employed' => 0, 'unemployed' => 0, 'oos' => 0];
    $programs = $programs ?? [];
    $totalParticipants = $totalParticipants ?? 0;
    $totalTarget = $totalTarget ?? 0;
    ?>

    <div class="header">
        <div class="seals">
            <img src="<?= esc($assetUri('dilg.png')) ?>" alt="DILG">
            <img src="<?= esc($assetUri('bacolod.png')) ?>" alt="Barangay seal">
        </div>
        <p>Republic of the Philippines</p>
        <p>Sangguniang Kabataan · Barangay <?= esc($barangay) ?></p>
        <p><?= esc($municipality) ?>, <?= esc($province) ?></p>
        <h1>SK Annual Report — Calendar Year <?= $year ?></h1>
        <p>Youth demographics and program participation</p>
    </div>
    <p class="meta">Prepared <?= date('F d, Y') ?> from barangay census and SK program records.</p>

    <table class="stats">
        <tr>
            <td><span class="num"><?= number_format($totals['total']) ?></span><span class="lbl">Registered Youth</span></td>
            <td><span class="num"><?= number_format($totals['student']) ?></span><span class="lbl">Students</span></td>
            <td><span class="num"><?= number_format($totals['oos']) ?></span><span class="lbl">Out-of-School</span></td>
            <td><span class="num"><?= number_format(count($programs)) ?></span><span class="lbl">Programs</span></td>
            <td><span class="num"><?= number_format($totalParticipants) ?></span><span class="lbl">Participants</span></td>
        </tr>
    </table>

    <h2>I. Youth Demographics (Ages 15–30)</h2>
    <?php if ((int) $totals['total'] === 0): ?>
        <div class="empty">No youth records found in the census.</div>
    <?php else: ?>
        <table class="data">
            <thead>
                <tr>
                    <th>Age Group</th>
                    <th>Total</th>
                    <th>Male</th>
                    <th>Female</th>
                    <th>Student</th>
                    <th>Employed</th>
                    <th>Unemployed</th>
                    <th>OSY</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demographics as $label => $d):
                    $pct = $totals['total'] > 0 ? round($d['total'] / $totals['total'] * 100, 1) : 0;
                ?>
                    <tr>
                        <td><?= esc($label) ?></td>
                        <td><?= number_format($d['total']) ?></td>
                        <td><?= number_format($d['male']) ?></td>
                        <td><?= number_format($d['female']) ?></td>
                        <td><?= number_format($d['student']) ?></td>
                        <td><?= number_format($d['employed']) ?></td>
                        <td><?= number_format($d['unemployed']) ?></td>
                        <td><?= number_format($d['oos']) ?></td>
                        <td><?= $pct ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td><?= number_format($totals['total']) ?></td>
                    <td><?= number_format($totals['male']) ?></td>
                    <td><?= number_format($totals['female']) ?></td>
                    <td><?= number_format($totals['student']) ?></td>
                    <td><?= number_format($totals['employed']) ?></td>
                    <td><?= number_format($totals['unemployed']) ?></td>
                    <td><?= number_format($totals['oos']) ?></td>
                    <td>100%</td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <h2>II. Program Participation</h2>
    <?php if ($programs === []): ?>
        <div class="empty">No SK programs recorded for CY <?= $year ?>.</div>
    <?php else: ?>
        <table class="data">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Target</th>
                    <th>Actual</th>
                    <th>Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programs as $p):
                    $target = (int) ($p['target_participants'] ?? 0);
                    $actual = (int) ($p['actual_participants'] ?? 0);
                    $rate = ($target > 0 && $actual > 0) ? min(100, round($actual / $target * 100)) : 0;
                    $dateStr = ! empty($p['start_date']) ? date('M d, Y', strtotime((string) $p['start_date'])) : '—';
                ?>
                    <tr>
                        <td><?= esc($p['name'] ?? '') ?></td>
                        <td><?= esc($p['category'] ?? '') ?></td>
                        <td><?= esc($dateStr) ?></td>
                        <td><?= $target > 0 ? number_format($target) : '—' ?></td>
                        <td><?= $actual > 0 ? number_format($actual) : '—' ?></td>
                        <td><?= $rate > 0 ? $rate . '%' : '—' ?></td>
                        <td><?= esc($p['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td><?= number_format($totalTarget) ?></td>
                    <td><?= number_format($totalParticipants) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</body>

</html>
