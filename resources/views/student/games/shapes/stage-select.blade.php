@extends('layouts.app')

@section('title', __('game.shapes_stage_title'))

@push('styles')
<style>
    @import url('/css/fonts.css');
    
    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }
    
    .stage-map {
        background: linear-gradient(135deg, #f4fae6 0%, #e8f5e9 25%, #f1f8e9 50%, #e8f5e9 75%, #f4fae6 100%);
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
        border: 4px solid #a5d6a7;
        background: #b9dc8d;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(76, 175, 80, 0.2);
    }
    
    .map-bg {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, #dff0c8 0%, #8bc75c 100%);
    }
    
    .map-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.95;
    }
    
    .decorative-blob {
        position: absolute;
        border-radius: 50%;
        opacity: 0.6;
    }
    
    .blob-1 { width: 80px; height: 80px; background: #c9e4a8; top: 3%; left: 2%; }
    .blob-2 { width: 120px; height: 120px; background: #d8edbc; top: 5%; right: 3%; }
    .blob-3 { width: 100px; height: 100px; background: #75ad4d; bottom: 5%; left: 3%; }
    .blob-4 { width: 80px; height: 80px; background: #74ad50; bottom: 8%; right: 5%; }
    
    .tree {
        position: absolute;
        z-index: 10;
    }
    
    .tree-trunk {
        width: 10px;
        height: 30px;
        background: #7c5939;
        border-radius: 2px;
        margin: 0 auto;
    }
    
    .tree-leaves {
        position: absolute;
        background: #2e7d32;
        border-radius: 50%;
    }
    
    .leaves-1 { width: 30px; height: 30px; top: -20px; left: -12px; }
    .leaves-2 { width: 30px; height: 30px; top: -25px; left: 4px; }
    .leaves-3 { width: 24px; height: 24px; top: -15px; left: 14px; }
    
    .tree-large .tree-trunk { width: 12px; height: 36px; }
    .tree-large .leaves-1 { width: 36px; height: 36px; top: -24px; left: -14px; }
    .tree-large .leaves-2 { width: 36px; height: 36px; top: -30px; left: 6px; }
    
    .tree-medium .tree-trunk { width: 10px; height: 30px; }
    .tree-medium .leaves-1 { width: 30px; height: 30px; top: -20px; left: -12px; }
    .tree-medium .leaves-2 { width: 30px; height: 30px; top: -26px; left: 6px; }
    
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
        border-radius: 50%;
        cursor: pointer;
        filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.15));
        transition: transform 0.2s ease;
        object-fit: contain;
    }
    
    .level-node:hover .level-btn {
        transform: scale(1.15);
    }
    
    .level-btn.locked {
        cursor: not-allowed;
    }
    
    .level-btn.locked:hover {
        transform: none;
    }
    
    .start-badge {
        position: absolute;
        background: #4caf50;
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
        color: #4caf50;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        white-space: nowrap;
    }
    
    .locked-node {
        position: absolute;
        z-index: 20;
        transform: translate(-50%, -50%);
    }
    
    .locked-btn {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.1));
        cursor: not-allowed;
    }
    
    .player-character {
        position: absolute;
        z-index: 25;
        transition: all 0.5s ease;
        transform: translate(-50%, -140%);
    }
    
    .player-current-level {
        animation: bounce 1s ease-in-out infinite;
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
        width: 32px;
        height: auto;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
        opacity: 0.85;
    }
    
    .coming-soon {
        position: absolute;
        color: #81c784;
        font-size: 0.75rem;
        font-weight: 700;
        text-align: center;
        white-space: nowrap;
    }
    
    .decor-star { font-size: 24px; animation: twinkle 2s ease-in-out infinite; }
    .decor-flower { font-size: 20px; animation: sway 3s ease-in-out infinite; }
    .decor-mushroom { font-size: 22px; }
    
    @keyframes twinkle {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.1); }
    }
    
    @keyframes sway {
        0%, 100% { transform: rotate(-5deg); }
        50% { transform: rotate(5deg); }
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

<div class="min-h-screen bg-[#f5f9ef] p-4 md:p-8">
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
                        <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ __('game.shapes_name') }}</h1>
                        <p class="text-sm text-[#5f6873]">{{ __('game.shapes_tagline') }}</p>
                    </div>
                </div>
                <div class="points-badge">
                    <span class="text-sm font-semibold text-[#37474f]"><i class="ico ico-star text-[#ffca3a]" aria-hidden="true"></i> {{ __('game.points', ['score' => $totalScore ?? 0]) }}</span>
                </div>
            </div>
        </div>

        <x-games-switcher topic="shapes" current="shapes" />

        <!-- Map Container -->
        <div class="map-container relative">
            <!-- Map Background -->
            <img src="{{ asset('assets/images/game/shapes/background.png') }}" alt="{{ __('game.shapes_map_alt') }}" class="map-image">

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
                    stroke="#8d6e63"
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
                    stroke="#d7ccc8"
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
                    <a href="{{ route('student.games.shapes.play', ['level' => $level['number']]) }}" class="level-node" style="left: {{ $level['left'] }}; top: {{ $level['top'] }};" title="{{ __('game.level_number', ['number' => $level['number']]) }}">
                        <img src="{{ asset('assets/images/game/shapes/btn_' . $level['number'] . '.png') }}" alt="{{ __('game.level_number', ['number' => $level['number']]) }}" class="level-btn" style="width: 52px; height: 52px;">
                        <span class="level-badge">@if($level['number'] === 1)<i class="ico ico-graduation-cap" aria-hidden="true"></i> {{ __('tutorial_stage.badge') }}@else{{ __('game.level_number', ['number' => $level['number']]) }}@endif</span>
                    </a>
                @else
                    <div class="locked-node" style="left: {{ $level['left'] }}; top: {{ $level['top'] }};" title="{{ __('game.level_locked_title', ['number' => $level['number']]) }}">
                        <img src="{{ asset('assets/images/game/shapes/btn_locked.png') }}" alt="{{ __('game.locked') }}" class="locked-btn">
                        <span class="level-badge" style="bottom: -20px; color: #9e9e9e;">{{ __('game.locked') }}</span>
                    </div>
                @endif
            @endforeach

            <!-- Player Character -->
            <div class="player-character player-current-level" id="playerCharacter" style="left: 9.19%; top: 44.89%;">
                <div class="player-indicator"></div>
            </div>

            <!-- Flag -->
            <div class="absolute right-[30%] top-[16%] z-20">
                <img src="{{ asset('assets/images/game/shapes/shape imgs/Star.png') }}" alt="{{ __('game.finish') }}" class="flag-img">
            </div>

            <!-- Coming Soon -->
            <div class="coming-soon absolute right-[20%] top-[10%]">
                {{ __('game.shapes_coming_soon') }}
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