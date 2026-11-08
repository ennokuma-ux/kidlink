<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_two_sections_are_defined()
    {
        $this->assertSame(['1-3 years old', '4-5 years old'], Student::SECTIONS);
    }

    private function makeUser(string $role, string $email): User
    {
        return User::create([
            'first_name' => 'Test', 'last_name' => $role,
            'email' => $email, 'password' => Hash::make('password123'),
            'role' => $role, 'is_active' => true,
        ]);
    }

    public static function invalidSections(): array
    {
        return [
            ['3-4 years old'],
            ['5-6 years old'],
            ['4 years old'],
            ['nursery'],
            ['4-5'],
        ];
    }

    #[DataProvider('invalidSections')]
    public function test_admin_cannot_save_a_section_outside_the_list(string $section)
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => $section,
        ])->assertSessionHasErrors('class_name');

        $this->assertDatabaseMissing('students', ['class_name' => $section]);
    }

    public function test_admin_can_save_either_valid_section()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        foreach (Student::SECTIONS as $index => $section) {
            $this->actingAs($admin)->post('/admin/users', [
                'first_name' => 'Mia', 'last_name' => 'Reyes' . $index,
                'email' => "mia{$index}@test.local", 'role' => 'student',
                'class_name' => $section,
            ])->assertSessionHasNoErrors();
        }

        foreach (Student::SECTIONS as $section) {
            $this->assertDatabaseHas('students', ['class_name' => $section]);
        }
    }

    public function test_admin_edit_rejects_a_section_outside_the_list()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
        ])->assertSessionHasNoErrors();

        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'mia@test.local'))->first();

        $this->actingAs($admin)->put("/admin/users/{$student->user_id}", [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'is_active' => '1',
            'class_name' => 'Toddler',
        ])->assertSessionHasErrors('class_name');
    }

    public function test_teacher_can_only_pick_from_the_section_list()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $this->actingAs($teacher)->post('/teacher/accounts', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'sex' => 'Female', 'date_of_birth' => '2021-01-01',
            'class_name' => '5-6 years old',
        ])->assertSessionHasErrors('class_name');

        $this->assertDatabaseMissing('students', ['class_name' => '5-6 years old']);
    }

    public function test_both_sections_are_offered_in_the_forms()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $html = $this->actingAs($admin)->get('/admin/users')->getContent();

        foreach (Student::SECTIONS as $section) {
            $this->assertStringContainsString('<option value="' . $section . '">', $html);
        }
        // The retired "3-4" band must not linger in the markup.
        $this->assertStringNotContainsString('>3-4 years old<', $html);
        $this->assertStringNotContainsString('>5-6 years old<', $html);
    }
}
