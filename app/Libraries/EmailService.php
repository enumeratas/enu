<?php

namespace App\Libraries;

/**
 * EmailService — Barangay Bacolod BIS mailer.
 *
 * Uses CodeIgniter's built-in Email service configured via
 * app/Config/Email.php (SMTP credentials, fromEmail, fromName).
 */
class EmailService
{
  protected \CodeIgniter\Email\Email $email;

  public function __construct()
  {
    $this->email = \Config\Services::email();
  }

  // =========================================================================
  // Public API
  // =========================================================================

  public function sendVerificationEmail(string $toEmail, string $toName, string $otp): bool
  {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Information System - Email Verification',
      $this->verificationTemplate($toName, $otp)
    );
  }

  public function sendAccountApprovalEmail(string $toEmail, string $toName): bool
  {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Information System - Account Approved',
      $this->accountApprovalTemplate($toName)
    );
  }

  public function sendPasswordChangeOtp(string $toEmail, string $toName, string $otp): bool
  {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Information System - Password Change Verification',
      $this->passwordChangeTemplate($toName, $otp)
    );
  }

  public function sendPasswordResetOtp(string $toEmail, string $toName, string $otp): bool
  {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Information System - Password Reset Code',
      $this->passwordResetTemplate($toName, $otp)
    );
  }

  public function sendEmailChangeOtp(string $toEmail, string $toName, string $otp): bool
  {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Information System - Email Change Verification',
      $this->emailChangeTemplate($toName, $otp)
    );
  }

  public function sendCensusUpdateAuthorization(string $toEmail, string $toName, string $link, string $deadline): bool
  {
    $name = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $deadlineText = htmlspecialchars($deadline, ENT_QUOTES, 'UTF-8');

    $body = '
<div class="body-h2">Barangay Census Update Authorization</div>
<p class="body-p">Dear <strong>' . $name . '</strong>,</p>
<p class="body-p">The Barangay Secretary has requested that you review and update your census information for Barangay Bacolod.</p>
<p class="body-p">Please click the secure link below to continue:</p>
<p class="body-p"><a href="' . $url . '" style="color:#1d2448;font-weight:700;">Click here to update your information</a></p>
<div class="otp-box" style="text-align:left;padding:20px 22px;">
  <div class="otp-lbl" style="margin-bottom:10px;">Deadline</div>
  <div style="font-size:18px;font-weight:700;color:#1f2937;">' . $deadlineText . '</div>
</div>
<p class="tip">Please complete the update before the deadline to avoid delays in your barangay records.</p>';

    return $this->send(
      $toEmail,
      $toName,
      'Barangay Census Update Authorization',
      $this->wrap($body, '#1d2448')
    );
  }

  public function sendClearanceUpdate(
    string $toEmail,
    string $toName,
    string $documentType,
    string $status,
    string $remarks = '',
    string $estimatedRelease = ''
  ): bool {
    $name    = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $document = htmlspecialchars($documentType, ENT_QUOTES, 'UTF-8');
    $reason  = htmlspecialchars($remarks, ENT_QUOTES, 'UTF-8');
    $status  = strtolower($status);

    $details = match ($status) {
      'approved' => 'Your request has been approved.' . ($estimatedRelease
        ? ' Estimated release date: <strong>' . htmlspecialchars($estimatedRelease, ENT_QUOTES, 'UTF-8') . '</strong>.'
        : ''),
      'rejected' => 'Your request could not be approved.' . ($reason
        ? ' Reason: <strong>' . $reason . '</strong>'
        : ''),
      'released' => 'Your document is ready and has been released. Please visit the barangay hall if you have not yet received it.',
      default => 'Your document request status has been updated to <strong>' . htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') . '</strong>.',
    };

    $body = '
<div class="body-h2">Clearance Request Update</div>
<p class="body-p">Dear <strong>' . $name . '</strong>,</p>
<p class="body-p">There is an update to your <strong>' . $document . '</strong> request.</p>
<div class="otp-box" style="text-align:left;padding:20px 22px;">
  <div class="otp-lbl" style="margin-bottom:10px;">Status</div>
  <div style="font-size:18px;font-weight:700;color:#1f2937;">' . htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') . '</div>
</div>
<p class="body-p">' . $details . '</p>
<p class="tip">You can view your clearance requests in the Barangay Information System.</p>';

    return $this->send(
      $toEmail,
      $toName,
      'Clearance Request ' . ucfirst($status) . ' — Barangay Bacolod',
      $this->wrap($body, $status === 'rejected' ? '#dc3545' : '#16a085')
    );
  }

  public function sendConcernReschedule(
    string $toEmail,
    string $toName,
    string $subject,
    string $newDate,
    string $newTime,
    string $notes = ''
  ): bool {
    $n    = htmlspecialchars($toName,  ENT_QUOTES);
    $subj = htmlspecialchars($subject, ENT_QUOTES);
    $dt   = htmlspecialchars($newDate, ENT_QUOTES);
    $tm   = htmlspecialchars($newTime, ENT_QUOTES);
    $note = $notes
      ? '<div style="margin-top:12px;font-size:12.5px;color:#9aa0b4;"><strong style="color:#e2e5ef;">Additional Note:</strong> ' . htmlspecialchars($notes, ENT_QUOTES) . '</div>'
      : '';

    $content = '
<div class="body-h2">Appointment Rescheduled</div>
<p class="body-p">
  Dear <strong>' . $n . '</strong>,<br><br>
  Your concern regarding <strong>' . $subj . '</strong> has been noted and your appointment
  has been rescheduled. Please take note of the updated schedule below.
</p>
<div class="otp-box" style="text-align:left;padding:20px 22px;">
  <div class="otp-lbl" style="margin-bottom:10px;">New Appointment Schedule</div>
  <div style="font-size:16px;font-weight:700;color:#e2e5ef;margin-bottom:4px;">' . $dt . '</div>
  <div style="font-size:13px;color:#9aa0b4;">' . $tm . ' &nbsp;&middot;&nbsp; Barangay Hall, Bacolod, Bato, Camarines Sur</div>' . $note . '
</div>
<div class="tip" style="color:#e67e22;">
  <b>Reminder:</b> Please be at the Barangay Hall on the scheduled date.
  Office hours are Monday to Friday, 8:00 AM – 5:00 PM.
</div>';

    return $this->send(
      $toEmail,
      $toName,
      'Appointment Rescheduled — Barangay Bacolod Concern',
      $this->html('linear-gradient(135deg,#e67e22,#ca6f1e)', $content)
    );
  }

  public function sendConcernOtp(string $toEmail, string $toName, string $otp): bool
  {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Information System - Email Verification',
      $this->concernOtpTemplate($toName, $otp)
    );
  }

  public function sendConcernResponse(
    string $toEmail,
    string $toName,
    string $subject,
    string $originalMessage,
    string $adminResponse,
    string $decision,
    ?string $appointmentDate = null,
    ?string $appointmentTime = null
  ): bool {
    return $this->send(
      $toEmail,
      $toName,
      'Re: ' . $subject . ' — Barangay Bacolod',
      $this->concernResponseTemplate(
        $toName,
        $subject,
        $originalMessage,
        $adminResponse,
        $decision,
        $appointmentDate,
        $appointmentTime
      )
    );
  }

  public function sendSummons(
    string $toEmail,
    string $toName,
    string $caseNo,
    string $incidentType,
    string $hearingDate,
    string $hearingTime,
    string $role = 'respondent'
  ): bool {
    return $this->send(
      $toEmail,
      $toName,
      'Barangay Bacolod - Official Summons (Case #' . $caseNo . ')',
      $this->summonsTemplate($toName, $caseNo, $incidentType, $hearingDate, $hearingTime, $role)
    );
  }

  /**
   * Send the full official summons letter embedded in the email body.
   * @param string $role  'complainant' | 'respondent'
   */
  public function sendSummonsWithLetter(
    string $toEmail,
    string $toName,
    string $caseNo,
    string $incidentType,
    string $hearingDate,
    string $hearingTime,
    string $complainantName,
    string $respondentName,
    string $respondentAddr,
    string $incidentDate,
    string $location,
    string $hearingNotes,
    string $captainName,
    string $secretaryName,
    string $role = 'respondent'
  ): bool {
    return $this->send(
      $toEmail,
      $toName,
      'Official Summons — Case #' . $caseNo . ' — Barangay Bacolod',
      $this->summonsLetterEmailTemplate(
        $toName,
        $caseNo,
        $incidentType,
        $hearingDate,
        $hearingTime,
        $complainantName,
        $respondentName,
        $respondentAddr,
        $incidentDate,
        $location,
        $hearingNotes,
        $captainName,
        $secretaryName,
        $role
      )
    );
  }

  public function sendStaffNotice(string $toEmail, string $toName, string $subject, string $message): bool
  {
    $name = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $text = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $heading = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

    $body = '
<div class="body-h2">' . $heading . '</div>
<p class="body-p">Dear <strong>' . $name . '</strong>,</p>
<p class="body-p">' . $text . '</p>
<p class="tip">Open the Barangay Information System to review this item.</p>';

    return $this->send(
      $toEmail,
      $toName,
      $subject . ' — Barangay Bacolod',
      $this->wrap($body, '#16325c')
    );
  }

    // =========================================================================
    // Core sender
    // =========================================================================

  protected function send(
    string $recipient,
    string $name,
    string $subject,
    string $message
  ): bool {
    $this->email->clear(true);
    $this->email->setTo($recipient, $name);
    $this->email->setFrom(
      config('Email')->fromEmail,
      config('Email')->fromName
    );
    $this->email->setSubject($subject);
    $this->email->setMessage($message);

    if (! $this->email->send()) {
      log_message(
        'error',
        '[EmailService] Failed sending to ' . $recipient . ' — ' .
          $this->email->printDebugger(['headers', 'subject', 'body'])
      );
      return false;
    }

    log_message('info', '[EmailService] Email sent to: ' . $recipient);
    return true;
  }

    // =========================================================================
    // Shared HTML shell  —  clean, light, minimal
    // =========================================================================

  /**
   * Wraps $content in a plain white email layout.
   * $accentColor is used only for the thin top border stripe.
   */
  private function wrap(string $content, string $accentColor = '#1f2937'): string
  {
    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
    color: #1f2937;
    -webkit-font-smoothing: antialiased;
  }
  a { color: #1f2937; }
</style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="100%" cellpadding="0" cellspacing="0"
        style="max-width:580px;background:#ffffff;border-radius:10px;
               overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.08);">

        <!-- Top accent stripe -->
        <tr>
          <td style="height:4px;background:' . $accentColor . ';font-size:0;line-height:0;">&nbsp;</td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:36px 40px 32px;">
            ' . $content . '
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:0 40px 32px;">
            <hr style="border:none;border-top:1px solid #e5e7eb;margin-bottom:20px;">
            <p style="font-size:12px;color:#6b7280;line-height:1.6;">
              This is an automated message from the Barangay Information System.
              Please do not reply to this email.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>';
  }

  /**
   * Renders the large OTP code box.
   */
  private function otpBox(string $otp): string
  {
    return '
<div style="text-align:center;margin:28px 0;">
  <span style="
    display:inline-block;
    background:#f3f4f6;
    border:1px solid #d1d5db;
    border-radius:8px;
    padding:18px 32px;
    font-size:34px;
    font-weight:bold;
    letter-spacing:8px;
    color:#111827;
    font-family:\'Courier New\',Courier,monospace;
  ">' . esc($otp) . '</span>
</div>';
  }

  /**
   * Standard paragraph style.
   */
  private function p(string $text, string $extraStyle = ''): string
  {
    return '<p style="font-size:14px;line-height:1.75;color:#374151;margin-bottom:14px;' . $extraStyle . '">' . $text . '</p>';
  }

  // =========================================================================
  // Templates
  // =========================================================================

  protected function verificationTemplate(string $name, string $otp): string
  {
    $n = htmlspecialchars($name, ENT_QUOTES);
    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Barangay Information System
</h2>'
      . $this->p('Hello <strong>' . $n . '</strong>,')
      . $this->p('Thank you for registering with the Barangay Information System.')
      . $this->p('Use the verification code below to verify your email address:')
      . $this->otpBox($otp)
      . $this->p('This verification code will expire in <strong>15 minutes</strong>.')
      . $this->p('If you did not create this account, you may safely ignore this email.');

    return $this->wrap($body, '#1f2937');
  }

  protected function accountApprovalTemplate(string $name): string
  {
    $n = htmlspecialchars($name, ENT_QUOTES);
    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Account Approved
</h2>'
      . $this->p('Hello <strong>' . $n . '</strong>,')
      . $this->p('Your Barangay Information System account has been approved and is now active.')
      . $this->p('You may now log in using your username and password to access your barangay services.')
      . $this->p('If you need assistance, please contact the barangay office during office hours.');

    return $this->wrap($body, '#16a085');
  }

  protected function passwordChangeTemplate(string $name, string $otp): string
  {
    $n = htmlspecialchars($name, ENT_QUOTES);
    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Password Change Request
</h2>'
      . $this->p('Hello <strong>' . $n . '</strong>,')
      . $this->p('We received a request to change your Barangay Information System account password.')
      . $this->p('Enter the verification code below to confirm this change:')
      . $this->otpBox($otp)
      . $this->p('This code will expire in <strong>15 minutes</strong>.')
      . $this->p('If you did not request a password change, please ignore this email — your password will remain unchanged.');

    return $this->wrap($body, '#1f2937');
  }

  protected function passwordResetTemplate(string $name, string $otp): string
  {
    $n = htmlspecialchars($name, ENT_QUOTES);
    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Password Reset
</h2>'
      . $this->p('Hello <strong>' . $n . '</strong>,')
      . $this->p('We received a request to reset your Barangay Information System password.')
      . $this->p('Your password reset code is:')
      . $this->otpBox($otp)
      . $this->p('This code will expire in <strong>15 minutes</strong>.')
      . $this->p('If you did not request a password reset, please ignore this email.');

    return $this->wrap($body, '#1f2937');
  }

  protected function emailChangeTemplate(string $name, string $otp): string
  {
    $n = htmlspecialchars($name, ENT_QUOTES);
    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Email Address Change
</h2>'
      . $this->p('Hello <strong>' . $n . '</strong>,')
      . $this->p('A barangay administrator has requested to update your account\'s email address to this address.')
      . $this->p('Enter the verification code below to confirm the change:')
      . $this->otpBox($otp)
      . $this->p('This code will expire in <strong>15 minutes</strong>.')
      . $this->p('Once verified, this will become your new login email. If you did not expect this change, please contact the barangay office.');

    return $this->wrap($body, '#1f2937');
  }

  protected function concernOtpTemplate(string $name, string $otp): string
  {
    $n = htmlspecialchars($name, ENT_QUOTES);
    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Barangay Information System
</h2>'
      . $this->p('Hello <strong>' . $n . '</strong>,')
      . $this->p('You are submitting a concern or inquiry to Barangay Bacolod.')
      . $this->p('Use the verification code below to confirm your email address and complete your submission:')
      . $this->otpBox($otp)
      . $this->p('This verification code will expire in <strong>15 minutes</strong>.')
      . $this->p('If you did not submit a concern on the Barangay Bacolod portal, you may safely ignore this email.');

    return $this->wrap($body, '#1f2937');
  }

  protected function concernResponseTemplate(
    string $name,
    string $subject,
    string $originalMessage,
    string $adminResponse,
    string $decision,
    ?string $appointmentDate = null,
    ?string $appointmentTime = null
  ): string {
    $n        = htmlspecialchars($name,            ENT_QUOTES);
    $subj     = htmlspecialchars($subject,         ENT_QUOTES);
    $origMsg  = htmlspecialchars($originalMessage, ENT_QUOTES);
    $respMsg  = htmlspecialchars($adminResponse,   ENT_QUOTES);
    $statusLabel = $decision === 'resolved'
      ? 'Resolved'
      : ($decision === 'approved' ? 'Approved' : 'Dismissed');
    $scheduleHtml = '';
    if (! empty($appointmentDate)) {
      $schedule = date('l, F d, Y', strtotime($appointmentDate));
      if (! empty($appointmentTime)) {
        $schedule .= ' at ' . date('g:i A', strtotime($appointmentTime));
      }
      $scheduleHtml = '
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #2563eb;border-radius:0 8px 8px 0;padding:18px 20px;margin-bottom:18px;">
  <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#2563eb;margin-bottom:8px;">Scheduled Date and Time</p>
  <p style="font-size:15px;font-weight:700;color:#1e3a8a;margin:0;">' . htmlspecialchars($schedule, ENT_QUOTES) . '</p>
</div>';
    }

    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Response to Your Concern
</h2>'
      . $this->p('Dear <strong>' . $n . '</strong>,')
      . $this->p('Thank you for reaching out to <strong>Barangay Bacolod</strong>. We have reviewed your concern and have provided a response below.')
      . '
<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px 20px;margin-bottom:18px;">
  <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af;margin-bottom:8px;">Your Original Concern</p>
  <p style="font-size:14px;font-weight:600;color:#1f2937;margin-bottom:6px;">' . $subj . '</p>
  <p style="font-size:13px;color:#4b5563;line-height:1.7;white-space:pre-wrap;">' . $origMsg . '</p>
</div>
<div style="background:#f9fafb;border:1px solid #e5e7eb;border-left:4px solid #1f2937;border-radius:0 8px 8px 0;padding:18px 20px;margin-bottom:18px;">
  <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af;margin-bottom:8px;">
    Official Response &nbsp;&middot;&nbsp;
    <span style="background:#1f2937;color:#fff;padding:2px 8px;border-radius:100px;font-size:10px;">' . $statusLabel . '</span>
  </p>
  <p style="font-size:14px;color:#1f2937;line-height:1.75;">' . nl2br($respMsg) . '</p>
</div>'
      . $scheduleHtml
      . $this->p('If you have further questions, please visit the barangay hall during office hours (Monday to Friday, 8:00 AM – 5:00 PM).');

    return $this->wrap($body, '#1f2937');
  }

  protected function summonsTemplate(
    string $name,
    string $caseNo,
    string $incidentType,
    string $hearingDate,
    string $hearingTime,
    string $role
  ): string {
    $n       = htmlspecialchars($name,         ENT_QUOTES);
    $caseEsc = htmlspecialchars($caseNo,       ENT_QUOTES);
    $typeEsc = htmlspecialchars($incidentType, ENT_QUOTES);
    $dateEsc = htmlspecialchars($hearingDate,  ENT_QUOTES);
    $timeEsc = htmlspecialchars($hearingTime,  ENT_QUOTES);
    $roleEsc = ucfirst(htmlspecialchars($role, ENT_QUOTES));

    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 20px;">
  Official Summons
</h2>
<p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#9ca3af;margin-bottom:16px;">
  Barangay Bacolod, Bato, Camarines Sur &mdash; Office of the Punong Barangay
</p>'
      . $this->p('Dear <strong>' . $n . '</strong>,')
      . $this->p('You are hereby summoned to appear before the <strong>Barangay Lupon ng Tagapamayapa</strong> in connection with the following blotter case:')
      . '
<table width="100%" cellpadding="0" cellspacing="0"
  style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;
         border-collapse:collapse;overflow:hidden;margin-bottom:20px;font-size:14px;">
  <tr style="border-bottom:1px solid #e5e7eb;">
    <td style="padding:10px 16px;font-weight:700;color:#1f2937;width:130px;">Case No.</td>
    <td style="padding:10px 16px;color:#374151;">BL-' . $caseEsc . '</td>
  </tr>
  <tr style="border-bottom:1px solid #e5e7eb;">
    <td style="padding:10px 16px;font-weight:700;color:#1f2937;">Incident Type</td>
    <td style="padding:10px 16px;color:#374151;">' . $typeEsc . '</td>
  </tr>
  <tr>
    <td style="padding:10px 16px;font-weight:700;color:#1f2937;">Your Role</td>
    <td style="padding:10px 16px;color:#374151;">' . $roleEsc . '</td>
  </tr>
</table>

<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;
            padding:20px;margin-bottom:20px;text-align:center;">
  <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
            color:#9ca3af;margin-bottom:10px;">Hearing Schedule</p>
  <p style="font-size:22px;font-weight:700;color:#1f2937;margin-bottom:4px;">' . $dateEsc . '</p>
  <p style="font-size:16px;color:#374151;margin-bottom:4px;">' . $timeEsc . '</p>
  <p style="font-size:13px;color:#9ca3af;">Barangay Hall, Bacolod, Bato, Camarines Sur</p>
</div>

<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;
            padding:14px 18px;margin-bottom:20px;font-size:13px;color:#991b1b;line-height:1.6;">
  <strong>Important:</strong> Failure to appear without valid reason may result in
  further legal action under RA 7160 (Katarungang Pambarangay Law).
</div>'
      . $this->p('Please bring a valid government-issued ID and any relevant documents or evidence.')
      . $this->p('For inquiries, contact the Barangay Hall during office hours (Monday to Friday, 8:00 AM – 5:00 PM).');

    return $this->wrap($body, '#1f2937');
  }

  /**
   * Full summons letter embedded as an email — includes hearing box, narrative,
   * and signature block, so the recipient has the official letter in their inbox.
   */
  protected function summonsLetterEmailTemplate(
    string $toName,
    string $caseNo,
    string $incidentType,
    string $hearingDate,
    string $hearingTime,
    string $complainantName,
    string $respondentName,
    string $respondentAddr,
    string $incidentDate,
    string $location,
    string $hearingNotes,
    string $captainName,
    string $secretaryName,
    string $role
  ): string {
    $n          = htmlspecialchars($toName,         ENT_QUOTES);
    $cName      = htmlspecialchars($complainantName, ENT_QUOTES);
    $rName      = htmlspecialchars($respondentName  ?: $toName, ENT_QUOTES);
    $rAddr      = htmlspecialchars($respondentAddr  ?: 'Barangay Bacolod, Bato, Camarines Sur', ENT_QUOTES);
    $iType      = htmlspecialchars($incidentType,   ENT_QUOTES);
    $iDate      = htmlspecialchars($incidentDate,   ENT_QUOTES);
    $loc        = htmlspecialchars($location,       ENT_QUOTES);
    $hNotes     = htmlspecialchars($hearingNotes,   ENT_QUOTES);
    $capName    = htmlspecialchars($captainName,    ENT_QUOTES);
    $secName    = htmlspecialchars($secretaryName,  ENT_QUOTES);
    $isComp     = ($role === 'complainant');
    $addressee  = $isComp ? $cName : $rName;
    $addrLine   = $isComp ? 'Barangay Bacolod, Bato, Camarines Sur' : $rAddr;

    $notesRow = $hNotes ? '
      <tr>
        <td style="padding:6px 12px;font-weight:700;color:#374151;width:100px;font-size:13px;">Notes:</td>
        <td style="padding:6px 12px;font-size:13px;color:#374151;">' . $hNotes . '</td>
      </tr>' : '';

    $body = '
<h2 style="font-size:18px;font-weight:700;color:#1f2937;margin:0 0 4px;">
  Barangay Bacolod, Bato, Camarines Sur
</h2>
<p style="font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:#6b7280;margin:0 0 20px;">
  Office of the Punong Barangay
</p>
<p style="font-size:13px;color:#6b7280;margin:0 0 20px;"><strong>Case No.:</strong> BL-' . $caseNo . '</p>

<h3 style="font-size:16px;font-weight:700;color:#1f2937;text-align:center;text-transform:uppercase;
           letter-spacing:2px;text-decoration:underline;margin:0 0 20px;">SUMMONS</h3>'

      . $this->p('To: <strong>' . $addressee . '</strong><br><span style="color:#6b7280;">' . $addrLine . '</span>')
      . $this->p('Greetings!')
      . $this->p(
        $isComp
          ? 'You are hereby summoned to appear before the <strong>Lupong Tagapamayapa</strong> of Barangay Bacolod, Bato, Camarines Sur, in connection with the blotter complaint you filed involving a case of <strong>' . $iType . '</strong>' . ($iDate ? ' that allegedly occurred on <strong>' . $iDate . '</strong>' : '') . ($loc ? ' at <strong>' . $loc . '</strong>' : '') . '.'
          : 'You are hereby summoned to appear before the <strong>Lupong Tagapamayapa</strong> of Barangay Bacolod, Bato, Camarines Sur, in connection with a complaint filed against you by <strong>' . $cName . '</strong> involving a case of <strong>' . $iType . '</strong>' . ($iDate ? ' that allegedly occurred on <strong>' . $iDate . '</strong>' : '') . ($loc ? ' at <strong>' . $loc . '</strong>' : '') . '.'
      )

      . '
<p style="font-size:14px;line-height:1.75;color:#374151;margin-bottom:14px;">The hearing has been scheduled as follows:</p>

<table width="100%" cellpadding="0" cellspacing="0"
  style="background:#f9fafb;border:1.5px solid #1f2937;border-radius:8px;
         border-collapse:collapse;overflow:hidden;margin-bottom:20px;">
  <tr style="background:#1f2937;">
    <td colspan="2" style="padding:10px 12px;font-size:11px;font-weight:700;
       letter-spacing:1px;text-transform:uppercase;color:#fff;text-align:center;">
      Hearing Schedule
    </td>
  </tr>
  <tr style="border-bottom:1px solid #e5e7eb;">
    <td style="padding:8px 12px;font-weight:700;color:#374151;width:100px;font-size:13px;">Date:</td>
    <td style="padding:8px 12px;font-size:14px;font-weight:700;color:#1f2937;">' . $hearingDate . '</td>
  </tr>
  <tr style="border-bottom:1px solid #e5e7eb;">
    <td style="padding:8px 12px;font-weight:700;color:#374151;font-size:13px;">Time:</td>
    <td style="padding:8px 12px;font-size:14px;font-weight:700;color:#1f2937;">' . $hearingTime . '</td>
  </tr>
  <tr style="border-bottom:1px solid #e5e7eb;">
    <td style="padding:8px 12px;font-weight:700;color:#374151;font-size:13px;">Venue:</td>
    <td style="padding:8px 12px;font-size:13px;color:#374151;">Barangay Hall, Bacolod, Bato, Camarines Sur</td>
  </tr>
  <tr' . ($hNotes ? ' style="border-bottom:1px solid #e5e7eb;"' : '') . '>
    <td style="padding:8px 12px;font-weight:700;color:#374151;font-size:13px;">Case No.:</td>
    <td style="padding:8px 12px;font-size:13px;color:#374151;">BL-' . $caseNo . '</td>
  </tr>' . $notesRow . '
</table>

<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;
            padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b;line-height:1.6;">
  <strong>Important:</strong> Failure to appear without valid reason may result in further legal
  action under RA 7160 (Katarungang Pambarangay Law).
</div>'

      . $this->p('Please bring a valid government-issued ID and any relevant documents or evidence.')
      . $this->p('For inquiries, contact the Barangay Hall during office hours (Monday to Friday, 8:00 AM – 5:00 PM).')
      . $this->p('Thank you for your cooperation.')

      . '
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;">
  <tr>
  <div style="font-size:13px;font-weight:700;color:#1f2937;text-transform:uppercase;">' . $capName . '</div>
  <td width="50%" style="text-align:center;padding:0 16px;">
    <div style="border-bottom:1px solid #1f2937;height:36px;margin-bottom:6px;"></div>
      <div style="font-size:11px;color:#6b7280;font-style:italic;">Punong Barangay<br>Barangay Bacolod, Bato, Camarines Sur</div>
    </td>
    <td width="50%" style="text-align:center;padding:0 16px;">
      <div style="border-bottom:1px solid #1f2937;height:36px;margin-bottom:6px;"></div>
      <div style="font-size:13px;font-weight:700;color:#1f2937;text-transform:uppercase;">' . $secName . '</div>
      <div style="font-size:11px;color:#6b7280;font-style:italic;">Barangay Secretary<br>Barangay Bacolod, Bato, Camarines Sur</div>
    </td>
  </tr>
</table>';

    return $this->wrap($body, '#1f2937');
  }
}
