{{--
    Shared game kit. Place it once in a game's play view, before its own script:

        <x-game-kit game="numbers" :level="$level" accent="#ffca3a" />

    It loads public/js/kidlink-game.js and hands it this game's URLs and words:
      - real play time for time_taken (game records)
      - the 15-second hint (shaking answers + mascot clue)
      - the level-complete card that appears immediately
    Then the game calls KidLinkGame.hints(...) and KidLinkGame.finish(...).
--}}
@props(['game', 'level' => 1, 'accent' => '#f47548'])

@php
    $routePrefix = 'student.games.'.$game.'.';

    $config = [
        'game' => $game,
        'level' => (int) $level,
        'accent' => $accent,
        'urls' => [
            'stage' => route($routePrefix.'stage-select'),
            'home' => route('student.home'),
            'next' => route($routePrefix.'play', ['level' => (int) $level + 1]),
            'submit' => route($routePrefix.'submit', ['level' => (int) $level]),
        ],
        'text' => [
            'level_num_complete' => __('game.level_num_complete'),
            'score_line' => __('game.score_line'),
            'mistakes_line' => __('game.mistakes_line'),
            'points_earned' => __('game.points_earned'),
            'back_to_map' => __('game.back_to_map'),
            'home' => __('game.home'),
            'next_level' => __('game.next_level'),
            'buddy_hint' => __('gamekit.buddy_hint'),
            'finish' => trans('gamekit.finish'),
        ],
        'hints' => trans('gamekit.hints.'.$game),
    ];
@endphp

<script>
    window.KidLinkGameConfig = @json($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
</script>
<script src="{{ \App\Support\Asset::v('js/kidlink-game.js') }}"></script>
