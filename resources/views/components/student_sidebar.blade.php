<aside class="fixed left-0 top-0 h-full w-64 bg-[#182438] text-white shadow-xl z-30 hidden md:block">
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <span class="text-[#ff8555] font-bold text-lg">K</span>
            </div>
            <div>
                <h2 class="font-bold text-lg">Kid<span class="text-[#ff8555]">Link</span></h2>
                <p class="text-xs text-white/60">{{ __('sidebar.student_portal') }}</p>
            </div>
        </div>
    </div>
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
            </div>
            <div>
                <p class="font-medium text-sm">{{ auth()->user()->full_name }}</p>
                <p class="text-xs text-white/60">{{ __('sidebar.student') }}</p>
            </div>
        </div>
    </div>
    <nav class="p-4 space-y-2">
        <a href="{{ route('student.home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('student.home') ? 'bg-[#ff8555]' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 011-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            {{ __('nav.home') }}
        </a>
        <a href="{{ route('student.games.shapes.stage-select') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('student.games.*') ? 'bg-[#ff8555]' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('nav.games') }}
        </a>
        @if(session('parent_user_id'))
        <form method="POST" action="{{ route('child-mode.exit') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                {{ __('nav.exit_child_mode') }}
            </button>
        </form>
        @endif
    </nav>
</aside>