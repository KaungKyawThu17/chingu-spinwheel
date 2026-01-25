<?php

namespace App\Filament\Admin\Resources\SurveyQuestionResource\Pages;

use App\Filament\Admin\Resources\SurveyQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSurveyQuestion extends CreateRecord
{
    protected static string $resource = SurveyQuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
