<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $children = auth()->user()->children()->where('is_active', true)->get();

        if ($children->isEmpty()) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

        $selectedChildId = $request->query('child_id');
        $child = $children->where('id', $selectedChildId)->first() ?? $children->first();

        $thisMonth = Carbon::today()->startOfMonth();
        $month = $this->resolveMonth($request->query('month'), $thisMonth);
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        // Six months of history in one query, newest month last.
        $historyStart = $thisMonth->copy()->subMonthsNoOverflow(5);
        $rangeStart = $historyStart->lt($monthStart) ? $historyStart : $monthStart;
        $rangeEnd = $thisMonth->copy()->endOfMonth()->gt($monthEnd) ? $thisMonth->copy()->endOfMonth() : $monthEnd;

        $rows = Attendance::where('student_id', $child->id)
            // Carbon bounds (00:00:00 to 23:59:59) so the last day is included on every driver.
            ->whereBetween('date', [$rangeStart, $rangeEnd])
            ->orderBy('date')
            ->get(['date', 'status']);

        // The viewed month.
        $attendances = $rows->filter(fn ($r) => $r->date->betweenIncluded($monthStart, $monthEnd))->values();
        $present = $attendances->where('status', 'Present')->count();
        $absent = $attendances->where('status', 'Absent')->count();
        $late = $attendances->where('status', 'Late')->count();
        $excused = $attendances->where('status', 'Excused')->count();
        $total = $attendances->count();
        $rate = Attendance::rate($present, $late, $absent);

        // Dates are Carbon instances, so key by formatted string: comparing a
        // Carbon object with 'Y-m-d' never matches and left every day blank.
        $byDate = $attendances->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $today = Carbon::today();
        $monthlyChart = [];
        for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
            $key = $day->format('Y-m-d');
            $monthlyChart[] = [
                'date' => $key,
                'day' => $day->format('j'),
                'status' => $byDate->get($key)?->status ?? 'No Record',
                'weekend' => $day->isWeekend(),
                'future' => $day->gt($today),
            ];
        }

        $monthlySummary = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $thisMonth->copy()->subMonthsNoOverflow($i);
            $mRows = $rows->filter(fn ($r) => $r->date->betweenIncluded($m->copy()->startOfMonth(), $m->copy()->endOfMonth()));
            $mPresent = $mRows->where('status', 'Present')->count();
            $mLate = $mRows->where('status', 'Late')->count();
            $mAbsent = $mRows->where('status', 'Absent')->count();

            $monthlySummary[] = [
                'month' => $m->translatedFormat('M Y'),
                'present' => $mPresent,
                'absent' => $mAbsent,
                'late' => $mLate,
                'excused' => $mRows->where('status', 'Excused')->count(),
                'recorded' => $mRows->count(),
                'rate' => Attendance::rate($mPresent, $mLate, $mAbsent),
            ];
        }

        $canGoNext = $monthStart->lt($thisMonth);

        return view('parent.attendance', compact(
            'children', 'child', 'attendances', 'present', 'absent', 'late', 'excused',
            'rate', 'month', 'monthlyChart', 'monthlySummary', 'total', 'canGoNext'
        ));
    }

    /** Accepts only YYYY-MM; anything else falls back to the current month. */
    private function resolveMonth($value, Carbon $fallback): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return Carbon::createFromFormat('!Y-m', $value)->startOfMonth();
        }

        return $fallback->copy();
    }
}
