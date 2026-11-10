<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EccdRecord;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


    private $domains0To3 = [
            'Gross Motor' => [
            ],
            'Fine Motor' => [
            ],
            'Self-Help' => [
            ],
            'Social-Emotional' => [
            ],
            'Receptive Language' => [
            ],
            'Expressive Language' => [
            ],
            'Cognitive' => [
            ],
        ];

    private $indicators0To3 = [
            'Gross Motor' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant lifts head when held standing'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant tries to lift head when lying on stomach'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant holds head upright on his own when lying on stomach'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant holds head steadily'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant rolls over from his back to his stomach with assistance'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant turns from his side to his back without assistance'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant sits well with support, leaning on hands if placed on hard surface'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant bounces when held standing, briefly bearing weight on legs'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant sits alone steadily'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant moves from sitting to creeping/crawling position'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant pulls self from sitting to standing position with minimal assistance'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Stands with minimum support'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Holds furniture with both hands and walks sideways'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks with both hands held'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Climbs on chair or another elevated piece of furniture like a bed without help'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks alone, rarely falls'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks backwards'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Runs without tripping or falling'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks down stairs, 2 feet on each step, with one hand held'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks up stairs holding handrail, 2 feet on each step'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks upstairs with alternate feet without holding on to the handrail'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Walks downstairs with alternate feet without holding onto a handrail'],
            ],
            'Fine Motor' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Hands of infant are kept open most of the time'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant waves arms, moves body at the sight of a dangling object/toy'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant brings both hands together toward dangling object/toy'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant reaches for and holds small toy/spoon'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Uses all five fingers to get food/toys placed on flat surface'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Picks up objects with thumb and index finger'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Pulls toy with string'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Displays a definite hand preference'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Grasps and transfers objects from hand to hand'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Pushes or pulls a large object'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Puts small objects in/out of container'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Holds crayon with all the fingers of his hand making a fist (i.e., palmar grasp)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Unscrews lid of container or unwraps food'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Scribbles spontaneously'],
            ],
            'Self-Help' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant sucks and swallows milk from breast or bottle'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant begins to take solid foods'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Chews solid food well'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Holds bottle all by himself'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Feeds self with finger food (e.g., biscuits, bread) using fingers'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Helps hold a cup for drinking'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Feeds self using fingers to eat rice/viands with spillage'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Drinks from a cup without help'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Feeds self using a spoon with spillage'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Gets a drink for himself without any help'],
            ],
            'Social-Emotional' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant stops crying when mother picks him up'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant smiles in response to the caregiver'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant reacts to familiar situations like bathing or feeding (e.g., thrashes about, sucks vigorously at the sight of bottle)'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant lifts arms to greet familiar people'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Cries when caregiver leaves'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Enjoys watching activities of nearby people or animals'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Smiles playfully at mirror image'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Friendly with strangers but initially may show slight anxiety or shyness'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Plays alone but likes to be near familiar adults or brothers and sisters'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Laughs or squeals aloud in play'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Plays peek-a-boo (bulaga)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Rolls ball interactively with caregiver/examiner'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Hugs or cuddles toys'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Responds with pleasure to friendly people'],
            ],
            'Receptive Language' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant startles to loud sounds'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant turns eyes/head toward sound'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant watches mother intently as she speaks to him'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant smiles at a familiar voice, like when his mother talks to him'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant stops crying when sung to'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Turns head when called by name, makes eye contact'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Understands "no"'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Imitates adults\' playful sounds like coughing, lip smacking'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Follows one step-command (e.g., give) when gestures are used'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Follows one step-command without need for gestures'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Points to a family member when asked to do so'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Points to five body parts on himself when asked to do so'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Points to five named pictured objects when asked to do so'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Follows one-step instructions that include simple prepositions (e.g., in, on, under, etc.)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Follows two-step instructions that include simple prepositions'],
            ],
            'Expressive Language' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant vocalizes (e.g., gurgles, coos, grunts)'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant vocalizes to express pleasure or displeasure'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant shouts to attract the attention of the caregiver'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant laughs out loud and squeals'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant vocalizes different vowel sounds (e.g., ahh, oooh)'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant vocalizes other sounds that do not mean anything'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant babbles continuously to amuse self or attract attention'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant tries to imitate the caregiver\'s vocalizations'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant uses body movements/gestures to make wants known (e.g., stretches arms to indicate what he wants)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Repeats vowel-consonant combinations without referring to anything or anyone (e.g., baba, mama, dada)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Uses sounds meaningfully to refer to specific objects/persons (e.g., "Mama" for his mother; "mamam" for water)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Chatters in a conversational tone without using real words'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Tries to imitate real words of adult'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Combines single words and gestures to make wants known (e.g., "out" while pointing to the door)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Uses 5 to 20 recognizable words'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Uses pronouns (e.g., I, me, ako, akin)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Uses two- to three-word verb-noun combinations (e.g., bingi gatas)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Names objects in pictures'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Speaks in grammatically correct two- to three-word sentences'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Asks "what" questions'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Asks "who" and "why" questions'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Gives account of recent experiences (with prompting) in order of occurrence using past tense'],
            ],
            'Cognitive' => [
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant inspects surroundings'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant gazes slowly at moving objects/people'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant turns head to bright colored objects'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant changes face when he does not like the taste of his food'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant explores and manipulates objects by biting, holding, and looking at them'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant shakes toy deliberately to make this sound'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant eventually gets used to irritating or bothersome familiar sounds (e.g., tricycle, crowing of cock)'],
                ['age_band' => 'Infants (0 months–1 year)', 'indicator' => 'Infant reacts to cloth placed over his face'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Looks in the direction of a fallen object'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Displays a preference for certain objects by reaching for these more readily than others'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Pushes the ball to make it roll'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Repeatedly drops or throws objects'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Looks for a partially hidden object'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Imitates behavior just seen a few minutes earlier'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Offers an object but may not release it'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Looks for completely hidden object'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Exhibits simple pretend play (feeds, puts doll to sleep)'],
                ['age_band' => '1 year 1 month–3 years', 'indicator' => 'Matches objects'],
            ],
        ];

    public function index(Request $request)
    {
        $teacherId = auth()->id();
        $students = Student::where('teacher_id', $teacherId)->where('is_active', true)->get();
        // No default child: the page opens on a prompt and the teacher picks one.
        // Resolved only against this teacher's own students, so a guessed id
        // cannot open someone else's checklist.
        $selectedStudent = $request->student_id ? $students->firstWhere('id', (int) $request->student_id) : null;

        $evaluationNumber = $request->evaluation ?? 1;
        $ageGroup = $request->query('age_group', '3-5');
        abort_unless(in_array($ageGroup, ['0-3', '3-5'], true), 404);
        $domains = $ageGroup === '0-3' ? $this->domains0To3 : $this->domains;
        $indicators = $ageGroup === '0-3' ? $this->indicators0To3 : $this->indicators;

        $records = collect();
        $scores = [];

        if ($selectedStudent) {
            $existingRecords = EccdRecord::where('student_id', $selectedStudent->id)
                ->where('assessed_by', $teacherId)
                ->where('age_group', $ageGroup)
                ->get();

            if ($existingRecords->isEmpty()) {
                $now = now();
                $newRecords = [];
                foreach ($domains as $domain) {
                    foreach ($indicators[$domain] as $item) {
                        $indicator = $ageGroup === '0-3' ? $item['indicator'] : $item;
                        $newRecords[] = [
                            'student_id' => $selectedStudent->id,
                            'domain' => $domain,
                            'indicator' => $indicator,
                            'age_group' => $ageGroup,
                            'age_band' => $ageGroup === '0-3' ? $item['age_band'] : null,
                            'status' => 'Not Yet',
                            'assessed_by' => $teacherId,
                            'assessed_at' => Carbon::today()->toDateString(),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
                EccdRecord::insert($newRecords);
                $existingRecords = EccdRecord::where('student_id', $selectedStudent->id)
                    ->where('assessed_by', $teacherId)
                    ->where('age_group', $ageGroup)
                    ->get();
            }

            $records = $existingRecords->groupBy('domain');

            foreach ($domains as $domain) {
                $domainRecords = $records->get($domain, collect());
                $achieved = $domainRecords->where('status', 'Achieved')->count();
                $total = max(count($indicators[$domain]), 1);
                $scores[$domain] = round(($achieved / $total) * 100);
            }


        }

        $recommendations = $this->generateExpertRecommendations($scores);

        $labels = ['domains' => [], 'indicators' => []];
        foreach ($domains as $domain) {
            $labels['domains'][$domain] = __('eccd.domain_' . $this->domainKey($domain));
            foreach ($indicators[$domain] as $i => $item) {
                $indicator = $ageGroup === '0-3' ? $item['indicator'] : $item;
                $labels['indicators'][$indicator] = $ageGroup === '0-3' ? $indicator : __('eccd.' . $this->domainKey($domain) . '_' . $i);
            }
        }

        return view('teacher.eccd', compact(
            'students', 'selectedStudent', 'records',
            'scores', 'evaluationNumber', 'recommendations', 'labels', 'ageGroup'
        ) + ['domains' => $domains]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'record_id' => 'required|integer|exists:eccd_records,id',
            'status' => 'required|in:Not Yet,In Progress,Achieved',
            'notes' => 'nullable|string',
            'age_group' => 'required|in:0-3,3-5',
        ]);

        $student = Student::where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->findOrFail($validated['student_id']);

        $record = EccdRecord::where('student_id', $student->id)
            ->where('assessed_by', auth()->id())
            ->where('age_group', $validated['age_group'])
            ->findOrFail($validated['record_id']);

        $record->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $record->notes,
            'assessed_at' => Carbon::today(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $record->status,
                'updated_at' => $record->assessed_at?->toDateString(),
            ]);
        }

        return redirect()->route('teacher.eccd.index', [
            'student_id' => $student->id,
            'evaluation' => $request->evaluation,
            'age_group' => $validated['age_group'],
        ])->with('success', __('eccd.flash_updated'));
    }

    /**
     * Saves every indicator the teacher changed on the checklist in one go.
     * The page only stages changes until this is called, so nothing is written
     * to a child's record before the teacher confirms.
     *
     * `next` is where to go afterwards (the link they were leaving through);
     * it is only honoured for a same-site path.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'statuses' => 'nullable|array',
            'statuses.*' => 'in:Not Yet,In Progress,Achieved',
            'evaluation' => 'nullable',
            'next' => 'nullable|string|max:2048',
            'age_group' => 'required|in:0-3,3-5',
        ]);

        $teacherId = auth()->id();

        $student = Student::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->findOrFail($validated['student_id']);

        $statuses = collect($validated['statuses'] ?? [])
            ->filter(fn ($status, $recordId) => ctype_digit((string) $recordId));

        $changed = 0;

        if ($statuses->isNotEmpty()) {
            // Only this teacher's records for this child can be touched, so a
            // tampered id is ignored rather than written to someone else's.
            $records = EccdRecord::where('student_id', $student->id)
                ->where('assessed_by', $teacherId)
                ->where('age_group', $validated['age_group'])
                ->whereIn('id', $statuses->keys()->map(fn ($id) => (int) $id)->all())
                ->get();

            DB::transaction(function () use ($records, $statuses, &$changed) {
                foreach ($records as $record) {
                    $status = $statuses[$record->id] ?? $statuses[(string) $record->id] ?? null;

                    if ($status === null || $status === $record->status) {
                        continue;
                    }

                    $record->update(['status' => $status, 'assessed_at' => Carbon::today()]);
                    $changed++;
                }
            });
        }

        $message = match (true) {
            $changed === 0 => __('There were no changes to save.'),
            $changed === 1 => __('1 change saved to :name checklist.', ['name' => $student->name.'’s']),
            default => __(':count changes saved to :name checklist.', ['count' => $changed, 'name' => $student->name.'’s']),
        };

        $next = (string) ($validated['next'] ?? '');
        $safeNext = str_starts_with($next, '/') && ! str_starts_with($next, '//') && ! str_contains($next, '\\');

        $redirect = $safeNext
            ? redirect($next)
            : redirect()->route('teacher.eccd.index', array_filter([
                'student_id' => $student->id,
                'evaluation' => $validated['evaluation'] ?? null,
                'age_group' => $validated['age_group'],
            ]));

        return $redirect->with('success', $message);
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
