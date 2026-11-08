<?php

namespace App\Rules;

use App\Support\AcademicYear as Years;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Academic years are custom, so almost anything is fine: 2025, 25-26,
 * 2025/2026, "Summer 2026". Only text that is too long or holds control
 * characters is refused.
 *
 *     'academic_year' => ['nullable', new \App\Rules\AcademicYear],
 *
 * Save with App\Support\AcademicYear::store(), which tidies standard years
 * and keeps custom labels as typed.
 */
class AcademicYear implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return; // use "required" when it must be filled in
        }

        $text = (string) $value;

        if (mb_strlen(trim($text)) > Years::MAX_LENGTH) {
            $fail('The academic year may not be longer than '.Years::MAX_LENGTH.' characters.');
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $text)) {
            $fail('The academic year contains characters that are not allowed.');
        }
    }
}
