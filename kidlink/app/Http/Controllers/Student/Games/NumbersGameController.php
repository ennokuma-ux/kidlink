<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NumbersGameController extends Controller
{
    public $totalLevels = 8;

    /** The biggest number this game ever asks for. */
    public const MAX_NUMBER = 10;

    public function stageSelect()
    {
        $user = Auth::user();
        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        $currentLevel = 1;
        $totalScore = 0;

        $highestCompleted = GameScore::where('student_id', $student->id)
            ->where('game_type', 'numbers')
            ->where('score', '>=', 1)
            ->max('level');

        $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

        $scores = GameScore::where('student_id', $student->id)
            ->where('game_type', 'numbers')
            ->get();
        $totalScore = $scores->sum('score');

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.numbers.stage-select', [
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
            ->where('game_type', 'numbers')
            ->where('score', '>=', 1)
            ->max('level') ?? 0;

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.numbers.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.numbers.play', [
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

        // The level total comes from the server, so a posted score is capped
        // against the real number of questions rather than anything the browser
        // claims the level contained.
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
            gameType: 'numbers',
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

    /** How many questions a level contains. The server owns this. */
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
        // Positions and the road are shared by every game map (see StageMap).
        return \App\Support\StageMap::levels('numbers', $currentLevel, $this->totalLevels);
    }

    protected function getLevelData(int $level): array
    {
        // The counting range grows with every level and is the full 1-10 by level 7.
        $levelData = [
            1 => ['title' => 'Count 1-4', 'description' => 'Count apples from 1 to 4!'],
            2 => ['title' => 'Count 1-5', 'description' => 'One more to count: 5!'],
            3 => ['title' => 'Count 1-6', 'description' => 'Count all the way to 6!'],
            4 => ['title' => 'Count 1-7', 'description' => 'Now up to 7!'],
            5 => ['title' => 'Count 1-8', 'description' => 'Count fruits up to 8!'],
            6 => ['title' => 'Count 1-9', 'description' => 'Almost there: count to 9!'],
            7 => ['title' => 'Count 1-10', 'description' => 'Count all the way to 10!'],
            8 => ['title' => 'Number Master', 'description' => 'The ultimate counting challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'];

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    protected function getGameData(int $level): array
    {
        // Numbers never go past 10. The range starts at 1-4 and grows by one each
        // level (and by two a little later), so level 7 already has the full 1-10.
        $maxByLevel = [1 => 4, 2 => 5, 3 => 6, 4 => 7, 5 => 8, 6 => 9];
        $max = min($maxByLevel[$level] ?? self::MAX_NUMBER, self::MAX_NUMBER);
        $pool = range(1, $max);
        // The number this level introduced; it comes up more often than the rest.
        $newest = ($level >= 2 && $level <= 7) ? $max : null;

        $objects = [
            ['icon' => asset('assets/icons/apple.svg'), 'name' => 'apples'],
            ['icon' => asset('assets/icons/orange.svg'), 'name' => 'oranges'],
            ['icon' => asset('assets/icons/lemon.svg'), 'name' => 'lemons'],
            ['icon' => asset('assets/icons/strawberry.svg'), 'name' => 'strawberries'],
            ['icon' => asset('assets/icons/grapes.svg'), 'name' => 'grapes'],
            ['icon' => asset('assets/icons/star.svg'), 'name' => 'stars'],
            ['icon' => asset('assets/icons/flower.svg'), 'name' => 'flowers'],
            ['icon' => asset('assets/icons/color-palette.svg'), 'name' => 'circles'],
            ['icon' => asset('assets/icons/paw.svg'), 'name' => 'fish'],
            ['icon' => asset('assets/icons/butterfly.svg'), 'name' => 'butterflies'],
        ];

        $rounds = $this->roundsForLevel($level);
        $allRounds = [];

        for ($r = 0; $r < $rounds; $r++) {
            $target = ($newest !== null && random_int(0, 1) === 1)
                ? $newest
                : $pool[array_rand($pool)];
            $obj = $objects[array_rand($objects)];

            $distractors = array_values(array_diff($pool, [$target]));
            shuffle($distractors);
            $optCount = min(4 + intdiv($level - 1, 2), count($distractors) + 1);

            $options = [$target];
            for ($i = 0; $i < $optCount - 1 && $i < count($distractors); $i++) {
                $options[] = $distractors[$i];
            }
            shuffle($options);

            $allRounds[] = [
                'target' => $target,
                'icon' => $obj['icon'],
                'name' => $obj['name'],
                'options' => $options,
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}
