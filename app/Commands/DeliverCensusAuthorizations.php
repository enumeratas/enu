<?php

namespace App\Commands;

use App\Libraries\EmailService;
use App\Models\CensusUpdateAuthorizationModel;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;

/**
 * Sends census update authorization emails that the secretary already recorded.
 * The web request returns immediately so the Residents page does not wait on SMTP.
 */
class DeliverCensusAuthorizations extends BaseCommand
{
    protected $group = 'Census';

    protected $name = 'census:deliver-auth';

    protected $description = 'Email census update authorizations prepared from the Residents page.';

    protected $usage = 'census:deliver-auth <queue-file>';

    public function run(array $params)
    {
        $file = (string) ($params[0] ?? '');
        $queueDir = realpath(WRITEPATH . 'census-auth-queue');
        $realFile = $file !== '' ? realpath($file) : false;
        if ($queueDir === false || $realFile === false || ! str_starts_with($realFile, $queueDir . DIRECTORY_SEPARATOR)) {
            return;
        }

        $ids = json_decode((string) file_get_contents($realFile), true);
        @unlink($realFile);
        if (! is_array($ids) || $ids === []) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if ($ids === []) {
            return;
        }

        @set_time_limit(0);
        $authModel = new CensusUpdateAuthorizationModel();
        $userModel = new UserModel();
        $rows = $authModel->whereIn('id', $ids)->where('status', 'pending')->findAll();

        foreach ($rows as $auth) {
            $user = $userModel->find((int) ($auth['user_id'] ?? 0));
            $email = trim((string) ($user['email'] ?? ''));
            if (! is_array($user) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $deadline = ! empty($auth['expires_at'])
                ? date('F j, Y', strtotime((string) $auth['expires_at']))
                : date('F j, Y', strtotime('+7 days'));
            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Resident';
            $link = site_url('census/update/' . $auth['token']);

            try {
                $sent = (new EmailService())->sendCensusUpdateAuthorization($email, $name, $link, $deadline);
            } catch (\Throwable $e) {
                log_message('error', 'Census authorization email failed: ' . $e->getMessage());
                $sent = false;
            }

            if ($sent) {
                $authModel->update((int) $auth['id'], [
                    'status'  => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
