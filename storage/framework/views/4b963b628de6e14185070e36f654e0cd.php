<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['days', 'date', 'baseUrl', 'search' => '', 'section' => '', 'year' => '']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['days', 'date', 'baseUrl', 'search' => '', 'section' => '', 'year' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $labels = [
        'present' => __('teacher.present'), 'late' => __('teacher.late'), 'absent' => __('teacher.absent'),
        'none' => __('teacher.calendar_no_register'), 'today' => __('teacher.calendar_today'),
    ];
    $extra = array_filter(['search' => $search, 'section' => $section, 'year' => $year], fn ($v) => $v !== '' && $v !== null);
?>

<div class="bg-white rounded-xl shadow-sm p-4 mt-6 w-full"
     x-data="attendanceCalendar(<?php echo \Illuminate\Support\Js::from($days)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($date)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($baseUrl)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($extra)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($labels)->toHtml() ?>)">
    <div class="flex items-center justify-between gap-2 mb-3">
        <div>
            <h3 class="text-sm font-semibold text-[#182438]"><?php echo e(__('teacher.calendar_attendance_title')); ?></h3>
            <p class="text-xs text-[#8b949d] hidden sm:block"><?php echo e(__('teacher.calendar_attendance_hint')); ?></p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="shift(-1)" class="w-7 h-7 rounded-lg border border-[#ebe8e1] hover:bg-[#eef2f8] text-[#182438]" aria-label="Previous month">&lsaquo;</button>
            <span class="min-w-[7rem] text-center text-sm font-semibold text-[#182438]" x-text="monthLabel"></span>
            <button type="button" @click="shift(1)" class="w-7 h-7 rounded-lg border border-[#ebe8e1] hover:bg-[#eef2f8] text-[#182438]" aria-label="Next month">&rsaquo;</button>
            <button type="button" @click="jump(todayStr)" class="ml-1 px-2 h-7 rounded-lg border border-[#ebe8e1] text-xs font-medium text-[#ff8555] hover:bg-[#fff6f0]" x-text="labels.today"></button>
        </div>
    </div>

    <div class="grid grid-cols-7 text-center text-[11px] font-semibold uppercase text-[#8b949d] mb-1">
        <template x-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d"><span x-text="d"></span></template>
    </div>
    <div class="grid grid-cols-7 gap-1">
        <template x-for="cell in cells" :key="cell.date">
            <button type="button" @click="go(cell.date)" @mouseenter="hover = cell.date; prefetch(cell.date)" @mouseleave="hover = null" :title="describe(cell.date)"
                    class="relative h-10 md:h-14 rounded-md border text-sm transition flex flex-col items-center justify-start pt-0.5"
                    :class="{
                        'border-[#ff8555] bg-[#fff0e6] font-bold': cell.selected,
                        'border-transparent hover:bg-[#eef2f8]': !cell.selected,
                        'text-[#c9ced6]': !cell.inMonth,
                        'text-[#182438]': cell.inMonth && !cell.weekend,
                        'text-[#8b949d]': cell.inMonth && cell.weekend
                    }">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full" :class="cell.today ? 'ring-2 ring-[#ff8555] font-bold' : ''" x-text="cell.day"></span>
                <span x-show="days[cell.date]" class="mt-0.5 flex items-center gap-1">
                    <span class="block w-2 h-2 rounded-full" :class="dotClass(cell.date)"></span>
                    <span class="hidden md:inline text-[10px] text-[#5f6873]" x-text="rateText(cell.date)"></span>
                </span>
            </button>
        </template>
    </div>

    <div class="mt-3 pt-2 border-t border-[#ebe8e1] flex flex-wrap items-center justify-between gap-2 text-[11px] text-[#5f6873]">
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#68b82f]"></span><?php echo e(__('teacher.calendar_legend_good')); ?></span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#d9b348]"></span><?php echo e(__('teacher.calendar_legend_mid')); ?></span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#f05d56]"></span><?php echo e(__('teacher.calendar_legend_low')); ?></span>
        </div>
        <span class="min-h-[1rem] font-medium text-[#182438]" x-text="hover ? describe(hover) : ''"></span>
    </div>
</div>

<script>
if (typeof window.attendanceCalendar !== 'function') {
window.attendanceCalendar = function (days, selected, baseUrl, extra, labels) {
    const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const parse = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const sel = parse(selected);
    const rate = e => { const t = e.present + e.late + e.absent; return t ? Math.round(((e.present + e.late) / t) * 100) : null; };
    return {
        days: days, labels: labels, hover: null, prefetched: new Set(),
        view: new Date(sel.getFullYear(), sel.getMonth(), 1),
        todayStr: iso(new Date()),
        get monthLabel() { return this.view.toLocaleDateString(undefined, { month: 'long', year: 'numeric' }); },
        get cells() {
            const first = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
            const offset = (first.getDay() + 6) % 7;
            const out = [];
            for (let i = 0; i < 42; i++) {
                const d = new Date(first.getFullYear(), first.getMonth(), 1 - offset + i);
                out.push({ date: iso(d), day: d.getDate(), inMonth: d.getMonth() === this.view.getMonth(),
                           today: iso(d) === this.todayStr, selected: iso(d) === selected,
                           weekend: d.getDay() === 0 || d.getDay() === 6 });
            }
            return out;
        },
        shift(n) { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + n, 1); },
        dotClass(date) {
            const e = this.days[date]; const r = e ? rate(e) : null;
            if (r === null) return 'bg-[#c9ced6]';
            return r >= 85 ? 'bg-[#68b82f]' : (r >= 60 ? 'bg-[#d9b348]' : 'bg-[#f05d56]');
        },
        rateText(date) { const e = this.days[date]; const r = e ? rate(e) : null; return r === null ? '' : r + '%'; },
        describe(date) {
            const label = parse(date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
            const e = this.days[date];
            if (!e) return label + ' · ' + this.labels.none;
            return label + ' · ' + this.labels.present + ' ' + e.present + ' · ' + this.labels.late + ' ' + e.late + ' · ' + this.labels.absent + ' ' + e.absent;
        },
        prefetch(date) {
            if (this.prefetched.has(date) || date === selected) return;
            this.prefetched.add(date);
            const params = new URLSearchParams(Object.assign({}, extra, { date: date }));
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = baseUrl + '?' + params.toString();
            document.head.appendChild(link);
        },
        go(date) {
            const params = new URLSearchParams(Object.assign({}, extra, { date: date }));
            const url = baseUrl + '?' + params.toString();
            if (typeof window.ajaxFilterNavigate === 'function') {
                window.ajaxFilterNavigate(url);
            } else {
                window.location.href = url;
            }
        },
        jump(date) { this.view = new Date(parse(date).getFullYear(), parse(date).getMonth(), 1); this.go(date); },
    };
};
}
</script>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/attendance-calendar.blade.php ENDPATH**/ ?>