<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    /**
     * Labels an announcement can carry, in order of severity. Each entry is
     * the stored value => the English label; views pick the colours from
     * tagStyle() so the badge looks the same on every screen.
     */
    public const TAGS = [
        'urgent' => 'Urgent',
        'emergency' => 'Emergency',
    ];

    protected $fillable = [
        'author_id',
        'title',
        'body',
        'tag',
        'is_pinned',
        'pinned_at',
        'archived_at',
        'archived_by',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'pinned_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function archiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function poll(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AnnouncementPoll::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnnouncementComment::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Announcements are broadcast, so the newest always leads. The id tiebreak
     * keeps the order stable when several land in the same second.
     */
    public function scopeNewestFirst($query)
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * The order of the board: pinned posts first (most recently pinned on
     * top), then everything else newest first.
     */
    public function scopeBoardOrder($query)
    {
        return $query->orderBy('is_pinned', 'desc')
            ->orderBy('pinned_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function tagLabel(): ?string
    {
        return $this->tag && isset(self::TAGS[$this->tag]) ? __(self::TAGS[$this->tag]) : null;
    }

    /**
     * Tailwind classes for the tag badge and the card accent.
     *
     * @return array{badge: string, card: string}|null
     */
    public function tagStyle(): ?array
    {
        return match ($this->tag) {
            'emergency' => [
                'badge' => 'bg-[#ff595e] text-white',
                'card' => 'border-[#ff595e] bg-[#fff5f4]',
            ],
            'urgent' => [
                'badge' => 'bg-[#ffca3a] text-[#3d2a00]',
                'card' => 'border-[#ffd97a] bg-[#fffbf0]',
            ],
            default => null,
        };
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * The moment after which a post counts as "new" for this user. Users who
     * have never opened the board fall back to when their account was made.
     */
    public static function seenCutoffFor(User $user)
    {
        return $user->announcements_seen_at ?? $user->created_at;
    }

    /**
     * Active announcements posted by someone else since the user last opened
     * the board. Cached for the request because the sidebar and the mobile
     * menu button both ask for it.
     */
    public static function unseenCountFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        static $cache = [];
        $key = $user->id.'|'.optional($user->announcements_seen_at)->timestamp;

        return $cache[$key] ??= (function () use ($user) {
            try {
                $query = static::active()->where('author_id', '!=', $user->id);

                if ($cutoff = static::seenCutoffFor($user)) {
                    $query->where('created_at', '>', $cutoff);
                }

                return $query->count();
            } catch (\Throwable $e) {
                return 0;
            }
        })();
    }
}
