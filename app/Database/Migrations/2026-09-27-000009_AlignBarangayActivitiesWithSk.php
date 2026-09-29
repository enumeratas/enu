<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignBarangayActivitiesWithSk extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('barangay_activities')) {
            return;
        }

        $fields = $this->db->getFieldNames('barangay_activities');
        $add    = [];

        if (! in_array('category', $fields, true)) {
            $add['category'] = ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Other'];
        }
        if (! in_array('requirements', $fields, true)) {
            $add['requirements'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! in_array('start_date', $fields, true)) {
            $add['start_date'] = ['type' => 'DATE', 'null' => true];
        }
        if (! in_array('end_date', $fields, true)) {
            $add['end_date'] = ['type' => 'DATE', 'null' => true];
        }
        if (! in_array('conducted_date', $fields, true)) {
            $add['conducted_date'] = ['type' => 'DATE', 'null' => true];
        }
        if (! in_array('min_age', $fields, true)) {
            $add['min_age'] = ['type' => 'INT', 'constraint' => 3, 'unsigned' => true, 'null' => true];
        }
        if (! in_array('max_age', $fields, true)) {
            $add['max_age'] = ['type' => 'INT', 'constraint' => 3, 'unsigned' => true, 'null' => true];
        }
        if (! in_array('target_participants', $fields, true)) {
            $add['target_participants'] = ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0];
        }
        if (! in_array('notify_residents', $fields, true)) {
            $add['notify_residents'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
        }

        if ($add !== []) {
            $this->forge->addColumn('barangay_activities', $add);
        }

        $this->db->query(
            'UPDATE barangay_activities
             SET conducted_date = activity_date
             WHERE conducted_date IS NULL AND activity_date IS NOT NULL'
        );
        $this->db->query(
            'UPDATE barangay_activities
             SET start_date = activity_date
             WHERE start_date IS NULL AND activity_date IS NOT NULL'
        );

        $today = date('Y-m-d');
        $rows  = $this->db->table('barangay_activities')->select('id, status, conducted_date, activity_date')->get()->getResultArray();
        foreach ($rows as $row) {
            if (($row['status'] ?? '') === 'Cancelled') {
                continue;
            }
            $date = $row['conducted_date'] ?: ($row['activity_date'] ?? null);
            if (! $date) {
                continue;
            }
            $status = $date < $today ? 'Completed' : ($date === $today ? 'Active' : 'Upcoming');
            if ($status !== ($row['status'] ?? '')) {
                $this->db->table('barangay_activities')->where('id', $row['id'])->update(['status' => $status]);
            }
        }

        if ($this->db->tableExists('barangay_activity_registrations')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'activity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'requirements_submitted' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'attachments' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('activity_id');
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey(['activity_id', 'user_id']);
        $this->forge->createTable('barangay_activity_registrations');
    }

    public function down()
    {
        if ($this->db->tableExists('barangay_activity_registrations')) {
            $this->forge->dropTable('barangay_activity_registrations');
        }

        if (! $this->db->tableExists('barangay_activities')) {
            return;
        }

        $fields = $this->db->getFieldNames('barangay_activities');
        $drop   = array_values(array_intersect($fields, [
            'category',
            'requirements',
            'start_date',
            'end_date',
            'conducted_date',
            'min_age',
            'max_age',
            'target_participants',
            'notify_residents',
        ]));

        if ($drop !== []) {
            $this->forge->dropColumn('barangay_activities', $drop);
        }
    }
}
