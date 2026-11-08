<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use Illuminate\Http\Request;

class AnnouncementCommentController extends Controller
{
    /**
     * Everyone who can read the board can reply to an announcement.
     */
    public function store(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $announcement->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ]);

        return redirect()->to(route('announcements.index').'#announcement-'.$announcement->id)
            ->with('open_announcement', $announcement->id);
    }

    /**
     * A comment can be removed by whoever wrote it, by the announcement's
     * author, and by admins.
     */
    public function destroy(Request $request, Announcement $announcement, AnnouncementComment $comment)
    {
        abort_unless($comment->announcement_id === $announcement->id, 404);

        $user = $request->user();
        $allowed = $comment->user_id === $user->id
            || $announcement->author_id === $user->id
            || in_array($user->role, ['super_admin', 'admin'], true);

        abort_unless($allowed, 403, 'You may not remove this comment.');

        $comment->delete();

        return redirect()->to(route('announcements.index').'#announcement-'.$announcement->id)
            ->with('open_announcement', $announcement->id);
    }
}
