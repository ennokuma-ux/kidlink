<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EccdRecord;
use App\Models\GameScore;
use App\Models\Student;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * A student who is missing data has to be told so.
 *
 * The existing recommendations only flag students who score badly, and they
 * skip anything unassessed, so a child with no records at all used to look
 * exactly like a child doing fine. These tests pin down the opposite rule:
 * nothing recorded means it gets said, out loud, as needing intervention.
 */
class AnalyticsInterventionFlagTest extends TestCase
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

    private function makeStudent(string $first, ?string $section = '1-3 years old'): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => 'Test',
            'sex' => 'Female', 'date_of_birth' => Student::dateOfBirthForSection($section ?? '1-3 years old'),
            'class_name' => $section,
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now()->subMonths(3), 'is_active' => true,
        ]);
    }

    /** @param array<int, string> $statuses */
    private function markAttendance(Student $student, array $statuses, int $daysAgo = 0): void
    {
        foreach ($statuses as $offset => $status) {
            Attendance::create([
                'student_id' => $student->id,
                'date' => Carbon::today()->subDays($daysAgo + $offset),
                'status' => $status,
                'marked_by' => $this->teacher->id,
            ]);
        }
    }

    private function play(Student $student, string $game, int $pct = 90): void
    {
        GameScore::create([
            'student_id' => $student->id, 'game_type' => $game, 'level' => 1,
            'score' => $pct, 'total' => 100, 'mistakes' => 0, 'percentage' => $pct,
        ]);
    }

    private function assess(Student $student, string $domain, int $achieved = 8, int $total = 8): void
    {
        for ($i = 0; $i < $total; $i++) {
            EccdRecord::create([
                'student_id' => $student->id, 'domain' => $domain,
                'indicator' => "indicator {$i}",
                'status' => $i < $achieved ? 'Achieved' : 'In Progress',
                'assessed_by' => $this->teacher->id, 'assessed_at' => now(),
            ]);
        }
    }

    private function flagFor(Student $student): ?array
    {
        $flags = collect(app(AnalyticsService::class)->interventionFlags([$student->id]));

        return $flags->firstWhere('student_id', $student->id);
    }

    private function reasonTypes(array $flag): array
    {
        return collect($flag['reasons'])->pluck('type')->sort()->values()->all();
    }

    // ---------------------------------------------------------------- topics

    public function test_a_child_with_no_records_at_all_is_flagged_for_a_baseline(): void
    {
        $student = $this->makeStudent('Empty');

        $flag = $this->flagFor($student);

        $this->assertNotNull($flag, 'A child with nothing on record was not flagged at all.');
        $this->assertTrue($flag['intervention_required']);
        $this->assertSame(['Baseline'], $this->reasonTypes($flag));
        $this->assertStringContainsString('No attendance', $flag['reasons'][0]['message']);
    }

    public function test_a_new_child_is_reported_once_not_as_eleven_missing_topics(): void
    {
        $student = $this->makeStudent('New');

        $flag = $this->flagFor($student);

        // One "get a baseline" reason, not one per game and one per domain.
        $this->assertSame(1, $flag['gap_count']);
        $this->assertStringContainsString('baseline', $flag['reasons'][0]['action']);
    }

    public function test_unplayed_games_are_named_as_missing_topics(): void
    {
        $student = $this->makeStudent('Part');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present']);
        $this->play($student, 'letters');
        $this->assess($student, 'Fine Motor');

        $flag = $this->flagFor($student);

        $this->assertNotNull($flag);
        $this->assertContains('Game', $this->reasonTypes($flag));

        $game = collect($flag['reasons'])->firstWhere('type', 'Game');
        $this->assertStringContainsString('Numbers', $game['label']);
        $this->assertStringContainsString('Colors', $game['label']);
        $this->assertStringContainsString('Shapes', $game['label']);
        $this->assertStringContainsString('Animals', $game['label']);
        // The one they did play must not be listed as missing.
        $this->assertStringNotContainsString('Letters', $game['label']);
        $this->assertSame('4 of 5 games have no scores recorded.', $game['message']);
    }

    public function test_unassessed_domains_are_named_as_missing_topics(): void
    {
        $student = $this->makeStudent('Half');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present']);
        $this->play($student, 'letters');
        $this->play($student, 'numbers');
        $this->play($student, 'colors');
        $this->play($student, 'shapes');
        $this->play($student, 'animals');
        $this->assess($student, 'Fine Motor');

        $flag = $this->flagFor($student);

        $this->assertNotNull($flag);
        $this->assertContains('Domain', $this->reasonTypes($flag));

        $domain = collect($flag['reasons'])->firstWhere('type', 'Domain');
        $this->assertStringContainsString('Gross Motor', $domain['label']);
        $this->assertStringContainsString('Cognitive', $domain['label']);
        $this->assertStringNotContainsString('Fine Motor', $domain['label']);
        $this->assertSame('6 of 7 ECCD domains have no records.', $domain['message']);
    }

    public function test_a_missing_domain_suggests_the_game_that_reinforces_it(): void
    {
        $student = $this->makeStudent('Motor');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present']);
        $this->play($student, 'letters');
        $this->play($student, 'numbers');
        $this->play($student, 'colors');
        $this->play($student, 'shapes');
        $this->play($student, 'animals');
        $this->assess($student, 'Fine Motor');

        $domain = collect($this->flagFor($student)['reasons'])->firstWhere('type', 'Domain');

        // Gross Motor maps to shapes, Cognitive to numbers/shapes/colors.
        $this->assertStringContainsString('reinforce through', $domain['action']);
        $this->assertStringContainsString('shapes', $domain['action']);
    }

    public function test_a_fully_recorded_child_is_not_flagged(): void
    {
        $student = $this->makeStudent('Solid');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present', 'Present']);
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $this->assertNull($this->flagFor($student), 'A fully recorded child was flagged for no reason.');
    }

    // ------------------------------------------------------------ attendance

    public function test_no_attendance_marked_is_flagged(): void
    {
        $student = $this->makeStudent('Unmarked');

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $flag = $this->flagFor($student);

        $this->assertNotNull($flag);
        $this->assertTrue($flag['intervention_required']);
        $this->assertSame('high', $flag['severity']);

        $attendance = collect($flag['reasons'])->firstWhere('type', 'Attendance');
        $this->assertSame('Attendance not recorded', $attendance['label']);
        $this->assertNull($attendance['rate']);
        $this->assertSame(0, $attendance['marked']);
    }

    public function test_low_attendance_is_flagged_with_the_measured_rate(): void
    {
        $student = $this->makeStudent('Absent');

        $this->markAttendance($student, ['Present', 'Present', 'Absent', 'Absent', 'Absent', 'Present']);

        $attendance = collect($this->flagFor($student)['reasons'])->firstWhere('type', 'Attendance');

        $this->assertSame('Low attendance', $attendance['label']);
        $this->assertSame(50, $attendance['rate']);
        $this->assertSame(6, $attendance['marked']);
        // 50% is under the 60% floor, so it is treated as the most urgent tier.
        $this->assertSame('high', $this->flagFor($student)['severity']);
    }

    public function test_merely_low_attendance_sits_below_the_urgent_tier(): void
    {
        $student = $this->makeStudent('Patchy');

        // 3 of 4 days: 75%, under the 80% threshold but over the 60% floor.
        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Absent']);

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $flag = $this->flagFor($student);

        $this->assertTrue($flag['intervention_required']);
        $this->assertSame('medium', $flag['severity']);
    }

    public function test_excused_days_still_count_as_missed_time(): void
    {
        $student = $this->makeStudent('Excused');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Excused', 'Excused']);

        $attendance = collect($this->flagFor($student)['reasons'])->firstWhere('type', 'Attendance');

        $this->assertSame(60, $attendance['rate']);
    }

    public function test_good_attendance_is_not_flagged(): void
    {
        $student = $this->makeStudent('Regular');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present', 'Late']);

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $this->assertNull($this->flagFor($student));
    }

    public function test_too_few_marked_days_is_not_treated_as_a_trend(): void
    {
        $student = $this->makeStudent('One');

        // One marked absence: not enough to call a pattern, and the games and
        // domains are all covered, so nothing should be flagged.
        $this->markAttendance($student, ['Absent']);

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $this->assertNull($this->flagFor($student));
    }

    public function test_attendance_outside_the_window_is_ignored(): void
    {
        $student = $this->makeStudent('Old');

        // Absences from three months ago must not drag down a 30-day window,
        // and their absence from the window is not itself a gap: the recent
        // days are all recorded.
        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present']);
        $this->markAttendance($student, ['Absent', 'Absent', 'Absent'], daysAgo: 80);

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $this->assertNull($this->flagFor($student));
    }

    public function test_recent_gaps_outweigh_the_ones_a_child_used_to_have(): void
    {
        $student = $this->makeStudent('Improving');

        $this->markAttendance($student, ['Present', 'Absent', 'Absent', 'Absent']);
        $this->markAttendance($student, ['Absent', 'Absent', 'Absent', 'Absent', 'Absent'], daysAgo: 40);

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        // 1 of 4 recent days attended, not the 0 of 5 from before the window.
        $attendance = collect($this->flagFor($student)['reasons'])->firstWhere('type', 'Attendance');

        $this->assertSame(25, $attendance['rate']);
        $this->assertSame(4, $attendance['marked']);
    }

    // ------------------------------------------------------------- ordering

    /**
     * The grouped reason lines mean a child can be missing most topics and
     * still show only two of them. Counting lines rather than actual gaps is
     * what previously let that read as "no intervention needed".
     */
    public function test_missing_topics_alone_require_intervention(): void
    {
        $student = $this->makeStudent('Bare');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present']);
        $this->play($student, 'letters');
        $this->assess($student, 'Fine Motor');

        $flag = $this->flagFor($student);

        $this->assertTrue(
            $flag['intervention_required'],
            'A child with no scores in 4 games and 5 domains was told no intervention was needed.'
        );
        $this->assertSame('high', $flag['severity']);
    }

    public function test_a_single_missing_game_is_still_called_out_at_low_priority(): void
    {
        $student = $this->makeStudent('One');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present']);
        foreach (['letters', 'numbers', 'colors', 'shapes'] as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        $flag = $this->flagFor($student);

        $this->assertTrue($flag['intervention_required']);
        $this->assertSame('low', $flag['severity']);
        $this->assertSame(['Game'], $this->reasonTypes($flag));
    }

    public function test_the_most_urgent_student_is_listed_first(): void
    {
        $urgent = $this->makeStudent('Aaron');
        $partial = $this->makeStudent('Zoe');

        // Urgent: no attendance marked at all.
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($urgent, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($urgent, $domain);
        }

        // Partial: everything recorded except one game.
        $this->markAttendance($partial, ['Present', 'Present', 'Present', 'Present']);
        foreach (['letters', 'numbers', 'colors', 'shapes'] as $game) {
            $this->play($partial, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($partial, $domain);
        }

        $flags = app(AnalyticsService::class)->interventionFlags([$partial->id, $urgent->id]);

        $this->assertCount(2, $flags);
        // Alphabetically Zoe comes last, so a name-ordered list would put her
        // first; severity has to win.
        $this->assertSame($urgent->id, $flags[0]['student_id']);
        $this->assertSame('high', $flags[0]['severity']);
        $this->assertSame('low', $flags[1]['severity']);
    }

    public function test_high_and_medium_are_not_ordered_alphabetically(): void
    {
        $middle = $this->makeStudent('Mia');
        $worst = $this->makeStudent('Ana');

        // 3 of 5 days: 60%, under the 80% threshold but not under the 60% floor.
        $this->markAttendance($middle, ['Present', 'Present', 'Present', 'Absent', 'Absent']);
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($middle, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($middle, $domain);
        }

        // No register at all: high.
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($worst, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($worst, $domain);
        }

        $flags = app(AnalyticsService::class)->interventionFlags([$middle->id, $worst->id]);

        $this->assertSame($worst->id, $flags[0]['student_id']);
        $this->assertSame('high', $flags[0]['severity']);
        $this->assertSame('medium', $flags[1]['severity']);
    }

    public function test_an_empty_list_produces_nothing(): void
    {
        $this->assertSame([], app(AnalyticsService::class)->interventionFlags([]));
    }

    public function test_the_default_view_keeps_the_most_urgent_first(): void
    {
        // The urgent child sorts last by name, so name order and urgency order
        // cannot accidentally agree.
        $urgent = $this->makeStudent('Zoe');
        $partial = $this->makeStudent('Aaron');

        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($urgent, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($urgent, $domain);
        }

        $this->markAttendance($partial, ['Present', 'Present', 'Present', 'Present']);
        foreach (['letters', 'numbers', 'colors', 'shapes'] as $game) {
            $this->play($partial, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($partial, $domain);
        }

        $names = fn ($flags) => array_column($flags, 'name');

        // Default: urgency wins over the alphabet.
        $default = $this->actingAs($this->teacher)->get('/teacher/analytics')->assertOk();
        $this->assertSame(['Zoe Test', 'Aaron Test'], $names($default->viewData('interventions')));

        // Asking for name order is respected instead.
        $byName = $this->actingAs($this->teacher)->get('/teacher/analytics?sort=name')->assertOk();
        $this->assertSame(['Aaron Test', 'Zoe Test'], $names($byName->viewData('interventions')));

        // Section sorting must not crash on rows with no average of their own.
        $bySection = $this->actingAs($this->teacher)->get('/teacher/analytics?sort=section')->assertOk();
        $this->assertCount(2, $bySection->viewData('interventions'));
    }

    // ----------------------------------------------------------------- pages

    public function test_the_teacher_page_leads_with_the_intervention_panel(): void
    {
        $student = $this->makeStudent('Blank');

        // Not child mode here: entering it swaps the session to the child, and
        // the teacher route would then correctly refuse the request.
        $response = $this->actingAs($this->teacher)->get('/teacher/analytics')->assertOk();

        $response->assertSee('Intervention Required');
        $response->assertSee('requires intervention');
        $response->assertSee('Blank Test');
        $response->assertSee('No attendance, game scores, ECCD records yet.');

        // It has to lead the page, not sit under the charts. The flag panel
        // comes before the first stat card, and before the cohort narrative
        // that used to be the only mention of intervention.
        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Intervention Required'),
            strpos($content, 'Game Levels Played'),
            'The intervention panel is no longer above the headline stats.'
        );
    }

    public function test_the_teacher_page_says_so_when_nothing_is_missing(): void
    {
        $student = $this->makeStudent('Complete');

        $this->markAttendance($student, ['Present', 'Present', 'Present', 'Present', 'Present']);
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->play($student, $game);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $this->assess($student, $domain);
        }

        // Not child mode here: entering it swaps the session to the child, and
        // the teacher route would then correctly refuse the request.
        $response = $this->actingAs($this->teacher)->get('/teacher/analytics')->assertOk();

        $response->assertSee('Nothing is missing');
        $response->assertDontSee('requires intervention');
    }

    public function test_the_admin_page_shows_the_panel_for_the_whole_centre(): void
    {
        $student = $this->makeStudent('Nobody');

        $this->actingAs($this->admin)
            ->get('/admin/analytics')
            ->assertOk()
            ->assertSee('Intervention Required')
            ->assertSee('Nobody Test');
    }

    public function test_the_panel_respects_the_section_filter(): void
    {
        $inside = $this->makeStudent('Inside', '1-3 years old');
        $this->makeStudent('Outside', '4-5 years old');

        // Not child mode: it would swap the session away from the teacher.
        $response = $this->actingAs($this->teacher)
            ->get('/teacher/analytics?section='.urlencode('4-5 years old'))
            ->assertOk();

        // The student picker lists every student whatever the filter, so assert
        // on the flags themselves rather than on raw page text.
        $flags = $response->viewData('interventions');

        $this->assertCount(1, $flags);
        $this->assertSame('Outside Test', $flags[0]['name']);
        $this->assertSame(1, $response->viewData('interventionCount'));
    }

    public function test_a_child_with_attendance_but_no_assessments_is_told_the_truth(): void
    {
        $student = $this->makeStudent('Assessed');

        // Marked in, but poorly, and never assessed. The register exists, so
        // "no attendance" would be a lie and the rate has to be shown instead.
        $this->markAttendance($student, ['Present', 'Absent', 'Absent']);

        $flag = $this->flagFor($student);

        $this->assertSame(['Attendance', 'Baseline'], $this->reasonTypes($flag));

        $baseline = collect($flag['reasons'])->firstWhere('type', 'Baseline');
        $this->assertSame('No game scores, ECCD records yet.', $baseline['message']);

        $attendance = collect($flag['reasons'])->firstWhere('type', 'Attendance');
        $this->assertSame(33, $attendance['rate']);
    }

    public function test_an_archived_student_is_not_flagged(): void
    {
        $student = $this->makeStudent('Archived');
        $student->update(['is_active' => false]);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics')->assertOk();

        $response->assertDontSee('Archived Test');
        $this->assertSame([], $response->viewData('interventions'));
    }
}