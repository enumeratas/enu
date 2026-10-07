<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> — Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Back link ── */
        .cd-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            font-weight: 600;
            color: #6b7280;
            text-decoration: none;
            margin-bottom: 20px;
            transition: color .15s;
        }

        .cd-back:hover {
            color: #1d2448;
        }

        /* ── Date hero ── */
        .cd-hero {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .cd-hero-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .cd-date-box {
            background: rgba(255, 255, 255, .12);
            border-radius: 14px;
            padding: 10px 16px;
            text-align: center;
            min-width: 64px;
            flex-shrink: 0;
        }

        .cd-date-box-month {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .65);
        }

        .cd-date-box-day {
            font-size: 36px;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
        }

        .cd-date-box-dow {
            font-size: 11px;
            color: rgba(255, 255, 255, .55);
            margin-top: 2px;
        }

        .cd-hero-title {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .cd-hero-sub {
            color: rgba(255, 255, 255, .6);
            font-size: 13px;
            margin: 0;
        }

        .cd-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 20px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border: 1.5px solid rgba(255, 255, 255, .25);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: background .18s;
            white-space: nowrap;
        }

        .cd-add-btn:hover {
            background: rgba(255, 255, 255, .25);
        }

        /* ── Navigation strip ── */
        .cd-nav-strip {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .cd-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #fff;
            border: 1.5px solid #e2e5ef;
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 600;
            color: #4a5068;
            text-decoration: none;
            transition: all .15s;
        }

        .cd-nav-btn:hover {
            background: #f0f2f8;
            border-color: #1d2448;
            color: #1d2448;
        }

        .cd-nav-today {
            background: #1d2448;
            color: #fff;
            border-color: #1d2448;
        }

        .cd-nav-today:hover {
            opacity: .88;
            background: #1d2448;
            color: #fff;
        }

        /* ── Events list ── */
        .cd-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .cd-event-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(29, 36, 72, .07);
            overflow: hidden;
            display: flex;
        }

        .cd-event-stripe {
            width: 6px;
            flex-shrink: 0;
        }

        .cd-event-body {
            padding: 18px 22px;
            flex: 1;
            min-width: 0;
        }

        .cd-event-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .cd-event-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0;
        }

        .cd-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .cd-meta-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 14px 24px;
            margin-bottom: 10px;
        }

        .cd-meta-item {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #6b7280;
        }

        .cd-meta-item i {
            font-size: 12px;
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }

        .cd-description {
            background: #f8f9fc;
            border: 1px solid #e8ecf4;
            border-radius: 9px;
            padding: 12px 14px;
            font-size: 13px;
            color: #4a5068;
            line-height: 1.75;
            margin-top: 8px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .cd-event-actions {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #f0f2f8;
        }

        .cd-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: opacity .15s;
        }

        .cd-action-btn:hover {
            opacity: .82;
        }

        .cd-action-btn--edit {
            background: #1d2448;
            color: #fff;
        }

        .cd-action-btn--blotter {
            background: #c0392b;
            color: #fff;
        }

        .cd-action-btn--delete {
            background: #fff;
            color: #c0392b;
            border: 1.5px solid #fad4d4;
        }

        .cd-action-btn--delete:hover {
            background: #c0392b;
            color: #fff;
            opacity: 1;
        }

        /* ── Empty state ── */
        .cd-empty {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(29, 36, 72, .06);
            padding: 56px 24px;
            text-align: center;
            color: #9aa0b4;
        }

        .cd-empty i {
            font-size: 42px;
            display: block;
            margin-bottom: 14px;
            opacity: .25;
        }

        .cd-empty p {
            font-size: 14px;
            margin: 0 0 20px;
        }

        /* ── Add Event modal (reused from calendar) ── */
        @keyframes calPop {
            from {
                transform: scale(.94);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $active    = 'calendar';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $events  = $events  ?? [];
    $dateStr = $dateStr ?? date('Y-m-d');
    $year    = $year    ?? (int)date('Y');
    $month   = $month   ?? (int)date('n');
    $today   = date('Y-m-d');

    $dateObj    = new DateTime($dateStr);
    $dayLabel   = $dateObj->format('l');               // e.g. Tuesday
    $monthLabel = $dateObj->format('F');               // e.g. September
    $dayNum     = $dateObj->format('j');               // e.g. 1
    $yearNum    = $dateObj->format('Y');               // e.g. 2026
    $isToday    = ($dateStr === $today);

    $prevDate = (clone $dateObj)->modify('-1 day')->format('Y-m-d');
    $nextDate = (clone $dateObj)->modify('+1 day')->format('Y-m-d');

    $typeConfig = [
        'hearing'     => ['icon' => 'fa-gavel',         'color' => '#c0392b', 'bg' => 'rgba(192,57,43,.1)',   'label' => 'Hearing'],
        'meeting'     => ['icon' => 'fa-users',         'color' => '#2980b9', 'bg' => 'rgba(41,128,185,.1)',  'label' => 'Meeting'],
        'appointment' => ['icon' => 'fa-calendar-check', 'color' => '#1d2448', 'bg' => 'rgba(29,36,72,.1)',    'label' => 'Appointment'],
        'event'       => ['icon' => 'fa-star',          'color' => '#16a085', 'bg' => 'rgba(22,160,133,.1)',  'label' => 'Event'],
        'other'       => ['icon' => 'fa-circle',        'color' => '#7f8c8d', 'bg' => 'rgba(127,140,141,.1)', 'label' => 'Other'],
    ];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php if (session()->getFlashdata('cal_success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('cal_success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('cal_error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('cal_error') ?>
                </div>
            <?php endif; ?>

            <!-- Back -->
            <a href="/<?= esc($role) ?>/calendar?year=<?= $year ?>&month=<?= $month ?>" class="cd-back">
                <i class="fas fa-arrow-left"></i> Back to <?= $monthLabel ?> <?= $yearNum ?>
            </a>

            <!-- Hero -->
            <div class="cd-hero">
                <div class="cd-hero-left">
                    <div class="cd-date-box">
                        <div class="cd-date-box-month"><?= strtoupper(substr($monthLabel, 0, 3)) ?></div>
                        <div class="cd-date-box-day"><?= $dayNum ?></div>
                        <div class="cd-date-box-dow"><?= strtoupper(substr($dayLabel, 0, 3)) ?></div>
                    </div>
                    <div>
                        <h2 class="cd-hero-title">
                            <?= $dayLabel ?>, <?= $monthLabel ?> <?= $dayNum ?>, <?= $yearNum ?>
                            <?php if ($isToday): ?>
                                <span style="font-size:13px;background:rgba(255,255,255,.2);border-radius:100px;padding:2px 10px;margin-left:8px;font-weight:600;vertical-align:middle;">Today</span>
                            <?php endif; ?>
                        </h2>
                        <p class="cd-hero-sub">
                            <?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> scheduled
                        </p>
                    </div>
                </div>
                <button onclick="openAddModal('<?= $dateStr ?>')" class="cd-add-btn">
                    <i class="fas fa-plus"></i> Add Event
                </button>
            </div>

            <!-- Prev / Today / Next strip -->
            <div class="cd-nav-strip">
                <a href="/<?= esc($role) ?>/calendar/day/<?= $prevDate ?>" class="cd-nav-btn">
                    <i class="fas fa-chevron-left"></i> <?= date('M d', strtotime($prevDate)) ?>
                </a>
                <?php if (! $isToday): ?>
                    <a href="/<?= esc($role) ?>/calendar/day/<?= $today ?>" class="cd-nav-btn cd-nav-today">
                        <i class="fas fa-dot-circle"></i> Today
                    </a>
                <?php endif; ?>
                <a href="/<?= esc($role) ?>/calendar/day/<?= $nextDate ?>" class="cd-nav-btn">
                    <?= date('M d', strtotime($nextDate)) ?> <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <!-- Events list -->
            <?php if (empty($events)): ?>
                <div class="cd-empty">
                    <i class="fas fa-calendar-day"></i>
                    <p>No events or activities scheduled for this day.</p>
                    <button onclick="openAddModal('<?= $dateStr ?>')"
                        style="padding:11px 22px;background:#1d2448;color:#fff;border:none;border-radius:10px;font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                        <i class="fas fa-plus"></i> Add Event
                    </button>
                </div>
            <?php else: ?>
                <div class="cd-list">
                    <?php foreach ($events as $ev):
                        $isBlotter = ! empty($ev['is_blotter']);
                        $type      = $ev['event_type'] ?? 'other';
                        $cfg       = $typeConfig[$type] ?? $typeConfig['other'];
                        $color     = $ev['color'] ?? $cfg['color'];
                        $startTime = ! empty($ev['start_time']) ? date('g:i A', strtotime($ev['start_time'])) : '';
                        $endTime   = ! empty($ev['end_time'])   ? date('g:i A', strtotime($ev['end_time']))   : '';
                        $canEdit   = ! $isBlotter && (int)($ev['created_by'] ?? 0) === (int)session()->get('user_id');
                    ?>
                        <div class="cd-event-card">
                            <div class="cd-event-stripe" style="background:<?= esc($color) ?>;"></div>
                            <div class="cd-event-body">

                                <!-- Title + type badge -->
                                <div class="cd-event-top">
                                    <h3 class="cd-event-title"><?= esc($ev['title']) ?></h3>
                                    <span class="cd-type-badge"
                                        style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                                        <i class="fas <?= $cfg['icon'] ?>"></i>
                                        <?= $cfg['label'] ?>
                                    </span>
                                </div>

                                <!-- Meta row -->
                                <div class="cd-meta-grid">
                                    <?php if ($startTime): ?>
                                        <div class="cd-meta-item">
                                            <i class="fas fa-clock" style="color:<?= esc($color) ?>;"></i>
                                            <span>
                                                <?= $startTime ?>
                                                <?= $endTime ? ' — ' . $endTime : '' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (! empty($ev['location'])): ?>
                                        <div class="cd-meta-item">
                                            <i class="fas fa-map-marker-alt" style="color:<?= esc($color) ?>;"></i>
                                            <span><?= esc($ev['location']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isBlotter): ?>
                                        <div class="cd-meta-item">
                                            <i class="fas fa-gavel" style="color:#c0392b;"></i>
                                            <span>Blotter Case #<?= (int)($ev['blotter_id'] ?? 0) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Description -->
                                <?php if (! empty($ev['description'])): ?>
                                    <div class="cd-description"><?= esc(\App\Models\ScheduleModel::visibleDescription($ev['description'])) ?></div>
                                <?php endif; ?>

                                <!-- Actions -->
                                <div class="cd-event-actions">
                                    <?php if ($isBlotter): ?>
                                        <a href="/<?= esc($role) ?>/blotter/<?= (int)($ev['blotter_id'] ?? 0) ?>"
                                            class="cd-action-btn cd-action-btn--blotter">
                                            <i class="fas fa-eye"></i> View Blotter Case
                                        </a>
                                    <?php elseif ($canEdit): ?>
                                        <a href="/<?= esc($role) ?>/calendar/view/<?= (int)$ev['id'] ?>"
                                            class="cd-action-btn cd-action-btn--edit">
                                            <i class="fas fa-edit"></i> Edit Event
                                        </a>
                                        <form action="/<?= esc($role) ?>/calendar/delete/<?= (int)$ev['id'] ?>"
                                            method="post"
                                            onsubmit="return confirm('Delete \'<?= esc(addslashes($ev['title'])) ?>\'? This cannot be undone.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="cd-action-btn cd-action-btn--delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Shared event — view only -->
                                        <span style="font-size:12px;color:#b0b6cc;display:flex;align-items:center;gap:5px;">
                                            <i class="fas fa-share-alt"></i> Shared with you
                                        </span>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <!-- ── Add Event Modal (same as calendar.php) ── -->
    <div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(15,17,30,.55);backdrop-filter:blur(3px);z-index:1000;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:18px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:hidden;animation:calPop .18s ease;">
            <div style="background:linear-gradient(135deg,#1d2448,#2e3a6e);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
                <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0;"><i class="fas fa-calendar-plus" style="margin-right:8px;opacity:.8;"></i>Add Event</h3>
                <button onclick="document.getElementById('addModal').style.display='none'"
                    style="background:rgba(255,255,255,.15);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:14px;">×</button>
            </div>
            <form action="/<?= esc($role) ?>/calendar/store" method="post" style="padding:22px 24px;">
                <?= csrf_field() ?>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">Title <span style="color:#c0392b;">*</span></label>
                    <input type="text" name="title" id="addTitle" required
                        placeholder="e.g. Barangay Meeting"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13.5px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">Date <span style="color:#c0392b;">*</span></label>
                        <input type="text" name="event_date" id="addDate" required autocomplete="off" placeholder="YYYY-MM-DD"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">Type</label>
                        <select name="event_type"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;box-sizing:border-box;background:#fff;">
                            <option value="appointment">Appointment</option>
                            <option value="meeting">Meeting</option>
                            <option value="hearing">Hearing</option>
                            <option value="event">Event</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">Start Time</label>
                        <select name="start_time" id="addStartTime"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;box-sizing:border-box;background:#fff;" disabled>
                            <option value="">Select a date first</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">End Time</label>
                        <select name="end_time" id="addEndTime"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;box-sizing:border-box;background:#fff;" disabled>
                            <option value="">Select a start time</option>
                        </select>
                    </div>
                </div>
                <p id="addSlotNote" style="margin:-6px 0 14px;font-size:12px;color:#9aa0b4;"></p>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">Location</label>
                    <input type="text" name="location" placeholder="e.g. Barangay Hall"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13.5px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:5px;">Description</label>
                    <textarea name="description" rows="2" placeholder="Optional notes…"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e5ef;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:#1a1d2e;outline:none;resize:vertical;box-sizing:border-box;text-transform:none !important;"></textarea>
                </div>
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#4a5068;margin-bottom:8px;">Color</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <?php foreach (['#1d2448', '#c0392b', '#2980b9', '#16a085', '#e67e22', '#8e44ad', '#27ae60', '#7f8c8d'] as $clr): ?>
                            <label style="cursor:pointer;">
                                <input type="radio" name="color" value="<?= $clr ?>" style="display:none;" <?= $clr === '#1d2448' ? 'checked' : '' ?>>
                                <span style="display:block;width:26px;height:26px;border-radius:50%;background:<?= $clr ?>;border:3px solid transparent;transition:border-color .15s;"
                                    onclick="this.style.borderColor='#fff';this.style.outline='2px solid <?= $clr ?>';"></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button"
                        onclick="document.getElementById('addModal').style.display='none'"
                        style="flex:1;padding:11px;background:#f0f2f8;color:#4a5068;border:none;border-radius:9px;font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="flex:2;padding:11px;background:#1d2448;color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;">
                        <i class="fas fa-save"></i> Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal(dateStr) {
            const dateInput = document.getElementById('addDate');
            document.getElementById('addTitle').value = '';
            if (dateInput._flatpickr) {
                dateInput._flatpickr.setDate(dateStr, true);
            } else {
                dateInput.value = dateStr;
                dateInput.dispatchEvent(new Event('change'));
            }
            document.getElementById('addModal').style.display = 'flex';
        }

        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') document.getElementById('addModal').style.display = 'none';
        });

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
    <script src="/js/appointment-slots.js?v=3"></script>
    <script>
        if (window.BISAppointmentSlots) {
            window.BISAppointmentSlots.bind(
                document.getElementById('addDate'),
                document.getElementById('addStartTime'),
                {
                    message: document.getElementById('addSlotNote'),
                    endTimeSelect: document.getElementById('addEndTime')
                }
            );
        }
    </script>
</body>

</html>