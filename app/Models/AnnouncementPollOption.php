<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementPollOption extends Model
{
    protected $fillable = [
        'announcement_poll_id',
        'label',
        'position',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(AnnouncementPoll::class, 'announcement_poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(AnnouncementPollVote::class, 'announcement_poll_option_id');
    }
}
