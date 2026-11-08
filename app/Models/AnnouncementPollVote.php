<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementPollVote extends Model
{
    protected $fillable = [
        'announcement_poll_id',
        'announcement_poll_option_id',
        'user_id',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(AnnouncementPoll::class, 'announcement_poll_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AnnouncementPollOption::class, 'announcement_poll_option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
