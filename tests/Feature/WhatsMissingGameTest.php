<?php

namespace Tests\Feature;

use App\Games\GameCatalog;
use App\Http\Controllers\Student\Games\WhatsMissingGameController;
use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * What's Missing? ships the child a sequence with one hole and a set of number
 * choices. The sequences and the choices are the ones the server generated, so
 * the browser cannot claim a round it was never given.
 */
class WhatsMissingGameTest extends TestCase
{
    use RefreshDatabase;

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
            'first_name' => 'Kid', 'last_name' => 'Counting',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    /** The rounds (sequences + choices) the play page ships to the browser. */
    private function roundsForLevel(int $level): array
    {
        $this->unlockThrough($level);

        $html = $this->get("/student/games/whats-missing/play/{$level}")->assertOk()->getContent();

        preg_match('/const rounds = (\[.*?\]);/s', $html, $rounds);

        $this->assertNotEmpty($rounds[1] ?? null, 'The play page did not ship any rounds.');

        return json_decode($rounds[1], true);
    }

    /**
     * Levels are gated behind progress, so clear everything before $level the
     * same way the other game tests do.
     */
    private function unlockThrough(int $level): void
    {
        for ($done = 1; $done < $level; $done++) {
            GameScore::firstOrCreate(
                [
                    'student_id' => $this->student->id,
                    'game_type' => 'whats-missing',
                    'level' => $done,
                ],
                ['score' => 1, 'total' => 1, 'percentage' => 100]
            );
        }
    }

    public function test_every_round_has_exactly_one_hole_and_offers_the_answer()
    {
        foreach ([1, 4, 8] as $level) {
            $rounds = $this->roundsForLevel($level);
            $expected = app(WhatsMissingGameController::class)->totalForLevel($level);

            $this->assertCount($expected, $rounds, "Level {$level} has the wrong round count.");

            foreach ($rounds as $index => $round) {
                $holes = count(array_filter($round['sequence'], fn ($v) => $v === null));

                $this->assertSame(1, $holes, "Level {$level} round {$index} should have exactly one hole.");
                $this->assertContains($round['answer'], $round['choices'], "Level {$level} round {$index} does not offer the answer.");
                $this->assertNotContains($round['answer'], array_filter($round['sequence']), "Level {$level} round {$index} shows the answer already.");
            }
        }
    }

    public function test_a_correct_choice_is_recorded_and_unlocks_the_next_level()
    {
        $total = app(WhatsMissingGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/whats-missing/submit/1', [
            'score' => $total,
            'mistakes' => 1,
            'time_taken' => 20,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => true,
            'recorded' => true,
            'total' => $total,
            'next_level_unlocked' => true,
        ]);

        $this->assertDatabaseHas('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'whats-missing',
            'level' => 1,
            'score' => $total,
        ]);

        $this->assertSame(1, Point::where('student_id', $this->student->id)->count());

        $this->get('/student/games/whats-missing/play/2')->assertOk();
    }

    public function test_an_incomplete_round_is_not_recorded()
    {
        $total = app(WhatsMissingGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/whats-missing/submit/1', [
            'score' => $total - 1,
            'mistakes' => 3,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => false,
            'recorded' => false,
        ]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'whats-missing',
        ]);

        $this->assertSame(0, Point::where('student_id', $this->student->id)->count());
    }

    public function test_the_score_is_capped_by_the_server_round_count()
    {
        $total = app(WhatsMissingGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/whats-missing/submit/1', ['score' => $total + 1])
            ->assertUnprocessable();
    }

    public function test_an_unknown_level_is_rejected()
    {
        $this->postJson('/student/games/whats-missing/submit/99', ['score' => 1])->assertNotFound();

        $this->get('/student/games/whats-missing/play/99')->assertNotFound();
    }

    public function test_a_locked_level_redirects_back_to_the_map()
    {
        $this->get('/student/games/whats-missing/play/3')->assertRedirect('/student/games/whats-missing');
    }

    public function test_the_game_sits_next_to_its_topic_neighbour_in_the_catalog_and_switcher()
    {
        $whats = GameCatalog::game('whats-missing');

        $this->assertNotNull($whats);
        $this->assertSame('numbers', $whats['topic']);

        $map = $this->get('/student/games/whats-missing')->assertOk();
        $map->assertSee('Choose a game:', 'The what\'s-missing stage-select lacks the switcher label.');
        $this->assertStringContainsString(
            'href="'.route('student.games.numbers.stage-select').'"',
            $map->getContent(),
            'The switcher does not link the sibling Numbers game.'
        );

        $play = $this->get('/student/games/whats-missing/play/1')->assertOk();
        $play->assertDontSee('Choose a game:', 'The what\'s-missing play page should hide the game switcher.');
        $this->assertStringNotContainsString(
            'href="'.route('student.games.numbers.stage-select').'"',
            $play->getContent(),
            'The what\'s-missing play page should not link the sibling Numbers game.'
        );
    }
}
