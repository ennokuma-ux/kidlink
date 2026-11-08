<?php

namespace Tests\Feature;

use App\Models\EccdRecord;
use App\Models\GameScore;
use App\Models\Student;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnalyticsSectionFilterTest extends TestCase
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

    private function makeStudent(string $first, string $last, string $section, int $accuracy, ?User $teacher = null): Student
    {
        $student = Student::create([
            'first_name' => $first, 'last_name' => $last, 'sex' => 'Female',
            'date_of_birth' => Student::dateOfBirthForSection($section),
            'class_name' => $section, 'teacher_id' => ($teacher ?? $this->teacher)->id,
            'is_active' => true, 'enrolled_date' => now()->subMonths(2),
        ]);

        GameScore::create([
            'student_id' => $student->id, 'game_type' => 'letters', 'level' => 1,
            'score' => $accuracy, 'total' => 100, 'mistakes' => 0,
            // The game controllers always persist this alongside the raw score.
            'percentage' => $accuracy,
        ]);

        // Below 50% on one domain is what puts a student on the intervention list.
        EccdRecord::create([
            'student_id' => $student->id, 'domain' => 'Fine Motor',
            'indicator' => 'threads beads', 'status' => 'Not Yet',
            'assessed_by' => ($teacher ?? $this->teacher)->id, 'assessed_at' => now(),
        ]);

        return $student;
    }

    public function test_each_section_has_its_own_colour_and_unknown_values_stay_neutral()
    {
        $colors = array_values(Student::SECTION_COLORS);

        $this->assertSame(array_keys(Student::SECTION_COLORS), Student::SECTIONS);
        $this->assertCount(count($colors), array_unique($colors), 'every section needs a distinct colour');

        foreach (Student::SECTIONS as $section) {
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', Student::colorForSection($section));
        }

        $this->assertSame(Student::UNASSIGNED_COLOR, Student::colorForSection(null));
        $this->assertSame(Student::UNASSIGNED_COLOR, Student::colorForSection('Toddlers'));
        $this->assertSame(Student::UNASSIGNED_SECTION, Student::sectionLabel(null));
    }

    public function test_teacher_analytics_offers_the_filter_controls_and_coloured_legend()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics');

        $response->assertOk()
            ->assertSee('name="section"', false)
            ->assertSee('name="sort"', false)
            ->assertSee('1-3 years old')
            ->assertSee('4-5 years old')
            ->assertSee('background-color: ' . Student::colorForSection('1-3 years old'), false)
            ->assertSee('background-color: ' . Student::colorForSection('4-5 years old'), false);
    }

    public function test_teacher_section_filter_narrows_the_whole_page()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $all = $this->actingAs($this->teacher)->get('/teacher/analytics');
        $filtered = $this->actingAs($this->teacher)->get('/teacher/analytics?section=' . urlencode('1-3 years old'));

        $this->assertSame(2, $all->viewData('summary')['students']);
        $this->assertSame(1, $filtered->viewData('summary')['students']);

        // The age histogram only counts the filtered cohort.
        $this->assertSame(2, array_sum($all->viewData('ageDistribution')));
        $this->assertSame(1, array_sum($filtered->viewData('ageDistribution')));

        $this->assertSame('1-3 years old', $filtered->viewData('section'));
        $this->assertSame(2, $filtered->viewData('totalStudents'));
    }

    public function test_teacher_only_sections_they_actually_have_are_offered()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics');

        $this->assertSame(['1-3 years old'], $response->viewData('availableSections'));
    }

    public function test_unknown_section_falls_back_to_every_student()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics?section=nursery');

        $response->assertOk();
        $this->assertSame('', $response->viewData('section'));
        $this->assertSame(2, $response->viewData('summary')['students']);
    }

    public function test_teacher_students_can_be_sorted_by_section_then_name()
    {
        // Deliberately inserted out of order so the sort has to do the work.
        $this->makeStudent('Zoe', 'Young', '4-5 years old', 40);
        $this->makeStudent('Cara', 'Young', '1-3 years old', 40);
        $this->makeStudent('Ana', 'Young', '1-3 years old', 40);

        $bySection = $this->actingAs($this->teacher)->get('/teacher/analytics?sort=section');
        $byName = $this->actingAs($this->teacher)->get('/teacher/analytics?sort=name');
        $byPerformance = $this->actingAs($this->teacher)->get('/teacher/analytics?sort=performance');

        $names = fn ($response) => array_column($response->viewData('recommendations'), 'name');

        // Canonical section order first, so both 2-3 names lead, name A-Z inside.
        $this->assertSame(['Ana Young', 'Cara Young', 'Zoe Young'], $names($bySection));

        // Pure alphabetical ignores section grouping.
        $this->assertSame(['Ana Young', 'Cara Young', 'Zoe Young'], $names($byName));

        $this->assertSame('performance', $byPerformance->viewData('sort'));
        $this->assertNotEmpty($names($byPerformance));
    }

    public function test_legend_counts_every_section_even_while_filtering()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $filtered = $this->actingAs($this->teacher)->get('/teacher/analytics?section=' . urlencode('1-3 years old'));

        $legend = collect($filtered->viewData('sectionLegend'))->keyBy('label');

        $this->assertSame(1, $legend['1-3 years old']['total']);
        $this->assertSame(1, $legend['4-5 years old']['total']);
        $this->assertSame(Student::colorForSection('4-5 years old'), $legend['4-5 years old']['color']);
    }

    public function test_unassigned_students_get_the_neutral_colour_and_sort_last()
    {
        $legacy = Student::create([
            'first_name' => 'Old', 'last_name' => 'Row', 'sex' => 'Female',
            'date_of_birth' => now()->subYears(4), 'class_name' => null,
            'teacher_id' => $this->teacher->id, 'is_active' => true, 'enrolled_date' => now(),
        ]);

        GameScore::create([
            'student_id' => $legacy->id, 'game_type' => 'letters', 'level' => 1,
            'score' => 40, 'total' => 100, 'mistakes' => 0,
        ]);
        EccdRecord::create([
            'student_id' => $legacy->id, 'domain' => 'Fine Motor',
            'indicator' => 'threads beads', 'status' => 'Not Yet',
            'assessed_by' => $this->teacher->id, 'assessed_at' => now(),
        ]);

        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics');

        $legend = collect($response->viewData('sectionLegend'))->keyBy('label');

        $this->assertTrue($legend->has(Student::UNASSIGNED_SECTION));
        $this->assertSame(Student::UNASSIGNED_COLOR, $legend[Student::UNASSIGNED_SECTION]['color']);

        // Unassigned sinks to the end of a section sort.
        $names = array_column($response->viewData('recommendations'), 'name');
        $this->assertSame('Old Row', end($names));
    }

    public function test_admin_analytics_offers_the_filter_controls()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);

        $response = $this->actingAs($this->admin)->get('/admin/analytics');

        $response->assertOk()
            ->assertSee('name="section"', false)
            ->assertSee('name="sort"', false)
            ->assertSee('1-3 years old');
    }

    public function test_admin_section_filter_narrows_the_student_tables()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 90);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $all = $this->actingAs($this->admin)->get('/admin/analytics');
        $filtered = $this->actingAs($this->admin)->get('/admin/analytics?section=' . urlencode('4-5 years old'));

        $this->assertCount(2, $all->viewData('topStudents'));
        $this->assertSame(['Ben Cruz'], array_column($filtered->viewData('topStudents'), 'name'));
        $this->assertSame(['Ben Cruz'], array_column($filtered->viewData('needsSupport'), 'name'));

        // The "students per class" breakdown compares classes, so the section
        // filter must not hide the other class from it.
        $sections = collect($filtered->viewData('classDistribution'))->keyBy('class_name');
        $this->assertCount(2, $sections);
        $this->assertSame(1, $sections['1-3 years old']['total']);
        $this->assertSame(1, $sections['4-5 years old']['total']);
    }

    public function test_admin_top_students_are_grouped_by_section()
    {
        $this->makeStudent('Zoe', 'Young', '4-5 years old', 95);
        $this->makeStudent('Ana', 'Young', '1-3 years old', 60);

        $response = $this->actingAs($this->admin)->get('/admin/analytics?sort=section');

        $this->assertSame(['Ana Young', 'Zoe Young'], array_column($response->viewData('topStudents'), 'name'));
    }

    public function test_admin_sort_by_name_ignores_section_grouping()
    {
        $this->makeStudent('Zoe', 'Young', '4-5 years old', 95);
        $this->makeStudent('Ana', 'Young', '1-3 years old', 60);

        $response = $this->actingAs($this->admin)->get('/admin/analytics?sort=name');

        $this->assertSame(['Ana Young', 'Zoe Young'], array_column($response->viewData('topStudents'), 'name'));
    }

    public function test_admin_class_breakdown_carries_section_colours()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $rows = collect($this->actingAs($this->admin)->get('/admin/analytics')->viewData('classDistribution'))
            ->keyBy('class_name');

        $this->assertSame(Student::colorForSection('1-3 years old'), $rows['1-3 years old']['color']);
        $this->assertSame(Student::colorForSection('4-5 years old'), $rows['4-5 years old']['color']);
        $this->assertNotSame($rows['1-3 years old']['color'], $rows['4-5 years old']['color']);
    }

    public function test_admin_never_reveals_a_section_from_another_teacher_as_its_own()
    {
        $other = User::create([
            'first_name' => 'Other', 'last_name' => 'Teacher',
            'email' => 'other@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40, $other);

        // The teacher only has one section, so the other one is not selectable.
        $this->assertSame(
            ['1-3 years old'],
            $this->actingAs($this->teacher)->get('/teacher/analytics')->viewData('availableSections')
        );

        // Asking for the section they do not have must not leak those students.
        $response = $this->actingAs($this->teacher)->get('/teacher/analytics?section=' . urlencode('4-5 years old'));
        $this->assertSame('', $response->viewData('section'));
        $this->assertSame(1, $response->viewData('summary')['students']);
    }

    public function test_service_sort_helper_leaves_performance_ranking_alone()
    {
        $rows = [
            ['student_id' => 1, 'name' => 'Zoe', 'class_name' => '4-5 years old'],
            ['student_id' => 2, 'name' => 'Ana', 'class_name' => '1-3 years old'],
        ];

        $service = app(AnalyticsService::class);

        $this->assertSame(
            [2, 1],
            array_column($service->sortStudentRows($rows, 'section'), 'student_id')
        );
        $this->assertSame(
            [1, 2],
            array_column($service->sortStudentRows($rows, 'performance'), 'student_id')
        );
    }

    public function test_teacher_analytics_offers_a_student_filter()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);

        $this->actingAs($this->teacher)->get('/teacher/analytics')
            ->assertOk()
            ->assertSee('name="student"', false)
            ->assertSee('All students')
            ->assertSee('Ana Reyes');
    }

    public function test_teacher_student_filter_narrows_every_figure_to_that_child()
    {
        $ana = $this->makeStudent('Ana', 'Reyes', '1-3 years old', 90);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics?student=' . $ana->id);

        $response->assertOk();
        $this->assertSame($ana->id, $response->viewData('selectedStudent')->id);
        $this->assertSame(1, $response->viewData('summary')['students']);

        // Only the chosen child's game accuracy shows up.
        $this->assertSame(90, $response->viewData('summary')['avg_game_pct']);
        $this->assertSame(1, $response->viewData('summary')['games_played']);

        // And the age histogram no longer counts the rest of the class.
        $this->assertSame(1, array_sum($response->viewData('ageDistribution')));

        $this->assertSame(['Ana Reyes'], array_column($response->viewData('recommendations'), 'name'));
    }

    public function test_teacher_individual_view_hides_class_wide_blocks()
    {
        $ana = $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $response = $this->actingAs($this->teacher)->get('/teacher/analytics?student=' . $ana->id);

        $response->assertOk()
            ->assertSee('Ana Reyes')
            // Class comparisons have no place on one child's page.
            ->assertDontSee('Average Score by Section');

        $this->assertSame([], $response->viewData('sectionScores'));
    }

    public function test_attendance_period_can_be_switched_to_a_month_or_another_week()
    {
        $ana = $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);

        $week = $this->actingAs($this->teacher)->get('/teacher/analytics?student=' . $ana->id . '&att_view=week&att_date=2026-03-11');
        $week->assertOk()->assertSee('name="att_view"', false);
        $this->assertSame('week', $week->viewData('attendance')['view']);
        $this->assertCount(7, $week->viewData('weeklyBreakdown'));
        $this->assertSame('2026-03-09', $week->viewData('weeklyBreakdown')[0]['date']);

        $month = $this->actingAs($this->teacher)->get('/teacher/analytics?student=' . $ana->id . '&att_view=month&att_date=2026-02-10');
        $this->assertSame('month', $month->viewData('attendance')['view']);
        $this->assertCount(28, $month->viewData('weeklyBreakdown'));

        // Garbage falls back to the current week.
        $bad = $this->actingAs($this->teacher)->get('/teacher/analytics?student=' . $ana->id . '&att_view=year&att_date=nope');
        $this->assertSame('week', $bad->viewData('attendance')['view']);
        $this->assertCount(7, $bad->viewData('weeklyBreakdown'));
    }

    public function test_teacher_cannot_scope_analytics_to_another_teachers_student()
    {
        $other = User::create([
            'first_name' => 'Other', 'last_name' => 'Teacher',
            'email' => 'other@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $notMine = $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40, $other);

        $response = $this->actingAs($this->teacher)
            ->get('/teacher/analytics?student=' . $notMine->id);

        $response->assertOk();
        // The id is ignored rather than honoured, so the cohort view stays.
        $this->assertNull($response->viewData('selectedStudent'));
        $this->assertSame(1, $response->viewData('summary')['students']);
    }

    public function test_teacher_student_filter_composes_with_the_section_filter()
    {
        $ana = $this->makeStudent('Ana', 'Reyes', '1-3 years old', 40);
        $ben = $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        // Ben lives in the other section, so pairing him with it must not match.
        $mismatch = $this->actingAs($this->teacher)->get(
            '/teacher/analytics?section=' . urlencode('1-3 years old') . '&student=' . $ben->id
        );
        $this->assertNull($mismatch->viewData('selectedStudent'));

        // The matching pair resolves normally.
        $match = $this->actingAs($this->teacher)->get(
            '/teacher/analytics?section=' . urlencode('1-3 years old') . '&student=' . $ana->id
        );
        $this->assertSame($ana->id, $match->viewData('selectedStudent')->id);
    }

    public function test_archived_students_cannot_be_selected()
    {
        $archived = $this->makeStudent('Old', 'Row', '1-3 years old', 40);
        $archived->update(['is_active' => false]);

        $response = $this->actingAs($this->teacher)
            ->get('/teacher/analytics?student=' . $archived->id);

        $this->assertNull($response->viewData('selectedStudent'));
    }

    public function test_admin_analytics_scopes_to_a_single_student()
    {
        $ana = $this->makeStudent('Ana', 'Reyes', '1-3 years old', 90);
        $this->makeStudent('Ben', 'Cruz', '4-5 years old', 40);

        $response = $this->actingAs($this->admin)->get('/admin/analytics?student=' . $ana->id);

        $response->assertOk()
            ->assertSee('name="student"', false)
            ->assertSee('Every figure on this page is for');

        $this->assertSame($ana->id, $response->viewData('selectedStudent')->id);
        $this->assertSame(1, $response->viewData('summary')['students']);

        // ...but "students per class" stays on every class, so inspecting one
        // child does not shrink the whole centre's breakdown to a single slice.
        $sections = collect($response->viewData('classDistribution'))->keyBy('class_name');
        $this->assertCount(2, $sections);
        $this->assertSame(1, $sections['1-3 years old']['total']);
        $this->assertSame(1, $sections['4-5 years old']['total']);
    }

    public function test_admin_cannot_scope_analytics_to_an_unknown_or_archived_student()
    {
        $archived = $this->makeStudent('Old', 'Row', '1-3 years old', 90);
        $archived->update(['is_active' => false]);

        foreach ([$archived->id, 999999] as $id) {
            $response = $this->actingAs($this->admin)->get('/admin/analytics?student=' . $id);

            $response->assertOk();
            $this->assertNull($response->viewData('selectedStudent'));
            $this->assertSame(1, $response->viewData('summary')['students']);
        }
    }
}
