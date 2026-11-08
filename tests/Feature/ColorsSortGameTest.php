<?php

namespace Tests\Feature;

use App\Games\GameCatalog;
use App\Http\Controllers\Student\Games\ColorsSortGameController;
use App\Models\GameScore;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Color Sort ships the child a row of paint drops and a row of buckets, one drop
 * per bucket, shuffled. The drops and the buckets are the ones the server dealt,
 * so the browser cannot claim a round it was never given.
 */
class ColorsSortGameTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);

        $this->student = Student::create([
            'first_name' => 'Kid', 'last_name' => 'Sorting',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    /** The rounds (buckets + tray) the play page ships to the browser. */
    private function roundsForLevel(int $level): array
    {
        $this->unlockThrough($level);

        $html = $this->get("/student/games/colors-sort/play/{$level}")->assertOk()->getContent();

        preg_match('/const rounds = (\[.*?\]);/s', $html, $rounds);

        $this->assertNotEmpty($rounds[1] ?? null, 'The play page did not ship any rounds.');

        return json_decode($rounds[1], true);
    }

    /**
     * Levels are gated behind progress, so clear everything before $level the
     * same way the other game tests do.
     */
    private function unlockThrough(int $level): void
    {
        for ($done = 1; $done < $level; $done++) {
            GameScore::firstOrCreate(
                [
                    'student_id' => $this->student->id,
                    'game_type' => 'colors-sort',
                    'level' => $done,
                ],
                ['score' => 1, 'total' => 1, 'percentage' => 100]
            );
        }
    }

    public function test_every_round_offers_exactly_one_drop_per_bucket()
    {
        foreach ([1, 3, 5, 8] as $level) {
            $rounds = $this->roundsForLevel($level);
            $expected = app(ColorsSortGameController::class)->totalForLevel($level);

            $placed = 0;

            foreach ($rounds as $index => $round) {
                $bucketTypes = array_map(fn ($b) => $b['type'], $round['buckets']);
                $dropTypes = array_map(fn ($d) => $d['type'], $round['tray']);

                sort($bucketTypes);
                sort($dropTypes);

                $this->assertSame($bucketTypes, $dropTypes, "Level {$level} round {$index} drops do not match its buckets.");

                $placed += count($round['buckets']);
            }

            $this->assertSame($expected, $placed, "Level {$level} has the wrong drop count.");
        }
    }

    public function test_every_drop_and_bucket_carries_a_name_hex_and_marker()
    {
        $rounds = $this->roundsForLevel(1);

        foreach (array_merge(
            array_merge(...array_map(fn ($r) => $r['buckets'], $rounds)),
            array_merge(...array_map(fn ($r) => $r['tray'], $rounds))
        ) as $paint) {
            $this->assertNotEmpty($paint['name']);
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $paint['hex']);
            $this->assertNotEmpty($paint['marker']);
        }
    }

    public function test_sorting_every_drop_is_recorded_and_unlocks_the_next_level()
    {
        $total = app(ColorsSortGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/colors-sort/submit/1', [
            'score' => $total,
            'mistakes' => 2,
            'time_taken' => 45,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => true,
            'recorded' => true,
            'total' => $total,
            'next_level_unlocked' => true,
        ]);

        $this->assertDatabaseHas('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'colors-sort',
            'level' => 1,
            'score' => $total,
        ]);

        $this->assertSame(1, Point::where('student_id', $this->student->id)->count());

        $this->get('/student/games/colors-sort/play/2')->assertOk();
    }

    public function test_a_partly_sorted_level_is_not_recorded()
    {
        $total = app(ColorsSortGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/colors-sort/submit/1', [
            'score' => $total - 1,
            'mistakes' => 4,
        ])->assertOk()->assertJson([
            'success' => true,
            'completed' => false,
            'recorded' => false,
        ]);

        $this->assertDatabaseMissing('game_scores', [
            'student_id' => $this->student->id,
            'game_type' => 'colors-sort',
        ]);

        $this->assertSame(0, Point::where('student_id', $this->student->id)->count());
    }

    public function test_the_score_is_capped_by_the_server_drop_count()
    {
        $total = app(ColorsSortGameController::class)->totalForLevel(1);

        $this->postJson('/student/games/colors-sort/submit/1', ['score' => $total + 1])
            ->assertUnprocessable();
    }

    public function test_an_unknown_level_is_rejected()
    {
        $this->postJson('/student/games/colors-sort/submit/99', ['score' => 1])->assertNotFound();

        $this->get('/student/games/colors-sort/play/99')->assertNotFound();
    }

    public function test_a_locked_level_redirects_back_to_the_map()
    {
        $this->get('/student/games/colors-sort/play/3')->assertRedirect('/student/games/colors-sort');
    }

    public function test_the_game_sits_next_to_its_topic_neighbour_in_the_catalog_and_switcher()
    {
        $sort = GameCatalog::game('colors-sort');

        $this->assertNotNull($sort);
        $this->assertSame('colors', $sort['topic']);

        $map = $this->get('/student/games/colors-sort')->assertOk();
        $map->assertSee('Choose a game:', 'The color-sort stage-select lacks the switcher label.');
        $this->assertStringContainsString(
            'href="'.route('student.games.colors.stage-select').'"',
            $map->getContent(),
            'The switcher does not link the sibling Colors game.'
        );

        $play = $this->get('/student/games/colors-sort/play/1')->assertOk();
        $play->assertDontSee('Choose a game:', 'The color-sort play page should hide the game switcher.');
        $this->assertStringNotContainsString(
            'href="'.route('student.games.colors.stage-select').'"',
            $play->getContent(),
            'The color-sort play page should not link the sibling Colors game.'
        );
    }
}
