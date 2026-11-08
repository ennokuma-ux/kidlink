<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ArchiveAccountTest extends TestCase
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

    private function makeStudent(User $teacher, string $email = 'kid@test.local'): array
    {
        $user = $this->makeUser('student', $email);

        $student = Student::create([
            'first_name' => 'Kid', 'last_name' => 'One', 'sex' => 'Male',
            'date_of_birth' => '2020-01-01', 'class_name' => '1-3 years old',
            'dominant_hand' => 'Right', 'teacher_id' => $teacher->id,
            'user_id' => $user->id, 'enrolled_date' => now(), 'is_active' => true,
        ]);

        return [$student, $user];
    }

    private function logOut()
    {
        return $this->post('/logout');
    }

    private function logIn(string $email)
    {
        return $this->post('/login', ['email' => $email, 'password' => 'password123']);
    }

    public function test_delete_routes_are_gone()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        [$student] = $this->makeStudent($teacher);

        $this->actingAs($teacher)
            ->delete("/teacher/accounts/{$student->id}")
            ->assertStatus(405);

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }

    public function test_accounts_page_has_no_delete_controls()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $this->makeStudent($teacher);

        $response = $this->actingAs($teacher)->get('/teacher/accounts');

        $response->assertOk();
        $response->assertDontSee('Delete this student', false);
        $this->assertStringNotContainsString("@method('DELETE')", $response->getContent());
        $this->assertStringContainsString('viewArchivedBtn', $response->getContent());
    }

    public function test_archiving_student_archives_linked_login_and_hides_from_active_list()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        [$student, $user] = $this->makeStudent($teacher);

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}")
            ->assertRedirect('/teacher/accounts');

        $this->assertFalse($student->fresh()->is_active);
        $this->assertFalse($user->fresh()->is_active, 'linked login must be archived too');

        $this->logOut();
        $this->logIn('kid@test.local')->assertSessionHasErrors('email');
        $this->assertGuest();

        $page = $this->actingAs($teacher)->get('/teacher/accounts');
        $page->assertOk();
        $this->assertStringContainsString('Archived Students', $page->getContent());
    }

    public function test_archived_student_with_live_session_is_locked_out()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        [$student, $user] = $this->makeStudent($teacher);

        // The student signs in, then the teacher archives them mid-session.
        $this->logIn('kid@test.local')->assertRedirect('/student/home');
        $this->assertAuthenticatedAs($user);

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}");

        // Same browser, still holding the pre-archive session.
        $this->actingAs($user->fresh());
        $this->get('/student/home')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_restoring_student_lets_them_back_in()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        [$student, $user] = $this->makeStudent($teacher);

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}");
        $this->assertFalse($student->fresh()->is_active);

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}")
            ->assertRedirect('/teacher/accounts');

        $this->assertTrue($student->fresh()->is_active);
        $this->assertTrue($user->fresh()->is_active);

        $this->logOut();
        $this->logIn('kid@test.local')->assertRedirect('/student/home');
    }

    public function test_archiving_parent_blocks_login_and_restores_it()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $parent = $this->makeUser('parent', 'parent@test.local');

        $this->actingAs($teacher)->put("/teacher/accounts/parent/{$parent->id}")
            ->assertRedirect('/teacher/accounts');
        $this->assertFalse($parent->fresh()->is_active);

        $this->logOut();
        $this->logIn('parent@test.local')->assertSessionHasErrors('email');

        $this->actingAs($teacher)->put("/teacher/accounts/parent/{$parent->id}");
        $this->logOut();
        $this->assertTrue($parent->fresh()->is_active);

        $this->logIn('parent@test.local')->assertRedirect('/parent/dashboard');
    }

    public function test_parent_toggle_cannot_target_non_parent_roles()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $this->actingAs($teacher)
            ->put("/teacher/accounts/parent/{$teacher->id}")
            ->assertStatus(404);
        $this->assertTrue($teacher->fresh()->is_active);
    }

    public function test_teacher_cannot_archive_another_teachers_student()
    {
        $owner = $this->makeUser('teacher', 'teacher@test.local');
        $other = $this->makeUser('teacher', 'other@test.local');
        [$student] = $this->makeStudent($owner);

        $this->actingAs($other)->put("/teacher/accounts/{$student->id}")
            ->assertStatus(403);
        $this->assertTrue($student->fresh()->is_active);
    }

    public function test_archived_student_disappears_from_teacher_lists()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        [$student] = $this->makeStudent($teacher);

        $this->actingAs($teacher)->put("/teacher/accounts/{$student->id}");

        $dashboard = $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk();
        $this->assertStringNotContainsString('Kid One', $dashboard->getContent());

        $attendance = $this->actingAs($teacher)->get('/teacher/attendance')->assertOk();
        $this->assertStringNotContainsString('Kid One', $attendance->getContent());
    }

    public function test_admin_delete_route_is_gone()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $parent = $this->makeUser('parent', 'parent@test.local');

        $this->actingAs($admin)
            ->delete("/admin/users/{$parent->id}")
            ->assertStatus(405);

        $this->assertDatabaseHas('users', ['id' => $parent->id]);
    }

    public function test_admin_users_page_has_no_delete_controls()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $this->makeUser('parent', 'parent@test.local');

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $this->assertStringNotContainsString("@method('DELETE')", $response->getContent());
        $this->assertStringNotContainsString('admin.users.destroy', $response->getContent());
        $this->assertStringContainsString('viewArchivedBtn', $response->getContent());
        $this->assertStringContainsString('Archived Accounts', $response->getContent());
    }

    public function test_admin_archive_blocks_login_and_restore_lets_them_back_in()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $parent = $this->makeUser('parent', 'parent@test.local');

        $this->actingAs($admin)->put("/admin/users/{$parent->id}/archive")
            ->assertRedirect('/admin/users');
        $this->assertFalse($parent->fresh()->is_active);

        $this->logOut();
        $this->logIn('parent@test.local')->assertSessionHasErrors('email');

        $this->actingAs($admin)->put("/admin/users/{$parent->id}/archive");
        $this->logOut();
        $this->assertTrue($parent->fresh()->is_active);

        $this->logIn('parent@test.local')->assertRedirect('/parent/dashboard');
    }

    public function test_admin_archive_keeps_linked_student_record_in_step()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        [$student, $user] = $this->makeStudent($teacher);

        $this->actingAs($admin)->put("/admin/users/{$user->id}/archive");

        $this->assertFalse($user->fresh()->is_active);
        $this->assertFalse($student->fresh()->is_active, 'linked student record must archive too');

        $this->actingAs($admin)->put("/admin/users/{$user->id}/archive");
        $this->assertTrue($user->fresh()->is_active);
        $this->assertTrue($student->fresh()->is_active);
    }

    public function test_admin_cannot_archive_self_or_super_admins()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $other = $this->makeUser('super_admin', 'root@test.local');

        $this->actingAs($admin)->put("/admin/users/{$admin->id}/archive");
        $this->assertTrue($admin->fresh()->is_active);

        $this->actingAs($admin)->put("/admin/users/{$other->id}/archive");
        $this->assertTrue($other->fresh()->is_active);
    }
}
