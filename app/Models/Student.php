<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'sex',
        'date_of_birth',
        'address',
        'dominant_hand',
        'parent_id',
        'teacher_id',
        'user_id',
        'enrolled_date',
        'is_active',
        'father_name',
        'father_contact',
        'mother_name',
        'mother_contact',
        'siblings_count',
        'class_name',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrolled_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function parents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withTimestamps();
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function activityAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityAttendance::class);
    }

    public function eccdRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EccdRecord::class);
    }

    public function gameScores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth?->age ?? 0;
    }

    public function getNameAttribute(): string
    {
        return $this->full_name ?? "{$this->first_name} {$this->last_name}";
    }
}
