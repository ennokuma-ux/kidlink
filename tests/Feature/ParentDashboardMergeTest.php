<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EccdRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Parent analytics was merged into the parent dashboard: /parent/dashboard and
 * the legacy /parent/analytics path both serve one combined view. The separate
 * Progress page no longer exists.
 */
class ParentDashboardMergeTest extends TestCase
{
    use RefreshDatabase;

    private function makeParent(string $email = 'parent@test.local'): User
    {
        return User::create([
            'first_name' => 'Pat', 'last_name' => 'Parent',
            'email' => $email, 'password' => Hash::make('password123'),
            'role' => 'parent', 'is_active' => true,
        ]);
    }

    private function makeStudent(User $teacher, string $first = 'Mia'): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => 'Santos', 'sex' => 'Female',
            'date_of_birth' => '2021-01-01', 'class_name' => '4-5 years old',
            'teacher_id' => $teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);
    }

    private function makeTeacher(): User
    {
        return User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);
    }

    public function test_the_dashboard_shows_the_analytics_sections(): void
    {
        $parent = $this->makeParent();
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent($teacher);
        $student->parents()->attach($parent->id);

        $html = $this->actingAs($parent)->get('/parent/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString(__('analytics.score_trend'), $html);
        $this->assertStringContainsString(__('analytics.game_performance'), $html);
        $this->assertStringContainsString(__('analytics.insights'), $html);
    }

    public function test_the_dashboard_still_shows_the_child_profile_and_register_stats(): void
    {
        $parent = $this->makeParent();
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent($teacher);
        $student->parents()->attach($parent->id);

        Attendance::create([
            'student_id' => $student->id, 'date' => now()->toDateString(),
            'status' => 'Present', 'marked_by' => $teacher->id,
        ]);

        $html = $this->actingAs($parent)->get('/parent/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Mia Santos', $html);
        $this->assertStringContainsString(__('analytics.stat_attendance_rate'), $html);
        $this->assertStringContainsString(__('dashboard.absences'), $html);
    }

    public function test_the_legacy_analytics_path_serves_the_merged_dashboard(): void
    {
        $parent = $this->makeParent();
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent($teacher);
        $student->parents()->attach($parent->id);

        $response = $this->actingAs($parent)->get('/parent/analytics')->assertOk();

        // Same view as the dashboard, not a redirect.
        $response->assertViewIs('parent.dashboard');
        $response->assertViewHas('overallAvg');
        $response->assertViewHas('insights');
    }

    public function test_insights_are_translated(): void
    {
        $parent = $this->makeParent();
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent($teacher);
        $student->parents()->attach($parent->id);

        EccdRecord::create([
            'student_id' => $student->id, 'domain' => 'Gross Motor',
            'indicator' => 'Runs safely', 'status' => 'Achieved',
            'assessed_by' => $teacher->id,
        ]);

        $localized = __('analytics.insight_strongest', ['domain' => 'Gross Motor', 'pct' => 100]);

        $html = $this->actingAs($parent)
            ->withSession(['locale' => 'en'])
            ->get('/parent/dashboard')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($localized, $html);
        $this->assertStringNotContainsString('analytics.insight_', $html, 'A raw translation key leaked into the page.');
    }

    public function test_the_dashboard_handles_a_parent_with_no_children(): void
    {
        $parent = $this->makeParent();

        $this->actingAs($parent)->get('/parent/dashboard')
            ->assertOk()
            ->assertSee(__('dashboard.no_children'));
    }

    public function test_the_progress_page_is_gone(): void
    {
        $parent = $this->makeParent();

        $this->actingAs($parent)->get('/parent/progress')->assertNotFound();
    }
}
