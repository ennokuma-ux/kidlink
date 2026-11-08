<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnnouncementTest extends TestCase
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

    private function postAnnouncement(string $title = 'School closes early'): Announcement
    {
        return Announcement::create([
            'author_id' => $this->makeUser('teacher', 'author' . uniqid() . '@t.local')->id,
            'title' => $title,
            'body' => 'Friday finishes at 12pm.',
        ]);
    }

    public function test_every_role_can_read_the_board()
    {
        $this->postAnnouncement('Parents must see this');

        foreach (['super_admin' => 'a@t.local', 'teacher' => 'b@t.local', 'parent' => 'c@t.local'] as $role => $email) {
            $user = $this->makeUser($role, $email);

            $this->actingAs($user)
                ->get('/announcements')
                ->assertOk()
                ->assertSee('Parents must see this');
        }
    }

    public function test_parent_cannot_open_the_compose_form()
    {
        $parent = $this->makeUser('parent', 'parent@t.local');

        $this->actingAs($parent)->get('/announcements/create')->assertForbidden();
    }

    public function test_parent_cannot_post_an_announcement()
    {
        $parent = $this->makeUser('parent', 'parent@t.local');

        $this->actingAs($parent)
            ->post('/announcements', ['title' => 'Sneaky', 'body' => 'Should not work.'])
            ->assertForbidden();

        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_parent_page_hides_the_post_button()
    {
        $parent = $this->makeUser('parent', 'parent@t.local');

        $response = $this->actingAs($parent)->get('/announcements');

        $response->assertOk();
        $response->assertDontSee('New Announcement');
        $response->assertDontSee('route', false);
        $this->assertStringNotContainsString('announcements.create', $response->getContent());
    }

    public function test_teacher_can_post_an_announcement()
    {
        $teacher = $this->makeUser('teacher', 'teacher@t.local');

        $this->actingAs($teacher)
            ->post('/announcements', ['title' => 'PTA meeting', 'body' => 'Thursday at 6pm.'])
            ->assertRedirect('/announcements');

        $this->assertDatabaseHas('announcements', [
            'title' => 'PTA meeting',
            'author_id' => $teacher->id,
        ]);
    }

    public function test_super_admin_can_post_an_announcement()
    {
        $admin = $this->makeUser('super_admin', 'admin@t.local');

        $this->actingAs($admin)->get('/announcements/create')->assertOk();

        $this->actingAs($admin)
            ->post('/announcements', ['title' => 'School closure', 'body' => 'Closed all week.'])
            ->assertRedirect('/announcements');

        $this->assertDatabaseHas('announcements', ['title' => 'School closure']);
    }

    public function test_announcement_requires_a_title_and_body()
    {
        $teacher = $this->makeUser('teacher', 'teacher@t.local');

        $this->actingAs($teacher)
            ->post('/announcements', ['title' => '', 'body' => ''])
            ->assertSessionHasErrors(['title', 'body']);
    }

    public function test_author_can_remove_their_own_announcement()
    {
        $teacher = $this->makeUser('teacher', 'teacher@t.local');
        $announcement = Announcement::create([
            'author_id' => $teacher->id, 'title' => 'Oops', 'body' => 'Wrong info.',
        ]);

        $this->actingAs($teacher)
            ->delete("/announcements/{$announcement->id}")
            ->assertRedirect('/announcements');

        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_another_teacher_cannot_remove_someone_elses_announcement()
    {
        $author = $this->makeUser('teacher', 'author@t.local');
        $other = $this->makeUser('teacher', 'other@t.local');
        $announcement = Announcement::create([
            'author_id' => $author->id, 'title' => 'Not yours', 'body' => 'Leave it alone.',
        ]);

        $this->actingAs($other)
            ->delete("/announcements/{$announcement->id}")
            ->assertForbidden();

        $this->assertDatabaseCount('announcements', 1);
    }

    public function test_super_admin_can_remove_any_announcement()
    {
        $admin = $this->makeUser('super_admin', 'admin@t.local');
        $announcement = $this->postAnnouncement('Remove me');

        $this->actingAs($admin)
            ->delete("/announcements/{$announcement->id}")
            ->assertRedirect('/announcements');

        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_announcements_are_listed_newest_first()
    {
        $this->postAnnouncement('Older notice');
        $this->postAnnouncement('Newer notice');

        $admin = $this->makeUser('super_admin', 'admin@t.local');
        $html = $this->actingAs($admin)->get('/announcements')->getContent();

        $this->assertLessThan(
            strpos($html, 'Older notice'),
            strpos($html, 'Newer notice'),
            'Newest announcements should be listed first.'
        );
    }

    public function test_guests_are_redirected_to_login()
    {
        $this->get('/announcements')->assertRedirect('/login');
    }
}
