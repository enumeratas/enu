<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Concern — Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Page layout ── */
        .cr-wrap {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 960px) {
            .cr-wrap {
                grid-template-columns: 1fr;
            }
        }

        /* ── Cards ── */
        .cr-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(29, 36, 72, .07);
            overflow: hidden;
        }

        .cr-card-header {
            padding: 20px 24px 18px;
            border-bottom: 1px solid #f0f2f8;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .cr-card-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .cr-card-header-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a1d2e;
            margin: 0 0 2px;
        }

        .cr-card-header-sub {
            font-size: 12px;
            color: #9aa0b4;
            margin: 0;
        }

        .cr-card-body {
            padding: 22px 24px;
        }

        /* ── Info grid ── */
        .cr-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .cr-info-item label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #9aa0b4;
            margin-bottom: 5px;
        }

        .cr-info-item span {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a1d2e;
        }

        .cr-info-item span.muted {
            font-weight: 400;
            color: #6b7280;
        }

        /* ── Appointment banner ── */
        .cr-appt {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff8f0;
            border: 1px solid #fde8c8;
            border-radius: 11px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }

        .cr-appt-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: rgba(230, 126, 34, .12);
            color: #e67e22;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .cr-appt-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #b07a00;
            margin-bottom: 3px;
        }

        .cr-appt-date {
            font-size: 14px;
            font-weight: 700;
            color: #1a1d2e;
        }

        /* ── Message box ── */
        .cr-message-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #9aa0b4;
            margin-bottom: 10px;
        }

        .cr-message-box {
            background: #f8f9fc;
            border: 1px solid #e8ecf4;
            border-radius: 11px;
            padding: 16px 18px;
            font-size: 13.5px;
            color: #4a5068;
            line-height: 1.8;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* ── Status badge ── */
        .cr-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 100px;
        }

        .cr-status--pending {
            background: #fff8e6;
            color: #b07a00;
            border: 1px solid #ffe08a;
        }

        .cr-status--approved {
            background: #e6f9f1;
            color: #0e9464;
            border: 1px solid #b2e8d2;
        }

        .cr-status--dismissed {
            background: #f3f4f8;
            color: #6b7280;
            border: 1px solid #e2e5ef;
        }

        .cr-slot-unavailable {
            border-color: #c0392b !important;
            background: #fff5f4 !important;
        }

        .cr-resolve-btn {
            background: #16a085;
            color: #fff;
            border: 0;
            box-shadow: 0 3px 9px rgba(22, 160, 133, .24);
            cursor: pointer;
            font-family: inherit;
            transition: background .15s, transform .15s, box-shadow .15s;
        }

        .cr-resolve-btn:hover {
            background: #12806a;
            transform: translateY(-1px);
            box-shadow: 0 5px 13px rgba(22, 160, 133, .3);
        }

        /* ── Action sidebar card ── */
        .cr-action-section {
            margin-bottom: 6px;
        }

        .cr-action-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: #9aa0b4;
            margin: 0 0 10px;
        }

        .cr-textarea {
            width: 100%;
            min-height: 130px;
            text-transform: none !important;
            padding: 12px 14px;
            border: 1.5px solid #e2e5ef;
            border-radius: 11px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            resize: vertical;
            box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s;
            line-height: 1.7;
        }

        .cr-textarea:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, .08);
        }

        .cr-textarea::placeholder {
            color: #c0c6d8;
        }

        .cr-hint {
            font-size: 11.5px;
            color: #b0b6cc;
            margin: 6px 0 16px;
        }

        .cr-btn {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
            transition: opacity .18s, transform .14s;
        }

        .cr-btn:last-child {
            margin-bottom: 0;
        }

        .cr-btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .cr-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
            transform: none;
        }

        .cr-btn--approve {
            background: linear-gradient(135deg, #16a085, #1abc9c);
            color: #fff;
        }

        .cr-btn--dismiss {
            background: #fff;
            color: #6b7280;
            border: 1.5px solid #e2e5ef;
        }

        .cr-btn--dismiss:hover {
            background: #f3f4f8;
            opacity: 1;
        }

        .cr-back-link {
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

        .cr-back-link:hover {
            color: #1d2448;
        }

        /* ── Already approved notice ── */
        .cr-approved-notice {
            background: #f5f7ff;
            border: 1.5px solid #dde2f5;
            border-radius: 11px;
            padding: 18px 20px;
            font-size: 13px;
            color: #4a5068;
            line-height: 1.7;
        }

        .cr-approved-notice strong {
            color: #1a1d2e;
        }

        .cr-notes-box {
            background: #f8f9fc;
            border: 1px solid #e8ecf4;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 13px;
            color: #4a5068;
            line-height: 1.75;
            white-space: pre-wrap;
            word-break: break-word;
            margin-top: 12px;
        }

        /* ── Alert ── */
        .cr-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 9px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .cr-alert--error {
            background: #fff0f1;
            color: #c0392b;
            border: 1px solid #fad4d4;
        }

        .cr-alert i {
            flex-shrink: 0;
            margin-top: 1px;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $active    = 'notifications';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $c         = $concern;
    $isPending  = ($c['status'] === 'pending');
    $isApproved = ($c['status'] === 'approved');

    // Format helpers
    $filedDate = date('F d, Y \a\t g:i A', strtotime($c['created_at']));

    $apptLine  = '';
    if (! empty($c['appointment_date'])) {
        $apptLine = date('l, F d, Y', strtotime($c['appointment_date']));
        if (! empty($c['appointment_time'])) {
            $apptLine .= ' at ' . date('g:i A', strtotime($c['appointment_time']));
        }
    }

    $statusMap = [
        'pending'   => ['label' => 'Pending',   'class' => 'cr-status--pending',   'icon' => 'fa-hourglass-half'],
        'approved'  => ['label' => 'Approved',  'class' => 'cr-status--approved',  'icon' => 'fa-check-circle'],
        'resolved'  => ['label' => 'Resolved',  'class' => 'cr-status--approved',  'icon' => 'fa-check-circle'],
        'dismissed' => ['label' => 'Dismissed', 'class' => 'cr-status--dismissed', 'icon' => 'fa-times-circle'],
    ];
    $statusInfo = $statusMap[$c['status']] ?? $statusMap['pending'];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <!-- Back link -->
            <a href="/<?= esc($role) ?>/concerns" class="cr-back-link">
                <i class="fas fa-arrow-left"></i> Back to Concerns
            </a>

            <!-- Page heading -->
            <div class="db-page-header" style="margin-bottom:22px;">
                <div>
                    <h2 style="margin:0 0 6px;">Review Concern</h2>
                    <p style="margin:0;font-size:12.5px;color:#9aa0b4;">
                        Submitted by <strong style="color:#1a1d2e;"><?= esc($c['full_name']) ?></strong>
                        &nbsp;·&nbsp; <?= $filedDate ?>
                        &nbsp;·&nbsp;
                        <span class="cr-status <?= $statusInfo['class'] ?>">
                            <i class="fas <?= $statusInfo['icon'] ?>"></i>
                            <?= $statusInfo['label'] ?>
                        </span>
                        <?php if ($isApproved): ?>
                    <form action="/<?= esc($role) ?>/concern/resolve/<?= (int)$c['id'] ?>" method="post" style="display:inline;margin-left:8px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="response" value="Appointment completed and concern resolved.">
                        <button type="submit" class="cr-status cr-resolve-btn" onclick="return confirm('Mark this appointment as resolved? The submitter will be notified by email.')">
                            <i class="fas fa-check-circle"></i> Resolve Appointment
                        </button>
                    </form>
                <?php endif; ?>
                </p>
                </div>
            </div>

            <div class="cr-wrap">

                <!-- ── LEFT: Concern details ── -->
                <div>
                    <div class="cr-card">
                        <div class="cr-card-header">
                            <div class="cr-card-header-icon" style="background:rgba(230,126,34,.12);color:#e67e22;">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <p class="cr-card-header-title"><?= esc($c['subject']) ?></p>
                                <p class="cr-card-header-sub">
                                    <?= esc($c['category'] ?: 'No category') ?>
                                </p>
                            </div>
                        </div>
                        <div class="cr-card-body">

                            <!-- Submitter info -->
                            <div class="cr-info-grid">
                                <div class="cr-info-item">
                                    <label>Full Name</label>
                                    <span><?= esc($c['full_name']) ?></span>
                                </div>
                                <div class="cr-info-item">
                                    <label>Email</label>
                                    <span class="muted"><?= esc($c['email']) ?></span>
                                </div>
                                <div class="cr-info-item">
                                    <label>Contact Number</label>
                                    <span class="muted"><?= esc($c['contact_number'] ?: '—') ?></span>
                                </div>
                                <div class="cr-info-item">
                                    <label>Date Filed</label>
                                    <span class="muted"><?= $filedDate ?></span>
                                </div>
                            </div>

                            <!-- Appointment -->
                            <?php if ($apptLine): ?>
                                <div class="cr-appt">
                                    <div class="cr-appt-icon"><i class="fas fa-calendar-check"></i></div>
                                    <div>
                                        <div class="cr-appt-label">Requested Appointment</div>
                                        <div class="cr-appt-date"><?= $apptLine ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Message -->
                            <div class="cr-message-label">Message</div>
                            <div class="cr-message-box"><?= esc($c['message']) ?></div>

                        </div>
                    </div>
                </div>

                <!-- ── RIGHT: Action sidebar ── -->
                <div>
                    <div class="cr-card">
                        <div class="cr-card-header">
                            <div class="cr-card-header-icon" style="background:rgba(29,36,72,.08);color:#1d2448;">
                                <i class="fas fa-reply"></i>
                            </div>
                            <div>
                                <p class="cr-card-header-title">Official Response</p>
                                <p class="cr-card-header-sub">Your reply will be sent to the submitter's email.</p>
                            </div>
                        </div>
                        <div class="cr-card-body">

                            <?php if (session()->getFlashdata('concern_form_error')): ?>
                                <div class="cr-alert cr-alert--error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?= session()->getFlashdata('concern_form_error') ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($isPending): ?>

                                <!-- Shared textarea used by both forms via JS -->
                                <div class="cr-action-section">
                                    <p class="cr-action-label">Response Message</p>
                                    <textarea
                                        id="sharedResponse"
                                        class="cr-textarea"
                                        placeholder="Write your official response here. This message will be emailed to <?= esc($c['full_name']) ?> at <?= esc($c['email']) ?>…"
                                        oninput="syncResponse(this.value)"><?= old('response', $c['notes'] ?? '') ?></textarea>
                                    <p class="cr-hint">
                                        <i class="fas fa-envelope" style="font-size:10px;"></i>
                                        Will be sent to <strong><?= esc($c['email']) ?></strong>
                                    </p>
                                </div>

                                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f2f8;">
                                    <button type="button" id="scheduleConcernToggle"
                                        onclick="toggleConcernSchedule()"
                                        style="
                                            width:100%;padding:11px 14px;
                                            background:#fff;color:#1d2448;
                                            border:1.5px solid #1d2448;border-radius:10px;
                                            font-size:13px;font-weight:600;font-family:'Poppins',sans-serif;
                                            cursor:pointer;display:flex;align-items:center;justify-content:center;
                                            gap:8px;transition:background .15s,color .15s;
                                        "
                                        onmouseover="this.style.background='#1d2448';this.style.color='#fff';"
                                        onmouseout="this.style.background='#fff';this.style.color='#1d2448';">
                                        <i class="fas <?= ! empty($c['appointment_date']) ? 'fa-calendar-edit' : 'fa-calendar-plus' ?>"></i>
                                        <?= ! empty($c['appointment_date']) ? 'Reschedule Appointment' : 'Create Appointment Schedule' ?>
                                    </button>

                                    <div id="scheduleConcernForm" style="display:none;margin-top:14px;">
                                        <form action="/<?= esc($role) ?>/concern/schedule/<?= (int)$c['id'] ?>" method="post">
                                            <?= csrf_field() ?>

                                            <p class="cr-action-label" style="margin-bottom:10px;">
                                                Set the appointment date and time for this concern. It will be recorded on the calendar and emailed to the submitter.
                                            </p>

                                            <div class="rl-drawer-field-wrap" style="margin-bottom:12px;">
                                                <label class="cr-action-label">Appointment Date <span style="color:#c0392b;">*</span></label>
                                                <input type="text" name="appointment_date"
                                                    id="scheduleAppointmentDate"
                                                    class="cr-textarea"
                                                    style="min-height:unset;padding:10px 12px;resize:none;font-size:13px;"
                                                    placeholder="YYYY-MM-DD"
                                                    autocomplete="off"
                                                    value="<?= esc($c['appointment_date'] ?? '') ?>"
                                                    required>
                                            </div>

                                            <div class="rl-drawer-field-wrap" style="margin-bottom:12px;">
                                                <label class="cr-action-label">Appointment Time <span style="font-size:11px;color:#b0b6cc;font-weight:400;">(optional)</span></label>
                                                <input type="time" name="appointment_time"
                                                    id="scheduleAppointmentTime"
                                                    class="cr-textarea"
                                                    style="min-height:unset;padding:10px 12px;resize:none;font-size:13px;"
                                                    value="<?= esc($c['appointment_time'] ?? '') ?>"
                                                    min="08:00" max="17:00">
                                                <p id="scheduleAvailability" class="cr-hint" style="margin-top:6px;"></p>
                                            </div>

                                            <button type="submit"
                                                style="
                                                    width:100%;padding:11px 14px;
                                                    background:linear-gradient(135deg,#1d2448,#2e3a6e);
                                                    color:#fff;border:none;border-radius:10px;
                                                    font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;
                                                    cursor:pointer;display:flex;align-items:center;
                                                    justify-content:center;gap:8px;
                                                    transition:opacity .15s;
                                                "
                                                onmouseover="this.style.opacity='.88'"
                                                onmouseout="this.style.opacity='1'">
                                                <i class="fas fa-calendar-check"></i> Save Appointment
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Approve form -->
                                <form id="approveForm"
                                    action="/<?= esc($role) ?>/concern/approve/<?= (int)$c['id'] ?>"
                                    method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="response" id="approveResponse"
                                        value="<?= old('response') ?>">
                                    <button type="button" class="cr-btn cr-btn--approve"
                                        onclick="submitWithConfirm('approve')">
                                        <i class="fas fa-check-circle"></i> Approve &amp; Send Response
                                    </button>
                                </form>

                                <!-- Dismiss form -->
                                <form id="dismissForm"
                                    action="/<?= esc($role) ?>/concern/dismiss/<?= (int)$c['id'] ?>"
                                    method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="response" id="dismissResponse"
                                        value="<?= old('response') ?>">
                                    <button type="button" class="cr-btn cr-btn--dismiss"
                                        onclick="submitWithConfirm('dismiss')">
                                        <i class="fas fa-times-circle"></i> Dismiss
                                    </button>
                                </form>

                            <?php else: ?>

                                <!-- Already actioned — show recorded response -->
                                <div class="cr-approved-notice">
                                    <strong>This concern has already been <?= esc($c['status']) ?>.</strong>
                                    <?php if (! empty($c['notes'])): ?>
                                        <p style="margin:4px 0 0;font-size:12.5px;color:#9aa0b4;">Recorded response:</p>
                                        <div class="cr-notes-box"><?= esc($c['notes']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- ── Reschedule section ── -->
                                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f2f8;">
                                    <button type="button" id="rescheduleToggle"
                                        onclick="toggleReschedule()"
                                        style="
                                            width:100%;padding:11px 14px;
                                            background:#fff;color:#1d2448;
                                            border:1.5px solid #1d2448;border-radius:10px;
                                            font-size:13px;font-weight:600;font-family:'Poppins',sans-serif;
                                            cursor:pointer;display:flex;align-items:center;justify-content:center;
                                            gap:8px;transition:background .15s,color .15s;
                                        "
                                        onmouseover="this.style.background='#1d2448';this.style.color='#fff';"
                                        onmouseout="this.style.background='#fff';this.style.color='#1d2448';">
                                        <i class="fas fa-calendar-edit"></i> Reschedule Appointment
                                    </button>

                                    <div id="rescheduleForm" style="display:none;margin-top:14px;">
                                        <form action="/<?= esc($role) ?>/concern/reschedule/<?= (int)$c['id'] ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="response" id="rescheduleResponse" value="<?= esc(old('response', $c['notes'] ?? '')) ?>">

                                            <p class="cr-action-label" style="margin-bottom:10px;">
                                                Set a new appointment date and time. The submitter will be notified by email.
                                            </p>

                                            <div class="rl-drawer-field-wrap" style="margin-bottom:12px;">
                                                <label class="cr-action-label">New Appointment Date <span style="color:#c0392b;">*</span></label>
                                                <div style="position:relative;">
                                                    <input type="text" name="appointment_date"
                                                        id="rescheduleAppointmentDate"
                                                        class="cr-textarea"
                                                        style="min-height:unset;padding:10px 12px;resize:none;font-size:13px;"
                                                        placeholder="YYYY-MM-DD"
                                                        autocomplete="off"
                                                        value="<?= esc($c['appointment_date'] ?? '') ?>"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="rl-drawer-field-wrap" style="margin-bottom:12px;">
                                                <label class="cr-action-label">New Appointment Time <span style="font-size:11px;color:#b0b6cc;font-weight:400;">(optional)</span></label>
                                                <input type="time" name="appointment_time"
                                                    id="rescheduleAppointmentTime"
                                                    class="cr-textarea"
                                                    style="min-height:unset;padding:10px 12px;resize:none;font-size:13px;"
                                                    value="<?= esc($c['appointment_time'] ?? '') ?>"
                                                    min="08:00" max="17:00">
                                                <p id="rescheduleAvailability" class="cr-hint" style="margin-top:6px;"></p>
                                            </div>

                                            <button type="submit"
                                                style="
                                                    width:100%;padding:11px 14px;
                                                    background:linear-gradient(135deg,#1d2448,#2e3a6e);
                                                    color:#fff;border:none;border-radius:10px;
                                                    font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;
                                                    cursor:pointer;display:flex;align-items:center;
                                                    justify-content:center;gap:8px;
                                                    transition:opacity .15s;
                                                "
                                                onmouseover="this.style.opacity='.88'"
                                                onmouseout="this.style.opacity='1'">
                                                <i class="fas fa-paper-plane"></i> Save &amp; Notify Submitter
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </div><!-- /.cr-wrap -->

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <!-- ── Confirm modal ── -->
    <div id="crConfirmOverlay"
        style="display:none;position:fixed;inset:0;background:rgba(15,17,30,.5);backdrop-filter:blur(3px);z-index:2000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:18px;max-width:400px;width:calc(100% - 32px);padding:32px 28px 26px;box-shadow:0 16px 48px rgba(0,0,0,.2);animation:pa-pop .18s ease;">
            <div id="crConfirmIcon"
                style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 16px;"></div>
            <h3 id="crConfirmTitle" style="font-size:16px;font-weight:700;color:#1a1d2e;text-align:center;margin:0 0 8px;"></h3>
            <p id="crConfirmBody" style="font-size:13px;color:#6b7280;text-align:center;line-height:1.65;margin:0 0 22px;"></p>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeConfirm()"
                    style="flex:1;padding:11px;border-radius:9px;border:1.5px solid #e2e5ef;background:#f0f2f8;font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;color:#4a5068;">
                    Cancel
                </button>
                <button type="button" id="crConfirmOk"
                    style="flex:1;padding:11px;border-radius:9px;border:none;font-size:13.5px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;color:#fff;">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        let _pendingAction = null;

        // Keep both hidden inputs in sync with the shared textarea
        function syncResponse(val) {
            document.getElementById('approveResponse').value = val;
            document.getElementById('dismissResponse').value = val;
            document.getElementById('rescheduleResponse').value = val;
        }

        function submitWithConfirm(action) {
            const response = document.getElementById('sharedResponse').value.trim();
            if (!response) {
                document.getElementById('sharedResponse').focus();
                document.getElementById('sharedResponse').style.borderColor = '#c0392b';
                setTimeout(() => document.getElementById('sharedResponse').style.borderColor = '', 1800);
                return;
            }

            const isApprove = action === 'approve';

            document.getElementById('crConfirmIcon').style.background = isApprove ?
                'rgba(22,160,133,.12)' : 'rgba(107,114,128,.1)';
            document.getElementById('crConfirmIcon').style.color = isApprove ? '#16a085' : '#6b7280';
            document.getElementById('crConfirmIcon').innerHTML = isApprove ?
                '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';

            document.getElementById('crConfirmTitle').textContent = isApprove ?
                'Approve & Send Response?' : 'Dismiss Concern?';
            document.getElementById('crConfirmBody').textContent = isApprove ?
                'This will mark the concern as approved and email your response to the submitter.' :
                'This will mark the concern as dismissed and email your response to the submitter.';

            const okBtn = document.getElementById('crConfirmOk');
            okBtn.style.background = isApprove ?
                'linear-gradient(135deg,#16a085,#1abc9c)' : 'linear-gradient(135deg,#636e72,#7f8c8d)';
            okBtn.textContent = isApprove ? 'Yes, Approve' : 'Yes, Dismiss';

            _pendingAction = () => document.getElementById(action + 'Form').submit();

            document.getElementById('crConfirmOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        document.getElementById('crConfirmOk').addEventListener('click', function() {
            if (typeof _pendingAction === 'function') _pendingAction();
            closeConfirm();
        });

        function closeConfirm() {
            document.getElementById('crConfirmOverlay').style.display = 'none';
            document.body.style.overflow = '';
            _pendingAction = null;
        }

        document.getElementById('crConfirmOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeConfirm();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeConfirm();
        });

        // ── Concern schedule form toggle ──────────────────────────────────────
        function toggleConcernSchedule() {
            const form = document.getElementById('scheduleConcernForm');
            const btn = document.getElementById('scheduleConcernToggle');
            if (!form || !btn) return;
            const open = form.style.display !== 'none';
            form.style.display = open ? 'none' : '';
            btn.innerHTML = open ?
                <?= ! empty($c['appointment_date'])
                    ? "'<i class=\"fas fa-calendar-edit\"></i> Reschedule Appointment'"
                    : "'<i class=\"fas fa-calendar-plus\"></i> Create Appointment Schedule'" ?> :
                '<i class="fas fa-times"></i> Cancel <?= ! empty($c['appointment_date']) ? 'Reschedule' : 'Appointment Schedule' ?>';
            if (open) {
                btn.style.background = '#fff';
                btn.style.color = '#1d2448';
            }
        }

        // ── Reschedule form toggle ─────────────────────────────────────────────
        function toggleReschedule() {
            const form = document.getElementById('rescheduleForm');
            const btn = document.getElementById('rescheduleToggle');
            if (!form) return;
            const open = form.style.display !== 'none';
            form.style.display = open ? 'none' : '';
            btn.innerHTML = open ?
                '<i class="fas fa-calendar-edit"></i> Reschedule Appointment' :
                '<i class="fas fa-times"></i> Cancel Reschedule';
            // Reset button colour when closing
            if (open) {
                btn.style.background = '#fff';
                btn.style.color = '#1d2448';
            }
        }

        function checkAppointmentAvailability(dateId, timeId, messageId, excludeScheduleId = 0) {
            const dateInput = document.getElementById(dateId);
            const timeInput = document.getElementById(timeId);
            const date = dateInput?.value;
            const time = timeInput?.value;
            const message = document.getElementById(messageId);
            const form = timeInput?.closest('form');
            const submitButton = form?.querySelector('button[type="submit"]');
            if (!message || !date) {
                if (message) message.textContent = '';
                dateInput?.classList.remove('cr-slot-unavailable');
                timeInput?.classList.remove('cr-slot-unavailable');
                if (submitButton) submitButton.disabled = false;
                return;
            }

            if (!time) {
                message.textContent = 'Select a time to check whether this appointment slot is available.';
                message.style.color = '#9aa0b4';
                dateInput.classList.remove('cr-slot-unavailable');
                timeInput.classList.remove('cr-slot-unavailable');
                if (submitButton) submitButton.disabled = false;
                return;
            }

            const params = new URLSearchParams({
                date,
                time
            });
            if (excludeScheduleId) params.set('exclude_schedule_id', excludeScheduleId);
            fetch('/<?= esc($role) ?>/concern/availability?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    message.textContent = data.message || '';
                    const available = data.available === true;
                    message.style.color = available ? '#0e9464' : '#c0392b';
                    dateInput.classList.toggle('cr-slot-unavailable', !available);
                    timeInput.classList.toggle('cr-slot-unavailable', !available);
                    if (submitButton) submitButton.disabled = !available;
                })
                .catch(() => {
                    message.textContent = 'Availability could not be checked. The server will validate the slot when saved.';
                    message.style.color = '#9aa0b4';
                    dateInput.classList.remove('cr-slot-unavailable');
                    timeInput.classList.remove('cr-slot-unavailable');
                    if (submitButton) submitButton.disabled = false;
                });
        }

        function initializeAppointmentDatePicker(dateId, excludeScheduleId = 0) {
            const dateInput = document.getElementById(dateId);
            if (!dateInput || typeof flatpickr !== 'function') return;

            const params = new URLSearchParams();
            if (excludeScheduleId) params.set('exclude_schedule_id', excludeScheduleId);

            fetch('/<?= esc($role) ?>/concern/unavailable-dates?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    flatpickr(dateInput, {
                        dateFormat: 'Y-m-d',
                        minDate: '<?= date('Y-m-d', strtotime('+1 day')) ?>',
                        disable: data.dates || [],
                        onChange: () => dateInput.dispatchEvent(new Event('change'))
                    });
                })
                .catch(() => {
                    flatpickr(dateInput, {
                        dateFormat: 'Y-m-d',
                        minDate: '<?= date('Y-m-d', strtotime('+1 day')) ?>',
                        onChange: () => dateInput.dispatchEvent(new Event('change'))
                    });
                });
        }

        [
            ['scheduleAppointmentDate', 'scheduleAppointmentTime', 'scheduleAvailability', <?= (int) ($c['schedule_id'] ?? 0) ?>],
            ['rescheduleAppointmentDate', 'rescheduleAppointmentTime', 'rescheduleAvailability', <?= (int) ($c['schedule_id'] ?? 0) ?>]
        ].forEach(([dateId, timeId, messageId, excludeScheduleId]) => {
            const dateInput = document.getElementById(dateId);
            const timeInput = document.getElementById(timeId);
            if (!dateInput || !timeInput) return;
            const check = () => checkAppointmentAvailability(dateId, timeId, messageId, excludeScheduleId);
            dateInput.addEventListener('change', check);
            timeInput.addEventListener('change', check);
            timeInput.closest('form')?.addEventListener('submit', event => {
                const submitButton = event.currentTarget.querySelector('button[type="submit"]');
                if (submitButton?.disabled) {
                    event.preventDefault();
                }
            });
            initializeAppointmentDatePicker(dateId, excludeScheduleId);
            check();
        });
    </script>
</body>

</html>