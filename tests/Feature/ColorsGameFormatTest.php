<?php

namespace Tests\Feature;

use App\Models\GameScore;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The colors game is a square in the middle surrounded by circles: the child has
 * to select every circle that shares the square's colour. These tests pin down
 * that contract so the round data and the view cannot drift apart again.
 */
class ColorsGameFormatTest extends TestCase
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
            'first_name' => 'Kid', 'last_name' => 'Colors',
            'sex' => 'Male', 'date_of_birth' => '2020-01-01',
            'class_name' => '1-3 years old', 'dominant_hand' => 'Right',
            'teacher_id' => $this->teacher->id,
            'enrolled_date' => now(), 'is_active' => true,
        ]);

        $this->actingAs($this->teacher)->post('/child-mode/enter/'.$this->student->id);
    }

    /**
     * Levels are gated behind progress, so clear everything before $level.
     *
     * Tests that walk several levels in a loop call this repeatedly for the same
     * early levels, and a stage can only be recorded once, so the row is left
     * alone if it is already there.
     */
    private function unlockThrough(int $level): void
    {
        for ($done = 1; $done < $level; $done++) {
            GameScore::firstOrCreate(
                [
                    'student_id' => $this->student->id,
                    'game_type' => 'colors',
                    'level' => $done,
                ],
                [
                    'score' => 3, 'total' => 3,
                    'mistakes' => 0, 'percentage' => 100,
                    'time_taken' => 20,
                ]
            );
        }
    }

    /**
     * The round payload the page ships to the browser, so the assertions describe
     * what the child actually plays rather than what the controller returned.
     */
    private function roundsForLevel(int $level): array
    {
        $this->unlockThrough($level);

        $html = $this->get("/student/games/colors/play/{$level}")->assertOk()->getContent();

        preg_match('/const rounds = (\[.*?\]);/s', $html, $m);
        $this->assertNotEmpty($m[1] ?? null, 'The play page did not ship any round data.');

        return json_decode($m[1], true);
    }

    public function test_every_round_asks_the_child_to_select_all_matching_circles()
    {
        foreach ([1, 4, 8] as $level) {
            foreach ($this->roundsForLevel($level) as $index => $round) {
                $where = "level {$level} round {$index}";

                $this->assertArrayHasKey('circles', $round, "{$where} has no circles.");
                $this->assertArrayHasKey('matchesNeeded', $round, "{$where} has no match count.");
                $this->assertArrayNotHasKey(
                    'options',
                    $round,
                    "{$where} still carries the old single-tap options."
                );

                $matches = array_values(array_filter($round['circles'], fn ($c) => $c['isMatch']));

                $this->assertCount(
                    $round['matchesNeeded'],
                    $matches,
                    "{$where} does not flag exactly as many circles as it asks for."
                );
                $this->assertGreaterThan(
                    0,
                    $round['matchesNeeded'],
                    "{$where} asks for nothing, so the round cannot be failed."
                );
                $this->assertLessThan(
                    count($round['circles']),
                    $round['matchesNeeded'],
                    "{$where} has no wrong circle to choose from."
                );

                foreach ($matches as $match) {
                    $this->assertSame(
                        $round['target'],
                        $match['type'],
                        "{$where} flags a circle that is not the square's colour."
                    );
                }

                foreach ($round['circles'] as $circle) {
                    if (! $circle['isMatch']) {
                        $this->assertNotSame(
                            $round['target'],
                            $circle['type'],
                            "{$where} hides an unmarked circle of the target colour."
                        );
                    }
                }
            }
        }
    }

    public function test_the_score_denominator_equals_the_circles_the_child_must_find()
    {
        foreach ([1, 5, 8] as $level) {
            $this->unlockThrough($level);

            $html = $this->get("/student/games/colors/play/{$level}")->assertOk()->getContent();

            preg_match('/const rounds = (\[.*?\]);/s', $html, $m);
            preg_match('/const totalQuestions = (\d+);/', $html, $q);
            preg_match('/const totalRounds = (\d+);/', $html, $r);

            $rounds = json_decode($m[1], true);

            $this->assertSame(
                array_sum(array_column($rounds, 'matchesNeeded')),
                (int) $q[1],
                "Level {$level} scores out of the wrong number."
            );
            $this->assertCount((int) $r[1], $rounds, "Level {$level} round count is off.");
        }
    }

    public function test_the_play_page_no_longer_uses_the_single_tap_tray()
    {
        $html = $this->get('/student/games/colors/play/1')->assertOk()->getContent();

        $this->assertStringContainsString('class="target-square"', $html);
        $this->assertStringContainsString('id="boardStage"', $html);
        $this->assertStringContainsString('Tap every circle that matches the square!', $html);

        foreach (['trayRow', 'color-swatch', 'target-circle'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "Leftover {$gone} from the old layout.");
        }
    }

    public function test_higher_levels_ask_the_child_to_find_more_circles()
    {
        $levelOne = $this->roundsForLevel(1)[0];
        $levelEight = $this->roundsForLevel(8)[0];

        $this->assertGreaterThan(
            $levelOne['matchesNeeded'],
            $levelEight['matchesNeeded'],
            'The hardest level should ask for more matches than the first.'
        );
        $this->assertGreaterThan(
            count($levelOne['circles']),
            count($levelEight['circles']),
            'The hardest level should have more circles around the square.'
        );
    }

    /**
     * The palette is the Okabe-Ito colour-vision-deficiency-safe set, with the
     * red swapped for #B22222 because the published #D55E00 sat too close to the
     * orange in hue and under red-green simulation. This guards against a palette
     * creeping back to a red/green pair, which is invisible to a colourblind
     * child but fatal for this game.
     */
    public function test_every_colour_comes_from_the_colorblind_safe_palette()
    {
        $approved = [
            '#B22222', '#E69F00', '#F0E442', '#009E73',
            '#0072B2', '#56B4E9', '#CC79A7', '#000000',
        ];

        foreach ([1, 5, 8] as $level) {
            foreach ($this->roundsForLevel($level) as $round) {
                $this->assertContains(
                    strtoupper($round['targetHex']),
                    $approved,
                    "Level {$level} uses a colour outside the safe palette."
                );

                foreach ($round['circles'] as $circle) {
                    $this->assertContains(
                        strtoupper($circle['hex']),
                        $approved,
                        "Level {$level} puts an unsafe colour on the board."
                    );
                }
            }
        }
    }

    public function test_the_old_red_green_palette_is_gone()
    {
        // Blue #3b82f6 and purple #a855f7 were literally the same colour to a
        // deuteranope (deltaE 0), so a child could not tell them apart at all.
        // #D55E00 is Okabe-Ito's own red, but it was too close to the orange here.
        $banned = ['#EF4444', '#22C55E', '#3B82F6', '#A855F7', '#EC4899', '#A16207', '#D55E00'];

        $seen = [];
        foreach ([1, 5, 8] as $level) {
            foreach ($this->roundsForLevel($level) as $round) {
                foreach ($round['circles'] as $circle) {
                    $seen[] = strtoupper($circle['hex']);
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_intersect($seen, $banned)),
            'A colour from the old palette crept back in.'
        );
    }

    /**
     * Hue is not the only channel: the colour aid toggle reveals one letter per
     * colour so a child who cannot separate two hues still has another way in.
     */
    public function test_every_colour_carries_a_marker_and_the_toggle_exists()
    {
        $markers = [];

        foreach ([1, 8] as $level) {
            foreach ($this->roundsForLevel($level) as $round) {
                $this->assertNotEmpty($round['targetMarker'] ?? null, 'The square has no marker.');

                foreach ($round['circles'] as $circle) {
                    $this->assertNotEmpty($circle['marker'] ?? null, "{$circle['type']} has no marker.");
                    $markers[$circle['type']] = $circle['marker'];
                }
            }
        }

        // Two colours sharing a marker would defeat the point of the aid.
        $this->assertSame(
            count($markers),
            count(array_unique($markers)),
            'Two colours share the same marker: '.implode(', ', array_keys($markers))
        );

        $html = $this->get('/student/games/colors/play/1')->assertOk()->getContent();
        $this->assertStringContainsString('id="aidToggle"', $html, 'No colour aid toggle.');
        $this->assertStringContainsString('body:not(.aid-on) .circle-marker', $html);
    }
}