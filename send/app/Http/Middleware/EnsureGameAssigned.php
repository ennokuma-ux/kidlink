<?php

namespace App\Http\Middleware;

use App\Games\GameCatalog;
use App\Models\Student;
use App\Models\StudentGameAssignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGameAssigned
{
    public function handle(Request $request, Closure $next, string $gameSlug): Response
    {
        abort_unless(GameCatalog::game($gameSlug), 404);

        $student = Student::where('user_id', $request->user()->id)->first();
        if (!$student) {
            abort(403, 'No student profile found for this account.');
        }

        $assigned = StudentGameAssignment::query()
            ->where('game_slug', $gameSlug)
            ->where(function ($query) use ($student) {
                $query->where('student_id', $student->id);
                if ($student->class_name) {
                    $query->orWhere(function ($classQuery) use ($student) {
                        $classQuery->whereNull('student_id')
                            ->where('teacher_id', $student->teacher_id)
                            ->where('class_name', $student->class_name);
                    });
                }
            })
            ->exists();

        if (!$assigned) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your teacher has not assigned this game to you.'], 403);
            }
            return redirect()->route('student.home')->with('error', 'Your teacher has not assigned this game to you yet.');
        }

        return $next($request);
    }
}
