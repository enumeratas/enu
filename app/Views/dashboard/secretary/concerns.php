<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment / Concerns Management — Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Stat cards ── */
        .cn-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 24px;
            width: 100%;
        }

        .cn-stat {
            background: #fff;
            border-radius: 0 !important;
            box-shadow: 0 2px 10px rgba(29, 36, 72, .06);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: box-shadow .18s, transform .15s;
            border: 2px solid transparent;
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .cn-stat:hover {
            box-shadow: 0 6px 20px rgba(29, 36, 72, .11);
            transform: translateY(-2px);
        }

        .cn-stat.active {
            border-color: currentColor;
        }

        .cn-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        @media (max-width: 900px) {
            .cn-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .cn-stats {
                grid-template-columns: 1fr;
            }
        }

        .cn-stat-num {
            font-size: 22px;
            font-weight: 700;
            color: #1a1d2e;
            line-height: 1.1;
        }

        .cn-stat-label {
            font-size: 11px;
            color: #9aa0b4;
            font-weight: 500;
            margin-top: 2px;
        }

        /* ── Status badges ── */
        .cn-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
            white-space: nowrap;
        }

        .cn-badge--pending {
            background: #fff8e6;
            color: #b07a00;
            border: 1px solid #ffe08a;
        }

        .cn-badge--resolved {
            background: #e6f9f1;
            color: #0e9464;
            border: 1px solid #b2e8d2;
        }

        .cn-badge--dismissed {
            background: #f3f4f8;
            color: #6b7280;
            border: 1px solid #e2e5ef;
        }

        /* ── Table tweaks ── */
        .cn-subject {
            font-weight: 600;
            color: #1a1d2e;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .cn-sub {
            font-size: 11.5px;
            color: #9aa0b4;
            margin-top: 2px;
            white-space: nowrap;
        }

        .cn-appt-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            background: #fff8f0;
            color: #b07a00;
            border: 1px solid #fde8c8;
            border-radius: 100px;
            padding: 2px 9px;
        }

        .cn-review-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s;
            white-space: nowrap;
        }

        .cn-review-btn--pending {
            background: linear-gradient(135deg, #e67e22, #ca6f1e);
            color: #fff;
        }

        .cn-review-btn--resolved {
            background: #f0f2f8;
            color: #4a5068;
            border: 1.5px solid #e2e5ef;
        }

        .cn-review-btn--dismissed {
            background: #f0f2f8;
            color: #4a5068;
            border: 1.5px solid #e2e5ef;
        }

        .cn-review-btn:hover {
            opacity: .85;
        }

        .cn-empty {
            text-align: center;
            padding: 48px 20px;
            color: #9aa0b4;
        }

        .cn-empty i {
            font-size: 36px;
            display: block;
            margin-bottom: 12px;
            opacity: .3;
        }

        .cr-slot-unavailable {
            border-color: #c0392b !important;
            background: #fff5f4 !important;
        }

        #quickAppointmentSubmit:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        tr.cn-person-row td {
            vertical-align: top;
        }

        tr.cn-person-row + tr.cn-person-row td {
            border-top: 1px dashed #eceff6;
        }

        tr.cn-person-start td {
            border-top: 2px solid #dce3f2;
        }

        .cn-person-cell {
            background: #f7f9fd;
        }
    </style>
</head>

<body class="db-body">
    <?php
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $sessionRole  = session()->get('role');
    $role         = is_string($sessionRole) && $sessionRole !== '' ? strtolower($sessionRole) : 'secretary';
    $concerns      = $concerns      ?? [];
    $concernGroups = $concernGroups ?? [];
    $counts       = $counts       ?? ['all' => 0, 'pending' => 0, 'approved' => 0, 'dismissed' => 0];
    $total        = $total        ?? 0;
    $currentPage  = $currentPage  ?? 1;
    $perPage      = $perPage      ?? 15;
    $filterStatus = $filterStatus ?? '';
    $search       = $search       ?? '';
    $totalPages   = max(1, (int)ceil($total / $perPage));
    $start        = $total > 0 ? ($currentPage - 1) * $perPage + 1 : 0;
    $end          = min($currentPage * $perPage, $total);

    $qs = http_build_query(array_filter(['search' => $search, 'status' => $filterStatus], fn($v) => $v !== ''));
    $qs = $qs ? '&' . $qs : '';
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin:0 0 20px;padding:13px 20px;display:flex;align-items:center;justify-content:center;gap:8px;text-align:center;line-height:1.5;">
                    <i class="fas fa-check-circle"></i> <?= esc(strip_tags((string) session()->getFlashdata('success'))) ?>
                </div>
            <?php endif; ?>
            <?php
            $listError = session()->getFlashdata('error') ?: session()->getFlashdata('concern_error');
            if ($listError): ?>
                <div class="db-alert db-alert--danger" style="margin:0 0 20px;padding:13px 20px;display:flex;align-items:center;justify-content:center;gap:8px;text-align:center;line-height:1.5;background:#fff5f4;color:#c0392b;border:1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(strip_tags((string) $listError)) ?>
                </div>
            <?php endif; ?>

            <!-- ── Stat cards ── -->
            <div class="cn-stats">
                <a href="?status="
                    class="cn-stat <?= $filterStatus === '' ? 'active' : '' ?>"
                    style="color:#1d2448;">
                    <div class="cn-stat-icon" style="background:rgba(29,36,72,.08);color:#1d2448;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <div class="cn-stat-num"><?= $counts['all'] ?></div>
                        <div class="cn-stat-label">All Concerns</div>
                    </div>
                </a>
                <a href="?status=pending"
                    class="cn-stat <?= $filterStatus === 'pending' ? 'active' : '' ?>"
                    style="color:#b07a00;">
                    <div class="cn-stat-icon" style="background:rgba(255,193,7,.13);color:#b07a00;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="cn-stat-num"><?= $counts['pending'] ?></div>
                        <div class="cn-stat-label">Pending</div>
                    </div>
                </a>
                <a href="?status=approved"
                    class="cn-stat <?= $filterStatus === 'approved' ? 'active' : '' ?>"
                    style="color:#0e9464;">
                    <div class="cn-stat-icon" style="background:rgba(22,199,154,.12);color:#0e9464;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="cn-stat-num"><?= $counts['approved'] ?></div>
                        <div class="cn-stat-label">Approved</div>
                    </div>
                </a>
                <a href="?status=dismissed"
                    class="cn-stat <?= $filterStatus === 'dismissed' ? 'active' : '' ?>"
                    style="color:#6b7280;">
                    <div class="cn-stat-icon" style="background:rgba(107,114,128,.1);color:#6b7280;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <div class="cn-stat-num"><?= $counts['dismissed'] ?></div>
                        <div class="cn-stat-label">Dismissed</div>
                    </div>
                </a>
            </div>

            <!-- ── Search / filter toolbar ── -->
            <form method="get" id="filterForm">
                <input type="hidden" name="status" value="<?= esc($filterStatus) ?>">
                <div class="db-toolbar" style="margin-bottom:16px;">
                    <div class="db-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search"
                            placeholder="Search name, email or subject…"
                            value="<?= esc($search) ?>"
                            onchange="this.form.submit()">
                    </div>
                    <div class="db-toolbar-actions">
                        <?php if ($search !== '' || $filterStatus !== ''): ?>
                            <a href="?status=" class="db-btn db-btn--outline">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                        <button type="button" class="db-btn" style="background:linear-gradient(135deg,#e67e22,#ca6f1e);color:#fff;border:none;" onclick="openAppointmentForm()">
                            <i class="fas fa-calendar-plus"></i> Create Appointment
                        </button>
                    </div>
                </div>
            </form>

            <!-- ── Concerns table ── -->
            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Submitter</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Appointment</th>
                            <th>Status</th>
                            <th>Filed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($concernGroups)): ?>
                            <tr>
                                <td colspan="7" class="cn-empty">
                                    <i class="fas fa-comments"></i>
                                    No concerns found<?= $filterStatus !== '' ? ' with status <strong>' . esc($filterStatus) . '</strong>' : '' ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($concernGroups as $group): ?>
                                <?php
                                $groupRows = $group['appointments'] ?? [];
                                $rowSpan   = max(1, count($groupRows));
                                ?>
                                <?php foreach ($groupRows as $index => $c): ?>
                                <tr class="cn-person-row<?= $index === 0 ? ' cn-person-start' : '' ?>">
                                    <?php if ($index === 0): ?>
                                    <td class="cn-person-cell" rowspan="<?= $rowSpan ?>">
                                        <div style="display:flex;align-items:center;gap:9px;">
                                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#e67e22,#ca6f1e);color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <?= strtoupper(($group['full_name'] ?: ($c['full_name'] ?? '?'))[0] ?? '?') ?>
                                            </div>
                                            <div>
                                                <div style="font-weight:600;font-size:13px;color:#1a1d2e;"><?= esc($group['full_name'] ?: ($c['full_name'] ?? '')) ?></div>
                                                <div style="font-size:11.5px;color:#9aa0b4;"><?= esc($group['email'] ?: ($c['email'] ?? '')) ?></div>
                                                <?php if ($rowSpan > 1): ?>
                                                    <div style="font-size:11px;color:#16325c;margin-top:4px;font-weight:600;"><?= $rowSpan ?> appointments</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <?php endif; ?>

                                    <td style="max-width:240px;">
                                        <span class="cn-subject" title="<?= esc($c['subject']) ?>"><?= esc($c['subject']) ?></span>
                                        <?php if (! empty($c['contact_number'])): ?>
                                            <span class="cn-sub"><i class="fas fa-phone" style="font-size:10px;"></i> <?= esc($c['contact_number']) ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span style="font-size:12.5px;color:#4a5068;">
                                            <?= esc($c['category'] ?: '—') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if (! empty($c['appointment_date'])): ?>
                                            <span class="cn-appt-pill">
                                                <i class="fas fa-calendar-check" style="font-size:10px;"></i>
                                                <?= date('M d, Y', strtotime($c['appointment_date'])) ?>
                                                <?php if (! empty($c['appointment_time'])): ?>
                                                    &nbsp;<?= date('g:i A', strtotime($c['appointment_time'])) ?>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#d0d4df;font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($c['status'] === 'pending'): ?>
                                            <span class="cn-badge cn-badge--pending">
                                                <i class="fas fa-hourglass-half"></i> Pending
                                            </span>
                                        <?php elseif (in_array($c['status'], ['approved', 'resolved'], true)): ?>
                                            <span class="cn-badge cn-badge--resolved">
                                                <i class="fas fa-check-circle"></i> <?= $c['status'] === 'resolved' ? 'Resolved' : 'Approved' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="cn-badge cn-badge--dismissed">
                                                <i class="fas fa-times-circle"></i> Dismissed
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td style="font-size:12.5px;color:#6b7280;white-space:nowrap;">
                                        <?= date('M d, Y', strtotime($c['created_at'])) ?><br>
                                        <span style="font-size:11px;color:#b0b6cc;"><?= date('g:i A', strtotime($c['created_at'])) ?></span>
                                    </td>

                                    <td>
                                        <?php $status = is_string($c['status'] ?? null) ? $c['status'] : ''; ?>
                                        <a href="/<?= esc($role) ?>/concern/<?= (int) $c['id'] ?>"
                                            class="cn-review-btn cn-review-btn--<?= in_array($status, ['approved', 'resolved'], true) ? 'approved' : esc($status) ?>">
                                            <?php if ($status === 'pending'): ?>
                                                <i class="fas fa-reply"></i> Review
                                            <?php else: ?>
                                                <i class="fas fa-eye"></i> View
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ── -->
            <?php if ($total > 0): ?>
                <div class="db-pagination">
                    <span class="db-page-info">
                        Showing <?= $start ?>–<?= $end ?> of <?= number_format($total) ?> resident<?= $total !== 1 ? 's' : '' ?>
                    </span>
                    <div class="db-page-btns">
                        <a href="?page=<?= max(1, $currentPage - 1) ?><?= $qs ?>"
                            class="db-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php
                        $rangeStart = max(1, $currentPage - 3);
                        $rangeEnd   = min($totalPages, $currentPage + 3);
                        if ($rangeStart > 1): ?>
                            <a href="?page=1<?= $qs ?>" class="db-page-btn">1</a>
                            <?php if ($rangeStart > 2): ?><span class="db-page-btn" style="cursor:default;">…</span><?php endif; ?>
                        <?php endif; ?>
                        <?php for ($p = $rangeStart; $p <= $rangeEnd; $p++): ?>
                            <a href="?page=<?= $p ?><?= $qs ?>"
                                class="db-page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($rangeEnd < $totalPages): ?>
                            <?php if ($rangeEnd < $totalPages - 1): ?><span class="db-page-btn" style="cursor:default;">…</span><?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $qs ?>" class="db-page-btn"><?= $totalPages ?></a>
                        <?php endif; ?>
                        <a href="?page=<?= min($totalPages, $currentPage + 1) ?><?= $qs ?>"
                            class="db-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <!-- ── Quick appointment request form ── -->
    <div id="appointmentFormModal" class="db-modal-overlay" onclick="if (event.target === this) closeAppointmentForm()" style="display:none;align-items:center;justify-content:center;position:fixed;inset:0;z-index:1000;background:rgba(18,23,48,.55);padding:20px;">
        <div style="width:min(620px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:14px;box-shadow:0 18px 50px rgba(18,23,48,.22);">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #f0f2f8;">
                <div>
                    <h3 style="margin:0;color:#1a1d2e;font-size:17px;"><i class="fas fa-calendar-check" style="color:#e67e22;margin-right:8px;"></i>Create Appointment Request</h3>
                    <p style="margin:4px 0 0;color:#9aa0b4;font-size:12px;">Same person, same slot, or the same open concern is blocked. A different concern on another date or time is saved on this page under that person.</p>
                </div>
                <button type="button" onclick="closeAppointmentForm()" aria-label="Close appointment form" style="border:0;background:none;color:#9aa0b4;font-size:18px;cursor:pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="/public/concern/store" method="post" style="padding:20px 22px;">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Full Name <span style="color:#e74c3c;">*</span></label>
                        <input type="text" name="full_name" placeholder="e.g. Juan Dela Cruz" required style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Email Address <span style="color:#e74c3c;">*</span></label>
                        <input type="email" name="email" placeholder="your@email.com" required style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Contact Number <span style="color:#b0b6cc;font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span></label>
                    <input type="tel" name="contact_number" class="js-contact-number" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Category</label>
                        <select name="category" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                            <option value="">— Select Category —</option>
                            <option>Infrastructure / Roads</option>
                            <option>Garbage / Sanitation</option>
                            <option>Street Lighting</option>
                            <option>Water Supply</option>
                            <option>Peace and Order</option>
                            <option>Barangay Services</option>
                            <option>Health and Sanitation</option>
                            <option>Business Permit Inquiry</option>
                            <option>Suggestion / Feedback</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Subject <span style="color:#e74c3c;">*</span></label>
                        <input type="text" name="subject" placeholder="Brief subject of your concern" required style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Message <span style="color:#e74c3c;">*</span></label>
                    <textarea name="message" rows="4" placeholder="Describe the concern or appointment purpose..." required style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;resize:vertical;"></textarea>
                </div>
                <div style="background:#f5f7ff;border:1px solid #dde2f5;border-radius:10px;padding:12px 14px;margin-bottom:16px;">
                    <p style="font-size:12px;color:#4a5068;margin:0 0 10px;"><i class="fas fa-calendar-check" style="color:#5b6fd6;margin-right:6px;"></i>Optionally request an appointment at the Barangay Hall (Mon–Fri, 8:00 AM–5:00 PM).</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Preferred Date</label>
                            <input type="date" name="appointment_date" id="quickAppointmentDate" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;">Preferred Time</label>
                            <input type="time" name="appointment_time" id="quickAppointmentTime" min="08:00" max="17:00" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dfe4f0;border-radius:9px;font:13px 'Poppins',sans-serif;">
                            <p id="quickAppointmentAvailability" style="margin:6px 0 0;font-size:11px;min-height:16px;"></p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="db-btn db-btn--outline" onclick="closeAppointmentForm()">Cancel</button>
                    <button type="submit" id="quickAppointmentSubmit" class="db-btn" style="background:linear-gradient(135deg,#e67e22,#ca6f1e);color:#fff;border:none;"><i class="fas fa-paper-plane"></i> Submit Appointment Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAppointmentForm() {
            document.getElementById('appointmentFormModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeAppointmentForm() {
            document.getElementById('appointmentFormModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        function checkQuickAppointmentAvailability() {
            const date = document.getElementById('quickAppointmentDate');
            const time = document.getElementById('quickAppointmentTime');
            const message = document.getElementById('quickAppointmentAvailability');
            const submit = document.getElementById('quickAppointmentSubmit');
            if (!date || !time || !message || !submit) return;

            date.classList.remove('cr-slot-unavailable');
            time.classList.remove('cr-slot-unavailable');
            submit.disabled = false;

            if (!date.value) {
                message.textContent = '';
                message.style.color = '#9aa0b4';
                return;
            }

            message.textContent = 'Checking date availability...';
            message.style.color = '#9aa0b4';
            const params = new URLSearchParams({
                date: date.value
            });
            if (time.value) params.set('time', time.value);
            fetch('/<?= esc($role) ?>/concern/availability?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    const available = data.available === true;
                    message.textContent = available ?
                        (time.value ? 'Available schedule.' : 'Date available. Select a time if needed.') :
                        (data.message || 'Unavailable: this date is fully booked. Please choose another date.');
                    message.style.color = available ? '#0e9464' : '#c0392b';
                    date.classList.toggle('cr-slot-unavailable', !available);
                    time.classList.toggle('cr-slot-unavailable', !available);
                    submit.disabled = !available;
                })
                .catch(() => {
                    message.textContent = 'Availability will be verified when submitted.';
                    message.style.color = '#9aa0b4';
                });
        }

        document.getElementById('quickAppointmentDate')?.addEventListener('change', checkQuickAppointmentAvailability);
        document.getElementById('quickAppointmentTime')?.addEventListener('change', checkQuickAppointmentAvailability);
        document.getElementById('quickAppointmentSubmit')?.closest('form')?.addEventListener('submit', event => {
            if (document.getElementById('quickAppointmentSubmit')?.disabled) {
                event.preventDefault();
            }
        });

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
</body>

</html>