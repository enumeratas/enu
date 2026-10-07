<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportsExportController extends BaseController
{
    /**
     * Render a print-ready HTML page using the same data as the on-screen report.
     * The browser's "Save as PDF" / "Print" produces a proper text-based PDF.
     */
    public function export(string $role = 'secretary')
    {
        return $this->renderReport($role);
    }

    public function download(string $role = 'secretary')
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setChroot(FCPATH);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->renderReport($role));
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();

        return $this->response->download(
            'barangay-report-' . date('Y-m-d') . '.pdf',
            $dompdf->output()
        );
    }

    public function skExport()
    {
        return $this->renderSkReport();
    }

    public function skDownload()
    {
        $year = $this->skReportYear();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->renderSkReport());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response->download(
            'sk-report-' . $year . '.pdf',
            $dompdf->output()
        );
    }

    private function skReportYear(): int
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        return $year;
    }

    private function renderSkReport(): string
    {
        $year = $this->skReportYear();
        $ui = new UIController();
        $settings = (new \App\Models\BarangaySettingsModel())->getReportFrontPageSettings();

        return view('dashboard/sk/reports_export', array_merge(
            $ui->buildSkReportData($year),
            [
                'filterYear' => $year,
                'reportFrontPage' => $settings,
            ]
        ));
    }

    private function renderReport(string $role): string
    {
        $data = $this->_buildReportData();

        $settingsModel = new \App\Models\BarangaySettingsModel();
        $reportFrontPage = $settingsModel->getReportFrontPageSettings();

        // Parse which sections to include (default: all)
        $sectionsParam = trim($this->request->getGet('sections') ?? '');
        $allSections   = ['demographic', 'age_bracket', 'sector', 'education', 'water'];

        if ($sectionsParam === '' || $sectionsParam === 'all') {
            $sections = $allSections;
        } else {
            $requested = array_map('trim', explode(',', $sectionsParam));
            $sections  = array_values(array_intersect($allSections, $requested));
            if (empty($sections)) $sections = $allSections;
        }

        return view('reports_export', array_merge($data, [
            'role'           => $role,
            'sections'       => array_flip($sections), // flip for fast isset() checks in view
            'reportFrontPage' => $reportFrontPage,
        ]));
    }

    // ── Shared report data builder (mirrors UIController::_buildReportData) ───
    private function _buildReportData(): array
    {
        $db = \Config\Database::connect();

        $heads   = $db->table('households')
            ->select('date_of_birth, gender, civil_status, occupation, nationality,
                      is_pwd, is_solo_parent, is_4ps, is_senior_citizen,
                      is_indigenous, monthly_income, educational_attainment,
                      registered_voter, num_families,
                      water_source_level, water_safety_managed,
                      sanitation_basic, sanitation_managed')
            ->get()->getResultArray();

        $members = $db->table('household_members')
            ->select('date_of_birth, gender, occupation, monthly_income, educational_attainment, is_pwd, marital_status')
            ->get()->getResultArray();

        $age = function (?string $dob): ?int {
            if (empty($dob)) return null;
            try {
                return (int) date_diff(date_create($dob), date_create('today'))->y;
            } catch (\Throwable $e) {
                return null;
            }
        };

        $brackets = [
            ['label' => 'Children 0 – 5 years old',   'min' => 0,  'max' => 5],
            ['label' => 'Children 6 – 12 years old',  'min' => 6,  'max' => 12],
            ['label' => 'Children 13 – 17 years old', 'min' => 13, 'max' => 17],
            ['label' => 'Adult 18 – 35 years old',    'min' => 18, 'max' => 35],
            ['label' => 'Adult 36 – 50 years old',    'min' => 36, 'max' => 50],
            ['label' => 'Adult 51 – 65 years old',    'min' => 51, 'max' => 65],
            ['label' => 'Adult 66 years old & above', 'min' => 66, 'max' => 999],
        ];

        $ageBrackets = array_map(fn($b) => array_merge($b, ['male' => 0, 'female' => 0, 'total' => 0]), $brackets);

        $countPerson = function (array $person) use ($age, $brackets, &$ageBrackets): void {
            $a = $age($person['date_of_birth'] ?? null);
            if ($a === null) return;
            foreach ($brackets as $i => $b) {
                if ($a >= $b['min'] && $a <= $b['max']) {
                    $g = strtolower($person['gender'] ?? '');
                    if ($g === 'female')   $ageBrackets[$i]['female']++;
                    elseif ($g === 'male') $ageBrackets[$i]['male']++;
                    $ageBrackets[$i]['total']++;
                    break;
                }
            }
        };

        foreach ($heads   as $h) $countPerson($h);
        foreach ($members as $m) $countPerson($m);

        $laborForce  = $unemployed = $osy = $osc = 0;
        $pwd         = $ofw = $soloParent = $indigenous = $seniorCitizen = $fourPs = 0;
        $civilSingle = $civilMarried = $civilWidow = $civilSeparated = 0;

        foreach ($heads as $h) {
            $occ = strtolower($h['occupation'] ?? '');
            $a   = $age($h['date_of_birth'] ?? null);

            if (! empty($occ) && ! in_array($occ, ['none', 'n/a', 'unemployed', ''])) $laborForce++;
            if (str_contains($occ, 'unemploy'))  $unemployed++;
            if (str_contains($occ, 'ofw') || str_contains($occ, 'overseas')) $ofw++;

            if ((int) $h['is_pwd'])            $pwd++;
            if ((int) $h['is_solo_parent'])    $soloParent++;
            if ((int) $h['is_indigenous'])     $indigenous++;
            if ((int) $h['is_senior_citizen']) $seniorCitizen++;
            if ((int) $h['is_4ps'])            $fourPs++;

            if ($a !== null && $a >= 15 && $a <= 24 && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) $osy++;
            if ($a !== null && $a >= 6  && $a <= 14  && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) $osc++;

            $cs = strtolower(trim($h['civil_status'] ?? ''));
            if ($cs === 'single')   $civilSingle++;
            if ($cs === 'married')  $civilMarried++;
            if (in_array($cs, ['widowed', 'widow'])) $civilWidow++;
            if (in_array($cs, ['separated', 'annulled'])) $civilSeparated++;
        }

        foreach ($members as $m) {
            $occ = strtolower($m['occupation'] ?? '');
            $a   = $age($m['date_of_birth'] ?? null);

            if (! empty($occ) && ! in_array($occ, ['none', 'n/a', 'unemployed', ''])) $laborForce++;
            if (str_contains($occ, 'unemploy'))  $unemployed++;
            if (str_contains($occ, 'ofw') || str_contains($occ, 'overseas')) $ofw++;

            if ($a !== null && $a >= 15 && $a <= 24 && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) $osy++;
            if ($a !== null && $a >= 6  && $a <= 14  && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) $osc++;
            if ($a !== null && $a >= 60) $seniorCitizen++;
        }

        $sectorMap = [
            'Labor Force' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Unemployed' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Out-of-School Youth (OSY) 15-24 y/o' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Out-of-School Children (OSC) 6-14 y/o' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Persons with Disabilities (PWDs)' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Overseas Filipino Workers (OFWs)' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Solo Parents' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Indigenous Peoples (IPs)' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Civil Status: Single' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Civil Status: Married' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Citizenship: Filipino' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Citizenship: Foreigner' => ['male' => 0, 'female' => 0, 'total' => 0],
        ];

        $addSectorCount = function (string $label, array $person) use (&$sectorMap): void {
            $gender = strtolower($person['gender'] ?? '');
            $sexKey = ($gender === 'female') ? 'female' : 'male';

            if (! array_key_exists($label, $sectorMap)) {
                return;
            }

            $sectorMap[$label][$sexKey]++;
            $sectorMap[$label]['total']++;
        };

        $collectSectorData = function (array $person) use (&$sectorMap, $age, $addSectorCount): void {
            $occ = strtolower($person['occupation'] ?? '');
            $a = $age($person['date_of_birth'] ?? null);

            if (! empty($occ) && ! in_array($occ, ['none', 'n/a', 'unemployed', ''])) {
                $addSectorCount('Labor Force', $person);
            }

            if (str_contains($occ, 'unemploy')) {
                $addSectorCount('Unemployed', $person);
            }

            if (str_contains($occ, 'ofw') || str_contains($occ, 'overseas')) {
                $addSectorCount('Overseas Filipino Workers (OFWs)', $person);
            }

            if ((int) ($person['is_pwd'] ?? 0)) {
                $addSectorCount('Persons with Disabilities (PWDs)', $person);
            }

            if ((int) ($person['is_solo_parent'] ?? 0)) {
                $addSectorCount('Solo Parents', $person);
            }

            if ((int) ($person['is_indigenous'] ?? 0)) {
                $addSectorCount('Indigenous Peoples (IPs)', $person);
            }

            $nationality = strtolower(trim((string) ($person['nationality'] ?? 'filipino')));
            if ($nationality === '' || str_contains($nationality, 'filipino')) {
                $addSectorCount('Citizenship: Filipino', $person);
            } else {
                $addSectorCount('Citizenship: Foreigner', $person);
            }

            if ($a !== null && $a >= 15 && $a <= 24 && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) {
                $addSectorCount('Out-of-School Youth (OSY) 15-24 y/o', $person);
            }

            if ($a !== null && $a >= 6 && $a <= 14 && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) {
                $addSectorCount('Out-of-School Children (OSC) 6-14 y/o', $person);
            }

            $cs = strtolower(trim((string) ($person['civil_status'] ?? $person['marital_status'] ?? '')));
            if ($cs === 'single') {
                $addSectorCount('Civil Status: Single', $person);
            }
            if ($cs === 'married') {
                $addSectorCount('Civil Status: Married', $person);
            }
            if ($cs === 'widowed' || $cs === 'widow') {
                $addSectorCount('Civil Status: Widowed', $person);
            }
            if ($cs === 'separated' || $cs === 'annulled') {
                $addSectorCount('Civil Status: Separated/Annulled', $person);
            }
        };

        foreach ($heads as $h) {
            $collectSectorData($h);
        }

        foreach ($members as $m) {
            $collectSectorData($m);
        }

        $sectorRows = [];
        foreach (array_keys($sectorMap) as $label) {
            $sectorRows[] = [
                'label' => $label,
                'male' => (int) $sectorMap[$label]['male'],
                'female' => (int) $sectorMap[$label]['female'],
                'total' => (int) $sectorMap[$label]['total'],
            ];
        }

        $waterRows = [
            ['label' => 'Level I – Point Source',            'total' => (int)$db->table('households')->where('water_source_level', '1')->countAllResults()],
            ['label' => 'Level II – Communal Faucet',        'total' => (int)$db->table('households')->where('water_source_level', '2')->countAllResults()],
            ['label' => 'Level III – Individual Connection', 'total' => (int)$db->table('households')->where('water_source_level', '3')->countAllResults()],
            ['label' => 'Safe Water (Managed)',               'total' => (int)$db->table('households')->where('water_safety_managed', 1)->countAllResults()],
        ];

        $sanitationRows = [
            ['label' => 'Basic Sanitation Facility',  'total' => (int)$db->table('households')->where('sanitation_basic', 1)->countAllResults()],
            ['label' => 'Safely Managed Sanitation',  'total' => (int)$db->table('households')->where('sanitation_managed', 1)->countAllResults()],
        ];

        $eduCounts = [];
        foreach (
            array_merge(
                array_column($heads,   'educational_attainment'),
                array_column($members, 'educational_attainment')
            ) as $edu
        ) {
            $key = ucwords(strtolower(trim($edu ?? 'Not Specified'))) ?: 'Not Specified';
            $eduCounts[$key] = ($eduCounts[$key] ?? 0) + 1;
        }
        arsort($eduCounts);
        $eduRows = array_map(fn($l, $t) => ['label' => $l, 'total' => $t], array_keys($eduCounts), $eduCounts);

        $totalPop         = count($heads) + count($members);
        $totalHouseholds  = count($heads);
        $totalMale        = array_sum(array_column($ageBrackets, 'male'));
        $totalFemale      = array_sum(array_column($ageBrackets, 'female'));
        $totalClearances  = $db->table('clearance_requests')->where('status', 'approved')->countAllResults();
        $avgHHSize        = $totalHouseholds > 0 ? round($totalPop / $totalHouseholds, 1) : 0;
        $registeredVoters = $db->table('households')->where('registered_voter', 1)->countAllResults();
        $totalFamilies    = (int) $db->query("SELECT COALESCE(SUM(num_families),0) AS t FROM households")->getRow()->t;

        return [
            'totalPop'         => $totalPop,
            'totalMale'        => $totalMale,
            'totalFemale'      => $totalFemale,
            'totalHouseholds'  => $totalHouseholds,
            'totalClearances'  => $totalClearances,
            'avgHHSize'        => $avgHHSize,
            'ageBrackets'      => $ageBrackets,
            'sectorRows'       => $sectorRows,
            'waterRows'        => $waterRows,
            'sanitationRows'   => $sanitationRows,
            'eduRows'          => $eduRows,
            'registeredVoters' => $registeredVoters,
            'totalFamilies'    => $totalFamilies,
        ];
    }
}
