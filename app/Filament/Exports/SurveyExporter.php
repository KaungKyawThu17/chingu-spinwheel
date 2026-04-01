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

    /**
     * @var array<string, string>
     */
    private const DEFAULT_QUESTION_KEYS = [
        'age' => 'Age',
        'gender' => 'Gender',
        'job_title' => 'Job Title',
        'drink_time' => 'Drink Time',
        'drink_place' => 'Drink Place',
        'drink_whom' => 'Drink With',
        'choose_reason' => 'Choose Reason',
        'drink_meal_important' => 'Meal Pairing Importance',
        'drink_meal_type' => 'Meal Pairing Type',
        'drink_flavor' => 'Drink Flavor',
    ];

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
        ];

        foreach (self::DEFAULT_QUESTION_KEYS as $key => $label) {
            $columns[] = self::makeQuestionColumn($key, $label, true);
        }

        $columns[] = ExportColumn::make('has_spun')
            ->label('Has Spun')
            ->formatStateUsing(fn ($state) => $state ? 'yes' : 'no');

        $columns[] = ExportColumn::make('prize')
            ->label('Prize');

        $columns[] = ExportColumn::make('created_at')
            ->label('Submitted At')
            ->formatStateUsing(function ($state): string {
                if ($state instanceof \DateTimeInterface) {
                    return $state->format('Y-m-d H:i:s');
                }

                return $state ? (string) $state : '';
            });

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
        return $query->with(['event', 'responses.question']);
    }

    /**
     * @return array<int, ExportColumn>
     */
    private static function getExtraQuestionColumns(): array
    {
        if (! Schema::hasTable('survey_questions') || ! Schema::hasTable('survey_responses')) {
            return [];
        }

        $skipKeys = [
            ...self::getSurveyFieldKeys(),
            ...array_keys(self::DEFAULT_QUESTION_KEYS),
        ];

        return SurveyQuestion::query()
            ->orderBy('order')
            ->get()
            ->reject(fn (SurveyQuestion $question): bool => in_array($question->key, $skipKeys, true))
            ->map(fn (SurveyQuestion $question): ExportColumn => self::makeQuestionColumn($question->key, $question->label, false))
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

    private static function makeQuestionColumn(string $key, string $label, bool $enabledByDefault): ExportColumn
    {
        return ExportColumn::make("question.{$key}")
            ->label($label)
            ->state(fn (Survey $record): string => self::answerByKey($record, $key))
            ->enabledByDefault($enabledByDefault);
    }

    private static function answerByKey(Survey $record, string $key): string
    {
        if (! $record->relationLoaded('responses')) {
            return '';
        }

        $response = $record->responses->first(function ($response) use ($key) {
            return $response->question?->key === $key;
        });

        if (! $response) {
            return '';
        }

        return self::formatAnswerValue($response->value);
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
