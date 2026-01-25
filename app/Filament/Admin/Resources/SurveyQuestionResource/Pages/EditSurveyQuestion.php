<?php

namespace App\Filament\Admin\Resources\SurveyQuestionResource\Pages;

use App\Filament\Admin\Resources\SurveyQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSurveyQuestion extends EditRecord
{
    protected static string $resource = SurveyQuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
