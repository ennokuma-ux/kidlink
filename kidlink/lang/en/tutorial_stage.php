<?php

/**
 * Level 1 of every game is the tutorial stage: a coach (the child's mascot)
 * explains the game, points at what to do, and steps back as the child learns.
 */
return [
    'badge' => 'Tutorial',
    'tag' => 'Tutorial',
    'start' => "Let's go!",
    'how_to' => 'How to play',
    'show_me' => 'Show me',
    'welcome_title' => 'Welcome to the tutorial!',
    'welcome_sub' => 'Level 1 teaches you how to play. Take your time. I will help!',
    'oops' => "Oops, that's okay! Try the glowing one.",
    'idle' => 'Need help? Tap "Show me".',

    'games' => [
        'numbers' => [
            'name' => 'Numbers',
            'goal' => 'Count the pictures, then tap the number that tells how many there are.',
            'steps' => ['Tap each picture to count it.', 'Find the number that says how many.', 'Tap that number!'],
            'count' => 'Tap each picture to count it. Start with the glowing one!',
            'answer' => 'Now tap the number that tells how many you counted.',
            'solo' => 'Your turn! Count, then tap the right number.',
        ],
        'letters' => [
            'name' => 'Letters',
            'goal' => 'Find the small letters that match the big letter in the middle.',
            'steps' => ['Look at the big letter.', 'Find its small twin around the circle.', 'Tap every small match!'],
            'tap' => 'Tap the small letter that matches the big one.',
            'solo' => 'Your turn! Find the small letters that match.',
        ],
        'colors' => [
            'name' => 'Colors',
            'goal' => 'Tap every circle that has the same color as the big square.',
            'steps' => ['Look at the color of the big square.', 'Find the circles with the same color.', 'Tap each match!'],
            'tap' => 'Tap the circle that has the same color as the square.',
            'solo' => 'Your turn! Tap the circles with the matching color.',
        ],
        'colors-sort' => [
            'name' => 'Color Sort',
            'goal' => 'Drag each paint drop into the bucket that has the same color.',
            'steps' => ['Pick up a paint drop.', 'Drag it to the bucket with the same color.', 'Let go to drop it in!'],
            'drag' => 'Drag the paint drop into the bucket with the same color.',
            'solo' => 'Your turn! Drag each drop into the right bucket.',
        ],
        'shapes' => [
            'name' => 'Shapes',
            'goal' => 'Drag each shape into the hole that fits it.',
            'steps' => ['Pick up a shape.', 'Drag it to the matching hole.', 'Let go to drop it in!'],
            'drag' => 'Drag the shape into the hole that fits it.',
            'solo' => 'Your turn! Put each shape in its hole.',
        ],
        'animals' => [
            'name' => 'Animals',
            'goal' => 'Look at the animal at the top, then tap the one that matches it.',
            'steps' => ['Look at the animal at the top.', 'Find the same animal below.', 'Tap it!'],
            'tap' => 'Tap the animal that matches the one at the top.',
            'solo' => 'Your turn! Find the matching animal.',
        ],
        'animals-match' => [
            'name' => 'Animal Match',
            'goal' => 'Drag each animal onto its matching twin.',
            'steps' => ['Pick up an animal from the bottom.', 'Drag it onto its twin.', 'Let go to match them!'],
            'drag' => 'Drag the animal onto its twin.',
            'solo' => 'Your turn! Match each animal with its twin.',
        ],
        'whats-missing' => [
            'name' => "What's Missing?",
            'goal' => 'Find the number that is missing from the row.',
            'steps' => ['Look at the numbers in order.', 'Find the empty box.', 'Tap the number that fits.'],
            'tap' => 'Tap the number that fills the empty box.',
            'solo' => 'Your turn! Find the missing number.',
        ],
        'shapes-bingo' => [
            'name' => 'Shape Bingo',
            'goal' => 'Look at the shape that is called, then tap it on your card.',
            'steps' => ['Look at the shape being called.', 'Find it on your card.', 'Tap it to dab it!'],
            'tap' => 'Tap the shape that was called.',
            'solo' => 'Your turn! Find the shape that was called.',
        ],
    ],
];
