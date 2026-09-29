    $this->assertFalse(OfflineSyncController::supportsOperation('concern_submission'));
    <?php

    use App\Controllers\AuthController;
    use App\Controllers\OfflineSyncController;
    use CodeIgniter\Test\CIUnitTestCase;

    final class OfflineSyncSupportTest extends CIUnitTestCase
    {
        public function testOfflineSyncSupportsClearanceAndBlotterSubmissionOperations(): void
        {
            $this->assertTrue(OfflineSyncController::supportsOperation('clearance_request'));
            $this->assertTrue(OfflineSyncController::supportsOperation('blotter_submission'));
            $this->assertFalse(OfflineSyncController::supportsOperation('concern_submission'));
            $this->assertFalse(OfflineSyncController::supportsOperation('unsupported_operation'));
        }

        public function testOfflineSessionSnapshotIsValidWithinAllowedWindow(): void
        {
            $snapshot = AuthController::sessionSnapshot([
                'id' => 42,
                'username' => 'resident01',
                'first_name' => 'Maria',
                'middle_name' => 'B.',
                'last_name' => 'Dela Cruz',
                'role' => 'resident',
                'avatar' => null,
                'household_no' => 'H-101',
            ]);

            $this->assertSame('resident01', $snapshot['username']);
            $this->assertSame('resident', $snapshot['role']);
            $this->assertTrue(AuthController::isOfflineSessionValid($snapshot));

            $expired = $snapshot;
            $expired['expires_at'] = date('c', strtotime('-1 day'));
            $this->assertFalse(AuthController::isOfflineSessionValid($expired));
        }
    }
