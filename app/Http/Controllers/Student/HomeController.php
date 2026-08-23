<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = Student::where('first_name', $user->first_name)
                ->where('last_name', $user->last_name)
                ->first();
        }

        $scores = collect();
        $totalScore = 0;
        $totalGames = 0;
        $streak = 0;

        if ($student) {
            $scores = GameScore::where('student_id', $student->id)->get();
            $totalScore = $scores->sum('score');
            $totalGames = $scores->count();

            $recent = $scores->sortByDesc('created_at')->take(5);
            foreach ($recent as $s) {
                if ($s->score >= $s->total * 0.5) {
                    $streak++;
                } else {
                    break;
                }
            }

            session(['student_child_id' => $student->id, 'student_child_name' => $student->name]);
        }

        $games = [
            ['type' => 'letters', 'name' => 'Letters', 'desc' => 'Learn the alphabet!', 'color' => 'from-[#ff8555] to-[#f47548]', 'img' => 'alphabet.png'],
            ['type' => 'numbers', 'name' => 'Numbers', 'desc' => 'Count and calculate!', 'color' => 'from-[#258cc5] to-[#1f6f9e]', 'img' => 'numbers.png'],
            ['type' => 'colors', 'name' => 'Colors', 'desc' => 'Identify colors!', 'color' => 'from-[#68b82f] to-[#4e8a2f]', 'img' => 'rainbow.png'],
            ['type' => 'shapes', 'name' => 'Shapes', 'desc' => 'Find the shapes!', 'color' => 'from-[#f05d56] to-[#e04942]', 'img' => 'shapes.png'],
            ['type' => 'animals', 'name' => 'Animals', 'desc' => 'Name the animals!', 'color' => 'from-[#d9b348] to-[#b8942e]', 'img' => 'elephant.png'],
        ];

        return view('student.home', compact('student', 'scores', 'totalScore', 'totalGames', 'streak', 'games'));
    }
}
