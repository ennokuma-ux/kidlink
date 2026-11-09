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
        return \App\Support\StageMap::levels('color-vision', $currentLevel, $this->totalLevels);
    }

    /**
     * Color Check is a screening game, not a lesson. It plays exactly like the
     * Colors game (tap every circle that matches the square), but each level is
     * built around colors that look the same to one kind of color vision
     * difficulty and clearly different to everyone else. A child who taps the
     * wrong circle on a level is telling us something about that level's axis,
     * so the level numbers below double as the screening map (AXIS_BY_LEVEL).
     *
     * It is a quick check, never a diagnosis: a grown-up should follow up with
     * an eye-care professional for a proper test.
     */
    public const AXIS_BY_LEVEL = [
        2 => 'red-green',
        3 => 'red-green',
        4 => 'red-green',
        5 => 'red-green',
        6 => 'red-green',
        7 => 'blue-yellow',
    ];

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Warm-up', 'description' => 'Easy colors first - tap the circles that match the square!'],
            2 => ['title' => 'Red and Green', 'description' => 'Look closely and tap every circle that matches!'],
            3 => ['title' => 'Orange and Green', 'description' => 'Look closely and tap every circle that matches!'],
            4 => ['title' => 'Pink and Grey', 'description' => 'Look closely and tap every circle that matches!'],
            5 => ['title' => 'Blue and Purple', 'description' => 'Look closely and tap every circle that matches!'],
            6 => ['title' => 'Teal and Green', 'description' => 'Look closely and tap every circle that matches!'],
            7 => ['title' => 'Light and Bright', 'description' => 'Look closely and tap every circle that matches!'],
            8 => ['title' => 'Color Check Finale', 'description' => 'A mix of everything - you are almost done!'],
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

    /** How many circles the child has to find in each round. */
    protected function matchesForLevel(int $level): int
    {
        return 1 + intdiv($level - 1, 3);
    }

    protected function circleCountForLevel(int $level): int
    {
        return match (true) {
            $level <= 1 => 4,
            $level <= 3 => 5,
            $level <= 7 => 6,
            default => 8,
        };
    }

    /**
     * Color families. Within a family every color has the same lightness, so
     * the only way to tell them apart is the hue. Each pair is clearly different
     * for typical color vision but falls close together when simulated for the
     * color vision difficulty named in the comment. 'control' colors are
     * different for everyone, so the game does not feel like every circle is a
     * trick.
     *
     * @return array<string, array{colors: array<string, string>, control?: array<string, string>}>
     */
    protected function families(): array
    {
        return [
            // Everyone should get these right: it shows the child understood the game.
            'warmup' => ['colors' => ['red' => '#CE3E33', 'blue' => '#0069C7', 'yellow' => '#F2D73C']],
            // Red / green / olive: the classic red-green confusion.
            'red-green' => ['colors' => ['red' => '#CF5546', 'green' => '#448B3D', 'olive' => '#8B7C35'], 'control' => ['blue' => '#0069C7']],
            // Orange / green / yellow-green.
            'orange-green' => ['colors' => ['orange' => '#E1963C', 'lime' => '#7CB556', 'moss' => '#A9AB39'], 'control' => ['purple' => '#6C56A2']],
            // Pink / grey / lilac.
            'pink-grey' => ['colors' => ['pink' => '#D38CA1', 'grey' => '#9EA1A4', 'lilac' => '#AA97CF'], 'control' => ['yellow' => '#F2D73C']],
            // Blue / violet / magenta.
            'blue-purple' => ['colors' => ['blue' => '#0068A8', 'violet' => '#6C56A2', 'magenta' => '#9F3E84'], 'control' => ['yellow' => '#F2D73C']],
            // Teal / sage / grey.
            'teal-grey' => ['colors' => ['teal' => '#2F9A92', 'sage' => '#519A67', 'stone' => '#878C8C'], 'control' => ['red' => '#CE3E33']],
            // Blue-yellow axis (the rarer kind): butter / pale pink / light grey.
            'light' => ['colors' => ['butter' => '#E6DA6D', 'blush' => '#FBCBD3', 'mist' => '#D8D7D3'], 'control' => ['blue' => '#0069C7']],
            // Blue-yellow axis: steel blue / deep teal / slate.
            'blue-teal' => ['colors' => ['steel' => '#3176B2', 'deepteal' => '#008083', 'slate' => '#6F7275'], 'control' => ['red' => '#CE3E33']],
        ];
    }

    /** The families a level draws its rounds from. */
    protected function familiesForLevel(int $level): array
    {
        return match ($level) {
            1 => ['warmup'],
            2 => ['red-green'],
            3 => ['orange-green'],
            4 => ['pink-grey'],
            5 => ['blue-purple'],
            6 => ['teal-grey'],
            7 => ['light', 'blue-teal'],
            default => ['red-green', 'orange-green', 'pink-grey', 'blue-purple', 'teal-grey', 'light', 'blue-teal'],
        };
    }

    protected function getGameData(int $level): array
    {
        $families = $this->families();
        $familyKeys = $this->familiesForLevel($level);

        $rounds = $this->roundsForLevel($level);
        $circleCount = $this->circleCountForLevel($level);
        $matches = $this->matchesForLevel($level);

        // Walk the level's families in a shuffled order so each shows up before
        // any repeats (matters on the final level, which mixes them all).
        $order = [];
        while (count($order) < $rounds) {
            $batch = $familyKeys;
            shuffle($batch);
            $order = array_merge($order, $batch);
        }

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            $family = $families[$order[$r]];
            $colors = $family['colors'];
            $control = $family['control'] ?? [];
            $palette = $colors + $control;

            // The target is always one of the look-alike colors, never the control.
            $targetKey = array_rand($colors);
            $lookAlikes = array_values(array_diff(array_keys($colors), [$targetKey]));
            $wrongCount = $circleCount - $matches;

            // Wrong circles: every look-alike at least once, then filler cycled
            // from the look-alikes and the control so no color hogs the board.
            $wrongKeys = $lookAlikes;
            $filler = array_merge($lookAlikes, array_keys($control));
            shuffle($filler);
            for ($i = 0; count($wrongKeys) < $wrongCount; $i++) {
                $wrongKeys[] = $filler[$i % count($filler)];
            }
            $wrongKeys = array_slice($wrongKeys, 0, $wrongCount);

            $circleKeys = array_merge(array_fill(0, $matches, $targetKey), $wrongKeys);
            shuffle($circleKeys);

            $circles = [];
            foreach ($circleKeys as $key) {
                $circles[] = [
                    'type' => $key,
                    // No color names anywhere: naming a color would let a child
                    // answer from the word instead of the hue, which is exactly
                    // what the check is measuring.
                    'name' => '',
                    'hex' => $palette[$key],
                    'marker' => '',
                    'isMatch' => $key === $targetKey,
                ];
            }

            $allRounds[] = [
                'target' => $targetKey,
                'targetName' => '',
                'targetHex' => $palette[$targetKey],
                'targetMarker' => '',
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
