@if($children->count() > 1)
<div class="mb-6" x-data="childSelector()">
    <div class="flex items-center gap-3 mb-2">
        <span class="text-sm font-semibold text-[#5f6873]">{{ __('child.viewing') }}</span>
        <div class="relative" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 hover:border-orange-300 transition shadow-sm">
                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg, #258cc5, #ff8555);">
                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                </span>
                {{ $child->first_name }} {{ $child->last_name }}
                <svg class="w-4 h-4 text-gray-400 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden" style="display: none;">
                <div class="p-2 border-b border-gray-100">
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input x-model="search" type="text" placeholder="{{ __('child.search_children_placeholder') }}" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-orange-300 focus:ring-1 focus:ring-orange-200" @keydown.escape="open = false" x-ref="searchInput" x-init="$watch('open', v => { if(v) $nextTick(() => $refs.searchInput.focus()) })">
                    </div>
                </div>
                <div class="max-h-64 overflow-y-auto py-1">
                    <template x-for="c in filtered" :key="c.id">
                        <a :href="'?child_id=' + c.id" class="flex items-center gap-3 px-4 py-2.5 text-sm transition hover:bg-orange-50" :class="{ 'bg-orange-50': c.id == {{ $child->id }} }">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" :style="'background: linear-gradient(135deg, ' + (c.id == {{ $child->id }} ? '#ff8555, #f47548' : '#258cc5, #1f6f9e') + ')'">
                                <span x-text="c.initials"></span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-700 truncate" x-text="c.name"></p>
                                <p class="text-xs text-gray-400" x-text="childLabels.age + c.age + (c.class_name ? ' · ' + c.class_name : '')"></p>
                            </div>
                            <svg x-show="c.id == {{ $child->id }}" class="w-5 h-5 text-orange-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </a>
                    </template>
                    <p x-show="filtered.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">{{ __('child.no_matching_children') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const childLabels = {!! json_encode(['age' => __('child.age_prefix')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
function childSelector() {
    return {
        open: false,
        search: '',
        children: {!! $children->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->first_name . ' ' . $c->last_name,
            'initials' => strtoupper(substr($c->first_name, 0, 1)) . strtoupper(substr($c->last_name, 0, 1)),
            'age' => $c->age,
            'class_name' => $c->class_name ?? '',
        ])->values() !!},
        get filtered() {
            if (!this.search.trim()) return this.children;
            const q = this.search.toLowerCase();
            return this.children.filter(c => c.name.toLowerCase().includes(q) || c.class_name.toLowerCase().includes(q));
        }
    }
}
</script>
@endif
