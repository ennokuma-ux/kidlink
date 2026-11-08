<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGameAssignment extends Model
{
    protected $fillable = ['teacher_id', 'student_id', 'class_name', 'game_slug', 'assigned_by'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
