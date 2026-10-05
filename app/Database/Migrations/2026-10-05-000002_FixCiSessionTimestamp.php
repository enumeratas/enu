<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * CodeIgniter's database session handler stores `timestamp` with NOW().
 * The original column was an unsigned INT, so NOW() overflowed to
 * 4294967295 and every session looked expired. Garbage collection then
 * deleted the signed-in session, and the next click opened the login page.
 */
class FixCiSessionTimestamp extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('ci_sessions')) {
            return;
        }

        $type = '';
        foreach ($this->db->getFieldData('ci_sessions') as $column) {
            if ($column->name === 'timestamp') {
                $type = strtolower((string) $column->type);
                break;
            }
        }

        if (in_array($type, ['datetime', 'timestamp'], true)) {
            return;
        }

        $table = $this->db->prefixTable('ci_sessions');

        $this->db->query("ALTER TABLE {$table} ADD COLUMN session_seen DATETIME NULL");
        $this->db->query("UPDATE {$table} SET session_seen = NOW()");

        $indexes = $this->db->query("SHOW INDEX FROM {$table}")->getResultArray();
        $drop = [];
        foreach ($indexes as $index) {
            $name = (string) ($index['Key_name'] ?? '');
            if ($name !== '' && $name !== 'PRIMARY' && ($index['Column_name'] ?? '') === 'timestamp') {
                $drop[$name] = true;
            }
        }
        foreach (array_keys($drop) as $name) {
            $safe = str_replace('`', '', $name);
            $this->db->query("ALTER TABLE {$table} DROP INDEX `{$safe}`");
        }

        $this->db->query("ALTER TABLE {$table} DROP COLUMN `timestamp`");
        $this->db->query("ALTER TABLE {$table} CHANGE session_seen `timestamp` DATETIME NOT NULL");
        $this->db->query("ALTER TABLE {$table} ADD INDEX `timestamp` (`timestamp`)");
    }

    public function down()
    {
        if (! $this->db->tableExists('ci_sessions')) {
            return;
        }

        $table = $this->db->prefixTable('ci_sessions');
        $this->db->query("ALTER TABLE {$table} ADD COLUMN timestamp_unix INT UNSIGNED NOT NULL DEFAULT 0");
        $this->db->query("UPDATE {$table} SET timestamp_unix = UNIX_TIMESTAMP(`timestamp`)");
        $this->db->query("ALTER TABLE {$table} DROP INDEX `timestamp`");
        $this->db->query("ALTER TABLE {$table} DROP COLUMN `timestamp`");
        $this->db->query("ALTER TABLE {$table} CHANGE timestamp_unix `timestamp` INT UNSIGNED NOT NULL DEFAULT 0");
        $this->db->query("ALTER TABLE {$table} ADD INDEX `timestamp` (`timestamp`)");
    }
}
