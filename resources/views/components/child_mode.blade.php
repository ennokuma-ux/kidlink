<button type="button" id="childModeBtn"
    class="flex items-center gap-2 bg-gradient-to-r from-[#68b82f] to-[#4e8a2f] hover:from-[#5da32a] hover:to-[#3f7a26] text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Child Mode
</button>

<div id="childModeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-xl font-bold text-[#182438]">Choose a Child</h3>
                <p class="text-sm text-[#5f6873]">Log in instantly as a student to play educational games.</p>
            </div>
            <button type="button" id="childModeClose" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <div class="relative mb-4">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
            <input type="text" id="childModeSearch" placeholder="Search children..." autocomplete="off"
                class="w-full pl-9 pr-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
        <div id="childModeList" class="space-y-2 max-h-72 overflow-y-auto">
            @forelse($children as $child)
            <form method="POST" action="{{ route('child-mode.enter', $child) }}" class="child-mode-item">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 border border-[#ebe8e1] rounded-xl hover:border-[#ff8555] hover:bg-[#faf8f3] transition text-left">
                    <div class="w-11 h-11 bg-gradient-to-br from-[#258cc5] to-[#ff8555] rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-[#182438]">{{ $child->name }}</p>
                        <p class="text-xs text-[#5f6873]">Age: {{ $child->age }} | Class: {{ $child->class_name ?? 'N/A' }}</p>
                    </div>
                    <span class="text-[#ff8555] font-semibold text-sm">Play</span>
                </button>
            </form>
            @empty
            <p class="text-center text-[#8b949d] py-6">No children available.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
const childModeBtn = document.getElementById('childModeBtn');
const childModeModal = document.getElementById('childModeModal');
const childModeClose = document.getElementById('childModeClose');
const childModeSearch = document.getElementById('childModeSearch');

if (childModeBtn && childModeModal) {
    childModeBtn.addEventListener('click', function () {
        childModeModal.classList.remove('hidden');
    });
    childModeClose.addEventListener('click', function () {
        childModeModal.classList.add('hidden');
        childModeSearch.value = '';
        document.querySelectorAll('.child-mode-item').forEach(item => item.style.display = '');
    });
    childModeModal.addEventListener('click', function (e) {
        if (e.target === this) {
            this.classList.add('hidden');
            childModeSearch.value = '';
            document.querySelectorAll('.child-mode-item').forEach(item => item.style.display = '');
        }
    });
    childModeSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.child-mode-item').forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}
</script>