<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountVisibilityTest extends TestCase
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

    private function makeAdmin(string $email = 'admin@test.local'): User
    {
        return $this->makeUser('super_admin', $email);
    }

    public function test_admin_sees_child_mode_on_every_admin_page()
    {
        $admin = $this->makeAdmin();

        foreach (['/admin/analytics', '/admin/users'] as $path) {
            $html = $this->actingAs($admin)->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('Child Mode', $html, "The Child Mode button is missing from {$path}.");
            $this->assertStringContainsString('role="dialog"', $html, "The picker is missing from {$path}.");
        }
    }

    public function test_admin_sees_child_mode_on_announcements()
    {
        $admin = $this->makeAdmin();

        $html = $this->actingAs($admin)->get('/announcements')->assertOk()->getContent();

        $this->assertStringContainsString('Child Mode', $html, 'Admins should see Child Mode on announcements.');
    }

    public function test_admin_can_play_as_any_child()
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $this->makeStudent($teacher, 'Rivera');

        // The admin oversees the whole centre, so every child is offered.
        $accounts = $this->actingAs($admin)
            ->getJson('/child-mode/accounts')
            ->assertOk()
            ->json('accounts');

        $this->assertCount(1, $accounts);
        $this->assertSame('Kid Rivera', $accounts[0]['name']);

        $student = Student::first();
        $this->actingAs($admin)
            ->post('/child-mode/enter/'.$student->id)
            ->assertRedirect(route('student.home'));
    }

    public function test_admin_cannot_play_as_an_archived_child()
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $archived = $this->makeStudent($teacher, 'Archived', false);

        $accounts = $this->actingAs($admin)
            ->getJson('/child-mode/accounts')
            ->assertOk()
            ->json('accounts');

        $this->assertCount(0, $accounts, 'Archived children should never be offered to an admin.');

        $this->actingAs($admin)
            ->post('/child-mode/enter/'.$archived->id)
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_teacher_cannot_play_as_another_teachers_child()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $other = $this->makeUser('teacher', 'other@test.local');
        $theirs = $this->makeStudent($other, 'NotMine');

        $accounts = $this->actingAs($teacher)
            ->getJson('/child-mode/accounts')
            ->assertOk()
            ->json('accounts');

        $this->assertCount(0, $accounts);

        $this->actingAs($teacher)
            ->post('/child-mode/enter/'.$theirs->id)
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_parent_cannot_play_as_a_child_they_do_not_own()
    {
        $parent = $this->makeUser('parent', 'parent@test.local');
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $someoneElses = $this->makeStudent($teacher, 'NotMine');
        $someoneElses->parents()->attach($this->makeUser('parent', 'otherparent@test.local')->id);

        $this->actingAs($parent)
            ->post('/child-mode/enter/'.$someoneElses->id)
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_admin_returns_to_the_admin_area_after_exiting_child_mode()
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Rivera');

        $this->actingAs($admin)->post('/child-mode/enter/'.$student->id);

        // Exiting must land back in the admin area, not a teacher or parent page.
        // Entering child mode provisions the student login on the fly.
        $this->actingAs($student->refresh()->user)
            ->post('/child-mode/exit')
            ->assertRedirect(route('admin.analytics.index'));
    }

    public function test_teacher_returns_to_the_teacher_area_after_exiting_child_mode()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Rivera');

        $this->actingAs($teacher)->post('/child-mode/enter/'.$student->id);

        $this->actingAs($student->refresh()->user)
            ->post('/child-mode/exit')
            ->assertRedirect(route('teacher.dashboard'));
    }

    public function test_students_cannot_reach_the_child_mode_picker()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeUser('student', 'student@test.local');

        // No student login may enumerate or enter child mode.
        $this->actingAs($student)->getJson('/child-mode/accounts')->assertForbidden();
        $this->actingAs($student)
            ->post('/child-mode/enter/'.$this->makeStudent($teacher, 'Kid')->id)
            ->assertForbidden();
    }

    private function makeStudent(?User $teacher, string $last, bool $active = true): Student
    {
        return Student::create([
            'first_name' => 'Kid', 'last_name' => $last, 'sex' => 'Male',
            'date_of_birth' => '2020-01-01', 'class_name' => '1-3 years old',
            'dominant_hand' => 'Right',
            'teacher_id' => $teacher?->id,
            'enrolled_date' => now(), 'is_active' => $active,
        ]);
    }

    public function test_unassigned_student_still_appears_on_teacher_accounts()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $unassigned = $this->makeStudent(null, 'UnassignedKid');

        $response = $this->actingAs($teacher)->get('/teacher/accounts');

        $response->assertOk();
        $response->assertSee('UnassignedKid');
    }

    public function test_unassigned_archived_student_appears_in_archived_tab()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $this->makeStudent(null, 'ArchivedUnassigned', active: false);

        $response = $this->actingAs($teacher)->get('/teacher/accounts');

        $response->assertOk();
        $response->assertSee('ArchivedUnassigned');
    }

    public function test_teacher_does_not_see_another_teachers_students()
    {
        $teacherA = $this->makeUser('teacher', 'a@test.local');
        $teacherB = $this->makeUser('teacher', 'b@test.local');
        $this->makeStudent($teacherB, 'BelongsToB');

        $response = $this->actingAs($teacherA)->get('/teacher/accounts');

        $response->assertOk();
        $response->assertDontSee('BelongsToB');
    }

    public function test_admin_can_assign_a_teacher_when_creating_a_student_account()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'New', 'last_name' => 'Kid',
            'email' => 'newkid@test.local', 'password' => 'password123',
            'role' => 'student', 'teacher_id' => $teacher->id,
            'class_name' => '4-5 years old',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('students', [
            'user_id' => User::where('email', 'newkid@test.local')->value('id'),
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_teacher_id_must_reference_a_real_teacher()
    {
        $admin = $this->makeUser('super_admin', 'admin@test.local');
        $parent = $this->makeUser('parent', 'parent@test.local');

        $this->actingAs($admin)->post('/admin/users', [
            'first_name' => 'Bad', 'last_name' => 'Kid',
            'email' => 'badkid@test.local', 'password' => 'password123',
            'role' => 'student', 'teacher_id' => $parent->id,
        ])->assertSessionHasErrors('teacher_id');
    }

    public function test_student_without_created_at_still_listed()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'NoTimestamp');
        $student->created_at = null;
        $student->save();

        $response = $this->actingAs($teacher)->get('/teacher/accounts');

        $response->assertOk();
        $response->assertSee('NoTimestamp');
    }

    public function test_older_accounts_are_not_dropped_from_the_list()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $old = $this->makeStudent($teacher, 'VeryOldKid');
        $old->created_at = now()->subYears(3);
        $old->save();

        $this->makeStudent($teacher, 'NewestKid');

        $response = $this->actingAs($teacher)->get('/teacher/accounts');

        $response->assertOk();
        $response->assertSee('VeryOldKid');
        $response->assertSee('NewestKid');

        // Scope to the students table itself; the layout embeds a separate
        // child-mode account list that uses its own ordering.
        $html = $response->getContent();
        $table = substr($html, strpos($html, 'id="studentsView"'), strpos($html, 'id="parentsView"') - strpos($html, 'id="studentsView"'));

        $newPos = strpos($table, 'NewestKid');
        $oldPos = strpos($table, 'VeryOldKid');

        $this->assertNotFalse($newPos, 'Newest account missing from the students table.');
        $this->assertNotFalse($oldPos, 'Older account missing from the students table.');
        $this->assertLessThan(
            $oldPos,
            $newPos,
            'Accounts should be ordered newest first, with older accounts still listed.'
        );
    }

    public function test_child_mode_picker_is_a_searchable_popup()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $this->makeStudent($teacher, 'Rivera');
        $this->makeStudent($teacher, 'Santos');

        $html = $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk()->getContent();

        // The chooser must be a modal, not an inline dropdown: a fixed
        // full-screen backdrop that closes on backdrop click or Escape.
        $this->assertStringContainsString('fixed inset-0', $html, 'Child mode should open in a popup overlay.');
        $this->assertStringContainsString('role="dialog"', $html, 'Popup should be announced as a dialog.');
        $this->assertStringContainsString('@click.self="close()"', $html, 'Clicking the backdrop should close the popup.');
        $this->assertStringContainsString('@keydown.escape.window="close()"', $html, 'Escape should close the popup.');

        // A search field wired to the account list.
        $this->assertStringContainsString('x-model="search"', $html, 'Popup should include a search box.');
        $this->assertStringContainsString('get filtered()', $html, 'Search box should filter the account list.');
        $this->assertStringContainsString('x-for="a in filtered"', $html, 'Account rows should render the filtered results.');

        // The search input is focused as soon as the popup opens.
        $this->assertStringContainsString('$refs.pickerSearch.focus()', $html, 'Search box should be focused on open.');

        // Both children remain selectable.
        $this->assertStringContainsString('Rivera', $html);
        $this->assertStringContainsString('Santos', $html);
    }

    public function test_child_mode_button_is_visible_even_with_no_children()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');

        $html = $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk()->getContent();

        // The button must stay put when there is nobody to play as, otherwise
        // it silently disappears and looks broken.
        $this->assertStringContainsString('Child Mode', $html, 'The Child Mode button should always be rendered.');
        $this->assertStringContainsString('childModeAccounts', $html, 'The picker should render even when empty.');
        $this->assertStringContainsString('No children yet.', $html, 'An empty picker should explain itself.');
        $this->assertStringContainsString('Manage accounts', $html, 'An empty picker should link to where children are added.');
        $this->assertStringContainsString('teacher/accounts', $html, 'A teacher with no children should be pointed at their Accounts page.');
    }

    public function test_child_mode_picker_searches_by_section_as_well_as_name()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $this->makeStudent($teacher, 'Rivera');

        $html = $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk()->getContent();

        // class_name is part of the haystack, so a section name can find a child.
        $this->assertStringContainsString('[a.first, a.name, a.class_name]', $html, 'Search should match name and section.');
    }

    public function test_child_mode_button_is_visible_to_parents_without_children()
    {
        $parent = $this->makeUser('parent', 'parent@test.local');

        $html = $this->actingAs($parent)->get('/parent/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Child Mode', $html, 'Parents should see the Child Mode button too.');
    }

    /**
     * A child part-way through a game must not be able to leave child mode: the
     * button sat over the game and a stray tap threw away their progress.
     */
    public function test_game_pages_have_no_exit_child_mode_button()
    {
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Games');

        $this->actingAs($teacher)->post('/child-mode/enter/'.$student->id);

        foreach (['animals', 'colors', 'letters', 'numbers', 'shapes'] as $game) {
            foreach (["/student/games/{$game}/", "/student/games/{$game}/play/1"] as $path) {
                $html = $this->get($path)->assertOk()->getContent();

                $this->assertStringNotContainsString(
                    'Exit Child Mode',
                    $html,
                    "The exit button still appears on {$path}."
                );
                $this->assertStringNotContainsString(
                    'child-mode/exit',
                    $html,
                    "{$path} still posts to the exit route."
                );
            }
        }
    }

    public function test_the_exit_button_is_still_available_on_the_student_home_page()
    {
        // Only the games lost the button; the child still needs a way out.
        $teacher = $this->makeUser('teacher', 'teacher@test.local');
        $student = $this->makeStudent($teacher, 'Games');

        $this->actingAs($teacher)->post('/child-mode/enter/'.$student->id);

        $this->assertStringContainsString(
            'Exit Child Mode',
            $this->get('/student/home')->assertOk()->getContent()
        );
    }
}

