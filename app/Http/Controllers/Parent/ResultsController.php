<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ActivityAttendance;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index()
    {
        $child = auth()->user()->children()->where('is_active', true)->first();

        if (!$child) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

        $results = ActivityAttendance::where('student_id', $child->id)
            ->with('activity')
            ->whereNotNull('score')
            ->orderBy('created_at', 'desc')
            ->get();

        $avgScore = $results->avg('score') ?? 0;
        $excellent = $results->where('score', '>=', 80)->count();
        $good = $results->where('score', '>=', 60)->where('score', '<', 80)->count();
        $needsWork = $results->where('score', '<', 60)->count();

        return view('parent.results', compact('child', 'results', 'avgScore', 'excellent', 'good', 'needsWork'));
    }
}
