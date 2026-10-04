<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\EmailService;
use App\Libraries\SessionHelper;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public static function sessionSnapshot(array $sessionData = []): array
    {
        $userId = (int) ($sessionData['user_id'] ?? $sessionData['id'] ?? 0);
        if ($userId <= 0) {
            return [];
        }

        $role = trim((string) ($sessionData['role'] ?? 'resident'));

        return [
            'user_id' => $userId,
            'id' => $userId,
            'role' => $role === '' ? 'resident' : $role,
            'avatar' => $sessionData['avatar'] ?? null,
            'household_no' => $sessionData['household_no'] ?? null,
            'saved_at' => date('c'),
            'expires_at' => gmdate('c', time() + (7 * 24 * 60 * 60)),
        ];
    }

    public static function isOfflineSessionValid(array $snapshot): bool
    {
        if (! isset($snapshot['user_id'], $snapshot['role'], $snapshot['expires_at'])) {
            return false;
        }

        $expiresAt = strtotime((string) $snapshot['expires_at']);
        if ($expiresAt === false || $expiresAt <= time()) {
            return false;
        }

        return (int) ($snapshot['user_id'] ?? 0) > 0 && trim((string) ($snapshot['role'] ?? '')) !== '';
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $isApiRequest = $this->isApiRequest();

        if (empty($username) || empty($password)) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Please enter your username and password.',
                ]);
            }

            return redirect()->to('/login')->with('error', 'Please enter your username and password.');
        }

        $user = $this->userModel->findByCredentials($username, $password);

        if (! $user) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid username or password.',
                ]);
            }

            return redirect()->to('/login')->with('error', 'Invalid username or password.');
        }

        // Block unverified email
        if (! $user['email_verified']) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Please verify your email address first. Check your inbox for the verification link.',
                ]);
            }

            return redirect()->to('/login')->with('error', 'Please verify your email address first. Check your inbox for the verification link.');
        }

        // Block pending accounts
        if ($user['status'] === 'pending') {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Your account is pending approval by the barangay secretary.',
                ]);
            }

            return redirect()->to('/login')->with('error', 'Your account is pending approval by the barangay secretary.');
        }

        // Block deceased resident accounts
        if ($user['status'] === 'deceased') {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'This account was closed after the resident was marked deceased. Please contact the barangay office.',
                ]);
            }

            return redirect()->to('/login')->with('error', 'This account was closed after the resident was marked deceased. Please contact the barangay office.');
        }

        // Block rejected accounts
        if ($user['status'] === 'rejected') {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Your account registration was not approved. Please contact the barangay office.',
                ]);
            }

            return redirect()->to('/login')->with('error', 'Your account registration was not approved. Please contact the barangay office.');
        }

        // Compose display name for session
        $displayName = trim(
            $user['first_name'] . ' ' .
                ($user['middle_name'] ? $user['middle_name'] . ' ' : '') .
                $user['last_name']
        );
        $userRole = strtolower(trim((string) $user['role']));

        // Each role gets its own cookie, so a second account in another tab
        // does not replace the first account's session.
        $remember = $this->request->getPost('remember_me') === '1';
        SessionHelper::configureForRole($userRole);
        SessionHelper::setLifetime($remember);

        $rememberCookie = SessionHelper::rememberCookieName($userRole);
        if ($remember) {
            $this->response->setCookie($rememberCookie, '1', SessionHelper::REMEMBER_SECONDS);
        } else {
            $this->response->deleteCookie($rememberCookie);
        }

        // Regenerate the session ID to prevent session fixation attacks.
        session()->regenerate(true);
        session()->set([
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'last_name'    => $user['last_name'],
            'first_name'   => $user['first_name'],
            'middle_name'  => $user['middle_name'] ?? '',
            'full_name'    => $displayName, // composed for display convenience
            'role'         => $userRole,
            'avatar'       => $user['avatar'] ?? null,
            'household_no' => $user['household_no'] ?? null,
        ]);

        if ($isApiRequest) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => '/' . $userRole . '/dashboard',
            ]);
        }

        return redirect()->to('/' . $userRole . '/dashboard')->withCookies();
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout()
    {
        $role = strtolower(trim((string) $this->request->getGet('role')));
        if ($role !== '') {
            SessionHelper::configureForRole($role);
        } else {
            $role = SessionHelper::configureFromCookie() ?? '';
        }

        session()->destroy();

        $redirect = redirect()->to('/');
        if ($role !== '') {
            $redirect->deleteCookie(SessionHelper::rememberCookieName($role));
        }

        return $redirect;
    }

    // ── Public Registration (Resident & SK only) ──────────────────────────────

    public function register()
    {
        // Public signup is always 'resident' — SK/officials are created by admin
        $role = 'resident';

        $lastName        = trim($this->request->getPost('last_name') ?? '');
        $firstName       = trim($this->request->getPost('first_name') ?? '');
        $middleName      = trim($this->request->getPost('middle_name') ?? '');
        $email           = $this->request->getPost('email');
        $username        = $this->request->getPost('username');
        $password        = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match.')->withInput();
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters.')->withInput();
        }

        // ── Resident census verification ──────────────────────────────────
        if ($role === 'resident') {
            $householdNo = trim($this->request->getPost('household_no') ?? '');

            if (empty($householdNo)) {
                return redirect()->back()->with('error', 'Household number is required for resident registration.')->withInput();
            }

            // Look up the household in the census
            $householdModel = new \App\Models\HouseholdModel();
            $household      = $householdModel->find($householdNo);

            if (! $household) {
                return redirect()->back()->with('error', 'Household number ' . esc($householdNo) . ' was not found in the census. Please check your household number or contact the barangay office.')->withInput();
            }

            // Verify the entered name matches the household head OR any member
            // Compare against both "First Last" and "Last First" patterns
            $enteredFull    = strtoupper(trim("$firstName $lastName"));
            $enteredFullAlt = strtoupper(trim("$lastName $firstName"));

            // Check household head
            $headFull    = strtoupper(trim($household['first_name'] . ' ' . $household['last_name']));
            $headFullAlt = strtoupper(trim($household['last_name'] . ' ' . $household['first_name']));

            $memberModel = new \App\Models\HouseholdMemberModel();
            $members     = $memberModel->where('household_no', $householdNo)->findAll();

            $nameFound = ($enteredFull === $headFull || $enteredFull === $headFullAlt
                || $enteredFullAlt === $headFull || $enteredFullAlt === $headFullAlt);

            if (! $nameFound) {
                foreach ($members as $m) {
                    $mFull    = strtoupper(trim($m['first_name'] . ' ' . $m['last_name']));
                    $mFullAlt = strtoupper(trim($m['last_name'] . ' ' . $m['first_name']));
                    if (
                        $enteredFull === $mFull || $enteredFull === $mFullAlt
                        || $enteredFullAlt === $mFull || $enteredFullAlt === $mFullAlt
                    ) {
                        $nameFound = true;
                        break;
                    }
                }
            }

            if (! $nameFound) {
                return redirect()->back()->with('error', 'Your name does not match any member recorded under Household #' . esc($householdNo) . '. Please check your name and household number, or contact the barangay office.')->withInput();
            }

            // ── Minor check: block registration if the matched person is under 18 ──
            // Find the DOB of the matched person (head or member)
            $matchedDob = null;

            $headFull2    = strtoupper(trim($household['first_name'] . ' ' . $household['last_name']));
            $headFullAlt2 = strtoupper(trim($household['last_name'] . ' ' . $household['first_name']));

            if (
                $enteredFull === $headFull2 || $enteredFull === $headFullAlt2
                || $enteredFullAlt === $headFull2 || $enteredFullAlt === $headFullAlt2
            ) {
                $matchedDob = $household['date_of_birth'] ?? null;
            } else {
                foreach ($members as $m) {
                    $mFull2    = strtoupper(trim($m['first_name'] . ' ' . $m['last_name']));
                    $mFullAlt2 = strtoupper(trim($m['last_name'] . ' ' . $m['first_name']));
                    if (
                        $enteredFull === $mFull2 || $enteredFull === $mFullAlt2
                        || $enteredFullAlt === $mFull2 || $enteredFullAlt === $mFullAlt2
                    ) {
                        $matchedDob = $m['date_of_birth'] ?? null;
                        break;
                    }
                }
            }

            if (! empty($matchedDob)) {
                $age = (int) date_diff(date_create($matchedDob), date_create('today'))->y;
                if ($age < 18) {
                    return redirect()->back()
                        ->with('error', 'Account registration is only allowed for residents who are 18 years old or above. Minors cannot create an account.')
                        ->withInput();
                }
            }
        }

        $otp     = strval(random_int(100000, 999999));
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $saved = $this->userModel->save([
            'last_name'            => $lastName,
            'first_name'           => $firstName,
            'middle_name'          => $middleName ?: null,
            'email'                => $email,
            'username'             => $username,
            'password'             => password_hash($password, PASSWORD_BCRYPT),
            'role'                 => $role,
            'status'               => 'unverified',
            'email_verified'       => 0,
            'verify_token'         => $otp,
            'verify_token_expires' => $expires,
            'household_no'         => ($role === 'resident') ? ($householdNo ?? null) : null,
        ]);

        if (! $saved) {
            $errors = implode(' ', $this->userModel->errors());
            return redirect()->back()->with('error', $errors)->withInput();
        }

        // Send OTP email — use "First Last" as the greeting name
        $displayName = trim("$firstName $lastName");
        try {
            $emailService = new EmailService();
            $emailService->sendVerificationEmail($email, $displayName, $otp);
        } catch (\Throwable $e) {
            log_message('error', 'Verification email failed: ' . $e->getMessage());
            if (ENVIRONMENT === 'development') {
                throw $e;
            }
            return redirect()->to('/login')->with('error', 'Account created but we could not send the verification email. Please contact the barangay office.');
        }

        // Store email in session so the verify page knows who to verify
        session()->set('pending_verify_email', $email);

        return redirect()->to('/verify-email');
    }

    // ── Show OTP entry page ───────────────────────────────────────────────────

    public function showVerifyEmail()
    {
        if (! session()->get('pending_verify_email')) {
            return redirect()->to('/login');
        }

        return view('verify_email');
    }

    // ── Handle OTP submission ─────────────────────────────────────────────────

    public function verifyEmail()
    {
        $email = session()->get('pending_verify_email');

        if (! $email) {
            return redirect()->to('/login')->with('error', 'Session expired. Please register again.');
        }

        $enteredOtp = trim($this->request->getPost('otp'));

        // Find user by email
        $user = $this->userModel->where('email', $email)->where('email_verified', 0)->first();

        if (! $user) {
            return redirect()->to('/login')->with('error', 'Account not found or already verified.');
        }

        // Check expiry
        if (strtotime($user['verify_token_expires']) < time()) {
            return redirect()->to('/verify-email')->with('error', 'Your code has expired. Please register again.');
        }

        // Check OTP
        if ($user['verify_token'] !== $enteredOtp) {
            return redirect()->to('/verify-email')->with('error', 'Incorrect verification code. Please try again.');
        }

        // Mark verified → status becomes pending (awaiting captain/secretary approval)
        $this->userModel->markEmailVerified($user['id']);
        $this->notifyStaffPendingAccount($user);
        session()->remove('pending_verify_email');

        return redirect()->to('/login')->with('success', 'Email verified! Your account is now pending approval by the barangay captain or secretary.');
    }

    /**
     * Tell admin, secretary, and captain that a resident or SK account is waiting for approval.
     */
    private function notifyStaffPendingAccount(array $user): void
    {
        $accountRole = (string) ($user['role'] ?? 'resident');
        if (! in_array($accountRole, ['resident', 'sk'], true)) {
            return;
        }

        $settings = new \App\Models\BarangaySettingsModel();
        if (! $settings->enabled('account_approval_alerts')) {
            return;
        }

        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'A new account';
        $kind = $accountRole === 'sk' ? 'SK' : 'resident';
        $staff = \Config\Database::connect()->table('users')
            ->select('id, email, first_name, last_name, role')
            ->whereIn('role', ['admin', 'secretary', 'captain'])
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        $mail = null;
        if ($settings->enabled('email_notifications')) {
            try {
                $mail = new EmailService();
            } catch (\Throwable $e) {
                log_message('error', 'Pending account mailer failed: ' . $e->getMessage());
            }
        }

        foreach ($staff as $person) {
            $link = match ($person['role']) {
                'captain' => '/captain/pending-accounts',
                'admin'   => '/admin/residents?account=pending',
                default   => '/secretary/residents?account=pending',
            };

            \App\Models\NotificationModel::push(
                (int) $person['id'],
                'account_pending',
                'Account pending approval',
                $name . ' verified a new ' . $kind . ' account and is waiting for approval.',
                $link
            );

            if (! $mail) {
                continue;
            }

            $email = trim((string) ($person['email'] ?? ''));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $staffName = trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? '')) ?: 'Staff';
            try {
                $mail->sendStaffNotice(
                    $email,
                    $staffName,
                    'New ' . $kind . ' account pending approval',
                    $name . ' verified their email and is waiting for approval.'
                );
            } catch (\Throwable $e) {
                log_message('error', 'Pending account email failed: ' . $e->getMessage());
            }
        }
    }

    // ── Resend OTP ────────────────────────────────────────────────────────────

    public function resendOtp()
    {
        $email = session()->get('pending_verify_email');

        if (! $email) {
            return redirect()->to('/login')->with('error', 'Session expired. Please register again.');
        }

        $user = $this->userModel->where('email', $email)->where('email_verified', 0)->first();

        if (! $user) {
            return redirect()->to('/login')->with('error', 'Account not found or already verified.');
        }

        // Generate a fresh OTP
        $otp     = strval(random_int(100000, 999999));
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->userModel->update($user['id'], [
            'verify_token'         => $otp,
            'verify_token_expires' => $expires,
        ]);

        $displayName = trim($user['first_name'] . ' ' . $user['last_name']);
        try {
            $emailService = new EmailService();
            $emailService->sendVerificationEmail($email, $displayName, $otp);
        } catch (\Throwable $e) {
            log_message('error', 'Resend OTP failed: ' . $e->getMessage());
            if (ENVIRONMENT === 'development') {
                throw $e;
            }
            return redirect()->to('/verify-email')->with('error', 'Could not resend the code. Please try again.');
        }

        return redirect()->to('/verify-email')->with('success', 'A new verification code has been sent to your email.');
    }

    // ── Forgot Password — Step 1: Show form ──────────────────────────────────

    public function showForgotPassword()
    {
        return view('forgot_password');
    }

    // ── Forgot Password — Step 2: Send OTP to email ───────────────────────────

    public function sendForgotPasswordOtp()
    {
        $email = trim($this->request->getPost('email') ?? '');
        $isApiRequest = $this->isApiRequest();

        if (empty($email)) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Please enter your email address.',
                ]);
            }

            return redirect()->back()->with('error', 'Please enter your email address.');
        }

        // Look up user by email — don't reveal whether it exists (security)
        $user = $this->userModel
            ->select('id, last_name, first_name, middle_name, email, status, email_verified')
            ->where('email', $email)
            ->first();

        if ($user && $user['email_verified'] && $user['status'] === 'active') {
            $otp     = strval(random_int(100000, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $this->userModel->update($user['id'], [
                'verify_token'         => $otp,
                'verify_token_expires' => $expires,
            ]);

            $displayName = trim($user['first_name'] . ' ' . $user['last_name']);

            try {
                $emailService = new EmailService();
                $emailService->sendPasswordResetOtp($user['email'], $displayName, $otp);
            } catch (\Throwable $e) {
                log_message('error', 'Forgot password OTP failed: ' . $e->getMessage());
                if (ENVIRONMENT === 'development') throw $e;
                if ($isApiRequest) {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Could not send the reset code. Please try again.',
                    ]);
                }
                return redirect()->back()->with('error', 'Could not send the reset code. Please try again.');
            }
        }

        // Always store email in session and redirect — prevents email enumeration
        session()->set('fp_email', $email);

        if ($isApiRequest) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'If that email is registered, a reset code has been sent.',
                'redirect' => '/forgot-password/verify',
            ]);
        }

        return redirect()->to('/forgot-password/verify')
            ->with('success', 'If that email is registered, a reset code has been sent.');
    }

    // ── Forgot Password — Step 3: Show OTP entry ─────────────────────────────

    public function showForgotPasswordOtp()
    {
        if (! session()->get('fp_email')) {
            return redirect()->to('/forgot-password');
        }
        return view('reset_password_otp');
    }

    // ── Forgot Password — Step 4: Verify OTP ─────────────────────────────────

    public function verifyForgotPasswordOtp()
    {
        $email = session()->get('fp_email');
        $isApiRequest = $this->isApiRequest();
        if (! $email) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Session expired. Please request a new reset code.',
                ]);
            }
            return redirect()->to('/forgot-password');
        }

        $otp = trim($this->request->getPost('otp') ?? '');

        $user = $this->userModel
            ->select('id, verify_token, verify_token_expires')
            ->where('email', $email)
            ->first();

        if (! $user || $user['verify_token'] !== $otp) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Incorrect code. Please try again.',
                ]);
            }
            return redirect()->back()->with('error', 'Incorrect code. Please try again.');
        }

        if (strtotime($user['verify_token_expires']) < time()) {
            session()->remove('fp_email');
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Your reset code has expired. Please request a new one.',
                ]);
            }
            return redirect()->to('/forgot-password')
                ->with('error', 'Your reset code has expired. Please request a new one.');
        }

        // OTP valid — mark as verified for the reset step
        session()->set('fp_verified', true);
        session()->set('fp_user_id', $user['id']);

        // Clear the token so it can't be reused
        $this->userModel->update($user['id'], [
            'verify_token'         => null,
            'verify_token_expires' => null,
        ]);

        if ($isApiRequest) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'OTP verified successfully.',
                'redirect' => '/forgot-password/new-password',
            ]);
        }

        return redirect()->to('/forgot-password/new-password');
    }

    // ── Forgot Password — Step 5: Resend OTP ─────────────────────────────────

    public function resendForgotPasswordOtp()
    {
        $email = session()->get('fp_email');
        if (! $email) {
            return redirect()->to('/forgot-password');
        }

        $user = $this->userModel
            ->select('id, last_name, first_name, email')
            ->where('email', $email)
            ->first();

        if ($user) {
            $otp     = strval(random_int(100000, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $this->userModel->update($user['id'], [
                'verify_token'         => $otp,
                'verify_token_expires' => $expires,
            ]);

            $displayName = trim($user['first_name'] . ' ' . $user['last_name']);
            try {
                $emailService = new EmailService();
                $emailService->sendPasswordResetOtp($user['email'], $displayName, $otp);
            } catch (\Throwable $e) {
                log_message('error', 'Resend forgot password OTP failed: ' . $e->getMessage());
                if (ENVIRONMENT === 'development') throw $e;
                return redirect()->to('/forgot-password/verify')
                    ->with('error', 'Could not resend the code. Please try again.');
            }
        }

        return redirect()->to('/forgot-password/verify')
            ->with('success', 'A new reset code has been sent to your email.');
    }

    // ── Forgot Password — Step 6: Show new password form ─────────────────────

    public function showNewPassword()
    {
        if (! session()->get('fp_verified')) {
            return redirect()->to('/forgot-password');
        }
        return view('reset_password_new');
    }

    // ── Forgot Password — Step 7: Save new password ───────────────────────────

    public function saveNewPassword()
    {
        $isApiRequest = $this->isApiRequest();
        if (! session()->get('fp_verified') || ! session()->get('fp_user_id')) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Your password reset session has expired. Please request a new reset code.',
                ]);
            }
            return redirect()->to('/forgot-password');
        }

        $newPassword     = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if (strlen($newPassword) < 8) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Password must be at least 8 characters.',
                ]);
            }
            return redirect()->back()->with('error', 'Password must be at least 8 characters.');
        }

        if ($newPassword !== $confirmPassword) {
            if ($isApiRequest) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Passwords do not match.',
                ]);
            }
            return redirect()->back()->with('error', 'Passwords do not match.');
        }

        $userId = (int) session()->get('fp_user_id');

        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);

        // Clear all forgot-password session data
        session()->remove(['fp_email', 'fp_verified', 'fp_user_id']);

        if ($isApiRequest) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Password reset successfully. You can now sign in with your new password.',
                'redirect' => '/login',
            ]);
        }

        return redirect()->to('/login')
            ->with('success', 'Password reset successfully. You can now sign in with your new password.');
    }

    // ── Pending Accounts (Captain & Secretary) ────────────────────────────────

    public function pendingAccounts()
    {
        $search = \App\Libraries\RecordSearch::term();
        $pending = \App\Libraries\RecordSearch::filter(
            $this->userModel->getPendingAccounts(),
            $search,
            static fn(array $user): string => implode(' ', [
                (string) ($user['first_name'] ?? ''),
                (string) ($user['last_name'] ?? ''),
                (string) ($user['username'] ?? ''),
                (string) ($user['email'] ?? ''),
                (string) ($user['role'] ?? ''),
            ]),
            static fn(array $user): array => [$user['created_at'] ?? null]
        );
        $role    = session()->get('role');

        return view('dashboard/' . staff_view_folder($role) . '/pending_accounts', [
            'pending' => $pending,
            'search'  => $search,
        ]);
    }

    public function approveAccount(int $id)
    {
        $user = $this->userModel->find($id);
        $this->userModel->approveUser($id);

        if ($user && ! empty($user['email'])) {
            try {
                $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'Resident');
                (new EmailService())->sendAccountApprovalEmail($user['email'], $displayName);
            } catch (\Throwable $e) {
                log_message('error', 'Account approval email failed for user ' . $id . ': ' . $e->getMessage());
            }
        }

        $role = session()->get('role');
        $back = in_array($role, ['secretary', 'admin'], true) ? '/' . $role . '/residents' : '/' . $role . '/pending-accounts';
        return redirect()->to($back)->with('success', 'Account approved successfully.');
    }

    public function rejectAccount(int $id)
    {
        $this->userModel->rejectUser($id);
        $role = session()->get('role');
        $back = in_array($role, ['secretary', 'admin'], true) ? '/' . $role . '/residents' : '/' . $role . '/pending-accounts';
        return redirect()->to($back)->with('success', 'Account rejected.');
    }

    // ── Promote existing resident → official role (Secretary only) ───────────

    public function promoteResident()
    {
        $callerRole = session()->get('role');
        $targetId  = (int) $this->request->getPost('user_id');
        $newRole   = strtolower(trim($this->request->getPost('role') ?? ''));
        $councilZone = trim((string) $this->request->getPost('council_zone'));

        if ($callerRole === 'captain' && $newRole !== 'secretary') {
            return redirect()->back()->with('error', 'The captain can appoint a Secretary only.')->withInput();
        }
        if (! in_array($callerRole, ['admin', 'captain'], true)) {
            return redirect()->back()->with('error', 'Only the admin can appoint officials.');
        }

        $allowed = ['admin', 'captain', 'secretary', 'sk', 'council'];
        if (! in_array($newRole, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid role for promotion.')->withInput();
        }

        if ($newRole === 'council' && ! in_array($councilZone, ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'], true)) {
            return redirect()->back()->with('error', 'Select a zone for the Barangay Council assignment.')->withInput();
        }
        if ($newRole === 'council') {
            $zoneTaken = $this->userModel
                ->where('role', 'council')
                ->where('status', 'active')
                ->where('council_zone', $councilZone)
                ->first();
            if ($zoneTaken) {
                return redirect()->back()->with('error', $councilZone . ' already has an assigned council member.')->withInput();
            }
        }

        // Load target user
        $target = $this->userModel->find($targetId);
        if (! $target || $target['role'] !== 'resident' || $target['status'] !== 'active') {
            return redirect()->back()->with('error', 'Selected user is not an eligible active resident.')->withInput();
        }

        // Age check — use the resident's own DOB (from household_members if a member,
        // otherwise the household head row).
        if (! empty($target['household_no'])) {
            $db  = \Config\Database::connect();

            // Try to find this person as a household member first
            $memberRow = $db->table('household_members')
                ->where('household_no', $target['household_no'])
                ->where('UPPER(TRIM(first_name))', strtoupper(trim($target['first_name'] ?? '')))
                ->where('UPPER(TRIM(last_name))',  strtoupper(trim($target['last_name']  ?? '')))
                ->get()->getRowArray();

            $dob = !empty($memberRow['date_of_birth'])
                ? $memberRow['date_of_birth']
                : ($db->table('households')->where('household_no', $target['household_no'])->get()->getRowArray()['date_of_birth'] ?? null);

            if (! empty($dob)) {
                $age = (int) date_diff(date_create($dob), date_create('today'))->y;
                if ($age < 18) {
                    return redirect()->back()->with('error', 'The selected resident must be at least 18 years old.')->withInput();
                }
            }
        }

        // Barangay Council allows up to seven active members.
        if ($newRole === 'council') {
            $councilCount = $this->userModel
                ->where('role', 'council')
                ->where('status', 'active')
                ->countAllResults();

            if ($councilCount >= 7) {
                return redirect()->back()->with('error', 'All 7 Barangay Council slots are already filled. Revoke a member before assigning another.')->withInput();
            }
        }

        // Single-instance check for Captain and Secretary.
        if (in_array($newRole, ['captain', 'secretary'], true)) {
            if ($newRole === 'secretary') {
                // Only allow one non-default (non-admin) secretary at a time.
                // The seeded default account username is `secretary_admin` and must not be revoked.
                // Also require that only the default admin may assign a resident as secretary.
                $existingNonAdmin = $this->userModel
                    ->where('role', 'secretary')
                    ->where('status', 'active')
                    ->where('username !=', 'secretary_admin')
                    ->first();

                if ($existingNonAdmin) {
                    $existingName = trim($existingNonAdmin['first_name'] . ' ' . $existingNonAdmin['last_name']);
                    return redirect()->back()->with(
                        'error',
                        'An active Secretary already exists (' . esc($existingName) . '). Demote them first before promoting someone else.'
                    )->withInput();
                }

                // Block self-promotion to secretary (secretary can't replace themselves this way)
                if ((int) session()->get('user_id') === $targetId) {
                    return redirect()->back()->with('error', 'You cannot promote your own account.')->withInput();
                }
            } else {
                $existing = $this->userModel->getActiveByRole($newRole);
                if ($existing) {
                    $existingName = trim($existing['first_name'] . ' ' . $existing['last_name']);
                    return redirect()->back()->with(
                        'error',
                        'An active ' . ucfirst($newRole) . ' already exists (' . esc($existingName) . '). Demote them first before promoting someone else.'
                    )->withInput();
                }
            }
        }

        // Promote
        $this->userModel->update($targetId, [
            'role'        => $newRole,
            'council_zone' => $newRole === 'council' ? $councilZone : null,
        ]);

        // Log the appointment in officials_history
        $this->logOfficialEvent($targetId, trim($target['first_name'] . ' ' . $target['last_name']), $newRole, 'appointed');

        $targetName = trim($target['first_name'] . ' ' . $target['last_name']);
        $back = $callerRole === 'captain' ? '/captain/create-account' : '/admin/create-account';
        return redirect()->to($back)
            ->with('success', esc($targetName) . ' has been promoted to ' . ucfirst($newRole) . ' and can now access the ' . ucfirst($newRole) . ' dashboard.');
    }

    // ── Demote official → resident (Secretary only) ───────────────────────────

    public function demoteOfficial(int $targetId)
    {
        $callerRole = session()->get('role');
        if (! in_array($callerRole, ['admin', 'captain'], true)) {
            return redirect()->back()->with('error', 'Only the admin can revoke official appointments.');
        }

        // Block self-demotion
        if ((int) session()->get('user_id') === $targetId) {
            return redirect()->back()->with('error', 'You cannot demote your own account.');
        }

        $target = $this->userModel->find($targetId);
        if (! $target) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Prevent demotion/deletion of the seeded default secretary admin
        if (! empty($target['username']) && in_array($target['username'], ['secretary_admin', 'admin'], true)) {
            return redirect()->back()->with('error', 'The default account cannot be demoted or revoked.');
        }

        $officialRoles = ['admin', 'captain', 'secretary', 'sk', 'council'];
        if (! in_array($target['role'], $officialRoles, true)) {
            return redirect()->back()->with('error', 'This user does not hold an official role.');
        }
        if ($callerRole === 'captain' && $target['role'] !== 'secretary') {
            return redirect()->back()->with('error', 'The captain can only revoke a Secretary appointment.');
        }

        $oldRole    = $target['role'];
        $targetName = trim($target['first_name'] . ' ' . $target['last_name']);

        // Demote back to resident
        $this->userModel->update($targetId, ['role' => 'resident', 'council_zone' => null]);

        // Log the revocation in officials_history
        $this->logOfficialEvent($targetId, $targetName, $oldRole, 'revoked');

        $back = $callerRole === 'captain' ? '/captain/create-account' : '/admin/create-account';
        return redirect()->to($back)
            ->with('success', esc($targetName) . ' has been demoted from ' . ucfirst($oldRole) . ' back to Resident.');
    }

    public function createOfficialAccount()
    {
        $callerRole = session()->get('role');
        if (! in_array($callerRole, ['admin', 'secretary'], true)) {
            return redirect()->back()
                ->with('error', 'You cannot create accounts from this page.')
                ->withInput();
        }

        $role = strtolower((string) $this->request->getPost('role'));
        if ($callerRole === 'secretary') {
            $role = 'resident';
        }
        $allowed = ['admin', 'captain', 'secretary', 'resident', 'sk', 'council'];

        if (! in_array($role, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid role selected.')->withInput();
        }

        // ── Single-instance enforcement for captain and secretary ─────────────
        if (in_array($role, ['captain', 'secretary'], true)) {
            if ($role === 'secretary') {
                // Allow the seeded default `secretary_admin` plus at most one additional resident secretary.
                $existingNonAdmin = $this->userModel
                    ->where('role', 'secretary')
                    ->where('status', 'active')
                    ->where('username !=', 'secretary_admin')
                    ->first();

                if ($existingNonAdmin) {
                    $existingName = trim($existingNonAdmin['first_name'] . ' ' . $existingNonAdmin['last_name']);
                    return redirect()->back()->with(
                        'error',
                        'An active Secretary account already exists (' . esc($existingName) . '). You must deactivate that account before creating a new one.'
                    )->withInput();
                }
            } else {
                $existing = $this->userModel->getActiveByRole($role);
                if ($existing) {
                    $existingName = trim($existing['first_name'] . ' ' . $existing['last_name']);
                    return redirect()->back()->with(
                        'error',
                        'An active ' . ucfirst($role) . ' account already exists (' . esc($existingName) . '). ' .
                            'You must deactivate that account before creating a new one.'
                    )->withInput();
                }
            }
        } elseif ($role === 'council') {
            $councilCount = $this->userModel
                ->where('role', 'council')
                ->where('status', 'active')
                ->countAllResults();

            if ($councilCount >= 7) {
                return redirect()->back()->with('error', 'All 7 Barangay Council slots are already filled.')->withInput();
            }
        }

        $password        = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match.')->withInput();
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters.')->withInput();
        }

        $householdNo = null;
        $councilZone = null;
        if ($role === 'council') {
            $councilZone = trim((string) $this->request->getPost('council_zone'));
            if (! in_array($councilZone, ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'], true)) {
                return redirect()->back()->with('error', 'Select a zone for the Barangay Council account.')->withInput();
            }
        }
        if ($role === 'resident') {
            $householdNo = trim($this->request->getPost('household_no') ?? '');
            if (! empty($householdNo)) {
                $householdModel = new \App\Models\HouseholdModel();
                if (! $householdModel->find($householdNo)) {
                    return redirect()->back()->with('error', 'Household number ' . esc($householdNo) . ' was not found in the census.')->withInput();
                }
            }
        }

        $saved = $this->userModel->save([
            'last_name'      => trim($this->request->getPost('last_name') ?? ''),
            'first_name'     => trim($this->request->getPost('first_name') ?? ''),
            'middle_name'    => trim($this->request->getPost('middle_name') ?? '') ?: null,
            'email'          => $this->request->getPost('email'),
            'username'       => $this->request->getPost('username'),
            'password'       => password_hash($password, PASSWORD_BCRYPT),
            'role'           => $role,
            'council_zone'   => $councilZone,
            'status'         => 'active',
            'email_verified' => 1,
            'household_no'   => $householdNo,
        ]);

        if (! $saved) {
            $errors = implode(' ', $this->userModel->errors());
            return redirect()->back()->with('error', $errors)->withInput();
        }

        // Log appointment for official roles
        if (in_array($role, ['admin', 'captain', 'secretary', 'sk', 'council'], true)) {
            $newUserId  = $this->userModel->getInsertID();
            $fullName   = trim(
                ($this->request->getPost('first_name') ?? '') . ' ' .
                    ($this->request->getPost('last_name')  ?? '')
            );
            $this->logOfficialEvent($newUserId, $fullName, $role, 'appointed', 'Account created from the official account page.');
        }

        return redirect()->to('/' . $callerRole . '/create-account')->with('success', ucfirst($role) . ' account created successfully.');
    }

    // ── Officials history page (secretary only) ───────────────────────────────

    public function officialsHistory()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $db = \Config\Database::connect();

        // If table doesn't exist yet (migration not run), show empty
        if (! $db->tableExists('officials_history')) {
            return view('dashboard/secretary/officials_history', [
                'history'  => [],
                'grouped'  => [],
                'role'     => $role,
                'pageTitle' => 'Officials History',
                'search'   => \App\Libraries\RecordSearch::term(),
            ]);
        }

        // Fetch all history ordered newest-first
        $raw = $db->table('officials_history')
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // Group by person (user_id) so we can compute date ranges
        // Each person gets an array of events. Pair appointed→revoked = one tenure.
        $byUser = [];
        foreach ($raw as $row) {
            $byUser[$row['user_id']][] = $row;
        }

        // Build tenure records: [ user_id, full_name, role, appointed_at, revoked_at, still_active ]
        $grouped = [];
        foreach ($byUser as $userId => $events) {
            // Sort events oldest-first to pair them correctly
            usort($events, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));

            $pending = []; // role => appointed_at event awaiting a revoke
            foreach ($events as $ev) {
                $r = $ev['role'];
                if ($ev['event'] === 'appointed') {
                    $pending[$r] = $ev;
                } elseif ($ev['event'] === 'revoked' && isset($pending[$r])) {
                    $grouped[] = [
                        'user_id'       => $userId,
                        'full_name'     => $ev['full_name'],
                        'role'          => $r,
                        'appointed_at'  => $pending[$r]['created_at'],
                        'revoked_at'    => $ev['created_at'],
                        'still_active'  => false,
                        'appointed_by'  => $pending[$r]['changed_by_name'] ?? '',
                        'revoked_by'    => $ev['changed_by_name'] ?? '',
                    ];
                    unset($pending[$r]);
                }
            }

            // Any remaining pending (no revoke yet) = currently active
            foreach ($pending as $r => $apEv) {
                $grouped[] = [
                    'user_id'       => $userId,
                    'full_name'     => $apEv['full_name'],
                    'role'          => $r,
                    'appointed_at'  => $apEv['created_at'],
                    'revoked_at'    => null,
                    'still_active'  => true,
                    'appointed_by'  => $apEv['changed_by_name'] ?? '',
                    'revoked_by'    => '',
                ];
            }
        }

        // Sort: active first, then by appointed_at newest-first
        usort($grouped, function ($a, $b) {
            if ($a['still_active'] !== $b['still_active']) {
                return $a['still_active'] ? -1 : 1;
            }
            return strcmp($b['appointed_at'], $a['appointed_at']);
        });

        $search = \App\Libraries\RecordSearch::term();
        $grouped = \App\Libraries\RecordSearch::filter(
            $grouped,
            $search,
            static fn(array $rec): string => implode(' ', [
                (string) ($rec['full_name'] ?? ''),
                (string) ($rec['role'] ?? ''),
                (string) ($rec['appointed_by'] ?? ''),
                (string) ($rec['revoked_by'] ?? ''),
            ]),
            static fn(array $rec): array => [
                $rec['appointed_at'] ?? null,
                $rec['revoked_at'] ?? null,
            ]
        );

        return view('dashboard/secretary/officials_history', [
            'history'   => $raw,
            'grouped'   => $grouped,
            'role'      => $role,
            'pageTitle' => 'Officials History',
            'search'    => $search,
        ]);
    }

    // ── Private: log an official appointment or revocation ───────────────────

    private function logOfficialEvent(
        int    $userId,
        string $fullName,
        string $role,
        string $event,
        string $notes = ''
    ): void {
        $db = \Config\Database::connect();
        if (! $db->tableExists('officials_history')) {
            return; // migration hasn't run yet — fail silently
        }

        $callerId   = (int) session()->get('user_id');
        $callerFn   = session()->get('first_name') ?? '';
        $callerLn   = session()->get('last_name')  ?? '';
        $callerName = trim($callerFn . ' ' . $callerLn) ?: 'System';

        $db->table('officials_history')->insert([
            'user_id'         => $userId,
            'full_name'       => $fullName,
            'role'            => $role,
            'event'           => $event,
            'changed_by_id'   => $callerId ?: null,
            'changed_by_name' => $callerName,
            'notes'           => $notes ?: null,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
