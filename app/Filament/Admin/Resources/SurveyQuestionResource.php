<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SurveyQuestionResource\Pages;
use App\Models\SurveyQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SurveyQuestionResource extends Resource
{
    protected static ?string $model = SurveyQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Survey Questions';

    protected static array $availableKeys = [
        'phone' => 'phone',
        'name' => 'name',
        'age' => 'age',
        'gender' => 'gender',
        'job_title' => 'job_title',
        'drink_time' => 'drink_time',
        'drink_place' => 'drink_place',
        'drink_whom' => 'drink_whom',
        'choose_reason' => 'choose_reason',
        'drink_meal_important' => 'drink_meal_important',
        'drink_meal_type' => 'drink_meal_type',
        'drink_flavor' => 'drink_flavor',
    ];

    public static function form(Form $form): Form
    {
        $keyList = implode(', ', array_keys(self::$availableKeys));

        return $form
            ->schema([
                Forms\Components\Section::make('Question')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(64)
                            ->regex('/^[a-z0-9_]+$/')
                            ->disabledOn('edit')
                            ->unique(ignoreRecord: true)
                            ->helperText("Built-in keys: {$keyList}. Use a new key to store answers in the survey_answers table."),
                        Forms\Components\Textarea::make('label')
                            ->label('Question Text')
                            ->required()
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'select' => 'Select',
                                'radio' => 'Radio',
                                'checkbox' => 'Checkbox',
                            ])
                            ->required()
                            ->default('text')
                            ->reactive(),
                        Forms\Components\Repeater::make('options')
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->addActionLabel('Add option')
                            ->defaultItems(0)
                            ->columns(2)
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, $state): void {
                                if (! is_array($state)) {
                                    $component->state([]);
                                    return;
                                }

                                $normalized = [];
                                foreach ($state as $option) {
                                    if (is_array($option)) {
                                        $value = $option['value'] ?? null;
                                        $label = $option['label'] ?? $value;

                                        if ($value === null) {
                                            continue;
                                        }

                                        $normalized[] = [
                                            'value' => $value,
                                            'label' => $label,
                                        ];
                                    } else {
                                        $normalized[] = [
                                            'value' => $option,
                                            'label' => $option,
                                        ];
                                    }
                                }

                                $component->state($normalized);
                            })
                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['select', 'radio', 'checkbox'], true)),
                        Forms\Components\Toggle::make('is_required')
                            ->default(true),
                        Forms\Components\Toggle::make('has_other')
                            ->label('Has Other Option')
                            ->default(false),
                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyQuestions::route('/'),
            'create' => Pages\CreateSurveyQuestion::route('/create'),
            'edit' => Pages\EditSurveyQuestion::route('/{record}/edit'),
        ];
    }
}
