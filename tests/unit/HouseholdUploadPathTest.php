<?php

use App\Controllers\HouseholdUploadController;
use CodeIgniter\Test\CIUnitTestCase;

final class HouseholdUploadPathTest extends CIUnitTestCase
{
    public function testNormalizePathAcceptsLegacyAndCurrentUploadPaths(): void
    {
        $this->assertSame('uploads/ids/pwd_123.png', HouseholdUploadController::normalizeUploadPath('ids/pwd_123.png'));
        $this->assertSame('uploads/ids/pwd_123.png', HouseholdUploadController::normalizeUploadPath('/uploads/ids/pwd_123.png'));
        $this->assertSame('uploads/ids/pwd_123.png', HouseholdUploadController::normalizeUploadPath('public/uploads/ids/pwd_123.png'));
        $this->assertSame('uploads/ownership/household.png', HouseholdUploadController::normalizeUploadPath('ownership/household.png'));
    }
}
