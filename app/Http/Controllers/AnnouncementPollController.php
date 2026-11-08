<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementPollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementPollController extends Controller
{
    /**
     * Everyone who can read the board can vote. Voting again replaces the
     * earlier answer until the poll closes, so a mis-tap is never final.
     */
    public function vote(Request $request, Announcement $announcement)
    {
        abort_if($announcement->isArchived(), 404);

        $poll = $announcement->poll;
        abort_if($poll === null, 404);
        $poll->setRelation('announcement', $announcement);

        $back = redirect()->to(route('announcements.index').'#announcement-'.$announcement->id)
            ->with('open_announcement', $announcement->id);

        if ($poll->isClosed()) {
            return $back->with('error', __('This poll has closed.'));
        }

        $picked = collect((array) $request->input('options', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($picked->isEmpty()) {
            return $back->with('error', __('Choose an option before voting.'));
        }

        if (! $poll->allow_multiple && $picked->count() > 1) {
            return $back->with('error', __('You can only choose one option in this poll.'));
        }

        // Only options that belong to this poll are accepted.
        $valid = $poll->options()->whereIn('id', $picked->all())->pluck('id');

        if ($valid->count() !== $picked->count()) {
            return $back->with('error', __('That option is not part of this poll.'));
        }

        $userId = $request->user()->id;

        DB::transaction(function () use ($poll, $valid, $userId) {
            AnnouncementPollVote::where('announcement_poll_id', $poll->id)
                ->where('user_id', $userId)
                ->delete();

            foreach ($valid as $optionId) {
                AnnouncementPollVote::create([
                    'announcement_poll_id' => $poll->id,
                    'announcement_poll_option_id' => $optionId,
                    'user_id' => $userId,
                ]);
            }
        });

        return $back->with('success', __('Your vote has been recorded.'));
    }
}
