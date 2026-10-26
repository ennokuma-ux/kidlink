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
                'route' => route('student.games.numbers.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Count 1-5', 'description' => 'Count apples from 1 to 5!'],
            2 => ['title' => 'Count 1-5', 'description' => 'Master counting to 5!'],
            3 => ['title' => 'Count 1-10', 'description' => 'Count fruits from 1 to 10!'],
            4 => ['title' => 'Count 1-10', 'description' => 'Count even faster!'],
            5 => ['title' => 'Count 11-20', 'description' => 'Big numbers incoming!'],
            6 => ['title' => 'Count 11-20', 'description' => 'Count up to 20!'],
            7 => ['title' => 'Number Expert', 'description' => 'Show your counting skills!'],
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
        $pool = match(true) {
            $level <= 2 => range(1, 5),
            $level <= 4 => range(1, 10),
            default => range(1, 20),
        };

        $objects = [
            ['emoji' => '🍎', 'name' => 'apples'],
            ['emoji' => '🍊', 'name' => 'oranges'],
            ['emoji' => '🍋', 'name' => 'lemons'],
            ['emoji' => '🍓', 'name' => 'strawberries'],
            ['emoji' => '🍇', 'name' => 'grapes'],
            ['emoji' => '⭐', 'name' => 'stars'],
            ['emoji' => '🌸', 'name' => 'flowers'],
            ['emoji' => '🎨', 'name' => 'circles'],
            ['emoji' => '🐟', 'name' => 'fish'],
            ['emoji' => '🦋', 'name' => 'butterflies'],
        ];

        $rounds = $this->roundsForLevel($level);
        $allRounds = [];

        for ($r = 0; $r < $rounds; $r++) {
            $target = $pool[array_rand($pool)];
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
                'emoji' => $obj['emoji'],
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
