<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsMissingGameController extends Controller
{
    public $totalLevels = 8;

    public $gameType = 'whats-missing';

    public function stageSelect()
    {
        $user = Auth::user();
        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        $highestCompleted = GameScore::where('student_id', $student->id)
            ->where('game_type', $this->gameType)
            ->where('score', '>=', 1)
            ->max('level');

        $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

        $scores = GameScore::where('student_id', $student->id)
            ->where('game_type', $this->gameType)
            ->get();
        $totalScore = $scores->sum('score');

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.whats-missing.stage-select', [
            'student' => $student,
            'currentLevel' => $currentLevel,
            'totalScore' => $totalScore,
            'levels' => $levels,
            'totalLevels' => $this->totalLevels,
        ]);
    }

    public function play($level)
    {
        $level = (int) $level;

        if ($level < 1 || $level > $this->totalLevels) {
            abort(404);
        }

        $user = Auth::user();
        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        $highestCompleted = GameScore::where('student_id', $student->id)
            ->where('game_type', $this->gameType)
            ->where('score', '>=', 1)
            ->max('level') ?? 0;

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.whats-missing.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.whats-missing.play', [
            'level' => $level,
            'levelData' => $levelData,
            'totalLevels' => $this->totalLevels,
            'rounds' => $gameData['rounds'],
            'totalRounds' => $gameData['totalRounds'],
            'score' => 0,
            'mistakes' => 0,
        ]);
    }

    public function submit(Request $request, $level)
    {
        $level = (int) $level;

        if ($level < 1 || $level > $this->totalLevels) {
            return response()->json(['error' => 'Unknown level'], 404);
        }

        $expectedTotal = $this->totalForLevel($level);

        $request->validate([
            'score' => 'required|integer|min:0|max:'.$expectedTotal,
            'time_taken' => 'nullable|integer|min:0',
            'mistakes' => 'nullable|integer|min:0',
        ]);

        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $result = $this->recorder()->record(
            student: $student,
            gameType: $this->gameType,
            level: $level,
            expectedTotal: $expectedTotal,
            score: (int) $request->score,
            mistakes: (int) ($request->mistakes ?? 0),
            timeTaken: $request->time_taken !== null ? (int) $request->time_taken : null,
        );

        return response()->json([
            'success' => true,
            'completed' => $result['completed'],
            'recorded' => $result['recorded'],
            'improved' => $result['improved'],
            'score' => $result['score'],
            'total' => $result['total'],
            'mistakes' => $result['mistakes'],
            'percentage' => $result['percentage'],
            'points_earned' => $result['points_earned'],
            'next_level_unlocked' => $result['completed'] && $level < $this->totalLevels,
        ]);
    }

    /** How many sequences the child has to solve across a level. The server owns this. */
    public function totalForLevel(int $level): int
    {
        return $this->getLevelData($level)['questions'];
    }

    private function recorder(): LevelCompletionRecorder
    {
        return app(LevelCompletionRecorder::class);
    }

    protected function getLevelsData(int $currentLevel): array
    {
        $positions = [
            1 => ['left' => '9.19%', 'top' => '44.89%'],
            2 => ['left' => '14.74%', 'top' => '22.36%'],
            3 => ['left' => '28.98%', 'top' => '18.39%'],
            4 => ['left' => '37.46%', 'top' => '38.34%'],
            5 => ['left' => '38.03%', 'top' => '63.16%'],
            6 => ['left' => '51.87%', 'top' => '61.66%'],
            7 => ['left' => '52.52%', 'top' => '37.22%'],
            8 => ['left' => '59.27%', 'top' => '19.2%'],
        ];

        $levels = [];
        for ($i = 1; $i <= $this->totalLevels; $i++) {
            $pos = $positions[$i] ?? ['left' => '50%', 'top' => '50%'];
            $levels[] = [
                'number' => $i,
                'unlocked' => $i <= $currentLevel,
                'completed' => $i < $currentLevel,
                'left' => $pos['left'],
                'top' => $pos['top'],
                'route' => route('student.games.whats-missing.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Number Detective', 'description' => 'One number is hiding - find it!'],
            2 => ['title' => 'Number Detective', 'description' => 'A longer sequence - spot the gap!'],
            3 => ['title' => 'Number Detective Pro', 'description' => 'Bigger numbers, same trick!'],
            4 => ['title' => 'Number Detective Pro', 'description' => 'Five spots and one is missing!'],
            5 => ['title' => 'Missing Number Master', 'description' => 'Count along - the numbers get bigger!'],
            6 => ['title' => 'Missing Number Master', 'description' => 'Six numbers and one sneaked away!'],
            7 => ['title' => 'Ultimate Counter', 'description' => 'Skip counting now - count by twos!'],
            8 => ['title' => 'Ultimate Counter', 'description' => 'The ultimate missing number challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'];

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level, 2);
    }

    protected function getGameData(int $level): array
    {
        $shape = $this->sequenceShape($level);

        $rounds = $this->roundsForLevel($level);

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            $allRounds[] = $this->makeRound($shape);
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }

    /**
     * How big each level's sequences are: length of the sequence, the step
     * between numbers, the highest number allowed, and how many choices the
     * child picks from.
     */
    protected function sequenceShape(int $level): array
    {
        $shape = [
            1 => ['length' => 3, 'step' => 1, 'max' => 5,  'choices' => 3],
            2 => ['length' => 4, 'step' => 1, 'max' => 6,  'choices' => 3],
            3 => ['length' => 4, 'step' => 1, 'max' => 10, 'choices' => 3],
            4 => ['length' => 5, 'step' => 1, 'max' => 10, 'choices' => 3],
            5 => ['length' => 5, 'step' => 1, 'max' => 15, 'choices' => 4],
            6 => ['length' => 6, 'step' => 1, 'max' => 20, 'choices' => 4],
            7 => ['length' => 6, 'step' => 2, 'max' => 20, 'choices' => 4],
            8 => ['length' => 7, 'step' => 2, 'max' => 24, 'choices' => 4],
        ];

        return $shape[$level] ?? $shape[1];
    }

    /** One sequence with a hole in it, plus the choices to pick the hole from. */
    protected function makeRound(array $shape): array
    {
        $length = $shape['length'];
        $step = $shape['step'];
        $max = $shape['max'];
        $choiceCount = $shape['choices'];

        $highestStart = $max - ($length - 1) * $step;
        $start = random_int(1, max(1, $highestStart));

        $sequence = [];
        for ($i = 0; $i < $length; $i++) {
            $sequence[] = $start + $i * $step;
        }

        // Never hide the first or last number: with a single gap at either end
        // the answer is just "one more/less", which is a different skill.
        $gapIndex = random_int(1, $length - 2);
        $answer = $sequence[$gapIndex];
        $sequence[$gapIndex] = null;

        // Distractors sit near the answer but are never a number already shown.
        $shown = array_filter($sequence);
        $pool = [];
        for ($n = 1; $n <= $max + $step; $n++) {
            if ($n !== $answer && !in_array($n, $shown, true)) {
                $pool[] = $n;
            }
        }
        // Nearest numbers first, so the choice is a real guess rather than a
        // scan of the whole range; shuffle so position is not a tell.
        usort($pool, fn ($a, $b) => abs($a - $answer) <=> abs($b - $answer));
        $near = array_slice($pool, 0, max($choiceCount - 1, 4));
        shuffle($near);
        $choices = array_slice($near, 0, $choiceCount - 1);
        $choices[] = $answer;
        shuffle($choices);

        return [
            'sequence' => array_values($sequence),
            'answer' => $answer,
            'choices' => $choices,
            'start' => $start,
            'step' => $step,
        ];
    }
}
