<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EccdRecord;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EccdController extends Controller
{
    private $domains = ['Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive', 'Social-Emotional'];

    private $indicators = [
        'Gross Motor' => [
            'Climbs on chair or other elevated piece of furniture like a bed without help',
            'Walks backwards',
            'Runs without tripping or falling',
            'Walks down stairs, two feet on each step, with one hand held',
            'Walks upstairs holding onto a handrail, two feet on each step',
            'Walks upstairs with alternate feet without holding onto a handrail',
            'Walks downstairs with alternate feet without holding onto a handrail',
            'Moves body part as directed',
            'Jumps up',
            'Throws ball overhead with direction',
            'Hops one to three steps on preferred foot',
            'Jumps and turns',
            'Dances patterns/joins group movement activities',
        ],
        'Fine Motor' => [
            'Uses all five fingers to get food/toys placed on a flat surface',
            'Picks up objects with thumb and index finger',
            'Displays a definite hand preference',
            'Puts small objects in/out of containers',
            'Holds crayon with all the fingers of his hand, making a fist (palmar grasp)',
            'Unscrews the lid of a container or unwraps food',
            'Scribbles spontaneously',
            'Scribbles vertical and horizontal lines',
            'Draws circle purposefully',
            'Draws a human figure (head, eyes, trunk, arms, hands/fingers)',
            'Draws a house using geometric forms',
        ],
        'Self-Help' => [
            'Feeds self with finger foods (e.g., biscuits, bread) using fingers',
            'Feeds self using fingers to eat rice/viands with spillage',
            'Feeds self using spoon with spillage',
            'Feeds self using fingers without spillage',
            'Feeds self using spoon without spillage',
            'Eats without need for spoon-feeding during any meal',
            'Helps hold cup for drinking',
            'Drinks from cup with spillage',
            'Drinks from cup unassisted',
            'Gets drink for self unassisted',
            'Pours from pitcher without spillage',
            'Prepares own food/snack',
            'Prepares meals for younger siblings/family members when no adult is around',
            'Participates when being dressed (e.g., raises arms or lifts leg)',
            'Pulls down gartered short pants',
            'Removes sandals',
            'Dresses without assistance except for buttoning and tying',
            'Dresses without assistance, including buttoning and tying',
            'Informs the adult only after he has already urinated or moved his bowels in his underpants',
            'Informs the adult of the need to urinate or move bowels so he can be brought to a designated place',
            'Goes to the designated place to urinate or move bowels but sometimes still does this in his underpants',
            'Goes to the designated place to urinate or move bowels and never does this in his underpants/wear anymore',
            'Wipes/cleans self after a bowel movement',
            'Participates when bathing (e.g., rubbing arms with soap)',
            'Washes and dries hands without any help',
            'Washes face without any help',
            'Bathes without any help',
        ],
        'Receptive Language' => [
            'Points to a family member when asked to do so',
            'Points to five body parts on himself when asked to do so',
            'Points to five named pictured objects when asked to do so',
            'Follows one-step instructions that include simple prepositions (e.g., in, on, under)',
            'Follows two-step instructions that include simple prepositions',
        ],
        'Expressive Language' => [
            'Uses 5 to 20 recognizable words',
            'Uses pronouns (e.g., I, me, ako, akin)',
            'Uses two- to three-word verb-noun combinations (e.g., bingi gatas)',
            'Names objects in pictures',
            'Speaks in grammatically correct two- to three-word sentences',
            'Asks "what" questions',
            'Asks "who" and "why" questions',
            'Gives an account of recent experiences, with prompting, in order of occurrence using past tense',
        ],
        'Cognitive' => [
            'Looks in the direction of a fallen object',
            'Looks for a partially hidden object',
            'Imitates behavior just seen a few minutes earlier',
            'Offers an object but will not release it',
            'Looks for a completely hidden object',
            'Exhibits simple pretend play (e.g., feeds, puts doll to sleep)',
            'Matches objects',
            'Matches 2 to 3 colors',
            'Matches pictures',
            'Sorts based on shapes',
            'Sorts objects based on two attributes (e.g., size and color)',
            'Arranges objects according to size from smallest to biggest',
            'Names four to six colors',
            'Copies shapes',
            'Names 3 animals or vegetables when asked',
            'States what common household items are used for',
            'Can assemble simple puzzles',
            'Demonstrates an understanding of opposites by completing a statement',
            'Points to left and right sides of body',
            'Can state what is silly or wrong with pictures',
            'Matches uppercase letters and matches lowercase letters',
        ],
        'Social-Emotional' => [
            'Enjoys watching activities of nearby people or animals',
            'Friendly with strangers but initially may show slight anxiety or shyness',
            'Plays alone but likes to be near familiar adults or brothers and sisters',
            'Laughs or squeals aloud in play',
            'Plays peek-a-boo (bulaga)',
            'Rolls ball interactively with caregiver/examiner',
            'Hugs or cuddles toys',
            'Demonstrates respect for elders using terms like "po" and "opo"',
            'Shares toys with others',
            'Imitates adult activities (e.g., cooking, washing)',
            'Identifies feelings in others',
            'Appropriately uses cultural gestures of greeting without much prompting (e.g., mano, bless, kiss)',
            'Comforts playmates/siblings in distress',
            'Persists when faced with a problem or obstacle to his wants',
            'Helps with family chores (e.g., wiping tables, watering plants)',
            'Curious about environment but knows when to stop asking questions of adults',
            'Waits for his turn',
            'Asks permission to play with a toy being used by another',
            'Defends possessions with determination',
            'Plays organized group games fairly (e.g., does not cheat to win)',
            'Can talk about complex feelings (e.g., anger, sadness, worry) he experiences',
            'Honors a simple bargain with caregiver (e.g., plays outside only after cleaning/fixing his/her room)',
            'Watches responsibly over younger siblings/family members',
            'Cooperates with adults and peers in group situations to minimize quarrels and conflicts',
        ],
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

        $labels = ['domains' => [], 'indicators' => []];
        foreach ($this->domains as $domain) {
            $labels['domains'][$domain] = __('eccd.domain_' . $this->domainKey($domain));
            foreach ($this->indicators[$domain] as $i => $indicator) {
                $labels['indicators'][$indicator] = __('eccd.' . $this->domainKey($domain) . '_' . $i);
            }
        }

        return view('teacher.eccd', compact(
            'students', 'selectedStudent', 'records',
            'scores', 'expectedScores', 'evaluationNumber', 'recommendations', 'labels'
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
        ])->with('success', __('eccd.flash_updated'));
    }

    private function getExpectedScore($age, $domain)
    {
        $baseline = min($age * 10, 100);
        $variation = ['Gross Motor' => 5, 'Fine Motor' => 0, 'Self-Help' => 5, 'Receptive Language' => -5, 'Expressive Language' => -10, 'Cognitive' => 0, 'Social-Emotional' => 0];
        $offset = $variation[$domain] ?? 0;
        return max(0, min(100, $baseline + $offset));
    }

    private function domainKey(string $domain): string
    {
        $map = [
            'Gross Motor' => 'gross_motor',
            'Fine Motor' => 'fine_motor',
            'Self-Help' => 'self_help',
            'Receptive Language' => 'receptive',
            'Expressive Language' => 'expressive',
            'Cognitive' => 'cognitive',
            'Social-Emotional' => 'social_emotional',
        ];

        return $map[$domain] ?? strtolower(str_replace([' ', '-'], '_', $domain));
    }

    private function generateExpertRecommendations(array $scores)
    {
        $recommendations = [];
        $overallAvg = !empty($scores) ? round(array_sum($scores) / count($scores)) : 0;

        if ($overallAvg >= 75) {
            $recommendations[] = ['title' => __('eccd.rec_on_track_title'), 'message' => __('eccd.rec_on_track_message'), 'severity' => 'success'];
        } else {
            $recommendations[] = ['title' => __('eccd.rec_followup_title'), 'message' => __('eccd.rec_followup_message'), 'severity' => 'warning'];
        }

        foreach ($scores as $domain => $score) {
            if ($score < 50) {
                $localized = __('eccd.domain_' . $this->domainKey($domain));
                $recommendations[] = [
                    'title' => __('eccd.rec_domain_attention_title', ['domain' => $localized]),
                    'message' => __('eccd.rec_domain_attention_message', ['score' => $score, 'domain' => $localized]),
                    'severity' => 'alert',
                ];
            }
        }

        return $recommendations;
    }
}
