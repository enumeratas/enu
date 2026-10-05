<?php

namespace App\Models;

use CodeIgniter\Model;

class HouseholdMemberModel extends Model
{
    protected $table         = 'household_members';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'household_no',
        'family_group',
        'relationship',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'date_of_birth',
        'gender',
        'marital_status',
        'occupation',
        'work_detail',
        'grade_level',
        'supporting_doc_path',
        'monthly_income',
        'philhealth_no',
        'educational_attainment',
        'is_pwd',
        'pwd_type',
        'id_pwd_path',
        'id_senior_path',
        'is_deceased',
        'year_of_death',
    ];

    protected $validationRules = [
        'household_no' => 'required|max_length[5]',
        'relationship' => 'required|max_length[50]',
        'last_name'    => 'required|max_length[80]',
        'first_name'   => 'required|max_length[80]',
    ];

    public function getByHousehold(string $householdNo): array
    {
        return $this->where('household_no', $householdNo)
            ->orderBy('relationship', 'ASC')
            ->findAll();
    }

    public function replaceMembers(string $householdNo, array $members): bool
    {
        $this->where('household_no', $householdNo)->delete();

        if (empty($members)) return true;

        // Collect all keys across every row so insertBatch gets uniform arrays
        $allKeys = [];
        foreach ($members as $m) {
            foreach (array_keys($m) as $k) {
                $allKeys[$k] = null;
            }
        }

        $normalised = [];
        foreach ($members as $m) {
            $m['household_no'] = $householdNo;
            // Fill missing keys with null so every row has identical structure
            $normalised[] = array_merge($allKeys, ['household_no' => $householdNo], $m);
        }

        return $this->insertBatch($normalised) !== false;
    }
}
