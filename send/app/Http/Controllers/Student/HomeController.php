<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;

class HomeController extends Controller
{
    public function index()
    {
        $student = $this->resolveStudent();
        $stats = $this->statsFor($student);

        $scores = $stats['scores'];
        $totalScore = $scores->sum('score');
        $totalGames = $stats['games_played'];
        $totalPoints = $stats['total_points'];
        $avgScore = $stats['avg_score'];

        $pointsByGame = Point::getTotalPointsByGame($student->id);
        $recentActivity = Point::getRecentActivity($student->id, 5);
        $streak = $stats['streak'];

        $highestLevelByGame = [];
        foreach ($scores as $s) {
            $g = $s->game_type;
            if (!isset($highestLevelByGame[$g]) || $s->level > $highestLevelByGame[$g]) {
                $highestLevelByGame[$g] = $s->level;
            }
        }

        $games = \App\Games\GameCatalog::all();

        foreach ($games as &$game) {
            $type = $game['slug'];
            $gameScores = $scores->where('game_type', $type);
            $game['played'] = $gameScores->count();
            $game['best'] = $gameScores->max('score');
            $game['highestLevel'] = $highestLevelByGame[$type] ?? 0;
            $game['points'] = $pointsByGame[$type] ?? 0;
        }
        unset($game);

        return view('student.home', compact(
            'student', 'scores', 'totalScore', 'totalGames', 'totalPoints',
            'pointsByGame', 'recentActivity', 'streak', 'games', 'avgScore'
        ));
    }

    /**
     * Live stats for the dashboard, read fresh from the database so the
     * numbers can be refreshed without reloading the page.
     */
    public function stats()
    {
        $stats = $this->statsFor($this->resolveStudent());

        return response()->json([
            'total_points' => $stats['total_points'],
            'games_played' => $stats['games_played'],
            'streak' => $stats['streak'],
            'avg_score' => $stats['avg_score'],
        ]);
    }

    private function resolveStudent(): Student
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        return $student;
    }

    private function statsFor(Student $student): array
    {
        $scores = GameScore::where('student_id', $student->id)->get();

        $streak = 0;
        foreach ($scores->sortByDesc('created_at')->take(5) as $s) {
            if ($s->score >= $s->total * 0.5) {
                $streak++;
            } else {
                break;
            }
        }

        $average = $scores->avg(fn (GameScore $s) => $this->accuracyFor($s));

        return [
            'scores' => $scores,
            'games_played' => $scores->count(),
            'total_points' => Point::getTotalPoints($student->id),
            'streak' => $streak,
            // Correct answers over total attempts, so mistakes count against it.
            'avg_score' => (int) round($average ?? 0),
        ];
    }

    /**
     * Guards against a zero denominator so a malformed row cannot 500 the page.
     */
    private function accuracyFor(GameScore $score): float
    {
        $attempts = $score->total + ($score->mistakes ?? 0);

        return $attempts > 0 ? 100 * $score->score / $attempts : 0.0;
    }
}
