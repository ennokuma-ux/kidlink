<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A shape bingo game: the child is dealt a bingo card of shapes and a caller
 * announces one shape at a time. Dabbing the matching cell covers it, and a
 * full card is a BINGO which finishes (and only then records) the level.
 *
 * Both the card and the call order come from the server, so a posted score is
 * capped against the real number of cells rather than anything the browser
 * claims the card held.
 */
class ShapesBingoGameController extends Controller
{
    public $totalLevels = 8;

    public function stageSelect()
    {
        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        $highestCompleted = GameScore::where('student_id', $student->id)
            ->where('game_type', 'shapes-bingo')
            ->where('score', '>=', 1)
            ->max('level');

        $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

        $totalScore = GameScore::where('student_id', $student->id)
            ->where('game_type', 'shapes-bingo')
            ->sum('score');

        return view('student.games.shapes-bingo.stage-select', [
            'student' => $student,
            'currentLevel' => $currentLevel,
            'totalScore' => $totalScore,
            'levels' => $this->getLevelsData($currentLevel),
            'totalLevels' => $this->totalLevels,
        ]);
    }

    public function play($level)
    {
        $level = (int) $level;

        if ($level < 1 || $level > $this->totalLevels) {
            abort(404);
        }

        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        $highestCompleted = GameScore::where('student_id', $student->id)
            ->where('game_type', 'shapes-bingo')
            ->where('score', '>=', 1)
            ->max('level') ?? 0;

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.shapes-bingo.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.shapes-bingo.play', [
            'level' => $level,
            'levelData' => $levelData,
            'totalLevels' => $this->totalLevels,
            'cells' => $gameData['cells'],
            'calls' => $gameData['calls'],
            'columns' => $gameData['columns'],
            'totalCells' => $gameData['totalCells'],
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
            gameType: 'shapes-bingo',
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

    /**
     * How many shapes a level asks the child to cover, and therefore its score
     * ceiling. The server owns this so the browser cannot claim a fuller card
     * than the level really dealt.
     */
    public function totalForLevel(int $level): int
    {
        return $this->cellsForLevel($level);
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
                'route' => route('student.games.shapes-bingo.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Shape Bingo', 'description' => 'Dab every shape as it is called!'],
            2 => ['title' => 'Shape Bingo', 'description' => 'Keep dabbing to fill your card!'],
            3 => ['title' => 'Bingo Builder', 'description' => 'A bigger card of shapes to cover!'],
            4 => ['title' => 'Bingo Builder', 'description' => 'More calls, more dabbing!'],
            5 => ['title' => 'Bingo Champion', 'description' => 'A full card of shapes!'],
            6 => ['title' => 'Bingo Champion', 'description' => 'Stay sharp and dab them all!'],
            7 => ['title' => 'Bingo Master', 'description' => 'The biggest card yet!'],
            8 => ['title' => 'Bingo Legend', 'description' => 'The ultimate shape bingo!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['cells'] = $this->cellsForLevel($level);

        return $data;
    }

    protected function cellsForLevel(int $level): int
    {
        return match (true) {
            $level <= 2 => 4,
            $level <= 4 => 6,
            $level <= 6 => 9,
            default => 12,
        };
    }

    protected function columnsForLevel(int $level): int
    {
        return match ($this->cellsForLevel($level)) {
            4 => 2,
            6 => 3,
            9 => 3,
            default => 4,
        };
    }

    protected function shapes(): array
    {
        return [
            'circle'   => ['name' => 'Circle',   'file' => 'Circle.png',   'color' => '#ec4899'],
            'heart'    => ['name' => 'Heart',    'file' => 'Heart.png',    'color' => '#ef4444'],
            'star'     => ['name' => 'Star',     'file' => 'Star.png',     'color' => '#f59e0b'],
            'triangle' => ['name' => 'Triangle', 'file' => 'Triangle.png', 'color' => '#10b981'],
            'square'   => ['name' => 'Square',   'file' => 'Square.png',   'color' => '#6366f1'],
        ];
    }

    protected function getGameData(int $level): array
    {
        $shapes = $this->shapes();
        $types = array_keys($shapes);
        shuffle($types);

        $count = $this->cellsForLevel($level);

        $cells = [];
        for ($i = 0; $i < $count; $i++) {
            $type = $types[$i % count($types)];
            $cells[] = $this->shapePayload($type, $shapes[$type], $i + 1);
        }
        shuffle($cells);

        // One call per cell: the caller simply walks the card's own shapes in a
        // fresh order, so covering every cell always lines up with every call.
        $calls = array_map(fn (array $cell) => [
            'type' => $cell['type'],
            'name' => $cell['name'],
            'image' => $cell['image'],
            'color' => $cell['color'],
        ], $cells);
        shuffle($calls);

        return [
            'cells' => $cells,
            'calls' => $calls,
            'columns' => $this->columnsForLevel($level),
            'totalCells' => $count,
        ];
    }

    protected function shapePayload(string $type, array $shape, int $id): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'name' => $shape['name'],
            'image' => asset('assets/images/game/shapes/shape imgs/'.$shape['file']),
            'color' => $shape['color'],
        ];
    }
}
