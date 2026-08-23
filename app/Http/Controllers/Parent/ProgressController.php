<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ActivityAttendance;
use App\Models\EccdRecord;
use App\Models\GameScore;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index()
    {
        $child = auth()->user()->children()->where('is_active', true)->first();

        if (!$child) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

        $overallAvg = ActivityAttendance::where('student_id', $child->id)->avg('score') ?? 0;

        $areaScores = ActivityAttendance::where('student_id', $child->id)
            ->join('activities', 'activity_attendance.activity_id', '=', 'activities.id')
            ->selectRaw('activities.type, AVG(activity_attendance.score) as avg_score')
            ->groupBy('activities.type')
            ->pluck('avg_score', 'type');

        $bestArea = $areaScores->sortDesc()->keys()->first() ?? 'N/A';
        $needsAttention = $areaScores->sort()->keys()->first() ?? 'N/A';

        $scoredActivities = ActivityAttendance::where('student_id', $child->id)->whereNotNull('score')->count();

        $trend = GameScore::where('student_id', $child->id)
            ->selectRaw('DATE(created_at) as date, AVG(score * 100.0 / total) as avg_score')
            ->groupBy('date')->orderBy('date')->get();

        $radarData = [];
        foreach (['Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive'] as $domain) {
            $achieved = EccdRecord::where('student_id', $child->id)
                ->where('domain', $domain)->where('status', 'Achieved')->count();
            $total = EccdRecord::where('student_id', $child->id)
                ->where('domain', $domain)->count();
            $radarData[$domain] = $total > 0 ? round(($achieved / $total) * 100) : 0;
        }

        $recentScores = ActivityAttendance::where('student_id', $child->id)
            ->with('activity')
            ->whereNotNull('score')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('parent.progress', compact(
            'child', 'overallAvg', 'bestArea', 'needsAttention',
            'scoredActivities', 'trend', 'areaScores', 'radarData', 'recentScores'
        ));
    }
}
