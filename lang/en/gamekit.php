<?php

/**
 * Strings for the shared game kit (public/js/kidlink-game.js): the 15-second
 * "need a clue?" hints and the level-complete card.
 *
 * Hint lists are ordered from gentle to obvious. The first hint after 15 quiet
 * seconds uses the first line, the next one (15 seconds later) the second, and
 * the last line repeats from then on.
 */
return [
    'buddy_hint' => 'Need a clue? Look at the wiggling ones!',

    'hints' => [
        'numbers' => [
            'count' => [
                "Let's count together! Tap each wiggling :name.",
                'One by one now. Tap every :name that is wiggling!',
            ],
            'answer' => [
                'You counted them all! Now find the number that matches.',
                'The answer is :answer! Tap the wiggling number.',
            ],
        ],
        'letters' => [
            'tap' => [
                'Look at the big :upper. Its small twin is hiding in the circle!',
                'Find the small :lower. The wiggling ones are matches!',
            ],
        ],
        'colors' => [
            'tap' => [
                'Look at the color in the middle. Which circles look the same?',
                'Look for :name. The wiggling circles are matches!',
            ],
        ],
        'color-vision' => [
            'tap' => [
                'Look at the color of the square. Which circles look exactly the same?',
            ],
        ],
        'colors-sort' => [
            'drag' => [
                'Pick up a paint drop and look for the bucket with the same color.',
                'Drag the wiggling drop into the wiggling bucket!',
            ],
        ],
        'shapes' => [
            'drag' => [
                'Look at the shape of the hole. Which piece looks the same?',
                'Drag the wiggling shape into the wiggling hole!',
            ],
        ],
        'animals' => [
            'tap' => [
                'Look at the animal on top. Can you find :name below?',
                'It is the wiggling animal!',
            ],
        ],
        'animals-match' => [
            'drag' => [
                'Every animal has a twin that looks exactly the same. Can you spot it?',
                'Drag the wiggling animal onto its twin!',
            ],
        ],
        'whats-missing' => [
            'tap' => [
                'Look at the numbers next to the empty box. What comes next?',
                'Count up in order. The missing number is :answer!',
            ],
        ],
        'shapes-bingo' => [
            'tap' => [
                'The caller wants a :name. Look for it on your card!',
                'Tap the wiggling :name!',
            ],
        ],
    ],

    'finish' => [
        'accuracy_line' => 'Accuracy: :percent%',
        'time_line' => 'Time: :time',
        'saving' => 'Saving your score...',
        'saved' => 'Score saved!',
        'save_failed' => "We couldn't save your score.",
        'retry' => 'Try again',
        'perfect' => 'Perfect! No mistakes!',
        'no_new_points' => 'No new points this time. Fewer mistakes earn more!',
    ],
];
