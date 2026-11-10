<?php

namespace App\Http\Controllers\Teacher;

use App\Games\GameCatalog;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentGameAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GameAssignmentController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        $students = Student::where('teacher_id', $teacherId)->whereRaw('is_active = true')
            ->orderBy('class_name')->orderBy('first_name')->orderBy('last_name')->get();
        $games = GameCatalog::all();
        $assignments = StudentGameAssignment::where('teacher_id', $teacherId)->get();
        $classNames = $students->pluck('class_name')->filter()->unique()->sort()->values();

        return view('teacher.game-assignments', compact('students', 'games', 'assignments', 'classNames'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        abort_unless((int) $student->teacher_id === (int) auth()->id(), 403);
        $slugs = $this->validatedSlugs($request);

        DB::transaction(function () use ($student, $slugs) {
            StudentGameAssignment::where('teacher_id', auth()->id())->where('student_id', $student->id)->delete();
            foreach ($slugs as $slug) {
                StudentGameAssignment::create([
                    'teacher_id' => auth()->id(), 'student_id' => $student->id,
                    'class_name' => null, 'game_slug' => $slug, 'assigned_by' => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'Game assignments saved for '.$student->name.'.');
    }

    public function updateClass(Request $request)
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', Rule::in(Student::SECTIONS)],
            'games' => ['nullable', 'array'],
            'games.*' => ['required', 'string', Rule::in(collect(GameCatalog::all())->pluck('slug')->all())],
        ]);
        abort_unless(Student::where('teacher_id', auth()->id())->where('class_name', $validated['class_name'])->exists(), 404);
        $slugs = array_values(array_unique($validated['games'] ?? []));

        DB::transaction(function () use ($validated, $slugs) {
            StudentGameAssignment::where('teacher_id', auth()->id())->whereNull('student_id')
                ->where('class_name', $validated['class_name'])->delete();
            foreach ($slugs as $slug) {
                StudentGameAssignment::create([
                    'teacher_id' => auth()->id(), 'student_id' => null,
                    'class_name' => $validated['class_name'], 'game_slug' => $slug, 'assigned_by' => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'Class game assignments saved for '.$validated['class_name'].'.');
    }

    private function validatedSlugs(Request $request): array
    {
        $validated = $request->validate([
            'games' => ['nullable', 'array'],
            'games.*' => ['required', 'string', Rule::in(collect(GameCatalog::all())->pluck('slug')->all())],
        ]);
        return array_values(array_unique($validated['games'] ?? []));
    }
}
