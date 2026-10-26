<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameScore extends Model
{
    use HasFactory;

    protected $table = 'game_scores';

    protected $fillable = [
        'student_id',
        'game_type',
        'level',
        'score',
        'total',
        'mistakes',
        'percentage',
        'time_taken',
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
