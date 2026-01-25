<?php

namespace App\Filament\Admin\Resources\SurveyQuestionResource\Pages;

use App\Filament\Admin\Resources\SurveyQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyQuestions extends ListRecords
{
    protected static string $resource = SurveyQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
