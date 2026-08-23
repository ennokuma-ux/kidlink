<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\Student;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'type' => 'required|in:letters,numbers,colors,shapes,animals',
            'score' => 'required|integer|min:0',
            'total' => 'required|integer|min:1',
        ]);

        $studentId = session('student_child_id');
        if (!$studentId) {
            $studentId = Student::where('user_id', auth()->id())->value('id');
        }
        if (!$studentId) {
            return response()->json(['ok' => false, 'message' => 'No student session'], 400);
        }

        GameScore::create([
            'student_id' => $studentId,
            'game_type' => $request->type,
            'score' => $request->score,
            'total' => $request->total,
        ]);

        return response()->json(['ok' => true]);
    }
}
