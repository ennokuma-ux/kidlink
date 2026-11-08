<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EccdRecord;
use App\Models\GameScore;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParentResultsMilestonesAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $parent;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);
        $this->parent = User::create([
            'first_name' => 'Pat', 'last_name' => 'Parent',
            'email' => 'parent@test.local', 'password' => Hash::make('password123'),
            'role' => 'parent', 'is_active' => true,
        ]);
        $this->student = Student::create([
            'first_name' => 'Mia', 'last_name' => 'Santos', 'sex' => 'Female',
            'date_of_birth' => '2021-01-01', 'class_name' => '4-5 years old',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);
        $this->student->parents()->attach($this->parent->id);
    }

    public function test_results_lists_game_scores(): void
    {
        GameScore::create([
            'student_id' => $this->student->id, 'game_type' => 'colors-sort', 'level' => 3,
            'score' => 5, 'total' => 5, 'mistakes' => 0, 'percentage' => 100, 'time_taken' => 75,
        ]);
        GameScore::create([
            'student_id' => $this->student->id, 'game_type' => 'letters', 'level' => 1,
            'score' => 5, 'total' => 5, 'mistakes' => 5, 'percentage' => 50, 'time_taken' => 30,
        ]);

        $this->actingAs($this->parent)->get('/parent/results')
            ->assertOk()
            ->assertSee('Color Sort')
            ->assertSee('Letters')
            ->assertSee('5 / 5 correct')
            ->assertSee('1m 15s')
            ->assertSee('75%'); // average of 100 and 50
    }

    public function test_results_shows_an_empty_state_without_game_scores(): void
    {
        $this->actingAs($this->parent)->get('/parent/results')
            ->assertOk()
            ->assertSee(__('parent_ui.no_game_scores', ['name' => 'Mia']));
    }

    public function test_attendance_calendar_marks_recorded_days(): void
    {
        $day = now()->startOfMonth();

        Attendance::create([
            'student_id' => $this->student->id, 'date' => $day->toDateString(),
            'status' => 'Late', 'marked_by' => $this->teacher->id,
        ]);

        $html = $this->actingAs($this->parent)->get('/parent/attendance')->assertOk()->getContent();

        $this->assertStringContainsString($day->toDateString() . ' · ' . __('parent_ui.stat_late'), $html);
        $this->assertStringContainsString('bg-yellow-100', $html);
    }

    public function test_attendance_rate_counts_late_and_ignores_excused(): void
    {
        foreach (['Present', 'Late', 'Absent', 'Excused'] as $i => $status) {
            Attendance::create([
                'student_id' => $this->student->id,
                'date' => now()->startOfMonth()->addDays($i)->toDateString(),
                'status' => $status, 'marked_by' => $this->teacher->id,
            ]);
        }

        // (Present + Late) / (Present + Late + Absent) = 2 / 3
        $this->actingAs($this->parent)->get('/parent/attendance')
            ->assertOk()
            ->assertSee('67%');
    }

    public function test_attendance_ignores_a_malformed_month(): void
    {
        $this->actingAs($this->parent)->get('/parent/attendance?month=garbage')->assertOk();
    }

    public function test_month_navigation_keeps_the_selected_child(): void
    {
        $html = $this->actingAs($this->parent)
            ->get('/parent/attendance?child_id=' . $this->student->id)
            ->assertOk()->getContent();

        $this->assertStringContainsString('child_id=' . $this->student->id . '&amp;month=', $html);
    }

    public function test_milestone_filter_does_not_zero_the_other_domains(): void
    {
        EccdRecord::create([
            'student_id' => $this->student->id, 'domain' => 'Gross Motor', 'indicator' => 'Walks backwards',
            'status' => 'Achieved', 'assessed_by' => $this->teacher->id,
        ]);
        EccdRecord::create([
            'student_id' => $this->student->id, 'domain' => 'Cognitive', 'indicator' => 'Matches objects',
            'status' => 'Not Yet', 'assessed_by' => $this->teacher->id,
        ]);

        $this->actingAs($this->parent)->get('/parent/milestones?domain=Cognitive')
            ->assertOk()
            ->assertSee('1/1 ' . __('parent_ui.achieved_label'))   // Gross Motor still reports its progress
            ->assertSee('Matches objects')
            ->assertDontSee('Walks backwards');                    // ...but only Cognitive is listed
    }

    public function test_milestone_filter_links_keep_the_selected_child(): void
    {
        EccdRecord::create([
            'student_id' => $this->student->id, 'domain' => 'Cognitive', 'indicator' => 'Matches objects',
            'status' => 'Not Yet', 'assessed_by' => $this->teacher->id,
        ]);

        $html = $this->actingAs($this->parent)->get('/parent/milestones')->assertOk()->getContent();

        $this->assertStringContainsString('child_id=' . $this->student->id . '&amp;domain=Cognitive', $html);
    }
}
