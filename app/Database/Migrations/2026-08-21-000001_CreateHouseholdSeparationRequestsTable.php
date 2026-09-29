<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tracks requests to separate a household member into their own household.
 * Follows the Household Split Workflow:
 *   1. Secretary/Captain opens original household record
 *   2. Selects a member and files a "Separate Household" request
 *   3. Secretary reviews and approves/rejects
 *   4. On approval: new household is created, member is removed from original, audit trail logged
 */
class CreateHouseholdSeparationRequestsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('household_separation_requests')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'member_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'comment'    => 'FK → household_members.id (member requesting separation)',
                ],
                'original_household_no' => [
                    'type'       => 'CHAR',
                    'constraint' => 5,
                    'comment'    => 'FK → households.household_no (member source household)',
                ],
                'separation_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Separate Household', 'Transfer Member'],
                    'default'    => 'Separate Household',
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'approved', 'rejected'],
                    'default'    => 'pending',
                ],
                'requested_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'processed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'processed_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('member_id');
            $this->forge->addKey('original_household_no');
            $this->forge->addKey('status');
            $this->forge->addForeignKey('member_id', 'household_members', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('original_household_no', 'households', 'household_no', 'CASCADE', 'CASCADE');
            $this->forge->createTable('household_separation_requests');
        }

        if (! $this->db->tableExists('household_separation_request_details')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'request_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'comment'    => 'FK → household_separation_requests.id',
                ],
                'change_reason' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'comment'    => 'e.g. Married, Employment Relocation, Family Dispute',
                ],
                'new_household_no' => [
                    'type'       => 'CHAR',
                    'constraint' => 5,
                    'null'       => true,
                    'comment'    => 'FK → households.household_no when approved',
                ],
                'new_zone' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'new_address' => ['type' => 'TEXT', 'null' => true],
                'new_civil_status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Single', 'Married', 'Widowed', 'Separated', 'Annulled'],
                    'null'       => true,
                ],
                'proof_verified' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'comment'    => 'Secretary confirms the new address and proof were verified',
                ],
                'rejection_reason' => ['type' => 'TEXT', 'null' => true],
                'audit_note' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'comment' => 'System-generated log: "From Household #XXXXX to Household #YYYYY"',
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('request_id');
            $this->forge->addForeignKey('request_id', 'household_separation_requests', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('new_household_no', 'households', 'household_no', 'SET NULL', 'SET NULL');
            $this->forge->createTable('household_separation_request_details');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('household_separation_request_details')) {
            $this->forge->dropTable('household_separation_request_details');
        }

        if ($this->db->tableExists('household_separation_requests')) {
            $this->forge->dropTable('household_separation_requests');
        }
    }
}
