<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRequest extends Model
{
    protected $fillable = [
        'requested_by', 'teacher_id', 'parent_ids', 'role', 'first_name', 'last_name', 'email', 'contact',
        'student_id', 'sex', 'date_of_birth', 'address', 'class_name', 'dominant_hand', 'father_name', 'father_contact', 'mother_name', 'mother_contact', 'siblings_count', 'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = ['reviewed_at' => 'datetime', 'parent_ids' => 'array'];

    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
