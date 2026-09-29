<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ResidentClearanceCancellationTest extends CIUnitTestCase
{
    public function testPendingResidentRequestShowsCancelAction(): void
    {
        $html = view('dashboard/resident/clearance', [
            'requests' => [[
                'id' => 99,
                'for_member' => 'Juan Dela Cruz',
                'member_relationship' => 'Household Head',
                'document_type' => 'Barangay Clearance',
                'purpose' => 'Employment / Job Application',
                'created_at' => '2026-09-09 08:00:00',
                'est_release_date' => '2026-09-10',
                'status' => 'pending',
            ]],
            'members' => [],
            'user' => [
                'id' => 1,
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
            ],
            'householdTotalIncome' => 0,
            'occupation' => '',
            'isEmployed' => false,
            'goodMoralBlocked' => false,
            'role' => 'resident',
            'flashMessage' => null,
            'flashClass' => 'db-alert--success',
            'flashSuccess' => true,
        ]);

        $this->assertStringContainsString('confirmCancel(99, "Barangay Clearance")', $html);
    }
}
