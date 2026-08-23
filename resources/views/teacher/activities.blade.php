@extends('layouts.teacher')

@section('title', 'Activity Planner')
@section('page-title', 'Activity Planner')

@section('content')
<div class="grid grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ff8555]">
        <p class="text-2xl font-bold text-[#182438]">{{ $stats['total'] }}</p>
        <p class="text-sm text-[#5f6873]">Total Activities</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#68b82f]">
        <p class="text-2xl font-bold text-[#68b82f]">{{ $stats['completed'] }}</p>
        <p class="text-sm text-[#5f6873]">Completed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#d9b348]">
        <p class="text-2xl font-bold text-[#d9b348]">{{ $stats['pending'] }}</p>
        <p class="text-sm text-[#5f6873]">Pending</p>
    </div>
</div>

<div class="flex justify-between items-center mb-6">
    <div class="flex gap-3">
        <button onclick="document.getElementById('addActivityModal').classList.remove('hidden')" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">
            + Add Activity
        </button>
    </div>
    <select id="typeFilter" onchange="filterActivities()" class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        <option value="all">All Types</option>
        <option value="Lesson">Lesson</option>
        <option value="Craft">Craft</option>
        <option value="Music">Music</option>
        <option value="Story">Story</option>
        <option value="Movement">Movement</option>
    </select>
</div>

<div class="grid grid-cols-5 gap-4">
    @foreach($days as $day)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-[#ff8555] text-white text-center py-3 font-semibold">{{ $day }}</div>
        <div class="p-3 space-y-2 min-h-[200px]">
            @forelse(($activities[$day] ?? collect()) as $activity)
            <div class="p-3 rounded-lg border text-sm activity-card" data-type="{{ $activity->type }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-semibold text-[#182438]">{{ $activity->title }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        {{ $activity->type === 'Lesson' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $activity->type === 'Craft' ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $activity->type === 'Music' ? 'bg-pink-100 text-pink-700' : '' }}
                        {{ $activity->type === 'Story' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $activity->type === 'Movement' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ $activity->type }}
                    </span>
                </div>
                <p class="text-[#5f6873] text-xs">{{ substr($activity->start_time, 0, 5) }} - {{ substr($activity->end_time, 0, 5) }}</p>
                @if($activity->group_name)<p class="text-[#8b949d] text-xs mt-1">Group: {{ $activity->group_name }}</p>@endif
                <div class="flex items-center justify-between mt-2 pt-2 border-t">
                    <form method="POST" action="{{ route('teacher.activities.toggle', $activity) }}" class="inline">
                        @csrf @method('PUT')
                        <button type="submit" class="text-xs {{ $activity->is_completed ? 'text-[#68b82f]' : 'text-[#8b949d]' }}">
                            {{ $activity->is_completed ? 'Completed' : 'Mark Done' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('teacher.activities.destroy', $activity) }}" class="inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-[#8b949d] text-xs text-center py-4">No activities</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<div id="addActivityModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">New Activity</h3>
            <button onclick="this.closest('#addActivityModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" action="{{ route('teacher.activities.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Title</label>
                <input type="text" name="title" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Type</label>
                <select name="type" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="Lesson">Lesson</option>
                    <option value="Craft">Craft</option>
                    <option value="Music">Music</option>
                    <option value="Story">Story</option>
                    <option value="Movement">Movement</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Day</label>
                <select name="day_of_week" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    @foreach($days as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Start Time</label>
                    <input type="time" name="start_time" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">End Time</label>
                    <input type="time" name="end_time" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Group (optional)</label>
                <input type="text" name="group_name" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#addActivityModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#182438] hover:bg-[#faf8f3] transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">Add Activity</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterActivities() {
    const type = document.getElementById('typeFilter').value;
    document.querySelectorAll('.activity-card').forEach(card => {
        card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
    });
}
</script>
@endpush


