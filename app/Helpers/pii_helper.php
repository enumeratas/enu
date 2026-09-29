<?php

use App\Libraries\PiiGuard;

if (! function_exists('pii')) {
    function pii(?string $text, string $variant = 'body'): string
    {
        return PiiGuard::img($text, $variant);
    }
}

if (! function_exists('pii_url')) {
    function pii_url(?string $text, string $variant = 'body'): string
    {
        $text = trim((string) $text);

        return $text === '' ? '' : PiiGuard::url($text, $variant);
    }
}
