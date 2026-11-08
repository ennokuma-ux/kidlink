<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityPlannerWeekTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $otherTeacher;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-06-10 09:00:00')); // a Wednesday
        Cache::flush();

        $this->teacher = $this->makeTeacher('week@test.local');
        $this->otherTeacher = $this->makeTeacher('other@test.local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeTeacher(string $email): User
    {
        return User::create([
            'first_name' => 'Teach', 'last_name' => 'Er',
            'email' => $email, 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);
    }

    private function mondayOf(string $date): string
    {
        return Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    private function makeActivity(string $title, string $weekStart, array $overrides = []): Activity
    {
        return Activity::create(array_merge([
            'title' => $title,
            'type' => 'Lesson',
            'day_of_week' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'teacher_id' => $this->teacher->id,
            'week_start' => $weekStart,
            'is_completed' => false,
        ], $overrides));
    }

    public function test_the_board_only_shows_the_requested_week(): void
    {
        $this->makeActivity('This Week', $this->mondayOf('2026-06-10'));
        $this->makeActivity('Next Week', $this->mondayOf('2026-06-17'));
        $this->makeActivity('Last Week', $this->mondayOf('2026-06-03'));

        $response = $this->actingAs($this->teacher)->get('/teacher/activities');

        $response->assertOk()
            ->assertSee('This Week')
            ->assertDontSee('Next Week')
            ->assertDontSee('Last Week');
    }

    public function test_the_default_board_is_the_current_week(): void
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/activities');

        $this->assertSame(
            Carbon::parse('2026-06-08')->toDateString(),
            $response->viewData('weekStart')->toDateString(),
        );
        $response->assertSee('Jun 8');
    }

    public function test_a_week_in_the_past_can_be_opened_and_still_holds_its_own_activities(): void
    {
        $this->makeActivity('Archived Plan', $this->mondayOf('2026-06-03'));

        $response = $this->actingAs($this->teacher)
            ->get('/teacher/activities?week='.$this->mondayOf('2026-06-03'));

        $response->assertOk()->assertSee('Archived Plan');
    }

    public function test_navigation_links_point_at_the_neighbouring_weeks(): void
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/activities');

        $this->assertSame($this->mondayOf('2026-06-01'), $response->viewData('prevWeek'));
        $this->assertSame($this->mondayOf('2026-06-15'), $response->viewData('nextWeek'));
        $response->assertSee('week='.$this->mondayOf('2026-06-15'), false);
    }

    public function test_a_week_parameter_midweek_is_normalised_to_its_monday(): void
    {
        $this->makeActivity('Kept', $this->mondayOf('2026-06-10'));

        $response = $this->actingAs($this->teacher)
            ->get('/teacher/activities?week=2026-06-11'); // Thursday inside the same week

        $response->assertOk()->assertSee('Kept');
        $this->assertSame($this->mondayOf('2026-06-08'), $response->viewData('weekStart')->toDateString());
    }

    public function test_an_unparseable_week_falls_back_to_the_current_week(): void
    {
        $this->makeActivity('Current', $this->mondayOf('2026-06-10'));

        $response = $this->actingAs($this->teacher)->get('/teacher/activities?week=not-a-date');

        $response->assertOk()->assertSee('Current');
        $this->assertSame($this->mondayOf('2026-06-08'), $response->viewData('weekStart')->toDateString());
    }

    public function test_stats_only_count_the_visible_week(): void
    {
        $this->makeActivity('A', $this->mondayOf('2026-06-10'));
        $this->makeActivity('B', $this->mondayOf('2026-06-10'), ['is_completed' => true]);
        $this->makeActivity('C', $this->mondayOf('2026-06-17'), ['is_completed' => true]);

        $stats = $this->actingAs($this->teacher)->get('/teacher/activities')->viewData('stats');

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['completed']);
        $this->assertSame(1, $stats['pending']);
    }

    public function test_completion_is_tracked_per_week(): void
    {
        $thisWeek = $this->makeActivity('This Week', $this->mondayOf('2026-06-10'));
        $nextWeek = $this->makeActivity('Next Week', $this->mondayOf('2026-06-17'));

        $this->actingAs($this->teacher)
            ->put('/teacher/activities/'.$thisWeek->id.'/toggle')
            ->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-10')]));

        $this->assertTrue($thisWeek->fresh()->is_completed);
        $this->assertFalse($nextWeek->fresh()->is_completed, 'next week must stay pending');
    }

    public function test_toggling_sends_the_teacher_back_to_the_owning_week(): void
    {
        $activity = $this->makeActivity('Older', $this->mondayOf('2026-06-03'));

        $this->actingAs($this->teacher)
            ->put('/teacher/activities/'.$activity->id.'/toggle')
            ->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-03')]));
    }

    public function test_deleting_sends_the_teacher_back_to_the_owning_week(): void
    {
        $activity = $this->makeActivity('Older', $this->mondayOf('2026-06-03'));

        $this->actingAs($this->teacher)
            ->delete('/teacher/activities/'.$activity->id)
            ->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-03')]));

        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    public function test_an_activity_is_created_into_the_week_being_viewed(): void
    {
        $this->actingAs($this->teacher)
            ->post('/teacher/activities', [
                'title' => 'Future Craft', 'type' => 'Craft', 'day_of_week' => 'Tuesday',
                'start_time' => '10:00', 'end_time' => '11:00', 'group_name' => 'Blue',
                'week' => '2026-06-18',
            ])
            ->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-17')]));

        $this->assertDatabaseHas('activities', [
            'title' => 'Future Craft',
            'teacher_id' => $this->teacher->id,
        ]);

        $stored = Activity::where('title', 'Future Craft')->firstOrFail();

        $this->assertSame(
            $this->mondayOf('2026-06-17'),
            $stored->week_start->toDateString(),
        );
    }

    public function test_an_activity_without_a_week_lands_in_the_current_week(): void
    {
        $this->actingAs($this->teacher)->post('/teacher/activities', [
            'title' => 'No Week Given', 'type' => 'Story', 'day_of_week' => 'Friday',
            'start_time' => '13:00', 'end_time' => '13:30',
        ])->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-08')]));
    }

    public function test_activities_are_scoped_to_the_owning_teacher(): void
    {
        $this->makeActivity('Mine', $this->mondayOf('2026-06-10'));

        $theirs = $this->makeActivity('Theirs', $this->mondayOf('2026-06-10'), [
            'teacher_id' => $this->otherTeacher->id,
        ]);

        $this->actingAs($this->teacher)
            ->get('/teacher/activities')
            ->assertSee('Mine')
            ->assertDontSee('Theirs');

        $this->actingAs($this->teacher)->put('/teacher/activities/'.$theirs->id.'/toggle')->assertForbidden();
        $this->actingAs($this->teacher)->delete('/teacher/activities/'.$theirs->id)->assertForbidden();
    }

    public function test_the_end_time_must_be_after_the_start_time(): void
    {
        $this->actingAs($this->teacher)
            ->post('/teacher/activities', [
                'title' => 'Backwards', 'type' => 'Lesson', 'day_of_week' => 'Monday',
                'start_time' => '11:00', 'end_time' => '10:00',
            ])
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseMissing('activities', ['title' => 'Backwards']);
    }

    public function test_activities_on_the_same_day_are_ordered_by_start_time(): void
    {
        $week = $this->mondayOf('2026-06-10');
        $this->makeActivity('Late', $week, ['start_time' => '14:00', 'end_time' => '15:00']);
        $this->makeActivity('Early', $week, ['start_time' => '07:00', 'end_time' => '08:00']);

        $view = $this->actingAs($this->teacher)->get('/teacher/activities')->viewData('activities');

        $titles = $view['Monday']->pluck('title')->all();

        $this->assertSame(['Early', 'Late'], $titles);
    }

    public function test_the_dashboard_only_counts_activities_from_the_current_week(): void
    {
        // Wednesday, matching the frozen "now" in setUp().
        $this->makeActivity('Today Plan', $this->mondayOf('2026-06-10'), ['day_of_week' => 'Wednesday']);
        $this->makeActivity('Same Day Last Week', $this->mondayOf('2026-06-03'), ['day_of_week' => 'Wednesday']);
        $this->makeActivity('Same Day Next Week', $this->mondayOf('2026-06-17'), ['day_of_week' => 'Wednesday']);

        $view = $this->actingAs($this->teacher)->get('/teacher/dashboard')->viewData();

        $this->assertSame(1, $view['activitiesToday']);
    }

    // --- conflict detection -------------------------------------------------

    private function postActivity(array $overrides = [])
    {
        return $this->actingAs($this->teacher)->post('/teacher/activities', array_merge([
            'title' => 'Proposed', 'type' => 'Lesson', 'day_of_week' => 'Monday',
            'start_time' => '09:00', 'end_time' => '10:00', 'week' => $this->mondayOf('2026-06-10'),
        ], $overrides));
    }

    public function test_an_overlapping_slot_is_rejected(): void
    {
        $this->makeActivity('Existing', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity(['start_time' => '09:30', 'end_time' => '10:30'])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseMissing('activities', ['title' => 'Proposed']);
    }

    public function test_a_slot_fully_containing_an_existing_one_is_rejected(): void
    {
        $this->makeActivity('Existing', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity(['start_time' => '08:00', 'end_time' => '11:00'])
            ->assertSessionHasErrors('start_time');
    }

    public function test_activities_that_merely_touch_at_an_endpoint_are_allowed(): void
    {
        $this->makeActivity('Morning', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity(['start_time' => '10:00', 'end_time' => '11:00'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('activities', ['title' => 'Proposed']);
    }

    public function test_the_same_slot_on_a_different_day_is_allowed(): void
    {
        $this->makeActivity('Monday One', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity(['day_of_week' => 'Tuesday'])->assertSessionHasNoErrors();
    }

    public function test_the_same_slot_in_a_different_week_is_allowed(): void
    {
        $this->makeActivity('This Week', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity(['week' => $this->mondayOf('2026-06-17')])->assertSessionHasNoErrors();
    }

    public function test_another_teachers_booking_does_not_block_a_slot(): void
    {
        $this->makeActivity('Theirs', $this->mondayOf('2026-06-10'), [
            'teacher_id' => $this->otherTeacher->id,
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity()->assertSessionHasNoErrors();
    }

    public function test_the_conflict_message_names_the_clashing_activity(): void
    {
        $this->makeActivity('Circle Time', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->postActivity(['start_time' => '09:15', 'end_time' => '09:45'])
            ->assertSessionHasErrors('start_time');

        $this->assertStringContainsString('Circle Time', session('errors')->first('start_time'));
    }

    // --- editing ------------------------------------------------------------

    public function test_an_activity_can_be_edited(): void
    {
        $activity = $this->makeActivity('Typo', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put('/teacher/activities/'.$activity->id, [
            'title' => 'Corrected', 'type' => 'Craft', 'day_of_week' => 'Tuesday',
            'start_time' => '14:00', 'end_time' => '15:30', 'group_name' => 'Blue',
            'week' => $this->mondayOf('2026-06-10'),
        ])->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-10')]));

        $activity->refresh();

        $this->assertSame('Corrected', $activity->title);
        $this->assertSame('Craft', $activity->type);
        $this->assertSame('Tuesday', $activity->day_of_week);
        $this->assertSame('Blue', $activity->group_name);
    }

    public function test_an_edit_can_move_an_activity_to_another_week(): void
    {
        $activity = $this->makeActivity('Movable', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put('/teacher/activities/'.$activity->id, [
            'title' => 'Movable', 'type' => 'Lesson', 'day_of_week' => 'Monday',
            'start_time' => '08:00', 'end_time' => '09:00', 'week' => $this->mondayOf('2026-06-17'),
        ])->assertRedirect(route('teacher.activities.index', ['week' => $this->mondayOf('2026-06-17')]));

        $this->assertSame(
            $this->mondayOf('2026-06-17'),
            $activity->fresh()->week_start->toDateString(),
        );
    }

    public function test_editing_is_rejected_when_the_new_slot_clashes(): void
    {
        $this->makeActivity('Keeper', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);
        $mover = $this->makeActivity('Mover', $this->mondayOf('2026-06-10'), [
            'start_time' => '13:00', 'end_time' => '14:00',
        ]);

        $this->actingAs($this->teacher)->put('/teacher/activities/'.$mover->id, [
            'title' => 'Mover', 'type' => 'Lesson', 'day_of_week' => 'Monday',
            'start_time' => '09:30', 'end_time' => '10:30', 'week' => $this->mondayOf('2026-06-10'),
        ])->assertSessionHasErrors('start_time');

        $this->assertSame('13:00', substr((string) $mover->fresh()->start_time, 0, 5));
    }

    public function test_an_activity_does_not_clash_with_itself_when_saved_unchanged(): void
    {
        $activity = $this->makeActivity('Steady', $this->mondayOf('2026-06-10'), [
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);

        $this->actingAs($this->teacher)->put('/teacher/activities/'.$activity->id, [
            'title' => 'Steady Renamed', 'type' => 'Lesson', 'day_of_week' => 'Monday',
            'start_time' => '09:00', 'end_time' => '10:00', 'week' => $this->mondayOf('2026-06-10'),
        ])->assertSessionHasNoErrors();

        $this->assertSame('Steady Renamed', $activity->fresh()->title);
    }

    public function test_another_teacher_cannot_edit_an_activity(): void
    {
        $theirs = $this->makeActivity('Theirs', $this->mondayOf('2026-06-10'), [
            'teacher_id' => $this->otherTeacher->id,
        ]);

        $this->actingAs($this->teacher)->put('/teacher/activities/'.$theirs->id, [
            'title' => 'Hijacked', 'type' => 'Lesson', 'day_of_week' => 'Monday',
            'start_time' => '08:00', 'end_time' => '09:00', 'week' => $this->mondayOf('2026-06-10'),
        ])->assertForbidden();

        $this->assertSame('Theirs', $theirs->fresh()->title);
    }

    // --- participation ------------------------------------------------------

    private function makeStudent(string $first, string $last = 'Cruz'): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => $last, 'sex' => 'Female',
            'date_of_birth' => now()->subYears(4), 'class_name' => '1-3 years old',
            'teacher_id' => $this->teacher->id, 'is_active' => true,
        ]);
    }

    public function test_the_participation_page_lists_the_teachers_students(): void
    {
        $this->makeStudent('Ana');
        $this->makeStudent('Ben', 'Reyes');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)
            ->get("/teacher/activities/{$activity->id}/participation")
            ->assertOk()
            ->assertSee('Group Time')
            ->assertSee('Ana Cruz')
            ->assertSee('Ben Reyes');
    }

    public function test_participation_records_scores_and_feedback(): void
    {
        $ana = $this->makeStudent('Ana');
        $ben = $this->makeStudent('Ben', 'Reyes');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)
            ->put("/teacher/activities/{$activity->id}/participation", [
                'participating' => [$ana->id, $ben->id],
                'score' => [$ana->id => 90, $ben->id => 55],
                'feedback' => [$ana->id => 'Excellent', $ben->id => ''],
            ])
            ->assertRedirect(route('teacher.activities.participation', $activity));

        $this->assertDatabaseHas('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $ana->id,
            'score' => 90, 'feedback' => 'Excellent',
        ]);
        $this->assertDatabaseHas('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $ben->id,
            'score' => 55, 'feedback' => null,
        ]);
    }

    public function test_saving_participation_twice_updates_instead_of_duplicating(): void
    {
        $ana = $this->makeStudent('Ana');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        foreach ([70, 85] as $score) {
            $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
                'participating' => [$ana->id], 'score' => [$ana->id => $score],
            ]);
        }

        $this->assertSame(1, ActivityAttendance::where('activity_id', $activity->id)->count());
        $this->assertSame(85, ActivityAttendance::where('activity_id', $activity->id)->first()->score);
    }

    public function test_unchecking_a_student_removes_their_record(): void
    {
        $ana = $this->makeStudent('Ana');
        $ben = $this->makeStudent('Ben', 'Reyes');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$ana->id, $ben->id],
            'score' => [$ana->id => 80, $ben->id => 80],
        ]);

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$ana->id], 'score' => [$ana->id => 80],
        ]);

        $this->assertDatabaseMissing('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $ben->id,
        ]);
        $this->assertDatabaseHas('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $ana->id,
        ]);
    }

    public function test_saving_nobody_clears_every_record(): void
    {
        $ana = $this->makeStudent('Ana');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$ana->id], 'score' => [$ana->id => 80],
        ]);

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [],
        ]);

        $this->assertSame(0, ActivityAttendance::where('activity_id', $activity->id)->count());
    }

    public function test_a_score_outside_zero_to_one_hundred_is_rejected(): void
    {
        $ana = $this->makeStudent('Ana');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$ana->id], 'score' => [$ana->id => 140],
        ])->assertSessionHasErrors('score.'.$ana->id);

        $this->assertDatabaseMissing('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $ana->id,
        ]);
    }

    public function test_participation_cannot_be_recorded_for_another_teachers_student(): void
    {
        $foreign = Student::create([
            'first_name' => 'Not', 'last_name' => 'Mine', 'sex' => 'Female',
            'date_of_birth' => now()->subYears(4), 'class_name' => '1-3 years old',
            'teacher_id' => $this->otherTeacher->id, 'is_active' => true,
        ]);
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$foreign->id], 'score' => [$foreign->id => 100],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $foreign->id,
        ]);
    }

    public function test_another_teacher_cannot_open_or_save_participation(): void
    {
        $theirs = $this->makeActivity('Theirs', $this->mondayOf('2026-06-10'), [
            'teacher_id' => $this->otherTeacher->id,
        ]);
        $ana = $this->makeStudent('Ana');

        $this->actingAs($this->teacher)
            ->get("/teacher/activities/{$theirs->id}/participation")
            ->assertForbidden();

        $this->actingAs($this->teacher)
            ->put("/teacher/activities/{$theirs->id}/participation", ['participating' => [$ana->id]])
            ->assertForbidden();
    }

    public function test_the_participation_page_reports_the_aggregate(): void
    {
        $ana = $this->makeStudent('Ana');
        $ben = $this->makeStudent('Ben', 'Reyes');
        $carol = $this->makeStudent('Carol', 'Diaz');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$ana->id, $ben->id, $carol->id],
            'score' => [$ana->id => 80, $ben->id => 60, $carol->id => 70],
        ]);

        $stats = $this->actingAs($this->teacher)
            ->get("/teacher/activities/{$activity->id}/participation")
            ->viewData('stats');

        $this->assertSame(3, $stats['participating']);
        $this->assertSame(3, $stats['scored']);
        $this->assertSame(70.0, $stats['average']);
    }

    public function test_deleting_an_activity_removes_its_participation(): void
    {
        $ana = $this->makeStudent('Ana');
        $activity = $this->makeActivity('Group Time', $this->mondayOf('2026-06-10'));

        $this->actingAs($this->teacher)->put("/teacher/activities/{$activity->id}/participation", [
            'participating' => [$ana->id], 'score' => [$ana->id => 80],
        ]);

        $this->actingAs($this->teacher)->delete('/teacher/activities/'.$activity->id);

        $this->assertDatabaseMissing('activity_attendance', [
            'activity_id' => $activity->id, 'student_id' => $ana->id,
        ]);
    }
}
