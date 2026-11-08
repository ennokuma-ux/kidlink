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
        $announcements = Announcement::with('author')->newestFirst()->get();

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
     * An author can retract their own announcement; a super admin can remove
     * any of them.
     */
    public function destroy(Request $request, Announcement $announcement)
    {
        $user = $request->user();

        $isAuthor = $announcement->author_id === $user->id;
        $isSuperAdmin = $user->role === 'super_admin';

        if (!$isAuthor && !$isSuperAdmin) {
            abort(403, 'You may only remove your own announcements.');
        }

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement removed.');
    }
}
