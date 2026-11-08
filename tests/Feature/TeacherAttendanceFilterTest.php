<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherAttendanceFilterTest extends TestCase
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
    }

    private function makeStudent(string $first, string $last, string $section): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => $last, 'sex' => 'Female',
            'date_of_birth' => now()->subYears(4), 'class_name' => $section,
            'teacher_id' => $this->teacher->id, 'is_active' => true,
        ]);
    }

    /**
     * Names actually rendered as attendance rows. A plain assertSee on the whole
     * page would also match the child-mode dropdown, which lists every student
     * and is deliberately not affected by the attendance filters.
     */
    private function listedNames($response): array
    {
        preg_match('/<tbody.*?>(.*?)<\/tbody>/s', $response->getContent(), $table);
        preg_match_all('/<span class="font-medium text-\[#182438\]">([^<]+)<\/span>/', $table[1] ?? '', $names);

        return $names[1];
    }

    public function test_all_students_are_listed_without_filters()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');
        $this->makeStudent('Ben', 'Cruz', '4-5 years old');

        $response = $this->actingAs($this->teacher)->get('/teacher/attendance');

        $response->assertOk()->assertSee('Ana Reyes')->assertSee('Ben Cruz');
        $this->assertSame(2, $response->viewData('stats')['total']);
    }

    public function test_section_filter_narrows_the_list()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');
        $this->makeStudent('Ben', 'Cruz', '4-5 years old');

        $response = $this->actingAs($this->teacher)
            ->get('/teacher/attendance?section=' . urlencode('1-3 years old'));

        $response->assertOk();
        $this->assertSame(['Ana Reyes'], $this->listedNames($response));
        $this->assertSame(1, $response->viewData('stats')['total']);
    }

    public function test_search_matches_first_or_last_name()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');
        $this->makeStudent('Ben', 'Cruz', '4-5 years old');

        $first = $this->actingAs($this->teacher)->get('/teacher/attendance?search=rey');
        $this->assertSame(['Ana Reyes'], $this->listedNames($first));

        $second = $this->actingAs($this->teacher)->get('/teacher/attendance?search=BEN');
        $this->assertSame(['Ben Cruz'], $this->listedNames($second));
    }

    public function test_search_and_section_combine()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');
        $this->makeStudent('Ana', 'Santos', '4-5 years old');
        $this->makeStudent('Ben', 'Cruz', '4-5 years old');

        $response = $this->actingAs($this->teacher)
            ->get('/teacher/attendance?search=ana&section=' . urlencode('4-5 years old'));

        $response->assertOk();
        $this->assertSame(['Ana Santos'], $this->listedNames($response));
        $this->assertSame(1, $response->viewData('stats')['total']);
    }

    public function test_stats_reflect_the_filter_not_the_whole_class()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');
        $ben = $this->makeStudent('Ben', 'Cruz', '4-5 years old');
        $this->makeStudent('Cara', 'Diaz', '4-5 years old');

        Attendance::create(['student_id' => $ben->id, 'date' => today(), 'status' => 'Present', 'marked_by' => $this->teacher->id]);
        Attendance::create(['student_id' => $this->makeStudent('Dan', 'Lim', '4-5 years old')->id, 'date' => today(), 'status' => 'Absent', 'marked_by' => $this->teacher->id]);
        Cache::flush();

        // Unfiltered: 1 present, 1 absent out of 4.
        $all = $this->actingAs($this->teacher)->get('/teacher/attendance')->viewData('stats');
        $this->assertSame(4, $all['total']);
        $this->assertSame(1, $all['present']);
        $this->assertSame(1, $all['absent']);

        // Filtered to 4-5: Ana (2-3) drops out, so the class drops from 4 to 3.
        // Ben is present and Dan is absent, and both are in 4-5, so those counts hold.
        $filtered = $this->actingAs($this->teacher)
            ->get('/teacher/attendance?section=' . urlencode('4-5 years old'))
            ->viewData('stats');

        $this->assertSame(3, $filtered['total']);
        $this->assertSame(1, $filtered['present']);
        $this->assertSame(1, $filtered['absent']);
    }

    public function test_only_sections_this_teacher_has_are_offered()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');

        $response = $this->actingAs($this->teacher)->get('/teacher/attendance');

        $this->assertSame(['1-3 years old'], $response->viewData('availableSections')->all());
        $response->assertOk()->assertSee('All sections');
    }

    public function test_unknown_section_falls_back_to_all_students()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');

        $response = $this->actingAs($this->teacher)->get('/teacher/attendance?section=Totally-Bogus');

        $response->assertOk()->assertSee('Ana Reyes');
        $this->assertSame('', $response->viewData('section'));
        $this->assertSame(1, $response->viewData('stats')['total']);
    }

    public function test_search_with_no_match_shows_empty_state()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');

        $this->actingAs($this->teacher)->get('/teacher/attendance?search=zzzz')
            ->assertOk()
            ->assertSee('No students match this search or section');
    }

    public function test_saving_keeps_the_active_filters()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');

        $studentId = Student::where('first_name', 'Ana')->value('id');

        $response = $this->actingAs($this->teacher)->post('/teacher/attendance', [
            'date' => today()->toDateString(),
            'attendance' => [$studentId => 'Present'],
            'search' => 'rey',
            'section' => '1-3 years old',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('search=rey', $response->headers->get('Location'));
        $this->assertStringContainsString('section=2-3', urldecode($response->headers->get('Location')));
        $this->assertDatabaseHas('attendance', [
            'student_id' => $studentId,
            'status' => 'Present',
        ]);
    }

    public function test_only_that_teachers_students_are_reachable()
    {
        $this->makeStudent('Ana', 'Reyes', '1-3 years old');

        $other = User::create([
            'first_name' => 'Other', 'last_name' => 'Teacher',
            'email' => 'other@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);
        Student::create([
            'first_name' => 'Hidden', 'last_name' => 'Kid', 'sex' => 'Male',
            'date_of_birth' => now()->subYears(4), 'class_name' => '1-3 years old',
            'teacher_id' => $other->id, 'is_active' => true,
        ]);
        Cache::flush();

        $this->actingAs($this->teacher)->get('/teacher/attendance?search=')
            ->assertOk()
            ->assertSee('Ana Reyes')
            ->assertDontSee('Hidden Kid');
    }
}
