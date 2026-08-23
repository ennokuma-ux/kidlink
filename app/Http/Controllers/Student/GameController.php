<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private $questions = [
        'letters' => [
            ['question' => 'What letter comes after A?', 'options' => ['B', 'C', 'D', 'E'], 'answer' => 0],
            ['question' => 'Which letter is a vowel?', 'options' => ['B', 'C', 'A', 'D'], 'answer' => 2],
            ['question' => 'What is the first letter of "Apple"?', 'options' => ['B', 'A', 'C', 'D'], 'answer' => 1],
            ['question' => 'How many letters are in the word "CAT"?', 'options' => ['2', '3', '4', '5'], 'answer' => 1],
            ['question' => 'Which letter comes before C?', 'options' => ['A', 'B', 'D', 'E'], 'answer' => 1],
        ],
        'numbers' => [
            ['question' => 'What is 2 + 2?', 'options' => ['3', '4', '5', '6'], 'answer' => 1],
            ['question' => 'Count the stars: ★★★ How many?', 'options' => ['2', '3', '4', '5'], 'answer' => 1],
            ['question' => 'What number comes after 5?', 'options' => ['4', '5', '6', '7'], 'answer' => 2],
            ['question' => 'How many fingers on one hand?', 'options' => ['3', '4', '5', '6'], 'answer' => 2],
            ['question' => 'What is 1 + 1?', 'options' => ['1', '2', '3', '4'], 'answer' => 1],
        ],
        'colors' => [
            ['question' => 'What color is the sky?', 'options' => ['Red', 'Blue', 'Green', 'Yellow'], 'answer' => 1],
            ['question' => 'What color is grass?', 'options' => ['Blue', 'Red', 'Green', 'Orange'], 'answer' => 2],
            ['question' => 'What color is a banana?', 'options' => ['Red', 'Blue', 'Green', 'Yellow'], 'answer' => 3],
            ['question' => 'What color is an apple?', 'options' => ['Red', 'Blue', 'Green', 'Yellow'], 'answer' => 0],
            ['question' => 'What color is snow?', 'options' => ['Black', 'White', 'Gray', 'Brown'], 'answer' => 1],
        ],
        'shapes' => [
            ['question' => 'Which shape has 3 sides?', 'options' => ['Square', 'Circle', 'Triangle', 'Rectangle'], 'answer' => 2],
            ['question' => 'Which shape is round?', 'options' => ['Square', 'Circle', 'Triangle', 'Star'], 'answer' => 1],
            ['question' => 'Which shape has 4 equal sides?', 'options' => ['Circle', 'Triangle', 'Square', 'Heart'], 'answer' => 2],
            ['question' => 'Which shape looks like a ball?', 'options' => ['Square', 'Triangle', 'Circle', 'Diamond'], 'answer' => 2],
            ['question' => 'Which shape has 4 sides but not equal?', 'options' => ['Square', 'Rectangle', 'Circle', 'Triangle'], 'answer' => 1],
        ],
        'animals' => [
            ['question' => 'Which animal says "Moo"?', 'options' => ['Dog', 'Cat', 'Cow', 'Bird'], 'answer' => 2],
            ['question' => 'Which animal has a long trunk?', 'options' => ['Cat', 'Dog', 'Elephant', 'Fish'], 'answer' => 2],
            ['question' => 'Which animal can fly?', 'options' => ['Fish', 'Bird', 'Dog', 'Cat'], 'answer' => 1],
            ['question' => 'Which animal lives in water?', 'options' => ['Dog', 'Cat', 'Fish', 'Bird'], 'answer' => 2],
            ['question' => 'Which animal says "Woof"?', 'options' => ['Cat', 'Dog', 'Cow', 'Duck'], 'answer' => 1],
        ],
    ];

    public function show(string $type)
    {
        if (!isset($this->questions[$type])) {
            abort(404);
        }

        $gameQuestions = $this->questions[$type];
        $studentId = session('student_child_id');

        return view('student.game', compact('type', 'gameQuestions', 'studentId'));
    }
}
