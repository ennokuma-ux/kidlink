@extends('layouts.app')

@section('title', __('student.home_title'))

@section('content')
<div class="min-h-screen" style="background: linear-gradient(180deg, #faf8f3 0%, #f0f4ff 100%);">
    <div class="container mx-auto px-4 py-6 max-w-6xl">
        <!-- Header -->
        <nav class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center shadow-md">
                    <span class="text-white font-bold text-2xl">K</span>
                </div>
                <span class="text-2xl font-bold text-gray-800">Kid<span class="text-orange-500">Link</span></span>
            </div>
            <div class="flex items-center gap-3">
                @if(session('parent_user_id'))
                <form method="POST" action="{{ route('child-mode.exit') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 bg-white border border-gray-200 hover:border-orange-300 text-gray-600 px-3 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('student.exit') }}
                    </button>
                </form>
                @endif
                <span class="text-sm text-gray-500 hidden md:inline">{{ __('student.welcome', ['name' => $student->first_name]) }}</span>
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold shadow">
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </button>
                    <div x-show="open" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl z-50 py-2" style="display: none;">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-700">{{ $student->first_name }} {{ $student->last_name }}</p>
                            <p class="text-xs text-gray-400">{{ __('student.role_student') }}</p>
                        </div>
                        @if(session('parent_user_id'))
                        <form method="POST" action="{{ route('child-mode.exit') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-orange-600 hover:bg-orange-50 flex items-center gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                {{ __('student.exit_child_mode') }}
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                {{ __('student.logout') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg" style="background: linear-gradient(135deg, #258cc5, #ff8555);">
                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $student->first_name }} {{ $student->last_name }}</h2>
                    <p class="text-gray-500">{{ __('student.age') }} {{ $student->age }} {{ __('student.years_word') }} | {{ __('student.class_label') }} {{ $student->class_name ?? 'N/A' }} | {{ __('student.hand_label') }} {{ $student->handLabel() }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6" x-data="{
                stats: { total_points: {{ $totalPoints }}, games_played: {{ $totalGames }}, streak: {{ $streak }}, avg_score: {{ $avgScore }} },
                async refresh() {
                    try {
                        const res = await fetch('/student/stats', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) this.stats = await res.json();
                    } catch (e) {
                        // keep the last known values if the refresh fails
                    }
                },
                init() {
                    setInterval(() => this.refresh(), 10000);
                    window.addEventListener('focus', () => this.refresh());
                    document.addEventListener('visibilitychange', () => { if (!document.hidden) this.refresh(); });
                }
            }">
            <div class="bg-white rounded-xl shadow-sm px-4 py-3 border-l-4 border-orange-500">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide">{{ __('student.stat_total_points') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.total_points">{{ $totalPoints }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm px-4 py-3 border-l-4 border-blue-500">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide">{{ __('student.stat_games_played') }}</p>
                <p class="text-2xl font-bold text-blue-500 mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.games_played">{{ $totalGames }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm px-4 py-3 border-l-4 border-green-500">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide">{{ __('student.stat_win_streak') }}</p>
                <p class="text-2xl font-bold text-green-500 mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.streak">{{ $streak }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm px-4 py-3 border-l-4 border-yellow-500">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide">{{ __('student.stat_avg_score') }}</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.avg_score + '%'">{{ $avgScore }}%</p>
            </div>
        </div>

        <!-- Recent Activity -->
        @if($recentActivity->count() > 0)
        <h2 class="text-xl font-bold text-gray-800 mb-5" style="font-family: 'Fredoka One', cursive;">{{ __('student.recent_activity') }}</h2>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            @foreach($recentActivity as $activity)
            <div class="flex items-center gap-4 px-5 py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="background: {{ match($activity->game_type) {
                    'shapes' => '#fee2e2',
                    'colors' => '#dcfce7',
                    'letters' => '#fff7ed',
                    'numbers' => '#dbeafe',
                    'animals' => '#fef9c3',
                    default => '#f3f4f6',
                } }}">
                    {{ match($activity->game_type) {
                        'shapes' => '🔷',
                        'colors' => '🎨',
                        'letters' => '🔤',
                        'numbers' => '🔢',
                        'animals' => '🐾',
                        default => '🎮',
                    } }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 capitalize">{{ $activity->game_type }} - {{ __('student.level') }} {{ $activity->level }}</p>
                    <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-green-600">+{{ $activity->points_earned }} {{ __('student.pts') }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- All Games Grid -->
        <div class="flex items-center gap-3 mb-5">
            <h2 class="text-xl font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;">{{ __('student.my_games') }}</h2>
            <span class="h-1.5 flex-1 rounded-full bg-gradient-to-r from-orange-200 via-orange-100 to-transparent"></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
            @foreach($games as $game)
            <div class="group relative bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-200 flex flex-col">
                <a href="{{ route('student.games.' . $game['slug'] . '.stage-select') }}" class="block flex-1">
                    <!-- Banner -->
                    <div class="relative h-32 flex items-center justify-center overflow-hidden" style="background: {{ $game['gradient'] }};">
                        <div class="absolute inset-0 opacity-50" style="background: radial-gradient(circle at 30% 15%, rgba(255,255,255,0.55), transparent 60%);"></div>
                        <div class="absolute -bottom-8 -left-6 w-24 h-24 rounded-full bg-white/15"></div>
                        <img src="{{ asset('storage/img/'.$game['img']) }}" alt="{{ $game['name'] }}"
                             class="relative w-20 h-20 object-contain drop-shadow-xl transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                        @if($game['played'] > 0)
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 bg-white/95 rounded-full px-2.5 py-1 text-xs font-bold text-gray-700 shadow-sm">⭐ {{ number_format($game['points']) }}</span>
                        @else
                        <span class="absolute top-3 right-3 inline-flex items-center rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide text-orange-500 shadow-sm">{{ __('student.new_badge') }}</span>
                        @endif
                    </div>

                    <!-- Body -->
                    <div class="p-5 pb-3">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-bold text-base text-gray-800" style="font-family: 'Fredoka One', cursive;">{{ $game['name'] }}</h3>
                            @if($game['played'] > 0)
                            <span class="shrink-0 rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-bold text-gray-400">{{ $game['played'] }}x</span>
                            @endif
                        </div>
                        <p class="text-gray-400 text-xs mt-1 leading-relaxed">{{ $game['desc'] }}</p>

                        @if($game['played'] > 0)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="text-gray-400 font-semibold">{{ __('student.level') }} {{ $game['highestLevel'] }}/8</span>
                                <span class="font-bold text-gray-600">{{ number_format($game['points']) }} {{ __('student.pts') }}</span>
                            </div>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 8; $i++)
                                <span class="h-1.5 flex-1 rounded-full" style="background: {{ $i <= $game['highestLevel'] ? $game['gradient'] : '#eef1f5' }};"></span>
                                @endfor
                            </div>
                        </div>
                        @else
                        <p class="text-gray-300 text-xs mt-4 font-semibold">{{ __('student.not_played') }}</p>
                        @endif
                    </div>
                </a>

                <!-- Play / Continue -->
                <div class="px-5 pb-5 pt-1">
                    <a href="{{ route('student.games.' . $game['slug'] . '.stage-select') }}"
                       class="flex items-center justify-center gap-2 w-full py-2.5 rounded-2xl text-white text-sm font-bold shadow-md transition hover:shadow-lg hover:brightness-105 active:scale-[0.98]"
                       style="background: {{ $game['gradient'] }};">
                        {{ $game['played'] > 0 ? __('student.continue') : __('student.play') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>


    </div>
</div>
@if(session('parent_user_id'))
<form method="POST" action="{{ route('child-mode.exit') }}" class="sm:hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50">
    @csrf
    <button type="submit" class="flex items-center gap-2 bg-white border border-gray-200 shadow-lg text-gray-600 px-6 py-3 rounded-full text-sm font-bold hover:bg-gray-50 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        {{ __('student.exit_child_mode') }}
    </button>
</form>
@endif
@endsection
