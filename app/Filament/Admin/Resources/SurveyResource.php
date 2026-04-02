<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SurveyResource\Pages;
use App\Filament\Exports\SurveyExporter;
use App\Models\Event;
use App\Models\Survey;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SurveyResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Survey::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static bool $shouldRegisterNavigation = true;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'export',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Participant')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Spin Results')
                    ->schema([
                        Forms\Components\Toggle::make('has_spun')
                            ->label('Has Spun')
                            ->default(false),
                        Forms\Components\TextInput::make('prize')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('event.location')
                    ->label('Location')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_spun')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prize')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(SurveyExporter::class)
                    ->visible(fn () => auth()->user()?->can('export_survey')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('location')
                    ->label('Location')
                    ->options(function (): array {
                        return Event::query()
                            ->whereNotNull('location')
                            ->select('location')
                            ->distinct()
                            ->orderBy('location')
                            ->pluck('location')
                            ->mapWithKeys(fn (string $location): array => [$location => ucfirst($location)])
                            ->all();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $location = $data['value'] ?? null;

                        if (! filled($location)) {
                            return $query;
                        }

                        return $query->whereHas('event', fn (Builder $eventQuery): Builder => $eventQuery->where('location', $location));
                    }),
                Tables\Filters\TernaryFilter::make('has_spun'),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveys::route('/'),
            'create' => Pages\CreateSurvey::route('/create'),
            'edit' => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }
}
