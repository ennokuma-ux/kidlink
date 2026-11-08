{{--
    Leaderboards: one ranking per topic (each game, each ECCD domain, each activity
    type). All of the data is sent once and filtered in the browser, so changing a
    filter never reloads the page. Ranks are worked out inside the section filter, so
    "Top 5 in 4-5 years old" is really that section's top five; searching for a name
    only hides the other rows and keeps each child's true rank.

    Variables: $topicLeaderboards, $selectedStudent (optional, always shown even
    when they fall outside the Top N).
--}}
@php
    $lbBasisLabels = ['Game' => __('Games'), 'Domain' => __('ECCD domains'), 'Activity' => __('Activities')];
    $lbBases = collect($topicLeaderboards)->pluck('basis')->unique()->values()
        ->map(fn ($basis) => ['key' => $basis, 'label' => $lbBasisLabels[$basis] ?? $basis])->all();
    $lbSectionNames = collect($topicLeaderboards)->flatMap(fn ($board) => collect($board['rows'])->pluck('section'))->unique()->values();
    $lbSections = $lbSectionNames->filter()->sort()->values()
        ->map(fn ($section) => ['key' => $section, 'label' => $section])->all();
    if ($lbSectionNames->contains('')) {
        $lbSections[] = ['key' => '__none', 'label' => \App\Models\Student::UNASSIGNED_SECTION];
    }
    $lbColors = collect($lbSections)->mapWithKeys(fn ($s) => [$s['key'] => \App\Models\Student::colorForSection($s['key'] === '__none' ? null : $s['key'])])->all();
    $lbPayload = [
        'boards' => $topicLeaderboards,
        'bases' => $lbBases,
        'sections' => $lbSections,
        'colors' => $lbColors,
        'selectedId' => $selectedStudent?->id,
        'exceeding' => \App\Services\AnalyticsService::EXCEEDING_PCT,
        'struggling' => \App\Services\AnalyticsService::STRUGGLING_PCT,
        'text' => [
            'ranked' => __('ranked'),
            'avg' => __('avg'),
            'more' => __('more'),
            'you' => __('Selected'),
        ],
    ];
    $lbField = 'mt-1 block rounded-lg border border-[#f1e7cc] bg-white px-3 py-2 text-sm text-[#182438] outline-none focus:ring-2 focus:ring-[#ff8555]';
@endphp

<div class="mb-6 rounded-xl bg-white p-3 shadow-sm sm:p-6">
    <div class="mb-4 flex items-center gap-2">
        <i class="ico ico-trophy text-xl text-[#ffca3a]" aria-hidden="true"></i>
        <h3 class="text-lg font-semibold text-[#182438]">{{ __('Leaderboards') }}</h3>
    </div>
    <p class="mb-4 text-sm text-[#5f6873]">{{ __('Who is leading in every game, ECCD domain and activity type. Filter by category, topic or section.') }}</p>

    @if(count($topicLeaderboards))
    <div x-data="klLeaderboards(@js($lbPayload))" class="space-y-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('Category') }}">
                <button type="button" @click="setBasis('all')"
                        :class="f.basis === 'all' ? 'border-[#ff8555] bg-[#ff8555] text-white' : 'border-[#f1e7cc] bg-white text-[#182438] hover:border-[#ff8555]'"
                        :aria-pressed="f.basis === 'all'" class="rounded-full border px-3 py-1.5 text-sm font-medium transition">{{ __('All') }}</button>
                <template x-for="b in bases" :key="b.key">
                    <button type="button" @click="setBasis(b.key)"
                            :class="f.basis === b.key ? 'border-[#ff8555] bg-[#ff8555] text-white' : 'border-[#f1e7cc] bg-white text-[#182438] hover:border-[#ff8555]'"
                            :aria-pressed="f.basis === b.key" class="rounded-full border px-3 py-1.5 text-sm font-medium transition" x-text="b.label"></button>
                </template>
            </div>

            <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Topic') }}
                <select x-model="f.topic" class="{{ $lbField }} capitalize">
                    <option value="all">{{ __('All topics') }}</option>
                    <template x-for="b in topicOptions" :key="b.key"><option :value="b.key" x-text="b.topic.replace(/-/g, ' ')"></option></template>
                </select>
            </label>

            <template x-if="sections.length > 1">
                <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Section') }}
                    <select x-model="f.section" class="{{ $lbField }}">
                        <option value="all">{{ __('All sections') }}</option>
                        <template x-for="s in sections" :key="s.key"><option :value="s.key" x-text="s.label"></option></template>
                    </select>
                </label>
            </template>

            <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Show') }}
                <select x-model.number="f.limit" class="{{ $lbField }}">
                    <option value="3">{{ __('Top 3') }}</option>
                    <option value="5">{{ __('Top 5') }}</option>
                    <option value="10">{{ __('Top 10') }}</option>
                    <option value="0">{{ __('Everyone') }}</option>
                </select>
            </label>

            <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Find a student') }}
                <input type="search" x-model.debounce.150ms="f.q" placeholder="{{ __('Search name') }}" autocomplete="off" class="{{ $lbField }} w-44">
            </label>

            <button type="button" x-show="isFiltered" x-cloak @click="reset()" class="pb-2 text-xs font-semibold text-[#ff8555] hover:text-[#f47548]">{{ __('Reset filters') }}</button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <template x-for="b in view" :key="b.key">
                <section class="rounded-xl border border-[#f1e7cc] p-4">
                    <header class="mb-3 flex items-start justify-between gap-2">
                        <div>
                            <h4 class="font-semibold capitalize text-[#182438]" x-text="b.topic.replace(/-/g, ' ')"></h4>
                            <p class="text-xs text-[#8b949d]"><span x-text="b.basisLabel"></span> · <span x-text="b.total + ' ' + text.ranked"></span></p>
                        </div>
                        <span class="rounded-full bg-[#fffbf0] px-2 py-0.5 text-xs font-semibold text-[#5f6873] whitespace-nowrap" x-text="text.avg + ' ' + b.avg + '%'"></span>
                    </header>
                    <ol class="space-y-1.5">
                        <template x-for="r in b.rows" :key="r.id">
                            <li class="rounded-lg px-2 py-1.5" :class="r.id === selectedId ? 'bg-[#fff0e6]' : ''">
                                <div class="flex items-center gap-2">
                                    <span class="flex w-6 shrink-0 justify-center text-sm font-bold text-[#5f6873]">
                                        <template x-if="r.rank <= 3"><i class="ico ico-trophy text-lg" :style="'color:' + medal(r.rank)" :title="'#' + r.rank"></i></template>
                                        <template x-if="r.rank > 3"><span x-text="r.rank"></span></template>
                                    </span>
                                    <span class="h-2 w-2 shrink-0 rounded-full" :style="'background-color:' + (colors[r.section || '__none'] || '#8b949d')"></span>
                                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-[#182438]" x-text="r.name"></span>
                                    <span class="text-sm font-bold" :style="'color:' + tone(r.pct)" x-text="r.pct + '%'"></span>
                                </div>
                                <div class="ml-8 mt-1 h-1.5 rounded-full bg-[#f1e7cc]">
                                    <div class="h-1.5 rounded-full" :style="'width:' + Math.min(r.pct, 100) + '%;background-color:' + tone(r.pct)"></div>
                                </div>
                            </li>
                        </template>
                    </ol>
                    <p x-show="b.more > 0" class="mt-2 text-center text-xs text-[#8b949d]" x-text="'+ ' + b.more + ' ' + text.more"></p>
                </section>
            </template>
        </div>

        <p x-show="view.length === 0" x-cloak class="py-8 text-center text-sm text-[#8b949d]">{{ __('No scores match these filters.') }}</p>
    </div>
    @else
    <p class="py-8 text-center text-sm text-[#8b949d]">{{ __('analytics.no_scores_recorded') }}</p>
    @endif
</div>

@if(count($topicLeaderboards))
<script>
window.klLeaderboards = function (data) {
    var BASIS = { Game: @json(__('Games')), Domain: @json(__('ECCD domains')), Activity: @json(__('Activities')) };
    return {
        boards: data.boards, bases: data.bases, sections: data.sections, colors: data.colors,
        selectedId: data.selectedId, text: data.text,
        f: { basis: 'all', topic: 'all', section: 'all', limit: 5, q: '' },
        view: [],

        init: function () {
            var self = this;
            this.$watch('f', function () { self.refresh(); });
            this.refresh();
        },
        get topicOptions() {
            var basis = this.f.basis;
            return this.boards.filter(function (b) { return basis === 'all' || b.basis === basis; });
        },
        get isFiltered() {
            var f = this.f;
            return f.basis !== 'all' || f.topic !== 'all' || f.section !== 'all' || f.limit !== 5 || f.q.trim() !== '';
        },
        setBasis: function (basis) { this.f.basis = basis; this.f.topic = 'all'; },
        reset: function () { this.f = { basis: 'all', topic: 'all', section: 'all', limit: 5, q: '' }; },
        medal: function (rank) { return rank === 1 ? '#ffca3a' : (rank === 2 ? '#9aa5b1' : '#c77d3b'); },
        tone: function (pct) {
            return pct >= data.exceeding ? '#8ac926' : (pct < data.struggling ? '#ff595e' : '#ffca3a');
        },

        // Rank inside the section filter (ties share a rank), then apply the
        // name search and the Top N, always keeping the chosen child on screen.
        refresh: function () {
            var f = this.f, q = f.q.trim().toLowerCase(), selectedId = this.selectedId, out = [];
            var limit = Number(f.limit) || 0;

            this.topicOptions.forEach(function (b) {
                if (f.topic !== 'all' && b.key !== f.topic) { return; }

                var pool = b.rows.filter(function (r) {
                    return f.section === 'all' || (f.section === '__none' ? r.section === '' : r.section === f.section);
                });
                if (!pool.length) { return; }

                var rank = 0, prev = null, sum = 0;
                var ranked = pool.map(function (r, i) {
                    if (r.pct !== prev) { rank = i + 1; prev = r.pct; }
                    sum += r.pct;
                    return { id: r.id, name: r.name, pct: r.pct, section: r.section, rank: rank };
                });

                var matched = q ? ranked.filter(function (r) { return r.name.toLowerCase().indexOf(q) !== -1; }) : ranked;
                if (!matched.length) { return; }

                var shown = limit > 0 ? matched.slice(0, limit) : matched;
                if (selectedId && !q && !shown.some(function (r) { return r.id === selectedId; })) {
                    var me = ranked.filter(function (r) { return r.id === selectedId; })[0];
                    if (me) { shown = shown.concat([me]); }
                }

                out.push({
                    key: b.key, topic: b.topic, basisLabel: BASIS[b.basis] || b.basis,
                    total: ranked.length, avg: Math.round(sum / ranked.length),
                    rows: shown, more: Math.max(0, matched.length - shown.length),
                });
            });

            this.view = out;
        },
    };
};
</script>
@endif
