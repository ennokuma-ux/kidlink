@extends('layouts.teacher')

@section('title', 'Game Assignments')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-[#ff8555]">Learning activities</p>
        <h1 class="mt-1 text-3xl font-extrabold text-[#182438]">Game Assignments</h1>
        <p class="mt-2 max-w-3xl text-sm text-gray-500">Choose the games each student or class can access. Games that are not assigned are hidden from students and blocked if they try to open a direct link.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-[#d0eb9c] bg-[#f3fae4] px-4 py-3 text-sm font-semibold text-[#3e610c]">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-[#ffc9cb] bg-[#fff1f1] px-4 py-3 text-sm text-[#c9343a]">Please review the assignment form and try again.</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-[#182438]">What type of assignment would you like to create?</h2>
        <p class="mt-1 text-sm text-gray-500">Choose an assignment type to show only the fields you need.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <button type="button" data-assignment-mode="class" aria-pressed="false" class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 text-left transition hover:border-orange-300 hover:bg-orange-50/50 focus:outline-none focus:ring-2 focus:ring-orange-200">
                <span class="rounded-lg bg-orange-100 p-2 text-orange-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 8v6m3-3h-6"/></svg></span>
                <span><span class="block font-bold text-gray-800">Assign to a class</span><span class="mt-1 block text-sm text-gray-500">Choose a class and the games everyone in it can play.</span></span>
            </button>
            <button type="button" data-assignment-mode="individual" aria-pressed="false" class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 text-left transition hover:border-orange-300 hover:bg-orange-50/50 focus:outline-none focus:ring-2 focus:ring-orange-200">
                <span class="rounded-lg bg-[#d3e8f6] p-2 text-[#0f5483]"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg></span>
                <span><span class="block font-bold text-gray-800">Assign to individual students</span><span class="mt-1 block text-sm text-gray-500">Search for a student and choose their games.</span></span>
            </button>
        </div>
    </section>

    <section id="classAssignmentSection" data-assignment-section="class" class="hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div><h2 class="text-xl font-bold text-[#182438]">Assign to a whole class</h2><p class="mt-1 text-sm text-gray-500">All current and future students in the selected class taught by you will receive these games.</p></div>
        </div>
        @if($classNames->isEmpty())
            <p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">No classes have active students assigned to you yet.</p>
        @else
        <form method="POST" action="{{ route('teacher.game-assignments.class.update') }}" class="space-y-5">
            @csrf @method('PUT')
            <div class="max-w-md"><label for="class_name" class="mb-1 block text-sm font-semibold text-gray-700">Class</label><select id="class_name" name="class_name" required class="w-full rounded-xl border-gray-300 focus:border-orange-400 focus:ring-orange-300"><option value="">Choose a class</option>@foreach($classNames as $className)<option value="{{ $className }}" @selected(old('class_name') === $className)>{{ $className }}</option>@endforeach</select></div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach($games as $game)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-3 transition hover:border-orange-300 hover:bg-orange-50/40"><input type="checkbox" name="games[]" value="{{ $game['slug'] }}" class="mt-1 rounded border-gray-300 text-orange-500 focus:ring-orange-300"><span><span class="block font-bold text-gray-800">{{ $game['name'] }}</span><span class="mt-0.5 block text-xs text-gray-500">{{ $game['desc'] }}</span></span></label>
            @endforeach</div>
            <button class="rounded-xl bg-[#ff8555] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600">Save class assignments</button>
            <p class="text-xs text-gray-400">Saving replaces the selected class's current game list. Leave every game unchecked to remove all class assignments.</p>
        </form>
        @endif
    </section>

    <section id="individualAssignmentSection" data-assignment-section="individual" class="hidden space-y-4">
        <div><h2 class="text-xl font-bold text-[#182438]">Assign to individual students</h2><p class="mt-1 text-sm text-gray-500">Individual assignments are added alongside class assignments. A student can play a game if it is assigned to them directly or to their class.</p></div>
        @if($students->count() > 0)
            <div class="grid max-w-3xl gap-3 sm:grid-cols-[minmax(0,1fr)_16rem]">
                <div class="relative">
                    <label for="gameAssignmentStudentSearch" class="mb-1 block text-sm font-semibold text-gray-700">Search students</label>
                    <span class="pointer-events-none absolute bottom-0 inset-y-auto flex h-[46px] items-center pl-4 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></span>
                    <input id="gameAssignmentStudentSearch" type="search" placeholder="Search by student name..." autocomplete="off" class="w-full rounded-xl border border-gray-300 bg-white py-3 pl-11 pr-4 text-sm text-gray-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </div>
                <div>
                    <label for="gameAssignmentSectionFilter" class="mb-1 block text-sm font-semibold text-gray-700">Filter by section</label>
                    <select id="gameAssignmentSectionFilter" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        <option value="">All sections</option>
                        @foreach($classNames as $className)
                            <option value="{{ strtolower($className) }}">{{ $className }}</option>
                        @endforeach
                        <option value="__unassigned">Unassigned section</option>
                    </select>
                </div>
                <p id="gameAssignmentStudentSearchCount" class="text-xs text-gray-500 sm:col-span-2">Showing {{ $students->count() }} students</p>
            </div>
            <p id="gameAssignmentStudentNoResults" class="hidden rounded-xl border border-dashed border-gray-300 bg-white p-5 text-sm text-gray-500">No students match your search.</p>
        @endif
        <div id="gameAssignmentStudentList" class="space-y-3">
        @forelse($students as $student)
            @php
                $directSlugs = $assignments->where('student_id', $student->id)->pluck('game_slug')->all();
                $classSlugs = $assignments->whereNull('student_id')->where('class_name', $student->class_name)->pluck('game_slug')->all();
            @endphp
            <details data-assignment-student data-student-name="{{ strtolower($student->name) }}" data-student-class="{{ strtolower($student->class_name ?? '') }}" class="group rounded-2xl border border-gray-200 bg-white shadow-sm" {{ $loop->first ? 'open' : '' }}>
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 p-4 sm:p-5"><span><span class="block font-bold text-[#182438]">{{ $student->name }}</span><span class="mt-1 block text-xs text-gray-500">{{ $student->class_name ?: 'No class' }} · {{ count(array_unique(array_merge($directSlugs, $classSlugs))) }} game(s) available including class assignments</span></span><span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700 group-open:hidden">Edit games</span><span class="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600 group-open:inline">Close</span></summary>
                <div class="border-t border-gray-100 p-4 sm:p-5">
                    <form method="POST" action="{{ route('teacher.game-assignments.student.update', $student) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach($games as $game)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-3 transition hover:border-orange-300 hover:bg-orange-50/40"><input type="checkbox" name="games[]" value="{{ $game['slug'] }}" @checked(in_array($game['slug'], $directSlugs, true)) class="mt-1 rounded border-gray-300 text-orange-500 focus:ring-orange-300"><span><span class="block font-bold text-gray-800">{{ $game['name'] }}</span><span class="mt-0.5 block text-xs text-gray-500">{{ in_array($game['slug'], $classSlugs, true) ? 'Also assigned to class' : $game['desc'] }}</span></span></label>
                        @endforeach</div>
                        <button class="rounded-xl bg-[#182438] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#263750]">Save student assignments</button>
                        <p class="text-xs text-gray-400">This saves direct assignments only. Class assignments remain in effect. Leave all unchecked to remove this student's direct assignments.</p>
                    </form>
                </div>
            </details>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-500">No active students are currently assigned to your teacher account.</div>
        @endforelse
        </div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modeButtons = [...document.querySelectorAll('[data-assignment-mode]')];
    const assignmentSections = [...document.querySelectorAll('[data-assignment-section]')];
    const setAssignmentMode = mode => {
        assignmentSections.forEach(section => section.classList.toggle('hidden', section.dataset.assignmentSection !== mode));
        modeButtons.forEach(button => {
            const selected = button.dataset.assignmentMode === mode;
            button.setAttribute('aria-pressed', String(selected));
            button.classList.toggle('border-orange-400', selected);
            button.classList.toggle('bg-orange-50', selected);
            button.classList.toggle('ring-1', selected);
            button.classList.toggle('ring-orange-200', selected);
        });
    };
    modeButtons.forEach(button => button.addEventListener('click', () => setAssignmentMode(button.dataset.assignmentMode)));

    const search = document.getElementById('gameAssignmentStudentSearch');
    const list = document.getElementById('gameAssignmentStudentList');
    if (!search || !list) return;
    const cards = [...list.querySelectorAll('[data-assignment-student]')];
    const sectionFilter = document.getElementById('gameAssignmentSectionFilter');
    const count = document.getElementById('gameAssignmentStudentSearchCount');
    const empty = document.getElementById('gameAssignmentStudentNoResults');
    const filter = () => {
        const term = search.value.trim().toLocaleLowerCase();
        const selectedSection = sectionFilter ? sectionFilter.value : '';
        let visible = 0;
        cards.forEach(card => {
            const studentClass = (card.dataset.studentClass || '').trim().toLocaleLowerCase();
            const matchesSearch = `${card.dataset.studentName} ${studentClass}`.includes(term);
            const matchesSection = !selectedSection
                || (selectedSection === '__unassigned' ? !studentClass : studentClass === selectedSection);
            const match = matchesSearch && matchesSection;
            card.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        count.textContent = `Showing ${visible} of ${cards.length} students`;
        empty.classList.toggle('hidden', visible !== 0);
    };
    search.addEventListener('input', filter);
    if (sectionFilter) sectionFilter.addEventListener('change', filter);
});
</script>
@endsection
