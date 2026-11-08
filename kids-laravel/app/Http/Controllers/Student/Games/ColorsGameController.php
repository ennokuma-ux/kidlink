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
                'route' => route('student.games.colors.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Primary Colors', 'description' => 'Tap the circles that match the square!'],
            2 => ['title' => 'Primary Colors', 'description' => 'Find every circle that matches the square!'],
            3 => ['title' => 'Secondary Colors', 'description' => 'Orange, green and purple are hiding here!'],
            4 => ['title' => 'Secondary Colors', 'description' => 'Pick out every circle of that colour!'],
            5 => ['title' => 'Rainbow Colors', 'description' => 'All the colors of the rainbow!'],
            6 => ['title' => 'Rainbow Colors', 'description' => 'Can you match them all?'],
            7 => ['title' => 'Color Expert', 'description' => 'Show your color skills!'],
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
            'red'    => ['name' => 'Red',    'hex' => '#B22222', 'marker' => 'R'],
            'orange' => ['name' => 'Orange', 'hex' => '#E69F00', 'marker' => 'O'],
            'yellow' => ['name' => 'Yellow', 'hex' => '#F0E442', 'marker' => 'Y'],
            'green'  => ['name' => 'Green',  'hex' => '#009E73', 'marker' => 'G'],
            'blue'   => ['name' => 'Blue',   'hex' => '#0072B2', 'marker' => 'B'],
            'sky'    => ['name' => 'Sky',    'hex' => '#56B4E9', 'marker' => 'S'],
            'purple' => ['name' => 'Purple', 'hex' => '#CC79A7', 'marker' => 'P'],
            'black'  => ['name' => 'Black',  'hex' => '#000000', 'marker' => 'K'],
        ];

        $pools = [
            1 => ['red', 'blue', 'yellow'],
            2 => ['red', 'blue', 'yellow'],
            3 => ['orange', 'green', 'purple'],
            4 => ['orange', 'green', 'purple'],
            5 => ['red', 'blue', 'yellow', 'orange', 'green', 'purple'],
            6 => ['red', 'blue', 'yellow', 'orange', 'green', 'purple'],
            7 => ['red', 'blue', 'yellow', 'orange', 'green', 'purple', 'sky'],
            8 => ['red', 'blue', 'yellow', 'orange', 'green', 'purple', 'sky', 'black'],
        ];

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
            $targetKey = $poolKeys[array_rand($poolKeys)];
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
                    // A letter per colour, revealed by the colour aid toggle, so a
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
