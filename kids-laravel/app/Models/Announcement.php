<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'body',
    ];

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Announcements are broadcast, so the newest always leads. The id tiebreak
     * keeps the order stable when several land in the same second.
     */
    public function scopeNewestFirst($query)
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }
}
