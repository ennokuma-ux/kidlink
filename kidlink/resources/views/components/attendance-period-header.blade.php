@props(['period', 'keep' => []])

@php
    $mode = $period['mode'];
    $baseUrl = url()->current();
    $link = fn (array $extra) => $baseUrl.'?'.http_build_query(array_merge($keep, $extra));
    $title = ($mode === 'week' && $period['isCurrent'])
        ? __('analytics.this_week_title')
        : ($mode === 'week' ? __('analytics.period_week_of', ['range' => $period['label']]) : $period['label']);
@endphp

<div class="mb-4" data-ajax-filter-scope
     x-data="attendancePeriodPicker(@js($period['anchor']), @js($mode), @js($baseUrl), @js($keep), @js(['today' => __('analytics.period_today'), 'viewMonth' => __('analytics.period_view_month')]))"
     @click.outside="open = false" @keydown.escape.window="open = false">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h3 class="text-lg font-semibold text-[#182438] truncate">{{ $title }}</h3>
            <p class="text-xs text-[#8b949d]">{{ $mode === 'week' ? $period['label'] : __('analytics.this_week_subtitle') }}</p>
        </div>

        <div class="flex items-center gap-2 relative">
            {{-- Week / month --}}
            <div class="inline-flex rounded-lg border border-[#f1e7cc] overflow-hidden text-sm font-medium">
                <a href="{{ $link(['att_mode' => 'week', 'att_date' => $period['anchor']]) }}"
                   class="px-3 py-1.5 {{ $mode === 'week' ? 'bg-[#182438] text-white' : 'text-[#5f6873] hover:bg-[#fbf0d6]' }}">{{ __('analytics.period_week') }}</a>
                <a href="{{ $link(['att_mode' => 'month', 'att_date' => $period['anchor']]) }}"
                   class="px-3 py-1.5 {{ $mode === 'month' ? 'bg-[#182438] text-white' : 'text-[#5f6873] hover:bg-[#fbf0d6]' }}">{{ __('analytics.period_month') }}</a>
            </div>

            {{-- Step back / forward --}}
            <a href="{{ $link(['att_mode' => $mode, 'att_date' => $period['prev']]) }}" title="{{ __('analytics.period_prev') }}" aria-label="{{ __('analytics.period_prev') }}"
               class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-[#f1e7cc] text-[#182438] hover:bg-[#fbf0d6]">&lsaquo;</a>
            <a href="{{ $link(['att_mode' => $mode, 'att_date' => $period['next']]) }}" title="{{ __('analytics.period_next') }}" aria-label="{{ __('analytics.period_next') }}"
               class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-[#f1e7cc] text-[#182438] hover:bg-[#fbf0d6]">&rsaquo;</a>

            {{-- Calendar pop-up --}}
            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="dialog" title="{{ __('analytics.period_pick') }}"
                    class="inline-flex items-center gap-2 px-3 h-8 rounded-lg border border-[#f1e7cc] text-sm font-medium text-[#182438] hover:border-[#ff8555] hover:bg-[#fff6f0]">
                <svg class="w-4 h-4 text-[#ff8555]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1z"/></svg>
                <span class="hidden sm:inline">{{ __('analytics.period_pick') }}</span>
            </button>

            <div x-show="open" x-cloak style="display:none;" x-transition.opacity.duration.150ms role="dialog"
                 class="absolute right-0 top-full mt-2 z-40 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-[#f1e7cc] p-4">
                <div class="flex items-center justify-between mb-2">
                    <button type="button" @click="shift(-1)" class="w-8 h-8 rounded-lg hover:bg-[#fbf0d6] text-[#182438]" aria-label="Previous month">&lsaquo;</button>
                    <span class="font-semibold text-sm text-[#182438]" x-text="monthLabel"></span>
                    <button type="button" @click="shift(1)" class="w-8 h-8 rounded-lg hover:bg-[#fbf0d6] text-[#182438]" aria-label="Next month">&rsaquo;</button>
                </div>
                <div class="grid grid-cols-7 text-center text-[10px] font-semibold uppercase text-[#8b949d] mb-1">
                    <template x-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d"><span x-text="d"></span></template>
                </div>
                <div class="grid grid-cols-7 gap-y-0.5">
                    <template x-for="cell in cells" :key="cell.date">
                        <a :href="href(cell.date)"
                           class="h-8 inline-flex items-center justify-center text-xs transition hover:bg-[#ffe3d1]"
                           :class="{
                               'text-[#c9ced6]': !cell.inMonth, 'text-[#182438]': cell.inMonth,
                               'bg-[#fff0e6]': cell.selected, 'rounded-l-lg': cell.first, 'rounded-r-lg': cell.last
                           }">
                            <span class="w-6 h-6 inline-flex items-center justify-center rounded-full" :class="cell.today ? 'ring-2 ring-[#ff8555] font-bold' : ''" x-text="cell.day"></span>
                        </a>
                    </template>
                </div>
                <p class="mt-3 text-[11px] text-[#8b949d]">{{ $mode === 'week' ? __('analytics.period_hint_week') : __('analytics.period_hint_month') }}</p>
                <div class="mt-2 pt-2 border-t border-[#f1e7cc] flex items-center justify-between text-xs">
                    <a :href="href(todayStr)" class="font-medium text-[#ff8555] hover:text-[#f47548]" x-text="labels.today"></a>
                    <a x-show="mode === 'month'" :href="href(viewFirst)" class="font-medium text-[#1982c4] hover:text-[#1e73a8]" x-text="labels.viewMonth.replace(':month', monthLabel)"></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof window.attendancePeriodPicker !== 'function') {
window.attendancePeriodPicker = function (anchor, mode, baseUrl, keep, labels) {
    const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const parse = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const start = parse(anchor);
    const weekEnd = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 6);
    return {
        open: false, mode: mode, labels: labels,
        view: new Date(start.getFullYear(), start.getMonth(), 1),
        todayStr: iso(new Date()),
        get viewFirst() { return iso(this.view); },
        get monthLabel() { return this.view.toLocaleDateString(undefined, { month: 'long', year: 'numeric' }); },
        get cells() {
            const first = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
            const offset = (first.getDay() + 6) % 7;
            const out = [];
            for (let i = 0; i < 42; i++) {
                const d = new Date(first.getFullYear(), first.getMonth(), 1 - offset + i);
                const selected = mode === 'week'
                    ? d >= start && d <= weekEnd
                    : d.getFullYear() === start.getFullYear() && d.getMonth() === start.getMonth();
                out.push({
                    date: iso(d), day: d.getDate(), inMonth: d.getMonth() === this.view.getMonth(),
                    today: iso(d) === this.todayStr, selected: selected,
                    first: selected && (mode === 'week' ? iso(d) === iso(start) : d.getDate() === 1),
                    last: selected && (mode === 'week' ? iso(d) === iso(weekEnd) : new Date(d.getFullYear(), d.getMonth(), d.getDate() + 1).getDate() === 1),
                });
            }
            return out;
        },
        shift(n) { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + n, 1); },
        href(date) {
            const params = new URLSearchParams(Object.assign({}, keep, { att_mode: mode, att_date: date }));
            return baseUrl + '?' + params.toString();
        },
    };
};
}
</script>
