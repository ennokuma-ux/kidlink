<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LettersGameController extends Controller
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
                ->where('game_type', 'letters')
                ->where('score', '>=', 1)
                ->max('level');

            $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

            $scores = GameScore::where('student_id', $student->id)
                ->where('game_type', 'letters')
                ->get();
            $totalScore = $scores->sum('score');
        }

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.letters.stage-select', [
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
                ->where('game_type', 'letters')
                ->where('score', '>=', 1)
                ->max('level') ?? 0;
        }

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.letters.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.letters.play', [
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
            gameType: 'letters',
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

    /** How many questions a level contains. The server owns this, not the browser. */
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
                'color' => $pos['color'] ?? 'red',
                'route' => route('student.games.letters.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => [
                'title' => 'Letter A-E',
                'description' => 'Match capital and lowercase A, B, C, D, E!',
                'objectives' => ['A', 'B', 'C', 'D', 'E'],
            ],
            2 => [
                'title' => 'Letter F-J',
                'description' => 'Match capital and lowercase F through J!',
                'objectives' => ['F', 'G', 'H', 'I', 'J'],
            ],
            3 => [
                'title' => 'Letter K-O',
                'description' => 'Match capital and lowercase K through O!',
                'objectives' => ['K', 'L', 'M', 'N', 'O'],
            ],
            4 => [
                'title' => 'Letter P-T',
                'description' => 'Match capital and lowercase P through T!',
                'objectives' => ['P', 'Q', 'R', 'S', 'T'],
            ],
            5 => [
                'title' => 'Letter U-Z',
                'description' => 'Match capital and lowercase U through Z!',
                'objectives' => ['U', 'V', 'W', 'X', 'Y', 'Z'],
            ],
            6 => [
                'title' => 'Mixed Match 1',
                'description' => 'Capital and lowercase in a bigger mix!',
                'objectives' => ['All', 'Mixed', 'Review'],
            ],
            7 => [
                'title' => 'Mixed Match 2',
                'description' => 'Everything mixed together!',
                'objectives' => ['All', 'Mixed', 'Review'],
            ],
            8 => [
                'title' => 'Letter Master',
                'description' => 'The ultimate capital & lowercase challenge!',
                'objectives' => ['All', 'Mixed', 'Speed'],
            ],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'] * 3;

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    protected function getGameData(int $level): array
    {
        // Letters available per level (objectives); levels 6+ review the whole alphabet
        $pools = [
            1 => ['A', 'B', 'C', 'D', 'E'],
            2 => ['F', 'G', 'H', 'I', 'J'],
            3 => ['K', 'L', 'M', 'N', 'O'],
            4 => ['P', 'Q', 'R', 'S', 'T'],
            5 => ['U', 'V', 'W', 'X', 'Y', 'Z'],
            6 => ['A', 'E', 'I', 'O', 'U', 'B', 'D', 'G', 'M', 'P', 'S', 'T'],
            7 => ['A', 'B', 'C', 'G', 'L', 'N', 'R', 'S', 'T', 'W', 'Y', 'Z'],
            8 => ['A', 'B', 'E', 'F', 'K', 'M', 'N', 'O', 'P', 'R', 'S', 'V', 'W', 'X', 'Z'],
        ];

        $letters = $pools[$level] ?? $pools[1];
        $colors = [
            'A' => '#ec4899', 'B' => '#f59e0b', 'C' => '#10b981',
            'D' => '#258cc5', 'E' => '#8b5cf6', 'F' => '#f05d56',
            'G' => '#0ea5e9', 'H' => '#f43f5e', 'I' => '#14b8a6',
            'J' => '#f97316', 'K' => '#6366f1', 'L' => '#84cc16',
            'M' => '#d946ef', 'N' => '#0891b2', 'O' => '#ef4444',
            'P' => '#3b82f6', 'Q' => '#f59e0b', 'R' => '#22c55e',
            'S' => '#e11d48', 'T' => '#06b6d4', 'U' => '#a855f7',
            'V' => '#ea580c', 'W' => '#2563eb', 'X' => '#059669',
            'Y' => '#c026d3', 'Z' => '#7c3aed',
        ];

        $rounds = $this->roundsForLevel($level);
        $startOffset = ($level - 1) % count($letters);

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            $target = $letters[($startOffset + $r) % count($letters)];
            $others = array_values(array_diff($letters, [$target]));

            $pool = [];
            // Exactly 3 matches: the same letter in the opposite case
            for ($i = 0; $i < 3; $i++) {
                $pool[] = $target;
            }
            // 5 distractors: other letters in the opposite case
            for ($i = 0; $i < 5; $i++) {
                $pool[] = $others[array_rand($others)];
            }
            shuffle($pool);

            $options = [];
            foreach ($pool as $letter) {
                $options[] = [
                    'type' => $letter,
                    'char' => $letter,
                    'name' => $letter,
                    'color' => $colors[$letter] ?? '#ff8555',
                    'isMatch' => $letter === $target,
                ];
            }

            $allRounds[] = [
                'target' => $target,
                'targetUpper' => strtoupper($target),
                'targetLower' => strtolower($target),
                'targetColor' => $colors[$target] ?? '#ff8555',
                'options' => $options,
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}