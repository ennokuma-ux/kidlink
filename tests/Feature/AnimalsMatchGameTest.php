<?php

namespace Tests\Feature;

use App\Games\GameCatalog;
use App\Http\Controllers\Student\Games\AnimalsMatchGameController;
use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Animal Match is a drag-and-drop matcher: the tray holds every animal of the
 * stage and the child drags each one onto its identical twin. A level only
 * counts as finished when every animal has found its twin, and the board the
 * browser plays is the one the server shipped.
 */
class AnimalsMatchGameTest extends TestCase
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
            'first_name' => 'Kid', 'last_name' => 'Match',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    /** The board (targets + tray) the play page ships to the browser. */
    private function boardForLevel(int $level): array
    {
        $this->unlockThrough($level);

        $html = $this->get("/student/games/animals-match/play/{$level}")->assertOk()->getContent();

        preg_match('/const targets = (\[.*?\]);/s', $html, $targets);
        preg_match('/const tray = (\[.*?\]);/s', $html, $tray);

        $this->assertNotEmpty($targets[1] ?? null, 'The play page did not ship any targets.');
        $this->assertNotEmpty($tray[1] ?? null, 'The play page did not ship any tray animals.');

        return [
            'targets' => json_decode($targets[1], true),
            'tray' => json_decode($tray[1], true),
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
                    'game_type' => 'animals-match',
                    'level' => $done,
                ],
                ['score' => 1, 'total' => 1, 'percentage' => 100]
            );
        }
    }

    public function test_every_tray_animal_has_exactly_one_twin_on_the_board()
    {
        foreach ([1, 3, 5, 6, 8] as $level) {
            $board = $this->boardForLevel($level);
            $expectedPairs = app(AnimalsMatchGameController::class)->totalForLevel($level);

            $this->assertCount($expectedPairs, $board['targets'], "Level {$level} has the wrong target count.");
            $this->assertCount($expectedPairs, $board['tray'], "Level {$level} has the wrong tray count.");

            $targetPairs = array_map(fn ($t) => $t['pair'], $board['targets']);
            $trayPairs = array_map(fn ($t) => $t['pair'], $board['tray']);

            sort($targetPairs);
            sort($trayPairs);

            $this->assertSame($targetPairs, $trayPairs, "Level {$level} tray animals have no matching twins on the board.");
        }
    }

    public function test_matching_every_animal_is_recorded_and_unlocks_the_next_level()
    {
        $total = app(AnimalsMatchGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/animals-match/submit/1', [
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
            'game_type' => 'animals-match',
            'level' => 1,
            'score' => $total,
        ]);

        $this->assertSame(1, Point::where('student_id', $this->student->id)->count());

        $this->get('/student/games/animals-match/play/2')->assertOk();
    }

    public function test_an_unfinished_board_is_not_recorded()
    {
        $total = app(AnimalsMatchGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/animals-match/submit/1', [
            'score' => $total - 1,
            'mistakes' => 4,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => false,
            'recorded' => false,
        ]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'animals-match',
        ]);

        $this->assertSame(0, Point::where('student_id', $this->student->id)->count());
    }

    public function test_the_score_is_capped_by_the_server_animal_count()
    {
        $total = app(AnimalsMatchGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/animals-match/submit/1', ['score' => $total + 1])
            ->assertUnprocessable();
    }

    public function test_an_unknown_level_is_rejected()
    {
        $this->postJson('/student/games/animals-match/submit/99', ['score' => 1])->assertNotFound();

        $this->get('/student/games/animals-match/play/99')->assertNotFound();
    }

    public function test_a_locked_level_redirects_back_to_the_map()
    {
        $this->get('/student/games/animals-match/play/3')->assertRedirect('/student/games/animals-match');
    }

    public function test_the_game_sits_next_to_its_topic_neighbour_in_the_catalog_and_switcher()
    {
        $match = GameCatalog::game('animals-match');

        $this->assertNotNull($match);
        $this->assertSame('animals', $match['topic']);

        $map = $this->get('/student/games/animals-match')->assertOk();
        $map->assertSee('Choose a game:', 'The match stage-select lacks the switcher label.');
        $this->assertStringContainsString(
            'href="'.route('student.games.animals.stage-select').'"',
            $map->getContent(),
            'The switcher does not link the sibling Animals game.'
        );

        $play = $this->get('/student/games/animals-match/play/1')->assertOk();
        $play->assertDontSee('Choose a game:', 'The match play page should hide the game switcher.');
        $this->assertStringNotContainsString(
            'href="'.route('student.games.animals.stage-select').'"',
            $play->getContent(),
            'The match play page should not link the sibling Animals game.'
        );
    }
}