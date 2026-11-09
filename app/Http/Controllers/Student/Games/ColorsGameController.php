<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColorsGameController extends Controller
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

        if ($student) {
            $highestCompleted = GameScore::where('student_id', $student->id)
                ->where('game_type', 'colors')
                ->where('score', '>=', 1)
                ->max('level');

            $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

            $scores = GameScore::where('student_id', $student->id)
                ->where('game_type', 'colors')
                ->get();
            $totalScore = $scores->sum('score');
        }

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.colors.stage-select', [
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

        $highestCompleted = 0;
        if ($student) {
            $highestCompleted = GameScore::where('student_id', $student->id)
                ->where('game_type', 'colors')
                ->where('score', '>=', 1)
                ->max('level') ?? 0;
        }

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.colors.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.colors.play', [
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
            gameType: 'colors',
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
            'final_score' => $result['final_score'] ?? $result['percentage'],
            'points_earned' => $result['points_earned'],
            'next_level_unlocked' => $result['completed'] && $level < $this->totalLevels,
        ]);
    }

    /** How many circles the child has to match across a level. The server owns this. */
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
        return \App\Support\StageMap::levels('colors', $currentLevel, $this->totalLevels);
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Primary Colors', 'description' => 'Tap the circles that match the square!'],
            2 => ['title' => 'Primary Colors', 'description' => 'Find every circle that matches the square!'],
            3 => ['title' => 'Green Joins In', 'description' => 'A new color: green!'],
            4 => ['title' => 'Orange Joins In', 'description' => 'Orange is here too!'],
            5 => ['title' => 'Purple Joins In', 'description' => 'Say hello to purple!'],
            6 => ['title' => 'Sky Blue Joins In', 'description' => 'Sky blue and blue look a bit alike - look closely!'],
            7 => ['title' => 'All the Colors', 'description' => 'Every color is here now!'],
            8 => ['title' => 'Color Master', 'description' => 'The ultimate color challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'] * $this->matchesForLevel($level);

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    /**
     * How many circles the child has to find in each round. This is the unit of
     * scoring: a round is only cleared once every match is found.
     */
    protected function matchesForLevel(int $level): int
    {
        return 1 + intdiv($level - 1, 3);
    }

    /**
     * Circles placed around the square. Capped at the pool size so every circle
     * keeps a distinct colour until the child reaches the wider rainbow levels.
     */
    protected function circleCountForLevel(int $level, int $poolSize): int
    {
        return max(3, min(6 + intdiv($level - 1, 2), $poolSize));
    }

    protected function getGameData(int $level): array
    {
// Okabe-Ito: the standard colour-vision-deficiency-safe palette, which the
        // previous red/green/yellow set was not.
        // Brown, pink and white were dropped on purpose: orange/brown is exactly
        // the pair a red-blind child cannot separate, and a tenth colour cannot
        // be added without putting a known-ambiguous pair back into the set.
        //
        // The red is the one deliberate departure from the published palette.
        // Okabe-Ito's #D55E00 sits at hue 10, barely 16 degrees from the orange,
        // so it read as orange and sat only deltaE 11 from it under
        // deuteranopia and protanopia. #B22222 moves the hue to 0 and lifts that
        // gap to deltaE 22 while still clearing 3:1 against the white board.
        $allColors = [
            'red'    => ['name' => 'Red',    'hex' => '#B22222', 'marker' => 'Red'],
            'orange' => ['name' => 'Orange', 'hex' => '#E69F00', 'marker' => 'Orange'],
            'yellow' => ['name' => 'Yellow', 'hex' => '#F0E442', 'marker' => 'Yellow'],
            'green'  => ['name' => 'Green',  'hex' => '#009E73', 'marker' => 'Green'],
            'blue'   => ['name' => 'Blue',   'hex' => '#0072B2', 'marker' => 'Blue'],
            'sky'    => ['name' => 'Sky',    'hex' => '#56B4E9', 'marker' => 'Sky'],
            'purple' => ['name' => 'Purple', 'hex' => '#CC79A7', 'marker' => 'Purple'],
            'black'  => ['name' => 'Black',  'hex' => '#000000', 'marker' => 'Black'],
        ];

        // One new colour per level, keeping every earlier one: the pool is all
        // eight colours by level 7.
        $pools = [
            1 => ['red', 'blue', 'yellow'],
            2 => ['red', 'blue', 'yellow'],
            3 => ['red', 'blue', 'yellow', 'green'],
            4 => ['red', 'blue', 'yellow', 'green', 'orange'],
            5 => ['red', 'blue', 'yellow', 'green', 'orange', 'purple'],
            6 => ['red', 'blue', 'yellow', 'green', 'orange', 'purple', 'sky'],
            7 => ['red', 'blue', 'yellow', 'green', 'orange', 'purple', 'sky', 'black'],
            8 => ['red', 'blue', 'yellow', 'green', 'orange', 'purple', 'sky', 'black'],
        ];

        // The colour a level introduces comes up as the target more often.
        $newColor = [3 => 'green', 4 => 'orange', 5 => 'purple', 6 => 'sky', 7 => 'black'][$level] ?? null;

        $poolKeys = $pools[$level] ?? $pools[1];
        $poolColors = [];
        foreach ($poolKeys as $key) {
            $poolColors[$key] = $allColors[$key];
        }

        $rounds = $this->roundsForLevel($level);
        $circleCount = $this->circleCountForLevel($level, count($poolKeys));

        // Always leave at least one wrong circle, otherwise there is nothing to
        // tell apart and the round cannot be failed.
        $matches = min($this->matchesForLevel($level), $circleCount - 1);

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            $targetKey = ($newColor !== null && random_int(0, 1) === 1)
                ? $newColor
                : $poolKeys[array_rand($poolKeys)];
            $distractorKeys = array_values(array_diff($poolKeys, [$targetKey]));
            shuffle($distractorKeys);
            $distractorKeys = array_slice($distractorKeys, 0, $circleCount - $matches);

            // The target colour repeats: the child has to collect every circle
            // of that colour, not just the first one they spot.
            $circleKeys = array_merge(array_fill(0, $matches, $targetKey), $distractorKeys);
            shuffle($circleKeys);

            $circles = [];
            foreach ($circleKeys as $key) {
                $circles[] = [
                    'type' => $key,
                    'name' => $poolColors[$key]['name'],
                    'hex' => $poolColors[$key]['hex'],
                    // The colour's whole name, revealed by the colour aid toggle, so a
                    // child who cannot separate two hues still has another channel.
                    'marker' => $poolColors[$key]['marker'],
                    'isMatch' => $key === $targetKey,
                ];
            }

            $allRounds[] = [
                'target' => $targetKey,
                'targetName' => $poolColors[$targetKey]['name'],
                'targetHex' => $poolColors[$targetKey]['hex'],
                'targetMarker' => $poolColors[$targetKey]['marker'],
                'matchesNeeded' => $matches,
                'circles' => $circles,
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}
