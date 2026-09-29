<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar - Barangay Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .events-page { padding: 108px 24px 64px; background: #f4f6fb; min-height: 100vh; }
        .events-wrap { max-width: 1100px; margin: 0 auto; }
        .events-hero { margin-bottom: 28px; }
        .events-hero h1 { font-size: 32px; color: #1d2448; margin: 8px 0 8px; }
        .events-hero p { color: #6b7280; font-size: 15px; max-width: 640px; }
        .events-layout { display: grid; grid-template-columns: 1.4fr .8fr; gap: 20px; align-items: start; }
        .cal-card, .side-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(29,36,72,.06); overflow: hidden; }
        .cal-head { background: linear-gradient(135deg,#1d2448,#2e3a6e); padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; }
        .cal-head a { color: #fff; text-decoration: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .cal-head a:hover { background: rgba(255,255,255,.12); }
        .cal-head h2 { color: #fff; font-size: 20px; margin: 0; }
        .cal-days, .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
        .cal-days div { text-align: center; padding: 10px 4px; font-size: 11px; font-weight: 700; color: #9aa0b4; background: #f7f8fc; }
        .cal-cell { min-height: 92px; border-top: 1px solid #f0f2f8; border-right: 1px solid #f0f2f8; padding: 6px; }
        .cal-cell:nth-child(7n) { border-right: none; }
        .cal-cell.is-today { background: #f0f4ff; }
        .cal-cell.is-muted { background: #fafbfd; }
        .cal-num { font-size: 12px; font-weight: 700; color: #1d2448; }
        .cal-chip { display: block; margin-top: 4px; font-size: 10px; line-height: 1.3; color: #1d2448; background: #eef1fb; border-radius: 4px; padding: 2px 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .side-card h3 { margin: 0; padding: 16px 18px; background: #1d2448; color: #fff; font-size: 14px; }
        .event-row { padding: 14px 18px; border-bottom: 1px solid #f0f2f8; }
        .event-row strong { display: block; color: #1a1d2e; font-size: 14px; }
        .event-meta { margin-top: 4px; color: #6b7280; font-size: 12px; }
        .empty-note { padding: 28px 18px; text-align: center; color: #9aa0b4; font-size: 13px; }
        .month-list { margin-top: 20px; }
        @media (max-width: 860px) {
            .events-layout { grid-template-columns: 1fr; }
            .cal-cell { min-height: 64px; }
        }
    </style>
</head>

<body>
    <?php
    $viewData = get_defined_vars();
    $year = (int) ($viewData['year'] ?? date('Y'));
    $month = (int) ($viewData['month'] ?? date('n'));
    $monthName = (string) ($viewData['monthName'] ?? date('F Y'));
    $byDate = is_array($viewData['byDate'] ?? null) ? $viewData['byDate'] : [];
    $events = is_array($viewData['events'] ?? null) ? $viewData['events'] : [];
    $upcoming = is_array($viewData['upcoming'] ?? null) ? $viewData['upcoming'] : [];
    $prevUrl = (string) ($viewData['prevUrl'] ?? '/events');
    $nextUrl = (string) ($viewData['nextUrl'] ?? '/events');
    $isLoggedIn = (bool) ($viewData['isLoggedIn'] ?? false);
    ?>
    <nav class="navbar" id="navbar">
        <div class="nav-inner">
            <a href="/" class="nav-brand">
                <img src="/bacolod.png" alt="Bacolod Logo">
                <span>BISync</span>
            </a>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/#services">Services</a>
                <a href="/#about">About</a>
                <a href="/events" class="active">Events</a>
                <a href="/faqs">FAQs</a>
                <?php if (! $isLoggedIn): ?>
                    <div class="nav-divider"></div>
                    <a href="/login" class="btn-login">Login</a>
                    <a href="/signup" class="btn-signup">Sign Up</a>
                <?php endif; ?>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
    <div class="mobile-menu" id="mobileMenu">
        <a href="/">Home</a>
        <a href="/#services">Services</a>
        <a href="/#about">About</a>
        <a href="/events">Events</a>
        <a href="/faqs">FAQs</a>
        <?php if (! $isLoggedIn): ?>
            <a href="/login" class="btn-login">Login</a>
            <a href="/signup" class="btn-signup">Sign Up</a>
        <?php endif; ?>
    </div>

    <?php
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
        $endLabel = $fmt($end);
        if ($startLabel !== '' && $endLabel !== '') {
            return $startLabel . ' – ' . $endLabel;
        }

        return $startLabel !== '' ? $startLabel : $endLabel;
    };

    $firstDow = (int) date('w', strtotime(sprintf('%04d-%02d-01', $year, $month)));
    $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
    $today = date('Y-m-d');
    ?>

    <main class="events-page">
        <div class="events-wrap">
            <div class="events-hero">
                <span class="section-tag">Public calendar</span>
                <h1>Barangay Events</h1>
                <p>Upcoming activities from the Barangay Bacolod calendar. No account is required to view this page. Blotter hearings are not published here.</p>
            </div>

            <div class="events-layout">
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
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $dayEvents = $byDate[$dateStr] ?? [];
                            $classes = 'cal-cell';
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
                            $when = date('M j, Y', strtotime($event['event_date']));
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
                <h3 style="margin:0;padding:16px 18px;background:#1d2448;color:#fff;font-size:14px;">Events in <?= esc($monthName) ?></h3>
                <?php if ($events === []): ?>
                    <div class="empty-note">There are no public events in <?= esc($monthName) ?>.</div>
                <?php else: ?>
                    <?php foreach ($events as $event):
                        $when = date('F j, Y', strtotime($event['event_date']));
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
                            <?php if (! empty($event['description'])): ?>
                                <div class="event-meta"><?= esc($event['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script>
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('open');
        });
    </script>
</body>

</html>
