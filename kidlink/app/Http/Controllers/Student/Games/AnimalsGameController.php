<?php

namespace App\Http\Controllers\Student\Games;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\LevelCompletionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnimalsGameController extends Controller
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
                ->where('game_type', 'animals')
                ->where('score', '>=', 1)
                ->max('level');

            $currentLevel = $highestCompleted ? min($highestCompleted + 1, $this->totalLevels) : 1;

            $scores = GameScore::where('student_id', $student->id)
                ->where('game_type', 'animals')
                ->get();
            $totalScore = $scores->sum('score');
        }

        $levels = $this->getLevelsData($currentLevel);

        return view('student.games.animals.stage-select', [
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
                ->where('game_type', 'animals')
                ->where('score', '>=', 1)
                ->max('level') ?? 0;
        }

        $unlockedLevel = min($highestCompleted + 1, $this->totalLevels);

        if ($level > $unlockedLevel) {
            return redirect()->route('student.games.animals.stage-select')
                ->with('error', 'Complete previous levels to unlock this stage.');
        }

        $levelData = $this->getLevelData($level);
        $gameData = $this->getGameData($level);

        return view('student.games.animals.play', [
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
            gameType: 'animals',
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
     * How many questions a level contains. The server owns this.
     *
     * One question is scored per round, since a round has a single correct animal
     * among its options, so the total is the round count rather than the number of
     * options on the board.
     */
    public function totalForLevel(int $level): int
    {
        return $this->roundsForLevel($level);
    }

    private function recorder(): LevelCompletionRecorder
    {
        return app(LevelCompletionRecorder::class);
    }

    protected function getLevelsData(int $currentLevel): array
    {
        // Positions and the road are shared by every game map (see StageMap).
        return \App\Support\StageMap::levels('animals', $currentLevel, $this->totalLevels);
    }

    protected function getLevelData(int $level): array
    {
        $levelData = [
            1 => ['title' => 'Farm Animals', 'description' => 'Meet the friendly farm animals!'],
            2 => ['title' => 'Farm Animals', 'description' => 'Horse joins the farm!'],
            3 => ['title' => 'Wild Animals', 'description' => 'Explore the wild kingdom!'],
            4 => ['title' => 'Wild Animals', 'description' => 'Zebra and monkey join in!'],
            5 => ['title' => 'Ocean Creatures', 'description' => 'Dive into the deep blue sea!'],
            6 => ['title' => 'Birds of the Sky', 'description' => 'Meet our feathered friends!'],
            7 => ['title' => 'Forest Friends', 'description' => 'Bear and fox are here - every animal is in!'],
            8 => ['title' => 'Animal Master', 'description' => 'The ultimate animal challenge!'],
        ];

        $data = $levelData[$level] ?? $levelData[1];
        $data['rounds'] = $this->roundsForLevel($level);
        $data['questions'] = $data['rounds'] * $this->optionsForLevel($level);

        return $data;
    }

    protected function roundsForLevel(int $level): int
    {
        return 3 + intdiv($level - 1, 2);
    }

    protected function optionsForLevel(int $level): int
    {
        return 4 + intdiv($level - 1, 2);
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

        $rounds = $this->roundsForLevel($level);
        $optCount = $this->optionsForLevel($level);

        $allRounds = [];
        for ($r = 0; $r < $rounds; $r++) {
            // The animals this level introduced are asked about more often.
            $targetKey = (! empty($newKeys) && random_int(0, 2) > 0)
                ? $newKeys[array_rand($newKeys)]
                : $poolKeys[array_rand($poolKeys)];
            $distractorKeys = array_values(array_diff($poolKeys, [$targetKey]));
            shuffle($distractorKeys);

            $options = [$targetKey => $allAnimals[$targetKey]];
            $needed = $optCount - 1;
            for ($i = 0; $i < $needed && $i < count($distractorKeys); $i++) {
                $options[$distractorKeys[$i]] = $allAnimals[$distractorKeys[$i]];
            }
            uksort($options, function () { return rand(-1, 1); });

            $optionList = [];
            foreach ($options as $key => $animal) {
                $optionList[] = [
                    'type' => $key,
                    'name' => $animal['name'],
                    'emoji' => $animal['emoji'],
                    'isMatch' => $key === $targetKey,
                ];
            }

            $allRounds[] = [
                'target' => $targetKey,
                'targetName' => $allAnimals[$targetKey]['name'],
                'targetEmoji' => $allAnimals[$targetKey]['emoji'],
                'options' => $optionList,
            ];
        }

        return [
            'rounds' => $allRounds,
            'totalRounds' => $rounds,
        ];
    }
}
