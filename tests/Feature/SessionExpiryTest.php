<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * A stale CSRF token is the visible symptom of an expired session. These cover the
 * three things that make it survivable: it does not happen in the first place, it
 * explains itself when it does, and the work in flight is recoverable.
 */
class SessionExpiryTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->teacher = User::create([
            'first_name' => 'Teach', 'last_name' => 'Er',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        Student::create([
            'first_name' => 'Ana', 'last_name' => 'Reyes', 'sex' => 'Female',
            'date_of_birth' => now()->subYears(4), 'class_name' => '1-3 years old',
            'teacher_id' => $this->teacher->id, 'is_active' => true,
        ]);
    }

    /**
     * Laravel's VerifyCsrfToken deliberately no-ops while the test suite runs, so a
     * stale token cannot be produced by posting. Drive the registered renderer
     * directly instead, which is the part that decides what the user sees.
     */
    private function renderTokenMismatch(bool $json = false): \Symfony\Component\HttpFoundation\Response
    {
        $server = $json ? ['HTTP_ACCEPT' => 'application/json'] : [];

        $request = Request::create(
            '/teacher/attendance?page=2',
            'POST',
            [],
            [],
            [],
            $server,
        );
        $request->setLaravelSession($this->app['session.store']);

        return app(ExceptionHandler::class)->render($request, new TokenMismatchException);
    }

    public function test_the_session_lifetime_is_long_enough_to_finish_a_task()
    {
        // Five minutes produced random "419 Page Expired" on long-open forms, which
        // is the whole reason this work exists.
        $this->assertGreaterThanOrEqual(
            30,
            (int) config('session.lifetime'),
            'A session must outlive a realistic task, not a coffee break.'
        );
    }

    public function test_pages_offer_a_keep_alive_ping()
    {
        $this->actingAs($this->teacher)->get(route('teacher.attendance.index'))
            ->assertOk()
            ->assertSee('id="session-keep-alive"', false)
            ->assertSee(route('session.keep-alive'), false);
    }

    public function test_the_student_layout_also_carries_the_keep_alive_ping()
    {
        // A child mid-game is the worst case: the session lapses and the score POST
        // is the first thing to notice. Student pages use layouts.app, which the
        // login page exercises without needing a student account.
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="session-keep-alive"', false);
    }

    public function test_the_keep_alive_endpoint_returns_the_current_token()
    {
        $this->actingAs($this->teacher)
            ->getJson(route('session.keep-alive'))
            ->assertOk()
            ->assertJsonStructure(['token', 'expires_in'])
            ->assertJson(fn ($json) => $json->whereType('token', 'string')->etc());
    }

    public function test_the_keep_alive_endpoint_is_reachable_without_being_signed_in()
    {
        // A guest session is worth refreshing too, and the ping must never itself
        // be the thing that fails.
        $this->getJson(route('session.keep-alive'))->assertOk();
    }

    public function test_an_expired_session_shows_a_recoverable_page_instead_of_a_bare_419()
    {
        $response = $this->renderTokenMismatch();
        $body = $response->getContent();

        $this->assertSame(419, $response->getStatusCode());
        $this->assertStringContainsString('Session timed out', $body);
        $this->assertStringContainsString('Sign in again', $body);
        $this->assertStringNotContainsString('Page Expired', $body);
    }

    public function test_the_expired_page_remembers_where_the_user_was()
    {
        $this->renderTokenMismatch();

        $intended = session('url.intended');

        $this->assertNotNull($intended, 'After signing in again the user must land back on the page they lost.');
        $this->assertStringEndsWith(
            '/teacher/attendance?page=2',
            $intended,
            'The intended URL must include the query string, not just the path.'
        );
    }

    public function test_an_ajax_call_gets_json_rather_than_an_html_page()
    {
        $response = $this->renderTokenMismatch(json: true);
        $payload = json_decode($response->getContent(), true);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertArrayHasKey('message', $payload);
        $this->assertArrayHasKey('login_url', $payload);
    }

    public function test_a_live_session_with_a_wrong_token_still_reports_a_csrf_problem()
    {
        // Signed in and authenticated: this is a real token problem, not a timeout,
        // so it must keep the plain 419 and stay obvious during development.
        $this->actingAs($this->teacher);

        $response = $this->renderTokenMismatch();

        $this->assertStringNotContainsString(
            'Session timed out',
            $response->getContent(),
            'A live session must not be misreported as a timeout.'
        );
    }
}
