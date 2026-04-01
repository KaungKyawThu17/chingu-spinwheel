<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SurveyResponse;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class TopFlavorsByLocationWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.top-flavors-by-location-widget';

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    protected string $location = '';

    protected string $heading = '';

    protected int $limit = 3;

    protected function getViewData(): array
    {
        return [
            'heading' => $this->heading !== '' ? $this->heading : ucfirst($this->location),
            'rows' => $this->getTopFlavors(),
        ];
    }

    /**
     * @return array<int, array{flavor: string, count: int}>
     */
    protected function getTopFlavors(): array
    {
        if (
            $this->location === '' ||
            ! Schema::hasTable('surveys') ||
            ! Schema::hasTable('survey_responses') ||
            ! Schema::hasTable('survey_questions') ||
            ! Schema::hasTable('events') ||
            ! Schema::hasColumn('surveys', 'event_id')
        ) {
            return [];
        }

        $counts = [];

        SurveyResponse::query()
            ->select(['id', 'value'])
            ->whereHas('question', fn ($query) => $query->where('key', 'drink_flavor'))
            ->whereHas('survey.event', fn ($query) => $query->where('location', $this->location))
            ->chunkById(200, function ($responses) use (&$counts): void {
                foreach ($responses as $response) {
                    foreach ($this->normalizeFlavors($response->value) as $flavor) {
                        $label = trim((string) $flavor);

                        if ($label === '') {
                            continue;
                        }

                        $counts[$label] = ($counts[$label] ?? 0) + 1;
                    }
                }
            });

        if (empty($counts)) {
            return [];
        }

        arsort($counts);

        $rows = [];
        foreach (array_slice($counts, 0, $this->limit, true) as $flavor => $count) {
            $rows[] = [
                'flavor' => $flavor,
                'count' => $count,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeFlavors(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return [(string) $value];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded)) {
                return $decoded;
            }

            if (is_string($decoded)) {
                return [$decoded];
            }
        }

        return [$value];
    }
}
