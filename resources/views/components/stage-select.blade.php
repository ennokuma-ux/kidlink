@props([
    'game',            // game slug, e.g. 'numbers'
    'topic',           // switcher topic, e.g. 'numbers'
    'lang',            // lang key prefix in lang/*/game.php, e.g. 'whats_missing'
    'bg',              // folder in public/assets/images/game that holds background.png
    'levels' => [],
    'currentLevel' => 1,
    'totalScore' => 0,
    'tagline' => null, // override for games whose tagline lives outside lang/game.php
])

@php
    $theme = \App\Support\StageMap::theme($game);
    $catalogGame = \App\Games\GameCatalog::game($game);
@endphp

@push('styles')
<style>
    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .ss-page {
        --ss-accent: {{ $theme['accent'] }};
        --ss-dark: {{ $theme['dark'] }};
        --ss-edge: {{ $theme['edge'] }};
        --ss-road: {{ $theme['road'] }};
        min-height: 100vh;
        background: {{ $theme['page'] }};
    }
    .ss-header {
        background: white;
        border-radius: 16px;
        padding: 16px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid #f1e7cc;
    }
    .ss-title-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; border-radius: 14px; color: #fff;
        background: var(--ss-accent); font-size: 1.6rem; flex: none;
    }
    .ss-points {
        background: white; border-radius: 9999px; padding: 8px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08); font-weight: 700; color: #37474f;
    }
    .ss-back {
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px;
        background: white; border: 1px solid #f1e7cc; border-radius: 10px;
        font-weight: 700; font-size: 0.875rem; color: #5f6873; transition: all .2s ease;
    }
    .ss-back:hover { border-color: #ff8555; color: #ff8555; background: #fff8f5; }

    .ss-map {
        position: relative;
        width: 100%;
        aspect-ratio: 12/7;
        border-radius: 24px;
        border: 4px solid {{ $theme['frame'] }};
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        background: {{ $theme['road'] }};
    }
    .ss-map-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ss-road { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }

    /* One level button. Its centre is the point on the road. */
    .ss-node {
        position: absolute; z-index: 20; transform: translate(-50%, -50%);
        display: flex; flex-direction: column; align-items: center; text-decoration: none;
    }
    .ss-btn {
        position: relative; display: flex; align-items: center; justify-content: center;
        width: clamp(36px, 4.4vw, 58px); height: clamp(36px, 4.4vw, 58px);
        border-radius: 50%; border: 4px solid #fff; color: #fff;
        font-family: 'Fredoka One', cursive; font-size: clamp(1rem, 1.7vw, 1.45rem);
        box-shadow: 0 6px 14px rgba(0,0,0,0.25);
        transition: transform .2s ease;
    }
    .ss-node.is-open .ss-btn {
        background: radial-gradient(circle at 30% 28%, var(--ss-accent), var(--ss-dark));
    }
    .ss-node.is-open:hover .ss-btn { transform: scale(1.15); }
    .ss-node.is-current .ss-btn {
        animation: ssPulse 1.6s ease-in-out infinite;
    }
    .ss-check {
        position: absolute; right: -9px; bottom: -9px; width: 22px; height: 22px;
        border-radius: 50%; background: #22c55e; border: 2px solid #fff; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 12px;
    }
    .ss-node.is-locked { cursor: not-allowed; }
    .ss-node.is-locked .ss-btn {
        background: #cfd4da; border-color: #eef0f3; color: #8a929b; box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }
    .ss-node.is-locked .ico { font-size: clamp(1rem, 1.8vw, 1.5rem); }
    @keyframes ssPulse {
        0%, 100% { box-shadow: 0 6px 14px rgba(0,0,0,0.25), 0 0 0 0 color-mix(in srgb, var(--ss-accent) 55%, transparent); }
        50%      { box-shadow: 0 6px 14px rgba(0,0,0,0.25), 0 0 0 12px color-mix(in srgb, var(--ss-accent) 0%, transparent); }
    }
    .ss-label {
        margin-top: 6px; background: #fff; padding: 1px 9px; border-radius: 12px;
        font-size: 0.65rem; font-weight: 800; white-space: nowrap; color: var(--ss-dark);
        box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    }
    .ss-node.is-locked .ss-label { color: #8a929b; font-weight: 700; }

    /* "You are here" marker over the current level. */
    .ss-here {
        position: absolute; z-index: 25; transform: translate(-50%, -150%);
        pointer-events: none;
    }
    .ss-here span {
        display: block; width: 24px; height: 24px; border-radius: 50%;
        background: var(--ss-accent); border: 3px solid #fff;
        box-shadow: 0 0 14px color-mix(in srgb, var(--ss-accent) 60%, transparent), 0 4px 8px rgba(0,0,0,0.2);
        animation: ssBounce 1s ease-in-out infinite;
    }
    @keyframes ssBounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

    /* Finish flag above the last level. */
    .ss-flag {
        position: absolute; z-index: 21; transform: translate(-12%, -190%);
        font-size: clamp(1.4rem, 2.4vw, 2rem); color: var(--ss-dark);
        filter: drop-shadow(0 2px 3px rgba(0,0,0,0.25)); pointer-events: none;
    }
    @media (max-width: 768px) { .ss-map { aspect-ratio: 4/3; } }
</style>
@endpush

{{-- Welcome mascot: Kido for boys, Linka for girls --}}
<x-mascot float bounce state="start" :size="230" />

<div class="ss-page p-4 md:p-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 ss-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('student.home') }}" class="ss-back">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        {{ __('game.games') }}
                    </a>
                    <span class="ss-title-icon" aria-hidden="true"><i class="ico ico-{{ $catalogGame['icon'] ?? 'game-controller' }}"></i></span>
                    <div>
                        <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ __('game.'.$lang.'_name') }}</h1>
                        <p class="text-sm text-[#5f6873]">{{ $tagline ?? __('game.'.$lang.'_tagline') }}</p>
                    </div>
                </div>
                <div class="ss-points">
                    <span class="text-sm font-semibold text-[#37474f]"><i class="ico ico-star text-[#ffca3a]" aria-hidden="true"></i> {{ __('game.stars', ['score' => $totalScore ?? 0]) }}</span>
                </div>
            </div>
        </div>

        <x-games-switcher :topic="$topic" :current="$game" />

        <div class="ss-map">
            <img src="{{ asset('assets/images/game/'.$bg.'/background.webp') }}" alt="{{ __('game.'.$lang.'_map_alt') }}" class="ss-map-image" decoding="async">

            {{-- The road: it starts at level 1 and ends at the last level. --}}
            <svg class="ss-road" viewBox="0 0 1200 700" preserveAspectRatio="none" aria-hidden="true">
                <path d="{{ \App\Support\StageMap::PATH }}" fill="none" stroke="{{ $theme['edge'] }}" stroke-width="48" stroke-linecap="round"/>
                <path d="{{ \App\Support\StageMap::PATH }}" fill="none" stroke="{{ $theme['road'] }}" stroke-width="38" stroke-linecap="round"/>
                <path d="{{ \App\Support\StageMap::PATH }}" fill="none" stroke="#ffffff" stroke-opacity=".75" stroke-width="3" stroke-dasharray="4 12" stroke-linecap="round"/>
            </svg>

            @foreach($levels as $level)
                @php
                    $state = $level['current'] ? 'is-current' : ($level['completed'] ? 'is-done' : '');
                    $label = $level['number'] === 1
                        ? null
                        : __('game.level_number', ['number' => $level['number']]);
                @endphp
                @if($level['unlocked'])
                    <a href="{{ $level['route'] }}" class="ss-node is-open {{ $state }}" style="left: {{ $level['left'] }}; top: {{ $level['top'] }};" title="{{ __('game.level_number', ['number' => $level['number']]) }}">
                        <span class="ss-btn">{{ $level['number'] }}@if($level['completed'])<span class="ss-check"><i class="ico ico-checkmark" aria-hidden="true"></i></span>@endif</span>
                        <span class="ss-label">@if($level['number'] === 1)<i class="ico ico-graduation-cap" aria-hidden="true"></i> {{ __('tutorial_stage.badge') }}@else{{ $label }}@endif</span>
                    </a>
                @else
                    <div class="ss-node is-locked" style="left: {{ $level['left'] }}; top: {{ $level['top'] }};" title="{{ __('game.level_locked_title', ['number' => $level['number']]) }}">
                        <span class="ss-btn"><i class="ico ico-lock-closed" aria-hidden="true"></i></span>
                        <span class="ss-label">{{ __('game.locked') }}</span>
                    </div>
                @endif
            @endforeach

            @php
                $current = collect($levels)->firstWhere('current', true) ?? collect($levels)->first();
                $last = collect($levels)->last();
            @endphp
            @if($current)
                <div class="ss-here" style="left: {{ $current['left'] }}; top: {{ $current['top'] }};"><span></span></div>
            @endif
            @if($last)
                <i class="ico ico-flag ss-flag" style="left: {{ $last['left'] }}; top: {{ $last['top'] }};" role="img" aria-label="{{ __('game.finish') }}"></i>
            @endif
        </div>
    </div>
</div>
