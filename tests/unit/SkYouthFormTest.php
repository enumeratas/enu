<?php

use App\Models\SkYouthModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SkYouthFormTest extends CIUnitTestCase
{
    public function testStandardChoicesStayAligned(): void
    {
        $this->assertSame('Male', SkYouthModel::normalizeChoice('male', SkYouthModel::GENDERS));
        $this->assertNull(SkYouthModel::normalizeChoice('', SkYouthModel::SCHOOL_TYPES));
        $this->assertSame('Public School', SkYouthModel::normalizeChoice('public school', SkYouthModel::SCHOOL_TYPES));
        $this->assertSame('Junior High School', SkYouthModel::educationChoice('Grade 10 Junior High'));
        $this->assertSame('2005-03-01', SkYouthModel::profileDate('2005-03-01 00:00:00'));
        $this->assertNull(SkYouthModel::profileDate(''));
        $this->assertSame('15-17', SkYouthModel::ageGroupFor(date('Y-m-d', strtotime('-16 years'))));
        $this->assertSame('18-21', SkYouthModel::ageGroupFor(date('Y-m-d', strtotime('-20 years'))));
        $this->assertSame('22-24', SkYouthModel::ageGroupFor(date('Y-m-d', strtotime('-23 years'))));
        $this->assertSame('25-30', SkYouthModel::ageGroupFor(date('Y-m-d', strtotime('-27 years'))));
        $this->assertNull(SkYouthModel::ageGroupFor(date('Y-m-d', strtotime('-10 years'))));
        $this->assertSame(
            '["Health","Education"]',
            SkYouthModel::encodeChoices(['Health', 'Sports', 'Education'], SkYouthModel::ADVOCACY_OPTIONS, true)
        );
        $this->assertSame(
            'SK Activities, Barangay Activity',
            SkYouthModel::encodeChoices('SK Activities, Barangay Activity', SkYouthModel::VOLUNTEER_OPTIONS)
        );
        $this->assertSame(
            [
                ['name' => 'SK', 'position' => 'Member', 'year' => '2024'],
            ],
            SkYouthModel::parseOrganizations('[{"name":"SK","position":"Member","year":"2024"}]')
        );
        $this->assertSame(
            ['Health', 'Education'],
            SkYouthModel::parseChoiceList('Health, Education')
        );
        $this->assertSame([], SkYouthModel::parseOrganizations(''));
    }
}
