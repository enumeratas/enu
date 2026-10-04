<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Records - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php $role = $role ?? 'secretary';
    $active = 'census';
    $pageTitle = 'Census Records';
    include(APPPATH . 'Views/dashboard/sidebar.php'); ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php
            $filters = (isset($filters) && is_array($filters)) ? $filters : [];
            $censusYears = (isset($censusYears) && is_array($censusYears)) ? $censusYears : [];
            $totalHouseholds = (isset($totalHouseholds) && is_numeric($totalHouseholds)) ? (int) $totalHouseholds : 0;
            $totalPopulation = (isset($totalPopulation) && is_numeric($totalPopulation)) ? (int) $totalPopulation : 0;
            $totalMale = (isset($totalMale) && is_numeric($totalMale)) ? (int) $totalMale : 0;
            $totalFemale = (isset($totalFemale) && is_numeric($totalFemale)) ? (int) $totalFemale : 0;
            $outOfSchoolYouth = (isset($outOfSchoolYouth) && is_numeric($outOfSchoolYouth)) ? (int) $outOfSchoolYouth : 0;
            $pwds = (isset($pwds) && is_numeric($pwds)) ? (int) $pwds : 0;
            $fourPs = (isset($fourPs) && is_numeric($fourPs)) ? (int) $fourPs : 0;
            $seniors = (isset($seniors) && is_numeric($seniors)) ? (int) $seniors : 0;
            $soloParent = (isset($soloParent) && is_numeric($soloParent)) ? (int) $soloParent : 0;
            $filteredTotal = (isset($filteredTotal) && is_numeric($filteredTotal)) ? (int) $filteredTotal : 0;
            $perPage = (isset($perPage) && is_numeric($perPage) && (int) $perPage > 0) ? (int) $perPage : 15;
            $currentPage = (isset($currentPage) && is_numeric($currentPage) && (int) $currentPage > 0) ? (int) $currentPage : 1;
            ?>

            <!-- Census Year selector — prominent above the toolbar -->
            <?php $selectedYear = $filters['census_year'] ?? ''; ?>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                <span style="font-size:12px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.5px;">
                    <i class="fas fa-calendar-alt" style="margin-right:5px;"></i>Census Year
                </span>
                <?php if (! empty($censusYears)): ?>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="?" style="display:inline-flex;align-items:center;padding:5px 14px;border-radius:20px;font-size:12.5px;font-weight:600;text-decoration:none;
                            <?= $selectedYear === '' ? 'background:#1d2448;color:#fff;border:1.5px solid #1d2448;' : 'background:#f5f7ff;color:#4a5068;border:1.5px solid #dde2f5;' ?>">
                            All Years
                        </a>
                        <?php foreach ($censusYears as $yr): ?>
                            <?php
                            // Build URL preserving all current filters except census_year
                            $q = array_filter($filters, fn($v, $k) => $k !== 'census_year' && $v !== '', ARRAY_FILTER_USE_BOTH);
                            $q['census_year'] = $yr;
                            $yearUrl = '?' . http_build_query($q);
                            $isActive = (string)$selectedYear === (string)$yr;
                            ?>
                            <a href="<?= $yearUrl ?>" style="display:inline-flex;align-items:center;padding:5px 14px;border-radius:20px;font-size:12.5px;font-weight:600;text-decoration:none;
                                <?= $isActive ? 'background:#1d2448;color:#fff;border:1.5px solid #1d2448;' : 'background:#f5f7ff;color:#4a5068;border:1.5px solid #dde2f5;' ?>">
                                <?= esc($yr) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($selectedYear !== ''): ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:#fff8e6;border:1.5px solid #fde8a0;color:#7a5c00;border-radius:8px;padding:4px 12px;font-size:12px;font-weight:600;">
                        <i class="fas fa-archive"></i> Viewing census data for <strong><?= esc($selectedYear) ?></strong>
                        <a href="?" style="color:#7a5c00;margin-left:4px;"><i class="fas fa-times"></i></a>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Toolbar + Filter Form -->
            <form method="get" action="" id="filterForm" data-live-results="censusResults">
                <div class="db-toolbar">
                    <div class="db-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="censusSearch" name="search" data-live-query placeholder="Search by name, household #, or birth date..."
                            value="<?= esc($filters['search'] ?? '') ?>"
                            autocomplete="off">
                    </div>
                    <div class="db-toolbar-actions">
                        <button class="db-btn db-btn--primary" type="button"
                            onclick="window.location.href='/<?= session()->get('role') ?>/census/new'">
                            <i class="fas fa-plus"></i> Add Household
                        </button>
                        <button class="db-btn db-btn--outline" type="button" onclick="toggleFilters()">
                            <i class="fas fa-filter"></i> Filters
                            <?php
                            $activeCount = 0;
                            foreach (['zone', 'gender', 'age_min', 'age_max', 'is_pwd', 'is_senior', 'is_solo', 'is_4ps', 'is_student', 'is_osy', 'is_indigent', 'census_year'] as $k) {
                                if (! empty($filters[$k])) $activeCount++;
                            }
                            ?>
                            <?php if ($activeCount > 0): ?>
                                <span style="background:#c0392b;color:#fff;border-radius:100px;padding:1px 7px;font-size:11px;margin-left:4px;"><?= $activeCount ?></span>
                            <?php endif; ?>
                        </button>
                        <button class="db-btn db-btn--outline" type="button" onclick="exportPdf()">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>

                <!-- Expandable filter panel -->
                <div id="filterPanel" style="<?= $activeCount > 0 ? '' : 'display:none;' ?> background:#fff;border:1px solid #e2e5ef;border-radius:10px;padding:16px 20px;margin-bottom:16px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;align-items:end;">

                        <!-- Zone -->
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Zone</label>
                            <select name="zone" class="db-filter-select" style="width:100%;" onchange="this.form.submit()">
                                <option value="">All Zones</option>
                                <?php foreach (['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'] as $z): ?>
                                    <option <?= ($filters['zone'] ?? '') === $z ? 'selected' : '' ?>><?= $z ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Gender</label>
                            <select name="gender" class="db-filter-select" style="width:100%;" onchange="this.form.submit()">
                                <option value="">All</option>
                                <option value="Male" <?= ($filters['gender'] ?? '') === 'Male'   ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($filters['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>

                        <!-- Age range -->
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Age (Min)</label>
                            <input type="number" name="age_min" min="0" max="120"
                                value="<?= esc($filters['age_min'] ?? '') ?>"
                                placeholder="e.g. 18"
                                class="db-filter-select" style="width:100%;padding:8px 10px;"
                                onchange="this.form.submit()">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Age (Max)</label>
                            <input type="number" name="age_max" min="0" max="120"
                                value="<?= esc($filters['age_max'] ?? '') ?>"
                                placeholder="e.g. 60"
                                class="db-filter-select" style="width:100%;padding:8px 10px;"
                                onchange="this.form.submit()">
                        </div>

                        <!-- Checkboxes -->
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Special Groups</label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_pwd" value="1" <?= !empty($filters['is_pwd']) ? 'checked' : '' ?> onchange="this.form.submit()"> PWD
                            </label>
                            <select name="is_senior" class="db-filter-select" style="width:100%;" onchange="this.form.submit()">
                                <option value="">All Seniors</option>
                                <option value="1" <?= ($filters['is_senior'] ?? '') === '1' ? 'selected' : '' ?>>All senior citizens (60+)</option>
                                <option value="with_id" <?= ($filters['is_senior'] ?? '') === 'with_id' ? 'selected' : '' ?>>Senior citizens with ID</option>
                                <option value="without_id" <?= ($filters['is_senior'] ?? '') === 'without_id' ? 'selected' : '' ?>>Senior citizens without ID</option>
                            </select>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_solo" value="1" <?= !empty($filters['is_solo']) ? 'checked' : '' ?> onchange="this.form.submit()"> Solo Parent
                            </label>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Programs / Status</label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_4ps" value="1" <?= !empty($filters['is_4ps']) ? 'checked' : '' ?> onchange="this.form.submit()"> 4Ps Beneficiary
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_student" value="1" <?= !empty($filters['is_student']) ? 'checked' : '' ?> onchange="this.form.submit()"> Has Student Member
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_employed" value="1" <?= !empty($filters['is_employed']) ? 'checked' : '' ?> onchange="this.form.submit()"> Employed / Labor Force
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_osy" value="1" <?= !empty($filters['is_osy']) ? 'checked' : '' ?> onchange="this.form.submit()"> Out-of-School Youth
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
                                <input type="checkbox" name="is_indigent" value="1" <?= !empty($filters['is_indigent']) ? 'checked' : '' ?> onchange="this.form.submit()"> Qualifies for Indigency <small style="color:#9aa0b4;">(≤₱5,000/mo)</small>
                            </label>
                        </div>

                        <!-- Census Year (also in filter panel for compact use) -->
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Census Year</label>
                            <select name="census_year" class="db-filter-select" style="width:100%;" onchange="this.form.submit()">
                                <option value="">All Years</option>
                                <?php foreach ($censusYears as $yr): ?>
                                    <option value="<?= esc($yr) ?>" <?= ($filters['census_year'] ?? '') == $yr ? 'selected' : '' ?>>
                                        <?= esc($yr) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Clear filters -->
                        <div style="display:flex;align-items:flex-end;">
                            <a href="?" class="db-btn db-btn--outline" style="width:100%;justify-content:center;text-decoration:none;">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        </div>

                    </div>

                    <!-- Active filter chips -->
                    <?php if ($activeCount > 0): ?>
                        <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                            <span style="font-size:11.5px;color:#9aa0b4;font-weight:600;">Active:</span>
                            <?php
                            $chipLabels = [
                                'zone'        => 'Zone: ',
                                'gender'      => 'Gender: ',
                                'age_min'     => 'Age ≥ ',
                                'age_max'     => 'Age ≤ ',
                                'is_pwd'      => 'PWD',
                                'is_senior'   => 'Senior Citizen',
                                'is_solo'     => 'Solo Parent',
                                'is_4ps'      => '4Ps',
                                'is_student'  => 'Has Student',
                                'is_employed' => 'Employed / Labor Force',
                                'is_osy'      => 'Out-of-School Youth',
                                'is_indigent' => 'Indigency Eligible',
                                'census_year' => 'Year: ',
                            ];
                            foreach ($chipLabels as $key => $label):
                                if (empty($filters[$key])) continue;
                                $val = in_array($key, ['is_pwd', 'is_senior', 'is_solo', 'is_4ps', 'is_student', 'is_employed', 'is_osy', 'is_indigent']) ? '' : esc($filters[$key]);
                            ?>
                                <span style="background:#eef0fb;color:#1d2448;font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:100px;border:1px solid #d0d8f5;">
                                    <?= $label . $val ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Stats row -->
            <div class="db-stats" style="margin-bottom:24px;">
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,199,154,0.15);color:#16c79a;"><i class="fas fa-home"></i></div>
                    <div><span class="db-stat-num"><?= $totalHouseholds ?></span><span class="db-stat-label">Households</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(22,160,133,0.15);color:#16a085;"><i class="fas fa-users"></i></div>
                    <div><span class="db-stat-num"><?= $totalPopulation ?></span><span class="db-stat-label">Total Residents</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(255,193,7,0.15);color:#ffc107;"><i class="fas fa-male"></i></div>
                    <div><span class="db-stat-num"><?= $totalMale ?></span><span class="db-stat-label">Male Residents</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(220,53,69,0.15);color:#dc3545;"><i class="fas fa-female"></i></div>
                    <div><span class="db-stat-num"><?= $totalFemale ?></span><span class="db-stat-label">Female Residents</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(230,126,34,0.15);color:#e67e22;"><i class="fas fa-user-graduate"></i></div>
                    <div><span class="db-stat-num"><?= $outOfSchoolYouth ?></span><span class="db-stat-label">Out-of-School Youth</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,0.15);color:#5b6fd6;"><i class="fas fa-wheelchair"></i></div>
                    <div><span class="db-stat-num"><?= $pwds ?></span><span class="db-stat-label">PWDs</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,0.15);color:#5b6fd6;"><i class="fas fa-hand-holding-heart"></i></div>
                    <div><span class="db-stat-num"><?= $fourPs ?></span><span class="db-stat-label">4P's</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,0.15);color:#5b6fd6;"><i class="fas fa-user-clock"></i></div>
                    <div><span class="db-stat-num"><?= $seniors ?></span><span class="db-stat-label">Senior Citizens</span></div>
                </div>
                <div class="db-stat-card">
                    <div class="db-stat-icon" style="background:rgba(91,111,214,0.15);color:#5b6fd6;"><i class="fas fa-child"></i></div>
                    <div><span class="db-stat-num"><?= $soloParent ?></span><span class="db-stat-label">Solo Parents</span></div>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php $pendingHouseholds = $pendingHouseholds ?? []; ?>
            <?php if (! empty($pendingHouseholds)): ?>
                <div style="background:#fffbf0;border:1.5px solid #ffe08a;border-radius:12px;margin-bottom:24px;overflow:hidden;">
                    <div style="display:flex;align-items:center;gap:10px;padding:14px 20px;font-size:14px;font-weight:700;color:#7a4200;">
                        <i class="fas fa-clock" style="color:#e67e22;"></i>
                        Pending Household Submissions
                        <span style="background:#e67e22;color:#fff;font-size:11px;border-radius:100px;padding:2px 8px;"><?= count($pendingHouseholds) ?></span>
                    </div>
                    <div class="db-table-wrap" style="margin:0;border-radius:0;border:none;box-shadow:none;">
                        <table class="db-table" style="border-radius:0;">
                            <thead>
                                <tr>
                                    <th>Household</th>
                                    <th>Zone</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingHouseholds as $pendingHousehold): ?>
                                    <tr>
                                        <td><strong><?= esc($pendingHousehold['last_name'] . ', ' . $pendingHousehold['first_name']) ?></strong><br><small>#<?= esc($pendingHousehold['household_no']) ?></small></td>
                                        <td><?= esc($pendingHousehold['zone'] ?? '—') ?></td>
                                        <td><?= ! empty($pendingHousehold['created_at']) ? date('M d, Y', strtotime($pendingHousehold['created_at'])) : '—' ?></td>
                                        <td>
                                            <div class="db-action-group">
                                                <form action="/secretary/census/approve/<?= esc($pendingHousehold['household_no']) ?>" method="post" style="display:inline;">
                                                    <?= csrf_field() ?><button type="submit" class="db-btn db-btn--success db-btn--sm"><i class="fas fa-check"></i> Approve</button>
                                                </form>
                                                <form action="/secretary/census/reject/<?= esc($pendingHousehold['household_no']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Reject this household submission?')">
                                                    <?= csrf_field() ?><button type="submit" class="db-btn db-btn--danger db-btn--sm"><i class="fas fa-times"></i> Reject</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── Pending Household Separation Requests ──────────────────────── -->
            <?php
            $pendingSeparations = $pendingSeparations ?? [];
            if (! empty($pendingSeparations)):
            ?>
                <div style="background:#fffbf0;border:1.5px solid #ffe08a;border-radius:12px;margin-bottom:24px;overflow:hidden;">
                    <!-- Panel header -->
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;cursor:pointer;user-select:none;"
                        onclick="this.nextElementSibling.classList.toggle('sep-panel-hidden');this.querySelector('.sep-chevron').classList.toggle('sep-chevron-closed');">
                        <div style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:700;color:#7a4200;">
                            <i class="fas fa-home" style="color:#e67e22;"></i>
                            Pending Household Separation Requests
                            <span style="background:#e67e22;color:#fff;font-size:11px;font-weight:700;border-radius:100px;padding:2px 8px;">
                                <?= count($pendingSeparations) ?>
                            </span>
                        </div>
                        <i class="fas fa-chevron-down sep-chevron" style="color:#b07a00;transition:transform .2s;"></i>
                    </div>

                    <!-- Panel body -->
                    <div style="border-top:1px solid #ffe08a;">
                        <div class="db-table-wrap" style="margin:0;border-radius:0;border:none;box-shadow:none;">
                            <table class="db-table" style="border-radius:0;">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Original Household</th>
                                        <th>Type</th>
                                        <th>Change Reason</th>
                                        <th>New Zone</th>
                                        <th>Proof Verified</th>
                                        <th>Requested By</th>
                                        <th>Filed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingSeparations as $sr): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight:600;color:#1a1d2e;">
                                                    <?= esc($sr['member_last_name']) ?>, <?= esc($sr['member_first_name']) ?>
                                                    <?php if (! empty($sr['member_middle_name'])): ?>
                                                        <?= esc(strtoupper($sr['member_middle_name'][0])) ?>.
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="/secretary/household/<?= esc($sr['original_household_no']) ?>"
                                                    style="font-weight:700;color:#1d2448;text-decoration:none;">
                                                    #<?= esc($sr['original_household_no']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span style="background:#fff8f0;color:#b07a00;border:1px solid #fde8c8;border-radius:100px;font-size:11px;font-weight:600;padding:3px 9px;">
                                                    <?= esc($sr['separation_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= esc($sr['change_reason'] ?? '—') ?></td>
                                            <td><?= esc($sr['new_zone'] ?? '—') ?></td>
                                            <td style="text-align:center;">
                                                <?php if ($sr['proof_verified']): ?>
                                                    <i class="fas fa-check-circle" style="color:#16c79a;" title="Verified"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle" style="color:#dc3545;" title="Not Verified"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:12px;color:#6b7280;">
                                                <?= esc(trim(($sr['requested_by_name'] ?? '') . ' ' . ($sr['requested_by_last'] ?? ''))) ?: '—' ?>
                                            </td>
                                            <td style="font-size:12px;color:#9aa0b4;">
                                                <?= $sr['created_at'] ? date('M d, Y', strtotime($sr['created_at'])) : '—' ?>
                                            </td>
                                            <td>
                                                <div class="db-action-group">
                                                    <!-- Approve -->
                                                    <form action="/secretary/census/separation/approve/<?= (int)$sr['id'] ?>" method="post"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Approve this <?= esc($sr['separation_type'], 'js') ?> request?\n<?= ($sr['separation_type'] === 'Transfer Member') ? 'The member will be linked to Household #' . esc($sr['new_household_no'] ?? 'SELECTED', 'js') . '.' : 'A new household will be created and the member removed from Household #' . esc($sr['original_household_no'], 'js') . '.' ?>')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="db-btn db-btn--success db-btn--sm">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                    <!-- Reject -->
                                                    <button type="button" class="db-btn db-btn--danger db-btn--sm"
                                                        onclick="openRejectSepModal(<?= (int)$sr['id'] ?>, '<?= esc($sr['member_first_name'] . ' ' . $sr['member_last_name'], 'js') ?>')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reject Separation Modal -->
                <div class="db-modal-overlay" id="rejectSepModal">
                    <div class="db-modal" style="max-width:420px;">
                        <div class="db-modal-header" style="background:#fff0f1;border-bottom:1px solid #fad4d4;">
                            <h3 style="color:#c0392b;font-size:14px;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-times-circle"></i> Reject Separation Request
                            </h3>
                            <button class="db-modal-close" onclick="closeModal('rejectSepModal')"><i class="fas fa-times"></i></button>
                        </div>
                        <form id="rejectSepForm" method="post">
                            <?= csrf_field() ?>
                            <div class="db-modal-body" style="padding:20px 24px;">
                                <p style="font-size:13.5px;color:#4a5068;margin:0 0 6px;">
                                    Reject separation request for <strong id="rejectSepName">—</strong>?
                                </p>
                                <p style="font-size:12px;color:#9aa0b4;margin:0 0 16px;">
                                    This will be logged in the audit trail.
                                </p>
                                <label style="font-size:12px;font-weight:600;color:#4a5068;display:block;margin-bottom:6px;">
                                    Reason for Rejection <span style="font-size:11px;font-weight:400;color:#b0b6cc;">(optional)</span>
                                </label>
                                <textarea name="rejection_reason" rows="3"
                                    placeholder="State reason for rejection..."
                                    style="width:100%;padding:9px 12px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
                            </div>
                            <div class="db-modal-footer" style="gap:10px;">
                                <button type="button" class="db-btn db-btn--outline" onclick="closeModal('rejectSepModal')" style="flex:1;">
                                    Cancel
                                </button>
                                <button type="submit" class="db-btn db-btn--danger" style="flex:1;justify-content:center;">
                                    <i class="fas fa-times"></i> Confirm Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <style>
                    .sep-panel-hidden {
                        display: none;
                    }

                    .sep-chevron-closed {
                        transform: rotate(-90deg);
                    }
                </style>
                <script>
                    function openRejectSepModal(id, name) {
                        document.getElementById('rejectSepName').textContent = name;
                        document.getElementById('rejectSepForm').action = '/secretary/census/separation/reject/' + id;
                        document.getElementById('rejectSepModal').classList.add('active');
                    }

                    function closeModal(id) {
                        document.getElementById(id).classList.remove('active');
                    }
                </script>
            <?php endif; ?>

            <div id="censusResults">
            <!-- Table -->
            <?php
            $persons          = $persons          ?? [];
            $households       = $households       ?? [];
            $hasSpecialFilter = $hasSpecialFilter ?? false;
            $roleVal          = (string)(session()->get('role') ?? 'secretary');
            ?>

            <?php if ($hasSpecialFilter): ?>
                <!-- FILTER MODE: individual persons (head + members) -->
                <div style="background:#f0f4ff;border:1px solid #d0d8f5;border-radius:8px;padding:10px 16px;margin-bottom:12px;font-size:12.5px;color:#1d2448;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-filter"></i>
                    <strong>Filter results</strong> — showing matching individuals (household heads + members).
                    <a href="?" style="margin-left:auto;color:#c0392b;font-weight:600;text-decoration:none;"><i class="fas fa-times"></i> Clear filters</a>
                </div>
                <div class="db-table-wrap">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Household #</th>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>Date of Birth</th>
                                <th>Age</th>
                                <th>Occupation</th>
                                <th>Monthly Income</th>
                                <th>Zone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($persons)): ?>
                                <tr>
                                    <td colspan="10" style="text-align:center;padding:32px;color:#9aa0b4;">
                                        <i class="fas fa-search" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                                        No records match the current filters.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $prevHH = null;
                                foreach ($persons as $p):
                                    $fn  = esc($p['last_name']) . ', ' . esc($p['first_name']);
                                    if (! empty($p['middle_name'])) $fn .= ' ' . esc($p['middle_name']);
                                    if (! empty($p['suffix']))      $fn .= ', ' . esc($p['suffix']);
                                    $ini    = strtoupper($p['first_name'][0] ?? '?');
                                    $dob    = ! empty($p['date_of_birth']) ? date('M d, Y', strtotime($p['date_of_birth'])) : '—';
                                    $age    = ! empty($p['date_of_birth']) ? (int)date_diff(date_create($p['date_of_birth']), date_create('today'))->y : '—';
                                    $isHead = $p['relationship'] === 'Household Head';
                                    $newHH  = $p['household_no'] !== $prevHH;
                                    $prevHH = $p['household_no'];
                                    $inc    = isset($p['monthly_income']) && $p['monthly_income'] > 0 ? '₱' . number_format($p['monthly_income'], 2) : '—';
                                ?>
                                    <tr style="<?= $isHead ? 'background:#f8f9ff;' : '' ?><?= ($newHH && !$isHead) ? 'border-top:2px solid #e8ecf4;' : '' ?>">
                                        <td><?php if ($isHead): ?><a href="/<?= $roleVal ?>/household/<?= esc($p['household_no']) ?>" style="font-weight:700;color:#1d2448;text-decoration:none;"><?= esc($p['household_no']) ?></a><?php else: ?><span style="color:#b0b6cc;font-size:12px;padding-left:10px;">└ <?= esc($p['household_no']) ?></span><?php endif; ?></td>
                                        <td>
                                            <div class="db-resident-name">
                                                <div class="db-avatar-sm" style="<?= $isHead ? '' : 'background:#6b7280;width:28px;height:28px;font-size:11px;' ?>"><?= $ini ?></div><span style="font-weight:<?= $isHead ? '600' : '400' ?>;"><?= $fn ?></span>
                                            </div>
                                        </td>
                                        <td><span class="hh-rel-badge" style="<?= $isHead ? 'background:#eef0fb;color:#1d2448;' : '' ?>"><?= esc(ucfirst($p['relationship'])) ?></span></td>
                                        <td><?= $dob ?></td>
                                        <td><?= $age ?></td>
                                        <td><?php
                                            $occText = trim((string) ($p['occupation'] ?? ''));
                                            $workText = trim((string) ($p['work_detail'] ?? ''));
                                            $gradeText = trim((string) ($p['grade_level'] ?? ''));
                                            if ($workText !== '' && stripos($occText, $workText) === false) {
                                                $occText = trim($occText . ' — ' . $workText);
                                            }
                                            if ($gradeText !== '') {
                                                $occText = trim($occText . ($occText !== '' ? ' · ' : '') . $gradeText);
                                            }
                                            echo esc($occText !== '' ? $occText : '—');
                                        ?></td>
                                        <td><?= $inc ?></td>
                                        <td><?= esc($p['zone'] ?? '—') ?></td>
                                        <td>
                                            <?php if ($isHead): ?>
                                                <?php
                                                $approvalStatus = $p['approval_status'] ?? 'approved';
                                                $isDraft = ($p['record_status'] ?? 'complete') === 'draft';
                                                ?>
                                                <?php if ($isDraft): ?>
                                                    <span class="db-badge db-badge--pending">Draft</span>
                                                <?php endif; ?>
                                                <?php if ($approvalStatus !== 'approved' || ! $isDraft): ?>
                                                    <span class="db-badge <?= $approvalStatus === 'approved' ? 'db-badge--approved' : 'db-badge--pending' ?>"><?= esc(ucfirst($approvalStatus)) ?></span>
                                                <?php endif; ?>
                                                <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td><?php if ($isHead): ?><div class="db-action-group"><a href="/<?= $roleVal ?>/household/<?= esc($p['household_no']) ?>" class="db-icon-btn db-icon-btn--view"><i class="fas fa-eye"></i></a>
                                                    <form action="/<?= $roleVal ?>/census/delete/<?= esc($p['household_no']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Delete household <?= esc($p['household_no']) ?>?')"><?= csrf_field() ?><button type="submit" class="db-icon-btn db-icon-btn--del"><i class="fas fa-trash"></i></button></form>
                                                </div><?php else: ?>—<?php endif; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <!-- DEFAULT MODE: household heads only -->
                <div class="db-table-wrap">
                    <table class="db-table" id="censusTable">
                        <thead>
                            <tr>
                                <th>Household #</th>
                                <th>Head of the Family</th>
                                <th>Date of Birth</th>
                                <th>Gender</th>
                                <th>Zone</th>
                                <th>Civil Status</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($households)): ?>
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:32px;color:#9aa0b4;">
                                        <i class="fas fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                                        No household records yet. Click <strong>Add Household</strong> to get started.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($households as $h):
                                    $fullName = esc($h['last_name']) . ', ' . esc($h['first_name']);
                                    $initial  = strtoupper($h['first_name'][0] ?? '?');
                                    $dob      = ! empty($h['date_of_birth']) ? date('M d, Y', strtotime($h['date_of_birth'])) : '—';
                                ?>
                                    <tr>
                                        <td><strong><?= esc($h['household_no']) ?></strong></td>
                                        <td><a href="/<?= $roleVal ?>/household/<?= esc($h['household_no']) ?>" class="hh-name-link">
                                                <div class="db-avatar-sm"><?= $initial ?></div><span><?= $fullName ?></span>
                                            </a></td>
                                        <td><?= $dob ?></td>
                                        <td><?= esc($h['gender']) ?></td>
                                        <td><?= esc($h['zone'] ?? '—') ?></td>
                                        <td><?= esc($h['civil_status']) ?></td>
                                        <td><?= esc($h['contact_number'] ?? '—') ?></td>
                                        <td>
                                            <?php
                                            $approvalStatus = $h['approval_status'] ?? 'approved';
                                            $isDraft = ($h['record_status'] ?? 'complete') === 'draft';
                                            ?>
                                            <?php if ($isDraft): ?>
                                                <span class="db-badge db-badge--pending">Draft</span>
                                            <?php endif; ?>
                                            <?php if ($approvalStatus !== 'approved' || ! $isDraft): ?>
                                                <span class="db-badge <?= $approvalStatus === 'approved' ? 'db-badge--approved' : ($approvalStatus === 'pending' ? 'db-badge--pending' : 'db-badge--danger') ?>">
                                                    <?= esc(ucfirst($approvalStatus)) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="db-action-group">
                                                <a href="/<?= $roleVal ?>/household/<?= esc($h['household_no']) ?>" class="db-icon-btn db-icon-btn--view"><i class="fas fa-eye"></i></a>
                                                <form action="/<?= $roleVal ?>/census/delete/<?= esc($h['household_no']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Delete household <?= esc($h['household_no']) ?>?')"><?= csrf_field() ?><button type="submit" class="db-icon-btn db-icon-btn--del"><i class="fas fa-trash"></i></button></form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php
            $displayTotal = $hasSpecialFilter ? $filteredTotal : ($totalHouseholdsFiltered ?? $totalHouseholds);
            $totalPages   = (int) ceil($displayTotal / $perPage);
            $start        = $displayTotal > 0 ? ($currentPage - 1) * $perPage + 1 : 0;
            $end          = min($currentPage * $perPage, $displayTotal);
            $qFilters     = $filters ?? [];
            unset($qFilters['page']);
            $qs = http_build_query(array_filter($qFilters, fn($v) => $v !== ''));
            $qs = $qs ? '&' . $qs : '';
            $label = $hasSpecialFilter ? 'person' : 'household';
            ?>
            <?php if ($displayTotal > 0): ?>
                <div class="db-pagination">
                    <span class="db-page-info">Showing <?= $start ?>–<?= $end ?> of <?= $displayTotal ?> <?= $label ?><?= $displayTotal !== 1 ? 's' : '' ?></span>
                    <div class="db-page-btns">
                        <a href="?page=<?= max(1, $currentPage - 1) ?><?= $qs ?>" class="db-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="?page=<?= $p ?><?= $qs ?>" class="db-page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <a href="?page=<?= min($totalPages, $currentPage + 1) ?><?= $qs ?>" class="db-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php elseif (($filters['search'] ?? '') !== '' || ($activeCount ?? 0) > 0): ?>
                <div style="text-align:center;padding:32px;color:#9aa0b4;">
                    <i class="fas fa-search" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                    No households match the current filters. <a href="?" style="color:#1d2448;font-weight:600;">Clear filters</a>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function toggleFilters() {
            const panel = document.getElementById('filterPanel');
            panel.style.display = panel.style.display === 'none' ? '' : 'none';
        }

        document.querySelectorAll('.db-nav-item').forEach(i => i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open')));

        function exportPdf() {
            const role = '<?= session()->get('role') ?>';
            const params = new URLSearchParams(window.location.search);
            params.delete('page');
            const qs = params.toString() ? '?' + params.toString() : '';
            window.location.href = '/' + role + '/census/export/pdf' + qs;
        }
    </script>
</body>

</html>