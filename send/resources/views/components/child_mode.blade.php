@php
    $user = auth()->user();
    $accounts = (in_array($user->role, ['super_admin', 'admin'], true)
        ? App\Models\Student::query()
        : ($user->role === 'teacher' ? $user->teacherStudents() : $user->children()))
        ->where('students.is_active', true)
        ->orderBy('first_name')
        ->get()
        ->map(fn ($c) => [
            'id' => $c->id,
            'first' => $c->first_name,
            'name' => $c->name,
            'class_name' => $c->class_name,
            'initial' => strtoupper(substr($c->first_name ?: '?', 0, 1)),
            'url' => route('child-mode.enter', $c),
        ])
        ->values()
        ->all();

    $manageAccountsUrl = match ($user->role) {
        'teacher' => route('teacher.accounts.index'),
        default => route('parent.dashboard'),
    };
@endphp
<script type="application/json" id="childModeLabels">{!! json_encode(['availableOne' => __('child.available_one'), 'availableOther' => __('child.available_other'), 'noSection' => __('child.no_section')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script type="application/json" id="childModeAccounts">{!! json_encode($accounts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<div x-data="{
        open: false,
        search: '',
        accounts: [],
        labels: {},
        init() {
            this.accounts = JSON.parse(document.getElementById('childModeAccounts').textContent);
            this.labels = JSON.parse(document.getElementById('childModeLabels').textContent);
        },
        async refresh() {
            try {
                const res = await fetch('{{ route('child-mode.accounts') }}', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.accounts = (await res.json()).accounts;
            } catch (e) {
                // keep the last known list if the refresh fails
            }
        },
        openPicker() {
            this.open = true;
            this.search = '';
            this.$nextTick(() => this.$refs.pickerSearch.focus());
            this.refresh();
        },
        close() {
            this.open = false;
            this.search = '';
        },
        get filtered() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.accounts;
            return this.accounts.filter(a => {
                const haystack = [a.first, a.name, a.class_name].filter(Boolean).join(' ').toLowerCase();
                return haystack.includes(q);
            });
        }
    }" @keydown.escape.window="close()">
    <button type="button" @click="openPicker()" class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ __('child.mode') }}
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="close()"
         class="fixed inset-0 z-[100] flex items-start justify-center p-4 sm:items-center bg-black/50 overflow-y-auto"
         style="display: none;"
         role="dialog"
         aria-modal="true"
         aria-label="{{ __('child.choose_aria') }}">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-md bg-white rounded-2xl shadow-2xl my-auto">
            <div class="flex items-start justify-between gap-3 p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-[#182438]">{{ __('child.choose_title') }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="accounts.length + (accounts.length === 1 ? labels.availableOne : labels.availableOther)"></p>
                </div>
                <button type="button" @click="close()" aria-label="{{ __('child.close') }}" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <div class="p-4 border-b border-gray-100">
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input x-ref="pickerSearch" x-model="search" type="search" autocomplete="off"
                           placeholder="{{ __('child.search_placeholder') }}"
                           aria-label="{{ __('child.search_aria') }}"
                           class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#ff8555] focus:ring-1 focus:ring-[#ff8555]">
                </div>
            </div>

            <div class="max-h-80 overflow-y-auto p-2">
                <template x-for="a in filtered" :key="a.id">
                    <form method="POST" :action="a.url">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-orange-50 transition flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #258cc5, #ff8555);" x-text="a.initial"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-[#182438] truncate" x-text="a.name"></span>
                                <span class="block text-xs text-gray-400 truncate" x-text="a.class_name || labels.noSection"></span>
                            </span>
                            <span class="text-xs font-semibold text-[#ff8555] flex-shrink-0">{{ __('child.play') }}</span>
                        </button>
                    </form>
                </template>
                <div x-show="filtered.length === 0" class="px-4 py-6 text-center">
                    <p class="text-sm text-gray-500 font-medium" x-show="accounts.length === 0">
                        {{ __('child.empty_title') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1" x-show="accounts.length === 0">
                        {{ __('child.empty_hint') }}
                    </p>
                    <a x-show="accounts.length === 0" href="{{ $manageAccountsUrl }}"
                       class="inline-block mt-3 text-sm font-semibold text-[#ff8555] hover:underline">
                        {{ __('child.manage_accounts') }} &rarr;
                    </a>
                    <p x-show="accounts.length > 0" class="text-sm text-gray-400">
                        {{ __('child.no_match') }}
                    </p>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 bg-[#faf8f3] rounded-b-2xl">
                <button type="button" @click="close()" class="w-full text-sm text-[#5f6873] hover:text-[#182438] font-medium transition">{{ __('child.cancel') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Tells the child's first page to greet them with the transition screen
     (see components/child-transition). Harmless if the note is never read:
     it expires after 10 seconds. --}}
<script>
(function () {
    if (window.__klEnterNote) return;
    window.__klEnterNote = true;
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
        if (!/\/child-mode\/enter\//.test(form.getAttribute('action') || '')) return;
        try {
            sessionStorage.setItem('klTransition', JSON.stringify({ ts: Date.now(), from: '/', kind: 'enter' }));
        } catch (e) { /* private mode: no greeting screen, nothing else changes */ }
    });
})();
</script>
