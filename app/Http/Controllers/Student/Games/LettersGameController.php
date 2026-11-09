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
        // Positions and the road are shared by every game map (see StageMap).
        return \App\Support\StageMap::levels('letters', $currentLevel, $this->totalLevels);
    }

    protected function getLevelData(int $level): array
    {
        // Each level adds a few new letters and keeps the earlier ones, so by
        // level 7 the whole alphabet is in play. Level 8 is a full-alphabet mix.
        $levelData = [
            1 => [
                'title' => 'Letter A-D',
                'description' => 'Match big and small letters A through D!',
                'objectives' => ['A', 'B', 'C', 'D'],
            ],
            2 => [
                'title' => 'Letter E-H',
                'description' => 'New letters E through H, plus the ones you already know!',
                'objectives' => ['E', 'F', 'G', 'H', 'Review'],
            ],
            3 => [
                'title' => 'Letter I-L',
                'description' => 'New letters I through L, plus the ones you already know!',
                'objectives' => ['I', 'J', 'K', 'L', 'Review'],
            ],
            4 => [
                'title' => 'Letter M-P',
                'description' => 'New letters M through P, plus the ones you already know!',
                'objectives' => ['M', 'N', 'O', 'P', 'Review'],
            ],
            5 => [
                'title' => 'Letter Q-T',
                'description' => 'New letters Q through T, plus the ones you already know!',
                'objectives' => ['Q', 'R', 'S', 'T', 'Review'],
            ],
            6 => [
                'title' => 'Letter U-X',
                'description' => 'New letters U through X, plus the ones you already know!',
                'objectives' => ['U', 'V', 'W', 'X', 'Review'],
            ],
            7 => [
                'title' => 'Letter Y-Z',
                'description' => 'The last letters, Y and Z! Now you know the whole alphabet!',
                'objectives' => ['Y', 'Z', 'Review'],
            ],
            8 => [
                'title' => 'Letter Master',
                'description' => 'The whole alphabet, all mixed up!',
                'objectives' => ['A-Z', 'Mixed', 'Speed'],
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
        // New letters introduced by each level. The pool grows level by level (the
        // new letters plus every earlier one) and is the full A-Z by level 7.
        $groups = [
            1 => ['A', 'B', 'C', 'D'],
            2 => ['E', 'F', 'G', 'H'],
            3 => ['I', 'J', 'K', 'L'],
            4 => ['M', 'N', 'O', 'P'],
            5 => ['Q', 'R', 'S', 'T'],
            6 => ['U', 'V', 'W', 'X'],
            7 => ['Y', 'Z'],
        ];

        if ($level >= 8) {
            $letters = range('A', 'Z');
            $focus = [];
        } else {
            $level = max(1, $level);
            $letters = [];
            for ($g = 1; $g <= $level; $g++) {
                $letters = array_merge($letters, $groups[$g]);
            }
            $focus = $groups[$level];
        }

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

        // Targets: this level's new letters first, then a mix of letters learned
        // earlier. (Level 8 has no new letters, so it is a random mix of all 26.)
        $newTargets = $focus;
        shuffle($newTargets);
        $reviewTargets = array_values(array_diff($letters, $focus));
        shuffle($reviewTargets);
        $targets = array_slice(array_merge($newTargets, $reviewTargets), 0, $rounds);
        shuffle($targets);

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            $target = $targets[$r];
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