<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Settings layout ── */
        .st-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .st-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(29, 36, 72, 0.06);
            overflow: hidden;
        }

        .st-card-header {
            padding: 18px 24px 14px;
            border-bottom: 1px solid #f0f2f8;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .st-card-header-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: #eef0fb;
            color: #1d2448;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .st-card-header h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1a1d2e;
            margin: 0;
        }

        .st-card-header p {
            font-size: 12px;
            color: #9aa0b4;
            margin: 2px 0 0;
        }

        .st-card-body {
            padding: 22px 24px;
        }

        /* Avatar */
        .st-avatar-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f0f2f8;
        }

        .st-avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
            font-size: 26px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .st-avatar-info h5 {
            font-size: 15px;
            font-weight: 600;
            color: #1a1d2e;
            margin: 0 0 2px;
        }

        .st-avatar-info span {
            font-size: 12px;
            color: #9aa0b4;
        }

        /* Form fields */
        .st-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .st-form-row--full {
            grid-template-columns: 1fr;
        }

        .st-form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .st-form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #4a5068;
        }

        .st-input-wrap {
            position: relative;
        }

        .st-input-wrap .st-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b6cc;
            font-size: 13px;
            pointer-events: none;
        }

        .st-input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1.5px solid #e2e5ef;
            border-radius: 8px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .st-input:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, 0.08);
        }

        .st-input::placeholder {
            color: #c0c6d8;
        }

        .st-pw-wrap {
            position: relative;
        }

        .st-pw-wrap .st-input {
            padding-right: 42px;
        }

        .st-eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b0b6cc;
            cursor: pointer;
            font-size: 13px;
            padding: 4px;
            transition: color 0.2s;
        }

        .st-eye-btn:hover {
            color: #1d2448;
        }

        /* Buttons */
        .st-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s;
            border: none;
        }

        .st-btn--primary {
            background: #1d2448;
            color: #fff;
        }

        .st-btn--primary:hover {
            background: #2e3a6e;
        }

        .st-btn--outline {
            background: #fff;
            color: #1d2448;
            border: 1.5px solid #1d2448;
        }

        .st-btn--outline:hover {
            background: #f0f2f8;
        }

        .st-btn--danger {
            background: #fff0f1;
            color: #c0392b;
            border: 1.5px solid #fad4d4;
        }

        .st-btn--danger:hover {
            background: #fad4d4;
        }

        /* Alerts */
        .st-alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 18px;
        }

        .st-alert--success {
            background: #f0faf6;
            color: #1a7a55;
            border: 1px solid #c3e8d8;
        }

        .st-alert--error {
            background: #fff0f1;
            color: #c0392b;
            border: 1px solid #fad4d4;
        }

        .st-alert--info {
            background: #f0f4ff;
            color: #1d2448;
            border: 1px solid #d0d8f5;
        }

        /* OTP input */
        .st-otp-group {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 16px 0;
        }

        .st-otp-group input {
            width: 46px;
            height: 54px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            border: 2px solid #e2e5ef;
            border-radius: 9px;
            outline: none;
            color: #1d2448;
            transition: border-color 0.2s, box-shadow 0.2s;
            caret-color: transparent;
        }

        .st-otp-group input:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, 0.1);
        }

        .st-otp-group input.filled {
            border-color: #1d2448;
            background: #f0f2ff;
        }

        /* Password strength */
        .st-pw-strength {
            margin-top: 6px;
        }

        .st-pw-bar-track {
            height: 4px;
            background: #e2e5ef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .st-pw-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }

        .st-pw-label {
            font-size: 11px;
            color: #9aa0b4;
        }

        /* Toggle */
        .st-toggle-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .st-toggle-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #f0f2f8;
        }

        .st-toggle-item:last-child {
            border-bottom: none;
        }

        .st-toggle-label {
            font-size: 13.5px;
            color: #1a1d2e;
            font-weight: 500;
        }

        .st-toggle-sub {
            font-size: 11.5px;
            color: #9aa0b4;
            margin-top: 2px;
        }

        .db-toggle {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .db-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .db-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #e2e5ef;
            border-radius: 24px;
            transition: .3s;
        }

        .db-toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .3s;
        }

        .db-toggle input:checked+.db-toggle-slider {
            background: #1d2448;
        }

        .db-toggle input:checked+.db-toggle-slider:before {
            transform: translateX(20px);
        }

        /* Step indicator */
        .st-steps {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }

        .st-step {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #9aa0b4;
        }

        .st-step.active {
            color: #1d2448;
            font-weight: 600;
        }

        .st-step-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #e2e5ef;
            color: #9aa0b4;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .st-step.active .st-step-num {
            background: #1d2448;
            color: #fff;
        }

        .st-step.done .st-step-num {
            background: #16c79a;
            color: #fff;
        }

        .st-step-sep {
            flex: 1;
            height: 1px;
            background: #e2e5ef;
        }

        @media (max-width: 900px) {
            .st-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = $role ?? 'secretary';
    $active    = 'settings';
    $pageTitle = 'Settings';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $user     = session()->get('user_id') ? (new \App\Models\UserModel())->find(session()->get('user_id')) : [];
    $systemPrefs = new \App\Models\BarangaySettingsModel();
    $emailAlertsOn = $systemPrefs->enabled('email_notifications');
    $autoApproveOn = $systemPrefs->enabled('auto_approve_clearances');
    $accountAlertsOn = $systemPrefs->enabled('account_approval_alerts');
    $publicContact = $systemPrefs->getAll();
    $canEditPublicContact = session()->get('role') === 'admin';
    $otpSent  = session()->getFlashdata('pw_otp_sent');
    $otpPending = session()->get('pw_otp_pending');
    $initial  = strtoupper(($user['first_name'][0] ?? 'S'));
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="st-alert st-alert--success" style="margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <div class="st-grid">

                <!-- ── Profile Card ── -->
                <div class="st-card">
                    <div class="st-card-header">
                        <div class="st-card-header-icon"><i class="fas fa-user-circle"></i></div>
                        <div>
                            <h4>Profile Information</h4>
                            <p>Update your name, email and contact details</p>
                        </div>
                    </div>
                    <div class="st-card-body">

                        <?php if (session()->getFlashdata('profile_error')): ?>
                            <div class="st-alert st-alert--error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= session()->getFlashdata('profile_error') ?>
                            </div>
                        <?php endif; ?>

                        <!-- Avatar row -->
                        <div class="st-avatar-row">
                            <div class="st-avatar" style="overflow:hidden;position:relative;flex-shrink:0;">
                                <?php
                                $avatarFile = session()->get('avatar');
                                if ($avatarFile && file_exists(FCPATH . 'uploads/avatars/' . $avatarFile)):
                                ?>
                                    <img id="avatarPreview" src="/uploads/avatars/<?= esc($avatarFile) ?>"
                                        alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <span id="avatarInitial" style="font-size:26px;font-weight:700;"><?= $initial ?></span>
                                    <img id="avatarPreview" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:50%;position:absolute;inset:0;">
                                <?php endif; ?>
                            </div>
                            <div class="st-avatar-info">
                                <h5><?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Secretary') ?></h5>
                                <span><?= esc($user['email'] ?? '') ?></span>
                                <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                    <label for="avatarInput" class="db-btn db-btn--outline" style="font-size:12px;padding:5px 12px;cursor:pointer;margin:0;">
                                        <i class="fas fa-camera"></i> Change Photo
                                    </label>
                                    <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="previewAvatar(this)">
                                    <span style="font-size:11px;color:#9aa0b4;">JPG, PNG, GIF or WebP · Max 2MB</span>
                                </div>
                                <div id="avatarUploadActions" style="display:none;margin-top:8px;">
                                    <form action="/<?= $role ?>/settings/avatar" method="post" enctype="multipart/form-data" id="avatarForm">
                                        <?= csrf_field() ?>
                                        <input type="file" name="avatar" id="avatarFormInput" style="display:none;" accept="image/*">
                                        <button type="submit" class="db-btn db-btn--primary" style="font-size:12px;padding:5px 14px;">
                                            <i class="fas fa-upload"></i> Upload Photo
                                        </button>
                                        <button type="button" onclick="cancelAvatar()" class="db-btn db-btn--outline" style="font-size:12px;padding:5px 12px;">
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <form action="/<?= $role ?>/settings/profile" method="post">
                            <?= csrf_field() ?>
                            <div class="st-form-row">
                                <div class="st-form-group">
                                    <label>Last Name</label>
                                    <div class="st-input-wrap">
                                        <i class="fas fa-user st-input-icon"></i>
                                        <input type="text" class="st-input" name="last_name"
                                            value="<?= esc($user['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="st-form-group">
                                    <label>First Name</label>
                                    <div class="st-input-wrap">
                                        <i class="fas fa-user st-input-icon"></i>
                                        <input type="text" class="st-input" name="first_name"
                                            value="<?= esc($user['first_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="st-form-row">
                                <div class="st-form-group">
                                    <label>Middle Name <span style="color:#9aa0b4;font-weight:400;">(optional)</span></label>
                                    <div class="st-input-wrap">
                                        <i class="fas fa-user st-input-icon"></i>
                                        <input type="text" class="st-input" name="middle_name"
                                            value="<?= esc($user['middle_name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="st-form-group">
                                    <label>Username</label>
                                    <div class="st-input-wrap">
                                        <i class="fas fa-at st-input-icon"></i>
                                        <input type="text" class="st-input" value="<?= esc($user['username'] ?? '') ?>" disabled
                                            style="background:#f5f6fa;cursor:not-allowed;">
                                    </div>
                                </div>
                            </div>
                            <div class="st-form-row st-form-row--full" style="margin-bottom:14px;">
                                <div class="st-form-group">
                                    <label>Email Address</label>
                                    <div class="st-input-wrap">
                                        <i class="fas fa-envelope st-input-icon"></i>
                                        <input type="email" class="st-input" name="email"
                                            value="<?= esc($user['email'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="st-form-row st-form-row--full" style="margin-bottom:20px;">
                                <div class="st-form-group">
                                    <label>Contact Number</label>
                                    <div class="st-input-wrap">
                                        <i class="fas fa-phone st-input-icon"></i>
                                        <input type="tel" class="st-input js-contact-number" name="contact_number" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)"
                                            value="<?= esc($user['contact_number'] ?? '') ?>"
                                            placeholder="09XXXXXXXXX">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="st-btn st-btn--primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ── Password Card ── -->
                <div class="st-card">
                    <div class="st-card-header">
                        <div class="st-card-header-icon" style="background:#fff0f1;color:#c0392b;">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h4>Change Password</h4>
                            <p>A verification code will be sent to your Gmail</p>
                        </div>
                    </div>
                    <div class="st-card-body">

                        <?php if (session()->getFlashdata('pw_error')): ?>
                            <div class="st-alert st-alert--error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= session()->getFlashdata('pw_error') ?>
                            </div>
                        <?php endif; ?>

                        <!-- Step indicator -->
                        <div class="st-steps">
                            <div class="st-step <?= (!$otpSent && !$otpPending) ? 'active' : 'done' ?>">
                                <span class="st-step-num"><i class="fas <?= (!$otpSent && !$otpPending) ? 'fa-1' : 'fa-check' ?>"></i></span>
                                <span>Enter Password</span>
                            </div>
                            <div class="st-step-sep"></div>
                            <div class="st-step <?= ($otpSent || $otpPending) ? 'active' : '' ?>">
                                <span class="st-step-num">2</span>
                                <span>Verify Code</span>
                            </div>
                        </div>

                        <?php if (! $otpSent && ! $otpPending): ?>
                            <!-- Step 1: Enter current + new password -->
                            <form action="/<?= $role ?>/settings/request-otp" method="post" id="pwForm">
                                <?= csrf_field() ?>

                                <div class="st-form-group" style="margin-bottom:14px;">
                                    <label>Current Password</label>
                                    <div class="st-input-wrap st-pw-wrap">
                                        <i class="fas fa-lock st-input-icon"></i>
                                        <input type="password" class="st-input" name="current_password"
                                            id="cur_pw" placeholder="Enter current password" required>
                                        <button type="button" class="st-eye-btn" onclick="togglePw('cur_pw','eye_cur')">
                                            <i class="fas fa-eye" id="eye_cur"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="st-form-group" style="margin-bottom:6px;">
                                    <label>New Password</label>
                                    <div class="st-input-wrap st-pw-wrap">
                                        <i class="fas fa-key st-input-icon"></i>
                                        <input type="password" class="st-input" name="new_password"
                                            id="new_pw" placeholder="Minimum 8 characters" minlength="8"
                                            required oninput="checkStrength(this.value)">
                                        <button type="button" class="st-eye-btn" onclick="togglePw('new_pw','eye_new')">
                                            <i class="fas fa-eye" id="eye_new"></i>
                                        </button>
                                    </div>
                                    <div class="st-pw-strength" id="pwStrength" style="display:none;">
                                        <div class="st-pw-bar-track">
                                            <div class="st-pw-bar-fill" id="pwBar"></div>
                                        </div>
                                        <span class="st-pw-label" id="pwLabel"></span>
                                    </div>
                                </div>

                                <div class="st-form-group" style="margin-bottom:20px;">
                                    <label>Confirm New Password</label>
                                    <div class="st-input-wrap st-pw-wrap">
                                        <i class="fas fa-key st-input-icon"></i>
                                        <input type="password" class="st-input" name="confirm_password"
                                            id="conf_pw" placeholder="Re-enter new password" required>
                                        <button type="button" class="st-eye-btn" onclick="togglePw('conf_pw','eye_conf')">
                                            <i class="fas fa-eye" id="eye_conf"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="st-alert st-alert--info" style="margin-bottom:16px;">
                                    <i class="fas fa-info-circle"></i>
                                    <span>A 6-digit verification code will be sent to <strong><?= esc($user['email'] ?? 'your email') ?></strong> to confirm the change.</span>
                                </div>

                                <button type="submit" class="st-btn st-btn--primary" style="width:100%;">
                                    <i class="fas fa-paper-plane"></i> Send Verification Code
                                </button>
                            </form>

                        <?php else: ?>
                            <!-- Step 2: Enter OTP -->
                            <div class="st-alert st-alert--info">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>A 6-digit code was sent to <strong><?= esc($user['email'] ?? 'your email') ?></strong>. Enter it below.</span>
                            </div>

                            <form action="/<?= $role ?>/settings/verify-otp" method="post" id="otpForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="otp" id="otp_hidden">

                                <div class="st-otp-group" id="otpGroup">
                                    <?php for ($i = 0; $i < 6; $i++): ?>
                                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                            autocomplete="off" class="otp-digit">
                                    <?php endfor; ?>
                                </div>

                                <button type="submit" class="st-btn st-btn--primary" style="width:100%;margin-bottom:12px;">
                                    <i class="fas fa-check-circle"></i> Verify & Change Password
                                </button>
                            </form>

                            <div style="text-align:center;font-size:13px;color:#9aa0b4;">
                                Didn't receive the code?
                                <a href="/<?= $role ?>/settings/change-password" style="color:#1d2448;font-weight:600;text-decoration:none;">Resend</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- ── Household Info Card (Captain only) ── -->
                <?php if ($role === 'captain'): ?>
                    <?php
                    $hhModel  = new \App\Models\HouseholdModel();
                    $memModel = new \App\Models\HouseholdMemberModel();
                    $captainHh  = ! empty($user['household_no']) ? $hhModel->find($user['household_no']) : null;
                    $hhMembers  = $captainHh ? $memModel->where('household_no', $user['household_no'])->findAll() : [];
                    $allPeople  = [];
                    if ($captainHh) {
                        $allPeople[] = array_merge($captainHh, ['_role' => 'Household Head']);
                        foreach ($hhMembers as $m) {
                            $allPeople[] = array_merge($m, ['_role' => ucfirst($m['relationship'] ?? 'Member')]);
                        }
                    }
                    $clsMap = [];
                    if ($captainHh) {
                        if ($captainHh['is_4ps'] ?? 0)            $clsMap[] = ['label' => '4Ps Beneficiary',   'color' => '#5b6fd6', 'bg' => '#eef0fb'];
                        if ($captainHh['is_pwd'] ?? 0)            $clsMap[] = ['label' => 'PWD Member',        'color' => '#e67e22', 'bg' => '#fff8f0'];
                        if ($captainHh['is_senior_citizen'] ?? 0) $clsMap[] = ['label' => 'Senior Citizen',    'color' => '#16a085', 'bg' => '#edfaf5'];
                        if ($captainHh['is_solo_parent'] ?? 0)    $clsMap[] = ['label' => 'Solo Parent',       'color' => '#8e44ad', 'bg' => '#f5f0ff'];
                        if ($captainHh['is_indigenous'] ?? 0)     $clsMap[] = ['label' => 'Indigenous People', 'color' => '#b07a00', 'bg' => '#fff8e6'];
                    }
                    ?>

                    <div class="st-card" style="grid-column:1/-1;overflow:hidden;">

                        <!-- Gradient header -->
                        <div style="background:linear-gradient(135deg,#1d2448,#2e3a6e);padding:20px 26px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div>
                                    <h4 style="margin:0 0 3px;font-size:15px;font-weight:700;color:#fff;">Household Information</h4>
                                    <p style="margin:0;font-size:12px;color:rgba(255,255,255,.6);">Your linked household record from the barangay census</p>
                                </div>
                            </div>
                            <?php if ($captainHh): ?>
                                <a href="/captain/household/<?= esc($user['household_no']) ?>"
                                    style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);border-radius:8px;font-size:12.5px;font-weight:600;color:#fff;text-decoration:none;white-space:nowrap;transition:background .15s;"
                                    onmouseover="this.style.background='rgba(255,255,255,.25)'"
                                    onmouseout="this.style.background='rgba(255,255,255,.15)'">
                                    <i class="fas fa-external-link-alt" style="font-size:11px;"></i> View Full Record
                                </a>
                            <?php endif; ?>
                        </div>

                        <div style="padding:24px 26px;">

                            <?php if (! $captainHh): ?>
                                <div style="text-align:center;padding:40px 20px;">
                                    <div style="width:64px;height:64px;border-radius:50%;background:#f0f2f8;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px;color:#d0d5e8;">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <p style="font-size:15px;font-weight:600;color:#4a5068;margin:0 0 8px;">No household linked</p>
                                    <p style="font-size:13px;color:#9aa0b4;margin:0;line-height:1.7;max-width:380px;margin:0 auto;">
                                        Your account is not currently linked to a household in the census records.
                                        Please contact the Secretary to link your account.
                                    </p>
                                </div>

                            <?php else: ?>

                                <!-- ── Stat tiles ── -->
                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(148px,1fr));gap:12px;margin-bottom:20px;">
                                    <?php
                                    $tiles = [
                                        ['label' => 'Household No.',     'value' => $user['household_no'],                          'icon' => 'fa-hashtag',        'c' => '#1d2448'],
                                        ['label' => 'Zone / Purok',      'value' => $captainHh['zone'] ?? '—',                      'icon' => 'fa-map-marker-alt', 'c' => '#5b6fd6'],
                                        ['label' => 'House Ownership',   'value' => $captainHh['house_ownership'] ?? '—',            'icon' => 'fa-key',            'c' => '#16a085'],
                                        ['label' => 'Years Residing',    'value' => current_years_of_residency($captainHh) . ' yrs', 'icon' => 'fa-clock',          'c' => '#e67e22'],
                                        ['label' => 'Total Members',     'value' => count($allPeople) . ' person' . (count($allPeople) !== 1 ? 's' : ''), 'icon' => 'fa-users', 'c' => '#7c5cbf'],
                                    ];
                                    ?>
                                    <?php foreach ($tiles as $t):
                                        [$r, $g, $b] = sscanf(ltrim($t['c'], '#'), '%02x%02x%02x');
                                    ?>
                                        <div style="background:#f8f9fc;border:1px solid #eef0f8;border-radius:12px;padding:14px 16px;">
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                                <div style="width:28px;height:28px;border-radius:7px;background:rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,.13);display:flex;align-items:center;justify-content:center;font-size:12px;color:<?= $t['c'] ?>;flex-shrink:0;">
                                                    <i class="fas <?= $t['icon'] ?>"></i>
                                                </div>
                                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9aa0b4;"><?= $t['label'] ?></span>
                                            </div>
                                            <div style="font-size:15px;font-weight:700;color:#1a1d2e;"><?= esc($t['value']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- ── Classifications ── -->
                                <?php if ($clsMap): ?>
                                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:20px;padding:12px 16px;background:#f8f9fc;border:1px solid #eef0f8;border-radius:12px;">
                                        <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9aa0b4;flex-shrink:0;">Classifications:</span>
                                        <?php foreach ($clsMap as $cls): ?>
                                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;padding:4px 12px;border-radius:100px;background:<?= $cls['bg'] ?>;color:<?= $cls['color'] ?>;border:1px solid <?= $cls['color'] ?>30;">
                                                <i class="fas fa-check-circle" style="font-size:10px;"></i><?= esc($cls['label']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- ── Members heading ── -->
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                    <i class="fas fa-users" style="font-size:13px;color:#5b6fd6;"></i>
                                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#4a5068;">Household Members</span>
                                </div>

                                <!-- ── Member cards ── -->
                                <div style="display:flex;flex-direction:column;gap:10px;">
                                    <?php foreach ($allPeople as $person):
                                        $pName   = trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
                                        $pDob    = !empty($person['date_of_birth']) ? date('M d, Y', strtotime($person['date_of_birth'])) : null;
                                        $pAge    = !empty($person['date_of_birth'])
                                            ? (int) date_diff(date_create($person['date_of_birth']), date_create('today'))->y
                                            : null;
                                        $pRel    = $person['_role'] ?? ucfirst($person['relationship'] ?? 'Member');
                                        $isHead  = $pRel === 'Household Head';
                                        $gender  = $person['gender'] ?? null;
                                        $occ     = $person['occupation'] ?? null;
                                        $initial = strtoupper($person['first_name'][0] ?? '?');
                                    ?>
                                        <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:<?= $isHead ? '#f8f9ff' : '#fff' ?>;border:1.5px solid <?= $isHead ? '#1d2448' : '#eef0f8' ?>;border-radius:12px;transition:box-shadow .15s;"
                                            onmouseover="this.style.boxShadow='0 4px 12px rgba(29,36,72,.08)'"
                                            onmouseout="this.style.boxShadow=''">

                                            <!-- Avatar -->
                                            <div style="width:46px;height:46px;border-radius:50%;background:<?= $isHead ? 'linear-gradient(135deg,#1d2448,#2e3a6e)' : '#eef0fb' ?>;color:<?= $isHead ? '#fff' : '#5b6fd6' ?>;font-size:17px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <?= $initial ?>
                                            </div>

                                            <!-- Info -->
                                            <div style="flex:1;min-width:0;">
                                                <div style="font-size:14px;font-weight:<?= $isHead ? '700' : '600' ?>;color:#1a1d2e;margin-bottom:5px;"><?= esc($pName) ?></div>
                                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                                                    <?php if ($pDob): ?>
                                                        <span><i class="fas fa-birthday-cake" style="margin-right:4px;font-size:10px;color:#b0b6cc;"></i><?= $pDob ?><?= $pAge !== null ? " ($pAge yrs)" : ''; ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($gender): ?>
                                                        <span style="color:#d0d4df;">·</span>
                                                        <span><i class="fas <?= strtolower($gender) === 'female' ? 'fa-venus' : 'fa-mars' ?>" style="margin-right:4px;font-size:10px;color:#b0b6cc;"></i><?= esc($gender) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($occ): ?>
                                                        <span style="color:#d0d4df;">·</span>
                                                        <span><i class="fas fa-briefcase" style="margin-right:4px;font-size:10px;color:#b0b6cc;"></i><?= esc($occ) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Badge -->
                                            <span style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;padding:5px 12px;border-radius:100px;background:<?= $isHead ? '#1d2448' : '#f3f4f8' ?>;color:<?= $isHead ? '#fff' : '#6b7280' ?>;">
                                                <?php if ($isHead): ?><i class="fas fa-home" style="font-size:9px;"></i><?php endif; ?>
                                                <?= esc($pRel) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- ── System Settings Card ── -->
                <div class="st-card" style="grid-column: 1 / -1;">
                    <div class="st-card-header">
                        <div class="st-card-header-icon"><i class="fas fa-cog"></i></div>
                        <div>
                            <h4>System Settings</h4>
                            <p>Configure system-wide preferences</p>
                        </div>
                    </div>
                    <div class="st-card-body">
                        <form id="systemSettingsForm" action="/<?= esc($role) ?>/settings/system" method="post">
                            <?= csrf_field() ?>
                            <div class="st-toggle-list">
                                <div class="st-toggle-item">
                                    <div>
                                        <div class="st-toggle-label">Email Notifications</div>
                                        <div class="st-toggle-sub">Receive email alerts for new clearance requests and approvals</div>
                                    </div>
                                    <label class="db-toggle">
                                        <input type="checkbox" name="email_notifications" value="1" <?= $emailAlertsOn ? 'checked' : '' ?>>
                                        <span class="db-toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="st-toggle-item">
                                    <div>
                                        <div class="st-toggle-label">Auto-approve Clearances</div>
                                        <div class="st-toggle-sub">Automatically approve clearance requests that meet all criteria</div>
                                    </div>
                                    <label class="db-toggle">
                                        <input type="checkbox" name="auto_approve_clearances" value="1" <?= $autoApproveOn ? 'checked' : '' ?>>
                                        <span class="db-toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="st-toggle-item">
                                    <div>
                                        <div class="st-toggle-label">Account Approval Alerts</div>
                                        <div class="st-toggle-sub">Get notified when new resident or SK accounts are pending approval</div>
                                    </div>
                                    <label class="db-toggle">
                                        <input type="checkbox" name="account_approval_alerts" value="1" <?= $accountAlertsOn ? 'checked' : '' ?>>
                                        <span class="db-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <?php if ($canEditPublicContact): ?>
                                <div style="margin-top:22px;padding-top:18px;border-top:1px solid #e8ecf4;">
                                    <h5 style="margin:0 0 4px;font-size:14px;color:#1a1d2e;">Public contact</h5>
                                    <p style="margin:0 0 14px;font-size:12px;color:#6b7280;">These details appear in the website footer and social icons.</p>
                                    <div class="st-form-row">
                                        <div class="st-form-group">
                                            <label for="public_address">Address</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_address" name="public_address" type="text" value="<?= esc((string) ($publicContact['public_address'] ?? '')) ?>">
                                        </div>
                                        <div class="st-form-group">
                                            <label for="public_phone">Phone</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_phone" name="public_phone" type="text" value="<?= esc((string) ($publicContact['public_phone'] ?? '')) ?>">
                                        </div>
                                    </div>
                                    <div class="st-form-row">
                                        <div class="st-form-group">
                                            <label for="public_email">Email</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_email" name="public_email" type="text" value="<?= esc((string) ($publicContact['public_email'] ?? '')) ?>">
                                        </div>
                                        <div class="st-form-group">
                                            <label for="public_hours">Office hours</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_hours" name="public_hours" type="text" value="<?= esc((string) ($publicContact['public_hours'] ?? '')) ?>">
                                        </div>
                                    </div>
                                    <div class="st-form-row">
                                        <div class="st-form-group">
                                            <label for="public_facebook">Facebook link</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_facebook" name="public_facebook" type="text" placeholder="https://facebook.com/..." value="<?= esc((string) ($publicContact['public_facebook'] ?? '')) ?>">
                                        </div>
                                        <div class="st-form-group">
                                            <label for="public_twitter">Twitter link</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_twitter" name="public_twitter" type="text" placeholder="https://twitter.com/..." value="<?= esc((string) ($publicContact['public_twitter'] ?? '')) ?>">
                                        </div>
                                    </div>
                                    <div class="st-form-row st-form-row--full">
                                        <div class="st-form-group">
                                            <label for="public_email_link">Email icon link</label>
                                            <input class="st-input" style="padding-left:12px;" id="public_email_link" name="public_email_link" type="text" placeholder="Leave blank to use the email above" value="<?= esc((string) ($publicContact['public_email_link'] ?? '')) ?>">
                                        </div>
                                    </div>
                                    <button type="button" class="st-btn st-btn--primary" id="savePublicContact" style="margin-top:4px;">Save public contact</button>
                                </div>
                            <?php endif; ?>
                            <p id="systemSettingsStatus" style="margin:14px 0 0;font-size:12px;color:#6b7280;min-height:16px;"></p>
                        </form>
                    </div>
                </div>



            </div>
        </div>
    </div>

    <script>
        // Password toggle
        function togglePw(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength
        function checkStrength(val) {
            const el = document.getElementById('pwStrength');
            const bar = document.getElementById('pwBar');
            const label = document.getElementById('pwLabel');
            if (!val) {
                el.style.display = 'none';
                return;
            }
            el.style.display = 'block';
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const levels = [{
                    w: '25%',
                    bg: '#e74c3c',
                    text: 'Weak'
                },
                {
                    w: '50%',
                    bg: '#e67e22',
                    text: 'Fair'
                },
                {
                    w: '75%',
                    bg: '#f1c40f',
                    text: 'Good'
                },
                {
                    w: '100%',
                    bg: '#16c79a',
                    text: 'Strong'
                },
            ];
            const lvl = levels[score - 1] || levels[0];
            bar.style.width = lvl.w;
            bar.style.background = lvl.bg;
            label.textContent = lvl.text;
            label.style.color = lvl.bg;
        }

        // Password form validation
        const pwForm = document.getElementById('pwForm');
        if (pwForm) {
            pwForm.addEventListener('submit', function(e) {
                const pw = document.getElementById('new_pw').value;
                const cpw = document.getElementById('conf_pw').value;
                if (pw !== cpw) {
                    e.preventDefault();
                    alert('New passwords do not match.');
                }
            });
        }

        // OTP digit inputs
        const digits = document.querySelectorAll('.otp-digit');
        const hidden = document.getElementById('otp_hidden');
        const otpForm = document.getElementById('otpForm');

        if (digits.length) {
            digits.forEach((input, idx) => {
                input.addEventListener('input', (e) => {
                    input.value = input.value.replace(/\D/g, '');
                    if (input.value) {
                        input.classList.add('filled');
                        if (idx < digits.length - 1) digits[idx + 1].focus();
                    } else {
                        input.classList.remove('filled');
                    }
                    syncOtp();
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && idx > 0) {
                        digits[idx - 1].focus();
                        digits[idx - 1].value = '';
                        digits[idx - 1].classList.remove('filled');
                        syncOtp();
                    }
                });
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData)
                        .getData('text').replace(/\D/g, '').slice(0, 6);
                    pasted.split('').forEach((ch, i) => {
                        if (digits[i]) {
                            digits[i].value = ch;
                            digits[i].classList.add('filled');
                        }
                    });
                    syncOtp();
                    digits[Math.min(pasted.length, digits.length - 1)].focus();
                });
            });
            digits[0].focus();
        }

        function syncOtp() {
            if (hidden) hidden.value = Array.from(digits).map(d => d.value).join('');
        }

        if (otpForm) {
            otpForm.addEventListener('submit', (e) => {
                syncOtp();
                if (hidden.value.length < 6) {
                    e.preventDefault();
                    alert('Please enter the complete 6-digit code.');
                }
            });
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );

        // ── Avatar preview & upload ──────────────────────────────────────────
        function previewAvatar(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be smaller than 2MB.');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const initial = document.getElementById('avatarInitial');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (initial) initial.style.display = 'none';
                // Copy file to the hidden form input
                const dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('avatarFormInput').files = dt.files;
                document.getElementById('avatarUploadActions').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        function cancelAvatar() {
            const preview = document.getElementById('avatarPreview');
            const initial = document.getElementById('avatarInitial');
            const avatarFile = '<?= esc(session()->get('avatar') ?? '') ?>';
            if (avatarFile) {
                preview.src = '/uploads/avatars/' + avatarFile;
                preview.style.display = 'block';
                if (initial) initial.style.display = 'none';
            } else {
                preview.src = '';
                preview.style.display = 'none';
                if (initial) initial.style.display = '';
            }
            document.getElementById('avatarUploadActions').style.display = 'none';
            document.getElementById('avatarInput').value = '';
        }

        // Reset password form — set action dynamically based on selected user
        function updateResetAction(userId) {
            const form = document.getElementById('resetPwForm');
            if (userId) {
                form.action = '/secretary/reset-password/' + userId;
            } else {
                form.action = '';
            }
        }

        function prepareResetForm(e) {
            const userId = document.getElementById('resetUserId').value;
            if (!userId) {
                e.preventDefault();
                alert('Please select a user.');
                return false;
            }
            const pw = document.getElementById('reset_pw1').value;
            const cpw = document.getElementById('reset_pw2').value;
            if (pw !== cpw) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }
            if (pw.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters.');
                return false;
            }
            return confirm('Reset password for this user? They will need to use the new password to log in.');
        }

        (function () {
            const form = document.getElementById('systemSettingsForm');
            const status = document.getElementById('systemSettingsStatus');
            if (!form) return;

            const names = ['email_notifications', 'auto_approve_clearances', 'account_approval_alerts'];

            function saveSystemSettings() {
                const data = new FormData(form);
                names.forEach(function (name) {
                    if (!data.has(name)) data.set(name, '0');
                });
                if (status) {
                    status.textContent = 'Saving…';
                    status.style.color = '#6b7280';
                }
                fetch(form.action, {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        if (!status) return;
                        status.textContent = payload.message || 'System settings saved.';
                        status.style.color = payload.success ? '#15803d' : '#b91c1c';
                    })
                    .catch(function () {
                        if (!status) return;
                        status.textContent = 'Could not save system settings.';
                        status.style.color = '#b91c1c';
                    });
            }

            form.querySelectorAll('input[type="checkbox"]').forEach(function (box) {
                box.addEventListener('change', saveSystemSettings);
            });

            const saveContact = document.getElementById('savePublicContact');
            if (saveContact) {
                saveContact.addEventListener('click', saveSystemSettings);
            }
        })();
    </script>
</body>

</html>