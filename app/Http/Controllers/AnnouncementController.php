<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementPoll;
use App\Models\AnnouncementPollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Throwable;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    private const MANAGER_ROLES = ['super_admin', 'admin', 'teacher'];

    /**
     * Every role can read the board. Only admins and teachers can post, which
     * is enforced by the role middleware on the write routes.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // What counts as "new" is fixed before we mark the board as seen, so
        // the page can still highlight what the user hadn't read yet.
        $seenCutoff = Announcement::seenCutoffFor($user);

        $announcements = Announcement::active()
            ->with(['author', 'comments.author', 'poll.options'])
            ->boardOrder()
            ->get();

        $user->forceFill(['announcements_seen_at' => now()])->saveQuietly();

        $pollData = $this->pollData($announcements, $user->id);

        return view('announcements.index', compact('announcements', 'seenCutoff', 'pollData'));
    }

    /**
     * Archived announcements. Parents never get here: the route sits behind the
     * admin/teacher role middleware, and the role is checked again.
     */
    public function archived(Request $request)
    {
        $this->authorizeManager($request);

        $announcements = Announcement::archived()
            ->with(['author', 'archiver', 'comments.author', 'poll.options'])
            ->orderBy('archived_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $pollData = $this->pollData($announcements, $request->user()->id);

        return view('announcements.archived', compact('announcements', 'pollData'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $withPoll = $request->boolean('poll_enabled');

        // Blank option boxes are simply ignored rather than failing the form.
        if ($withPoll) {
            $request->merge([
                'poll_options' => collect((array) $request->input('poll_options', []))
                    ->map(fn ($option) => is_string($option) ? trim($option) : '')
                    ->filter(fn ($option) => $option !== '')
                    ->values()
                    ->all(),
            ]);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'tag' => ['nullable', 'string', Rule::in(array_keys(Announcement::TAGS))],
            'is_pinned' => 'nullable|boolean',
            'poll_enabled' => 'nullable|boolean',
        ];

        if ($withPoll) {
            $rules += [
                'poll_question' => 'required|string|max:255',
                'poll_options' => ['required', 'array', 'min:'.AnnouncementPoll::MIN_OPTIONS, 'max:'.AnnouncementPoll::MAX_OPTIONS],
                'poll_options.*' => 'string|max:150|distinct:ignore_case',
                'poll_multiple' => 'nullable|boolean',
                'poll_closes_at' => 'nullable|date|after:now',
            ];
        }

        $validated = $request->validate($rules, [
            'title.required' => __('Please enter an announcement title.'),
            'title.max' => __('The announcement title cannot exceed 255 characters.'),
            'body.required' => __('Please write the announcement before publishing.'),
            'body.max' => __('The announcement cannot exceed 5,000 characters.'),
            'tag.in' => __('Please choose a valid announcement tag.'),
            'poll_question.required' => __('Enter the question for your poll.'),
            'poll_options.required' => __('Add at least :min options to the poll.', ['min' => AnnouncementPoll::MIN_OPTIONS]),
            'poll_options.min' => __('Add at least :min options to the poll.', ['min' => AnnouncementPoll::MIN_OPTIONS]),
            'poll_options.max' => __('A poll can have at most :max options.', ['max' => AnnouncementPoll::MAX_OPTIONS]),
            'poll_options.*.distinct' => __('Each poll option must be different.'),
            'poll_closes_at.after' => __('The poll must close at a time in the future.'),
        ]);

        $pinned = $request->boolean('is_pinned');

        try {
            $announcement = DB::transaction(function () use ($request, $validated, $pinned, $withPoll) {
                $announcement = Announcement::create([
                    'author_id' => $request->user()->id,
                    'title' => trim($validated['title']),
                    'body' => trim($validated['body']),
                    'tag' => ($validated['tag'] ?? null) ?: null,
                    'is_pinned' => $pinned,
                    'pinned_at' => $pinned ? now() : null,
                ]);

                if ($withPoll) {
                    $poll = $announcement->poll()->create([
                        'question' => trim($validated['poll_question']),
                        'allow_multiple' => $request->boolean('poll_multiple'),
                        'closes_at' => ! empty($validated['poll_closes_at']) ? $validated['poll_closes_at'] : null,
                    ]);

                    foreach (array_values($validated['poll_options']) as $position => $label) {
                        $poll->options()->create(['label' => trim($label), 'position' => $position]);
                    }
                }

                return $announcement;
            });
        } catch (QueryException $exception) {
            Log::error('Announcement publishing failed because of a database error.', [
                'user_id' => $request->user()->id,
                'sql_state' => $exception->errorInfo[0] ?? null,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput()->with(
                'error',
                'Your announcement was not saved because the announcement database is not up to date. Please ask the administrator to run the pending database migrations, then try again.'
            );
        } catch (Throwable $exception) {
            Log::error('Announcement publishing failed.', [
                'user_id' => $request->user()->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Your announcement could not be published. Please check your connection and try again. If the problem continues, contact an administrator.');
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement published successfully.');
    }

    /**
     * Pin an announcement to the top of the board, or take it off again. Any
     * teacher or admin can do it, like archiving.
     */
    public function pin(Request $request, Announcement $announcement)
    {
        $this->authorizeManager($request);

        abort_if($announcement->isArchived(), 404);

        $pinning = ! $announcement->is_pinned;

        $announcement->forceFill([
            'is_pinned' => $pinning,
            'pinned_at' => $pinning ? now() : null,
        ])->save();

        return redirect()->to(route('announcements.index').'#announcement-'.$announcement->id)
            ->with('success', $pinning ? __('Announcement pinned.') : __('Announcement unpinned.'));
    }

    /**
     * Archiving takes an announcement off the board without deleting it. Any
     * teacher or admin can do it; parents and students never can.
     */
    public function archive(Request $request, Announcement $announcement)
    {
        $this->authorizeManager($request);

        if (! $announcement->isArchived()) {
            // An archived announcement leaves the board, so it cannot stay pinned.
            $announcement->forceFill([
                'archived_at' => now(),
                'archived_by' => $request->user()->id,
                'is_pinned' => false,
                'pinned_at' => null,
            ])->save();
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement archived.');
    }

    /**
     * Everything the poll cards need, keyed by announcement id: the totals per
     * option, how many people voted, and what the current user picked. Done in
     * two grouped queries so a long board is not one query per poll.
     *
     * @return array<int, array{poll: AnnouncementPoll, closed: bool, counts: array<int,int>, voters: int, mine: array<int,int>}>
     */
    private function pollData($announcements, int $userId): array
    {
        $polls = $announcements->pluck('poll')->filter()->values();

        if ($polls->isEmpty()) {
            return [];
        }

        $pollIds = $polls->pluck('id')->all();

        $counts = AnnouncementPollVote::query()
            ->whereIn('announcement_poll_id', $pollIds)
            ->selectRaw('announcement_poll_option_id, COUNT(*) as total')
            ->groupBy('announcement_poll_option_id')
            ->pluck('total', 'announcement_poll_option_id');

        $voters = AnnouncementPollVote::query()
            ->whereIn('announcement_poll_id', $pollIds)
            ->selectRaw('announcement_poll_id, COUNT(DISTINCT user_id) as total')
            ->groupBy('announcement_poll_id')
            ->pluck('total', 'announcement_poll_id');

        $mine = AnnouncementPollVote::query()
            ->whereIn('announcement_poll_id', $pollIds)
            ->where('user_id', $userId)
            ->get(['announcement_poll_id', 'announcement_poll_option_id'])
            ->groupBy('announcement_poll_id');

        $data = [];

        foreach ($announcements as $announcement) {
            $poll = $announcement->poll;

            if (! $poll) {
                continue;
            }

            $poll->setRelation('announcement', $announcement);

            $data[$announcement->id] = [
                'poll' => $poll,
                'closed' => $poll->isClosed(),
                'counts' => $poll->options->mapWithKeys(fn ($option) => [$option->id => (int) ($counts[$option->id] ?? 0)])->all(),
                'voters' => (int) ($voters[$poll->id] ?? 0),
                'mine' => ($mine[$poll->id] ?? collect())->pluck('announcement_poll_option_id')->map(fn ($id) => (int) $id)->all(),
            ];
        }

        return $data;
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role, self::MANAGER_ROLES, true),
            403,
            'Only teachers and admins can manage announcements.'
        );
    }
}
