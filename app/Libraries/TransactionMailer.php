<?php

namespace App\Libraries;

use App\Models\BarangaySettingsModel;

/**
 * Sends transaction and announcement email once per event.
 * A failed delivery is recorded and can be tried again. A successful
 * delivery for the same key is not sent a second time.
 */
class TransactionMailer
{
    public const LOCATION = 'Barangay Hall, Bacolod, Bato, Camarines Sur';

    private ?EmailService $mail = null;

    private bool $smtpDown = false;

    public static function isDeliverableAddress(string $email): bool
    {
        $email = trim($email);
        if ($email === '' || strlen($email) > 190 || preg_match('/\s/', $email) === 1) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function dateLabel(?string $date): string
    {
        $date = trim((string) $date);
        if ($date === '') {
            return 'Not specified';
        }
        $stamp = strtotime($date);

        return $stamp ? date('l, F j, Y', $stamp) : $date;
    }

    public static function timeRange(?string $start, ?string $end = null): string
    {
        $startLabel = self::clock($start);
        $endLabel   = self::clock($end);
        if ($startLabel === 'Not specified') {
            return 'Not specified';
        }
        if ($endLabel === 'Not specified' || strcasecmp($startLabel, $endLabel) === 0) {
            return $startLabel;
        }

        return $startLabel . ' – ' . $endLabel;
    }

    public static function clock(?string $time): string
    {
        $time = trim((string) $time);
        if ($time === '' || $time === '00:00:00') {
            return 'Not specified';
        }
        $stamp = strtotime($time);

        return $stamp ? date('g:i A', $stamp) : 'Not specified';
    }

    /**
     * @param array<string, scalar|null> $parts
     */
    public static function stamp(array $parts): string
    {
        $flat = [];
        foreach ($parts as $key => $value) {
            $flat[] = strtolower(trim((string) $key)) . '=' . strtolower(trim((string) $value));
        }

        return sha1(implode('|', $flat));
    }

    /**
     * @param array<string, scalar|null> $details
     */
    public function notice(
        string $dedupeKey,
        string $email,
        string $name,
        string $subject,
        string $heading,
        string $intro,
        array $details = [],
        string $instructions = ''
    ): string {
        return $this->deliver(
            $dedupeKey,
            $email,
            $name,
            function (EmailService $mail) use ($email, $name, $subject, $heading, $intro, $details, $instructions): bool {
                return $mail->sendTransactionNotice($email, $name, $subject, $heading, $intro, $details, $instructions);
            }
        );
    }

    /**
     * @param array<string, scalar|null> $details
     */
    public function noticeUser(
        int $userId,
        string $dedupeKey,
        string $subject,
        string $heading,
        string $intro,
        array $details = [],
        string $instructions = ''
    ): string {
        if ($userId <= 0) {
            return 'invalid';
        }

        $user = db_connect()->table('users')
            ->select('email, first_name, last_name, username, status')
            ->where('id', $userId)
            ->get()
            ->getRowArray();
        if (! is_array($user)) {
            return 'invalid';
        }

        $status = strtolower(trim((string) ($user['status'] ?? '')));
        if (in_array($status, ['rejected', 'inactive', 'disabled', 'blocked'], true)) {
            return 'skipped';
        }

        $name = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($user['username'] ?? '')) ?: 'Resident';
        }

        return $this->notice(
            $dedupeKey,
            (string) ($user['email'] ?? ''),
            $name,
            $subject,
            $heading,
            $intro,
            $details,
            $instructions
        );
    }

    /**
     * @param array<string, scalar|null> $details
     * @return array{sent:int,failed:int,invalid:int,duplicate:int,disabled:bool}
     */
    public function broadcastActiveResidents(
        string $eventKey,
        string $subject,
        string $heading,
        string $intro,
        array $details = [],
        string $instructions = ''
    ): array {
        $summary = ['sent' => 0, 'failed' => 0, 'invalid' => 0, 'duplicate' => 0, 'disabled' => false];
        if (! $this->enabled()) {
            $summary['disabled'] = true;

            return $summary;
        }

        @set_time_limit(180);
        $rows = db_connect()->table('users')
            ->select('email, first_name, last_name, username')
            ->where('role', 'resident')
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        $seen = [];
        foreach ($rows as $row) {
            if ($this->smtpDown) {
                break;
            }
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '' || isset($seen[$email])) {
                if ($email !== '' && isset($seen[$email])) {
                    $summary['duplicate']++;
                } elseif ($email === '') {
                    $summary['invalid']++;
                }
                continue;
            }
            $seen[$email] = true;
            $name = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
            if ($name === '') {
                $name = trim((string) ($row['username'] ?? '')) ?: 'Resident';
            }
            $result = $this->notice(
                $eventKey . ':' . $email,
                $email,
                $name,
                $subject,
                $heading,
                $intro,
                $details,
                $instructions
            );
            if ($result === 'sent') {
                $summary['sent']++;
            } elseif ($result === 'duplicate') {
                $summary['duplicate']++;
            } elseif ($result === 'invalid') {
                $summary['invalid']++;
            } elseif ($result === 'failed') {
                $summary['failed']++;
            } elseif ($result === 'disabled') {
                $summary['disabled'] = true;
                break;
            }
        }

        return $summary;
    }

    public function deliver(string $dedupeKey, string $email, string $name, callable $sender): string
    {
        $dedupeKey = $this->normalizeKey($dedupeKey);
        $email = trim($email);
        $name = trim($name) !== '' ? trim($name) : 'Resident';

        if (! $this->enabled()) {
            return 'disabled';
        }
        if ($this->smtpDown) {
            return 'failed';
        }
        if (! self::isDeliverableAddress($email)) {
            log_message('warning', '[TransactionMailer] Skipped invalid email for ' . $dedupeKey);

            return 'invalid';
        }

        $claim = $this->claim($dedupeKey, $email, '');
        if ($claim === 'duplicate') {
            return 'duplicate';
        }
        if ($claim !== 'claimed') {
            log_message('error', '[TransactionMailer] Could not record email attempt for ' . $dedupeKey);

            return 'failed';
        }

        try {
            $sent = (bool) $sender($this->mail());
        } catch (\Throwable $e) {
            $this->finish($dedupeKey, 'failed', 'The email could not be delivered.');
            log_message('error', '[TransactionMailer] Send failed for ' . $dedupeKey . ': ' . $e->getMessage());

            return 'failed';
        }

        if ($sent) {
            $this->finish($dedupeKey, 'sent', '');

            return 'sent';
        }

        $kind = $this->mail()->lastFailure();
        if ($kind === 'smtp') {
            $this->smtpDown = true;
        }
        $this->finish($dedupeKey, 'failed', $kind === 'smtp'
            ? 'The mail server rejected the login or could not be reached.'
            : 'The message was not accepted for this address.');
        log_message('error', '[TransactionMailer] Delivery failed for ' . $email . ' (' . $dedupeKey . ').');

        return 'failed';
    }

    public function enabled(): bool
    {
        try {
            return (new BarangaySettingsModel())->enabled('email_notifications');
        } catch (\Throwable $e) {
            log_message('error', '[TransactionMailer] Could not read email setting: ' . $e->getMessage());

            return false;
        }
    }

    private function mail(): EmailService
    {
        return $this->mail ??= new EmailService();
    }

    private function normalizeKey(string $key): string
    {
        $key = trim(preg_replace('/\s+/', ' ', $key) ?? '');
        if (strlen($key) <= 180) {
            return $key;
        }

        return substr($key, 0, 120) . ':' . sha1($key);
    }

    private function claim(string $key, string $email, string $subject): string
    {
        if (! $this->ensureTable()) {
            return 'failed';
        }

        $db = db_connect();
        $now = date('Y-m-d H:i:s');
        $existing = $db->table('email_delivery_log')->where('dedupe_key', $key)->get()->getRowArray();
        if (is_array($existing)) {
            if (($existing['status'] ?? '') === 'sent') {
                return 'duplicate';
            }
            if (($existing['status'] ?? '') === 'pending') {
                $updated = strtotime((string) ($existing['updated_at'] ?? $existing['created_at'] ?? ''));
                if ($updated !== false && (time() - $updated) < 120) {
                    return 'duplicate';
                }
            }
            $db->table('email_delivery_log')->where('dedupe_key', $key)->update([
                'recipient'     => $email,
                'subject'       => $subject !== '' ? mb_substr($subject, 0, 250) : ($existing['subject'] ?? null),
                'status'        => 'pending',
                'error_message' => null,
                'updated_at'    => $now,
            ]);

            return 'claimed';
        }

        try {
            $db->table('email_delivery_log')->insert([
                'dedupe_key'    => $key,
                'recipient'     => $email,
                'subject'       => $subject !== '' ? mb_substr($subject, 0, 250) : null,
                'status'        => 'pending',
                'error_message' => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        } catch (\Throwable $e) {
            $again = $db->table('email_delivery_log')->where('dedupe_key', $key)->get()->getRowArray();
            if (is_array($again) && ($again['status'] ?? '') === 'sent') {
                return 'duplicate';
            }

            return 'failed';
        }

        return 'claimed';
    }

    private function finish(string $key, string $status, string $error): void
    {
        try {
            db_connect()->table('email_delivery_log')->where('dedupe_key', $key)->update([
                'status'        => $status,
                'error_message' => $error !== '' ? mb_substr($error, 0, 500) : null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[TransactionMailer] Could not update delivery log: ' . $e->getMessage());
        }
    }

    private function ensureTable(): bool
    {
        static $ready = false;
        if ($ready) {
            return true;
        }

        try {
            $db = db_connect();
            if ($db->tableExists('email_delivery_log')) {
                $ready = true;

                return true;
            }

            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'dedupe_key' => ['type' => 'VARCHAR', 'constraint' => 191],
                'recipient' => ['type' => 'VARCHAR', 'constraint' => 190],
                'subject' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'error_message' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addPrimaryKey('id');
            $forge->addUniqueKey('dedupe_key');
            $forge->createTable('email_delivery_log', true);
            $ready = true;

            return true;
        } catch (\Throwable $e) {
            if (db_connect()->tableExists('email_delivery_log')) {
                $ready = true;

                return true;
            }
            log_message('error', '[TransactionMailer] Delivery log table is unavailable: ' . $e->getMessage());

            return false;
        }
    }
}
