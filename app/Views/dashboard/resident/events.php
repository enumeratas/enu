<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Events - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .evt-hero h2 { margin: 0 0 6px; color: #1d2448; }
        .evt-hero p { margin: 0; color: #6b7280; font-size: 14px; }
        .evt-layout { display: grid; grid-template-columns: 1.4fr .8fr; gap: 20px; align-items: start; margin-top: 16px; }
        .cal-card, .side-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(29, 36, 72, .06); overflow: hidden; }
        .cal-head { background: linear-gradient(135deg, #1d2448, #2e3a6e); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; }
        .cal-head a { color: #fff; text-decoration: none; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .cal-head a:hover { background: rgba(255, 255, 255, .14); }
        .cal-head h2 { color: #fff; font-size: 18px; margin: 0; }
        .cal-days, .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
        .cal-days div { text-align: center; padding: 10px 4px; font-size: 11px; font-weight: 700; color: #9aa0b4; background: #f7f8fc; }
        .cal-cell { min-height: 92px; border-top: 1px solid #f0f2f8; border-right: 1px solid #f0f2f8; padding: 6px; }
        .cal-cell:nth-child(7n) { border-right: none; }
        .cal-cell.is-today { background: #f0f4ff; }
        .cal-cell.is-muted { background: #fafbfd; }
        .cal-num { font-size: 12px; font-weight: 700; color: #1d2448; }
        .cal-chip { display: block; margin-top: 4px; font-size: 10px; line-height: 1.3; color: #1d2448; background: #eef1fb; border-radius: 4px; padding: 2px 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .side-card h3 { margin: 0; padding: 14px 18px; background: #1d2448; color: #fff; font-size: 14px; }
        .event-row { padding: 14px 18px; border-bottom: 1px solid #f0f2f8; }
        .event-row strong { display: block; color: #1a1d2e; font-size: 14px; }
        .event-meta { margin-top: 4px; color: #6b7280; font-size: 12px; }
        .empty-note { padding: 28px 18px; text-align: center; color: #9aa0b4; font-size: 13px; }
        .month-list { margin-top: 20px; }
        @media (max-width: 1024px) {
            .evt-layout { grid-template-columns: 1fr; }
            .cal-cell { min-height: 64px; }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = 'resident';
    $active    = 'events';
    $pageTitle = 'Barangay Events';
    $year      = (int) ($year ?? date('Y'));
    $month     = (int) ($month ?? date('n'));
    $monthName = (string) ($monthName ?? date('F Y'));
    $byDate    = is_array($byDate ?? null) ? $byDate : [];
    $events    = is_array($events ?? null) ? $events : [];
    $upcoming  = is_array($upcoming ?? null) ? $upcoming : [];
    $prevUrl   = (string) ($prevUrl ?? '/resident/events');
    $nextUrl   = (string) ($nextUrl ?? '/resident/events');

    $formatTime = static function ($start, $end): string {
        $fmt = static function ($time): string {
            $time = trim((string) $time);
            if ($time === '' || $time === '00:00:00') {
                return '';
            }
            $stamp = strtotime($time);

            return $stamp === false ? '' : date('g:i A', $stamp);
        };
        $startLabel = $fmt($start);
        $endLabel   = $fmt($end);
        if ($startLabel !== '' && $endLabel !== '') {
            return $startLabel . ' – ' . $endLabel;
        }

        return $startLabel !== '' ? $startLabel : $endLabel;
    };

    $firstDow    = (int) date('w', strtotime(sprintf('%04d-%02d-01', $year, $month)));
    $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
    $today       = date('Y-m-d');

    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div class="dash-welcome evt-hero" style="margin-bottom:16px;">
                <div>
                    <h2>Barangay Events</h2>
                    <p>Upcoming activities from the Barangay Bacolod calendar.</p>
                </div>
                <div class="dash-welcome-icon"><i class="fas fa-calendar-alt"></i></div>
            </div>

            <div class="evt-layout">
                <section class="cal-card">
                    <div class="cal-head">
                        <a href="<?= esc($prevUrl) ?>" aria-label="Previous month"><i class="fas fa-chevron-left"></i></a>
                        <h2><?= esc($monthName) ?></h2>
                        <a href="<?= esc($nextUrl) ?>" aria-label="Next month"><i class="fas fa-chevron-right"></i></a>
                    </div>
                    <div class="cal-days">
                        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName): ?>
                            <div><?= $dayName ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="cal-grid">
                        <?php for ($i = 0; $i < $firstDow; $i++): ?>
                            <div class="cal-cell is-muted"></div>
                        <?php endfor; ?>
                        <?php for ($day = 1; $day <= $daysInMonth; $day++):
                            $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $dayEvents = $byDate[$dateStr] ?? [];
                            $classes   = 'cal-cell';
                            if ($dateStr === $today) {
                                $classes .= ' is-today';
                            }
                        ?>
                            <div class="<?= $classes ?>">
                                <div class="cal-num"><?= $day ?></div>
                                <?php foreach (array_slice($dayEvents, 0, 2) as $event): ?>
                                    <span class="cal-chip" title="<?= esc($event['title']) ?>"><?= esc($event['title']) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($dayEvents) > 2): ?>
                                    <span class="cal-chip">+<?= count($dayEvents) - 2 ?> more</span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </section>

                <aside class="side-card">
                    <h3><i class="fas fa-clock" style="margin-right:8px;"></i>Next 90 days</h3>
                    <?php if ($upcoming === []): ?>
                        <div class="empty-note">No upcoming events yet.</div>
                    <?php else: ?>
                        <?php foreach ($upcoming as $event):
                            $when      = date('M j, Y', strtotime($event['event_date']));
                            $timeLabel = $formatTime($event['start_time'] ?? '', $event['end_time'] ?? '');
                        ?>
                            <div class="event-row">
                                <strong><?= esc($event['title']) ?></strong>
                                <div class="event-meta">
                                    <?= esc(ucfirst((string) ($event['event_type'] ?? 'event'))) ?>
                                    · <?= esc($when) ?>
                                    <?php if ($timeLabel !== ''): ?> · <?= esc($timeLabel) ?><?php endif; ?>
                                    <?php if (! empty($event['location'])): ?><br><?= esc($event['location']) ?><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </aside>
            </div>

            <section class="cal-card month-list">
                <h3 style="margin:0;padding:14px 18px;background:#1d2448;color:#fff;font-size:14px;">Events in <?= esc($monthName) ?></h3>
                <?php if ($events === []): ?>
                    <div class="empty-note">There are no public events in <?= esc($monthName) ?>.</div>
                <?php else: ?>
                    <?php foreach ($events as $event):
                        $when      = date('F j, Y', strtotime($event['event_date']));
                        $timeLabel = $formatTime($event['start_time'] ?? '', $event['end_time'] ?? '');
                    ?>
                        <div class="event-row">
                            <strong><?= esc($event['title']) ?></strong>
                            <div class="event-meta">
                                <?= esc(ucfirst((string) ($event['event_type'] ?? 'event'))) ?>
                                · <?= esc($when) ?>
                                <?php if ($timeLabel !== ''): ?> · <?= esc($timeLabel) ?><?php endif; ?>
                                <?php if (! empty($event['location'])): ?> · <?= esc($event['location']) ?><?php endif; ?>
                            </div>
                            <?php $visibleDescription = \App\Models\ScheduleModel::visibleDescription($event['description'] ?? ''); ?>
                            <?php if ($visibleDescription !== ''): ?>
                                <div class="event-meta"><?= esc($visibleDescription) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>

</html>
