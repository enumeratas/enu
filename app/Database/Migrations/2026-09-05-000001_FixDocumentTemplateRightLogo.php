<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Replace the second (right-side) bacolod.png logo with Picture1.png
 * in all bc-wrap document templates that use the two-seal header layout.
 *
 * The header HTML pattern is:
 *   <img src="/bacolod.png" ...>  ← left seal (kept as-is)
 *   <div class="bc-header-center">...</div>
 *   <img src="/bacolod.png" ...>  ← right seal (changed to /Picture1.png)
 *
 * We do a targeted replacement: after the bc-header-center closing tag,
 * change the first img src that is still /bacolod.png to /Picture1.png.
 */
class FixDocumentTemplateRightLogo extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('document_templates')) {
            return;
        }

        $rows = $this->db->table('document_templates')->get()->getResultArray();

        foreach ($rows as $row) {
            $html = $row['html'] ?? '';

            // Only process templates that use the bc-header-row two-seal layout
            if (strpos($html, 'bc-header-row') === false) {
                continue;
            }

            // Replace the second occurrence of /bacolod.png (the right seal)
            // Strategy: split on </div> after bc-header-center to locate the right img
            $fixed = $this->fixRightLogo($html);

            if ($fixed !== $html) {
                $this->db->table('document_templates')
                    ->where('id', $row['id'])
                    ->update(['html' => $fixed, 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('document_templates')) {
            return;
        }

        $rows = $this->db->table('document_templates')->get()->getResultArray();

        foreach ($rows as $row) {
            $html = $row['html'] ?? '';

            if (strpos($html, 'bc-header-row') === false) {
                continue;
            }

            $reverted = $this->revertRightLogo($html);

            if ($reverted !== $html) {
                $this->db->table('document_templates')
                    ->where('id', $row['id'])
                    ->update(['html' => $reverted, 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    /**
     * Replace the right-side seal: the img that comes AFTER </div> (closing
     * bc-header-center) within the bc-header-row.
     */
    private function fixRightLogo(string $html): string
    {
        // Find bc-header-center closing </div>, then replace next bacolod.png img
        $marker = '</div>';
        $pos = strpos($html, 'bc-header-center');
        if ($pos === false) {
            return $html;
        }

        // Find the closing </div> of bc-header-center after its opening
        $closePos = strpos($html, $marker, $pos);
        if ($closePos === false) {
            return $html;
        }

        $after = substr($html, $closePos);
        $afterFixed = preg_replace(
            '#(<img[^>]+src=["\'])/bacolod\.png(["\'][^>]*>)#',
            '$1/Picture1.png$2',
            $after,
            1  // replace only the first occurrence (the right seal)
        );

        return substr($html, 0, $closePos) . $afterFixed;
    }

    private function revertRightLogo(string $html): string
    {
        $marker = '</div>';
        $pos = strpos($html, 'bc-header-center');
        if ($pos === false) {
            return $html;
        }

        $closePos = strpos($html, $marker, $pos);
        if ($closePos === false) {
            return $html;
        }

        $after = substr($html, $closePos);
        $afterReverted = preg_replace(
            '#(<img[^>]+src=["\'])/Picture1\.png(["\'][^>]*>)#',
            '$1/bacolod.png$2',
            $after,
            1
        );

        return substr($html, 0, $closePos) . $afterReverted;
    }
}
