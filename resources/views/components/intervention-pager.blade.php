<nav class="px-5 py-3 {{ $position === 'top' ? 'border-b' : 'border-t' }} border-[#f5c6cf] flex flex-wrap items-center justify-between gap-3" aria-label="{{ $position === 'top' ? 'Intervention pagination (top)' : 'Intervention pagination' }}">
    <p class="text-xs text-[#6b7280]" aria-live="polite">
        Showing <span class="font-semibold text-[#182438]">{{ $interventions->firstItem() }}–{{ $interventions->lastItem() }}</span>
        of <span class="font-semibold text-[#182438]">{{ $interventions->total() }}</span> interventions
    </p>
    <div class="flex flex-wrap items-center gap-1.5">
        @if($interventions->onFirstPage())
            <span class="px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50" aria-disabled="true">Previous</span>
        @else
            <a href="{{ $interventions->previousPageUrl() }}" rel="prev" class="px-3 py-2 rounded-lg border border-[#d7dee8] text-xs font-semibold text-[#182438] hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#1982c4]">Previous</a>
        @endif

        @foreach($interventions->getUrlRange(1, $interventions->lastPage()) as $page => $url)
            @if($page === $interventions->currentPage())
                <span class="min-w-9 px-3 py-2 rounded-lg bg-[#1982c4] text-white text-xs font-bold text-center" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="min-w-9 px-3 py-2 rounded-lg border border-[#d7dee8] bg-white text-[#182438] text-xs font-semibold text-center hover:bg-[#eef6fc] focus:outline-none focus:ring-2 focus:ring-[#1982c4]" aria-label="Go to intervention page {{ $page }}">{{ $page }}</a>
            @endif
        @endforeach

        @if($interventions->hasMorePages())
            <a href="{{ $interventions->nextPageUrl() }}" rel="next" class="px-3 py-2 rounded-lg border border-[#d7dee8] text-xs font-semibold text-[#182438] hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#1982c4]">Next</a>
        @else
            <span class="px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-400 bg-gray-50" aria-disabled="true">Next</span>
        @endif
    </div>
</nav>
