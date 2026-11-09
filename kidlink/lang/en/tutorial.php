<?php

return [
    'ui' => [
        'button' => 'How to play',
        'title' => 'Tutorial',
        'step_of' => 'Step :current of :total',
        'next' => 'Next',
        'back' => 'Back',
        'skip' => 'Skip',
        'close' => 'Close',
        'try_it' => 'Your turn! Give it a try.',
        'correct' => 'Yes! You got it!',
        'wrong' => 'Almost! Try another one.',
        'ready_title' => "You're ready!",
        'ready_text' => "That's all you need to know. Pick a stage and have fun!",
        'start' => "Let's play!",
        'replay' => 'Watch again',
    ],

    // goal: what the game is about. practice: one tap-the-answer try-out that
    // is never scored. tip: the last hint before play.
    'games' => [
        'shapes' => [
            'name' => 'Shapes',
            'goal' => 'Look closely at each shape. Find the one that matches what you are asked for!',
            'practice' => ['question' => 'Tap the circle!', 'options' => ['🟦', '🔵', '🔺'], 'answer' => 1],
            'tip' => 'Circles are round, squares have four equal sides, triangles have three corners.',
        ],
        'colors' => [
            'name' => 'Colors',
            'goal' => 'Look at the colors and tap the one you are asked for!',
            'practice' => ['question' => 'Tap the red one!', 'options' => ['🍌', '🫐', '🍎'], 'answer' => 2],
            'tip' => 'Take your time. You can look at every choice before you tap.',
        ],
        'color-vision' => [
            'name' => 'Color Check',
            'goal' => 'Look closely at the colors and tap the ones that match!',
            'practice' => ['question' => 'Tap the blue one!', 'options' => ['🍌', '🫐', '🍎'], 'answer' => 1],
            'tip' => 'Take your time. Look at every circle before you tap.',
        ],
        'colors-sort' => [
            'name' => 'Color Sort',
            'goal' => 'Put each thing in the basket with the same color!',
            'practice' => ['question' => 'Which basket for the apple 🍎?', 'options' => ['🟦', '🟨', '🟥'], 'answer' => 2],
            'tip' => 'Look at the color of the item first, then find its matching basket.',
        ],
        'letters' => [
            'name' => 'Letters',
            'goal' => 'Find the right letter! Look and listen carefully.',
            'practice' => ['question' => 'Tap the letter B!', 'options' => ['A', 'C', 'B'], 'answer' => 2],
            'tip' => 'Say the letter out loud as you tap it. It helps you remember!',
        ],
        'animals' => [
            'name' => 'Animals',
            'goal' => 'Who is hiding? Tap the animal you are asked to find!',
            'practice' => ['question' => 'Tap the cat!', 'options' => ['🐶', '🐱', '🐟'], 'answer' => 1],
            'tip' => 'Try making the animal sound. Meow! Woof!',
        ],
        'animals-match' => [
            'name' => 'Animal Match',
            'goal' => 'Find the animal that matches the one you see!',
            'practice' => ['question' => 'Match this one: 🐰', 'options' => ['🐻', '🐰', '🐸'], 'answer' => 1],
            'tip' => 'Look at the ears, the tail and the shape. They help you tell animals apart.',
        ],
        'shapes-bingo' => [
            'name' => 'Shapes Bingo',
            'goal' => 'Find the shape on your bingo card. Fill the card to win!',
            'practice' => ['question' => 'Tap the square on the card!', 'options' => ['🔵', '🔺', '🟦'], 'answer' => 2],
            'tip' => 'Scan the whole card before you tap. The shape is always there!',
        ],
        'numbers' => [
            'name' => 'Numbers',
            'goal' => 'Count carefully and tap the right number!',
            'practice' => ['question' => 'How many apples? 🍎🍎🍎', 'options' => ['2', '3', '4'], 'answer' => 1],
            'tip' => 'Point at each thing with your finger and count one by one.',
        ],
        'whats-missing' => [
            'name' => "What's Missing?",
            'goal' => 'Look at the pictures, then find the one that disappeared!',
            'practice' => ['question' => '🍎 ❓ 🍇  What is missing?', 'options' => ['🍋', '🍌', '🍓'], 'answer' => 1],
            'tip' => 'Remember the order of the pictures before they change.',
        ],
    ],
];
