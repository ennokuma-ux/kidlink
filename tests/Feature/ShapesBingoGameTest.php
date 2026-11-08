<?php

namespace Tests\Feature;

use App\Games\GameCatalog;
use App\Http\Controllers\Student\Games\ShapesBingoGameController;
use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Shape Bingo deals the child a card of shapes and a caller announces one shape
 * at a time; dabbing the matching cell covers it and a full card finishes the
 * level. The card and the call order are the ones the server shipped, so the
 * browser cannot claim a fuller card than it was dealt.
 */
class ShapesBingoGameTest extends TestCase
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
            'first_name' => 'Kid', 'last_name' => 'Bingo',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    /** The card (cells + calls) the play page ships to the browser. */
    private function boardForLevel(int $level): array
    {
        $this->unlockThrough($level);

        $html = $this->get("/student/games/shapes-bingo/play/{$level}")->assertOk()->getContent();

        preg_match('/const cells = (\[.*?\]);/s', $html, $cells);
        preg_match('/const calls = (\[.*?\]);/s', $html, $calls);

        $this->assertNotEmpty($cells[1] ?? null, 'The play page did not ship any board cells.');
        $this->assertNotEmpty($calls[1] ?? null, 'The play page did not ship any calls.');

        return [
            'cells' => json_decode($cells[1], true),
            'calls' => json_decode($calls[1], true),
        ];
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
                    'game_type' => 'shapes-bingo',
                    'level' => $done,
                ],
                ['score' => 1, 'total' => 1, 'percentage' => 100]
            );
        }
    }

    public function test_the_card_never_asks_for_a_shape_that_is_never_called()
    {
        foreach ([1, 3, 5, 6, 8] as $level) {
            $board = $this->boardForLevel($level);
            $expected = app(ShapesBingoGameController::class)->totalForLevel($level);

            $this->assertCount($expected, $board['cells'], "Level {$level} has the wrong cell count.");
            $this->assertCount($expected, $board['calls'], "Level {$level} has the wrong call count.");

            $cellTypes = array_map(fn ($c) => $c['type'], $board['cells']);
            $callTypes = array_map(fn ($c) => $c['type'], $board['calls']);

            sort($cellTypes);
            sort($callTypes);

            $this->assertSame($cellTypes, $callTypes, "Level {$level} calls do not cover every cell.");
        }
    }

    public function test_every_cell_and_call_carries_a_shape_image_and_name()
    {
        $board = $this->boardForLevel(1);

        foreach (array_merge($board['cells'], $board['calls']) as $shape) {
            $this->assertNotEmpty($shape['name']);
            $this->assertStringContainsString('shape imgs', $shape['image']);
        }
    }

    public function test_covering_the_whole_card_is_recorded_and_unlocks_the_next_level()
    {
        $total = app(ShapesBingoGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/shapes-bingo/submit/1', [
            'score' => $total,
            'mistakes' => 2,
            'time_taken' => 45,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => true,
            'recorded' => true,
            'total' => $total,
            'next_level_unlocked' => true,
        ]);

        $this->assertDatabaseHas('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'shapes-bingo',
            'level' => 1,
            'score' => $total,
        ]);

        $this->assertSame(1, Point::where('student_id', $this->student->id)->count());

        $this->get('/student/games/shapes-bingo/play/2')->assertOk();
    }

    public function test_a_partly_covered_card_is_not_recorded()
    {
        $total = app(ShapesBingoGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/shapes-bingo/submit/1', [
            'score' => $total - 1,
            'mistakes' => 4,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => false,
            'recorded' => false,
        ]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'shapes-bingo',
        ]);

        $this->assertSame(0, Point::where('student_id', $this->student->id)->count());
    }

    public function test_the_score_is_capped_by_the_server_cell_count()
    {
        $total = app(ShapesBingoGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/shapes-bingo/submit/1', ['score' => $total + 1])
            ->assertUnprocessable();
    }

    public function test_an_unknown_level_is_rejected()
    {
        $this->postJson('/student/games/shapes-bingo/submit/99', ['score' => 1])->assertNotFound();

        $this->get('/student/games/shapes-bingo/play/99')->assertNotFound();
    }

    public function test_a_locked_level_redirects_back_to_the_map()
    {
        $this->get('/student/games/shapes-bingo/play/3')->assertRedirect('/student/games/shapes-bingo');
    }

    public function test_the_game_sits_next_to_its_topic_neighbour_in_the_catalog_and_switcher()
    {
        $bingo = GameCatalog::game('shapes-bingo');

        $this->assertNotNull($bingo);
        $this->assertSame('shapes', $bingo['topic']);

        $map = $this->get('/student/games/shapes-bingo')->assertOk();
        $map->assertSee('Choose a game:', 'The bingo stage-select lacks the switcher label.');
        $this->assertStringContainsString(
            'href="'.route('student.games.shapes.stage-select').'"',
            $map->getContent(),
            'The switcher does not link the sibling Shapes game.'
        );

        $play = $this->get('/student/games/shapes-bingo/play/1')->assertOk();
        $play->assertDontSee('Choose a game:', 'The bingo play page should hide the game switcher.');
        $this->assertStringNotContainsString(
            'href="'.route('student.games.shapes.stage-select').'"',
            $play->getContent(),
            'The bingo play page should not link the sibling Shapes game.'
        );
    }
}
