<?php

namespace App\Filament\Exports;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class SurveyExporter extends Exporter
{
    protected static ?string $model = Survey::class;

    public static function getColumns(): array
    {
        $columns = [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('event.name')
                ->label('Event'),
            ExportColumn::make('event.location')
                ->label('Location'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('age')
                ->label('Age'),
            ExportColumn::make('gender')
                ->label('Gender'),
            ExportColumn::make('job_title')
                ->label('Job Title'),
            ExportColumn::make('drink_time')
                ->label('Drink Time'),
            ExportColumn::make('drink_place')
                ->label('Drink Place')
                ->formatStateUsing(fn ($state) => self::formatList($state)),
            ExportColumn::make('drink_whom')
                ->label('Drink With'),
            ExportColumn::make('choose_reason')
                ->label('Choose Reason')
                ->formatStateUsing(fn ($state) => self::formatList($state)),
            ExportColumn::make('drink_meal_important')
                ->label('Meal Pairing Importance'),
            ExportColumn::make('drink_meal_type')
                ->label('Meal Pairing Type')
                ->formatStateUsing(fn ($state) => self::formatList($state)),
            ExportColumn::make('drink_flavor')
                ->label('Drink Flavor')
                ->formatStateUsing(fn ($state) => self::formatList($state)),
            ExportColumn::make('has_spun')
                ->label('Has Spun')
                ->formatStateUsing(fn ($state) => $state ? 'yes' : 'no'),
            ExportColumn::make('prize')
                ->label('Prize'),
            ExportColumn::make('created_at')
                ->label('Submitted At')
                ->formatStateUsing(function ($state): string {
                    if ($state instanceof \DateTimeInterface) {
                        return $state->format('Y-m-d H:i:s');
                    }

                    return $state ? (string) $state : '';
                }),
        ];

        foreach (self::getExtraQuestionColumns() as $column) {
            $columns[] = $column;
        }

        return $columns;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successfulRows = number_format($export->successful_rows);
        $failedRows = number_format($export->failed_rows);

        return "Export completed. {$successfulRows} rows exported, {$failedRows} failed.";
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['event', 'answers.question']);
    }

    private static function formatList(mixed $state): string
    {
        if (is_array($state)) {
            return implode(', ', $state);
        }

        if (! is_string($state)) {
            return '';
        }

        $decoded = json_decode($state, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return implode(', ', $decoded);
        }

        return $state;
    }

    /**
     * @return array<int, ExportColumn>
     */
    private static function getExtraQuestionColumns(): array
    {
        if (! Schema::hasTable('survey_questions') || ! Schema::hasTable('survey_answers')) {
            return [];
        }

        $surveyKeys = self::getSurveyFieldKeys();

        return SurveyQuestion::query()
            ->orderBy('order')
            ->get()
            ->reject(fn (SurveyQuestion $question): bool => in_array($question->key, $surveyKeys, true))
            ->map(function (SurveyQuestion $question): ExportColumn {
                return ExportColumn::make("extra.{$question->key}")
                    ->label($question->label)
                    ->state(function (Survey $record) use ($question): string {
                        if (! $record->relationLoaded('answers')) {
                            return '';
                        }

                        $answer = $record->answers
                            ->firstWhere('survey_question_id', $question->id);

                        if (! $answer) {
                            return '';
                        }

                        return self::formatAnswerValue($answer->value);
                    })
                    ->enabledByDefault(false);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function getSurveyFieldKeys(): array
    {
        $fillable = (new Survey())->getFillable();

        return array_values(array_diff($fillable, ['has_spun', 'prize', 'event_id']));
    }

    private static function formatAnswerValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode(', ', $decoded);
            }
        }

        return $value !== null ? (string) $value : '';
    }
}
