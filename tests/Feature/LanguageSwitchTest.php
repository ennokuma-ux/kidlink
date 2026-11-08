<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The header language button: it must appear, it must actually change the
 * rendered language, and a bogus value in the cookie must never be able to
 * break or redirect a page.
 */
class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        $this->admin = User::create([
            'first_name' => 'Test', 'last_name' => 'Admin',
            'email' => 'admin@test.local', 'password' => Hash::make('password123'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function makeStudent(string $first): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => 'Test',
            'sex' => 'Female', 'date_of_birth' => Student::dateOfBirthForSection('1-3 years old'),
            'class_name' => '1-3 years old',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now()->subMonths(3), 'is_active' => true,
        ]);
    }

    // ------------------------------------------------------------- the button

    public function test_the_switcher_offers_every_configured_language(): void
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/analytics')->assertOk();

        $response->assertSee('name="locale"', false);
        $response->assertSee(route('language.switch'), false);

        foreach (config('app.supported_locales') as $code => $label) {
            $response->assertSee('value="'.$code.'"', false);
            // Named in its own language, so it stays legible however the
            // surrounding text is translated.
            $response->assertSee('title="'.$label.'"', false);
        }
    }

    public function test_the_switcher_appears_on_the_admin_page_too(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/analytics')
            ->assertOk()
            ->assertSee('name="locale"', false);
    }

    public function test_the_active_language_is_marked_current(): void
    {
        $english = $this->actingAs($this->teacher)
            ->withSession(['locale' => 'en'])
            ->get('/teacher/analytics')
            ->assertOk();

        // Only the chosen one carries aria-current="true"; the others say false,
        // so the state does not depend on colour alone.
        $this->assertSame(
            1,
            substr_count($english->getContent(), 'aria-current="true"'),
            'Exactly one language should be marked as current.'
        );
    }

    // --------------------------------------------------------------- switching

    public function test_posting_a_language_stores_it(): void
    {
        $response = $this->actingAs($this->teacher)
            ->post('/language', ['locale' => 'fil'])
            ->assertRedirect();

        $response->assertSessionHas('locale', 'fil');
        // Long-lived, so the choice outlives the session.
        $response->assertCookie('locale');
    }

    public function test_an_unsupported_language_is_ignored(): void
    {
        $response = $this->actingAs($this->teacher)
            ->from('/teacher/analytics')
            ->post('/language', ['locale' => 'xx'])
            ->assertRedirect('/teacher/analytics');

        $this->assertNull(
            session('locale'),
            'An unconfigured language reached the session.'
        );
    }

    public function test_the_language_survives_a_page_reload(): void
    {
        $this->actingAs($this->teacher)->post('/language', ['locale' => 'hil']);

        // Same session, new request: the choice has to come back with it.
        $response = $this->actingAs($this->teacher)->get('/teacher/analytics')->assertOk();

        $response->assertSee('Maayong pag-abut, Test Teacher');
        $response->assertSee('Listahan sang ECCD');
    }

    // -------------------------------------------------------------- rendering

    public function test_the_interface_renders_in_filipino(): void
    {
        $response = $this->actingAs($this->teacher)
            ->withSession(['locale' => 'fil'])
            ->get('/teacher/analytics')
            ->assertOk();

        $response->assertSee('Maligayang pagdating, Test Teacher');
        $response->assertSee('Portal ng Guro');
        $response->assertSee('Pagdalo', false);
        $response->assertSee('Listahan ng ECCD');
        $response->assertSee('Plano ng Gawain');
    }

    public function test_the_interface_renders_in_hiligaynon(): void
    {
        $response = $this->actingAs($this->teacher)
            ->withSession(['locale' => 'hil'])
            ->get('/teacher/analytics')
            ->assertOk();

        $response->assertSee('Maayong pag-abut, Test Teacher');
        $response->assertSee('Portal sang Guru');
        $response->assertSee('Listahan sang ECCD');
        $response->assertSee('Plano sang Gawain');
    }

    public function test_english_is_unchanged_by_the_switcher(): void
    {
        $response = $this->actingAs($this->teacher)
            ->withSession(['locale' => 'en'])
            ->get('/teacher/analytics')
            ->assertOk();

        $response->assertSee('Welcome, Test Teacher');
        $response->assertSee('Teacher Portal');
        $response->assertSee('Activity Planner');
    }

    public function test_the_html_lang_attribute_follows_the_selection(): void
    {
        $response = $this->actingAs($this->teacher)
            ->withSession(['locale' => 'fil'])
            ->get('/teacher/analytics')
            ->assertOk();

        $response->assertSee('<html lang="fil">', false);
    }

    public function test_guests_can_see_the_switcher_before_logging_in(): void
    {
        // A guest has no header, but the choice has to be reachable before
        // login or the endpoint is a dead end.
        foreach (['/', '/login'] as $uri) {
            $response = $this->get($uri)->assertOk();

            $response->assertSee('name="locale"', false);
            $response->assertSee(route('language.switch'), false);
        }
    }

    /** The login form is static UI text, so it has to switch too. */
    public function test_the_login_page_translates_its_form()
    {
        $response = $this->withSession(['locale' => 'fil'])
            ->get('/login')
            ->assertOk();

        $response->assertSee('Maligayang Pagbabalik');
        $response->assertSee('Mag-sign In');
        $response->assertSee('Wala pang account?');
        $response->assertSee('placeholder="Ilagay ang iyong email"', false);
        $response->assertSee('placeholder="Ilagay ang iyong password"', false);
        $response->assertDontSee('Welcome Back');
        $response->assertDontSee('>Sign In<');
    }

    /** Dashboard card labels are not navigation, but they are static UI. */
    public function test_the_teacher_dashboard_labels_translate()
    {
        $response = $this->actingAs($this->teacher)
            ->withSession(['locale' => 'fil'])
            ->get('/teacher/dashboard')
            ->assertOk();

        $response->assertSee('Kabuuang Estudyante');
        $response->assertSee('Nakapasok Ngayon');
        $response->assertSee('Absent Ngayon');
        $response->assertSee('Mga Gawain Ngayon');
        $response->assertDontSee('Total Students');
    }

    // --------------------------------------------------------------- hardening

    public function test_a_forged_locale_cookie_falls_back_to_english(): void
    {
        // EncryptCookies normally blocks this, but if anything ever hands
        // SetLocale a string outside the allow-list it must not fatal.
        $this->withUnencryptedCookie('locale', '../../etc/passwd')
            ->get('/login')
            ->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function test_guests_can_choose_a_language(): void
    {
        $this->from('/login')
            ->post('/language', ['locale' => 'fil'])
            ->assertRedirect('/login');

        $this->assertSame('fil', session('locale'));
    }

    /**
     * Every locale must translate exactly the same keys. A gap means one
     * language silently shows English in a nav item, which is worse than an
     * obvious crash because nobody notices it.
     */
    public function test_every_language_translates_the_same_keys(): void
    {
        $groups = array_map(
            fn ($file) => basename($file, '.php'),
            glob(base_path('lang/en/*.php'))
        );

        $this->assertNotEmpty($groups);

        $english = [];
        foreach ($groups as $group) {
            $english[$group] = require base_path("lang/en/$group.php");
        }

        foreach (array_diff(array_keys(config('app.supported_locales')), ['en']) as $locale) {
            foreach ($groups as $group) {
                $path = base_path("lang/$locale/$group.php");
                $this->assertFileExists($path, "$locale is missing the $group group.");

                $translated = require $path;

                $missing = array_diff(array_keys($english[$group]), array_keys($translated));
                $extra = array_diff(array_keys($translated), array_keys($english[$group]));

                $this->assertSame([], array_values($missing), "$locale/$group is missing keys.");
                $this->assertSame([], array_values($extra), "$locale/$group has unknown keys.");

                foreach ($translated as $key => $value) {
                    $this->assertNotEmpty($value, "$locale/$group.$key is blank.");
                }

                // Intentionally not asserting values differ from English:
                // "Dashboard" and "Super Admin" are genuine loanwords in both
                // target languages, so identical text there is correct.
            }
        }
    }
}
