<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ActivityAttendance;
use App\Models\Attendance;
use App\Models\EccdRecord;
use App\Models\GameScore;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const DOMAINS = AnalyticsService::ECCD_DOMAINS;

    public function index(Request $request)
    {
        $user = auth()->user();
        $children = $user->children()->where('is_active', true)->get();

        if ($children->isEmpty()) {
            return view('parent.dashboard', [
                'children' => $children,
                'child' => null,
                'avgScore' => 0,
                'overallAvg' => 0,
                'attendanceRate' => 0,
                'absentCount' => 0,
                'lateCount' => 0,
                'domainMastery' => 0,
                'scoredActivities' => 0,
                'scoreTrend' => collect(),
                'gamePerformance' => collect(),
                'areaScores' => collect(),
                'domainScores' => [],
                'domainStatus' => collect(),
                'attendanceTrend' => collect(),
                'assessedIndicators' => 0,
                'totalPlays' => 0,
                'bestArea' => 'N/A',
                'needsAttentionArea' => 'N/A',
                'strongestDomain' => 'N/A',
                'weakestDomain' => 'N/A',
                'insights' => [],
            ]);
        }

        $selectedChildId = $request->query('child_id');
        $child = $children->where('id', $selectedChildId)->first() ?? $children->first();

        $studentId = $child->id;

        $avgScore = ActivityAttendance::where('student_id', $studentId)->avg('score') ?? 0;
        $overallAvg = (float) ActivityAttendance::where('student_id', $studentId)->avg('score');
        $scoredActivities = ActivityAttendance::where('student_id', $studentId)->whereNotNull('score')->count();

        $totalAttendance = Attendance::where('student_id', $studentId)->count();
        $presentCount = Attendance::where('student_id', $studentId)->where('status', 'Present')->count();
        $absentCount = Attendance::where('student_id', $studentId)->where('status', 'Absent')->count();
        $lateCount = Attendance::where('student_id', $studentId)->where('status', 'Late')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        $scoreTrend = GameScore::where('student_id', $studentId)
            ->selectRaw('DATE(created_at) as date, '.AnalyticsService::ACCURACY_SQL.' as avg_score, COUNT(*) as plays')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalPlays = GameScore::where('student_id', $studentId)->count();

        $gamePerformance = GameScore::where('student_id', $studentId)
            ->selectRaw('game_type, COUNT(*) as plays, '.AnalyticsService::ACCURACY_SQL.' as avg_pct')
            ->groupBy('game_type')
            ->orderByDesc('plays')
            ->get();

        $areaScores = ActivityAttendance::where('student_id', $studentId)
            ->join('activities', 'activity_attendance.activity_id', '=', 'activities.id')
            ->selectRaw('activities.type, AVG(activity_attendance.score) as avg_score')
            ->groupBy('activities.type')
            ->orderByDesc('avg_score')
            ->pluck('avg_score', 'type');

        $domainScores = [];
        $domainCounts = [];
        foreach (self::DOMAINS as $domain) {
            $records = EccdRecord::where('student_id', $studentId)->where('domain', $domain);
            $total = (clone $records)->count();
            $achieved = (clone $records)->where('status', 'Achieved')->count();
            $domainCounts[$domain] = $total;
            $domainScores[$domain] = $total > 0 ? round(($achieved / $total) * 100) : 0;
        }

        $assessedIndicators = array_sum($domainCounts);
        $achievedIndicators = EccdRecord::where('student_id', $studentId)->where('status', 'Achieved')->count();
        $domainMastery = $assessedIndicators > 0 ? round(($achievedIndicators / $assessedIndicators) * 100) : 0;

        $domainStatus = EccdRecord::where('student_id', $studentId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Bucketed in PHP rather than with DATE_TRUNC, which only exists on
        // Postgres and would break the SQLite test driver.
        $weekStart = Carbon::today()->startOfWeek()->subWeeks(7);
        $buckets = [];
        foreach (Attendance::where('student_id', $studentId)
            ->where('date', '>=', $weekStart)
            ->select(['date', 'status'])
            ->cursor() as $row) {
            $key = Carbon::parse($row->date)->startOfWeek()->format('Y-m-d');
            $buckets[$key] ??= ['week' => $key, 'present' => 0, 'absent' => 0, 'late' => 0];
            $field = strtolower($row->status);
            if (isset($buckets[$key][$field])) {
                $buckets[$key][$field]++;
            }
        }
        $attendanceTrend = collect();
        for ($i = 0; $i < 8; $i++) {
            $week = $weekStart->copy()->addWeeks($i)->format('Y-m-d');
            $attendanceTrend->push($buckets[$week] ?? ['week' => $week, 'present' => 0, 'absent' => 0, 'late' => 0]);
        }

        $rankedAreas = $areaScores->sortDesc();
        $bestArea = $rankedAreas->keys()->first() ?? 'N/A';
        $needsAttentionArea = $rankedAreas->filter(fn ($s) => $s !== null)->sort()->keys()->first() ?? 'N/A';

        $rankedDomains = collect($domainScores)->filter(fn ($v, $k) => $domainCounts[$k] > 0)->sortDesc();
        $strongestDomain = $rankedDomains->keys()->first() ?? 'N/A';
        $weakestDomain = $rankedDomains->keys()->last() ?? 'N/A';

        $insights = [];
        if ($assessedIndicators > 0) {
            $insights[] = $domainMastery >= 80
                ? __('analytics.insight_mastery_high', ['name' => $child->first_name, 'pct' => $domainMastery])
                : __('analytics.insight_mastery_some', ['pct' => $domainMastery, 'count' => $assessedIndicators]);
        }
        if ($strongestDomain !== 'N/A') {
            $insights[] = __('analytics.insight_strongest', ['domain' => $strongestDomain, 'pct' => $domainScores[$strongestDomain]]);
        }
        if ($weakestDomain !== 'N/A' && $weakestDomain !== $strongestDomain) {
            $insights[] = __('analytics.insight_weakest', ['domain' => $weakestDomain, 'pct' => $domainScores[$weakestDomain]]);
        }
        if ($bestArea !== 'N/A') {
            $insights[] = __('analytics.insight_best_area', ['area' => $bestArea, 'pct' => round($rankedAreas[$bestArea])]);
        }
        if ($needsAttentionArea !== 'N/A' && $needsAttentionArea !== $bestArea) {
            $insights[] = __('analytics.insight_focus_area', ['area' => $needsAttentionArea, 'pct' => round($rankedAreas[$needsAttentionArea])]);
        }
        if ($attendanceRate < 85 && $totalAttendance > 0) {
            $insights[] = __('analytics.insight_attendance_low', ['rate' => $attendanceRate, 'days' => $totalAttendance]);
        } elseif ($totalAttendance > 0) {
            $insights[] = __('analytics.insight_attendance_ok', ['rate' => $attendanceRate, 'days' => $totalAttendance]);
        }
        if ($totalPlays === 0) {
            $insights[] = __('analytics.insight_no_games');
        }

        return view('parent.dashboard', compact(
            'children', 'child',
            'avgScore', 'overallAvg', 'attendanceRate', 'absentCount', 'lateCount',
            'scoredActivities', 'domainMastery',
            'scoreTrend', 'gamePerformance', 'areaScores',
            'domainScores', 'domainStatus', 'attendanceTrend',
            'assessedIndicators', 'totalPlays',
            'bestArea', 'needsAttentionArea', 'strongestDomain', 'weakestDomain',
            'insights'
        ));
    }
}
