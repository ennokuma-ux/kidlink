<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the KidLink mascots (Kido and Linka) and the expression to show
 * for a given game state. Kido is the buddy for boys, Linka for girls.
 */
class Mascot
{
    public const KIDO = 'kido';
    public const LINKA = 'linka';

    /** Concrete expression artwork available per mascot (relative to /public). */
    public const EXPRESSIONS = [
        self::KIDO => [
            'main' => 'assets/images/mascot/kido/kido-main.webp',
            'happy' => 'assets/images/mascot/kido/kido-expression-happy.webp',
            'cheer' => 'assets/images/mascot/kido/kido-expression-cheer.webp',
            'question' => 'assets/images/mascot/kido/kido-expression-question.webp',
            'shock' => 'assets/images/mascot/kido/kido-expression-shock.webp',
        ],
        self::LINKA => [
            'main' => 'assets/images/mascot/linka/linka-main.webp',
            'happy' => 'assets/images/mascot/linka/linka-expression-happy.webp',
            'cheer' => 'assets/images/mascot/linka/linka-expression-cheer.webp',
            'question' => 'assets/images/mascot/linka/linka-expression-question.webp',
            'wave' => 'assets/images/mascot/linka/linka-expression-wave.webp',
        ],
    ];

    /** Semantic game states used by the in-game buddy (hint = the 15-second clue). */
    public const STATES = ['start', 'correct', 'wrong', 'next', 'hint'];

    public static function forSex(?string $sex): string
    {
        return strtolower(trim((string) $sex)) === 'female' ? self::LINKA : self::KIDO;
    }

    /**
     * Mascot for the student currently signed in. Child mode signs in as the
     * student's own user, so this resolves the active learner's buddy.
     */
    public static function forCurrentStudent(): string
    {
        return self::forSex(self::currentStudent()?->sex);
    }

    public static function currentStudent(): ?Student
    {
        if (! Auth::check()) {
            return null;
        }

        // Parent/teacher child mode explicitly records which child is active.
        // Prefer that ID over a user_id lookup so a shared or legacy-linked
        // account can never make a girl inherit the wrong sibling's mascot.
        if (session()->boolean('student_mode') && session()->filled('student_child_id')) {
            $activeStudent = Student::find(session('student_child_id'));

            if ($activeStudent && (int) $activeStudent->user_id === (int) Auth::id()) {
                return $activeStudent;
            }
        }

        return Student::where('user_id', Auth::id())->first();
    }

    public static function exists(string $mascot): bool
    {
        return array_key_exists($mascot, self::EXPRESSIONS);
    }

    /** The expression catalog for a mascot, defaulting to Kido. */
    public static function expressions(string $mascot): array
    {
        return self::EXPRESSIONS[$mascot] ?? self::EXPRESSIONS[self::KIDO];
    }

    /**
     * Translate a semantic state (start/correct/wrong/next/hint) into a concrete
     * expression for the given mascot, falling back gracefully.
     */
    public static function expression(string $mascot, string $state): string
    {
        $map = [
            'start' => $mascot === self::LINKA ? 'wave' : 'happy',
            'correct' => 'cheer',
            'wrong' => $mascot === self::LINKA ? 'question' : 'shock',
            'next' => 'cheer',
            // The mascot looks puzzled-but-helpful while giving a clue.
            'hint' => 'question',
        ];

        $expression = $map[$state] ?? $state;
        $available = self::expressions($mascot);

        if (! array_key_exists($expression, $available)) {
            $expression = array_key_exists('happy', $available) ? 'happy' : 'main';
        }

        return $expression;
    }

    /** Public-relative path for a mascot shown in a given state. */
    public static function path(string $mascot, string $state): string
    {
        return self::expressions($mascot)[self::expression($mascot, $state)];
    }

    /** The semantic states mapped to asset paths, for the in-game buddy runtime. */
    public static function buddyStates(string $mascot): array
    {
        $states = [];
        foreach (self::STATES as $state) {
            $states[$state] = self::path($mascot, $state);
        }

        return $states;
    }
}
