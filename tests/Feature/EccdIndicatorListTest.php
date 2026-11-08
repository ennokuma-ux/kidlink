<?php

namespace Tests\Feature;

use App\Http\Controllers\Teacher\EccdController;
use App\Models\EccdRecord;
use App\Models\Student;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class EccdIndicatorListTest extends TestCase
{
    use RefreshDatabase;

    /** The official list: seven domains, 109 indicators in total. */
    private const EXPECTED_COUNTS = [
        'Gross Motor' => 13,
        'Fine Motor' => 11,
        'Self-Help' => 27,
        'Receptive Language' => 5,
        'Expressive Language' => 8,
        'Cognitive' => 21,
        'Social-Emotional' => 24,
    ];

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->teacher = User::create([
            'first_name' => 'Teach', 'last_name' => 'Er',
            'email' => 'teacher@test.local', 'password' => Hash::make('password123'),
            'role' => 'teacher', 'is_active' => true,
        ]);
    }

    private function makeStudent(string $first): Student
    {
        return Student::create([
            'first_name' => $first, 'last_name' => 'Test', 'sex' => 'Female',
            'date_of_birth' => Student::dateOfBirthForSection('1-3 years old'),
            'class_name' => '1-3 years old', 'teacher_id' => $this->teacher->id,
            'is_active' => true, 'enrolled_date' => now(),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function controllerIndicators(): array
    {
        $property = new ReflectionProperty(EccdController::class, 'indicators');
        $property->setAccessible(true);

        return $property->getValue(new EccdController());
    }

    private function controllerDomainKey(string $domain): string
    {
        $method = new ReflectionMethod(EccdController::class, 'domainKey');
        $method->setAccessible(true);

        return $method->invoke(new EccdController(), $domain);
    }

    public function test_the_domain_list_is_the_official_seven(): void
    {
        $this->assertSame([
            'Gross Motor',
            'Fine Motor',
            'Self-Help',
            'Receptive Language',
            'Expressive Language',
            'Cognitive',
            'Social-Emotional',
        ], AnalyticsService::ECCD_DOMAINS);
    }

    public function test_the_teacher_checklist_uses_the_same_domains_as_analytics(): void
    {
        $property = new ReflectionProperty(EccdController::class, 'domains');
        $property->setAccessible(true);

        $this->assertSame(AnalyticsService::ECCD_DOMAINS, $property->getValue(new EccdController()));
    }

    public function test_every_domain_has_the_official_number_of_indicators(): void
    {
        $indicators = $this->controllerIndicators();

        $this->assertSame(AnalyticsService::ECCD_DOMAINS, array_keys($indicators));

        $counts = array_map('count', $indicators);
        $this->assertSame(self::EXPECTED_COUNTS, $counts);
        $this->assertSame(109, array_sum($counts));
    }

    public function test_every_indicator_resolves_a_label_in_every_language(): void
    {
        $originalLocale = config('app.locale');

        foreach (['en', 'fil', 'hil'] as $locale) {
            app('translator')->setLocale($locale);

            foreach ($this->controllerIndicators() as $domain => $indicators) {
                $domainKey = 'eccd.domain_' . $this->controllerDomainKey($domain);
                $this->assertNotSame($domainKey, __($domainKey), "Missing {$domainKey} in {$locale}");
                $this->assertNotSame('', trim(__($domainKey)), "Blank {$domainKey} in {$locale}");

                foreach ($indicators as $i => $indicator) {
                    $key = 'eccd.' . $this->controllerDomainKey($domain) . '_' . $i;
                    $label = __($key);
                    $this->assertNotSame($key, $label, "Missing {$key} in {$locale}");
                    $this->assertNotSame('', trim($label), "Blank {$key} in {$locale}");
                }
            }
        }

        app('translator')->setLocale($originalLocale);
    }

    public function test_the_list_carries_no_markdown_or_numbering_leftovers(): void
    {
        foreach ($this->controllerIndicators() as $domain => $indicators) {
            $this->assertStringNotContainsString('#', $domain);
            $this->assertDoesNotMatchRegularExpression('/^\d+\./', $domain);

            foreach ($indicators as $indicator) {
                $this->assertStringNotContainsString('#', $indicator);
                $this->assertDoesNotMatchRegularExpression('/^\d+\./', $indicator);
            }
        }

        $originalLocale = config('app.locale');

        foreach (['en', 'fil', 'hil'] as $locale) {
            app('translator')->setLocale($locale);

            foreach ($this->controllerIndicators() as $domain => $indicators) {
                $labels = array_merge(
                    [__('eccd.domain_' . $this->controllerDomainKey($domain))],
                    array_map(fn ($i) => __('eccd.' . $this->controllerDomainKey($domain) . '_' . $i), array_keys($indicators))
                );

                foreach ($labels as $label) {
                    $this->assertStringNotContainsString('#', $label);
                    $this->assertDoesNotMatchRegularExpression('/^\d+\./', $label);
                }
            }
        }

        app('translator')->setLocale($originalLocale);
    }

    public function test_filipino_and_hiligaynon_labels_are_really_translated(): void
    {
        $originalLocale = config('app.locale');

        $samples = ['gross_motor_0', 'self_help_26', 'cognitive_12', 'social_emotional_23'];

        foreach ($samples as $key) {
            app('translator')->setLocale('en');
            $english = __('eccd.' . $key);

            app('translator')->setLocale('fil');
            $filipino = __('eccd.' . $key);

            app('translator')->setLocale('hil');
            $hiligaynon = __('eccd.' . $key);

            $this->assertNotSame($english, $filipino, "fil.{$key} is still English");
            $this->assertNotSame($english, $hiligaynon, "hil.{$key} is still English");
            $this->assertNotSame($filipino, $hiligaynon, "fil.{$key} and hil.{$key} are identical");
        }

        app('translator')->setLocale($originalLocale);
    }

    public function test_social_emotional_records_pass_the_domain_constraint(): void
    {
        $student = $this->makeStudent('Ana');

        EccdRecord::create([
            'student_id' => $student->id,
            'domain' => 'Social-Emotional',
            'indicator' => 'Waits for his turn',
            'status' => 'Not Yet',
            'assessed_by' => $this->teacher->id,
            'assessed_at' => now(),
        ]);

        $this->assertSame('Social-Emotional', EccdRecord::first()->domain);
    }

    public function test_opening_the_checklist_builds_the_full_109_row_list(): void
    {
        $this->makeStudent('Ana');

        $this->actingAs($this->teacher)->get('/teacher/eccd')->assertRedirect();

        $this->assertSame(109, EccdRecord::count());

        foreach (self::EXPECTED_COUNTS as $domain => $count) {
            $this->assertSame($count, EccdRecord::where('domain', $domain)->count(), $domain);
        }

        $response = $this->actingAs($this->teacher)->get('/teacher/eccd');

        $response->assertOk();
        $response->assertSee('Social-Emotional');
        $response->assertSee('Plays peek-a-boo (bulaga)');
        $response->assertSee('Dances patterns/joins group movement activities');
    }
}
