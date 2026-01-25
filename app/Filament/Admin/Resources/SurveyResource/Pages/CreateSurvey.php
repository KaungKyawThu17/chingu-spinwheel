<?php

namespace App\Filament\Admin\Resources\SurveyResource\Pages;

use App\Filament\Admin\Resources\SurveyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSurvey extends CreateRecord
{
    protected static string $resource = SurveyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
