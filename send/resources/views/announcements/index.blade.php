@extends('layouts.announcements')

@section('title', __('announcements.announcements'))
@section('page-title', __('announcements.announcements'))

@section('content')
@php
    $canPost = in_array(auth()->user()->role, ['super_admin', 'admin', 'teacher'], true);
@endphp

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">{{ __('announcements.announcements') }}</h2>
            <p class="text-sm text-[#5f6873] mt-1">
                {{ $canPost
                    ? __('announcements.post_updates_description')
                    : __('announcements.updates_from_description') }}
            </p>
        </div>
        @if($canPost)
            <a href="{{ route('announcements.create') }}" class="bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
                {{ __('announcements.new_announcement_button') }}
            </a>
        @endif
    </div>
</div>

@if(!$canPost)
    <div class="bg-[#fff8f2] border border-[#ffe0cc] rounded-xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-[#ff8555] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-[#8a5a3b]">{{ __('announcements.announcements_posted_info') }}</p>
    </div>
@endif

<div class="space-y-4">
    @forelse($announcements as $announcement)
        @php
            $user = auth()->user();
            $isModerator = in_array($user->role, ['super_admin', 'admin'], true);
            // Teachers and admins can delete any announcement; parents never see the button.
            $canRemove = $canPost;
            $authorName = $announcement->author?->full_name ?? __('announcements.removed_account');
            $commentCount = $announcement->comments->count();
            $startOpen = (int) session('open_announcement') === $announcement->id;
        @endphp
        <article id="announcement-{{ $announcement->id }}" x-data="{ open: @js($startOpen) }"
                 class="bg-white rounded-xl shadow-sm overflow-hidden border border-transparent transition"
                 :class="open ? 'border-[#ffd9c4]' : ''">
            {{-- The whole announcement is this bar; click it to read the rest --}}
            <div class="flex items-stretch">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                        :title="open ? @js(__('announcements.read_less')) : @js(__('announcements.read_more'))"
                        class="flex-1 min-w-0 flex items-center gap-3 sm:gap-4 p-4 text-left hover:bg-[#f3f6fb] transition">
                    <span class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">
                        {{ strtoupper(mb_substr($authorName, 0, 1)) }}
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="font-semibold text-[#182438] truncate">{{ $announcement->title }}</span>
                            @if($commentCount)
                                <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-[#eef2f8] px-2 py-0.5 text-[11px] font-semibold text-[#5f6873]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5m-9 6l3-3h11a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v14z"/></svg>
                                    {{ $commentCount }}
                                </span>
                            @endif
                        </span>
                        <span class="block text-xs text-[#8b949d] mt-0.5 truncate">
                            {{ $authorName }}
                            @if($announcement->author)
                                &middot; {{ ucfirst(str_replace('_', ' ', $announcement->author->role)) }}
                            @endif
                            &middot; <time datetime="{{ $announcement->created_at?->toIso8601String() }}">{{ $announcement->created_at?->diffForHumans() }}</time>
                        </span>
                        <span x-show="!open" class="block text-sm text-[#5f6873] mt-1 truncate">{{ $announcement->body }}</span>
                    </span>
                    <svg class="w-5 h-5 shrink-0 text-[#8b949d] transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                @if($canRemove)
                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}"
                          data-confirm="{{ __('announcements.remove_this_announcement') }}" data-confirm-title="{{ __('announcements.remove_title') }}"
                          data-confirm-yes="{{ __('announcements.remove') }}" data-confirm-no="{{ __('announcements.cancel') }}"
                          class="shrink-0 flex items-center px-3 border-l border-[#ebe8e1]">
                        @csrf @method('DELETE')
                        <button type="submit" title="{{ __('announcements.remove') }}" aria-label="{{ __('announcements.remove') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-[#f05d56] hover:bg-[#fdecea] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span class="hidden sm:inline">{{ __('announcements.remove') }}</span>
                        </button>
                    </form>
                @endif
            </div>

            {{-- Full message and the conversation under it --}}
            <div x-show="open" x-cloak style="display:none;" class="px-5 pb-5 pt-4 border-t border-[#ebe8e1]">
                <p class="text-sm text-[#182438] whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>

                <div class="mt-5 pt-4 border-t border-dashed border-[#ebe8e1]">
                    <h4 class="text-sm font-semibold text-[#182438] mb-3">
                        {{ __('announcements.comments') }}
                        <span class="text-[#8b949d] font-normal">({{ $commentCount }})</span>
                    </h4>

                    <div class="space-y-3">
                        @forelse($announcement->comments as $comment)
                            @php
                                $commentAuthor = $comment->author?->full_name ?? __('announcements.removed_account');
                                $canRemoveComment = $comment->user_id === $user->id || $announcement->author_id === $user->id || $isModerator;
                            @endphp
                            <div class="flex items-start gap-3">
                                <span class="w-8 h-8 rounded-full bg-[#258cc5] text-white text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($commentAuthor, 0, 1)) }}</span>
                                <div class="flex-1 min-w-0 rounded-xl bg-[#eef2f8] px-4 py-2.5">
                                    <p class="text-xs text-[#8b949d]">
                                        <span class="font-semibold text-[#182438]">{{ $commentAuthor }}</span>
                                        @if($comment->author) &middot; {{ ucfirst(str_replace('_', ' ', $comment->author->role)) }} @endif
                                        &middot; {{ $comment->created_at?->diffForHumans() }}
                                    </p>
                                    <p class="text-sm text-[#182438] mt-1 whitespace-pre-line break-words">{{ $comment->body }}</p>
                                </div>
                                @if($canRemoveComment)
                                    <form method="POST" action="{{ route('announcements.comments.destroy', [$announcement, $comment]) }}"
                                          data-confirm="{{ __('announcements.remove_comment_confirm') }}" data-confirm-title="{{ __('announcements.remove_comment_title') }}"
                                          data-confirm-yes="{{ __('announcements.remove') }}" data-confirm-no="{{ __('announcements.cancel') }}" class="shrink-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="{{ __('announcements.remove_comment') }}" aria-label="{{ __('announcements.remove_comment') }}"
                                                class="w-7 h-7 rounded-lg text-[#8b949d] hover:text-[#f05d56] hover:bg-[#fdecea] transition">&times;</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[#8b949d]">{{ __('announcements.no_comments') }}</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('announcements.comments.store', $announcement) }}" class="mt-4 flex flex-col sm:flex-row gap-2">
                        @csrf
                        <textarea name="body" rows="2" maxlength="1000" required placeholder="{{ __('announcements.add_comment_placeholder') }}"
                                  class="flex-1 rounded-lg border border-[#ebe8e1] px-3 py-2 text-sm focus:ring-2 focus:ring-[#ff8555] outline-none resize-y"></textarea>
                        <button type="submit" class="self-end sm:self-stretch px-5 py-2 rounded-lg bg-[#ff8555] text-white text-sm font-semibold hover:bg-[#f47548] transition">{{ __('announcements.post_comment') }}</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-4 text-[#ebe8e1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <p class="text-[#5f6873]">{{ __('announcements.no_announcements_yet') }}</p>
            @if($canPost)
                <a href="{{ route('announcements.create') }}" class="inline-block mt-4 text-[#ff8555] hover:text-[#f47548] font-medium text-sm">{{ __('announcements.post_the_first_one') }}</a>
            @endif
        </div>
    @endforelse
</div>
@endsection
