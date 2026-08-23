@extends('layouts.app')

@section('title', 'Student Games')

@push('styles')
<style>
.game-card { transition: all 0.3s ease; cursor: pointer; }
.game-card:hover { transform: translateY(-8px) scale(1.02); }
.star { animation: twinkle 1.5s ease-in-out infinite; }
@keyframes twinkle { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #f9f3d5 0%, #e3f1f2 40%, #e3f2f4 70%, #f9e9ed 100%);">
    <div class="container mx-auto px-4 py-6">
        <nav class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-[#ff8555] rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-2xl">K</span>
                </div>
                <span class="text-[#182438] font-bold text-2xl">Kid<span class="text-[#ff8555]">Link</span></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-[#5f6873] text-sm">Welcome, {{ $student->name ?? auth()->user()->first_name }}!</span>
                <div class="relative group">
                    <button class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        @if(session('parent_user_id'))
                        <form method="POST" action="{{ route('child-mode.exit') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-[#5f6873] hover:bg-[#faf8f3] rounded-t-xl">Exit Child Mode</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-b-xl">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="bg-white rounded-3xl shadow-lg p-8 mb-8 text-center border border-[#ebe8e1]">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="flex items-center gap-1">
                    <svg class="w-6 h-6 text-[#d9b348] star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="font-bold text-xl text-[#182438]">{{ $totalScore }}</span>
                </div>
                <div class="h-8 w-px bg-[#ebe8e1]"></div>
                <div>
                    <span class="text-[#5f6873] text-sm">Streak</span>
                    <span class="font-bold text-xl text-[#182438] ml-1">{{ $streak }}🔥</span>
                </div>
                <div class="h-8 w-px bg-[#ebe8e1]"></div>
                <div>
                    <span class="text-[#5f6873] text-sm">Level</span>
                    <span class="font-bold text-xl text-[#182438] ml-1">{{ min(ceil($totalGames / 5), 10) }}</span>
                </div>
            </div>
            <h1 class="text-4xl font-bold mb-2 text-[#182438]">Let's Learn & Play!</h1>
            <p class="text-[#5f6873] text-lg">Choose a game to start learning</p>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-8 mb-8 border border-[#ebe8e1]">
            <h2 class="text-2xl font-bold text-[#182438] mb-6">Featured Game</h2>
            @php $featured = $games[0]; @endphp
            <a href="{{ route('student.game.show', $featured['type']) }}" class="block game-card">
                <div class="rounded-2xl p-8 flex items-center gap-8" style="background: linear-gradient(135deg, #ff8555, #f47548);">
                    <img src="{{ asset('storage/img/'.$featured['img']) }}" alt="{{ $featured['name'] }}" class="w-24 h-24 object-contain">
                    <div class="text-white">
                        <h3 class="text-3xl font-bold mb-2">{{ $featured['name'] }}</h3>
                        <p class="text-white/80 text-lg">{{ $featured['desc'] }}</p>
                        <span class="inline-block mt-4 bg-white/20 px-6 py-2 rounded-full font-semibold">Play Now →</span>
                    </div>
                </div>
            </a>
        </div>

        <h2 class="text-2xl font-bold text-[#182438] mb-6">All Games</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            @foreach($games as $game)
            <a href="{{ route('student.game.show', $game['type']) }}" class="game-card">
                <div class="bg-white rounded-2xl p-6 text-center border border-[#ebe8e1] shadow-sm" style="border-top: 4px solid {{ $game['color'] === 'from-[#ff8555] to-[#f47548]' ? '#ff8555' : '#258cc5' }};">
                    <img src="{{ asset('storage/img/'.$game['img']) }}" alt="{{ $game['name'] }}" class="w-20 h-20 mx-auto mb-4 object-contain">
                    <h3 class="font-bold text-lg text-[#182438]">{{ $game['name'] }}</h3>
                    <p class="text-[#5f6873] text-sm mt-1">{{ $game['desc'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
