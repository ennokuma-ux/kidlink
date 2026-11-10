<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EccdRecord extends Model
{
    use HasFactory;

    protected $table = 'eccd_records';

    protected $fillable = [
        'student_id',
        'age_group',
        'domain',
        'indicator',
        'age_band',
        'notes',
        'status',
        'assessed_by',
        'assessed_at',
    ];

    protected $casts = [
        'assessed_at' => 'date',
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assessor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
