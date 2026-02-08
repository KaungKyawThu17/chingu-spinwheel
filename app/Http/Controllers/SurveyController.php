<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Survey;
use App\Models\SurveyAnswer;
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
        return view('spinwheel');
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

        if ($questions->isNotEmpty()) {
            return $this->submitDynamicSurvey($request, $questions, $activeEvent);
        }

        $request->merge([
            'phone' => preg_replace('/\D/', '', $request->phone)
        ]);

        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'phone'  => [
                'required',
                'regex:#^(09|959)[0-9]{7,9}$#',
            ],

            'age'    => 'required|string',
            'gender' => 'required|string',
            'job_title' => 'required|string',
            'job_title_other' => 'nullable|string|max:255',

            'drink_time' => 'required|string',

            // if drink_place is multiple, make it array; adjust if single
            'drink_place' => 'required|array',
            'drink_place.*' => 'string',


            'drink_whom' => 'required|string',

            // choose_reason (if multi-select, same array logic)
            'choose_reason' => 'required|array',
            'choose_reason.*' => 'string',


            'drink_meal_important' => 'required|string',

            'drink_meal_type' => 'required|array',
            'drink_meal_type.*' => 'string',

            // we’ll add custom rule for drink_meal_type_other in ->after()
            'drink_meal_type_other' => 'nullable|string|max:255',

            // if flavors are multiple, use array; if single, string
            'drink_flavor' => 'required|array',
            'drink_flavor.*' => 'string',

        ], [
            'phone.required' => 'ဖုန်းနံပါတ်ကို ဖြည့်ပါ။',
            'phone.regex' => 'ဖုန်းနံပါတ်သည် မြန်မာဖုန်းနံပါတ် format အတိုင်းမဟုတ်ပါ။',
        ]);

        // 🔴 Custom "other must be filled" rule here
        $validator->after(function ($validator) use ($request) {
            $types = (array) $request->input('drink_meal_type', []);

            if (in_array('other', $types)) {
                $other = $request->input('drink_meal_type_other');

                if (!$other || trim($other) === '') {
                    $validator->errors()->add(
                        'drink_meal_type_other',
                        'အခြားရွေးချယ်ပါက အကြောင်းအရာကို ဖြည့်ရန် လိုအပ်ပါသည်။'
                    );
                }
            }
        });

        $validator->after(function ($validator) use ($request) {
            if ($request->job_title === 'other') {
                if (!$request->job_title_other || trim($request->job_title_other) === '') {
                    $validator->errors()->add(
                        'job_title_other',
                        'အလုပ်အကိုင် Other ကိုရွေးချယ်ပါက ရေးထည့်ပေးရန် လိုအပ်သည်။'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $phone = $validated['phone'];

        if (str_starts_with($phone, '09')) {
            $phone = '959' . substr($phone, 2);
        }

        // Clean "other" out of drink_meal_type and replace with typed value
        $mealTypes = $validated['drink_meal_type'];

        // remove literal "other"
        $mealTypes = array_filter($mealTypes, fn($item) => $item !== 'other');

        // add user-typed other if exists
        if (!empty($validated['drink_meal_type_other'])) {
            $mealTypes[] = $validated['drink_meal_type_other'];
        }

        $finalJobTitle = $validated['job_title'] === 'other'
            ? $validated['job_title_other']
            : $validated['job_title'];


        // 🔽 your create, using $mealTypes instead of raw drink_meal_type
        $survey = Survey::create([
            'name'   => $validated['name'],
            'phone'  => $phone,
            'age'    => $validated['age'],
            'gender' => $validated['gender'],
            'job_title' => $finalJobTitle,
            'drink_time' => $validated['drink_time'],
            'drink_place' =>  json_encode($validated['drink_place']),
            'drink_whom' => $validated['drink_whom'],
            'choose_reason' =>  json_encode($validated['choose_reason']),
            'drink_meal_important' => $validated['drink_meal_important'],

            'drink_meal_type' => json_encode($mealTypes),
            'drink_meal_type_other' => $validated['drink_meal_type_other'] ?? null,

            'drink_flavor' => json_encode($validated['drink_flavor']),
            'event_id' => $activeEvent->id,
        ]);

        // keep your redirect to spin page etc.
        session([
            'survey_phone' => $survey->phone,
            'survey_event_id' => $survey->event_id,
        ]);

        return redirect()->route('survey.spin');
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
        $this->storeSurveyAnswers($survey, $questions, $validated);

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

        // LIMIT: 1 spin per phone
        if ($survey->has_spun) {
            return response()->json([
                'success' => false,
                'message' => 'You have already spun the wheel.',
            ], 403);
        }

        // ====== BACKEND WINNING RATIO (60/30/10) ======
        $prize = $this->pickPrize(); // Sticker Pack / Fans / Charm

        // Save result to DB
        $survey->update([
            'has_spun' => true,
            'prize'    => $prize['name'],
        ]);

        return response()->json([
            'success' => true,
            'prize'   => $prize['name'],
            'segment' => $prize['index'],
        ]);
    }

    private function pickPrize(): array
    {
        // ORDER HERE MUST MATCH FRONTEND 'segments' ARRAY
        $prizes = [
            ['name' => 'Sticker Pack', 'weight' => 60],
            ['name' => 'Fans',         'weight' => 30],
            ['name' => 'Charm',        'weight' => 10],
        ];

        $totalWeight = array_sum(array_column($prizes, 'weight'));
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

    private function getSurveyQuestions(): Collection
    {
        if (! Schema::hasTable('survey_questions')) {
            return collect();
        }

        return SurveyQuestion::query()
            ->where('is_active', true)
            ->orderBy('order')
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

    private function storeSurveyAnswers(Survey $survey, Collection $questions, array $validated): void
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

            SurveyAnswer::create([
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
            'age' => '',
            'gender' => '',
            'job_title' => '',
            'drink_time' => '',
            'drink_place' => json_encode([]),
            'drink_whom' => '',
            'choose_reason' => json_encode([]),
            'drink_meal_important' => '',
            'drink_meal_type' => json_encode([]),
            'drink_flavor' => json_encode([]),
        ];

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
