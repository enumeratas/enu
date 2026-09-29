<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HouseholdModel;
use App\Models\HouseholdMemberModel;

class CensusExportController extends BaseController
{
    /**
     * Render a print-ready landscape HTML page that the browser can save as PDF.
     * Accepts the same GET filters as the census list page.
     */
    public function exportPdf()
    {
        $role = session()->get('role');
        $db   = \Config\Database::connect();

        // ── Read all filters (mirrors UIController::_censusView) ─────────────
        $zone       = trim($_GET['zone']        ?? '');
        $gender     = trim($_GET['gender']      ?? '');
        $search     = trim($_GET['search']      ?? '');
        $ageMin     = trim($_GET['age_min']     ?? '');
        $ageMax     = trim($_GET['age_max']     ?? '');
        $isPwd      = trim($_GET['is_pwd']      ?? '');
        $isSenior   = trim($_GET['is_senior']   ?? '');
        $isSolo     = trim($_GET['is_solo']     ?? '');
        $is4ps      = trim($_GET['is_4ps']      ?? '');
        $isStudent  = trim($_GET['is_student']  ?? '');
        $isOsy      = trim($_GET['is_osy']      ?? '');
        $isIndigent = trim($_GET['is_indigent'] ?? '');
        $censusYear = trim($_GET['census_year'] ?? '');

        // ── Build WHERE for households ────────────────────────────────────────
        $hw = [];

        if ($zone !== '') {
            $z    = $db->escapeString($zone);
            $hw[] = "h.zone = '{$z}'";
        }
        if ($search !== '') {
            $s    = $db->escapeLikeString($search);
            $hw[] = "(h.last_name LIKE '%{$s}%' OR h.first_name LIKE '%{$s}%' OR h.household_no LIKE '%{$s}%')";
        }
        if ($gender !== '') {
            $g    = $db->escapeString($gender);
            $hw[] = "h.gender = '{$g}'";
        }
        if ($isPwd === '1')      $hw[] = "h.is_pwd = 1";
        if (in_array($isSenior, ['1', 'with_id', 'without_id'], true)) {
            $seniorCutoff = date('Y-m-d', strtotime('-60 years'));
            $headSenior = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$seniorCutoff}'";
            $memberSenior = "EXISTS (SELECT 1 FROM household_members sm WHERE sm.household_no = h.household_no AND sm.date_of_birth IS NOT NULL AND sm.date_of_birth <= '{$seniorCutoff}'";
            if ($isSenior === 'with_id') {
                $headSenior .= " AND h.id_senior_path IS NOT NULL AND h.id_senior_path <> ''";
                $memberSenior .= " AND sm.id_senior_path IS NOT NULL AND sm.id_senior_path <> ''";
            } elseif ($isSenior === 'without_id') {
                $headSenior .= " AND (h.id_senior_path IS NULL OR h.id_senior_path = '')";
                $memberSenior .= " AND (sm.id_senior_path IS NULL OR sm.id_senior_path = '')";
            }
            $memberSenior .= ')';
            $hw[] = "({$headSenior} OR {$memberSenior})";
        }
        if ($isSolo === '1')     $hw[] = "h.is_solo_parent = 1";
        if ($is4ps === '1')      $hw[] = "h.is_4ps = 1";
        if ($isStudent === '1')  $hw[] = "UPPER(h.occupation) LIKE '%STUDENT%'";
        if ($isOsy === '1') {
            $youthMinDob = date('Y-m-d', strtotime('-30 years'));
            $youthMaxDob = date('Y-m-d', strtotime('-15 years'));
            $headOsy = "h.date_of_birth IS NOT NULL AND h.date_of_birth BETWEEN '{$youthMinDob}' AND '{$youthMaxDob}' AND (UPPER(h.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(h.occupation) LIKE '%OUT OF SCHOOL%' OR UPPER(h.occupation) LIKE '%OSY%')";
            $memberOsy = "EXISTS (SELECT 1 FROM household_members sm WHERE sm.household_no = h.household_no AND sm.date_of_birth IS NOT NULL AND sm.date_of_birth BETWEEN '{$youthMinDob}' AND '{$youthMaxDob}' AND (UPPER(sm.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(sm.occupation) LIKE '%OUT OF SCHOOL%' OR UPPER(sm.occupation) LIKE '%OSY%'))";
            $hw[] = "({$headOsy} OR {$memberOsy})";
        }
        if ($isIndigent === '1') $hw[] = "h.monthly_income > 0 AND h.monthly_income <= 5000";
        if ($censusYear !== '') {
            $y    = (int) $censusYear;
            $hw[] = "h.census_year = {$y}";
        }
        if ($ageMin !== '') {
            $d    = date('Y-m-d', strtotime('-' . (int)$ageMin . ' years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$d}'";
        }
        if ($ageMax !== '') {
            $d    = date('Y-m-d', strtotime('-' . (int)$ageMax . ' years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth >= '{$d}'";
        }

        $hwSql = ! empty($hw) ? ' WHERE ' . implode(' AND ', $hw) : '';

        // ── Fetch matching households ─────────────────────────────────────────
        $households = $db->query(
            "SELECT h.* FROM households h{$hwSql} ORDER BY h.zone ASC, h.household_no ASC"
        )->getResultArray();

        // Attach members to each household
        $memberModel = new HouseholdMemberModel();
        foreach ($households as &$hh) {
            $hh['members'] = $memberModel
                ->where('household_no', $hh['household_no'])
                ->orderBy('relationship', 'ASC')
                ->findAll();
        }
        unset($hh);

        // ── Group by zone for page breaks ─────────────────────────────────────
        $byZone = [];
        foreach ($households as $hh) {
            $z = $hh['zone'] ?: 'Unassigned';
            $byZone[$z][] = $hh;
        }

        // ── Build a human-readable filter summary for the PDF header ──────────
        $activeFilters = [];
        if ($zone)             $activeFilters[] = 'Zone: ' . $zone;
        if ($gender)           $activeFilters[] = 'Gender: ' . $gender;
        if ($ageMin)           $activeFilters[] = 'Age ≥ ' . $ageMin;
        if ($ageMax)           $activeFilters[] = 'Age ≤ ' . $ageMax;
        if ($isPwd === '1')    $activeFilters[] = 'PWD';
        if ($isSenior === '1') $activeFilters[] = 'Senior Citizen';
        if ($isSenior === '1') $activeFilters[] = 'Senior Citizens (60+)';
        if ($isSenior === 'with_id') $activeFilters[] = 'Senior Citizens (60+) with ID';
        if ($isSenior === 'without_id') $activeFilters[] = 'Senior Citizens (60+) without ID';
        if ($isSolo === '1')   $activeFilters[] = 'Solo Parent';
        if ($is4ps === '1')    $activeFilters[] = '4Ps';
        if ($isStudent === '1')  $activeFilters[] = 'Has Student';
        if ($isIndigent === '1') $activeFilters[] = 'Indigency Eligible';
        if ($search)             $activeFilters[] = 'Search: "' . $search . '"';
        if ($censusYear !== '')  $activeFilters[] = 'Year: ' . $censusYear;

        return view('dashboard/captain/census_export_pdf', [
            'byZone'           => $byZone,
            'zone'             => $zone,
            'censusYear'       => $censusYear !== '' ? (int) $censusYear : null,
            'totalProjected'   => count($households),
            'dateAccomplished' => date('m/d/Y'),
            'role'             => $role,
            'activeFilters'    => $activeFilters,
        ]);
    }
}
