<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        $activities = Activity::where('teacher_id', $teacherId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $types = Activity::where('teacher_id', $teacherId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $stats = [
            'total' => Activity::where('teacher_id', $teacherId)->count(),
            'completed' => Activity::where('teacher_id', $teacherId)->where('is_completed', true)->count(),
            'pending' => Activity::where('teacher_id', $teacherId)->where('is_completed', false)->count(),
        ];

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return view('teacher.activities', compact('activities', 'types', 'stats', 'days'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Lesson,Craft,Music,Story,Movement',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'group_name' => 'nullable|string|max:255',
        ]);

        Activity::create([
            'title' => $request->title,
            'type' => $request->type,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'group_name' => $request->group_name,
            'teacher_id' => auth()->id(),
            'week_start' => Carbon::now()->startOfWeek(),
        ]);

        return redirect()->route('teacher.activities.index')->with('success', 'Activity created!');
    }

    public function toggleComplete(Activity $activity)
    {
        if ($activity->teacher_id !== auth()->id()) abort(403);
        $activity->update(['is_completed' => !$activity->is_completed]);
        return redirect()->route('teacher.activities.index')->with('success', 'Activity updated!');
    }

    public function destroy(Activity $activity)
    {
        if ($activity->teacher_id !== auth()->id()) abort(403);
        $activity->delete();
        return redirect()->route('teacher.activities.index')->with('success', 'Activity deleted!');
    }
}
