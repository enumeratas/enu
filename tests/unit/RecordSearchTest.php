<?php

use App\Libraries\RecordSearch;
use CodeIgniter\Test\CIUnitTestCase;

final class RecordSearchTest extends CIUnitTestCase
{
    public function testPersonMatchesFullNameInEitherOrder(): void
    {
        $this->assertTrue(RecordSearch::personMatches('Maria Santos', 'Maria Cruz Santos', ''));
        $this->assertTrue(RecordSearch::personMatches('Santos, Maria', 'Maria Cruz Santos', ''));
    }

    public function testPersonMatchesUsernameAndEmail(): void
    {
        $this->assertTrue(RecordSearch::personMatches('maria.santos', 'Maria Santos', 'maria.santos maria@example.com'));
        $this->assertTrue(RecordSearch::personMatches('maria@example.com', 'Maria Santos', 'maria.santos maria@example.com'));
        $this->assertFalse(RecordSearch::personMatches('juan.delacruz', 'Maria Santos', 'maria.santos maria@example.com'));
    }
}
