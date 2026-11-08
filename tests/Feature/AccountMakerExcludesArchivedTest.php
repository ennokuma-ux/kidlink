<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountMakerExcludesArchivedTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email, bool $active = true): User
    {
        return User::create([
            'first_name' => 'Test', 'last_name' => $role,
            'email' => $email, 'password' => Hash::make('password123'),
            'role' => $role, 'is_active' => $active,
        ]);
    }

    public function test_account_maker_pickers_exclude_archived_parents_and_teachers()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $activeParent = $this->makeUser('parent', 'active.parent@test.local');
        $archivedParent = $this->makeUser('parent', 'archived.parent@test.local', false);
        $activeTeacher = $this->makeUser('teacher', 'active.teacher@test.local');
        $archivedTeacher = $this->makeUser('teacher', 'archived.teacher@test.local', false);

        $data = $this->actingAs($admin)->get('/admin/users')->viewData();

        $this->assertTrue($data['allParents']->contains('id', $activeParent->id));
        $this->assertTrue($data['allTeachers']->contains('id', $activeTeacher->id));
        $this->assertFalse($data['allParents']->contains('id', $archivedParent->id));
        $this->assertFalse($data['allTeachers']->contains('id', $archivedTeacher->id));
    }

    public function test_account_maker_picker_excludes_archived_students()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');

        $activeStudent = Student::create([
            'first_name' => 'Kylie', 'last_name' => 'Bright', 'sex' => 'Female',
            'date_of_birth' => now()->subYears(4), 'class_name' => '4-5 years old',
            'is_active' => true,
        ]);
        $archivedStudent = Student::create([
            'first_name' => 'Zaria', 'last_name' => 'Dimmed', 'sex' => 'Male',
            'date_of_birth' => now()->subYears(4), 'class_name' => '4-5 years old',
            'is_active' => false,
        ]);

        $students = $this->actingAs($admin)->get('/admin/users')->viewData()['allStudents'];

        $this->assertTrue($students->contains('id', $activeStudent->id));
        $this->assertFalse($students->contains('id', $archivedStudent->id));
    }

    public function test_archived_teacher_cannot_be_assigned_to_a_new_student()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $archivedTeacher = $this->makeUser('teacher', 'archived.teacher@test.local', false);

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old', 'teacher_id' => $archivedTeacher->id,
        ])->assertSessionHasErrors('teacher_id');

        $this->assertDatabaseMissing('students', ['teacher_id' => $archivedTeacher->id]);
    }

    public function test_archived_parent_cannot_be_connected_to_a_new_student()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $archivedParent = $this->makeUser('parent', 'archived.parent@test.local', false);

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old', 'parent_ids' => [$archivedParent->id],
        ])->assertSessionHasErrors('parent_ids.0');

        $this->assertDatabaseMissing('parent_student', ['parent_id' => $archivedParent->id]);
    }

    public function test_active_teacher_and_parents_still_work()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $parent = $this->makeUser('parent', 'parent@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Mia', 'last_name' => 'Reyes',
            'email' => 'mia@test.local', 'role' => 'student',
            'class_name' => '4-5 years old',
            'teacher_id' => $teacher->id,
            'parent_ids' => [$parent->id],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', [
            'teacher_id' => $teacher->id,
        ]);
        $this->assertDatabaseHas('parent_student', ['parent_id' => $parent->id]);
    }

    public function test_archived_accounts_remain_visible_in_the_archived_tab()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $archivedParent = $this->makeUser('parent', 'archived.parent@test.local', false);

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSee($archivedParent->email, false);
    }
}
