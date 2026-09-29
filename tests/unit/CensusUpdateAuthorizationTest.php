<?php

use App\Models\CensusUpdateAuthorizationModel;
use CodeIgniter\Test\CIUnitTestCase;

final class CensusUpdateAuthorizationTest extends CIUnitTestCase
{
    public function testAuthorizationTokenIsGeneratedAndValidFormat(): void
    {
        $model = new CensusUpdateAuthorizationModel();
        $token = $model->generateToken();

        $this->assertIsString($token);
        $this->assertNotSame('', $token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $token);
    }

    public function testHouseholdHeadGetsFullUpdateAccessWhileMembersAreLimited(): void
    {
        $user = [
            'first_name' => 'Maria',
            'last_name' => 'Bantayan',
        ];

        $household = [
            'household_no' => '17089',
            'first_name' => 'Maria',
            'last_name' => 'Bantayan',
        ];

        $members = [
            ['first_name' => 'Maria', 'last_name' => 'Bantayan'],
            ['first_name' => 'Liane', 'last_name' => 'Bantayan'],
        ];

        $headAccess = \App\Controllers\CensusController::resolveResidentHouseholdAccess($user, $household, $members);
        $this->assertTrue($headAccess['is_household_head']);
        $this->assertTrue($headAccess['can_edit_household']);
        $this->assertTrue($headAccess['can_edit_personal']);

        $memberUser = ['first_name' => 'Liane', 'last_name' => 'Bantayan'];
        $memberAccess = \App\Controllers\CensusController::resolveResidentHouseholdAccess($memberUser, $household, $members);
        $this->assertFalse($memberAccess['is_household_head']);
        $this->assertFalse($memberAccess['can_edit_household']);
        $this->assertTrue($memberAccess['can_edit_personal']);
    }
}
