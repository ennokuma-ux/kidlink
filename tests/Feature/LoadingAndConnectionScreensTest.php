<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Every page carries the loading screen and the connection-lost screen; only
 * child mode adds the transition screens, and it uses the child wording.
 */
class LoadingAndConnectionScreensTest extends TestCase
{
    use RefreshDatabase;

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

    private function enterChildMode(): void
    {
        $student = Student::create([
            'first_name' => 'Mia', 'last_name' => 'Santos', 'sex' => 'Female',
            'date_of_birth' => '2020-01-01', 'class_name' => '1-3 years old',
            'dominant_hand' => 'Right', 'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$student->id);
    }

    public function test_child_mode_pages_have_loading_connection_and_transition_screens(): void
    {
        $this->enterChildMode();

        foreach (['/student/home', '/student/games/colors', '/student/games/colors/play/1'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('id="kl-transition"', false)
                ->assertSee('id="kl-loader"', false)
                ->assertSee('id="kl-offline"', false)
                ->assertSee('class="is-child"', false)
                ->assertSee(__('connection.child_title'));
        }
    }

    public function test_transition_config_knows_every_game_and_the_childs_name(): void
    {
        $this->enterChildMode();

        $this->get('/student/home')->assertOk()
            ->assertSee('"name":"Mia"', false)
            ->assertSee('"colors-sort":', false)
            ->assertSee('"animals-match":', false);
    }

    public function test_staff_pages_get_loading_and_connection_screens_without_transitions(): void
    {
        $this->actingAs($this->teacher)->get('/teacher/dashboard')->assertOk()
            ->assertSee('id="kl-loader"', false)
            ->assertSee('id="kl-offline"', false)
            ->assertDontSee('id="kl-transition"', false)
            ->assertDontSee('class="is-child"', false)
            ->assertSee(__('connection.title'));
    }

    public function test_guest_pages_get_loading_and_connection_screens(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('id="kl-loader"', false)
            ->assertSee('id="kl-offline"', false)
            ->assertDontSee('id="kl-transition"', false);
    }

    public function test_entering_child_mode_leaves_a_greeting_note_for_the_first_page(): void
    {
        $this->actingAs($this->teacher)->get('/teacher/dashboard')->assertOk()
            ->assertSee('klTransition', false);
    }

    public function test_every_language_has_the_same_screen_texts(): void
    {
        foreach (['connection', 'transition'] as $file) {
            $english = array_keys(require lang_path("en/{$file}.php"));

            foreach (['fil', 'hil'] as $locale) {
                $keys = array_keys(require lang_path("{$locale}/{$file}.php"));
                $this->assertSame([], array_diff($english, $keys), "{$locale}/{$file}.php is missing keys");
                $this->assertSame([], array_diff($keys, $english), "{$locale}/{$file}.php has extra keys");
            }
        }
    }
}
