<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\Games\AnimalsGameController;
use App\Http\Controllers\Student\Games\ColorsGameController;
use App\Http\Controllers\Student\Games\LettersGameController;
use App\Http\Controllers\Student\Games\NumbersGameController;
use App\Http\Controllers\Student\Games\ShapesGameController;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ScoreController extends Controller
{
    /**
     * Legacy entry point, kept for any client still posting to /save-score.
     *
     * It used to write a row straight from the posted numbers, which meant the
     * browser decided the total and the level was never supplied at all. It now
     * goes through the same recorder as the per-game submit endpoints, so it
     * can only record a finished stage and only once.
     */
    public function save(Request $request)
    {
        $request->validate([
            'type' => 'required|in:letters,numbers,colors,shapes,animals',
            'score' => 'required|integer|min:0',
            'level' => 'required|integer|min:1',
            'mistakes' => 'nullable|integer|min:0',
            'time_taken' => 'nullable|integer|min:0',
        ]);

        $studentId = session('student_child_id');
        if (!$studentId) {
            $studentId = Student::where('user_id', auth()->id())->value('id');
        }
        if (!$studentId) {
            return response()->json(['ok' => false, 'message' => 'No student session'], 400);
        }

        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['ok' => false, 'message' => 'No student session'], 400);
        }

        $gameType = $request->type;
        $level = (int) $request->level;

        try {
            $expectedTotal = $this->totalForLevel($gameType, $level);
        } catch (InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => 'Unknown level'], 404);
        }

        try {
            $result = app(LevelCompletionRecorder::class)->record(
                student: $student,
                gameType: $gameType,
                level: $level,
                expectedTotal: $expectedTotal,
                score: (int) $request->score,
                mistakes: (int) ($request->mistakes ?? 0),
                timeTaken: $request->time_taken !== null ? (int) $request->time_taken : null,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'completed' => $result['completed'],
            'recorded' => $result['recorded'],
            'score' => $result['score'],
            'total' => $result['total'],
            'mistakes' => $result['mistakes'],
            'percentage' => $result['percentage'],
            'points_earned' => $result['points_earned'],
        ]);
    }

    private function totalForLevel(string $gameType, int $level): int
    {
        $controllers = [
            'letters' => LettersGameController::class,
            'numbers' => NumbersGameController::class,
            'colors' => ColorsGameController::class,
            'shapes' => ShapesGameController::class,
            'animals' => AnimalsGameController::class,
        ];

        if (! isset($controllers[$gameType])) {
            throw new InvalidArgumentException("Unknown game {$gameType}.");
        }

        $controller = app($controllers[$gameType]);

        if ($level < 1 || $level > $controller->totalLevels) {
            throw new InvalidArgumentException("Unknown level {$level} for {$gameType}.");
        }

        return $controller->totalForLevel($level);
    }
}