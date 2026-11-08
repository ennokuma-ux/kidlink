<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateStudentAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'first_name' => 'Admin', 'last_name' => 'User',
            'email' => 'admin@test.local', 'password' => Hash::make('password123'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    public function test_student_is_saved_with_the_chosen_section()
    {
        $response = $this->actingAs($this->admin())->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
        ]);

        $response->assertRedirect('/admin/users')->assertSessionHasNoErrors();

        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'mia@test.local'))->first();

        $this->assertNotNull($student);
        $this->assertSame('4-5 years old', $student->class_name);
    }

    public function test_student_date_of_birth_matches_the_chosen_section()
    {
        $admin = $this->admin();

        foreach (Student::SECTIONS as $section) {
            $email = explode(' ', $section)[0] . '@test.local';

            $this->actingAs($admin)->post('/admin/users', [
                'first_name' => 'Kid', 'last_name' => 'Test',
                'email' => $email, 'role' => 'student',
                'class_name' => $section,
            ])->assertSessionHasNoErrors();

            $student = Student::whereHas('user', fn ($q) => $q->where('email', $email))->first();

            preg_match('/(\d+)\s*-\s*(\d+)/', $section, $range);
            $age = $student->date_of_birth->age;

            $this->assertGreaterThanOrEqual((int) $range[1], $age, "{$section} child is too young");
            $this->assertLessThanOrEqual((int) $range[2], $age, "{$section} child is too old");
        }
    }

    public function test_student_needs_no_password_but_one_is_generated()
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '1-3 years old',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'mia@test.local')->first();

        $this->assertNotNull($user->password);
        $this->assertSame(
            ['email' => 'mia@test.local'],
            ['email' => session('generated_credentials')['email']]
        );
        $this->assertTrue(Hash::check(session('generated_credentials')['password'], $user->password));
    }

    public function test_generated_password_is_returned_once()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '1-3 years old',
        ]);

        $shown = session('generated_credentials')['password'];

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSee('mia@test.local')
            ->assertSee($shown);

        // Reloading the page (the flash is consumed) must not leak the password.
        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertDontSee($shown);
    }

    public function test_student_section_is_required()
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
        ])->assertSessionHasErrors('class_name');

        $this->assertDatabaseCount('users', 1); // only the admin
    }

    public function test_non_student_roles_still_require_a_password()
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'first_name' => 'Ana', 'last_name' => 'Cruz',
            'email' => 'ana@test.local', 'role' => 'parent',
        ])->assertSessionHasErrors('password');
    }

    public function test_non_student_accounts_do_not_show_generated_credentials()
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'first_name' => 'Ana', 'last_name' => 'Cruz',
            'email' => 'ana@test.local', 'role' => 'parent',
            'password' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertNull(session('generated_credentials'));
    }

    public function test_create_form_swaps_password_for_section_when_student_is_selected()
    {
        $response = $this->actingAs($this->admin())->get('/admin/users');

        $response->assertOk();
        $response->assertSee('addStudentClassSection', false);
        $response->assertSee('Section / Class');
        $response->assertSee('addPasswordField', false);
    }
}
