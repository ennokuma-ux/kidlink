<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Academic years, typed however people write them.
 *
 * Anything with the right numbers is accepted and stored as one shape,
 * "2025-2026", so filters, sorting and "same year" checks never see two
 * spellings of the same year:
 *
 *   2025            -> 2025-2026   (a single year is the year the term starts)
 *   2025-2026       -> 2025-2026
 *   2025/26         -> 2025-2026
 *   25-26, 25/26    -> 2025-2026
 *   2025 - 2026     -> 2025-2026   (spaces, dashes, slashes, "to" all work)
 *   SY 2025-2026    -> 2025-2026   (letters and dots such as "A.Y." are ignored)
 *   20252026        -> 2025-2026
 *
 * Academic years are custom: anything that is not a recognisable year ("Summer
 * 2026", "SY 2025-2026 Batch B", "2025-2027") is kept exactly as typed, trimmed.
 * Only recognisable years (two consecutive years between MIN_YEAR and MAX_YEAR)
 * are tidied to "2025-2026". Use store() when saving; normalize() answers only
 * "is this a standard year, and which one?".
 */
class AcademicYear
{
    public const MIN_YEAR = 1990;

    public const MAX_YEAR = 2100;

    /** The school year begins in June, as in the Philippines. */
    public const START_MONTH = 6;

    /** Longest custom label that is stored (the column is 60 characters). */
    public const MAX_LENGTH = 60;

    /**
     * What to save: the tidy "2025-2026" for a standard year, otherwise the
     * custom label exactly as typed (whitespace collapsed). Null when blank.
     */
    public static function store(?string $input): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $input));

        if ($text === '') {
            return null;
        }

        return self::normalize($text) ?? mb_substr($text, 0, self::MAX_LENGTH);
    }

    /** True for a label that is not a standard year, e.g. "Summer 2026". */
    public static function isCustom(?string $stored): bool
    {
        return trim((string) $stored) !== '' && self::parse($stored) === null;
    }

    /**
     * Why the last parse failed, when it did: 'empty', 'format', 'range' or 'span'.
     */
    public static function problem(?string $input): ?string
    {
        return self::resolve($input)['problem'];
    }

    /**
     * The canonical "2025-2026", or null when the input is blank or unusable.
     */
    public static function normalize(?string $input): ?string
    {
        $years = self::parse($input);

        return $years ? $years['start'].'-'.$years['end'] : null;
    }

    /**
     * @return array{start: int, end: int}|null
     */
    public static function parse(?string $input): ?array
    {
        return self::resolve($input)['years'];
    }

    /** For display: "2025–2026" with an en dash, a custom label as saved, or "—". */
    public static function label(?string $stored): string
    {
        $years = self::parse($stored);

        if ($years) {
            return $years['start'].'–'.$years['end'];
        }

        return trim((string) $stored) !== '' ? trim((string) $stored) : '—';
    }

    /** The school year a date falls in: 2025-09-01 and 2026-03-01 are both 2025-2026. */
    public static function forDate(Carbon|string|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }

        $start = $date->month >= self::START_MONTH ? $date->year : $date->year - 1;

        return $start.'-'.($start + 1);
    }

    public static function current(?Carbon $now = null): string
    {
        return self::forDate($now ?? Carbon::now());
    }

    /**
     * Suggestions for a datalist or filter: a few past years, this one, a few
     * ahead (newest first), then every custom label already saved on a student.
     * Suggestions only - anything can be typed.
     *
     * @return list<string>
     */
    public static function options(int $before = 4, int $after = 1, ?Carbon $now = null): array
    {
        $start = (int) substr(self::current($now), 0, 4);
        $options = [];

        for ($year = $start + $after; $year >= $start - $before; $year--) {
            $options[] = $year.'-'.($year + 1);
        }

        try {
            $saved = \Illuminate\Support\Facades\DB::table('students')
                ->whereNotNull('academic_year')
                ->distinct()
                ->orderBy('academic_year')
                ->limit(100)
                ->pluck('academic_year')
                ->all();
        } catch (\Throwable) {
            $saved = []; // column not migrated yet, or no database: generated list only
        }

        return array_values(array_unique(array_merge($options, $saved)));
    }

    /**
     * @return array{years: array{start: int, end: int}|null, problem: string|null}
     */
    private static function resolve(?string $input): array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return ['years' => null, 'problem' => 'empty'];
        }

        // Full-width digits and unusual dashes arrive from phones and pasted text.
        $input = preg_replace('/\s+/u', ' ', $input);
        $input = strtr($input, ['０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9']);

        // Only text that is purely a year counts as one, optionally led by "SY",
        // "A.Y." or "school year". Anything with other words ("Summer 2026",
        // "SY 2025-2026 Batch B") is a custom label and is left alone.
        $bare = preg_replace('/^(?:a\.?\s*y\.?|s\.?\s*y\.?|academic\s+year|school\s+year)\s*:?\s*/iu', '', $input);

        if (preg_match('/^(\d{8}|\d{6})$/', $bare, $m)) {
            // "20252026" and "202526" have no separator to split on.
            $groups = [substr($m[1], 0, 4), substr($m[1], 4)];
        } elseif (preg_match('/^(\d{1,4})(?:\s*(?:-|–|—|\/|to)\s*(\d{1,4}))?$/iu', $bare, $m)) {
            $groups = array_values(array_filter([$m[1], $m[2] ?? null], fn ($g) => $g !== null && $g !== ''));
        } else {
            return ['years' => null, 'problem' => 'format'];
        }

        if (count($groups) < 1 || count($groups) > 2) {
            return ['years' => null, 'problem' => 'format'];
        }

        $start = self::fullYear($groups[0], null);
        if ($start === null) {
            return ['years' => null, 'problem' => 'format'];
        }

        if (count($groups) === 1) {
            $end = $start + 1;
        } else {
            $end = self::fullYear($groups[1], $start);
            if ($end === null) {
                return ['years' => null, 'problem' => 'format'];
            }
        }

        if ($start < self::MIN_YEAR || $end > self::MAX_YEAR) {
            return ['years' => null, 'problem' => 'range'];
        }

        if ($end !== $start + 1) {
            return ['years' => null, 'problem' => 'span'];
        }

        return ['years' => ['start' => $start, 'end' => $end], 'problem' => null];
    }

    /**
     * Completes a short year. "25" is 2025; for the second year the digits are
     * read relative to the first, so "2025-26" is 2026 and "1999-00" is 2000.
     */
    private static function fullYear(string $digits, ?int $after): ?int
    {
        $length = strlen($digits);
        $value = (int) $digits;

        if ($length === 4) {
            return $value;
        }

        if ($after === null) {
            return $length === 2 ? 2000 + $value : null;
        }

        if ($length === 2) {
            $year = intdiv($after, 100) * 100 + $value;

            return $year <= $after ? $year + 100 : $year;
        }

        if ($length === 1) {
            $year = intdiv($after, 10) * 10 + $value;

            return $year <= $after ? $year + 10 : $year;
        }

        return null;
    }
}
