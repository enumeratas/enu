<?php

namespace App\Libraries;

/**
 * Shared text and date matching for list search boxes.
 * Date text accepts a year, a month name, "Sep 2, 1980", and m/d/Y.
 */
class RecordSearch
{
    public static function term(): string
    {
        $value = $_GET['search'] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param list<string> $textColumns
     * @param list<string> $dateColumns
     */
    public static function clause(array $textColumns, array $dateColumns, string $raw): string
    {
        $like = str_replace("'", "''", \Config\Database::connect()->escapeLikeString(trim($raw)));
        $parts = [];
        foreach ($textColumns as $column) {
            $parts[] = $column . " LIKE '%{$like}%'";
        }
        foreach ($dateColumns as $column) {
            $parts[] = "DATE_FORMAT({$column}, '%Y-%m-%d') LIKE '%{$like}%'";
            $parts[] = "DATE_FORMAT({$column}, '%m/%d/%Y') LIKE '%{$like}%'";
            $parts[] = "DATE_FORMAT({$column}, '%c/%e/%Y') LIKE '%{$like}%'";
            $parsed = self::dateEqualsSql($column, $raw);
            if ($parsed !== '') {
                $parts[] = '(' . $parsed . ')';
            }
        }

        return $parts === [] ? '1=0' : '(' . implode(' OR ', $parts) . ')';
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param callable(array<string, mixed>): string $text
     * @param callable(array<string, mixed>): list<string|null> $dates
     * @return list<array<string, mixed>>
     */
    public static function filter(array $rows, string $raw, callable $text, callable $dates): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return array_values($rows);
        }

        $kept = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $dateValues = $dates($row);
            if (! is_array($dateValues)) {
                $dateValues = [];
            }
            if (self::matches($raw, (string) $text($row), $dateValues)) {
                $kept[] = $row;
            }
        }

        return $kept;
    }

    /**
     * @param list<string|null> $dates
     */
    public static function matches(string $raw, string $text, array $dates = []): bool
    {
        $needle = strtolower(trim($raw));
        if ($needle === '') {
            return true;
        }

        $haystack = strtolower($text);
        foreach ($dates as $date) {
            $haystack .= ' ' . strtolower(self::dateHaystack(is_string($date) ? $date : null));
            if (self::dateMatches($raw, is_string($date) ? $date : null)) {
                return true;
            }
        }

        return $haystack !== '' && str_contains($haystack, $needle);
    }

    public static function dateHaystack(?string $value): string
    {
        $timestamp = self::timestamp($value);
        if ($timestamp === null) {
            return '';
        }

        return implode(' ', [
            date('Y-m-d', $timestamp),
            date('m/d/Y', $timestamp),
            date('n/j/Y', $timestamp),
            date('M d, Y', $timestamp),
            date('M j, Y', $timestamp),
            date('F d, Y', $timestamp),
            date('F j, Y', $timestamp),
            date('F', $timestamp),
            date('M', $timestamp),
            date('Y', $timestamp),
        ]);
    }

    public static function dateEqualsSql(string $column, string $raw): string
    {
        $term = trim($raw);
        $months = self::months();
        $clauses = [];
        if (preg_match('/^(19|20)\d{2}$/', $term) === 1) {
            $clauses[] = 'YEAR(' . $column . ') = ' . (int) $term;
        }
        $lower = strtolower($term);
        if (isset($months[$lower])) {
            $clauses[] = 'MONTH(' . $column . ') = ' . $months[$lower];
        }
        if (preg_match('/^([a-z]+)\s+(\d{1,2})(?:,?\s*(\d{4}))?$/i', $term, $match) === 1) {
            $month = $months[strtolower($match[1])] ?? 0;
            $day = (int) $match[2];
            if ($month >= 1 && $day >= 1 && $day <= 31) {
                $sql = "MONTH({$column}) = {$month} AND DAY({$column}) = {$day}";
                if (($match[3] ?? '') !== '') {
                    $sql .= ' AND YEAR(' . $column . ') = ' . (int) $match[3];
                }
                $clauses[] = '(' . $sql . ')';
            }
        }
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $term, $match) === 1) {
            $month = (int) $match[1];
            $day = (int) $match[2];
            $year = (int) $match[3];
            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                $clauses[] = sprintf("%s = '%04d-%02d-%02d'", $column, $year, $month, $day);
            }
        }

        return implode(' OR ', $clauses);
    }

    private static function dateMatches(string $raw, ?string $value): bool
    {
        $timestamp = self::timestamp($value);
        if ($timestamp === null) {
            return false;
        }

        $month = (int) date('n', $timestamp);
        $day = (int) date('j', $timestamp);
        $year = (int) date('Y', $timestamp);
        $term = trim($raw);
        $months = self::months();
        if (preg_match('/^(19|20)\d{2}$/', $term) === 1 && (int) $term === $year) {
            return true;
        }
        $lower = strtolower($term);
        if (isset($months[$lower]) && $months[$lower] === $month) {
            return true;
        }
        if (preg_match('/^([a-z]+)\s+(\d{1,2})(?:,?\s*(\d{4}))?$/i', $term, $match) === 1) {
            $queryMonth = $months[strtolower($match[1])] ?? 0;
            $queryDay = (int) $match[2];
            $queryYear = ($match[3] ?? '') !== '' ? (int) $match[3] : $year;

            return $queryMonth === $month && $queryDay === $day && $queryYear === $year;
        }
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $term, $match) === 1) {
            return (int) $match[1] === $month
                && (int) $match[2] === $day
                && (int) $match[3] === $year;
        }

        return false;
    }

    /** @return array<string, int> */
    private static function months(): array
    {
        return [
            'jan' => 1, 'january' => 1, 'feb' => 2, 'february' => 2,
            'mar' => 3, 'march' => 3, 'apr' => 4, 'april' => 4,
            'may' => 5, 'jun' => 6, 'june' => 6, 'jul' => 7, 'july' => 7,
            'aug' => 8, 'august' => 8, 'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10, 'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12,
        ];
    }

    private static function timestamp(?string $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);

        return $timestamp === false ? null : $timestamp;
    }
}
