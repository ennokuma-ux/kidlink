@props(['topic', 'current'])

@php
    $games = \App\Games\GameCatalog::gamesForTopic($topic);
    $switcherStudent = \App\Models\Student::where('user_id', auth()->id())->first();
    if ($switcherStudent) {
        $allowedSlugs = \App\Support\GameAssignmentAccess::slugsFor($switcherStudent);
        $games = array_values(array_filter($games, fn ($g) => in_array($g['slug'], $allowedSlugs, true) || $g['slug'] === $current));
    }
@endphp

@if(count($games))
<div class="mb-5">
    <p class="text-xs font-extrabold uppercase tracking-widest text-[#6b7280] mb-2">{{ __('game.switch_label') }}</p>
    <div class="flex flex-wrap items-center gap-2">
        @foreach($games as $game)
            @if($game['slug'] === $current)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-extrabold text-white shadow-sm" style="background: {{ $game['gradient'] }};">
                    <i class="ico ico-{{ $game['icon'] }}" aria-hidden="true"></i>
                    {{ $game['name'] }}
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
            @else
                <a href="{{ route('student.games.' . $game['slug'] . '.stage-select') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-extrabold text-[#5f6873] bg-white border-2 border-[#f1e7cc] hover:bg-[#182438] hover:text-white hover:border-[#182438] transition">
                    <i class="ico ico-{{ $game['icon'] }}" style="color: {{ preg_match('/#[0-9a-fA-F]{3,8}/', $game['gradient'], $switcherHex) ? $switcherHex[0] : 'currentColor' }};" aria-hidden="true"></i>
                    {{ $game['name'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
@endif