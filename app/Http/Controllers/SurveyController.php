<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Prize;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * @var array<int, array{name: string, weight: int, color: string}>
     */
    private const DEFAULT_WHEEL_PRIZES = [
        ['name' => 'Sticker Pack', 'weight' => 60, 'color' => '#55B9E6'],
        ['name' => 'Fans', 'weight' => 30, 'color' => '#F7B7C4'],
        ['name' => 'Charm', 'weight' => 10, 'color' => '#52B848'],
    ];

    public function showSurveyForm(Request $request)
    {
        $location = $request->query('location') ?: 'yangon';

        return view('survey', [
            'activeEvent' => $this->getActiveEvent($location),
            'location' => $location,
            'questions' => $this->getSurveyQuestions(),
        ]);
    }

    // Show spin wheel
    public function spinWheel()
    {
        return view('spinwheel', [
            'wheelPrizes' => $this->getWheelPrizes(),
        ]);
    }
    //
    public function submitSurvey(Request $request)
    {
        $location = $request->input('location') ?: $request->query('location');
        $location = $location ?: 'yangon';
        $activeEvent = $this->getActiveEvent($location);

        if (! $activeEvent) {
            return back()
                ->withErrors(['event' => 'No active event is available right now.'])
                ->withInput();
        }

        $questions = $this->getSurveyQuestions();
        if ($questions->isEmpty()) {
            return back()
                ->withErrors(['survey' => 'Survey questions are not configured yet.'])
                ->withInput();
        }

        return $this->submitDynamicSurvey($request, $questions, $activeEvent);
    }

    private function submitDynamicSurvey(Request $request, Collection $questions, Event $activeEvent)
    {
        if ($questions->contains('key', 'phone')) {
            $request->merge([
                'phone' => preg_replace('/\D/', '', (string) $request->input('phone')),
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            $this->buildDynamicRules($questions),
            $this->buildDynamicMessages()
        );

        $this->applyOtherValidation($validator, $questions, $request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $data = $this->buildSurveyData($questions, $validated);
        $survey = Survey::create([
            ...$data,
            'event_id' => $activeEvent->id,
        ]);
        $this->storeSurveyResponses($survey, $questions, $validated);

        session([
            'survey_phone' => $survey->phone,
            'survey_event_id' => $survey->event_id,
        ]);

        return redirect()->route('survey.spin');
    }


    public function processSpin(Request $request): JsonResponse
    {
        $phone = session('survey_phone');
        $eventId = session('survey_event_id');

        if (!$phone || !$eventId) {
            return response()->json([
                'success' => false,
                'message' => 'Survey session not found. Please fill the survey first.',
            ], 400);
        }

        $survey = Survey::query()
            ->where('phone', $phone)
            ->where('event_id', $eventId)
            ->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey record not found for this event.',
            ], 404);
        }

        // LIMIT: 1 spin per phone/event. Use an atomic conditional update to prevent race conditions.
        $wheelPrizes = $this->getWheelPrizes();
        $prize = $this->pickPrize($wheelPrizes);
        $updated = Survey::query()
            ->whereKey($survey->id)
            ->where('has_spun', false)
            ->update([
                'has_spun' => true,
                'prize' => $prize['name'],
            ]);

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'You have already spun the wheel.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'prize'   => $prize['name'],
            'segment' => $prize['index'],
            'segmentCount' => count($wheelPrizes),
        ]);
    }

    /**
     * @param array<int, array{name: string, weight: int, color: string}> $prizes
     * @return array{name: string, index: int}
     */
    private function pickPrize(array $prizes): array
    {
        $totalWeight = array_sum(array_column($prizes, 'weight'));

        if ($totalWeight <= 0) {
            return [
                'name' => $prizes[0]['name'],
                'index' => 0,
            ];
        }

        $rand = random_int(1, $totalWeight); // secure & fair
        $running = 0;

        foreach ($prizes as $index => $prize) {
            $running += $prize['weight'];
            if ($rand <= $running) {
                // Return both name and index
                return [
                    'name'  => $prize['name'],
                    'index' => $index,
                ];
            }
        }

        // Fallback (should never happen)
        return [
            'name'  => $prizes[0]['name'],
            'index' => 0,
        ];
    }

    /**
     * @return array<int, array{name: string, weight: int, color: string}>
     */
    private function getWheelPrizes(): array
    {
        if (! Schema::hasTable('prizes')) {
            return self::DEFAULT_WHEEL_PRIZES;
        }

        $prizes = Prize::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get(['name', 'weight', 'color'])
            ->map(function (Prize $prize, int $index): array {
                return [
                    'name' => trim((string) $prize->name),
                    'weight' => max(0, (int) $prize->weight),
                    'color' => $this->normalizePrizeColor($prize->color, $index),
                ];
            })
            ->filter(fn (array $prize): bool => $prize['name'] !== '' && $prize['weight'] > 0)
            ->values()
            ->all();

        return ! empty($prizes) ? $prizes : self::DEFAULT_WHEEL_PRIZES;
    }

    private function normalizePrizeColor(?string $color, int $index): string
    {
        $value = strtoupper(trim((string) $color));

        if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
            return $value;
        }

        return self::DEFAULT_WHEEL_PRIZES[$index % count(self::DEFAULT_WHEEL_PRIZES)]['color'];
    }

    private function getSurveyQuestions(): Collection
    {
        if (! Schema::hasTable('survey_questions')) {
            return collect();
        }

        return SurveyQuestion::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->with(['questionOptions' => fn ($query) => $query->where('is_active', true)->orderBy('order')])
            ->get();
    }

    private function getActiveEvent(?string $location = null): ?Event
    {
        if (! Schema::hasTable('events')) {
            return null;
        }

        $today = now()->toDateString();

        return Event::query()
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', $today)
            ->whereDate('ends_at', '>=', $today)
            ->when($location, fn ($query) => $query->where('location', $location))
            ->orderByDesc('starts_at')
            ->first();
    }

    private function buildDynamicRules(Collection $questions): array
    {
        $rules = [];

        foreach ($questions as $question) {
            $key = $question->key;
            $requiredRule = $question->is_required ? 'required' : 'nullable';
            $options = $this->normalizeOptions($question);

            if ($question->type === 'checkbox') {
                $rules[$key] = array_filter([$requiredRule, 'array', $question->is_required ? 'min:1' : null]);

                $itemRules = ['string'];
                if (! empty($options)) {
                    $itemRules[] = Rule::in($options);
                }

                $rules["{$key}.*"] = $itemRules;
            } else {
                $fieldRules = [$requiredRule, 'string', 'max:255'];
                if (in_array($question->type, ['select', 'radio'], true) && ! empty($options)) {
                    $fieldRules[] = Rule::in($options);
                }

                if ($key === 'phone') {
                    $fieldRules[] = 'regex:#^(09|959)[0-9]{7,9}$#';
                }

                $rules[$key] = $fieldRules;
            }

            if ($question->has_other) {
                $rules["{$key}_other"] = ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }

    private function buildDynamicMessages(): array
    {
        return [
            'phone.required' => 'Please enter a phone number.',
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }

    private function applyOtherValidation($validator, Collection $questions, Request $request): void
    {
        $validator->after(function ($validator) use ($questions, $request): void {
            foreach ($questions as $question) {
                if (! $question->has_other) {
                    continue;
                }

                $key = $question->key;
                $otherKey = "{$key}_other";

                if ($question->type === 'checkbox') {
                    $values = (array) $request->input($key, []);

                    if (in_array('other', $values, true) && blank($request->input($otherKey))) {
                        $validator->errors()->add($otherKey, 'Please specify the other option.');
                    }
                } else {
                    if ($request->input($key) === 'other' && blank($request->input($otherKey))) {
                        $validator->errors()->add($otherKey, 'Please specify the other option.');
                    }
                }
            }
        });
    }

    private function buildSurveyData(Collection $questions, array $validated): array
    {
        $data = [];
        $surveyKeys = $this->getSurveyFieldKeys();

        foreach ($questions as $question) {
            $key = $question->key;

            if (! in_array($key, $surveyKeys, true)) {
                continue;
            }

            $value = $this->resolveQuestionValue($question, $validated);

            if ($value === null) {
                continue;
            }

            if ($question->type === 'checkbox') {
                $data[$key] = json_encode(array_values((array) $value));
            } else {
                $data[$key] = $value;
            }
        }

        if (isset($data['phone']) && str_starts_with($data['phone'], '09')) {
            $data['phone'] = '959' . substr($data['phone'], 2);
        }

        return $this->applySurveyDefaults($data);
    }

    private function normalizeOptions(SurveyQuestion $question): array
    {
        $options = array_map(
            static fn (array $option): string => $option['value'],
            $question->option_pairs
        );

        if ($question->has_other) {
            $options[] = 'other';
        }

        return $options;
    }

    private function resolveQuestionValue(SurveyQuestion $question, array $validated): array | string | null
    {
        $key = $question->key;

        if (! array_key_exists($key, $validated)) {
            return null;
        }

        if ($question->type === 'checkbox') {
            $values = is_array($validated[$key]) ? $validated[$key] : [];

            if ($question->has_other) {
                $values = $this->mergeOtherValue($values, $validated["{$key}_other"] ?? null);
            }

            return array_values($values);
        }

        $value = $validated[$key];

        if ($question->has_other && $value === 'other') {
            return $validated["{$key}_other"] ?? '';
        }

        return $value;
    }

    private function mergeOtherValue(array $values, ?string $other): array
    {
        $values = array_values(array_filter($values, static fn (string $value): bool => $value !== 'other'));

        if (filled($other)) {
            $values[] = $other;
        }

        return $values;
    }

    private function storeSurveyResponses(Survey $survey, Collection $questions, array $validated): void
    {
        $surveyKeys = $this->getSurveyFieldKeys();

        foreach ($questions as $question) {
            $key = $question->key;

            if (in_array($key, $surveyKeys, true)) {
                continue;
            }

            $value = $this->resolveQuestionValue($question, $validated);

            if ($value === null) {
                continue;
            }

            SurveyResponse::create([
                'survey_id' => $survey->id,
                'survey_question_id' => $question->id,
                'value' => $value,
            ]);
        }
    }

    private function getSurveyFieldKeys(): array
    {
        $fillable = (new Survey())->getFillable();

        return array_values(array_diff($fillable, ['has_spun', 'prize', 'event_id']));
    }

    private function applySurveyDefaults(array $data): array
    {
        $defaults = [
            'name' => '',
            'phone' => '',
        ];

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
