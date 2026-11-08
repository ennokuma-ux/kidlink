<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-slate-900">Individual Analytics</h2>
        <p class="mt-1 text-sm text-slate-600">Search by a student's full name or nickname.</p>
    </div>

    <form method="GET" action="{{ route('teacher.analytics.individual.index') }}" class="flex flex-col gap-3 sm:flex-row">
        <label for="student-search" class="sr-only">Search students</label>
        <input
            id="student-search"
            name="q"
            type="search"
            value="{{ request('q') }}"
            placeholder="Enter full name or nickname"
            autocomplete="off"
            class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
        >
        <button
            type="submit"
            class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300"
        >
            Search
        </button>
    </form>

    @if (isset($students))
        <div class="mt-5">
            @if ($students->isEmpty())
                <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    No students found. Try another name or nickname.
                </p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($students as $student)
                        <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-slate-900">{{ $student->full_name ?? $student->fullname ?? $student->name }}</p>
                                @if (!empty($student->nickname))
                                    <p class="text-sm text-slate-500">Nickname: {{ $student->nickname }}</p>
                                @endif
                            </div>
                            <a
                                href="{{ route('teacher.analytics.individual.show', $student->id) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-orange-200 px-4 py-2 text-sm font-semibold text-orange-700 transition hover:bg-orange-50"
                            >
                                View analytics
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if (method_exists($students, 'links'))
                    <div class="mt-4">{{ $students->withQueryString()->links() }}</div>
                @endif
            @endif
        </div>
    @endif
</section>
