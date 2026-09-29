<?php

use App\Controllers\CensusController;
use CodeIgniter\Test\CIUnitTestCase;

final class HouseholdTransferValidationTest extends CIUnitTestCase
{
    public function testTransferDestinationValidationRejectsMissingOrSameHousehold(): void
    {
        $missing = CensusController::validateTransferDestinationHousehold('1001', '');
        $this->assertFalse($missing['valid']);
        $this->assertStringContainsString('destination household', strtolower((string) $missing['message']));

        $same = CensusController::validateTransferDestinationHousehold('1001', '1001');
        $this->assertFalse($same['valid']);
        $this->assertStringContainsString('different from the current household', strtolower((string) $same['message']));

        $valid = CensusController::validateTransferDestinationHousehold('1001', '2002');
        $this->assertTrue($valid['valid']);
        $this->assertSame('2002', $valid['destination_household_no']);
    }
}
