<?php

namespace App\Libraries;

use App\Models\CensusUpdateAuthorizationModel;
use App\Models\UserModel;

/**
 * Sends census update authorization emails that were already saved.
 */
class CensusAuthorizationMailer
{
    /**
     * @param list<int> $authIds
     * @return array{sent: int, failed: int}
     */
    public function sendPending(array $authIds): array
    {
        $authIds = array_values(array_unique(array_filter(array_map('intval', $authIds), static fn (int $id): bool => $id > 0)));
        if ($authIds === []) {
            return ['sent' => 0, 'failed' => 0];
        }

        $authModel = new CensusUpdateAuthorizationModel();
        $userModel = new UserModel();
        $rows = $authModel->whereIn('id', $authIds)->where('status', 'pending')->findAll();
        $sent = 0;
        $failed = 0;

        foreach ($rows as $auth) {
            if (! is_array($auth)) {
                $failed++;
                continue;
            }

            try {
                $user = $userModel->find((int) ($auth['user_id'] ?? 0));
                $email = trim((string) (is_array($user) ? ($user['email'] ?? '') : ''));
                if (! is_array($user) || ! TransactionMailer::isDeliverableAddress($email)) {
                    $failed++;
                    continue;
                }

                $deadline = ! empty($auth['expires_at'])
                    ? date('F j, Y', strtotime((string) $auth['expires_at']))
                    : date('F j, Y', strtotime('+7 days'));
                $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Resident';
                $link = site_url('census/update/' . $auth['token']);
                $delivered = (new EmailService())->sendCensusUpdateAuthorization($email, $name, $link, $deadline);
                if ($delivered) {
                    $authModel->update((int) $auth['id'], [
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s'),
                    ]);
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                log_message('error', 'Census authorization email failed: ' . $e->getMessage());
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
