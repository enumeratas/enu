<?php

namespace App\Libraries;

use App\Models\BarangaySettingsModel;
use Config\Database;

/**
 * Decides what happens to a resident login when that person is marked deceased.
 *
 * Policy lives in barangay_settings.deceased_account_action:
 *   block — set users.status to deceased (cannot log in)
 *   keep  — leave the account as-is
 */
class DeceasedAccountService
{
    public const ACTION_BLOCK = 'block';
    public const ACTION_KEEP  = 'keep';

    public function currentAction(): string
    {
        $value = (new BarangaySettingsModel())->getValue('deceased_account_action', self::ACTION_BLOCK);

        return $value === self::ACTION_KEEP ? self::ACTION_KEEP : self::ACTION_BLOCK;
    }

    public function saveAction(string $action): void
    {
        $action = $action === self::ACTION_KEEP ? self::ACTION_KEEP : self::ACTION_BLOCK;
        $model  = new BarangaySettingsModel();
        $row    = $model->where('setting_key', 'deceased_account_action')->first();
        if ($row) {
            $model->update($row['id'], ['setting_value' => $action]);
            return;
        }

        $model->insert([
            'setting_key'   => 'deceased_account_action',
            'setting_value' => $action,
            'label'         => 'Deceased account action',
            'group'         => 'accounts',
            'sort_order'    => 90,
        ]);
    }

    /** Find a resident account that matches this census person. */
    public function findResidentAccount(string $firstName, string $lastName, ?string $middleName = null, ?string $householdNo = null): ?array
    {
        $wantedFirst  = $this->normalizeName($firstName);
        $wantedLast   = $this->normalizeName($lastName);
        $wantedMiddle = $this->normalizeName((string) $middleName);
        if ($wantedFirst === '' || $wantedLast === '') {
            return null;
        }

        $rows = Database::connect()->table('users')
            ->select('id, first_name, middle_name, last_name, username, email, status, household_no, role')
            ->where('role', 'resident')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            if ($this->normalizeName((string) $row['last_name']) !== $wantedLast) {
                continue;
            }
            if ($this->normalizeName((string) $row['first_name']) !== $wantedFirst) {
                continue;
            }
            $rowMiddle = $this->normalizeName((string) ($row['middle_name'] ?? ''));
            if ($wantedMiddle !== '' && $rowMiddle !== '' && $rowMiddle !== $wantedMiddle) {
                continue;
            }
            $rowHousehold = trim((string) ($row['household_no'] ?? ''));
            if ($householdNo !== null && $householdNo !== '' && $rowHousehold !== '' && $rowHousehold !== $householdNo) {
                continue;
            }

            return $row;
        }

        return null;
    }

    /**
     * Apply the barangay policy after a census person is marked deceased.
     *
     * @return string|null Short message about the account, or null if none.
     */
    public function applyOnMarkedDeceased(array $person, ?string $householdNo = null): ?string
    {
        $account = $this->findResidentAccount(
            (string) ($person['first_name'] ?? ''),
            (string) ($person['last_name'] ?? ''),
            $person['middle_name'] ?? null,
            $householdNo
        );
        if (! $account) {
            return null;
        }

        if ($this->currentAction() === self::ACTION_KEEP) {
            return 'A resident account (' . $account['username'] . ') is still active because the barangay policy is to keep logins.';
        }

        if ($account['status'] === 'deceased') {
            return 'The matching resident account (' . $account['username'] . ') is already blocked.';
        }

        $this->updateStatus((int) $account['id'], 'deceased');

        return 'The resident account (' . $account['username'] . ') can no longer log in.';
    }

    /**
     * Restore a blocked account when the deceased mark is cleared.
     *
     * @return string|null
     */
    public function applyOnClearedDeceased(array $person, ?string $householdNo = null): ?string
    {
        $account = $this->findResidentAccount(
            (string) ($person['first_name'] ?? ''),
            (string) ($person['last_name'] ?? ''),
            $person['middle_name'] ?? null,
            $householdNo
        );
        if (! $account || $account['status'] !== 'deceased') {
            return null;
        }

        $this->updateStatus((int) $account['id'], 'active');

        return 'The resident account (' . $account['username'] . ') can log in again.';
    }

    public function setAccountStatus(int $userId, string $status): bool
    {
        if (! in_array($status, ['active', 'deceased'], true)) {
            return false;
        }
        $user = Database::connect()->table('users')->where('id', $userId)->get()->getRowArray();
        if (! $user || ($user['role'] ?? '') !== 'resident') {
            return false;
        }

        return $this->updateStatus($userId, $status);
    }

    private function updateStatus(int $userId, string $status): bool
    {
        return Database::connect()->table('users')
            ->where('id', $userId)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /** Deceased census people who have a matching resident account. */
    public function listLinkedAccounts(): array
    {
        $db = Database::connect();
        $heads = $db->table('households h')
            ->select("h.household_no, h.first_name, h.middle_name, h.last_name, h.year_of_death, 'Household Head' AS relationship, u.id AS user_id, u.username, u.email, u.status AS account_status")
            ->join('users u', $this->joinOnName('u', 'h') . " AND u.role = 'resident'", 'inner')
            ->where('h.is_deceased', 1)
            ->get()
            ->getResultArray();

        $members = $db->table('household_members m')
            ->select("m.household_no, m.first_name, m.middle_name, m.last_name, m.year_of_death, m.relationship, u.id AS user_id, u.username, u.email, u.status AS account_status")
            ->join('users u', $this->joinOnName('u', 'm') . " AND u.role = 'resident'", 'inner')
            ->where('m.is_deceased', 1)
            ->get()
            ->getResultArray();

        return array_merge($heads, $members);
    }

    private function joinOnName(string $userAlias, string $personAlias): string
    {
        $norm = static function (string $alias, string $column): string {
            return "REPLACE(REPLACE(REPLACE(UPPER(TRIM({$alias}.{$column})), '.', ''), '-', ''), ' ', '')";
        };

        return $norm($userAlias, 'last_name') . ' = ' . $norm($personAlias, 'last_name')
            . ' AND ' . $norm($userAlias, 'first_name') . ' = ' . $norm($personAlias, 'first_name');
    }

    private function normalizeName(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = str_replace(['.', '-'], '', $value);

        return preg_replace('/\s+/', '', $value) ?? '';
    }
}
