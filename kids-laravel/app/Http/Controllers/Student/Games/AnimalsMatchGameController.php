<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A drag-and-drop matching game: the tray holds every animal of the stage and
 * the child drags each one onto its identical twin below. A level is finished
 * (and only then recorded) once every animal has found its twin.
 */
class AnimalsMatchGameController extends Controller
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
                ->where('game_type', 'animals-match')
                ->where('score', '>=', 1)
                ->max('level');

            $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

            $scores = GameScore::where('student_id', $student->id)
                ->where('game_type', 'animals-match')
                ->get();
            $totalScore = $scores->sum('score');
        }

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.animals-match.stage-select', [
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
                ->where('game_type', 'animals-match')
                ->where('score', '>=', 1)
                ->max('level') ?? 0;
        }

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.animals-match.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.animals-match.play', [
            'level' => $level,
            'levelData' => $levelData,
            'totalLevels' => $this->totalLevels,
            'targets' => $gameData['targets'],
            'tray' => $gameData['tray'],
            'totalPairs' => $gameData['totalPairs'],
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
        // against the real number of animals rather than anything the browser
        // claims the tray held.
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
            gameType: 'animals-match',
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
     * How many animals a level asks the child to match, and therefore its score
     * ceiling. The server owns this so the browser cannot claim a fuller tray
     * than the level really held.
     */
    public function totalForLevel(int $level): int
    {
        return $this->pairsForLevel($level);
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
                'route' => route('student.games.animals-match.play', ['level' => $i]),
            ];
        }
        return $levels;
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Farm Pairs', 'description' => 'Drag each farm animal onto its twin!'],
            2 => ['title' => 'Farm Pairs', 'description' => 'More farm animals to match!'],
            3 => ['title' => 'Wild Pairs', 'description' => 'Drag each wild animal onto its twin!'],
            4 => ['title' => 'Wild Pairs', 'description' => 'More wild animals to match!'],
            5 => ['title' => 'Ocean Pairs', 'description' => 'Drag each ocean creature onto its twin!'],
            6 => ['title' => 'Sky Pairs', 'description' => 'Drag each bird onto its twin!'],
            7 => ['title' => 'Animal Expert', 'description' => 'Match even more tricky animals!'],
            8 => ['title' => 'Animal Master', 'description' => 'The ultimate matching challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['pairs'] = $this->pairsForLevel($level);

        return $data;
    }

    protected function pairsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    protected function getGameData(int $level): array
    {
        $allAnimals = [
            'cow'       => ['name' => 'Cow',       'emoji' => '🐄'],
            'pig'       => ['name' => 'Pig',       'emoji' => '🐷'],
            'chicken'   => ['name' => 'Chicken',   'emoji' => '🐔'],
            'sheep'     => ['name' => 'Sheep',     'emoji' => '🐑'],
            'horse'     => ['name' => 'Horse',     'emoji' => '🐴'],
            'lion'      => ['name' => 'Lion',      'emoji' => '🦁'],
            'elephant'  => ['name' => 'Elephant',  'emoji' => '🐘'],
            'giraffe'   => ['name' => 'Giraffe',   'emoji' => '🦒'],
            'zebra'     => ['name' => 'Zebra',     'emoji' => '🦓'],
            'monkey'    => ['name' => 'Monkey',    'emoji' => '🐵'],
            'dolphin'   => ['name' => 'Dolphin',   'emoji' => '🐬'],
            'whale'     => ['name' => 'Whale',     'emoji' => '🐋'],
            'shark'     => ['name' => 'Shark',     'emoji' => '🦈'],
            'octopus'   => ['name' => 'Octopus',   'emoji' => '🐙'],
            'eagle'     => ['name' => 'Eagle',     'emoji' => '🦅'],
            'parrot'    => ['name' => 'Parrot',    'emoji' => '🦜'],
            'owl'       => ['name' => 'Owl',       'emoji' => '🦉'],
            'penguin'   => ['name' => 'Penguin',   'emoji' => '🐧'],
            'bear'      => ['name' => 'Bear',      'emoji' => '🐻'],
            'fox'       => ['name' => 'Fox',       'emoji' => '🦊'],
        ];

        $pools = [
            1 => ['cow', 'pig', 'chicken', 'sheep', 'horse'],
            2 => ['cow', 'pig', 'chicken', 'sheep', 'horse'],
            3 => ['lion', 'elephant', 'giraffe', 'zebra', 'monkey'],
            4 => ['lion', 'elephant', 'giraffe', 'zebra', 'monkey'],
            5 => ['dolphin', 'whale', 'shark', 'octopus', 'bear', 'fox'],
            6 => ['eagle', 'parrot', 'owl', 'penguin', 'bear', 'fox'],
            7 => ['cow', 'lion', 'dolphin', 'eagle', 'monkey', 'shark', 'parrot', 'bear'],
            8 => array_keys($allAnimals),
        ];

        $poolKeys = $pools[$level] ?? $pools[1];
        $poolKeys = array_values($poolKeys);
        shuffle($poolKeys);
        $chosen = array_slice($poolKeys, 0, $this->pairsForLevel($level));

        $targets = [];
        $tray = [];
        foreach ($chosen as $index => $key) {
            $targets[] = [
                'pair' => $index + 1,
                'name' => $allAnimals[$key]['name'],
                'emoji' => $allAnimals[$key]['emoji'],
            ];
            $tray[] = [
                'pair' => $index + 1,
                'name' => $allAnimals[$key]['name'],
                'emoji' => $allAnimals[$key]['emoji'],
            ];
        }
        shuffle($tray);

        return [
            'targets' => $targets,
            'tray' => $tray,
            'totalPairs' => count($targets),
        ];
    }
}