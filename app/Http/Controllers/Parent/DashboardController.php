<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ActivityAttendance;
use App\Models\Attendance;
use App\Models\GameScore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $children = auth()->user()->children()->where('is_active', true)->get();
        $child = $children->first();

        if (!$child) {
            return view('parent.dashboard', compact('children', 'child'))->with('info', 'No children registered yet.');
        }

        $avgScore = ActivityAttendance::where('student_id', $child->id)->avg('score') ?? 0;
        $totalAttendance = Attendance::where('student_id', $child->id)->count();
        $presentCount = Attendance::where('student_id', $child->id)->where('status', 'Present')->count();
        $absentCount = Attendance::where('student_id', $child->id)->where('status', 'Absent')->count();
        $lateCount = Attendance::where('student_id', $child->id)->where('status', 'Late')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        $gameScores = GameScore::where('student_id', $child->id)
            ->selectRaw('DATE(created_at) as date, AVG(score * 100.0 / total) as avg_score')
            ->groupBy('date')->orderBy('date')->get();

        $areaScores = ActivityAttendance::where('student_id', $child->id)
            ->join('activities', 'activity_attendance.activity_id', '=', 'activities.id')
            ->selectRaw('activities.type, AVG(activity_attendance.score) as avg_score')
            ->groupBy('activities.type')
            ->pluck('avg_score', 'type');

        $radarData = [];
        foreach (['Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive'] as $domain) {
            $achieved = \App\Models\EccdRecord::where('student_id', $child->id)
                ->where('domain', $domain)->where('status', 'Achieved')->count();
            $total = \App\Models\EccdRecord::where('student_id', $child->id)
                ->where('domain', $domain)->count();
            $radarData[$domain] = $total > 0 ? round(($achieved / $total) * 100) : 0;
        }

        return view('parent.dashboard', compact(
            'children', 'child', 'avgScore', 'attendanceRate',
            'absentCount', 'lateCount', 'gameScores', 'areaScores', 'radarData'
        ));
    }
}
