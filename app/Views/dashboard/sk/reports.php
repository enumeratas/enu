<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Reports - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        tfoot td {
            padding: 10px 14px;
            font-size: 13px;
            color: #1d2448;
            background: #f5f7fb;
            font-weight: 600;
        }

        .db-badge--completed {
            background: rgba(108, 117, 125, .12);
            color: #6c757d;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .db-badge--cancelled {
            background: rgba(220, 53, 69, .10);
            color: #c0392b;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .pct-bar {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pct-bar-track {
            flex: 1;
            background: #eaeef6;
            border-radius: 4px;
            height: 6px;
            min-width: 60px;
        }

        .pct-bar-fill {
            height: 6px;
            border-radius: 4px;
        }

        .sk-rpt-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 24px;
        }

        .sk-rpt-filter {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .sk-rpt-filter label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: .4px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .sk-rpt-filter select {
            min-width: 120px;
            padding: 9px 12px;
            border: 1.5px solid #e2e5ef;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
        }

        .sk-rpt-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sk-print-header {
            display: none;
        }

        @media print {
            .db-sidebar,
            .db-topbar,
            .sk-rpt-toolbar,
            .db-hamburger,
            .no-print {
                display: none !important;
            }

            .db-main {
                margin: 0 !important;
                width: 100% !important;
            }

            .db-content {
                padding: 0 !important;
            }

            .sk-print-header {
                display: block;
                text-align: center;
                margin-bottom: 18px;
                border-bottom: 2px solid #1d2448;
                padding-bottom: 12px;
            }

            .sk-print-header h1 {
                margin: 6px 0 2px;
                font-size: 16px;
                color: #1d2448;
            }

            .sk-print-header p {
                margin: 0;
                font-size: 11px;
                color: #4a5068;
            }

            .db-stat-card,
            .db-table-wrap {
                box-shadow: none !important;
                break-inside: avoid;
            }

            body {
                background: #fff;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $sessionRole = strtolower((string) (session()->get('role') ?? 'sk'));
    $role      = $sessionRole === 'admin' ? 'admin' : 'sk';
    $active    = $sessionRole === 'admin' ? 'sk_reports' : 'reports';
    $pageTitle = 'SK Reports';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $demographics      = $demographics      ?? [];
    $totals            = $totals            ?? ['total' => 0, 'male' => 0, 'female' => 0, 'student' => 0, 'employed' => 0, 'unemployed' => 0, 'oos' => 0];
    $programs          = $programs          ?? [];
    $progCounts        = $progCounts        ?? ['total' => 0, 'Active' => 0, 'Upcoming' => 0, 'Completed' => 0, 'Cancelled' => 0];
    $totalParticipants = $totalParticipants ?? 0;
    $totalTarget       = $totalTarget       ?? (int) array_sum(array_column($programs, 'target_participants'));
    $filterYear        = (int) ($filterYear ?? date('Y'));
    $exportBase        = $role === 'admin' ? '/admin/sk-reports' : '/sk/reports';

    $badgeMap = [
        'Active'    => 'db-badge--approved',
        'Upcoming'  => 'db-badge--pending',
        'Completed' => 'db-badge--completed',
        'Cancelled' => 'db-badge--cancelled',
    ];
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <div class="sk-print-header">
                <p>Republic of the Philippines · Barangay Bacolod, Bato, Camarines Sur</p>
                <h1>Sangguniang Kabataan Report — CY <?= (int) $filterYear ?></h1>
                <p>Youth demographics and program participation as of <?= date('F d, Y') ?></p>
            </div>

            <div class="sk-rpt-toolbar">
                <form method="get" action="/<?= esc($role === 'admin' ? 'admin/sk-reports' : 'sk/reports') ?>" class="sk-rpt-filter">
                    <div>
                        <label for="skYear">Report Year</label>
                        <select id="skYear" name="year" onchange="this.form.submit()">
                            <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 6; $y--): ?>
                                <option value="<?= $y ?>" <?= $filterYear === $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="db-btn db-btn--outline">
                        <i class="fas fa-sync-alt"></i> Apply
                    </button>
                </form>
                <div class="sk-rpt-actions">
                    <button class="db-btn db-btn--outline" type="button" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <a class="db-btn db-btn--outline" href="<?= esc($exportBase) ?>/export?year=<?= (int) $filterYear ?>" target="_blank">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                    <a class="db-btn db-btn--primary" href="<?= esc($exportBase) ?>/download?year=<?= (int) $filterYear ?>">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>
            </div>

            <div class="db-stats" style="margin-bottom:28px;">
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,.15);color:#5b6fd6;"><i class="fas fa-users"></i></div>
                    <div><span class="db-stat-num"><?= number_format($totals['total']) ?></span><span class="db-stat-label">Registered Youth (15–30)</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,199,154,.15);color:#16c79a;"><i class="fas fa-graduation-cap"></i></div>
                    <div><span class="db-stat-num"><?= number_format($totals['student']) ?></span><span class="db-stat-label">Students</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(255,193,7,.15);color:#e6a800;"><i class="fas fa-user-times"></i></div>
                    <div><span class="db-stat-num"><?= number_format($totals['oos']) ?></span><span class="db-stat-label">Out-of-School</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,.15);color:#5b6fd6;"><i class="fas fa-calendar-check"></i></div>
                    <div><span class="db-stat-num"><?= (int) ($progCounts['total'] ?? 0) ?></span><span class="db-stat-label">Programs Recorded</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,199,154,.15);color:#16c79a;"><i class="fas fa-user-check"></i></div>
                    <div><span class="db-stat-num"><?= number_format($totalParticipants) ?></span><span class="db-stat-label">Total Participants</span></div>
                </div>
            </div>

            <h3 class="db-section-title">
                <i class="fas fa-chart-pie" style="color:#5b6fd6;margin-right:8px;"></i>
                Youth Demographics
            </h3>

            <?php if ($totals['total'] === 0): ?>
                <div class="db-table-wrap" style="margin-bottom:32px;">
                    <div style="text-align:center;padding:36px;color:#9aa0b4;">
                        <i class="fas fa-users" style="font-size:30px;display:block;margin-bottom:10px;color:#d0d5e8;"></i>
                        No youth records (aged 15–30) found in the census.
                    </div>
                </div>
            <?php else: ?>
                <div class="db-table-wrap" style="margin-bottom:32px;">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Age Group</th>
                                <th>Total Count</th>
                                <th>Male</th>
                                <th>Female</th>
                                <th>Student</th>
                                <th>Employed</th>
                                <th>Unemployed</th>
                                <th>Out-of-School</th>
                                <th>% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($demographics as $label => $d):
                                $pct = $totals['total'] > 0 ? round($d['total'] / $totals['total'] * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><strong><?= esc($label) ?></strong></td>
                                    <td><?= number_format($d['total']) ?></td>
                                    <td><?= number_format($d['male']) ?></td>
                                    <td><?= number_format($d['female']) ?></td>
                                    <td><?= number_format($d['student']) ?></td>
                                    <td><?= number_format($d['employed']) ?></td>
                                    <td><?= number_format($d['unemployed']) ?></td>
                                    <td><?= number_format($d['oos']) ?></td>
                                    <td>
                                        <div class="pct-bar">
                                            <div class="pct-bar-track">
                                                <div class="pct-bar-fill" style="width:<?= $pct ?>%;background:#5b6fd6;"></div>
                                            </div>
                                            <span style="font-size:12px;color:#7a8aaa;white-space:nowrap;"><?= $pct ?>%</span>
                                        </div>
                                    </td>
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
                </div>
            <?php endif; ?>

            <h3 class="db-section-title">
                <i class="fas fa-chart-bar" style="color:#5b6fd6;margin-right:8px;"></i>
                Program Participation — CY <?= (int) $filterYear ?>
            </h3>

            <?php if (empty($programs)): ?>
                <div class="db-table-wrap" style="margin-bottom:32px;">
                    <div style="text-align:center;padding:36px;color:#9aa0b4;">
                        <i class="fas fa-calendar-times" style="font-size:30px;display:block;margin-bottom:10px;color:#d0d5e8;"></i>
                        No programs recorded for <?= (int) $filterYear ?>. Add programs from the <a href="/<?= esc($role) ?>/programs" style="color:#1d2448;font-weight:600;">Programs & Events</a> page.
                    </div>
                </div>
            <?php else: ?>
                <div class="db-table-wrap" style="margin-bottom:32px;">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Target</th>
                                <th>Actual Participants</th>
                                <th>Completion Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $p):
                                $target = (int) $p['target_participants'];
                                $actual = (int) $p['actual_participants'];
                                $rate   = ($target > 0 && $actual > 0) ? min(100, round($actual / $target * 100)) : 0;
                                $rateColor = $rate >= 90 ? '#16c79a' : ($rate >= 70 ? '#ffc107' : '#dc3545');
                                $dateStr = $p['start_date'] ? date('M d, Y', strtotime($p['start_date'])) : '—';
                                $badge  = $badgeMap[$p['status']] ?? 'db-badge--pending';
                            ?>
                                <tr>
                                    <td><strong><?= esc($p['name']) ?></strong></td>
                                    <td><?= esc($p['category']) ?></td>
                                    <td><?= $dateStr ?></td>
                                    <td><?= $target > 0 ? $target : '—' ?></td>
                                    <td><?= $actual > 0 ? $actual : '<span style="color:#bbb;">—</span>' ?></td>
                                    <td>
                                        <?php if ($rate > 0): ?>
                                            <div class="pct-bar">
                                                <div class="pct-bar-track" style="min-width:70px;">
                                                    <div class="pct-bar-fill" style="width:<?= $rate ?>%;background:<?= $rateColor ?>;"></div>
                                                </div>
                                                <span style="font-size:12px;color:#7a8aaa;white-space:nowrap;"><?= $rate ?>%</span>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#bbb;font-size:13px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="db-badge <?= $badge ?>"><?= esc($p['status']) ?></span></td>
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
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>
