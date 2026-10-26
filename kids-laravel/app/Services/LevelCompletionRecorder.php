<?php

namespace App\Services;

use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Writes a level result, and only once the level is actually finished.
 *
 * The browser used to be able to hand us any score it liked, which meant a
 * half-played stage, a double-tapped submit button or a retried request could
 * each leave a row behind. Those rows then unlocked the next level and landed in
 * the reports, so points could be farmed by replaying one level.
 */
class LevelCompletionRecorder
{
    /**
     * @return array{recorded: bool, completed: bool, improved: bool, score: int, total: int, mistakes: int, percentage: int, points_earned: int}
     */
    public function record(
        Student $student,
        string $gameType,
        int $level,
        int $expectedTotal,
        int $score,
        int $mistakes = 0,
        ?int $timeTaken = null
    ): array {
        if ($score < 0 || $mistakes < 0) {
            throw new InvalidArgumentException('A score cannot be negative.');
        }

        if ($expectedTotal < 1) {
            throw new InvalidArgumentException("Level {$level} of {$gameType} has nothing to answer.");
        }

        if ($score > $expectedTotal) {
            throw new InvalidArgumentException(
                "Reported {$score} of {$expectedTotal} for {$gameType} level {$level}."
            );
        }

        // Every question in a stage has to be answered before the stage counts as
        // finished, so an abandoned attempt is not recorded, does not unlock the
        // next level and never reaches the reports.
        if ($score !== $expectedTotal) {
            return [
                'recorded' => false,
                'completed' => false,
                'improved' => false,
                'score' => $score,
                'total' => $expectedTotal,
                'mistakes' => $mistakes,
                'percentage' => $this->percentage($score, $expectedTotal, $mistakes),
                'points_earned' => 0,
            ];
        }

        return DB::transaction(function () use ($student, $gameType, $level, $expectedTotal, $score, $mistakes, $timeTaken) {
            $existing = GameScore::where('student_id', $student->id)
                ->where('game_type', $gameType)
                ->where('level', $level)
                ->lockForUpdate()
                ->first();

            $percentage = $this->percentage($score, $expectedTotal, $mistakes);

            if ($existing) {
                // Replaying a level is allowed and keeps the best result, but the
                // points were already paid the first time round.
                $improved = $score > $existing->score
                    || $percentage > (int) $existing->percentage;

                if ($improved) {
                    $existing->update([
                        'score' => $score,
                        'total' => $expectedTotal,
                        'mistakes' => $mistakes,
                        'percentage' => $percentage,
                        'time_taken' => $timeTaken,
                    ]);
                }

                return [
                    'recorded' => false,
                    'completed' => true,
                    'improved' => $improved,
                    'score' => $score,
                    'total' => $expectedTotal,
                    'mistakes' => $mistakes,
                    'percentage' => $percentage,
                    'points_earned' => 0,
                ];
            }

            GameScore::create([
                'student_id' => $student->id,
                'game_type' => $gameType,
                'level' => $level,
                'score' => $score,
                'total' => $expectedTotal,
                'mistakes' => $mistakes,
                'percentage' => $percentage,
                'time_taken' => $timeTaken,
            ]);

            $pointsEarned = $this->pointsFor($score, $mistakes);

            Point::awardPoints(
                studentId: $student->id,
                gameType: $gameType,
                level: $level,
                points: $pointsEarned,
                source: 'level_complete',
                metadata: [
                    'score' => $score,
                    'total' => $expectedTotal,
                    'percentage' => $percentage,
                    'mistakes' => $mistakes,
                    'time_taken' => $timeTaken,
                ]
            );

            return [
                'recorded' => true,
                'completed' => true,
                'improved' => false,
                'score' => $score,
                'total' => $expectedTotal,
                'mistakes' => $mistakes,
                'percentage' => $percentage,
                'points_earned' => $pointsEarned,
            ];
        });
    }

    /**
     * There is no perfect-score bonus any more: a stage is only ever recorded
     * once it is finished, so every recorded score is a full one and the bonus
     * would be a flat amount added to everybody.
     */
    private function pointsFor(int $score, int $mistakes): int
    {
        $points = $score * 10;

        if ($mistakes === 0) {
            $points += 25;
        }

        return $points;
    }

    private function percentage(int $score, int $total, int $mistakes): int
    {
        $attempts = $total + $mistakes;

        return $attempts > 0 ? (int) round(($score / $attempts) * 100) : 0;
    }
}