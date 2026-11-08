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
            'main' => 'assets/images/mascot/kido/kido-main.png',
            'happy' => 'assets/images/mascot/kido/kido-expression-happy.png',
            'cheer' => 'assets/images/mascot/kido/kido-expression-cheer.png',
            'question' => 'assets/images/mascot/kido/kido-expression-question.png',
            'shock' => 'assets/images/mascot/kido/kido-expression-shock.png',
        ],
        self::LINKA => [
            'main' => 'assets/images/mascot/linka/linka-main.png',
            'happy' => 'assets/images/mascot/linka/linka-expression-happy.png',
            'cheer' => 'assets/images/mascot/linka/linka-expression-cheer.png',
            'question' => 'assets/images/mascot/linka/linka-expression-question.png',
            'wave' => 'assets/images/mascot/linka/linka-expression-wave.png',
        ],
    ];

    /** Semantic game states used by the in-game buddy. */
    public const STATES = ['start', 'correct', 'wrong', 'next'];

    public static function forSex(?string $sex): string
    {
        return strtolower((string) $sex) === 'female' ? self::LINKA : self::KIDO;
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
     * Translate a semantic state (start/correct/wrong/next) into a concrete
     * expression for the given mascot, falling back gracefully.
     */
    public static function expression(string $mascot, string $state): string
    {
        $map = [
            'start' => $mascot === self::LINKA ? 'wave' : 'happy',
            'correct' => 'cheer',
            'wrong' => $mascot === self::LINKA ? 'question' : 'shock',
            'next' => 'cheer',
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
