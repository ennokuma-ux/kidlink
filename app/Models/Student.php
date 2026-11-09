<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The only sections a child can be enrolled in. Validation and every
     * form read from here so the list cannot drift between screens.
     */
    public const SECTIONS = [
        '1-3 years old',
        '4-5 years old',
    ];

    /**
     * One colour per section, used everywhere a section is visualised so the
     * same group is never two different colours between screens. Taken from the
     * existing analytics palette.
     */
    public const SECTION_COLORS = [
        '1-3 years old' => '#1982c4',
        '4-5 years old' => '#ff8555',
    ];

    /**
     * Sections can be null in the database (legacy rows, and students created
     * before a section was required), so anything unrecognised shares one
     * neutral colour rather than being painted as a real section.
     */
    public const UNASSIGNED_SECTION = 'Unassigned';

    public const UNASSIGNED_COLOR = '#8b949d';

    /**
     * The handedness a child account can be recorded with. "Not yet
     * established" is the neutral choice for toddlers whose dominant hand is
     * still undecided, and the default when no pick is made. Validation and
     * every form read from here so the list cannot drift between screens.
     */
    public const HANDS = [
        'Right',
        'Left',
        'Both',
        'Not yet established',
    ];

    /**
     * The translated label for each stored handedness value.
     */
    public static function handLabels(): array
    {
        return [
            'Right' => __('student.hand_right'),
            'Left' => __('student.hand_left'),
            'Both' => __('student.hand_both'),
            'Not yet established' => __('student.hand_not_established'),
        ];
    }

    /**
     * The display label for this student's handedness. A value outside the
     * list renders as itself rather than blank, and an empty one falls back
     * to the neutral label so the column never shows nothing at all.
     */
    public function handLabel(): string
    {
        $value = (string) $this->dominant_hand;

        return self::handLabels()[$value] ?? ($value ?: __('student.hand_not_established'));
    }

    /**
     * The display label for a stored section, or the neutral "Unassigned" one.
     */
    public static function sectionLabel(?string $section): string
    {
        return $section ?: self::UNASSIGNED_SECTION;
    }

    /**
     * The colour for a stored section, falling back to the neutral one so an
     * unexpected value can never crash a chart or render as no colour at all.
     */
    public static function colorForSection(?string $section): string
    {
        return self::SECTION_COLORS[$section] ?? self::UNASSIGNED_COLOR;
    }

    /**
     * A date of birth that keeps a child's age inside the range their section
     * is named for, so an assigned birthday can never contradict the section.
     * The middle of the range is used: "1-3 years old" yields a 2-year-old and
     * "4-5 years old" a 4-year-old. Deriving it from the label means a new
     * entry in SECTIONS needs no change here.
     */
    public static function dateOfBirthForSection(?string $section): ?Carbon
    {
        if (! $section || ! preg_match('/(\d+)\s*(?:-\s*(\d+)\s*)?years?\s*old/i', $section, $matches)) {
            return null;
        }

        $from = (int) $matches[1];
        $to = ($matches[2] ?? '') === '' ? $from : (int) $matches[2];

        return now()->subYears($from)->subMonths((int) round(($to - $from) * 6));
    }

    /**
     * Month the school year starts in. A child enrolled from June onwards
     * belongs to the year that begins that calendar year.
     */
    public const ACADEMIC_YEAR_START_MONTH = 6;

    /**
     * The academic year a date falls in, as "2026-2027".
     */
    public static function academicYearFor(?Carbon $date = null): string
    {
        $date ??= now();
        $start = $date->month >= self::ACADEMIC_YEAR_START_MONTH ? $date->year : $date->year - 1;

        return $start.'-'.($start + 1);
    }

    public static function currentAcademicYear(): string
    {
        return self::academicYearFor(now());
    }

    /**
     * Whether a value is a standard academic year (two consecutive years).
     * Academic years may also be custom labels such as "Summer 2026"; those
     * are valid to save but are not "standard".
     */
    public static function isValidAcademicYear(?string $value): bool
    {
        return \App\Support\AcademicYear::normalize($value) !== null;
    }

    /**
     * Years offered in pickers: the previous, current and next school year,
     * plus any year already stored (so an old record is never unselectable),
     * newest first.
     *
     * @param  iterable<string|null>  $existing
     * @return list<string>
     */
    public static function academicYearOptions(iterable $existing = []): array
    {
        $startYear = (int) substr(self::currentAcademicYear(), 0, 4);

        $years = collect([$startYear + 1, $startYear, $startYear - 1])
            ->map(fn ($y) => $y.'-'.($y + 1))
            ->merge(collect($existing)->map(fn ($y) => trim((string) $y))->filter())
            ->unique()
            ->sortDesc()
            ->values();

        // Standard years first (newest first), then custom labels A-Z.
        $standard = $years->filter(fn ($y) => self::isValidAcademicYear($y))->values();
        $custom = $years->reject(fn ($y) => self::isValidAcademicYear($y))->sort()->values();

        return $standard->merge($custom)->all();
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'sex',
        'date_of_birth',
        'address',
        'dominant_hand',
        'parent_id',
        'teacher_id',
        'user_id',
        'enrolled_date',
        'is_active',
        'father_name',
        'father_contact',
        'mother_name',
        'mother_contact',
        'siblings_count',
        'class_name',
        'academic_year',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrolled_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function parents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withTimestamps();
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function activityAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityAttendance::class);
    }

    public function eccdRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EccdRecord::class);
    }

    public function gameScores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth?->age ?? 0;
    }

    /**
     * "Below 1" for babies under a year old (their age is 0), otherwise "N yrs".
     * Used by the age filter, the accounts table and the creation forms so a
     * zero is never shown as "0 yrs".
     */
    public static function ageLabel(int $age): string
    {
        return $age < 1 ? __('Below 1') : $age.' '.__('teacher.yrs');
    }

    /**
     * Ages offered in the age filter: below 1 through 5 always, so a filter
     * never silently lacks an age nobody happens to be enrolled at yet, plus
     * any older age already on record.
     *
     * @param  iterable<int>  $existing
     * @return list<int>
     */
    public static function ageFilterOptions(iterable $existing = []): array
    {
        return collect(range(0, 5))->merge($existing)->map(fn ($a) => (int) $a)->unique()->sort()->values()->all();
    }

    public function getNameAttribute(): string
    {
        return $this->full_name ?? "{$this->first_name} {$this->last_name}";
    }
}
