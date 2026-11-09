<?php

namespace App\Games;

/**
 * The single registry of student games.
 *
 * Every game carries an 'icon': the name of an icon in public/assets/icons,
 * drawn with <i class="ico ico-NAME"> (see public/css/icons.css). Game art is
 * never a PNG here, so the home grid, the game switcher and the loading screens
 * all share one set of icons that take the surrounding colour.
 *
 * A game belongs to a topic (Shapes, Colors, Letters, Animals, Numbers) and a
 * topic may hold several games, each with its own gameplay, slug, levels and
 * scoring. Adding a new entry here (plus its controller, views and routes)
 * automatically surfaces it in the home grid and in the in-game switcher.
 */
class GameCatalog
{
    /** Ordered topic slugs. Kept in the same order the home page lists them. */
    public const TOPICS = ['letters', 'numbers', 'colors', 'shapes', 'animals'];

    /** Every game in the catalogue. */
    public static function all(): array
    {
        return [
            [
                'topic' => 'letters',
                'slug' => 'letters',
                'name' => 'Letters',
                'desc' => 'Learn the alphabet!',
                'gradient' => 'linear-gradient(135deg, #ff8555, #f47548)',
                'icon' => 'language',
            ],
            [
                'topic' => 'numbers',
                'slug' => 'numbers',
                'name' => 'Numbers',
                'desc' => 'Count and calculate!',
                'gradient' => 'linear-gradient(135deg, #1982c4, #146aa3)',
                'icon' => 'calculator',
            ],
            [
                'topic' => 'numbers',
                'slug' => 'whats-missing',
                'name' => 'What\'s Missing?',
                'desc' => 'Find the missing number in the sequence!',
                'gradient' => 'linear-gradient(135deg, #1982c4, #146aa3)',
                'icon' => 'lightbulb',
            ],
            [
                'topic' => 'colors',
                'slug' => 'colors',
                'name' => 'Colors',
                'desc' => 'Identify colors!',
                'gradient' => 'linear-gradient(135deg, #8ac926, #6fa51c)',
                'icon' => 'color-palette',
            ],
            [
                'topic' => 'colors',
                'slug' => 'colors-sort',
                'name' => 'Color Sort',
                'desc' => 'Drag each paint drop into its matching bucket!',
                'gradient' => 'linear-gradient(135deg, #ff595e, #e8454a)',
                'icon' => 'water',
            ],
            [
                'topic' => 'colors',
                'slug' => 'color-vision',
                'name' => 'Color Check',
                'desc' => 'A gentle color vision check - tap the circles that match!',
                'gradient' => 'linear-gradient(135deg, #c026d3, #9d1fb0)',
                'icon' => 'eye',
            ],
            [
                'topic' => 'shapes',
                'slug' => 'shapes',
                'name' => 'Shapes',
                'desc' => 'Find the shapes!',
                'gradient' => 'linear-gradient(135deg, #ff595e, #e8454a)',
                'icon' => 'shapes',
            ],
            [
                'topic' => 'shapes',
                'slug' => 'shapes-bingo',
                'name' => 'Shape Bingo',
                'desc' => 'Dab the called shapes and shout Bingo!',
                'gradient' => 'linear-gradient(135deg, #6a4c93, #523a75)',
                'icon' => 'grid',
            ],
            [
                'topic' => 'animals',
                'slug' => 'animals',
                'name' => 'Animals',
                'desc' => 'Name the animals!',
                'gradient' => 'linear-gradient(135deg, #ffca3a, #b8942e)',
                'icon' => 'paw',
            ],
            [
                'topic' => 'animals',
                'slug' => 'animals-match',
                'name' => 'Animal Match',
                'desc' => 'Drag each animal onto its matching twin!',
                'gradient' => 'linear-gradient(135deg, #14b8a6, #0d9488)',
                'icon' => 'copy',
            ],
        ];
    }

    /** Games that share a topic. */
    public static function gamesForTopic(string $topic): array
    {
        return array_values(array_filter(self::all(), fn (array $game) => $game['topic'] === $topic));
    }

    /** A single game by its slug, or null. */
    public static function game(string $slug): ?array
    {
        foreach (self::all() as $game) {
            if ($game['slug'] === $slug) {
                return $game;
            }
        }

        return null;
    }
}