@props(['route', 'scope', 'section' => '', 'student' => null, 'sort' => 'section', 'shown' => 0, 'total' => 0, 'legend' => []])

@php
    $label = match ($scope) {
        'individual' => __('analytics.scope_viewing_student', ['name' => $student?->name]),
        'section' => __('analytics.scope_viewing_section', ['name' => \App\Models\Student::sectionLabel($section)]),
        default => __('analytics.scope_viewing_all'),
    };
    $keep = match ($scope) {
        'individual' => ['student' => $student?->id],
        'section' => ['section' => $section],
        default => ['scope' => 'all'],
    };
@endphp

<div class="bg-white rounded-xl shadow-sm border border-[#ebe8e1] p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <a href="{{ route($route) }}"
           class="inline-flex items-center gap-2 self-start px-4 py-2 rounded-lg border border-[#ebe8e1] text-sm font-semibold text-[#182438] hover:bg-[#eef2f8] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('analytics.scope_back') }}
        </a>

        <div class="min-w-0 sm:flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#8b949d]">{{ __('analytics.scope_viewing') }}</p>
            <p class="font-bold text-[#182438] truncate">{{ $label }}</p>
        </div>

        <form method="GET" data-ajax-filter action="{{ route($route) }}" class="flex items-center gap-2">
            @foreach($keep as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="analyticsSort" class="text-xs font-semibold uppercase tracking-wide text-[#5f6873]">{{ __('analytics.sort_label') }}</label>
            <select name="sort" id="analyticsSort" onchange="this.form.requestSubmit()"
                    class="rounded-lg border border-[#ebe8e1] bg-white px-3 py-2 text-sm text-[#182438] focus:ring-2 focus:ring-[#ff8555] outline-none">
                <option value="section" @selected($sort === 'section')>{{ __('analytics.sort_section') }}</option>
                <option value="name" @selected($sort === 'name')>{{ __('analytics.sort_name') }}</option>
                <option value="performance" @selected($sort === 'performance')>{{ __('analytics.sort_performance') }}</option>
            </select>
        </form>
    </div>

    @if($scope === 'all' && count($legend))
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 pt-4 border-t border-[#ebe8e1]">
            <span class="text-xs font-semibold uppercase tracking-wide text-[#8b949d]">{{ __('analytics.sections_label') }}</span>
            @foreach($legend as $item)
                <span class="inline-flex items-center gap-2 text-sm text-[#5f6873]">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                    <span class="text-[#182438]">{{ $item['label'] }}</span>
                    <span class="text-[#8b949d]">{{ $item['total'] }}</span>
                </span>
            @endforeach
        </div>
    @endif
</div>
