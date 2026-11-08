@extends('layouts.announcements')

@section('title', __('Archived announcements'))
@section('page-title', __('Archived announcements'))

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">{{ __('Archived announcements') }}</h2>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('Announcements taken off the board. Only teachers and admins can see this list.') }}</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-2 border border-[#f1e7cc] text-[#5f6873] hover:bg-[#fff6e0] px-4 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            &larr; {{ __('Back to announcements') }}
        </a>
    </div>
</div>

<div class="space-y-4">
    @forelse($announcements as $announcement)
        @php
            $authorName = $announcement->author?->full_name ?? __('announcements.removed_account');
            $archiverName = $announcement->archiver?->full_name ?? __('announcements.removed_account');
            $commentCount = $announcement->comments->count();
            $tagStyle = $announcement->tagStyle();
            $pollInfo = $pollData[$announcement->id] ?? null;
        @endphp
        <article id="announcement-{{ $announcement->id }}" x-data="{ open: false }" class="bg-white rounded-xl shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                    class="w-full flex items-center gap-3 sm:gap-4 p-4 text-left hover:bg-[#fff6e0] transition">
                <span class="w-10 h-10 bg-[#8b949d] rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">
                    {{ strtoupper(mb_substr($authorName, 0, 1)) }}
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-center gap-2 min-w-0">
                        <span class="font-semibold text-[#182438] truncate">{{ $announcement->title }}</span>
                        <span class="shrink-0 rounded-full bg-[#fbf0d6] px-2 py-0.5 text-[11px] font-semibold text-[#5f6873]">{{ __('Archived') }}</span>
                        @if($tagStyle)
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide {{ $tagStyle['badge'] }}">{{ $announcement->tagLabel() }}</span>
                        @endif
                        @if($pollInfo)
                            <span class="shrink-0 rounded-full bg-[#e1f0f8] px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-[#1e73a8]">{{ __('Poll') }}</span>
                        @endif
                        @if($commentCount)
                            <span class="shrink-0 rounded-full bg-[#fbf0d6] px-2 py-0.5 text-[11px] font-semibold text-[#5f6873]">{{ $commentCount }} {{ __('announcements.comments') }}</span>
                        @endif
                    </span>
                    <span class="block text-xs text-[#8b949d] mt-0.5 truncate">
                        {{ $authorName }} &middot; {{ __('posted') }} {{ $announcement->created_at?->format('M j, Y') }}
                        &middot; {{ __('archived') }} {{ $announcement->archived_at?->format('M j, Y') }} {{ __('by') }} {{ $archiverName }}
                    </span>
                </span>
                <svg class="w-5 h-5 shrink-0 text-[#8b949d] transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" x-cloak style="display:none;" class="px-5 pb-5 pt-4 border-t border-[#f1e7cc]">
                <p class="text-sm text-[#182438] whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>

                @if($pollInfo)
                    @include('announcements.partials.poll', ['announcement' => $announcement, 'data' => $pollInfo, 'canVote' => false])
                @endif

                @if($commentCount)
                    <div class="mt-5 pt-4 border-t border-dashed border-[#f1e7cc] space-y-3">
                        <h4 class="text-sm font-semibold text-[#182438]">{{ __('announcements.comments') }} <span class="text-[#8b949d] font-normal">({{ $commentCount }})</span></h4>
                        @foreach($announcement->comments as $comment)
                            @php $commentAuthor = $comment->author?->full_name ?? __('announcements.removed_account'); @endphp
                            <div class="flex items-start gap-3">
                                <span class="w-8 h-8 rounded-full bg-[#1982c4] text-white text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($commentAuthor, 0, 1)) }}</span>
                                <div class="flex-1 min-w-0 rounded-xl bg-[#fbf0d6] px-4 py-2.5">
                                    <p class="text-xs text-[#8b949d]"><span class="font-semibold text-[#182438]">{{ $commentAuthor }}</span> &middot; {{ $comment->created_at?->diffForHumans() }}</p>
                                    <p class="text-sm text-[#182438] mt-1 whitespace-pre-line break-words">{{ $comment->body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-[#5f6873]">{{ __('No archived announcements.') }}</p>
        </div>
    @endforelse
</div>
@endsection
