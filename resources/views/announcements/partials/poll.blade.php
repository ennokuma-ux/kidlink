{{--
    One announcement's poll.

    $announcement  the announcement the poll belongs to
    $data          the entry for it from AnnouncementController::pollData()
    $canVote       false on the archive, where polls are read-only
--}}
@php
    $poll = $data['poll'];
    $closed = $data['closed'] || ! $canVote;
    $mine = $data['mine'];
    $voters = $data['voters'];
    $hasVoted = count($mine) > 0;
    $startOnResults = $hasVoted || $closed;
    $inputType = $poll->allow_multiple ? 'checkbox' : 'radio';
@endphp

<div class="mt-5 rounded-xl border border-[#f1e7cc] bg-[#fffbf0] p-4"
     x-data="{ results: @js($startOnResults) }">
    <div class="flex items-start gap-3">
        <span class="mt-0.5 w-8 h-8 rounded-lg bg-[#6a4c93] text-white flex items-center justify-center shrink-0" aria-hidden="true">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6m6 13V10M3 19V13m18 6V3"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#6a4c93]">{{ __('Poll') }}</p>
            <h4 class="font-semibold text-[#182438] break-words">{{ $poll->question }}</h4>
            <p class="text-xs text-[#8b949d] mt-0.5">
                {{ $poll->allow_multiple ? __('Choose all that apply') : __('Choose one') }}
                @if($poll->closes_at)
                    &middot;
                    @if($poll->closes_at->isPast())
                        {{ __('Closed') }} {{ $poll->closes_at->format('M j, Y g:i A') }}
                    @else
                        {{ __('Closes') }} {{ $poll->closes_at->format('M j, Y g:i A') }}
                    @endif
                @elseif($closed)
                    &middot; {{ __('Closed') }}
                @endif
            </p>
        </div>
    </div>

    {{-- Voting --}}
    @unless($closed)
        <form x-show="!results" @if($startOnResults) style="display:none;" @endif method="POST"
              action="{{ route('announcements.poll.vote', $announcement) }}" class="mt-4 space-y-2">
            @csrf
            @foreach($poll->options as $option)
                <label class="flex items-center gap-3 rounded-lg border border-[#f1e7cc] bg-white px-4 py-2.5 cursor-pointer hover:border-[#ff8555] hover:bg-[#fff6f0] transition has-[:checked]:border-[#ff8555] has-[:checked]:bg-[#fff6f0]">
                    <input type="{{ $inputType }}" name="options[]" value="{{ $option->id }}"
                           @checked(in_array($option->id, $mine, true))
                           class="w-4 h-4 text-[#ff8555] focus:ring-[#ff8555] {{ $poll->allow_multiple ? 'rounded' : '' }}">
                    <span class="text-sm text-[#182438] break-words">{{ $option->label }}</span>
                </label>
            @endforeach
            <div class="flex flex-wrap items-center gap-3 pt-1">
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#ff8555] text-white text-sm font-semibold hover:bg-[#f47548] transition">
                    {{ $hasVoted ? __('Update my vote') : __('Vote') }}
                </button>
                <button type="button" @click="results = true" class="text-sm font-semibold text-[#6a4c93] hover:underline">{{ __('See results') }}</button>
            </div>
        </form>
    @endunless

    {{-- Results --}}
    <div x-show="results" @unless($startOnResults) style="display:none;" @endunless class="mt-4 space-y-3">
        @foreach($poll->options as $option)
            @php
                $count = $data['counts'][$option->id] ?? 0;
                $pct = $voters > 0 ? (int) round($count / $voters * 100) : 0;
                $isMine = in_array($option->id, $mine, true);
            @endphp
            <div>
                <div class="flex items-baseline justify-between gap-3 text-sm">
                    <span class="text-[#182438] break-words min-w-0">
                        {{ $option->label }}
                        @if($isMine)
                            <span class="ml-1 inline-block rounded-full bg-[#ecf7d6] px-2 py-0.5 text-[11px] font-semibold text-[#2f7a1a] align-middle">{{ __('Your vote') }}</span>
                        @endif
                    </span>
                    <span class="shrink-0 tabular-nums text-[#5f6873]">{{ $count }} &middot; {{ $pct }}%</span>
                </div>
                <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-[#f1e7cc]" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="{{ $option->label }}">
                    <div class="h-full rounded-full {{ $isMine ? 'bg-[#8ac926]' : 'bg-[#6a4c93]' }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @endforeach
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <p class="text-xs text-[#8b949d]">
                {{ $voters === 1 ? __('1 person voted') : __(':count people voted', ['count' => $voters]) }}
            </p>
            @unless($closed)
                <button type="button" @click="results = false" class="text-sm font-semibold text-[#6a4c93] hover:underline">
                    {{ $hasVoted ? __('Change my vote') : __('Back to voting') }}
                </button>
            @endunless
        </div>
    </div>
</div>
