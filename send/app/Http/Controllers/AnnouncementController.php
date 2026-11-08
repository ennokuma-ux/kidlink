<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Every role can read the board. Only admins and teachers can post, which
     * is enforced by the role middleware on the write routes.
     */
    public function index()
    {
        $announcements = Announcement::with(['author', 'comments.author'])->newestFirst()->get();

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

        Announcement::create([
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement published.');
    }

    /**
     * Any teacher or admin can remove an announcement. Parents and students
     * never can: the route is behind the same role middleware as posting, and
     * the role is checked again here.
     */
    public function destroy(Request $request, Announcement $announcement)
    {
        if (! in_array($request->user()->role, ['super_admin', 'admin', 'teacher'], true)) {
            abort(403, 'Only teachers and admins can remove announcements.');
        }

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement removed.');
    }
}
