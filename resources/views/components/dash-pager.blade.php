@props(['paginator', 'param', 'url', 'label' => 'items'])

{{--
    Page links for a card that reloads on its own (see public/js/dash-partials.js).
    Each link is a real URL, so it still works with JavaScript off, in a new
    tab, or when copied; with JavaScript on, only the card behind it is fetched.
--}}
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = collect([1, $last, $current - 1, $current, $current + 1])
        ->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
    $linkAttrs = fn (int $page) => 'href="'.e($paginator->url($page)).'" data-partial-link data-no-loader data-partial-url="'.e($url).'" data-param="'.e($param).'" data-value="'.$page.'"';
    $btn = 'px-3 py-2 rounded-lg border border-[#d7dee8] text-xs font-semibold text-[#182438] bg-white hover:bg-[#eef6fc] focus:outline-none focus:ring-2 focus:ring-[#1982c4]';
    $off = 'px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50';
@endphp

@if($paginator->total() > 0)
<nav class="mt-4 flex flex-wrap items-center justify-between gap-3" aria-label="{{ ucfirst($label) }} pages">
    <p class="text-xs text-[#6b7280]" aria-live="polite">
        Showing <span class="font-semibold text-[#182438]">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
        of <span class="font-semibold text-[#182438]">{{ $paginator->total() }}</span> {{ $label }}
    </p>

    @if($paginator->hasPages())
    <div class="flex flex-wrap items-center gap-1.5">
        @if($current > 1)
            <a {!! $linkAttrs($current - 1) !!} rel="prev" class="{{ $btn }}">Previous</a>
        @else
            <span class="{{ $off }}" aria-disabled="true">Previous</span>
        @endif

        @foreach($pages as $i => $p)
            @if($i > 0 && $p - $pages[$i - 1] > 1)
                <span class="px-1 text-xs text-[#6b7280]" aria-hidden="true">…</span>
            @endif
            @if($p === $current)
                <span class="min-w-9 px-3 py-2 rounded-lg bg-[#1982c4] text-white text-xs font-bold text-center" aria-current="page">{{ $p }}</span>
            @else
                <a {!! $linkAttrs($p) !!} class="min-w-9 text-center {{ $btn }}" aria-label="Go to page {{ $p }}">{{ $p }}</a>
            @endif
        @endforeach

        @if($current < $last)
            <a {!! $linkAttrs($current + 1) !!} rel="next" class="{{ $btn }}">Next</a>
        @else
            <span class="{{ $off }}" aria-disabled="true">Next</span>
        @endif
    </div>
    @endif
</nav>
@endif
