<?php

namespace Tests\Feature;

use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * A level must only reach the database once the child has actually finished it,
 * and it must reach it exactly once no matter how many times the browser posts.
 *
 * Every game is covered here because the scoring path is duplicated across the
 * five controllers, and a fix that only reaches one of them leaves the others
 * still awarding points for a stage the child walked away from.
 */
class LevelCompletionRecordingTest extends TestCase
{
    use RefreshDatabase;

    private const GAMES = [
        'animals'       => 'animals',
        'animals-match' => 'animals-match',
        'colors'        => 'colors',
        'colors-sort'   => 'colors-sort',
        'whats-missing' => 'whats-missing',
        'letters'       => 'letters',
        'numbers'       => 'numbers',
        'shapes'        => 'shapes',
        'shapes-bingo'  => 'shapes-bingo',
    ];

    private User $teacher;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        $this->student = Student::create([
            'first_name' => 'Kid', 'last_name' => 'Levels',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    private function serverTotal(string $game, int $level): int
    {
        $classes = [
            'animals'       => \App\Http\Controllers\Student\Games\AnimalsGameController::class,
            'animals-match' => \App\Http\Controllers\Student\Games\AnimalsMatchGameController::class,
            'colors'        => \App\Http\Controllers\Student\Games\ColorsGameController::class,
            'colors-sort'   => \App\Http\Controllers\Student\Games\ColorsSortGameController::class,
            'whats-missing' => \App\Http\Controllers\Student\Games\WhatsMissingGameController::class,
            'letters'       => \App\Http\Controllers\Student\Games\LettersGameController::class,
            'numbers'       => \App\Http\Controllers\Student\Games\NumbersGameController::class,
            'shapes'        => \App\Http\Controllers\Student\Games\ShapesGameController::class,
            'shapes-bingo'  => \App\Http\Controllers\Student\Games\ShapesBingoGameController::class,
        ];

        return app($classes[$game])->totalForLevel($level);
    }

    private function submit(string $game, int $level, array $payload = [])
    {
        return $this->postJson("/student/games/{$game}/submit/{$level}", array_merge([
            'score' => 0,
            'mistakes' => 0,
            'time_taken' => 30,
        ], $payload));
    }

    public static function games(): array
    {
        return array_map(fn ($game) => [$game], array_keys(self::GAMES));
    }

    /** @dataProvider games */
    public function test_a_finished_level_is_recorded(string $game): void
    {
        $total = $this->serverTotal($game, 1);

        $this->submit($game, 1, ['score' => $total])
            ->assertOk()
            ->assertJson(['success' => true, 'completed' => true, 'recorded' => true, 'total' => $total]);

        $this->assertDatabaseHas('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => $game,
            'level' => 1,
            'score' => $total,
        ]);

        $this->assertSame(1, Point::where('student_id', $this->student->id)->count());
    }

    /** @dataProvider games */
    public function test_an_unfinished_level_is_not_recorded(string $game): void
    {
        $total = $this->serverTotal($game, 1);

        $response = $this->submit($game, 1, ['score' => $total - 1, 'mistakes' => 2])
            ->assertOk()
            ->assertJson(['success' => true, 'completed' => false, 'recorded' => false]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => $game,
        ]);

        $this->assertSame(0, Point::where('student_id', $this->student->id)->count());
    }

    /** @dataProvider games */
    public function test_a_zero_score_is_not_recorded(string $game): void
    {
        $this->submit($game, 1, ['score' => 0, 'mistakes' => 5])->assertOk();

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => $game,
        ]);
    }

    /** @dataProvider games */
    public function test_posting_the_same_level_twice_records_it_once(string $game): void
    {
        $total = $this->serverTotal($game, 1);

        $this->submit($game, 1, ['score' => $total])->assertJson(['recorded' => true]);
        $this->submit($game, 1, ['score' => $total])->assertJson(['recorded' => false]);

        $this->assertSame(1, GameScore::where([
            'student_id' => $this->student->id,
            'game_type' => $game,
            'level' => 1,
        ])->count());

        $this->assertSame(1, Point::where([
            'student_id' => $this->student->id,
            'game_type' => $game,
        ])->count());
    }

    /** @dataProvider games */
    public function test_points_are_paid_once_across_repeated_posts(string $game): void
    {
        $total = $this->serverTotal($game, 1);

        $first = $this->submit($game, 1, ['score' => $total])->assertJson(['recorded' => true]);
        $second = $this->submit($game, 1, ['score' => $total])->assertJson(['recorded' => false]);

        $expected = $total * 10 + 25;
        $this->assertSame($expected, $first->json('points_earned'));
        $this->assertSame(0, $second->json('points_earned'));

        $this->assertSame(
            $expected,
            (int) Point::where('student_id', $this->student->id)->latest('id')->value('total_points')
        );
    }

    /** @dataProvider games */
    public function test_a_score_above_the_level_total_is_rejected(string $game): void
    {
        $total = $this->serverTotal($game, 1);

        $this->submit($game, 1, ['score' => $total + 5])->assertStatus(422);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => $game,
        ]);
    }

    /** @dataProvider games */
    public function test_the_browser_cannot_inflate_the_total_to_fake_completion(string $game): void
    {
        // Pretending the level was only one question long used to be enough to
        // store a "complete" row and unlock the next level.
        $this->submit($game, 1, ['score' => 1, 'total' => 1])
            ->assertOk()
            ->assertJson(['completed' => false, 'recorded' => false]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => $game,
        ]);
    }

    /** @dataProvider games */
    public function test_an_unknown_level_is_rejected(string $game): void
    {
        $this->submit($game, 99, ['score' => 3])->assertStatus(404);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => $game,
        ]);
    }

    public function test_a_mistaken_attempt_after_a_finished_level_keeps_the_best_result(): void
    {
        $total = $this->serverTotal('colors', 1);

        $this->submit('colors', 1, ['score' => $total, 'mistakes' => 0])->assertJson(['recorded' => true]);

        // Fewer mistakes means a higher percentage, even though the raw score is
        // the same, so the stored row should improve.
        $this->submit('colors', 1, ['score' => $total, 'mistakes' => 0])->assertJson(['improved' => false]);

        $row = GameScore::where([
            'student_id' => $this->student->id,
            'game_type' => 'colors',
            'level' => 1,
        ])->first();

        $this->assertSame(100, (int) $row->percentage);
        $this->assertSame(1, Point::where('student_id', $this->student->id)->count());
    }

    public function test_an_unfinished_level_does_not_unlock_the_next_one(): void
    {
        $total = $this->serverTotal('numbers', 1);

        $this->submit('numbers', 1, ['score' => $total - 1])
            ->assertOk()
            ->assertJson(['next_level_unlocked' => false]);

        $html = $this->get('/student/games/numbers/')->assertOk()->getContent();

        $this->assertStringContainsString('Level 1', $html);
    }

    public function test_a_finished_level_unlocks_the_next_one(): void
    {
        $total = $this->serverTotal('numbers', 1);

        $this->submit('numbers', 1, ['score' => $total])
            ->assertOk()
            ->assertJson(['next_level_unlocked' => true]);
    }

    public function test_the_unique_index_stops_a_duplicate_row_at_the_database_level(): void
    {
        $this->submit('letters', 1, ['score' => $this->serverTotal('letters', 1)])->assertOk();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        // The recorder's own check passes, so this proves the database is the
        // backstop rather than the application check alone.
        GameScore::create([
            'student_id' => $this->student->id,
            'game_type' => 'letters',
            'level' => 1,
            'score' => 9, 'total' => 9,
            'mistakes' => 0, 'percentage' => 100,
        ]);
    }

    public function test_the_legacy_save_score_endpoint_cannot_bypass_the_rule(): void
    {
        $total = $this->serverTotal('letters', 1);

        $this->postJson('/student/save-score', [
            'type' => 'letters',
            'level' => 1,
            'score' => 1,
            'total' => 1,
        ])->assertOk()->assertJson(['ok' => true, 'recorded' => false, 'completed' => false]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'letters',
        ]);

        $this->postJson('/student/save-score', [
            'type' => 'letters',
            'level' => 1,
            'score' => $total,
        ])->assertOk()->assertJson(['ok' => true, 'recorded' => true]);

        $this->assertDatabaseHas('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'letters',
            'level' => 1,
        ]);
    }

    public function test_the_legacy_endpoint_requires_a_level(): void
    {
        $this->postJson('/student/save-score', [
            'type' => 'letters',
            'score' => 3,
            'total' => 3,
        ])->assertStatus(422);
    }

    public function test_points_are_not_awarded_for_a_replay_of_a_finished_level(): void
    {
        $total = $this->serverTotal('shapes', 1);

        $this->submit('shapes', 1, ['score' => $total])->assertJson(['points_earned' => $total * 10 + 25]);

        $before = (int) Point::where('student_id', $this->student->id)->latest('id')->value('total_points');

        $this->submit('shapes', 1, ['score' => $total])->assertJson(['points_earned' => 0]);

        $after = (int) Point::where('student_id', $this->student->id)->latest('id')->value('total_points');

        $this->assertSame($before, $after);
    }

    public function test_a_level_with_mistakes_still_counts_as_finished(): void
    {
        $total = $this->serverTotal('animals', 1);

        // Answering everything is what finishes a stage, so mistakes are allowed
        // and only cost the no-mistakes bonus.
        $this->submit('animals', 1, ['score' => $total, 'mistakes' => 3])
            ->assertOk()
            ->assertJson(['completed' => true, 'recorded' => true]);

        $this->assertSame(1, GameScore::where([
            'student_id' => $this->student->id,
            'game_type' => 'animals',
        ])->count());

        $row = GameScore::where(['student_id' => $this->student->id, 'game_type' => 'animals'])->first();
        $this->assertSame(3, (int) $row->mistakes);
        $this->assertSame((int) round(($total / ($total + 3)) * 100), (int) $row->percentage);
    }

    public function test_recording_a_level_awards_points_once_even_when_every_level_is_played(): void
    {
        $expected = 0;
        $rows = 0;

        for ($level = 1; $level <= 8; $level++) {
            $total = $this->serverTotal('letters', $level);
            $this->submit('letters', $level, ['score' => $total])->assertOk();
            $expected += $total * 10 + 25;
            $rows++;
        }

        $this->assertSame(8, GameScore::where([
            'student_id' => $this->student->id,
            'game_type' => 'letters',
        ])->count());

        $this->assertSame(
            $expected,
            (int) Point::where('student_id', $this->student->id)->latest('id')->value('total_points')
        );

        $this->assertSame($rows, 8);
    }
}