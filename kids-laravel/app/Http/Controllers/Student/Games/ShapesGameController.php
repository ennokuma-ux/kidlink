<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShapesGameController extends Controller
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
                ->where('game_type', 'shapes')
                ->where('score', '>=', 1) // at least 1 point to consider "completed"
                ->max('level');

            $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

            $scores = GameScore::where('student_id', $student->id)
                ->where('game_type', 'shapes')
                ->get();
            $totalScore = $scores->sum('score');
        }

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.shapes.stage-select', [
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

        // Check if level is unlocked
        $highestCompleted = 0;
        if ($student) {
            $highestCompleted = GameScore::where('student_id', $student->id)
                ->where('game_type', 'shapes')
                ->where('score', '>=', 1)
                ->max('level') ?? 0;
        }

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.shapes.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.shapes.play', [
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
            gameType: 'shapes',
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

    /** How many holes the child has to fill across a level. The server owns this. */
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
                'route' => route('student.games.shapes.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Shape Sorter', 'description' => 'Drag each shape into its matching hole!'],
            2 => ['title' => 'Shape Sorter', 'description' => 'Drop the shapes into the right holes!'],
            3 => ['title' => 'Shape Sorter Pro', 'description' => 'Three holes to fill - you can do it!'],
            4 => ['title' => 'Shape Sorter Pro', 'description' => 'More holes, more fun to sort!'],
            5 => ['title' => 'Shape Sorter Master', 'description' => 'Four holes - sort them all!'],
            6 => ['title' => 'Shape Sorter Master', 'description' => 'Sharpen your sorting skills!'],
            7 => ['title' => 'Ultimate Sorter', 'description' => 'Five holes to fill - great job!'],
            8 => ['title' => 'Ultimate Sorter', 'description' => 'The ultimate shape sorting challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'] * $this->holesForLevel($level);

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    protected function holesForLevel(int $level): int
    {
        return 2 + intdiv($level - 1, 2);
    }

    protected function getGameData(int $level): array
    {
        $shapes = [
            'circle' => [
                'type' => 'circle',
                'name' => 'Circle',
                'color' => '#ec4899',
                'hue' => 330,
                'image' => 'assets/images/game/shapes/shape imgs/Circle.png',
            ],
            'heart' => [
                'type' => 'heart',
                'name' => 'Heart',
                'color' => '#ef4444',
                'hue' => 0,
                'image' => 'assets/images/game/shapes/shape imgs/Heart.png',
            ],
            'star' => [
                'type' => 'star',
                'name' => 'Star',
                'color' => '#f59e0b',
                'hue' => 40,
                'image' => 'assets/images/game/shapes/shape imgs/Star.png',
            ],
            'triangle' => [
                'type' => 'triangle',
                'name' => 'Triangle',
                'color' => '#10b981',
                'hue' => 160,
                'image' => 'assets/images/game/shapes/shape imgs/Triangle.png',
            ],
            'square' => [
                'type' => 'square',
                'name' => 'Square',
                'color' => '#6366f1',
                'hue' => 230,
                'image' => 'assets/images/game/shapes/shape imgs/Square.png',
            ],
        ];

        $types = array_keys($shapes);
        $rounds = $this->roundsForLevel($level);
        $holeCount = $this->holesForLevel($level);

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            // Pick unique shapes for holes (no duplicates in same round)
            $shuffledTypes = $types;
            shuffle($shuffledTypes);
            $targetTypes = array_slice($shuffledTypes, 0, $holeCount);

            $holes = [];
            foreach ($targetTypes as $type) {
                $holes[] = [
                    'type' => $type,
                    'name' => $shapes[$type]['name'],
                    'image' => $shapes[$type]['image'],
                    'color' => $shapes[$type]['color'],
                    'hue' => $shapes[$type]['hue'],
                ];
            }

            // Tray: one draggable shape per hole (same multiset), shuffled
            $trayTypes = $targetTypes;
            shuffle($trayTypes);

            $tray = [];
            foreach ($trayTypes as $type) {
                $tray[] = [
                    'type' => $type,
                    'name' => $shapes[$type]['name'],
                    'image' => $shapes[$type]['image'],
                    'color' => $shapes[$type]['color'],
                    'hue' => $shapes[$type]['hue'],
                ];
            }

            $allRounds[] = [
                'holes' => $holes,
                'tray' => $tray,
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}