<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = 'smtp.gmail.com';

    /**
     * SMTP Username
     */
    public string $SMTPUser = '';

    /**
     * SMTP Password
     */
    public string $SMTPPass = '';

    /**
     * SMTP Port — 465 for SSL, 587 for TLS/STARTTLS
     */
    public int $SMTPPort = 587;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 10;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     * Use 'ssl' for port 465 (Gmail recommended).
     * Use 'tls' for port 587 with STARTTLS.
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail — html for styled emails
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use "\r\n" to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use "\r\n" to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    public function __construct()
    {
        parent::__construct();

        // Load values from .env file
        $this->protocol   = env('email.protocol', $this->protocol);
        $this->SMTPHost   = env('email.SMTPHost', $this->SMTPHost);
        $this->SMTPUser   = env('email.SMTPUser', $this->SMTPUser);
        $this->SMTPPass   = env('email.SMTPPass', $this->SMTPPass);
        $this->SMTPPort   = (int) env('email.SMTPPort', $this->SMTPPort);
        $this->SMTPCrypto = env('email.SMTPCrypto', $this->SMTPCrypto);
        $this->mailType   = env('email.mailType', $this->mailType);
        $this->charset    = env('email.charset', $this->charset);
        $this->wordWrap   = filter_var(env('email.wordWrap', $this->wordWrap), FILTER_VALIDATE_BOOLEAN);

        // For Gmail, fromEmail MUST match SMTPUser or Gmail will reject it
        $this->fromEmail = env('email.fromEmail', $this->SMTPUser);
        $this->fromName  = env('email.fromName', 'BIS');

        // Trim whitespace
        $this->SMTPHost  = trim($this->SMTPHost);
        $this->SMTPUser  = trim($this->SMTPUser);
        $this->fromEmail = trim($this->fromEmail);
        $this->fromName  = trim($this->fromName);

        // Gmail app passwords are 16 letters. Pasting them with spaces must still work.
        $this->SMTPPass = preg_replace('/\s+/', '', $this->SMTPPass) ?? '';

        // Gmail security: If using Gmail SMTP, force fromEmail to match SMTPUser
        if (stripos($this->SMTPHost, 'gmail') !== false && $this->fromEmail !== $this->SMTPUser) {
            log_message('warning', '[Email Config] Gmail requires fromEmail to match SMTPUser. Overriding fromEmail from "' . $this->fromEmail . '" to "' . $this->SMTPUser . '"');
            $this->fromEmail = $this->SMTPUser;
        }
    }
}
