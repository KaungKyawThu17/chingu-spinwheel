<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SurveyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_survey_submit_validates_required_phone_field(): void
    {
        $this->createActiveEvent();
        $this->createQuestion('phone', 'Phone', 1);
        $this->createQuestion('name', 'Name', 2);

        $response = $this
            ->from(route('survey.form'))
            ->post(route('survey.submit'), [
                'name' => 'Alice',
                'location' => 'yangon',
            ]);

        $response->assertRedirect(route('survey.form'));
        $response->assertSessionHasErrors(['phone']);
    }

    public function test_spin_endpoint_allows_only_one_successful_spin_per_survey(): void
    {
        $event = $this->createActiveEvent();
        $survey = Survey::create([
            'name' => 'Spin User',
            'phone' => '959111111111',
            'event_id' => $event->id,
        ]);

        $session = [
            'survey_phone' => $survey->phone,
            'survey_event_id' => $event->id,
        ];

        $firstResponse = $this->withSession($session)
            ->postJson(route('survey.spin.process'));

        $firstResponse
            ->assertOk()
            ->assertJson(['success' => true]);

        $survey->refresh();
        $this->assertTrue((bool) $survey->has_spun);
        $this->assertNotNull($survey->prize);

        $secondResponse = $this->withSession($session)
            ->postJson(route('survey.spin.process'));

        $secondResponse
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You have already spun the wheel.',
            ]);
    }

    public function test_survey_submit_endpoint_is_throttled(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post(route('survey.submit'), []);
        }

        $blockedResponse = $this->post(route('survey.submit'), []);

        $blockedResponse->assertStatus(429);
    }

    public function test_dynamic_questions_store_other_values_in_survey_responses(): void
    {
        $this->createActiveEvent();
        $this->createQuestion('phone', 'Phone', 1);
        $this->createQuestion('name', 'Name', 2);

        $jobTitle = $this->createQuestion(
            key: 'job_title',
            label: 'Job Title',
            order: 3,
            type: 'select',
            hasOther: true,
            options: [
                ['value' => 'student', 'label' => 'Student'],
            ],
        );

        $mealType = $this->createQuestion(
            key: 'drink_meal_type',
            label: 'Meal Type',
            order: 4,
            type: 'checkbox',
            hasOther: true,
            options: [
                ['value' => 'spicy', 'label' => 'Spicy'],
            ],
        );

        $response = $this->post(route('survey.submit'), [
            'location' => 'yangon',
            'phone' => '09111111111',
            'name' => 'Alice',
            'job_title' => 'other',
            'job_title_other' => 'Designer',
            'drink_meal_type' => ['spicy', 'other'],
            'drink_meal_type_other' => 'Street Food',
        ]);

        $response->assertRedirect(route('survey.spin'));

        $survey = Survey::query()->firstOrFail();
        $this->assertSame('959111111111', $survey->phone);

        $jobTitleResponse = SurveyResponse::query()
            ->where('survey_id', $survey->id)
            ->where('survey_question_id', $jobTitle->id)
            ->firstOrFail();
        $this->assertSame('Designer', $jobTitleResponse->value);

        $mealTypeResponse = SurveyResponse::query()
            ->where('survey_id', $survey->id)
            ->where('survey_question_id', $mealType->id)
            ->firstOrFail();
        $this->assertSame(['spicy', 'Street Food'], $mealTypeResponse->value);
    }

    private function createActiveEvent(): Event
    {
        return Event::create([
            'name' => 'Test Event',
            'location' => 'yangon',
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);
    }

    private function createQuestion(
        string $key,
        string $label,
        int $order,
        string $type = 'text',
        bool $hasOther = false,
        bool $isRequired = true,
        array $options = [],
    ): SurveyQuestion
    {
        $question = SurveyQuestion::create([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'is_required' => $isRequired,
            'has_other' => $hasOther,
            'order' => $order,
            'is_active' => true,
        ]);

        if (! empty($options)) {
            $rows = [];
            foreach ($options as $index => $option) {
                $rows[] = [
                    'value' => (string) $option['value'],
                    'label' => (string) ($option['label'] ?? $option['value']),
                    'order' => $index + 1,
                    'is_active' => true,
                ];
            }

            $question->questionOptions()->createMany($rows);
        }

        return $question;
    }
}
