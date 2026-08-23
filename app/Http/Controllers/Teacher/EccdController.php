<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EccdRecord;
use App\Models\ExpertRecommendation;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EccdController extends Controller
{
    private $domains = ['Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive'];

    private $indicators = [
        'Gross Motor' => ['Walks independently', 'Runs with coordination', 'Jumps in place', 'Balances on one foot', 'Throws ball overhead', 'Catches large ball', 'Climbs playground equipment', 'Pedals tricycle', 'Walks up/down stairs', 'Kicks ball forward'],
        'Fine Motor' => ['Stacks blocks (6+)', 'Holds crayon properly', 'Copies circle shape', 'Strings large beads', 'Uses scissors', 'Draws person (3 parts)', 'Buttons clothing', 'Completes puzzles (4+ pieces)', 'Traces shapes', 'Writes some letters'],
        'Self-Help' => ['Feeds self with spoon', 'Drinks from cup', 'Washes hands', 'Puts on shoes', 'Toilets independently', 'Brushes teeth with help', 'Dresses with assistance', 'Tidies up toys', 'Blows nose', 'Sleeps through night'],
        'Receptive Language' => ['Follows 1-step commands', 'Points to body parts', 'Understands "no"', 'Identifies objects by name', 'Follows 2-step commands', 'Understands prepositions', 'Sorts by color/shape', 'Recognizes own name', 'Understands actions words', 'Follows simple stories'],
        'Expressive Language' => ['Says first words', 'Uses 2-word phrases', 'Asks simple questions', 'Names familiar objects', 'Uses pronouns', 'Sings simple songs', 'Tells simple stories', 'Uses past tense', 'Asks "why" questions', 'Holds conversation'],
        'Cognitive' => ['Matches objects', 'Identifies colors', 'Counts to 10', 'Recognizes shapes', 'Sorts by size', 'Understands time concepts', 'Shows object permanence', 'Engages pretend play', 'Follows daily routine', 'Shows problem-solving'],
    ];

    public function index(Request $request)
    {
        $teacherId = auth()->id();
        $students = Student::where('teacher_id', $teacherId)->where('is_active', true)->get();
        $selectedStudent = $request->student_id ? Student::find($request->student_id) : $students->first();

        $evaluationNumber = $request->evaluation ?? 1;

        $records = collect();
        $scores = [];
        $expectedScores = [];

        if ($selectedStudent) {
            $records = EccdRecord::where('student_id', $selectedStudent->id)
                ->where('assessed_by', $teacherId)
                ->get()
                ->groupBy('domain');

            foreach ($this->domains as $domain) {
                $domainRecords = $records->get($domain, collect());
                $achieved = $domainRecords->where('status', 'Achieved')->count();
                $total = max(count($this->indicators[$domain]), 1);
                $scores[$domain] = round(($achieved / $total) * 100);
                $expectedScores[$domain] = $this->getExpectedScore($selectedStudent->age, $domain);
            }

            if ($records->isEmpty()) {
                foreach ($this->domains as $domain) {
                    foreach ($this->indicators[$domain] as $indicator) {
                        EccdRecord::create([
                            'student_id' => $selectedStudent->id,
                            'domain' => $domain,
                            'indicator' => $indicator,
                            'status' => 'Not Yet',
                            'assessed_by' => $teacherId,
                            'assessed_at' => Carbon::today(),
                        ]);
                    }
                }

                return redirect()->route('teacher.eccd.index', ['student_id' => $selectedStudent->id]);
            }
        }

        $recommendations = $this->generateExpertRecommendations($scores);

        return view('teacher.eccd', compact(
            'students', 'selectedStudent', 'records',
            'scores', 'expectedScores', 'evaluationNumber', 'recommendations'
        ) + ['domains' => $this->domains]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'record_id' => 'required|exists:eccd_records,id',
            'status' => 'required|in:Not Yet,In Progress,Achieved',
            'notes' => 'nullable|string',
        ]);

        $record = EccdRecord::findOrFail($request->record_id);
        $record->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'assessed_at' => Carbon::today(),
        ]);

        return redirect()->route('teacher.eccd.index', [
            'student_id' => $request->student_id,
            'evaluation' => $request->evaluation,
        ])->with('success', 'ECCD record updated!');
    }

    private function getExpectedScore($age, $domain)
    {
        $baseline = min($age * 10, 100);
        $variation = ['Gross Motor' => 5, 'Fine Motor' => 0, 'Self-Help' => 5, 'Receptive Language' => -5, 'Expressive Language' => -10, 'Cognitive' => 0];
        $offset = $variation[$domain] ?? 0;
        return max(0, min(100, $baseline + $offset));
    }

    private function generateExpertRecommendations(array $scores)
    {
        $recommendations = [];
        $overallAvg = !empty($scores) ? round(array_sum($scores) / count($scores)) : 0;

        if ($overallAvg >= 75) {
            $recommendations[] = ['title' => 'On Track', 'message' => 'Child is developing well across all domains. Continue providing enriching activities.', 'severity' => 'success'];
        } else {
            $recommendations[] = ['title' => 'Needs Follow-up', 'message' => 'Some domains need attention. Consider additional support activities.', 'severity' => 'warning'];
        }

        foreach ($scores as $domain => $score) {
            if ($score < 50) {
                $recommendations[] = ['title' => "$domain - Needs Attention", 'message' => "Score of $score% in $domain is below expected. Please plan targeted interventions.", 'severity' => 'alert'];
            }
        }

        return $recommendations;
    }
}
