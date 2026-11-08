<?php

namespace Tests\Feature;

use App\Games\GameCatalog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Every game's stage map carries a switcher for the other games in the same
 * topic, so a topic can hold several games with different gameplay without
 * burying them. Once the child is inside a stage the switcher is hidden, so the
 * only way back to another game is the map.
 */
class GameSwitcherTest extends TestCase
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
            'first_name' => 'Kid', 'last_name' => 'Games',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    public function test_every_catalog_game_has_routes_and_its_stage_map_ships_the_switcher()
    {
        foreach (GameCatalog::all() as $game) {
            $gameName = "{$game['slug']} game";

            $this->assertTrue(
                \Route::has('student.games.'.$game['slug'].'.stage-select'),
                "$gameName is in the catalogue but has no stage-select route."
            );
            $this->assertTrue(
                \Route::has('student.games.'.$game['slug'].'.play'),
                "$gameName is in the catalogue but has no play route."
            );

            $stageUrl = route('student.games.'.$game['slug'].'.stage-select');
            $stage = $this->get($stageUrl)->assertOk();
            $stage->assertSee('Choose a game:', $gameName.' stage-select does not render the switcher label.');
            $stage->assertSee($stageUrl, $gameName.' stage-select is missing its own link.');

            $playUrl = route('student.games.'.$game['slug'].'.play', ['level' => 1]);
            $this->get($playUrl)->assertOk()
                ->assertDontSee('Choose a game:', $gameName.' play page should hide the game switcher.');
        }
    }

    public function test_a_topic_page_only_links_its_own_games()
    {
        $html = $this->get(route('student.games.shapes.stage-select'))->assertOk()->getContent();

        $this->assertStringContainsString(route('student.games.shapes.stage-select'), $html);

        foreach (['letters', 'numbers', 'colors', 'animals', 'animals-match'] as $other) {
            $this->assertStringNotContainsString(
                route('student.games.'.$other.'.stage-select'),
                $html,
                "Shapes page must not link to the {$other} game; it belongs to another topic."
            );
        }
    }
}