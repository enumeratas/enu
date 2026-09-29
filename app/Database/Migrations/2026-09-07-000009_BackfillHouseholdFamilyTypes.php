<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillHouseholdFamilyTypes extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
            UPDATE households h
            SET family_type = CASE
                WHEN EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no
                      AND LOWER(m.relationship) IN ('grandparent', 'grandchild')
                ) THEN 'Multigenerational family'
                WHEN EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'spouse'
                ) AND EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'child'
                ) AND EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no
                      AND LOWER(m.relationship) IN ('sibling', 'father', 'mother', 'aunt/uncle', 'cousin', 'other relative')
                ) THEN 'Extended family'
                WHEN EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'child'
                ) AND NOT EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'spouse'
                ) THEN 'Single-parent family'
                WHEN EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'spouse'
                ) AND EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'child'
                ) THEN 'Nuclear family'
                WHEN EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no AND LOWER(m.relationship) = 'spouse'
                ) THEN 'Childless family'
                WHEN EXISTS (
                    SELECT 1 FROM household_members m
                    WHERE m.household_no = h.household_no
                      AND LOWER(m.relationship) IN ('sibling', 'father', 'mother', 'aunt/uncle', 'cousin', 'other relative')
                ) THEN 'Extended family'
                ELSE 'Chosen family'
            END
            WHERE family_type IS NULL OR family_type = ''
        SQL);
    }

    public function down()
    {
        $this->db->table('households')->update(['family_type' => null]);
    }
}
