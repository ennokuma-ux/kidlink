<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Support\Mascot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The KidLink mascots greet learners across the site. Boys get Kido, girls get
 * Linka, and every game page ships an in-game buddy that reacts to answers.
 */
class MascotTest extends TestCase
{
    use RefreshDatabase;

    private const GAMES = ['shapes', 'colors', 'letters', 'animals', 'animals-match', 'shapes-bingo', 'numbers'];

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);
    }

    private function child(string $sex): Student
    {
        return Student::create([
            'first_name' => 'Kid', 'last_name' => 'Mascot',
            'sex' => $sex, 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);
    }

    private function enterChildMode(Student $student): void
    {
        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$student->id);
    }

    public function test_every_mascot_expression_asset_exists(): void
    {
        foreach (Mascot::EXPRESSIONS as $mascot => $expressions) {
            foreach ($expressions as $state => $path) {
                $this->assertFileExists(public_path($path), "Missing {$mascot} '{$state}' artwork at {$path}.");
            }
        }
    }

    public function test_boys_get_kido_across_every_game_page(): void
    {
        $this->enterChildMode($this->child('Male'));

        foreach (self::GAMES as $game) {
            $this->get("/student/games/{$game}")->assertOk()->assertSee('assets/images/mascot/kido/', false);
            $this->get("/student/games/{$game}/play/1")->assertOk()
                ->assertSee('data-mascot-buddy', false)
                ->assertSee('assets/images/mascot/kido/', false);
        }
    }

    public function test_girls_get_linka_across_every_game_page(): void
    {
        $this->enterChildMode($this->child('Female'));

        foreach (self::GAMES as $game) {
            $this->get("/student/games/{$game}")->assertOk()->assertSee('assets/images/mascot/linka/', false);
            $this->get("/student/games/{$game}/play/1")->assertOk()
                ->assertSee('data-mascot-buddy', false)
                ->assertSee('assets/images/mascot/linka/', false)
                ->assertDontSee('assets/images/mascot/kido/', false);
        }
    }

    public function test_buddy_ships_every_expression_and_speech_line(): void
    {
        $this->enterChildMode($this->child('Male'));

        $response = $this->get('/student/games/shapes/play/1')->assertOk();

        // The initial expression is the <img src>; the rest ride along in the
        // buddy's JSON payload (where slashes are escaped), so match filenames.
        foreach (Mascot::buddyStates('kido') as $path) {
            $response->assertSee(basename($path), false);
        }

        // Speech lines render escaped, so match distinctive fragments.
        $response->assertSee('play!', false);
        $response->assertSee('Great job!', false);
        $response->assertSee('try that again!', false);
        $response->assertSee('next stage!', false);
    }

    public function test_landing_page_greets_with_both_mascots(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('assets/images/mascot/kido/', false)
            ->assertSee('assets/images/mascot/linka/', false)
            ->assertSee(__('mascot.landing_hello'), false);
    }

    public function test_login_page_has_no_mascot(): void
    {
        $this->get('/login')->assertOk()
            ->assertDontSee('assets/images/mascot/kido/', false)
            ->assertDontSee(__('mascot.login_welcome'), false)
            ->assertSee('Kid', false);
    }

    public function test_error_page_shows_a_mascot(): void
    {
        $this->get('/this-route-does-not-exist')->assertNotFound()
            ->assertSee('assets/images/mascot/kido/', false);
    }
}
