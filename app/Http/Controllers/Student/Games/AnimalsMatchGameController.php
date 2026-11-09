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
            'final_score' => $result['final_score'] ?? $result['percentage'],
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
        // Positions and the road are shared by every game map (see StageMap).
        return \App\Support\StageMap::levels('animals-match', $currentLevel, $this->totalLevels);
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Farm Pairs', 'description' => 'Drag each farm animal onto its twin!'],
            2 => ['title' => 'Farm Pairs', 'description' => 'Horse joins the farm - match them all!'],
            3 => ['title' => 'Wild Pairs', 'description' => 'Lions, elephants and giraffes to match!'],
            4 => ['title' => 'Wild Pairs', 'description' => 'Zebra and monkey join the wild ones!'],
            5 => ['title' => 'Ocean Pairs', 'description' => 'Drag each ocean creature onto its twin!'],
            6 => ['title' => 'Sky Pairs', 'description' => 'Drag each bird onto its twin!'],
            7 => ['title' => 'Forest Pairs', 'description' => 'Bear and fox are here - every animal is in!'],
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
            'cow'       => ['name' => 'Cow',       'emoji' => asset('assets/icons/cow.svg')],
            'pig'       => ['name' => 'Pig',       'emoji' => asset('assets/icons/pig.svg')],
            'chicken'   => ['name' => 'Chicken',   'emoji' => asset('assets/icons/chicken.svg')],
            'sheep'     => ['name' => 'Sheep',     'emoji' => asset('assets/icons/sheep.svg')],
            'horse'     => ['name' => 'Horse',     'emoji' => asset('assets/icons/horse.svg')],
            'lion'      => ['name' => 'Lion',      'emoji' => asset('assets/icons/lion.svg')],
            'elephant'  => ['name' => 'Elephant',  'emoji' => asset('assets/icons/elephant.svg')],
            'giraffe'   => ['name' => 'Giraffe',   'emoji' => asset('assets/icons/giraffe.svg')],
            'zebra'     => ['name' => 'Zebra',     'emoji' => asset('assets/icons/zebra.svg')],
            'monkey'    => ['name' => 'Monkey',    'emoji' => asset('assets/icons/monkey.svg')],
            'dolphin'   => ['name' => 'Dolphin',   'emoji' => asset('assets/icons/dolphin.svg')],
            'whale'     => ['name' => 'Whale',     'emoji' => asset('assets/icons/whale.svg')],
            'shark'     => ['name' => 'Shark',     'emoji' => asset('assets/icons/shark.svg')],
            'octopus'   => ['name' => 'Octopus',   'emoji' => asset('assets/icons/octopus.svg')],
            'eagle'     => ['name' => 'Eagle',     'emoji' => asset('assets/icons/eagle.svg')],
            'parrot'    => ['name' => 'Parrot',    'emoji' => asset('assets/icons/parrot.svg')],
            'owl'       => ['name' => 'Owl',       'emoji' => asset('assets/icons/owl.svg')],
            'penguin'   => ['name' => 'Penguin',   'emoji' => asset('assets/icons/penguin.svg')],
            'bear'      => ['name' => 'Bear',      'emoji' => asset('assets/icons/bear.svg')],
            'fox'       => ['name' => 'Fox',       'emoji' => asset('assets/icons/fox.svg')],
        ];

        // Each level adds new animals and keeps the earlier ones, so all 20 are in
        // play by level 7.
        $introduced = [
            1 => ['cow', 'pig', 'chicken', 'sheep'],
            2 => ['horse'],
            3 => ['lion', 'elephant', 'giraffe'],
            4 => ['zebra', 'monkey'],
            5 => ['dolphin', 'whale', 'shark', 'octopus'],
            6 => ['eagle', 'parrot', 'owl', 'penguin'],
            7 => ['bear', 'fox'],
        ];
        $poolKeys = [];
        for ($l = 1; $l <= min(max($level, 1), 7); $l++) {
            $poolKeys = array_merge($poolKeys, $introduced[$l]);
        }
        $newKeys = $introduced[$level] ?? [];

        // This level's new animals are always in the round; earlier ones fill the rest.
        $newPick = $newKeys;
        shuffle($newPick);
        $rest = array_values(array_diff($poolKeys, $newKeys));
        shuffle($rest);
        $chosen = array_slice(array_merge($newPick, $rest), 0, $this->pairsForLevel($level));

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