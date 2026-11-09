<?php

namespace App\Support;

/**
 * Shared layout and colours for every game's level map.
 *
 * The path is one curve; the eight level buttons sit at equal distances along
 * it, so every button is exactly on the road, the first one is where the road
 * starts and the last one is where it ends. Positions are percentages of the
 * 1200x700 map, matching the SVG viewBox.
 */
class StageMap
{
    /** Road geometry in the 1200x700 viewBox (starts at level 1, ends at level 8). */
    public const PATH = 'M 110.3 314.2 C 120.9 245.3, 111 179.6, 210 140 C 350 80, 420 160, 450 280 C 490 370, 390 440, 500 460 C 620 480, 680 410, 630 260 C 590 130, 720 120, 830 140';

    /** Level button centres, as [left %, top %]. */
    public const POSITIONS = [
        1 => [9.19, 44.89],
        2 => [16.02, 21.14],
        3 => [31.41, 20.97],
        4 => [38.31, 45.67],
        5 => [43.48, 66.10],
        6 => [53.97, 50.22],
        7 => [53.53, 23.39],
        8 => [69.17, 20.00],
    ];

    /**
     * Colours per game. 'accent' matches the game's colour on the home grid, the
     * road colours match the map picture behind it.
     */
    public const THEMES = [
        'letters'       => ['accent' => '#f47548', 'dark' => '#d4572a', 'edge' => '#c84d91', 'road' => '#f7e4f0', 'page' => '#fff8f0', 'frame' => '#ffc9cb'],
        'numbers'       => ['accent' => '#1982c4', 'dark' => '#146aa3', 'edge' => '#b8622a', 'road' => '#fff1d0', 'page' => '#eaf4fb', 'frame' => '#e3b565'],
        'whats-missing' => ['accent' => '#1982c4', 'dark' => '#146aa3', 'edge' => '#b8622a', 'road' => '#fff1d0', 'page' => '#eaf4fb', 'frame' => '#e3b565'],
        'colors'        => ['accent' => '#6fa51c', 'dark' => '#55810f', 'edge' => '#7a5a3a', 'road' => '#f1e4c8', 'page' => '#f4fae6', 'frame' => '#a5d6a7'],
        'colors-sort'   => ['accent' => '#e8454a', 'dark' => '#c63439', 'edge' => '#7a5a3a', 'road' => '#f1e4c8', 'page' => '#fff1f1', 'frame' => '#a5d6a7'],
        'color-vision'  => ['accent' => '#a21caf', 'dark' => '#86198f', 'edge' => '#7a5a3a', 'road' => '#f1e4c8', 'page' => '#fdf4ff', 'frame' => '#a5d6a7'],
        'shapes'        => ['accent' => '#e8454a', 'dark' => '#c63439', 'edge' => '#8d6e63', 'road' => '#d7ccc8', 'page' => '#f1f7fb', 'frame' => '#b3d6ee'],
        'shapes-bingo'  => ['accent' => '#6a4c93', 'dark' => '#523a75', 'edge' => '#8d6e63', 'road' => '#d7ccc8', 'page' => '#f4f0f8', 'frame' => '#b3d6ee'],
        'animals'       => ['accent' => '#b8942e', 'dark' => '#8f7222', 'edge' => '#8a5a2b', 'road' => '#fff3d6', 'page' => '#effafb', 'frame' => '#8fd3e0'],
        'animals-match' => ['accent' => '#0d9488', 'dark' => '#0a7a70', 'edge' => '#8a5a2b', 'road' => '#fff3d6', 'page' => '#f0fdfa', 'frame' => '#8fd3e0'],
    ];

    public static function theme(string $game): array
    {
        return self::THEMES[$game] ?? self::THEMES['letters'];
    }

    /**
     * The level list the map view draws.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function levels(string $game, int $currentLevel, int $totalLevels = 8): array
    {
        $levels = [];
        for ($i = 1; $i <= $totalLevels; $i++) {
            [$left, $top] = self::POSITIONS[$i] ?? [50.0, 50.0];
            $levels[] = [
                'number' => $i,
                'unlocked' => $i <= $currentLevel,
                'completed' => $i < $currentLevel,
                'current' => $i === $currentLevel,
                'left' => $left.'%',
                'top' => $top.'%',
                'route' => route('student.games.'.$game.'.play', ['level' => $i]),
            ];
        }

        return $levels;
    }
}
