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
                // Replaying a level is allowed and keeps the best result.
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

                // Points are capped at what a flawless-as-possible run of this level is
                // worth: a replay only pays the part not already paid for it, so a level
                // finished before points existed (or one improved on) is credited once,
                // and replaying the same result can never farm more.
                $topUp = 0;
                $attemptPoints = $this->pointsFor($expectedTotal, $mistakes);
                $alreadyPaid = (int) Point::where('student_id', $student->id)
                    ->where('game_type', $gameType)
                    ->where('level', $level)
                    ->sum('points_earned');

                if ($attemptPoints > $alreadyPaid) {
                    $topUp = $attemptPoints - $alreadyPaid;

                    Point::awardPoints(
                        studentId: $student->id,
                        gameType: $gameType,
                        level: $level,
                        points: $topUp,
                        source: 'level_improved',
                        metadata: [
                            'score' => $score,
                            'total' => $expectedTotal,
                            'percentage' => $percentage,
                            'mistakes' => $mistakes,
                            'time_taken' => $timeTaken,
                        ]
                    );
                }

                return [
                    'recorded' => $topUp > 0,
                    'completed' => true,
                    'improved' => $improved,
                    'score' => $score,
                    'total' => $expectedTotal,
                    'mistakes' => $mistakes,
                    'percentage' => $percentage,
                    'points_earned' => $topUp,
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

            $pointsEarned = $this->pointsFor($expectedTotal, $mistakes);

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

    /** Points a single correct answer is worth, so a level's total score is total x this. */
    public const POINTS_PER_QUESTION = 10;

    /** Extra points for a level finished without a single mistake. */
    public const FLAWLESS_BONUS = 25;

    /**
     * Points follow the accuracy percentage: the level's total score is every
     * question at full value, and each mistake lowers the percentage of that
     * total that is paid out (5 questions, 1 mistake = 83% of 50 points).
     * A finished stage always answers every question, so only mistakes can
     * take points away. A mistake-free stage also earns the flat bonus.
     */
    private function pointsFor(int $total, int $mistakes): int
    {
        $levelTotal = $total * self::POINTS_PER_QUESTION;
        $percentage = $this->percentage($total, $total, $mistakes);

        $points = (int) round($levelTotal * $percentage / 100);

        if ($mistakes === 0) {
            $points += self::FLAWLESS_BONUS;
        }

        return max(0, $points);
    }

    private function percentage(int $score, int $total, int $mistakes): int
    {
        $attempts = $total + $mistakes;

        return $attempts > 0 ? (int) round(($score / $attempts) * 100) : 0;
    }
}