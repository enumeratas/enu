<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment / Concern - Bacolod BIS</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .concern-page-intro {
            width: 100%;
            max-width: none;
            margin: 0 0 18px;
        }

        .clr-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
        }

        .clr-badge--pending {
            background: #fff8f0;
            color: #b7600a;
            border: 1px solid #fde8c8;
        }

        .clr-badge--approved {
            background: #f0faf6;
            color: #1a7a55;
            border: 1px solid #c3e8d8;
        }

        .clr-badge--rejected {
            background: #fff0f1;
            color: #c0392b;
            border: 1px solid #fad4d4;
        }

        .clr-badge--released {
            background: #eef0fb;
            color: #1d2448;
            border: 1px solid #d0d8f5;
        }

        .concern-empty {
            text-align: center;
            color: #9aa0b4;
            padding: 28px 12px;
        }

        .concern-empty i {
            display: block;
            font-size: 28px;
            margin-bottom: 8px;
            color: #c5cad8;
        }

        .concern-modal {
            max-width: 760px;
            width: calc(100% - 32px);
        }

        .concern-form {
            padding: 8px 4px 0;
        }

        .concern-section {
            color: #1d2448;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            border-bottom: 1px solid #e8ecf4;
            padding-bottom: 8px;
            margin: 0 0 16px;
        }

        .concern-section:not(:first-child) {
            margin-top: 22px;
        }

        .concern-readonly {
            background: #f5f7fb !important;
            color: #687087 !important;
        }

        .concern-form textarea {
            min-height: 110px;
        }

        .concern-submit {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid #e8ecf4;
        }

        .concern-slot-note {
            margin: 6px 0 0;
            font-size: 12px;
            min-height: 16px;
            color: #9aa0b4;
        }

        @media (max-width: 700px) {
            .db-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role = (isset($role) && is_string($role) && $role !== '') ? $role : 'sk';
    $active = 'concerns';
    $pageTitle = 'Appointment / Concern';
    include(APPPATH . 'Views/dashboard/sidebar.php');
    $account = (isset($account) && is_array($account)) ? $account : [];
    $concerns = (isset($concerns) && is_array($concerns)) ? $concerns : [];
    $successMessage = session()->getFlashdata('success');
    $errorMessage = session()->getFlashdata('error');
    $reopenForm = $errorMessage || old('subject') || old('message');
    $categories = [
        'Barangay Services',
        'Health and Sanitation',
        'Peace and Order',
        'Infrastructure / Roads',
        'Suggestion / Feedback',
        'Other',
    ];
    $selectedCategory = old('category');
    $selectedCategory = is_string($selectedCategory) ? $selectedCategory : '';
    $fullName = trim(($account['first_name'] ?? '') . ' ' . ($account['middle_name'] ?? '') . ' ' . ($account['last_name'] ?? ''));
    $minDate = date('Y-m-d', strtotime('+1 day'));
    $oldTime = old('appointment_time');
    $oldTime = is_string($oldTime) ? $oldTime : '';
    $oldDate = old('appointment_date');
    $oldDate = is_string($oldDate) ? $oldDate : '';
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (is_string($successMessage) && $successMessage !== ''): ?><div class="db-alert db-alert--success"><i class="fas fa-check-circle"></i><?= esc($successMessage) ?></div><?php endif; ?>
            <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="db-alert db-alert--error"><i class="fas fa-exclamation-circle"></i><?= esc($errorMessage) ?></div><?php endif; ?>

            <div class="db-alert db-alert--info concern-page-intro">
                <i class="fas fa-info-circle"></i> Your appointment and concern requests are listed here. A pending request can be cancelled. Dates and times that are already booked stay unavailable for a new appointment.
            </div>

            <div class="db-toolbar">
                <form method="get" data-live-results="liveResults" class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search subject or date..." value="<?= esc($search ?? '') ?>">
                </form>
                <div class="db-toolbar-actions">
                    <button type="button" class="db-btn db-btn--primary" onclick="openModal('newConcernModal')">
                        <i class="fas fa-plus"></i> New Request
                    </button>
                </div>
            </div>

            <div id="liveResults">
            <div class="db-table-wrap">
                <table class="db-table" id="concernsTable">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Appointment</th>
                            <th>Date Filed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($concerns === []): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="concern-empty">
                                        <i class="fas fa-calendar-check"></i>
                                        <p><?= ($search ?? '') !== '' ? 'No requests match your search.' : 'No requests yet. Click <strong>New Request</strong> to get started.' ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($concerns as $row):
                                if (! is_array($row)) {
                                    continue;
                                }
                                $status = is_string($row['status'] ?? null) ? $row['status'] : 'pending';
                                $statusMap = [
                                    'pending'   => ['clr-badge--pending',  'fa-clock',        'Pending'],
                                    'approved'  => ['clr-badge--approved', 'fa-check-circle', 'Approved'],
                                    'resolved'  => ['clr-badge--released', 'fa-check-double', 'Resolved'],
                                    'dismissed' => ['clr-badge--rejected', 'fa-times-circle', 'Dismissed'],
                                ];
                                [$badgeClass, $icon, $label] = $statusMap[$status] ?? $statusMap['pending'];
                                $subject = is_string($row['subject'] ?? null) ? $row['subject'] : '';
                                $category = is_string($row['category'] ?? null) && $row['category'] !== '' ? $row['category'] : '—';
                                $filed = ! empty($row['created_at']) ? date('M d, Y', strtotime((string) $row['created_at'])) : '—';
                                $appointment = '—';
                                if (! empty($row['appointment_date'])) {
                                    $appointment = date('M d, Y', strtotime((string) $row['appointment_date']));
                                    if (! empty($row['appointment_time'])) {
                                        $appointment .= ' · ' . date('g:i A', strtotime((string) $row['appointment_time']));
                                    }
                                }
                                $notes = is_string($row['notes'] ?? null) ? trim($row['notes']) : '';
                                $search = strtolower($subject . ' ' . $category . ' ' . $appointment . ' ' . $filed . ' ' . $label);
                            ?>
                                <tr data-search="<?= esc($search) ?>">
                                    <td>
                                        <div style="font-size:13px;font-weight:600;color:#1a1d2e;"><?= esc($subject) ?></div>
                                        <?php if ($notes !== ''): ?>
                                            <div style="font-size:11px;color:#9aa0b4;margin-top:3px;">Office note: <?= esc($notes) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($category) ?></td>
                                    <td><?= esc($appointment) ?></td>
                                    <td><?= esc($filed) ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <span class="clr-badge <?= esc($badgeClass) ?>">
                                                <i class="fas <?= esc($icon) ?>"></i> <?= esc($label) ?>
                                            </span>
                                            <?php if ($status === 'pending'): ?>
                                                <button type="button"
                                                    class="db-btn db-btn--xs db-btn--danger"
                                                    onclick='confirmCancel(<?= (int) ($row['id'] ?? 0) ?>, <?= json_encode($subject) ?>)'>
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>

    <div class="db-modal-overlay" id="cancelModal">
        <div class="db-modal" style="max-width:400px;">
            <div class="db-modal-header" style="background:#fff0f1;border-bottom:1px solid #fad4d4;">
                <h3 style="color:#c0392b;font-size:14px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-exclamation-triangle"></i> Cancel Request
                </h3>
                <button type="button" class="db-modal-close" onclick="closeModal('cancelModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="db-modal-body" style="padding:24px;">
                <p style="font-size:13.5px;color:#4a5068;margin:0 0 6px;">Cancel this pending request?</p>
                <p id="cancelSubject" style="font-size:14px;font-weight:700;color:#1a1d2e;margin:0 0 16px;"></p>
                <p style="font-size:12.5px;color:#9aa0b4;margin:0;">The request is removed and its appointment time becomes available again.</p>
            </div>
            <div class="db-modal-footer" style="gap:10px;">
                <button type="button" class="db-btn db-btn--outline" onclick="closeModal('cancelModal')" style="flex:1;">
                    Keep Request
                </button>
                <form id="cancelForm" method="post" style="flex:1;">
                    <?= csrf_field() ?>
                    <button type="submit" class="db-btn db-btn--danger" style="width:100%;justify-content:center;">
                        <i class="fas fa-times"></i> Yes, Cancel It
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="db-modal-overlay" id="newConcernModal">
        <div class="db-modal concern-modal">
            <div class="db-modal-header">
                <h3><i class="fas fa-calendar-check"></i> New Appointment / Concern</h3>
                <button type="button" class="db-modal-close" onclick="closeModal('newConcernModal')"><i class="fas fa-times"></i></button>
            </div>
            <form action="/<?= esc($role) ?>/concerns/store" method="post" class="concern-form" id="concernForm">
                <?= csrf_field() ?>
                <div class="db-modal-body" style="max-height:72vh;overflow-y:auto;">
                    <p class="concern-section">Your Information</p>
                    <div class="db-form-grid">
                        <div class="db-form-group"><label>Full Name</label><input class="concern-readonly" type="text" value="<?= esc($fullName) ?>" readonly></div>
                        <div class="db-form-group"><label>Email Address</label><input class="concern-readonly" type="email" value="<?= esc($account['email'] ?? '') ?>" readonly></div>
                        <div class="db-form-group"><label>Contact Number</label><input type="tel" name="contact_number" class="js-contact-number" value="<?= esc(old('contact_number', $account['contact_number'] ?? '')) ?>" maxlength="11" inputmode="numeric"></div>
                    </div>
                    <p class="concern-section">Concern Details</p>
                    <div class="db-form-grid">
                        <div class="db-form-group"><label>Category</label>
                            <select name="category">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $categoryOption): ?>
                                    <option value="<?= esc($categoryOption) ?>" <?= $selectedCategory === $categoryOption ? 'selected' : '' ?>><?= esc($categoryOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="db-form-group db-form-group--full"><label>Subject *</label><input type="text" name="subject" value="<?= esc(old('subject')) ?>" required></div>
                        <div class="db-form-group db-form-group--full"><label>Message *</label><textarea name="message" rows="5" required><?= esc(old('message')) ?></textarea></div>
                    </div>
                    <p class="concern-section">Appointment Schedule <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#9aa0b4;">(optional)</span></p>
                    <div class="db-form-grid">
                        <div class="db-form-group">
                            <label>Preferred Date</label>
                            <input type="text" name="appointment_date" id="concernDate" value="<?= esc($oldDate) ?>" placeholder="Select a date" autocomplete="off">
                        </div>
                        <div class="db-form-group">
                            <label>Preferred Time</label>
                            <select name="appointment_time" id="concernTime" data-current="<?= esc($oldTime) ?>" disabled>
                                <option value="">Select a date first</option>
                            </select>
                            <p id="concernSlotNote" class="concern-slot-note"></p>
                        </div>
                    </div>
                </div>
                <div class="db-modal-footer concern-submit">
                    <button type="button" class="db-btn db-btn--outline" onclick="closeModal('newConcernModal')">Close</button>
                    <button type="submit" class="db-btn db-btn--primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/js/appointment-slots.js?v=2"></script>
    <script>
        function openModal(id) {
            var modal = document.getElementById(id);
            if (modal) modal.classList.add('active');
        }

        function closeModal(id) {
            var modal = document.getElementById(id);
            if (modal) modal.classList.remove('active');
        }

        function confirmCancel(id, subject) {
            var label = document.getElementById('cancelSubject');
            var form = document.getElementById('cancelForm');
            if (label) label.textContent = subject || 'This request';
            if (form) form.action = '/<?= esc($role) ?>/concerns/cancel/' + id;
            openModal('cancelModal');
        }

        function filterConcerns() {
            var input = document.getElementById('searchInput');
            if (!input) return;
            var query = (input.value || '').toLowerCase();
            document.querySelectorAll('#concernsTable tbody tr[data-search]').forEach(function (row) {
                row.style.display = (row.getAttribute('data-search') || '').indexOf(query) !== -1 ? '' : 'none';
            });
        }

        var concernDate = document.getElementById('concernDate');
        var concernTime = document.getElementById('concernTime');
        if (window.BISAppointmentSlots && concernDate && concernTime) {
            BISAppointmentSlots.bind(concernDate, concernTime, {
                minDate: '<?= esc($minDate) ?>',
                message: document.getElementById('concernSlotNote')
            });
        }

        document.getElementById('concernForm')?.addEventListener('submit', function (event) {
            var note = document.getElementById('concernSlotNote');
            if (concernDate && concernDate.value && concernTime && !concernTime.value) {
                event.preventDefault();
                if (note) {
                    note.textContent = 'Choose an open time for that date.';
                    note.style.color = '#c0392b';
                }
            }
        });

        <?php if ($reopenForm): ?>
        openModal('newConcernModal');
        <?php endif; ?>
    </script>
</body>

</html>
