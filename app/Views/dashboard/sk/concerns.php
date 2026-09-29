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

        .concern-card {
            width: 100%;
            max-width: none;
            margin: 0;
            background: #fff;
            border: 1px solid #e4e8f0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(29, 36, 72, .06);
        }

        .concern-card-header {
            background: linear-gradient(135deg, #c0392b, #a93226);
            color: #fff;
            padding: 18px 24px;
        }

        .concern-card-header h2 {
            font-size: 19px;
            margin: 0 0 5px;
        }

        .concern-card-header p {
            font-size: 12px;
            margin: 0;
            opacity: .86;
        }

        .concern-form {
            padding: 24px;
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
            margin-top: 26px;
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
            margin-top: 26px;
            padding-top: 18px;
            border-top: 1px solid #e8ecf4;
        }

        @media (max-width: 700px) {
            .concern-form {
                padding: 20px 16px;
            }

            .concern-card-header {
                padding: 18px 20px;
            }

            .db-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role = $role ?? 'sk';
    $active = 'concerns';
    $pageTitle = 'Appointment / Concern';
    include(APPPATH . 'Views/dashboard/sidebar.php');
    $account = $account ?? [];
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (session()->getFlashdata('success')): ?><div class="db-alert db-alert--success"><i class="fas fa-check-circle"></i><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="db-alert db-alert--error"><i class="fas fa-exclamation-circle"></i><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

            <div class="db-alert db-alert--info concern-page-intro">
                <i class="fas fa-info-circle"></i> Submit an appointment request or concern using your <?= esc(strtoupper($role)) ?> account. A second booking for a different concern is allowed on another date or time; the same concern or the same slot is blocked.
            </div>
            <div class="concern-card">
                <div class="concern-card-header">
                    <h2><i class="fas fa-calendar-check"></i> Appointment / Concern</h2>
                    <p>Barangay Bacolod - Official Request Form</p>
                </div>
                <form action="/<?= esc($role) ?>/concerns/store" method="post" class="concern-form">
                    <?= csrf_field() ?>
                    <p class="concern-section">Your Information</p>
                    <div class="db-form-grid">
                        <div class="db-form-group"><label>Full Name</label><input class="concern-readonly" type="text" value="<?= esc(trim(($account['first_name'] ?? '') . ' ' . ($account['middle_name'] ?? '') . ' ' . ($account['last_name'] ?? ''))) ?>" readonly></div>
                        <div class="db-form-group"><label>Email Address</label><input class="concern-readonly" type="email" value="<?= esc($account['email'] ?? '') ?>" readonly></div>
                        <div class="db-form-group"><label>Contact Number</label><input type="tel" name="contact_number" class="js-contact-number" value="<?= esc($account['contact_number'] ?? '') ?>" maxlength="11" inputmode="numeric"></div>
                    </div>
                    <p class="concern-section">Concern Details</p>
                    <div class="db-form-grid">
                        <div class="db-form-group"><label>Category</label><select name="category">
                                <option value="">Select Category</option>
                                <option>Barangay Services</option>
                                <option>Health and Sanitation</option>
                                <option>Peace and Order</option>
                                <option>Infrastructure / Roads</option>
                                <option>Suggestion / Feedback</option>
                                <option>Other</option>
                            </select></div>
                        <div class="db-form-group db-form-group--full"><label>Subject *</label><input type="text" name="subject" value="<?= esc(old('subject')) ?>" required></div>
                        <div class="db-form-group db-form-group--full"><label>Message *</label><textarea name="message" rows="5" required><?= esc(old('message')) ?></textarea></div>
                    </div>
                    <p class="concern-section">Appointment Schedule <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#9aa0b4;">(optional)</span></p>
                    <div class="db-form-grid">
                        <div class="db-form-group"><label>Preferred Date</label><input type="date" name="appointment_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"></div>
                        <div class="db-form-group"><label>Preferred Time</label><input type="time" name="appointment_time" min="08:00" max="17:00"></div>
                    </div>
                    <div class="concern-submit"><button type="submit" class="db-btn db-btn--primary"><i class="fas fa-paper-plane"></i> Submit Request</button></div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>