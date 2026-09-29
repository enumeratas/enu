<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Replace literal \n sequences stored in document_template HTML with real newlines.
 *
 * Some templates were saved to the database with the two-character sequence
 * backslash + n  (ASCII 92 + 110) instead of a real LF (ASCII 10).
 * This makes the preview render visible "\n" text between every HTML tag.
 *
 * This migration replaces every literal \n in the `html` column with a
 * real newline character using MySQL's REPLACE() function.
 */
class NormaliseTemplateHtmlNewlines extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('document_templates')) {
            return;
        }

        // REPLACE the two-character sequence '\n' (0x5C 0x6E) with a real LF (0x0A)
        // CHAR(10) = LF in MySQL
        $this->db->query(
            "UPDATE document_templates
             SET html = REPLACE(html, '\\\\n', CHAR(10)),
                 updated_at = NOW()
             WHERE html LIKE '%\\\\n%'"
        );
    }

    public function down(): void
    {
        // Not reversible — real newlines are equivalent and preferred
    }
}
