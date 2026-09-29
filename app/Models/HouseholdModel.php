<?php

namespace App\Models;

use CodeIgniter\Model;

class HouseholdModel extends Model
{
    protected $table         = 'households';
    protected $primaryKey    = 'household_no';
    protected $useTimestamps = true;
    protected $useAutoIncrement = false;

    protected $allowedFields = [
        'household_no',
        'zone',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'civil_status',
        'nationality',
        'religion',
        'occupation',
        'monthly_income',
        'contact_number',
        'educational_attainment',
        'philhealth_no',
        'address',
        'years_of_residency',
        'residency_start_year',
        'house_ownership',
        'is_4ps',
        'id_4ps_path',
        'id_4ps_verified',
        'is_pwd',
        'pwd_type',
        'id_pwd_path',
        'id_pwd_verified',
        'is_senior_citizen',
        'id_senior_path',
        'id_senior_verified',
        'is_solo_parent',
        'id_solo_parent_path',
        'id_solo_parent_verified',
        'is_indigenous',
        'registered_voter',
        'num_families',
        'water_source_level',
        'water_safety_managed',
        'sanitation_basic',
        'sanitation_managed',
        'recorded_by',
        'recorded_date',
        'approval_status',
        'census_year',
        'ownership_document_path',
        'ownership_notes',
        'shared_address_group',
        'family_number',
        'family_type',
        'is_deceased',
        'year_of_death',
    ];

    protected $validationRules = [
        'household_no' => 'required|max_length[5]',
        'last_name'    => 'required|max_length[80]',
        'first_name'   => 'required|max_length[80]',
        'gender'       => 'required|in_list[Male,Female]',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Get all households with member count.
     */
    public function getWithMemberCount(): array
    {
        return $this->db->table('households h')
            ->select('h.*, COUNT(m.id) AS member_count')
            ->join('household_members m', 'm.household_id = h.id', 'left')
            ->groupBy('h.id')
            ->orderBy('h.household_no', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get a single household with all its members.
     */
    public function getWithMembers(string $householdNo): ?array
    {
        $household = $this->find($householdNo);
        if (! $household) return null;

        $household['members'] = (new \App\Models\HouseholdMemberModel())
            ->where('household_no', $householdNo)
            ->orderBy('relationship', 'ASC')
            ->findAll();

        return $household;
    }

    /**
     * Generate a unique 5-digit household number.
     * Keeps trying until a non-colliding number is found.
     */
    public function generateHouseholdNo(): string
    {
        do {
            $no = str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        } while ($this->where('household_no', $no)->countAllResults() > 0);

        return $no;
    }
}
