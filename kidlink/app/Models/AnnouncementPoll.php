<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementPoll extends Model
{
    public const MIN_OPTIONS = 2;

    public const MAX_OPTIONS = 6;

    protected $fillable = [
        'announcement_id',
        'question',
        'allow_multiple',
        'closes_at',
    ];

    protected $casts = [
        'allow_multiple' => 'boolean',
        'closes_at' => 'datetime',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AnnouncementPollOption::class)->orderBy('position')->orderBy('id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(AnnouncementPollVote::class);
    }

    /**
     * Closed once its end time has passed, or when its announcement has been
     * archived (a poll on an archived post stays readable but cannot be voted on).
     */
    public function isClosed(): bool
    {
        if ($this->closes_at !== null && $this->closes_at->isPast()) {
            return true;
        }

        return $this->announcement?->isArchived() ?? false;
    }
}
