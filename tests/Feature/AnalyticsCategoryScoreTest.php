<?php

namespace Tests\Feature;

use App\Models\EccdRecord;
use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnalyticsCategoryScoreTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->teacher = User::create([
            'first_name' => 'Teach', 'last_name' => 'Er',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        $this->admin = User::create([
            'first_name' => 'Super', 'last_name' => 'Admin',
            'email' => 'admin@test.local', 'password' => Hash::make('password123'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function makeStudent(string $first, string $section): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => 'Test', 'sex' => 'Female',
            'date_of_birth' => Student::dateOfBirthForSection($section),
            'class_name' => $section, 'teacher_id' => $this->teacher->id,
            'is_active' => true, 'enrolled_date' => now(),
        ]);
    }

    private function play(Student $student, string $game, int $score, int $total = 100): void
    {
        GameScore::create([
            'student_id' => $student->id, 'game_type' => $game, 'level' => 1,
            'score' => $score, 'total' => $total, 'mistakes' => 0,
        ]);
    }

    /**
     * @param array<string, int> $achieved achieved count per domain
     */
    private function assess(Student $student, string $domain, int $achieved, int $total): void
    {
        for ($i = 0; $i < $total; $i++) {
            EccdRecord::create([
                'student_id' => $student->id, 'domain' => $domain,
                'indicator' => "indicator {$i}", 'status' => $i < $achieved ? 'Achieved' : 'In Progress',
                'assessed_by' => $this->teacher->id, 'assessed_at' => now(),
            ]);
        }
    }

    public function test_each_game_gets_its_own_score_instead_of_one_blend()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->play($student, 'letters', 90);
        $this->play($student, 'shapes', 40);
        $this->play($student, 'numbers', 65);

        $row = app(AnalyticsService::class)->studentCategoryBreakdown([$student->id])[0];

        $this->assertSame(90, $row['games']['letters']);
        $this->assertSame(40, $row['games']['shapes']);
        $this->assertSame(65, $row['games']['numbers']);

        // Never played, so no score rather than a misleading zero.
        $this->assertNull($row['games']['colors']);
        $this->assertNull($row['games']['animals']);

        // The blend is the mean of the played games only: (90 + 40 + 65) / 3.
        $this->assertSame(65, $row['game_overall']);
    }

    public function test_each_eccd_domain_gets_its_own_score()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->assess($student, 'Fine Motor', 1, 4);   // 25%
        $this->assess($student, 'Cognitive', 3, 4);    // 75%
        $this->assess($student, 'Self-Help', 2, 2);    // 100%

        $row = app(AnalyticsService::class)->studentCategoryBreakdown([$student->id])[0];

        $this->assertSame(25, $row['domains']['Fine Motor']);
        $this->assertSame(75, $row['domains']['Cognitive']);
        $this->assertSame(100, $row['domains']['Self-Help']);
        $this->assertNull($row['domains']['Gross Motor']);

        // (25 + 75 + 100) / 3.
        $this->assertSame(67, $row['domain_overall']);
    }

    public function test_category_breakdown_carries_the_section()
    {
        $younger = $this->makeStudent('Ana', '1-3 years old');
        $older = $this->makeStudent('Ben', '4-5 years old');

        $rows = collect(app(AnalyticsService::class)->studentCategoryBreakdown([$younger->id, $older->id]))
            ->keyBy('name');

        $this->assertSame('1-3 years old', $rows['Ana Test']['class_name']);
        $this->assertSame('4-5 years old', $rows['Ben Test']['class_name']);
    }

    public function test_section_rollup_averages_the_same_category_scores()
    {
        $a = $this->makeStudent('Ana', '1-3 years old');
        $b = $this->makeStudent('Ben', '1-3 years old');
        $c = $this->makeStudent('Cara', '4-5 years old');

        $this->play($a, 'letters', 80);
        $this->play($b, 'letters', 60);
        $this->play($c, 'letters', 30);

        $rows = collect(app(AnalyticsService::class)->sectionScoreBreakdown([$a->id, $b->id, $c->id]))
            ->keyBy('label');

        $this->assertSame(2, $rows['1-3 years old']['students']);
        $this->assertSame(70, $rows['1-3 years old']['game_pct']);   // (80 + 60) / 2
        $this->assertSame(30, $rows['4-5 years old']['game_pct']);
        $this->assertNull($rows['1-3 years old']['domain_pct']);     // no ECCD data at all

        $this->assertSame(Student::colorForSection('1-3 years old'), $rows['1-3 years old']['color']);
    }

    public function test_teacher_page_renders_a_column_per_category()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->play($student, 'letters', 90);
        $this->play($student, 'shapes', 40);
        $this->assess($student, 'Cognitive', 3, 4);

        $html = $this->actingAs($this->teacher)->get('/teacher/analytics')->getContent();

        // One column for every game type and every ECCD domain.
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $this->assertStringContainsString('>' . ucfirst($game) . '</th>', $html);
        }
        foreach (AnalyticsService::ECCD_DOMAINS as $domain) {
            $short = AnalyticsService::ECCD_DOMAIN_SHORT[$domain];
            $this->assertStringContainsString('title="' . $domain . '">' . $short . '</th>', $html);
        }

        $this->assertStringContainsString('Score by Category, Per Student', $html);
        $this->assertStringContainsString('Average Score by Section', $html);

        // The unplayed game is a dash, the played ones carry their own score.
        $this->assertStringContainsString('Shapes', $html);
        $this->assertStringContainsString('90%', $html);
        $this->assertStringContainsString('40%', $html);
    }

    public function test_teacher_section_filter_narrows_the_category_table()
    {
        $younger = $this->makeStudent('Ana', '1-3 years old');
        $older = $this->makeStudent('Ben', '4-5 years old');
        $this->play($younger, 'letters', 90);
        $this->play($older, 'letters', 30);

        $all = $this->actingAs($this->teacher)->get('/teacher/analytics');
        $this->assertCount(2, $all->viewData('categoryScores'));
        $this->assertCount(2, $all->viewData('sectionScores'));

        $filtered = $this->actingAs($this->teacher)
            ->get('/teacher/analytics?section=' . urlencode('1-3 years old'));

        $this->assertCount(1, $filtered->viewData('categoryScores'));
        $this->assertSame(['Ana Test'], array_column($filtered->viewData('categoryScores'), 'name'));

        // The per-section table compares classes, so the section filter must not
        // hide the other class from it.
        $this->assertCount(2, $filtered->viewData('sectionScores'));
    }

    public function test_selecting_one_student_leaves_the_section_rollup_on_the_whole_cohort()
    {
        $ana = $this->makeStudent('Ana', '1-3 years old');
        $ben = $this->makeStudent('Ben', '1-3 years old');
        $cara = $this->makeStudent('Cara', '4-5 years old');
        $this->play($ana, 'letters', 90);
        $this->play($ben, 'letters', 60);
        $this->play($cara, 'letters', 30);

        $selected = $this->actingAs($this->teacher)
            ->get('/teacher/analytics?student=' . $ana->id);

        // The child-specific figures narrow to Ana...
        $this->assertSame($ana->id, $selected->viewData('selectedStudent')->id);
        $this->assertCount(1, $selected->viewData('categoryScores'));

        // ...but the per-section rollup still counts every child in the cohort,
        // so choosing a student to inspect must not shrink another class.
        $sections = collect($selected->viewData('sectionScores'))->keyBy('label');
        $this->assertCount(2, $sections);

        $this->assertSame(2, $sections['1-3 years old']['students']);
        $this->assertSame(1, $sections['4-5 years old']['students']);
        $this->assertSame(75, $sections['1-3 years old']['game_pct']); // (90 + 60) / 2
        $this->assertSame(30, $sections['4-5 years old']['game_pct']);

        // The intervention panel is class-wide too: it keeps naming every child
        // who needs action, while the blurb stays on the child on screen.
        $this->assertCount(3, $selected->viewData('interventions'));
        $this->assertCount(1, $selected->viewData('studentInterventions'));
    }

    public function test_selecting_one_student_leaves_the_admin_rollups_on_the_whole_cohort()
    {
        $ana = $this->makeStudent('Ana', '1-3 years old');
        $ben = $this->makeStudent('Ben', '1-3 years old');
        $cara = $this->makeStudent('Cara', '4-5 years old');
        $this->play($ana, 'letters', 90);
        $this->play($ben, 'letters', 60);
        $this->play($cara, 'letters', 30);

        $selected = $this->actingAs($this->admin)
            ->get('/admin/analytics?student=' . $ana->id);

        // The child on screen is narrowed...
        $this->assertSame($ana->id, $selected->viewData('selectedStudent')->id);

        // ...but the centre-wide per-class table still counts every child, so
        // inspecting one student must not shrink the whole centre to one row.
        $sections = collect($selected->viewData('sectionScores'))->keyBy('label');
        $this->assertCount(2, $sections);
        $this->assertSame(2, $sections['1-3 years old']['students']);
        $this->assertSame(1, $sections['4-5 years old']['students']);

        // ...and the intervention panel keeps naming everyone who needs action,
        // not just the child being inspected.
        $this->assertCount(3, $selected->viewData('interventions'));
    }

    public function test_admin_leaderboards_carry_per_game_scores()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->play($student, 'letters', 95);
        $this->play($student, 'colors', 35);

        $rows = $this->actingAs($this->admin)->get('/admin/analytics')->viewData('topStudents');

        $this->assertCount(1, $rows);
        $this->assertSame(95, $rows[0]['games']['letters']);
        $this->assertSame(35, $rows[0]['games']['colors']);
        $this->assertNull($rows[0]['games']['shapes']);

        $support = $this->actingAs($this->admin)->get('/admin/analytics')->viewData('needsSupport');
        $this->assertSame(35, $support[0]['games']['colors']);
    }

    public function test_total_points_are_summed_per_student_and_per_game()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');

        // awardPoints writes a running per-game balance, so the grand total must
        // come from points_earned rather than a sum of total_points.
        Point::awardPoints($student->id, 'letters', 1, 120, 'level_complete');
        Point::awardPoints($student->id, 'letters', 2, 80, 'level_complete');
        Point::awardPoints($student->id, 'shapes', 1, 45, 'level_complete');

        $row = app(AnalyticsService::class)->studentCategoryBreakdown([$student->id])[0];

        $this->assertSame(200, $row['points']['letters']);
        $this->assertSame(45, $row['points']['shapes']);
        $this->assertNull($row['points']['numbers']);
        $this->assertSame(245, $row['total_points']);
        $this->assertSame(3, $row['awards']);
    }

    public function test_students_without_points_report_zero_not_null()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->play($student, 'letters', 90);

        $row = app(AnalyticsService::class)->studentCategoryBreakdown([$student->id])[0];

        $this->assertSame(0, $row['total_points']);
        $this->assertNull($row['points']['letters']);
    }

    public function test_section_rollup_aggregates_points()
    {
        $a = $this->makeStudent('Ana', '1-3 years old');
        $b = $this->makeStudent('Ben', '1-3 years old');
        $c = $this->makeStudent('Cara', '4-5 years old');

        Point::awardPoints($a->id, 'letters', 1, 100, 'level_complete');
        Point::awardPoints($b->id, 'letters', 1, 300, 'level_complete');
        Point::awardPoints($c->id, 'letters', 1, 50, 'level_complete');

        $rows = collect(app(AnalyticsService::class)->sectionScoreBreakdown([$a->id, $b->id, $c->id]))
            ->keyBy('label');

        $this->assertSame(400, $rows['1-3 years old']['total_points']);
        $this->assertSame(200, $rows['1-3 years old']['avg_points']);
        $this->assertSame(50, $rows['4-5 years old']['total_points']);
    }

    public function test_section_rollup_ignores_students_who_earned_nothing_in_the_average()
    {
        $a = $this->makeStudent('Ana', '1-3 years old');
        $b = $this->makeStudent('Ben', '1-3 years old');
        $this->play($a, 'letters', 80);
        $this->play($b, 'letters', 60);

        Point::awardPoints($a->id, 'letters', 1, 400, 'level_complete');

        $rows = collect(app(AnalyticsService::class)->sectionScoreBreakdown([$a->id, $b->id]))
            ->keyBy('label');

        // Ben has no points yet, so the average reflects Ana alone rather than
        // being dragged down by a zero he never played for.
        $this->assertSame(400, $rows['1-3 years old']['avg_points']);
        $this->assertSame(400, $rows['1-3 years old']['total_points']);
    }

    public function test_teacher_page_shows_individual_total_scores()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->play($student, 'letters', 90);
        Point::awardPoints($student->id, 'letters', 1, 1250, 'level_complete');
        Point::awardPoints($student->id, 'colors', 1, 500, 'level_complete');

        $html = $this->actingAs($this->teacher)->get('/teacher/analytics')->getContent();

        $this->assertStringContainsString('Points earned', $html);
        $this->assertStringContainsString('Total pts', $html);
        $this->assertStringContainsString('Avg points', $html);

        // Per-game points and the formatted grand total.
        $this->assertStringContainsString('>1,250</span>', $html);
        $this->assertStringContainsString('>1,750</span>', $html);
    }

    public function test_admin_leaderboards_show_points()
    {
        $student = $this->makeStudent('Ana', '1-3 years old');
        $this->play($student, 'letters', 95);
        Point::awardPoints($student->id, 'letters', 1, 830, 'level_complete');

        $rows = $this->actingAs($this->admin)->get('/admin/analytics')->viewData('topStudents');

        $this->assertSame(830, $rows[0]['total_points']);

        $html = $this->actingAs($this->admin)->get('/admin/analytics')->getContent();
        $this->assertStringContainsString('>830</td>', $html);
    }

    public function test_empty_cohort_produces_no_category_rows()
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/analytics');

        $response->assertOk();
        $this->assertSame([], $response->viewData('categoryScores'));
        $this->assertSame([], $response->viewData('sectionScores'));
    }
}
