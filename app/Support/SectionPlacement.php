<?php

namespace App\Support;

use App\Models\Student;
use Carbon\Carbon;

/**
 * Works out which section a child belongs in from their date of birth.
 *
 * Kept out of the Student model on purpose: the model is edited by other
 * changes, and this lookup was lost whenever an older copy of it was put back.
 * It reads Student::SECTIONS, so a new section is picked up automatically as
 * long as its label carries an age range such as "1-3 years old".
 */
class SectionPlacement
{
    /**
     * The age range each section covers, read from its label
     * ("1-3 years old" => 1 to 3). Sections without an age range are skipped.
     *
     * @return array<string, array{min: int, max: int}>
     */
    public static function ranges(): array
    {
        $ranges = [];

        foreach (Student::SECTIONS as $section) {
            if (preg_match('/(\d+)\s*(?:-\s*(\d+)\s*)?years?\s*old/i', $section, $matches)) {
                $from = (int) $matches[1];
                $to = ($matches[2] ?? '') === '' ? $from : (int) $matches[2];
                $ranges[$section] = ['min' => $from, 'max' => $to];
            }
        }

        return $ranges;
    }

    /**
     * The matching section, or null when the date is missing, in the future,
     * or the age fits no section (under 1 or over 5), so nothing is guessed.
     */
    public static function forDateOfBirth(Carbon|string|null $dateOfBirth): ?string
    {
        if (! $dateOfBirth) {
            return null;
        }

        try {
            $birthday = $dateOfBirth instanceof Carbon ? $dateOfBirth : Carbon::parse($dateOfBirth);
        } catch (\Throwable) {
            return null;
        }

        if ($birthday->isFuture()) {
            return null;
        }

        $age = $birthday->age;

        foreach (self::ranges() as $section => $range) {
            if ($age >= $range['min'] && $age <= $range['max']) {
                return $section;
            }
        }

        return null;
    }
}
