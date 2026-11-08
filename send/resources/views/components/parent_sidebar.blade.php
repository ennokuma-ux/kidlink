<aside class="fixed top-0 left-0 h-full w-64 bg-white border-r border-gray-100 shadow-lg z-40 flex flex-col overflow-y-auto transition-transform duration-200 -translate-x-full md:translate-x-0" :class="{ '!translate-x-0': navOpen }">
    <div class="p-5 border-b border-gray-100 relative">
        <button type="button" @click="navOpen = false" class="md:hidden absolute top-3 right-3 w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 text-2xl leading-none" aria-label="Close menu">&times;</button>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center shadow">
                <span class="text-white font-bold text-lg">K</span>
            </div>
            <span class="text-xl font-bold text-gray-800">Kid<span class="text-orange-500">Link</span></span>
        </div>
    </div>
    <nav class="flex-1 p-4 space-y-1">
        <a href="{{ route('parent.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('parent.dashboard') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
            {{ __('nav.dashboard') }}
        </a>
        <a href="{{ route('parent.results.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('parent.results.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            {{ __('nav.results') }}
        </a>
        <a href="{{ route('parent.milestones.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('parent.milestones.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            {{ __('nav.milestones') }}
        </a>
        <a href="{{ route('parent.attendance.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('parent.attendance.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('nav.attendance') }}
        </a>
        <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('announcements.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-500 hover:bg-gray-50' }}">
            <i class="ico ico-megaphone w-5 h-5" aria-hidden="true"></i>
            {{ __('nav.announcements') }}
        </a>
    </nav>
    <div class="p-4 border-t border-gray-100">
        <div class="text-xs text-gray-400 text-center">{{ __('sidebar.footer') }}</div>
    </div>
</aside>
