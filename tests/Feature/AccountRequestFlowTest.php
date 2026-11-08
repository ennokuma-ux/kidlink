<?php

namespace Tests\Feature;

use App\Models\AccountRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::create([
            'first_name' => ucfirst($role), 'last_name' => 'User', 'email' => $email,
            'password' => bcrypt('password123'), 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_teacher_parent_account_becomes_a_pending_request(): void
    {
        $teacher = $this->user('teacher', 't@x.test');

        $this->actingAs($teacher)->post('/teacher/parents', [
            'first_name' => 'Pam', 'last_name' => 'Parent', 'email' => 'pam@x.test',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['email' => 'pam@x.test']);
        $this->assertDatabaseHas('account_requests', [
            'email' => 'pam@x.test', 'role' => 'parent', 'status' => 'pending', 'requested_by' => $teacher->id,
        ]);
    }

    public function test_teacher_student_account_becomes_a_pending_request_and_admin_approves_it(): void
    {
        $teacher = $this->user('teacher', 't@x.test');
        $admin = $this->user('super_admin', 'a@x.test');
        $parent = $this->user('parent', 'p@x.test');

        $this->actingAs($teacher)->post('/teacher/accounts', [
            'first_name' => 'Kid', 'last_name' => 'Child', 'sex' => 'Male', 'date_of_birth' => '2022-01-01',
            'class_name' => Student::SECTIONS[0], 'teacher_id' => $teacher->id, 'parent_ids' => [$parent->id],
            'create_login' => 'on', 'student_email' => 'kid@x.test',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, Student::count());
        $this->assertDatabaseMissing('users', ['email' => 'kid@x.test']);
        $request = AccountRequest::firstOrFail();

        $this->actingAs($admin)->get('/admin/account-requests')->assertOk()->assertSee('Kid');
        $this->actingAs($admin)->post("/admin/account-requests/{$request->id}/approve")
            ->assertRedirect()->assertSessionHas('generated_credentials');

        $student = Student::firstOrFail();
        $this->assertSame($teacher->id, (int) $student->teacher_id);
        $this->assertSame($parent->id, (int) $student->parent_id);
        $this->assertTrue($student->parents->contains($parent));
        $this->assertDatabaseHas('users', ['email' => 'kid@x.test', 'role' => 'student']);
        $this->assertSame('approved', $request->fresh()->status);
    }

    public function test_student_without_login_is_approved_without_a_user(): void
    {
        $teacher = $this->user('teacher', 't@x.test');
        $admin = $this->user('super_admin', 'a@x.test');

        $this->actingAs($teacher)->post('/teacher/accounts', [
            'first_name' => 'No', 'last_name' => 'Login', 'sex' => 'Female', 'date_of_birth' => '2022-01-01',
            'teacher_id' => $teacher->id,
        ])->assertSessionHasNoErrors();

        $request = AccountRequest::firstOrFail();
        $this->actingAs($admin)->post("/admin/account-requests/{$request->id}/approve")->assertSessionHasNoErrors();

        $this->assertNull(Student::firstOrFail()->user_id);
    }

    public function test_reject_creates_nothing_and_teacher_cannot_approve(): void
    {
        $teacher = $this->user('teacher', 't@x.test');
        $admin = $this->user('super_admin', 'a@x.test');
        $request = AccountRequest::create([
            'requested_by' => $teacher->id, 'role' => 'parent', 'first_name' => 'P', 'last_name' => 'Q',
            'email' => 'pq@x.test', 'status' => 'pending',
        ]);

        $this->actingAs($teacher)->post("/admin/account-requests/{$request->id}/approve")->assertForbidden();

        $this->actingAs($admin)->post("/admin/account-requests/{$request->id}/reject")->assertRedirect();
        $this->assertSame('rejected', $request->fresh()->status);
        $this->assertDatabaseMissing('users', ['email' => 'pq@x.test']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $teacher = $this->user('teacher', 't@x.test');
        $this->user('parent', 'dup@x.test');

        $this->actingAs($teacher)->post('/teacher/parents', [
            'first_name' => 'D', 'last_name' => 'D', 'email' => 'dup@x.test',
        ])->assertSessionHasErrors('email');
        $this->assertSame(0, AccountRequest::count());
    }

    public function test_parent_profile_page_loads(): void
    {
        $parent = $this->user('parent', 'p@x.test');
        $this->actingAs($parent)->get('/parent/profile')->assertOk();
        $this->actingAs($parent)->get('/parent/dashboard')->assertOk();
    }

    public function test_teacher_can_edit_a_parent_linked_to_their_student_but_not_others(): void
    {
        $teacher = $this->user('teacher', 't@x.test');
        $other = $this->user('teacher', 't2@x.test');
        $mine = $this->user('parent', 'mine@x.test');
        $theirs = $this->user('parent', 'theirs@x.test');

        foreach ([[$teacher, $mine], [$other, $theirs]] as $i => [$t, $p]) {
            $student = Student::create([
                'first_name' => 'K'.$i, 'last_name' => 'S', 'sex' => 'Male', 'date_of_birth' => '2022-01-01',
                'class_name' => Student::SECTIONS[0], 'dominant_hand' => 'Right', 'teacher_id' => $t->id,
                'enrolled_date' => now(), 'is_active' => true,
            ]);
            $student->parents()->attach($p->id);
        }

        $this->actingAs($teacher)->get('/teacher/accounts?view=parents')->assertOk()->assertSee('edit-parent-btn');

        $this->actingAs($teacher)->put("/teacher/parents/{$mine->id}", [
            'first_name' => 'New', 'last_name' => 'Name', 'email' => 'mine@x.test', 'contact' => '0917',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $mine->id, 'first_name' => 'New', 'contact' => '0917']);

        $this->actingAs($teacher)->put("/teacher/parents/{$theirs->id}", [
            'first_name' => 'Hack', 'last_name' => 'X', 'email' => 'theirs@x.test',
        ])->assertForbidden();
    }
}
