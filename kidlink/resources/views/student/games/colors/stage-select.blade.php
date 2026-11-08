@extends('layouts.app')

@section('title', __('game.colors_stage_title'))

@push('styles')
<style>
    @import url('/css/fonts.css');
    
    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }
    
    .stage-map {
        background: linear-gradient(135deg, #fef7ff 0%, #ffe9ea 25%, #fff1f1 50%, #ffe9ea 75%, #fef7ff 100%);
        background-size: 200% 200%;
        animation: gradientShift 20s ease infinite;
        min-height: 100vh;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .map-container {
        position: relative;
        width: 100%;
        aspect-ratio: 12/7;
        max-width: 100%;
        border-radius: 24px;
        border: 4px solid #ffc9cb;
        background: #ffe9ea;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(236, 72, 153, 0.15);
    }
    
    .map-bg {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, #ffe9ea 0%, #ffc9cb 100%);
    }
    
    .map-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.9;
    }
    
    .decorative-img {
        position: absolute;
        pointer-events: none;
    }
    
    .tree-img {
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    }
    
    .decoration-img {
        animation: float 4s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-8px) rotate(3deg); }
    }
    
    .path-svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    
    .level-node {
        position: absolute;
        z-index: 20;
        transition: transform 0.2s ease;
        transform: translate(-50%, -50%);
    }
    
    .level-node:hover {
        transform: translate(-50%, -50%) scale(1.15);
    }
    
    .level-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: 4px solid white;
        font-family: 'Fredoka One', cursive;
        font-size: 1.25rem;
        font-weight: 700;
        color: white;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }
    
    .level-node:hover .level-btn {
        transform: scale(1.15);
    }
    
    .level-btn.unlocked {
        border-color: white;
        color: white;
    }
    
    .level-btn.locked {
        border-color: #ffc9cb;
        background: #f0eef5;
        color: #c4b5c7;
        cursor: not-allowed;
    }
    
    .level-btn.locked:hover {
        transform: none;
    }
    
    .start-badge {
        position: absolute;
        background: #ff595e;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        z-index: 15;
    }
    
    .level-badge {
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.625rem;
        font-weight: 700;
        color: #ff595e;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        white-space: nowrap;
    }
    
    .locked-node {
        position: absolute;
        z-index: 20;
        transform: translate(-50%, -50%);
    }
    
    .locked-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: 4px solid #ffc9cb;
        background: #f0eef5;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        cursor: not-allowed;
    }
    
    .player-character {
        position: absolute;
        z-index: 25;
        transition: all 0.5s ease;
        transform: translate(-50%, -140%);
    }
    
    .player-indicator {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #ff8555;
        border: 3px solid white;
        box-shadow: 0 0 14px rgba(255, 133, 85, 0.6), 0 4px 8px rgba(0,0,0,0.2);
        animation: bounce 1s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    
    .flag-img {
        width: 28px;
        height: auto;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    
    .coming-soon {
        position: absolute;
        color: #f472b6;
        font-size: 0.75rem;
        font-weight: 700;
        text-align: center;
        white-space: nowrap;
        text-shadow: 0 1px 2px rgba(255,255,255,0.8);
    }
    
    .decor-img {
        animation: float 4s ease-in-out infinite;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-8px) rotate(3deg); }
    }
    
    .header-bar {
        background: white;
        border-radius: 16px;
        padding: 16px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid #f1e7cc;
    }
    
    .points-badge {
        background: white;
        border-radius: 9999px;
        padding: 8px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        font-weight: 700;
        color: #37474f;
    }

    .btn-back-map:hover {
        border-color: #ff8555 !important;
        color: #ff8555 !important;
        background: #fff8f5 !important;
    }
    
    @media (max-width: 768px) {
        .map-container { aspect-ratio: 4/3; }
    }
</style>
@endpush

@section('content')
{{-- Welcome mascot: Kido for boys, Linka for girls --}}
<x-mascot float bounce state="start" :size="230" />

<div class="min-h-screen bg-[#fff1f1] p-4 md:p-8">
    <div class="mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-6 header-bar">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('student.home') }}" class="btn-back-map" style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;background:white;border:1px solid #f1e7cc;border-radius:10px;font-weight:700;font-size:0.875rem;color:#5f6873;cursor:pointer;transition:all 0.2s ease;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        {{ __('game.games') }}
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ __('game.colors_name') }}</h1>
                        <p class="text-sm text-[#5f6873]">{{ __('game.colors_tagline') }}</p>
                    </div>
                </div>
                <div class="points-badge">
                    <span class="text-sm font-semibold text-[#37474f]"><img src="{{ asset('assets/icons/color-palette.svg') }}" alt="" class="inline h-4 w-4 mr-1" aria-hidden="true"> {{ __('game.stars', ['score' => $totalScore ?? 0]) }}</span>
                </div>
            </div>
        </div>

        <x-games-switcher topic="colors" current="colors" />

        <!-- Map Container -->
        <div class="map-container relative">
            <!-- Map Background -->
            <img src="{{ asset('assets/images/game/colors/background.png') }}" alt="{{ __('game.colors_map_alt') }}" class="map-image">
            
            <!-- Path -->
            <svg class="path-svg" viewBox="0 0 1200 700" preserveAspectRatio="none">
                <path
                    d="M 60 420
                       C 160 320, 60 200, 210 140
                       C 350 80, 420 160, 450 270
                       C 490 370, 390 440, 500 460
                       C 620 480, 680 410, 630 260
                       C 590 130, 720 120, 830 140"
                    fill="none"
                    stroke="#c84d91"
                    stroke-width="48"
                    stroke-linecap="round"
                />
                <path
                    d="M 60 420
                       C 160 320, 60 200, 210 140
                       C 350 80, 420 160, 450 280
                       C 490 370, 390 440, 500 460
                       C 620 480, 680 410, 630 260
                       C 590 130, 720 120, 830 140"
                    fill="none"
                    stroke="#f7e4f0"
                    stroke-width="38"
                    stroke-linecap="round"
                />
                <path
                    d="M 60 420
                       C 160 320, 60 200, 210 140
                       C 350 80, 420 160, 450 280
                       C 490 370, 390 440, 500 460
                       C 620 480, 680 410, 630 260
                       C 590 130, 720 120, 830 140"
                    fill="none"
                    stroke="#efebe9"
                    stroke-width="3"
                    stroke-dasharray="4 12"
                    stroke-linecap="round"
                />
            </svg>

            <!-- Levels -->
            @foreach($levels as $level)
                @if($level['unlocked'])
                    <a href="{{ route('student.games.colors.play', ['level' => $level['number']]) }}" class="level-node" style="left: {{ $level['left'] }}; top: {{ $level['top'] }};" title="{{ __('game.level_number', ['number' => $level['number']]) }}">
                        <img src="{{ asset('assets/images/game/colors/btn_' . $level['number'] . '.png') }}" alt="{{ __('game.level_number', ['number' => $level['number']]) }}" class="level-btn unlocked" style="width: 52px; height: 52px;">
                        <span class="level-badge">@if($level['number'] === 1)<i class="ico ico-graduation-cap" aria-hidden="true"></i> {{ __('tutorial_stage.badge') }}@else{{ __('game.level_number', ['number' => $level['number']]) }}@endif</span>
                    </a>
                @else
                    <div class="locked-node" style="left: {{ $level['left'] }}; top: {{ $level['top'] }};" title="{{ __('game.level_locked_title', ['number' => $level['number']]) }}">
                        <img src="{{ asset('assets/images/game/colors/btn_locked.png') }}" alt="{{ __('game.locked') }}" class="locked-btn" style="width: 52px; height: 52px;">
                        <span class="level-badge" style="bottom: -20px; color: #c4b5c7;">{{ __('game.locked') }}</span>
                    </div>
                @endif
            @endforeach

            <!-- Player Character -->
            <div class="player-character player-current-level" id="playerCharacter" style="left: 9.19%; top: 44.89%;">
                <div class="player-indicator"></div>
            </div>

            <!-- Flag -->
            <div class="absolute right-[30%] top-[16%] z-20">
                <img src="{{ asset('assets/images/game/colors/btn_8.png') }}" alt="{{ __('game.finish') }}" class="flag-img" style="width: 28px; height: 28px;">
            </div>

            <!-- Coming Soon -->
            <div class="coming-soon absolute right-[20%] top-[10%]">
                {{ __('game.colors_coming_soon') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Animate player along the path based on current level
    document.addEventListener('DOMContentLoaded', function() {
        const player = document.getElementById('playerCharacter');
        if (!player) return;

        // Level positions (percentage based on map container) - aligned to SVG path
        const levelPositions = {
            1: { left: '9.19%', top: '44.89%' },
            2: { left: '14.74%', top: '22.36%' },
            3: { left: '28.98%', top: '18.39%' },
            4: { left: '37.46%', top: '38.34%' },
            5: { left: '38.03%', top: '63.16%' },
            6: { left: '51.87%', top: '61.66%' },
            7: { left: '52.52%', top: '37.22%' },
            8: { left: '59.27%', top: '19.2%' },
        };

        // Get current level from data attribute or URL
        const currentLevel = {{ $currentLevel ?? 1 }};
        
        if (levelPositions[currentLevel]) {
            const pos = levelPositions[currentLevel];
            document.getElementById('playerCharacter').style.left = pos.left;
            document.getElementById('playerCharacter').style.top = pos.top;
        }
    });
</script>
@endpush