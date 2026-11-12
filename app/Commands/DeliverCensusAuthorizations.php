<?php

namespace App\Commands;

use App\Libraries\CensusAuthorizationMailer;
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
        (new CensusAuthorizationMailer())->sendPending($ids);
    }
}
