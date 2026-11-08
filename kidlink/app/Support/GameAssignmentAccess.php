<?php

namespace App\Support;

use App\Games\GameCatalog;
use App\Models\Student;
use App\Models\StudentGameAssignment;

class GameAssignmentAccess
{
    public static function allows(Student $student, string $slug): bool
    {
        if (!GameCatalog::game($slug)) {
            return false;
        }

        return StudentGameAssignment::query()->where('game_slug', $slug)
            ->where(function ($query) use ($student) {
                $query->where('student_id', $student->id);
                if ($student->class_name) {
                    $query->orWhere(function ($classQuery) use ($student) {
                        $classQuery->whereNull('student_id')
                            ->where('teacher_id', $student->teacher_id)
                            ->where('class_name', $student->class_name);
                    });
                }
            })->exists();
    }

    /**
     * Every game slug this student may play, in one query.
     *
     * @return array<int, string>
     */
    public static function slugsFor(Student $student): array
    {
        return StudentGameAssignment::query()
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
            ->pluck('game_slug')
            ->unique()
            ->values()
            ->all();
    }
}
