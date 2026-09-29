<?php

use App\Models\ScheduleModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ScheduleRoleAccessTest extends CIUnitTestCase
{
    public function testOfficialSchedulingRolesAreAllowed(): void
    {
        $this->assertTrue(ScheduleModel::isOfficialSchedulingRole('admin'));
        $this->assertTrue(ScheduleModel::isOfficialSchedulingRole('secretary'));
        $this->assertTrue(ScheduleModel::isOfficialSchedulingRole('captain'));
        $this->assertFalse(ScheduleModel::isOfficialSchedulingRole('resident'));
        $this->assertFalse(ScheduleModel::isOfficialSchedulingRole('sk'));
    }
}
