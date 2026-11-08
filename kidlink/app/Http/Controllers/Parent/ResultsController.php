<?php

namespace App\Http\Controllers\Parent;

use App\Games\GameCatalog;
use App\Http\Controllers\Controller;
use App\Models\ActivityAttendance;
use App\Models\GameScore;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    /** How many individual game scores the history list shows. */
    private const HISTORY_LIMIT = 50;

    public function index(Request $request)
    {
        $children = auth()->user()->activeChildren();

        if ($children->isEmpty()) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

        $selectedChildId = $request->query('child_id');
        $child = $children->where('id', $selectedChildId)->first() ?? $children->first();

        // One row per finished level (the best attempt), newest activity first.
        $scores = GameScore::where('student_id', $child->id)
            ->select(['id', 'game_type', 'level', 'score', 'total', 'mistakes', 'percentage', 'time_taken', 'created_at', 'updated_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (GameScore $score) => $this->present($score));

        $totalLevels = $scores->count();
        $avgAccuracy = $totalLevels > 0 ? (int) round($scores->avg('accuracy')) : 0;
        $excellent = $scores->where('accuracy', '>=', 80)->count();
        $good = $scores->where('accuracy', '>=', 60)->where('accuracy', '<', 80)->count();
        $needsWork = $scores->where('accuracy', '<', 60)->count();

        // Per-game summary, in the same order as the game catalogue.
        $catalogOrder = array_flip(array_column(GameCatalog::all(), 'slug'));
        $byGame = $scores->groupBy('game_type')
            ->map(fn ($rows, $type) => [
                'game_type' => $type,
                'game' => $rows->first()['game'],
                'levels' => $rows->count(),
                'highest_level' => $rows->max('level'),
                'accuracy' => (int) round($rows->avg('accuracy')),
                'mistakes' => (int) $rows->sum('mistakes'),
            ])
            ->sortBy(fn ($row) => $catalogOrder[$row['game_type']] ?? 999)
            ->values();

        $bestGame = $byGame->sortByDesc('accuracy')->first();

        // Optional filter on the history list; an unknown value is ignored.
        $game = (string) $request->query('game', '');
        if (!$byGame->contains('game_type', $game)) {
            $game = '';
        }

        $history = $scores
            ->when($game !== '', fn ($rows) => $rows->where('game_type', $game))
            ->take(self::HISTORY_LIMIT)
            ->values();

        // Teacher-scored classroom activities are kept, but as a secondary list.
        $activityResults = ActivityAttendance::where('student_id', $child->id)
            ->select(['id', 'activity_id', 'score', 'created_at'])
            ->with('activity:id,title')
            ->whereNotNull('score')
            ->orderBy('created_at', 'desc')
            ->limit(self::HISTORY_LIMIT)
            ->get();

        return view('parent.results', compact(
            'children', 'child', 'scores', 'totalLevels', 'avgAccuracy',
            'excellent', 'good', 'needsWork', 'byGame', 'bestGame',
            'game', 'history', 'activityResults'
        ) + ['historyLimit' => self::HISTORY_LIMIT]);
    }

    /**
     * Flatten a stored score into what the page needs. Accuracy matches the
     * rest of the app: correct answers out of every attempt, mistakes included.
     */
    private function present(GameScore $score): array
    {
        $mistakes = (int) ($score->mistakes ?? 0);
        $attempts = (int) $score->total + $mistakes;

        $accuracy = $score->percentage !== null
            ? (int) $score->percentage
            : ($attempts > 0 ? (int) round(($score->score / $attempts) * 100) : 0);

        return [
            'game_type' => $score->game_type,
            'game' => GameCatalog::game($score->game_type)['name']
                ?? ucwords(str_replace('-', ' ', $score->game_type)),
            'level' => (int) $score->level,
            'score' => (int) $score->score,
            'total' => (int) $score->total,
            'mistakes' => $mistakes,
            'accuracy' => max(0, min(100, $accuracy)),
            'time' => $this->formatTime($score->time_taken),
            'played_at' => $score->updated_at ?? $score->created_at,
        ];
    }

    private function formatTime($seconds): ?string
    {
        // Rows saved before play time was measured hold 0. No finished level takes
        // 0 seconds, so treat it as "unknown" and show nothing rather than "0s".
        if ($seconds === null || $seconds <= 0) {
            return null;
        }

        $seconds = (int) $seconds;

        return $seconds >= 60
            ? sprintf('%dm %02ds', intdiv($seconds, 60), $seconds % 60)
            : $seconds . 's';
    }
}
