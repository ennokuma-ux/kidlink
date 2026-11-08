<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'student_id',
        'date',
        'status',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Attendance rate as a percentage. A late child still came to school, so
     * Present and Late both count as attended; Excused days are left out of the
     * calculation so they never count against the child.
     */
    public static function rate(int $present, int $late, int $absent): int
    {
        $counted = $present + $late + $absent;

        return $counted > 0 ? (int) round((($present + $late) / $counted) * 100) : 0;
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function marker(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
