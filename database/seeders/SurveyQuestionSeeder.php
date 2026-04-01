<?php

namespace Database\Seeders;

use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SurveyQuestionSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('survey_questions') || ! Schema::hasTable('survey_question_options')) {
            return;
        }

        $questions = [
            [
                'key' => 'phone',
                'label' => 'Phone',
                'type' => 'text',
                'is_required' => true,
                'has_other' => false,
                'order' => 1,
                'is_active' => true,
                'options' => [],
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'is_required' => true,
                'has_other' => false,
                'order' => 2,
                'is_active' => true,
                'options' => [],
            ],
            [
                'key' => 'age',
                'label' => 'Age Group',
                'type' => 'select',
                'is_required' => true,
                'has_other' => false,
                'order' => 3,
                'is_active' => true,
                'options' => [
                    ['value' => '18-24', 'label' => '18-24'],
                    ['value' => '25-29', 'label' => '25-29'],
                    ['value' => '30-35', 'label' => '30-35'],
                    ['value' => '35+', 'label' => '35+'],
                ],
            ],
            [
                'key' => 'gender',
                'label' => 'Gender',
                'type' => 'radio',
                'is_required' => true,
                'has_other' => false,
                'order' => 4,
                'is_active' => true,
                'options' => [
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                    ['value' => 'prefer_no', 'label' => 'Prefer not to say'],
                ],
            ],
            [
                'key' => 'job_title',
                'label' => 'Occupation',
                'type' => 'select',
                'is_required' => true,
                'has_other' => true,
                'order' => 5,
                'is_active' => true,
                'options' => [
                    ['value' => 'student', 'label' => 'Student'],
                    ['value' => 'office', 'label' => 'Office worker'],
                    ['value' => 'business', 'label' => 'Business owner'],
                    ['value' => 'freelancer', 'label' => 'Freelancer'],
                ],
            ],
            [
                'key' => 'drink_time',
                'label' => 'How often do you drink?',
                'type' => 'radio',
                'is_required' => true,
                'has_other' => false,
                'order' => 6,
                'is_active' => true,
                'options' => [
                    ['value' => 'daily', 'label' => 'Daily'],
                    ['value' => 'oneormore', 'label' => 'Once or more per week'],
                    ['value' => 'two-three', 'label' => 'Two to three times per week'],
                    ['value' => 'monthly', 'label' => 'A few times per month'],
                    ['value' => 'special', 'label' => 'Special occasions only'],
                ],
            ],
            [
                'key' => 'drink_place',
                'label' => 'Where do you usually drink?',
                'type' => 'checkbox',
                'is_required' => true,
                'has_other' => false,
                'order' => 7,
                'is_active' => true,
                'options' => [
                    ['value' => 'home', 'label' => 'Home'],
                    ['value' => 'bar', 'label' => 'Bar'],
                    ['value' => 'restaurant', 'label' => 'Restaurant'],
                    ['value' => 'korean_bbq', 'label' => 'Korean BBQ'],
                    ['value' => 'staycation', 'label' => 'Staycation'],
                ],
            ],
            [
                'key' => 'drink_whom',
                'label' => 'Who do you usually drink with?',
                'type' => 'radio',
                'is_required' => true,
                'has_other' => false,
                'order' => 8,
                'is_active' => true,
                'options' => [
                    ['value' => 'friends', 'label' => 'Friends'],
                    ['value' => 'family', 'label' => 'Family'],
                    ['value' => 'colleagues', 'label' => 'Colleagues'],
                    ['value' => 'alone', 'label' => 'Alone'],
                ],
            ],
            [
                'key' => 'choose_reason',
                'label' => 'Why do you choose this brand?',
                'type' => 'checkbox',
                'is_required' => true,
                'has_other' => false,
                'order' => 9,
                'is_active' => true,
                'options' => [
                    ['value' => 'taste', 'label' => 'Taste'],
                    ['value' => 'alcohol', 'label' => 'Alcohol content'],
                    ['value' => 'price', 'label' => 'Price'],
                    ['value' => 'packaging', 'label' => 'Packaging'],
                    ['value' => 'brand_reputation', 'label' => 'Brand reputation'],
                    ['value' => 'availability', 'label' => 'Availability'],
                ],
            ],
            [
                'key' => 'drink_meal_important',
                'label' => 'How important is food pairing?',
                'type' => 'radio',
                'is_required' => true,
                'has_other' => false,
                'order' => 10,
                'is_active' => true,
                'options' => [
                    ['value' => 'very', 'label' => 'Very important'],
                    ['value' => 'somewhat', 'label' => 'Somewhat important'],
                    ['value' => 'not_important', 'label' => 'Not important'],
                ],
            ],
            [
                'key' => 'drink_meal_type',
                'label' => 'What food pairs well with your drink?',
                'type' => 'checkbox',
                'is_required' => true,
                'has_other' => true,
                'order' => 11,
                'is_active' => true,
                'options' => [
                    ['value' => 'korean_bbq', 'label' => 'Korean BBQ'],
                    ['value' => 'spicy', 'label' => 'Spicy dishes'],
                    ['value' => 'fried', 'label' => 'Fried food'],
                ],
            ],
            [
                'key' => 'drink_flavor',
                'label' => 'Which flavors do you prefer?',
                'type' => 'checkbox',
                'is_required' => true,
                'has_other' => false,
                'order' => 12,
                'is_active' => true,
                'options' => [
                    ['value' => 'fresh', 'label' => 'Fresh'],
                    ['value' => 'peach', 'label' => 'Peach'],
                    ['value' => 'yogurt', 'label' => 'Yogurt'],
                    ['value' => 'blueberry', 'label' => 'Blueberry'],
                    ['value' => 'grapefruit', 'label' => 'Grapefruit'],
                    ['value' => 'strawberry', 'label' => 'Strawberry'],
                    ['value' => 'green_grape', 'label' => 'Green Grape'],
                    ['value' => 'lychee', 'label' => 'Lychee'],
                ],
            ],
        ];

        foreach ($questions as $question) {
            $optionPairs = $question['options'] ?? [];
            unset($question['options']);

            $record = SurveyQuestion::updateOrCreate(
                ['key' => $question['key']],
                $question,
            );

            $record->questionOptions()->delete();

            $rows = [];
            foreach ($optionPairs as $index => $option) {
                $rows[] = [
                    'value' => (string) $option['value'],
                    'label' => (string) ($option['label'] ?? $option['value']),
                    'order' => $index + 1,
                    'is_active' => true,
                ];
            }

            if (! empty($rows)) {
                $record->questionOptions()->createMany($rows);
            }
        }
    }
}
