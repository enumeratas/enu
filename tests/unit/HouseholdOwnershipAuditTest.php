<?php

use App\Controllers\CensusController;
use CodeIgniter\Test\CIUnitTestCase;

final class HouseholdOwnershipAuditTest extends CIUnitTestCase
{
    public function testOwnershipAuditInsertDoesNotCrashWhenAuditTableIsMissing(): void
    {
        $method = new ReflectionMethod(CensusController::class, 'recordHouseholdOwnershipChange');
        $method->setAccessible(true);

        $controller = new CensusController();

        $method->invoke($controller, [
            'household_no' => '12345',
            'changed_by' => 1,
            'old_ownership' => 'Owned',
            'new_ownership' => 'Shared',
            'old_num_families' => 1,
            'new_num_families' => 2,
            'document_path' => null,
            'notes' => 'Test note',
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertTrue(true);
    }
}
