<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * A child's handedness is recorded at account creation and shown on every
 * screen that already lists the child's age and class. "Not yet established"
 * is the neutral fourth option: a toddler's dominant hand is often still
 * undecided, so leaving the field alone must never silently mark them
 * right-handed.
 */
class DominantHandTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email): User
    {
        return User::create([
            'first_name' => 'Test', 'last_name' => $role,
            'email' => $email, 'password' => Hash::make('password123'),
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function makeStudent(User $teacher, string $hand, bool $active = true): Student
    {
        return Student::create([
            'first_name' => 'Kid', 'last_name' => 'Hand', 'sex' => 'Male',
            'date_of_birth' => '2022-01-01', 'class_name' => '1-3 years old',
            'dominant_hand' => $hand,
            'teacher_id' => $teacher->id,
            'enrolled_date' => now(), 'is_active' => $active,
        ]);
    }

    public function test_four_handedness_options_are_defined(): void
    {
        $this->assertSame(
            ['Right', 'Left', 'Both', 'Not yet established'],
            Student::HANDS
        );
    }

    public static function validHands(): array
    {
        return array_map(fn (string $hand) => [$hand], Student::HANDS);
    }

    public static function invalidHands(): array
    {
        return [
            ['Right-handed'],
            ['ambidextrous'],
            ['Middle'],
            ['left'],
            ['NONE'],
        ];
    }

    // ------------------------------------------------------- teacher: create

    #[DataProvider('validHands')]
    public function test_teacher_can_save_each_handedness(string $hand): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $this->actingAs($teacher)->post('/teacher/accounts', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'sex' => 'Female', 'date_of_birth' => '2021-01-01',
            'dominant_hand' => $hand,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', [
            'last_name' => 'Reyes', 'dominant_hand' => $hand,
        ]);
    }

    public function test_teacher_store_defaults_to_not_yet_established_when_the_field_is_omitted(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $this->actingAs($teacher)->post('/teacher/accounts', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'sex' => 'Female', 'date_of_birth' => '2021-01-01',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', [
            'last_name' => 'Reyes', 'dominant_hand' => 'Not yet established',
        ]);
    }

    #[DataProvider('invalidHands')]
    public function test_teacher_store_rejects_a_hand_outside_the_list(string $hand): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $this->actingAs($teacher)->post('/teacher/accounts', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'sex' => 'Female', 'date_of_birth' => '2021-01-01',
            'dominant_hand' => $hand,
        ])->assertSessionHasErrors('dominant_hand');

        $this->assertDatabaseMissing('students', ['dominant_hand' => $hand]);
    }

    // -------------------------------------------------------- teacher: edit

    public function test_teacher_edit_can_change_the_handedness(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Right');

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}/update", [
            'first_name' => 'Kid', 'last_name' => 'Hand', 'sex' => 'Male',
            'date_of_birth' => '2022-01-01', 'dominant_hand' => 'Left',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'dominant_hand' => 'Left']);
    }

    public function test_teacher_edit_keeps_the_handedness_when_the_field_is_not_sent(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Both');

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}/update", [
            'first_name' => 'Kid', 'last_name' => 'Hand', 'sex' => 'Male',
            'date_of_birth' => '2022-01-01',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'dominant_hand' => 'Both']);
    }

    public function test_teacher_edit_rejects_a_hand_outside_the_list(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Right');

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}/update", [
            'first_name' => 'Kid', 'last_name' => 'Hand', 'sex' => 'Male',
            'date_of_birth' => '2022-01-01', 'dominant_hand' => 'Middle',
        ])->assertSessionHasErrors('dominant_hand');

        $this->assertDatabaseHas('students', ['id' => $student->id, 'dominant_hand' => 'Right']);
    }

    // --------------------------------------------------------- admin: create

    #[DataProvider('validHands')]
    public function test_admin_can_save_each_handedness(string $hand): void
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
            'dominant_hand' => $hand,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', ['dominant_hand' => $hand]);
    }

    public function test_admin_store_defaults_to_not_yet_established_when_the_field_is_omitted(): void
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', ['dominant_hand' => 'Not yet established']);
    }

    #[DataProvider('invalidHands')]
    public function test_admin_store_rejects_a_hand_outside_the_list(string $hand): void
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
            'dominant_hand' => $hand,
        ])->assertSessionHasErrors('dominant_hand');

        $this->assertDatabaseMissing('students', ['dominant_hand' => $hand]);
    }

    // ---------------------------------------------------------- admin: edit

    public function test_admin_edit_can_change_the_handedness(): void
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
            'dominant_hand' => 'Right',
        ])->assertSessionHasNoErrors();

        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'mia@test.local'))->firstOrFail();

        $this->actingAs($admin)->put("/admin/users/{$student->user_id}", [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'is_active' => '1',
            'dominant_hand' => 'Both',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'dominant_hand' => 'Both']);
    }

    public function test_admin_edit_rejects_a_hand_outside_the_list(): void
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
            'dominant_hand' => 'Right',
        ])->assertSessionHasNoErrors();

        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'mia@test.local'))->firstOrFail();

        $this->actingAs($admin)->put("/admin/users/{$student->user_id}", [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'is_active' => '1',
            'dominant_hand' => 'ambidextrous',
        ])->assertSessionHasErrors('dominant_hand');

        $this->assertDatabaseHas('students', ['id' => $student->id, 'dominant_hand' => 'Right']);
    }

    // ------------------------------------------------------------- display

    public function test_all_four_options_are_offered_on_both_account_forms(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $teacherHtml = $this->actingAs($teacher)->get('/teacher/accounts')->assertOk()->getContent();
        $adminHtml = $this->actingAs($admin)->get('/admin/users')->assertOk()->getContent();

        foreach (Student::HANDS as $hand) {
            $option = '<option value="' . $hand . '"';
            $this->assertStringContainsString($option, $teacherHtml, "The teacher form is missing the {$hand} option.");
            $this->assertStringContainsString($option, $adminHtml, "The admin form is missing the {$hand} option.");
        }
    }

    public function test_the_teacher_account_tables_show_every_handedness(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        foreach (Student::HANDS as $hand) {
            $this->makeStudent($teacher, $hand);
        }
        $this->makeStudent($teacher, 'Right', active: false);

        $html = $this->actingAs($teacher)->get('/teacher/accounts')->assertOk()->getContent();

        foreach (Student::HANDS as $hand) {
            // The table cell, not merely an <option> in one of the modals.
            $cell = '<td class="px-6 py-4 text-[#5f6873]">' . $hand . '</td>';
            $this->assertStringContainsString($cell, $html, "The table has no cell showing {$hand}.");
        }

        // Header of the active table, header of the archived table, and the
        // label of each of the two modals.
        $this->assertGreaterThanOrEqual(
            4,
            substr_count($html, 'Handedness'),
            'Handedness should be labelled on both tables and both forms.'
        );
    }

    public function test_the_student_home_page_shows_the_handedness(): void
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Left');

        $this->actingAs($teacher)->post('/child-mode/enter/' . $student->id);

        $html = $this->get('/student/home')->assertOk()->getContent();

        $this->assertStringContainsString(
            '| Handedness Left',
            $html,
            'The welcome line should include the handedness next to age and class.'
        );
    }

    public function test_the_parent_dashboard_shows_the_handedness(): void
    {
        $parent = $this->makeUser('parent', 'parent@test.local');
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Not yet established');
        $student->parents()->attach($parent->id);

        $html = $this->actingAs($parent)->get('/parent/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString(
            '| Handedness Not yet established',
            $html,
            'The parent view should include the handedness next to age and class.'
        );
    }
}
