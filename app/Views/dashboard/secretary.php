<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">

</head>

<body class="db-body">
    <?php
    // The captain shares this same dashboard, so honour whatever role the
    // controller passed in and fall back to secretary for direct hits.
    $role      = isset($role) && in_array(strtolower((string) $role), ['secretary', 'captain', 'admin'], true)
        ? strtolower((string) $role)
        : 'secretary';
    $active    = 'dashboard';
    $pageTitle = 'Dashboard';
    $officeLabel = $role === 'captain'
        ? 'Office of the Barangay Captain'
        : ($role === 'admin' ? 'Barangay Administration' : 'Office of the Barangay Secretary');
    $roleGreeting = $role === 'captain' ? 'Captain' : ($role === 'admin' ? 'Admin' : 'Secretary');
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $totalHouseholds   = $totalHouseholds   ?? 0;
    $totalPopulation   = $totalPopulation   ?? 0;
    $pendingClearances = $pendingClearances ?? 0;
    $pendingBlotter    = $pendingBlotter    ?? 0;
    $activeBlotter     = $activeBlotter     ?? 0;
    $resolvedBlotter   = $resolvedBlotter   ?? 0;
    $pendingAccounts   = $pendingAccounts   ?? 0;
    $pwds              = $pwds              ?? 0;
    $seniors           = $seniors           ?? 0;
    $soloParents       = $soloParents       ?? 0;
    $fourPs            = $fourPs            ?? 0;
    $recentClearances  = $recentClearances  ?? [];
    $recentBlotter     = $recentBlotter     ?? [];
    $recentConcerns    = $recentConcerns    ?? [];
    $todayAppts        = $todayAppts        ?? [];
    $todayBlotterAppts = $todayBlotterAppts ?? [];

    $firstName = session()->get('first_name') ?? session()->get('username') ?? $roleGreeting;
    $today     = date('l, F d, Y');

    $statusBadge = function (string $s): string {
        $map = [
            'pending'            => 'dbadge--pending',
            'under_investigation' => 'dbadge--active',
            'approved'           => 'dbadge--approved',
            'resolved'           => 'dbadge--resolved',
            'dismissed'          => 'dbadge--dismissed',
            'rejected'           => 'dbadge--rejected',
        ];
        $label = [
            'pending'            => 'Pending',
            'under_investigation' => 'Active',
            'approved'           => 'Approved',
            'resolved'           => 'Resolved',
            'dismissed'          => 'Dismissed',
            'rejected'           => 'Rejected',
        ];
        $cls = $map[$s] ?? 'dbadge--pending';
        $lbl = $label[$s] ?? ucfirst($s);
        return "<span class=\"dbadge {$cls}\">{$lbl}</span>";
    };
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <section class="dash-welcome">
                <div class="gov-mast">
                    <img class="gov-seal" src="/bacolod.png" alt="Barangay Bacolod seal" style="width:48px;height:48px;max-width:48px;object-fit:contain;">
                    <div>
                        <p class="gov-kicker">Barangay Bacolod, Bato, Camarines Sur</p>
                        <h2>Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>, <?= esc($firstName) ?></h2>
                        <p><?= esc($officeLabel) ?></p>
                        <div class="dash-welcome-date"><?= $today ?></div>
                    </div>
                </div>
            </section>

            <!-- Stats row -->
            <div class="dash-grid">
                <a href="/<?= esc($role) ?>/census" class="dash-stat">
                    <div class="dash-stat-icon" style="background:rgba(91,111,214,.15);color:#5b6fd6;">
                        <i class="fas fa-home"></i>
                    </div>
                    <div>
                        <span class="dash-stat-num"><?= number_format($totalHouseholds) ?></span>
                        <span class="dash-stat-lbl">Households</span>
                    </div>
                </a>
                <a href="/<?= esc($role) ?>/census" class="dash-stat">
                    <div class="dash-stat-icon" style="background:rgba(22,199,154,.15);color:#16c79a;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="dash-stat-num"><?= number_format($totalPopulation) ?></span>
                        <span class="dash-stat-lbl">Total Population</span>
                    </div>
                </a>
                <a href="/<?= esc($role) ?>/clearance" class="dash-stat">
                    <div class="dash-stat-icon" style="background:rgba(255,193,7,.15);color:#f0a500;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <span class="dash-stat-num"><?= $pendingClearances ?></span>
                        <span class="dash-stat-lbl">Pending Clearances</span>
                    </div>
                </a>
                <?php $approvalsHref = $role === 'captain' ? '/captain/pending-accounts' : '/' . $role . '/residents?account=pending'; ?>
                <a href="<?= esc($approvalsHref) ?>" class="dash-stat">
                    <div class="dash-stat-icon" style="background:rgba(192,57,43,.12);color:#c0392b;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <span class="dash-stat-num"><?= $pendingAccounts ?></span>
                        <span class="dash-stat-lbl">Pending Approvals</span>
                    </div>
                </a>
            </div>

            <!-- Main content -->
            <div class="dash-row">

                <!-- Left: activity -->
                <div style="display:flex;flex-direction:column;gap:20px;">

                    <!-- Recent Clearance Requests -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-file-signature" style="color:#5b6fd6;margin-right:8px;"></i>Recent Clearance Requests</h4>
                            <a href="/<?= esc($role) ?>/clearance">View all →</a>
                        </div>
                        <?php if (empty($recentClearances)): ?>
                            <div class="dash-empty"><i class="fas fa-inbox"></i>
                                <p>No clearance requests yet.</p>
                            </div>
                        <?php else: ?>
                            <table class="dash-mini-table">
                                <thead>
                                    <tr>
                                        <th>Resident</th>
                                        <th>Document</th>
                                        <th>Filed</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentClearances as $cr): ?>
                                        <tr>
                                            <td style="font-weight:600;color:#1a1d2e;"><?= esc($cr['resident_name'] ?: '—') ?></td>
                                            <td><?= esc($cr['document_type']) ?></td>
                                            <td style="color:#9aa0b4;font-size:12px;"><?= date('M d, Y', strtotime($cr['created_at'])) ?></td>
                                            <td><?= $statusBadge($cr['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Blotter Reports -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-gavel" style="color:#c0392b;margin-right:8px;"></i>Recent Blotter Reports</h4>
                            <a href="/<?= esc($role) ?>/blotter">View all →</a>
                        </div>
                        <?php if (empty($recentBlotter)): ?>
                            <div class="dash-empty"><i class="fas fa-shield-alt"></i>
                                <p>No blotter reports.</p>
                            </div>
                        <?php else: ?>
                            <table class="dash-mini-table">
                                <thead>
                                    <tr>
                                        <th>Complainant</th>
                                        <th>Incident Type</th>
                                        <th>Filed</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBlotter as $br): ?>
                                        <tr>
                                            <td style="font-weight:600;color:#1a1d2e;"><?= esc($br['complainant_name']) ?></td>
                                            <td><?= esc($br['incident_type']) ?></td>
                                            <td style="color:#9aa0b4;font-size:12px;"><?= date('M d, Y', strtotime($br['created_at'])) ?></td>
                                            <td><?= $statusBadge($br['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Blotter Reports -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-comments" style="color:#e67e22;margin-right:8px;"></i>Recent Appointment/Concerns</h4>
                            <a href="/<?= esc($role) ?>/concerns">View all →</a>
                        </div>
                        <?php
                        $recentConcerns = $recentConcerns ?? [];
                        $statusBadgeConcern = function (string $s): string {
                            return match ($s) {
                                'resolved'  => '<span style="color:#0e9464;font-weight:700;font-size:11px;background:#e6f9f1;padding:2px 8px;border-radius:100px;">Resolved</span>',
                                'dismissed' => '<span style="color:#6b7280;font-weight:700;font-size:11px;background:#f3f4f8;padding:2px 8px;border-radius:100px;">Dismissed</span>',
                                default     => '<span style="color:#b07a00;font-weight:700;font-size:11px;background:#fff8e6;padding:2px 8px;border-radius:100px;">Pending</span>',
                            };
                        };
                        ?>
                        <?php if (empty($recentConcerns)): ?>
                            <div class="dash-empty"><i class="fas fa-comments"></i>
                                <p>No concerns yet.</p>
                            </div>
                        <?php else: ?>
                            <table class="dash-mini-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Subject</th>
                                        <th>Appointment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentConcerns as $cn): ?>
                                        <tr style="cursor:pointer;" onclick="window.location.href='/<?= esc($role) ?>/concern/<?= (int)$cn['id'] ?>'">
                                            <td style="font-weight:600;color:#1a1d2e;"><?= esc($cn['full_name']) ?></td>
                                            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= esc($cn['subject']) ?>"><?= esc($cn['subject']) ?></td>
                                            <td style="color:#9aa0b4;font-size:12px;">
                                                <?= $cn['appointment_date']
                                                    ? date('M d, Y', strtotime($cn['appointment_date']))
                                                    : '<span style="color:#d0d4df;">None</span>' ?>
                                            </td>
                                            <td><?= $statusBadgeConcern($cn['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right sidebar -->
                <div style="display:flex;flex-direction:column;gap:20px;">

                    <!-- Today's Schedule -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-calendar-day" style="color:#16a085;margin-right:8px;"></i>Today's Schedule</h4>
                            <a href="/<?= esc($role) ?>/calendar">Calendar →</a>
                        </div>
                        <?php
                        $allToday = array_merge(
                            array_map(fn($e) => ['time' => $e['start_time'] ?? '', 'title' => $e['title'], 'sub' => $e['location'] ?? ''], $todayAppts),
                            array_map(fn($b) => ['time' => $b['appointment_time'] ?? '', 'title' => 'Blotter: ' . $b['incident_type'], 'sub' => $b['complainant_name']], $todayBlotterAppts)
                        );
                        usort($allToday, fn($a, $b) => strcmp($a['time'], $b['time']));
                        ?>
                        <?php if (empty($allToday)): ?>
                            <div class="dash-empty" style="padding:20px 16px;"><i class="fas fa-calendar-check"></i>
                                <p>No events today.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($allToday as $ev): ?>
                                <div class="dash-appt">
                                    <div class="dash-appt-time"><?= $ev['time'] ? date('h:i A', strtotime($ev['time'])) : 'Anytime' ?></div>
                                    <div>
                                        <div class="dash-appt-title"><?= esc($ev['title']) ?></div>
                                        <?php if ($ev['sub']): ?><div class="dash-appt-sub"><?= esc($ev['sub']) ?></div><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Population snapshot -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-chart-pie" style="color:#5b6fd6;margin-right:8px;"></i>Population Snapshot</h4>
                            <a href="/<?= esc($role) ?>/reports">Reports →</a>
                        </div>
                        <div class="dash-pop-row">
                            <div class="dash-pop-cell">
                                <span class="dash-pop-num" style="color:#5b6fd6;"><?= $pwds ?></span>
                                <div class="dash-pop-lbl">PWDs</div>
                            </div>
                            <div class="dash-pop-cell">
                                <span class="dash-pop-num" style="color:#16a085;"><?= $seniors ?></span>
                                <div class="dash-pop-lbl">Senior Citizens</div>
                            </div>
                            <div class="dash-pop-cell">
                                <span class="dash-pop-num" style="color:#e67e22;"><?= $soloParents ?></span>
                                <div class="dash-pop-lbl">Solo Parents</div>
                            </div>
                            <div class="dash-pop-cell">
                                <span class="dash-pop-num" style="color:#c0392b;"><?= $fourPs ?></span>
                                <div class="dash-pop-lbl">4Ps Members</div>
                            </div>
                        </div>
                    </div>

                    <!-- Blotter summary -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-balance-scale" style="color:#c0392b;margin-right:8px;"></i>Blotter Summary</h4>
                        </div>
                        <div class="dash-pop-row">
                            <div class="dash-pop-cell">
                                <span class="dash-pop-num" style="color:#f0a500;"><?= $pendingBlotter ?></span>
                                <div class="dash-pop-lbl">Pending</div>
                            </div>
                            <div class="dash-pop-cell">
                                <span class="dash-pop-num" style="color:#c0392b;"><?= $activeBlotter ?></span>
                                <div class="dash-pop-lbl">Under Investigation</div>
                            </div>
                            <div class="dash-pop-cell" style="grid-column:1/-1;border-right:none;">
                                <span class="dash-pop-num" style="color:#16a085;"><?= $resolvedBlotter ?></span>
                                <div class="dash-pop-lbl">Resolved</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick links -->
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h4><i class="fas fa-bolt" style="color:#f0a500;margin-right:8px;"></i>Quick Access</h4>
                        </div>
                        <div class="dash-links">
                            <a href="/<?= esc($role) ?>/census" class="dash-link"><i class="fas fa-home"></i> Census</a>
                            <a href="/<?= esc($role) ?>/clearance" class="dash-link"><i class="fas fa-file-alt"></i> Clearances</a>
                            <a href="/<?= esc($role) ?>/blotter" class="dash-link"><i class="fas fa-gavel"></i> Blotter</a>
                            <a href="/<?= esc($role) ?>/calendar" class="dash-link"><i class="fas fa-calendar"></i> Calendar</a>
                            <a href="<?= esc($approvalsHref) ?>" class="dash-link"><i class="fas fa-user-check"></i> Approvals</a>
                            <a href="/<?= esc($role) ?>/create-account" class="dash-link"><i class="fas fa-user-plus"></i> Create Account</a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <script>
        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>