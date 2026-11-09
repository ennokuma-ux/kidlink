<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColorsSortGameController extends Controller
{
    public $totalLevels = 8;

    public $gameType = 'colors-sort';

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
            ->where('game_type', $this->gameType)
            ->where('score', '>=', 1)
            ->max('level');

        $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

        $scores = GameScore::where('student_id', $student->id)
            ->where('game_type', $this->gameType)
            ->get();
        $totalScore = $scores->sum('score');

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.colors-sort.stage-select', [
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
            return redirect()->route('student.games.colors-sort.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.colors-sort.play', [
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
            'final_score' => $result['final_score'] ?? $result['percentage'],
            'points_earned' => $result['points_earned'],
            'next_level_unlocked' => $result['completed'] && $level < $this->totalLevels,
        ]);
    }

    /** How many blobs the child has to place across a level. The server owns this. */
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
        return \App\Support\StageMap::levels('colors-sort', $currentLevel, $this->totalLevels);
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Color Sorter', 'description' => 'Drag each paint drop into its matching bucket!'],
            2 => ['title' => 'Color Sorter', 'description' => 'Two buckets - sort the colors!'],
            3 => ['title' => 'Color Sorter Pro', 'description' => 'Three buckets to fill - you can do it!'],
            4 => ['title' => 'Color Sorter Pro', 'description' => 'More buckets, more colors to sort!'],
            5 => ['title' => 'Color Sorter Master', 'description' => 'Four buckets and a new color: purple!'],
            6 => ['title' => 'Color Sorter Master', 'description' => 'Sky blue joins the buckets!'],
            7 => ['title' => 'Ultimate Sorter', 'description' => 'Every color is here - five buckets to fill!'],
            8 => ['title' => 'Ultimate Sorter', 'description' => 'The ultimate color sorting challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'] * $this->bucketsForLevel($level);

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    protected function bucketsForLevel(int $level): int
    {
        return 2 + intdiv($level - 1, 2);
    }

    protected function getGameData(int $level): array
    {
        // Colorblind-safe palette, matching the Colors game's letters.
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

        // One new colour per level, keeping the earlier ones: all eight colours
        // are in play by level 7.
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
        $newColor = [3 => 'green', 4 => 'orange', 5 => 'purple', 6 => 'sky', 7 => 'black'][$level] ?? null;

        $poolKeys = $pools[$level] ?? $pools[1];
        $poolColors = [];
        foreach ($poolKeys as $key) {
            $poolColors[$key] = $allColors[$key];
        }

        $rounds = $this->roundsForLevel($level);
        $bucketCount = min($this->bucketsForLevel($level), count($poolKeys));

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            // One bucket per colour in this round, order shuffled so the child
            // has to look rather than rely on position.
            $shuffledKeys = $poolKeys;
            shuffle($shuffledKeys);
            $roundKeys = array_slice($shuffledKeys, 0, $bucketCount);
            // The colour this level introduced is always part of the round.
            if ($newColor !== null && ! in_array($newColor, $roundKeys, true)) {
                $roundKeys[0] = $newColor;
            }

            $buckets = [];
            foreach ($roundKeys as $key) {
                $buckets[] = [
                    'type' => $key,
                    'name' => $poolColors[$key]['name'],
                    'hex' => $poolColors[$key]['hex'],
                    'marker' => $poolColors[$key]['marker'],
                ];
            }

            // Tray: exactly one paint drop per bucket (same multiset), shuffled.
            $trayKeys = $roundKeys;
            shuffle($trayKeys);

            $tray = [];
            foreach ($trayKeys as $key) {
                $tray[] = [
                    'type' => $key,
                    'name' => $poolColors[$key]['name'],
                    'hex' => $poolColors[$key]['hex'],
                    'marker' => $poolColors[$key]['marker'],
                ];
            }

            $allRounds[] = [
                'buckets' => $buckets,
                'tray' => $tray,
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}
