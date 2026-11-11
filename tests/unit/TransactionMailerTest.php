<?php

use App\Libraries\TransactionMailer;
use CodeIgniter\Test\CIUnitTestCase;

final class TransactionMailerTest extends CIUnitTestCase
{
    public function testDeliverableAddressAcceptsANormalMailbox(): void
    {
        $this->assertTrue(TransactionMailer::isDeliverableAddress('resident@example.com'));
    }

    public function testDeliverableAddressRejectsBlankAndMalformedValues(): void
    {
        $this->assertFalse(TransactionMailer::isDeliverableAddress(''));
        $this->assertFalse(TransactionMailer::isDeliverableAddress('not-an-email'));
        $this->assertFalse(TransactionMailer::isDeliverableAddress('resident @example.com'));
    }

    public function testTimeRangeKeepsASingleClockWhenTheEndIsMissing(): void
    {
        $this->assertSame('8:00 AM', TransactionMailer::timeRange('08:00:00', null));
        $this->assertSame('Not specified', TransactionMailer::timeRange('', null));
        $this->assertSame('8:00 AM – 10:30 AM', TransactionMailer::timeRange('08:00', '10:30'));
    }

    public function testStampChangesWhenTheAnnouncementChanges(): void
    {
        $first = TransactionMailer::stamp(['title' => 'Cleanup', 'date' => '2026-11-04']);
        $second = TransactionMailer::stamp(['title' => 'Cleanup', 'date' => '2026-11-05']);

        $this->assertNotSame($first, $second);
        $this->assertSame($first, TransactionMailer::stamp(['title' => 'Cleanup', 'date' => '2026-11-04']));
    }
}
