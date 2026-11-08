<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    protected $fillable = [
        'student_id',
        'game_type',
        'level',
        'points_earned',
        'total_points',
        'source',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public static function awardPoints(int $studentId, string $gameType, int $level, int $points, string $source, array $metadata = []): self
    {
        $lastTotal = static::where('student_id', $studentId)
            ->where('game_type', $gameType)
            ->latest()
            ->value('total_points') ?? 0;

        $newTotal = $lastTotal + $points;

        return static::create([
            'student_id' => $studentId,
            'game_type' => $gameType,
            'level' => $level,
            'points_earned' => $points,
            'total_points' => $newTotal,
            'source' => $source,
            'metadata' => $metadata,
        ]);
    }

    public static function getTotalPoints(int $studentId, string $gameType = null): int
    {
        $query = static::where('student_id', $studentId);
        
        if ($gameType) {
            $query->where('game_type', $gameType);
        }

        return $query->sum('points_earned');
    }

    public static function getTotalPointsByGame(int $studentId): array
    {
        return static::where('student_id', $studentId)
            ->selectRaw('game_type, SUM(points_earned) as total')
            ->groupBy('game_type')
            ->pluck('total', 'game_type')
            ->toArray();
    }

    public static function getRecentActivity(int $studentId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('student_id', $studentId)
            ->latest()
            ->limit($limit)
            ->get();
    }
}