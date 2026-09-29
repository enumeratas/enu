<?php

use App\Libraries\EmailService;
use CodeIgniter\Test\CIUnitTestCase;

final class AccountApprovalEmailTest extends CIUnitTestCase
{
    public function testApprovalEmailMethodExists(): void
    {
        $this->assertTrue(method_exists(EmailService::class, 'sendAccountApprovalEmail'));
    }
}
