<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Dashboard - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php
    $role      = 'sk';
    $active    = 'dashboard';
    $pageTitle = 'SK Dashboard';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $stats       = $stats       ?? ['total' => 0, 'male' => 0, 'female' => 0, 'oos' => 0, 'employed' => 0, 'students' => 0, 'programs' => 0];
    $recentYouth = $recentYouth ?? [];
    $progCounts  = $progCounts  ?? ['total' => 0, 'Active' => 0, 'Upcoming' => 0, 'Completed' => 0];

    $statusStyle = [
        'Student'       => 'background:#f0faf6;color:#1a7a55;border:1px solid #c3e8d8;',
        'Employed'      => 'background:#eef0fb;color:#1d2448;border:1px solid #d0d8f5;',
        'Unemployed'    => 'background:#fff8f0;color:#b7600a;border:1px solid #fde8c8;',
        'Out-of-School' => 'background:#fff0f1;color:#c0392b;border:1px solid #fad4d4;',
        '—'             => 'background:#f5f6fa;color:#9aa0b4;border:1px solid #e2e5ef;',
    ];

    $username = esc(session()->get('username') ?? 'SK Official');
    $hour     = (int) date('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <!-- ═══════════════════════════════════════════
                 WELCOME BANNER
            ═══════════════════════════════════════════ -->
            <div class="db-welcome" style="margin-bottom:24px;">
                <div>
                    <h2><?= $greeting ?>, <?= $username ?> 👋</h2>
                    <p>Sangguniang Kabataan — Barangay Bacolod, Bato, Camarines Sur</p>
                </div>
                <div class="db-welcome-icon"><i class="fas fa-star"></i></div>
            </div>


            <!-- ═══════════════════════════════════════════
                 YOUTH STAT CARDS
            ═══════════════════════════════════════════ -->
            <div class="db-stats" style="margin-bottom:24px;">

                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,.15);color:#5b6fd6;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($stats['total']) ?></span>
                        <span class="db-stat-label">Registered Youth</span>
                    </div>
                </div>

                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,.15);color:#5b6fd6;">
                        <i class="fas fa-male"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($stats['male']) ?></span>
                        <span class="db-stat-label">Male</span>
                    </div>
                </div>

                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(220,53,69,.15);color:#dc3545;">
                        <i class="fas fa-female"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($stats['female']) ?></span>
                        <span class="db-stat-label">Female</span>
                    </div>
                </div>

                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(255,193,7,.15);color:#e6a800;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($stats['students']) ?></span>
                        <span class="db-stat-label">Students</span>
                    </div>
                </div>

                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,199,154,.15);color:#16c79a;">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($stats['employed']) ?></span>
                        <span class="db-stat-label">Employed</span>
                    </div>
                </div>

                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(220,53,69,.12);color:#c0392b;">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div>
                        <span class="db-stat-num"><?= number_format($stats['oos']) ?></span>
                        <span class="db-stat-label">Out-of-School</span>
                    </div>
                </div>

            </div>


            <!-- ═══════════════════════════════════════════
                 TWO-COLUMN BODY
            ═══════════════════════════════════════════ -->
            <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">


                <!-- ─────────────────────────────────────
                     LEFT — PROGRAMS SUMMARY + QUICK LINKS
                ───────────────────────────────────────── -->
                <div style="display:flex;flex-direction:column;gap:16px;">

                    <!-- Programs card -->
                    <div style="background:#fff;border-radius:14px;border:1px solid #eaecf4;padding:18px 20px;box-shadow:0 1px 6px rgba(29,36,72,.05);">

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                            <span style="font-size:13px;font-weight:700;color:#1d2448;">Programs</span>
                            <a href="/sk/programs" style="font-size:11px;color:#5b6fd6;text-decoration:none;font-weight:600;">
                                View all →
                            </a>
                        </div>

                        <?php foreach (
                            [
                                ['Active',    $progCounts['Active'],    '#16c79a', 'fa-play-circle',   'rgba(22,199,154,.12)'],
                                ['Upcoming',  $progCounts['Upcoming'],  '#e6a800', 'fa-clock',          'rgba(230,168,0,.12)'],
                                ['Completed', $progCounts['Completed'], '#6c757d', 'fa-check-double',   'rgba(108,117,125,.12)'],
                            ] as [$label, $count, $color, $icon, $bg]
                        ): ?>
                            <div style="display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid #f3f4f8;">
                                <div style="width:34px;height:34px;border-radius:9px;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas <?= $icon ?>" style="color:<?= $color ?>;font-size:14px;"></i>
                                </div>
                                <div style="flex:1;">
                                    <span style="font-size:12px;color:#6b7280;"><?= $label ?></span>
                                </div>
                                <span style="font-size:16px;font-weight:700;color:#1d2448;"><?= $count ?></span>
                            </div>
                        <?php endforeach; ?>

                        <a href="/sk/programs"
                            style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px;padding:9px;background:linear-gradient(135deg,#1d2448,#2e3a6e);border-radius:9px;text-decoration:none;">
                            <i class="fas fa-calendar-alt" style="color:#fff;font-size:12px;"></i>
                            <span style="color:#fff;font-size:12px;font-weight:600;">Manage Programs</span>
                        </a>

                    </div>

                </div>


                <!-- ─────────────────────────────────────
                     RIGHT — RECENT YOUTH TABLE
                ───────────────────────────────────────── -->
                <div style="background:#fff;border-radius:14px;border:1px solid #eaecf4;overflow:hidden;box-shadow:0 1px 6px rgba(29,36,72,.05);">

                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f0f2f8;">
                        <span style="font-size:13px;font-weight:700;color:#1d2448;">
                            <i class="fas fa-user-friends" style="color:#5b6fd6;margin-right:7px;"></i>
                            Recently Added Youth
                        </span>
                        <a href="/sk/profiling" style="font-size:11px;color:#5b6fd6;text-decoration:none;font-weight:600;">
                            View all →
                        </a>
                    </div>

                    <div style="overflow-x:auto;">
                        <table class="db-table" style="margin:0;">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Zone</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentYouth)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:36px 20px;color:#9aa0b4;">
                                            <i class="fas fa-users" style="font-size:24px;display:block;margin-bottom:8px;color:#d0d5e8;"></i>
                                            No youth profiles yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentYouth as $y):
                                        $statusSt  = $statusStyle[$y['status']] ?? $statusStyle['—'];
                                        $fullName  = esc(trim(($y['first_name'] ?? '') . ' ' . ($y['last_name'] ?? '')));
                                    ?>
                                        <tr>
                                            <td><strong><?= $fullName ?></strong></td>
                                            <td><?= $y['age'] ?></td>
                                            <td><?= esc($y['gender'] ?? '—') ?></td>
                                            <td><?= esc($y['zone'] ?? '—') ?></td>
                                            <td>
                                                <span style="display:inline-block;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;<?= $statusSt ?>">
                                                    <?= esc($y['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/sk/profiling?search=<?= urlencode(trim(($y['first_name'] ?? '') . ' ' . ($y['last_name'] ?? ''))) ?>"
                                                    class="db-action-btn">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div><!-- end two-column -->

        </div><!-- end db-content -->
    </div><!-- end db-main -->

    <script>
        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>