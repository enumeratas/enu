<?php

namespace App\Libraries;

class PiiGuard
{
    public static function token(string $text, string $variant = 'body'): string
    {
        $text = self::normalize($text);
        $userId = (int) (session()->get('user_id') ?? 0);
        $mac = hash_hmac('sha256', $userId . "\n" . $variant . "\n" . $text, self::secret());
        cache()->save(self::cacheKey($mac, $userId), [
            't' => $text,
            'v' => $variant,
        ], 3600);

        return $mac;
    }

    public static function url(string $text, string $variant = 'body'): string
    {
        $role = preg_replace('/[^a-z]/', '', strtolower((string) (session()->get('role') ?? 'resident'))) ?: 'resident';

        return '/' . $role . '/pii/' . self::token($text, $variant);
    }

    public static function img(?string $text, string $variant = 'body'): string
    {
        $text = trim((string) $text);
        if ($text === '' || $text === '—' || $text === '-') {
            return esc($text === '' ? '—' : $text);
        }

        return '<img class="pii-text pii-text--' . esc($variant, 'attr') . '" src="' . esc(self::url($text, $variant), 'attr') . '" alt="" draggable="false">';
    }

    public static function load(string $token): ?array
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower($token));
        if (strlen($token) !== 64) {
            return null;
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $row = cache()->get(self::cacheKey($token, $userId));

        return is_array($row) && isset($row['t']) ? $row : null;
    }

    public static function png(string $text, string $variant = 'body'): string
    {
        $text = self::normalize($text);
        if ($text === '') {
            $text = '—';
        }

        $style = self::variantStyle($variant);
        $font  = self::fontPath();

        if (! function_exists('imagecreatetruecolor')) {
            return self::svgPngFallback($text, $style);
        }

        if ($font !== null && function_exists('imagettfbbox')) {
            $box = imagettfbbox($style['size'], 0, $font, $text);
            $textW = abs($box[2] - $box[0]);
            $textH = abs($box[7] - $box[1]);
            $width = max(8, $textW + 6);
            $height = max(16, $textH + 8);
            $image = imagecreatetruecolor($width, $height);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparent);
            $color = imagecolorallocate($image, $style['r'], $style['g'], $style['b']);
            imagettftext($image, $style['size'], 0, 3, $height - 5, $color, $font, $text);
        } else {
            $width = max(8, imagefontwidth(3) * strlen($text) + 6);
            $height = 18;
            $image = imagecreatetruecolor($width, $height);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparent);
            $color = imagecolorallocate($image, $style['r'], $style['g'], $style['b']);
            imagestring($image, 3, 2, 2, $text, $color);
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }

    public static function phrases(): array
    {
        $cached = cache()->get('pii_phrases_v1');
        if (is_array($cached) && $cached !== []) {
            return self::withSessionPhrases($cached);
        }

        $phrases = [];
        try {
            $db = \Config\Database::connect();
            $phrases = array_merge(
                $phrases,
                self::fromTable($db, 'users', ['first_name', 'middle_name', 'last_name', 'email', 'contact_number', 'username']),
                self::fromTable($db, 'households', ['first_name', 'middle_name', 'last_name', 'email', 'contact_number', 'address', 'philhealth_no']),
                self::fromTable($db, 'household_members', ['first_name', 'middle_name', 'last_name', 'contact_number', 'philhealth_no']),
                self::fromTable($db, 'concern_submissions', ['full_name', 'email', 'contact_number']),
                self::fromTable($db, 'blotter_reports', ['complainant_name', 'complainant_email', 'complainant_contact', 'complainant_address', 'respondent_name', 'respondent_email', 'respondent_address']),
                self::fromTable($db, 'sk_youth', ['first_name', 'middle_name', 'last_name', 'email', 'contact_number'])
            );
        } catch (\Throwable $e) {
            log_message('debug', 'PiiGuard phrase load skipped: ' . $e->getMessage());
        }

        $phrases = self::uniqueSorted($phrases);
        cache()->save('pii_phrases_v1', $phrases, 120);

        return self::withSessionPhrases($phrases);
    }

    public static function redactHtml(string $html): string
    {
        $phrases = self::phrases();
        if (preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $html, $emails)) {
            $phrases = array_merge($phrases, $emails[0]);
        }
        if (preg_match_all('/(?<!\d)09\d{9}(?!\d)/', $html, $phones)) {
            $phrases = array_merge($phrases, $phones[0]);
        }
        $phrases = self::uniqueSorted($phrases);
        if ($phrases === []) {
            return $html;
        }

        $parts = preg_split(
            '/(<script\b[^>]*>.*?<\/script>|<style\b[^>]*>.*?<\/style>|<textarea\b[^>]*>.*?<\/textarea>|<option\b[^>]*>.*?<\/option>|<input\b[^>]*>)/is',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        if ($parts === false) {
            return $html;
        }

        foreach ($parts as $i => $part) {
            if ($part === '' || self::isProtectedChunk($part)) {
                continue;
            }
            $parts[$i] = self::redactChunk($part, $phrases);
        }

        return implode('', $parts);
    }

    public static function forgetPhraseCache(): void
    {
        cache()->delete('pii_phrases_v1');
    }

    private static function redactChunk(string $chunk, array $phrases): string
    {
        $chunk = preg_replace_callback(
            '/\s(data-name|data-fullname|data-email|data-contact|data-resident|title|alt|aria-label)\s*=\s*(["\'])(.*?)\2/i',
            static function (array $m) use ($phrases): string {
                foreach ($phrases as $phrase) {
                    if ($phrase !== '' && stripos($m[3], $phrase) !== false) {
                        return ' ' . $m[1] . '=' . $m[2] . $m[2];
                    }
                }

                return $m[0];
            },
            $chunk
        ) ?? $chunk;

        return preg_replace_callback(
            '/>([^<]+)</',
            static function (array $m) use ($phrases): string {
                return '>' . self::replacePhrases($m[1], $phrases) . '<';
            },
            $chunk
        ) ?? $chunk;
    }

    private static function replacePhrases(string $text, array $phrases): string
    {
        foreach ($phrases as $phrase) {
            if ($phrase === '' || stripos($text, $phrase) === false) {
                continue;
            }
            $img = self::img($phrase, self::guessVariant($text));
            $text = preg_replace('/' . preg_quote($phrase, '/') . '/iu', $img, $text) ?? $text;
        }

        return $text;
    }

    private static function guessVariant(string $text): string
    {
        $len = strlen(trim($text));

        return $len <= 22 ? 'name' : 'body';
    }

    private static function isProtectedChunk(string $chunk): bool
    {
        return preg_match('/^<(script|style|textarea|option|input)\b/i', $chunk) === 1;
    }

    private static function fromTable($db, string $table, array $columns): array
    {
        if (! $db->tableExists($table)) {
            return [];
        }

        $fields = $db->getFieldNames($table);
        $select = array_values(array_intersect($columns, $fields));
        if ($select === []) {
            return [];
        }

        $out = [];
        foreach ($db->table($table)->select($select)->get()->getResultArray() as $row) {
            $first = trim((string) ($row['first_name'] ?? ''));
            $middle = trim((string) ($row['middle_name'] ?? ''));
            $last = trim((string) ($row['last_name'] ?? ''));
            foreach (array_filter([
                trim($first . ' ' . $middle . ' ' . $last),
                trim($first . ' ' . $last),
                trim($last . ', ' . $first . ($middle !== '' ? ' ' . $middle : '')),
                trim($last . ', ' . $first),
                trim((string) ($row['full_name'] ?? '')),
                trim((string) ($row['email'] ?? '')),
                trim((string) ($row['contact_number'] ?? '')),
                trim((string) ($row['complainant_name'] ?? '')),
                trim((string) ($row['complainant_email'] ?? '')),
                trim((string) ($row['complainant_contact'] ?? '')),
                trim((string) ($row['complainant_address'] ?? '')),
                trim((string) ($row['respondent_name'] ?? '')),
                trim((string) ($row['respondent_email'] ?? '')),
                trim((string) ($row['respondent_address'] ?? '')),
                trim((string) ($row['address'] ?? '')),
                trim((string) ($row['philhealth_no'] ?? '')),
            ]) as $value) {
                if (self::isUsablePhrase($value)) {
                    $out[] = $value;
                }
            }
        }

        return $out;
    }

    private static function withSessionPhrases(array $phrases): array
    {
        $first = trim((string) (session()->get('first_name') ?? ''));
        $middle = trim((string) (session()->get('middle_name') ?? ''));
        $last = trim((string) (session()->get('last_name') ?? ''));
        foreach ([
            trim((string) (session()->get('full_name') ?? '')),
            trim($first . ' ' . $middle . ' ' . $last),
            trim($first . ' ' . $last),
            trim((string) (session()->get('email') ?? '')),
            trim((string) (session()->get('contact_number') ?? '')),
            trim((string) (session()->get('username') ?? '')),
        ] as $value) {
            if (self::isUsablePhrase($value)) {
                $phrases[] = $value;
            }
        }

        return self::uniqueSorted($phrases);
    }

    private static function isUsablePhrase(string $value): bool
    {
        $value = self::normalize($value);
        if ($value === '' || $value === '—') {
            return false;
        }
        if (str_contains($value, '@')) {
            return strlen($value) >= 6;
        }
        if (preg_match('/^09\d{9}$/', preg_replace('/\D+/', '', $value))) {
            return true;
        }
        if (preg_match('/\d/', $value) && strlen($value) >= 8) {
            return true;
        }

        $words = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($words) && count($words) >= 2 && strlen($value) >= 7;
    }

    private static function uniqueSorted(array $phrases): array
    {
        $unique = [];
        foreach ($phrases as $phrase) {
            $phrase = self::normalize((string) $phrase);
            if ($phrase !== '') {
                $unique[mb_strtolower($phrase)] = $phrase;
            }
        }
        $values = array_values($unique);
        usort($values, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        return $values;
    }

    private static function variantStyle(string $variant): array
    {
        return match ($variant) {
            'muted' => ['size' => 10.0, 'r' => 154, 'g' => 160, 'b' => 180],
            'small' => ['size' => 10.0, 'r' => 74, 'g' => 80, 'b' => 104],
            'inverse' => ['size' => 12.0, 'r' => 255, 'g' => 255, 'b' => 255],
            default => ['size' => 11.5, 'r' => 26, 'g' => 29, 'b' => 46],
        };
    }

    private static function fontPath(): ?string
    {
        foreach ([
            'C:\\Windows\\Fonts\\segoeui.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\calibri.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
        ] as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function svgPngFallback(string $text, array $style): string
    {
        $color = sprintf('#%02x%02x%02x', $style['r'], $style['g'], $style['b']);
        $safe = htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" height="18"><text x="0" y="13" font-size="12" font-family="Segoe UI, Arial, sans-serif" fill="' . $color . '">' . $safe . '</text></svg>';

        return $svg;
    }

    private static function secret(): string
    {
        $key = (string) (config('Encryption')->key ?? '');
        if (str_starts_with($key, 'hex2bin:')) {
            $bin = hex2bin(substr($key, 8));
            if (is_string($bin) && $bin !== '') {
                return $bin;
            }
        }
        if ($key !== '') {
            return $key;
        }

        return hash('sha256', APPPATH . 'PiiGuard.v1', true);
    }

    private static function cacheKey(string $token, int $userId): string
    {
        return 'pii_token_' . $userId . '_' . $token;
    }

    private static function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }
}
