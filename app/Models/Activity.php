<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'day_of_week',
        'start_time',
        'end_time',
        'group_name',
        'teacher_id',
        'week_start',
        'is_completed',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'week_start' => 'date',
        'is_completed' => 'boolean',
    ];

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityAttendance::class);
    }
}
