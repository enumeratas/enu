<?php

namespace App\Database\Migrations;

use App\Models\SkYouthModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

/**
 * Puts SK profiling into third normal form.
 *
 * Organizations, ranked issues, and checkbox groups were repeating values
 * inside sk_youth. Parent name and occupation describe the parent, and the
 * age group is calculated from the date of birth. Those facts now live in
 * their own tables, with one value in each column.
 */
class NormalizeSkYouthToThirdNormalForm extends Migration
{
    public function up()
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection || ! $db->tableExists('sk_youth')) {
            return;
        }

        $this->createOrganizations();
        $this->createIssues();
        $this->createChoices();
        $this->createGuardians();

        $fields = $db->getFieldNames('sk_youth');
        if (in_array('organizations', $fields, true) || in_array('mother_name', $fields, true) || in_array('issue_1', $fields, true)) {
            $this->copyExistingValues($db);
        }

        $drop = array_values(array_intersect($db->getFieldNames('sk_youth'), [
            'organizations',
            'health_concerns',
            'social_inclusion',
            'advocacy',
            'volunteer',
            'issue_1',
            'issue_2',
            'issue_3',
            'age_group',
            'mother_name',
            'mother_occupation',
            'father_name',
            'father_occupation',
        ]));
        if ($drop !== []) {
            $this->forge->dropColumn('sk_youth', $drop);
        }
    }

    public function down()
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection || ! $db->tableExists('sk_youth')) {
            return;
        }

        $fields = $db->getFieldNames('sk_youth');
        $add = [];
        if (! in_array('mother_name', $fields, true)) {
            $add['mother_name'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true];
        }
        if (! in_array('mother_occupation', $fields, true)) {
            $add['mother_occupation'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
        }
        if (! in_array('father_name', $fields, true)) {
            $add['father_name'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true];
        }
        if (! in_array('father_occupation', $fields, true)) {
            $add['father_occupation'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
        }
        if (! in_array('organizations', $fields, true)) {
            $add['organizations'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! in_array('age_group', $fields, true)) {
            $add['age_group'] = ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true];
        }
        if (! in_array('health_concerns', $fields, true)) {
            $add['health_concerns'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! in_array('social_inclusion', $fields, true)) {
            $add['social_inclusion'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! in_array('advocacy', $fields, true)) {
            $add['advocacy'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! in_array('volunteer', $fields, true)) {
            $add['volunteer'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! in_array('issue_1', $fields, true)) {
            $add['issue_1'] = ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true];
        }
        if (! in_array('issue_2', $fields, true)) {
            $add['issue_2'] = ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true];
        }
        if (! in_array('issue_3', $fields, true)) {
            $add['issue_3'] = ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true];
        }
        if ($add !== []) {
            $this->forge->addColumn('sk_youth', $add);
        }

        if ($db->tableExists('sk_youth_organizations')) {
            $grouped = [];
            foreach ($db->table('sk_youth_organizations')->orderBy('sort_order', 'ASC')->get()->getResultArray() as $row) {
                $grouped[(int) $row['youth_id']][] = [
                    'name'     => (string) ($row['organization_name'] ?? ''),
                    'position' => (string) ($row['position'] ?? ''),
                    'year'     => (string) ($row['membership_year'] ?? ''),
                ];
            }
            foreach ($grouped as $youthId => $orgs) {
                $db->table('sk_youth')->where('id', $youthId)->update([
                    'organizations' => json_encode($orgs),
                ]);
            }
            $this->forge->dropTable('sk_youth_organizations', true);
        }

        if ($db->tableExists('sk_youth_issues')) {
            foreach ($db->table('sk_youth_issues')->get()->getResultArray() as $row) {
                $rank = (int) ($row['rank_no'] ?? 0);
                if ($rank < 1 || $rank > 3) {
                    continue;
                }
                $db->table('sk_youth')->where('id', (int) $row['youth_id'])->update([
                    'issue_' . $rank => $row['concern'] ?? null,
                ]);
            }
            $this->forge->dropTable('sk_youth_issues', true);
        }

        if ($db->tableExists('sk_youth_choices')) {
            $grouped = [];
            foreach ($db->table('sk_youth_choices')->orderBy('id', 'ASC')->get()->getResultArray() as $row) {
                $grouped[(int) $row['youth_id']][(string) $row['choice_group']][] = (string) $row['choice_value'];
            }
            $columns = [
                'health' => ['health_concerns', true],
                'social' => ['social_inclusion', true],
                'advocacy' => ['advocacy', false],
                'volunteer' => ['volunteer', false],
            ];
            foreach ($grouped as $youthId => $groups) {
                $update = [];
                foreach ($columns as $group => [$column, $asJson]) {
                    $values = $groups[$group] ?? [];
                    if ($values === []) {
                        continue;
                    }
                    $update[$column] = $asJson ? json_encode($values) : implode(', ', $values);
                }
                if ($update !== []) {
                    $db->table('sk_youth')->where('id', $youthId)->update($update);
                }
            }
            $this->forge->dropTable('sk_youth_choices', true);
        }

        if ($db->tableExists('sk_youth_guardians')) {
            foreach ($db->table('sk_youth_guardians')->get()->getResultArray() as $row) {
                $role = (string) ($row['role'] ?? '');
                if (! in_array($role, ['mother', 'father'], true)) {
                    continue;
                }
                $db->table('sk_youth')->where('id', (int) $row['youth_id'])->update([
                    $role . '_name' => $row['full_name'] ?? null,
                    $role . '_occupation' => $row['occupation'] ?? null,
                ]);
            }
            $this->forge->dropTable('sk_youth_guardians', true);
        }
    }

    private function createOrganizations(): void
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection || $db->tableExists('sk_youth_organizations')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'youth_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sort_order' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true],
            'organization_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'position' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'membership_year' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['youth_id', 'sort_order']);
        $this->forge->addForeignKey('youth_id', 'sk_youth', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sk_youth_organizations');
    }

    private function createIssues(): void
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection || $db->tableExists('sk_youth_issues')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'youth_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rank_no' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true],
            'concern' => ['type' => 'VARCHAR', 'constraint' => 200],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['youth_id', 'rank_no']);
        $this->forge->addForeignKey('youth_id', 'sk_youth', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sk_youth_issues');
    }

    private function createChoices(): void
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection || $db->tableExists('sk_youth_choices')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'youth_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'choice_group' => ['type' => 'ENUM', 'constraint' => ['health', 'social', 'advocacy', 'volunteer']],
            'choice_value' => ['type' => 'VARCHAR', 'constraint' => 120],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['youth_id', 'choice_group', 'choice_value']);
        $this->forge->addForeignKey('youth_id', 'sk_youth', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sk_youth_choices');
    }

    private function createGuardians(): void
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection || $db->tableExists('sk_youth_guardians')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'youth_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'role' => ['type' => 'ENUM', 'constraint' => ['mother', 'father']],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'occupation' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['youth_id', 'role']);
        $this->forge->addForeignKey('youth_id', 'sk_youth', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sk_youth_guardians');
    }

    private function copyExistingValues(BaseConnection $db): void
    {
        $rows = $db->table('sk_youth')->get()->getResultArray();
        foreach ($rows as $row) {
            $youthId = (int) ($row['id'] ?? 0);
            if ($youthId < 1) {
                continue;
            }

            $orgs = SkYouthModel::parseOrganizations($row['organizations'] ?? null);
            if ($orgs !== [] && (int) $db->table('sk_youth_organizations')->where('youth_id', $youthId)->countAllResults() === 0) {
                $batch = [];
                foreach ($orgs as $index => $org) {
                    $batch[] = [
                        'youth_id' => $youthId,
                        'sort_order' => $index + 1,
                        'organization_name' => $org['name'],
                        'position' => $org['position'] !== '' ? $org['position'] : null,
                        'membership_year' => $org['year'] !== '' ? $org['year'] : null,
                    ];
                }
                $db->table('sk_youth_organizations')->insertBatch($batch);
            }

            for ($rank = 1; $rank <= 3; $rank++) {
                $concern = SkYouthModel::limitText($row['issue_' . $rank] ?? '', 200);
                if ($concern === null) {
                    continue;
                }
                $exists = $db->table('sk_youth_issues')->where('youth_id', $youthId)->where('rank_no', $rank)->countAllResults();
                if ($exists > 0) {
                    continue;
                }
                $db->table('sk_youth_issues')->insert([
                    'youth_id' => $youthId,
                    'rank_no' => $rank,
                    'concern' => $concern,
                ]);
            }

            $groups = [
                'health' => SkYouthModel::parseChoiceList($row['health_concerns'] ?? null),
                'social' => SkYouthModel::parseChoiceList($row['social_inclusion'] ?? null),
                'advocacy' => SkYouthModel::parseChoiceList($row['advocacy'] ?? null),
                'volunteer' => SkYouthModel::parseChoiceList($row['volunteer'] ?? null),
            ];
            foreach ($groups as $group => $values) {
                foreach ($values as $value) {
                    $exists = $db->table('sk_youth_choices')
                        ->where('youth_id', $youthId)
                        ->where('choice_group', $group)
                        ->where('choice_value', $value)
                        ->countAllResults();
                    if ($exists > 0) {
                        continue;
                    }
                    $db->table('sk_youth_choices')->insert([
                        'youth_id' => $youthId,
                        'choice_group' => $group,
                        'choice_value' => $value,
                    ]);
                }
            }

            foreach (['mother', 'father'] as $role) {
                $name = SkYouthModel::limitText($row[$role . '_name'] ?? '', 150);
                $occupation = SkYouthModel::limitText($row[$role . '_occupation'] ?? '', 100);
                if ($name === null && $occupation === null) {
                    continue;
                }
                $exists = $db->table('sk_youth_guardians')->where('youth_id', $youthId)->where('role', $role)->countAllResults();
                if ($exists > 0) {
                    continue;
                }
                $db->table('sk_youth_guardians')->insert([
                    'youth_id' => $youthId,
                    'role' => $role,
                    'full_name' => $name,
                    'occupation' => $occupation,
                ]);
            }
        }
    }
}
