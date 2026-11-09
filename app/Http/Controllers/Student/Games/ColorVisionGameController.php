<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColorVisionGameController extends Controller
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
                ->where('game_type', 'color-vision')
                ->where('score', '>=', 1)
                ->max('level');

            $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

            $scores = GameScore::where('student_id', $student->id)
                ->where('game_type', 'color-vision')
                ->get();
            $totalScore = $scores->sum('score');
        }

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.color-vision.stage-select', [
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
                ->where('game_type', 'color-vision')
                ->where('score', '>=', 1)
                ->max('level') ?? 0;
        }

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.color-vision.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.color-vision.play', [
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
            gameType: 'color-vision',
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

    /** How many plates the child has to read across a level. The server owns this. */
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
        return \App\Support\StageMap::levels('color-vision', $currentLevel, $this->totalLevels);
    }

    /**
     * Color Check is an Ishihara-style screening game. Each plate is a disc of
     * colored dots with a number hidden in it: the number is made of dots in one
     * family of colors, the background of dots in another. Dot sizes and
     * lightness are mixed inside each family, so the number can only be found by
     * telling the two color families apart, never by brightness or dot size.
     *
     * Every plate type below is built around one kind of color vision
     * difficulty (AXIS_BY_PLATE). Typical color vision reads all of them; a child
     * who has trouble with one kind of plate is telling us something about that
     * axis. The warm-up plates use colors everyone can tell apart, so a child who
     * misses those has simply not understood the game yet.
     *
     * It is a quick check, never a diagnosis: a grown-up should follow up with an
     * eye-care professional for a proper test.
     */
    public const AXIS_BY_PLATE = [
        'warmup' => 'control',
        'orange-on-green' => 'red-green',
        'green-on-orange' => 'red-green',
        'red-on-olive' => 'red-green',
        'pink-on-grey' => 'red-green',
        'blue-on-green' => 'blue-yellow',
        'violet-on-yellow' => 'blue-yellow',
    ];

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Warm-up', 'description' => 'Easy colors first - find the hidden number!'],
            2 => ['title' => 'Orange and Green', 'description' => 'Look closely - which number is hiding in the dots?'],
            3 => ['title' => 'Green and Orange', 'description' => 'Look closely - which number is hiding in the dots?'],
            4 => ['title' => 'Red and Olive', 'description' => 'Look closely - which number is hiding in the dots?'],
            5 => ['title' => 'Pink and Grey', 'description' => 'Look closely - which number is hiding in the dots?'],
            6 => ['title' => 'Blue and Green', 'description' => 'Look closely - which number is hiding in the dots?'],
            7 => ['title' => 'Violet and Yellow', 'description' => 'Look closely - which number is hiding in the dots?'],
            8 => ['title' => 'Color Check Finale', 'description' => 'A mix of everything - you are almost done!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        // One hidden number per plate, so one question per plate.
        $data['questions'] = $data['rounds'];

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    /**
     * Dot colors for each plate type. 'figure' makes the number, 'ground' makes
     * the background. Both groups mix light and dark shades on purpose.
     *
     * @return array<string, array{figure: string[], ground: string[]}>
     */
    protected function plates(): array
    {
        return [
            // Everyone can read these: deep blue number on warm sand.
            'warmup' => [
                'figure' => ['#2B5FA8', '#3A72BE', '#234E8C', '#4A82CC'],
                'ground' => ['#E3D2A8', '#EBDDB9', '#D4C195', '#F0E4C4'],
            ],
            // The classic: an orange number among greens.
            'orange-on-green' => [
                'figure' => ['#F08A4B', '#F6A35C', '#E8743B', '#F3B46F'],
                'ground' => ['#9DBD5F', '#B5CC72', '#86A94F', '#C3D68C', '#A2B85A'],
            ],
            // The same trap turned around: a green number among oranges.
            'green-on-orange' => [
                'figure' => ['#8FB45A', '#A6C66E', '#7AA14A', '#B7D083'],
                'ground' => ['#EAA05A', '#F2B980', '#DD8A45', '#F0C08A', '#E59550'],
            ],
            // A red number among olive and khaki.
            'red-on-olive' => [
                'figure' => ['#D2574A', '#C4473D', '#E0695B', '#CF6A52'],
                'ground' => ['#8A8A5A', '#9B9B6A', '#767650', '#A9A97A', '#8F8C4E'],
            ],
            // A pink number among greys.
            'pink-on-grey' => [
                'figure' => ['#E29AA6', '#D98896', '#EEB0BA', '#DE9FA0'],
                'ground' => ['#A7A7A2', '#B8B8B2', '#8F8F8A', '#C4C4BD', '#ABABA3'],
            ],
            // The rarer blue-yellow kind: blue among greens.
            'blue-on-green' => [
                'figure' => ['#4C8FD6', '#5FA0E2', '#3F7FC4', '#6AAEE8'],
                'ground' => ['#78B85A', '#8EC86E', '#63A548', '#A2D283', '#6FB452'],
            ],
            // ... and violet among yellows.
            'violet-on-yellow' => [
                'figure' => ['#8A67C4', '#9C7BD2', '#7855B3', '#A88BDA'],
                'ground' => ['#D4C24A', '#E2D160', '#C4B23A', '#ECE07F', '#D8C84E'],
            ],
        ];
    }

    /** The plate types a level draws its rounds from. */
    protected function platesForLevel(int $level): array
    {
        return match ($level) {
            1 => ['warmup'],
            2 => ['orange-on-green'],
            3 => ['green-on-orange'],
            4 => ['red-on-olive'],
            5 => ['pink-on-grey'],
            6 => ['blue-on-green'],
            7 => ['violet-on-yellow'],
            default => ['orange-on-green', 'green-on-orange', 'red-on-olive', 'pink-on-grey', 'blue-on-green', 'violet-on-yellow'],
        };
    }

    protected function getGameData(int $level): array
    {
        $plates = $this->plates();
        $types = $this->platesForLevel($level);
        $rounds = $this->roundsForLevel($level);

        // Walk the level's plate types in a shuffled order so each shows up
        // before any repeats (matters on the finale, which mixes them all).
        $order = [];
        while (count($order) < $rounds) {
            $batch = $types;
            shuffle($batch);
            $order = array_merge($order, $batch);
        }

        // The warm-up sticks to the easiest numbers.
        $pool = $level === 1 ? [1, 2, 3, 4, 5] : [1, 2, 3, 4, 5, 6, 7, 8, 9];
        shuffle($pool);

        $allRounds = [];
        $previous = null;
        for ($r = 0; $r < $rounds; $r++) {
            $type = $order[$r];

            // Never the same number twice in a row.
            $answer = null;
            foreach ($pool as $k => $candidate) {
                if ($candidate !== $previous) {
                    $answer = $candidate;
                    unset($pool[$k]);
                    break;
                }
            }
            if ($answer === null) {
                $answer = $pool ? array_shift($pool) : random_int(1, 9);
            }
            if (!$pool) {
                $pool = range(1, $level === 1 ? 5 : 9);
                shuffle($pool);
            }
            $previous = $answer;

            // Three different wrong numbers to choose from.
            $wrong = array_values(array_diff($level === 1 ? range(1, 5) : range(1, 9), [$answer]));
            shuffle($wrong);
            $options = array_merge([$answer], array_slice($wrong, 0, 3));
            shuffle($options);

            $allRounds[] = [
                'plate' => $type,
                'axis' => self::AXIS_BY_PLATE[$type],
                'figure' => $plates[$type]['figure'],
                'ground' => $plates[$type]['ground'],
                'answer' => $answer,
                'options' => $options,
                'seed' => random_int(1, 2147483646),
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}
