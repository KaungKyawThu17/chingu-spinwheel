<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Event;
use App\Models\Survey;
use Illuminate\Support\Facades\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class EventUniquePhonesWidget extends TableWidget
{
    protected static ?string $heading = 'Unique Phones per Event';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Event::query()->orderByDesc('starts_at'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('End')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unique_phones')
                    ->label('Unique Phones')
                    ->getStateUsing(fn (Event $record): int => $this->countUniquePhones($record)),
            ]);
    }

    protected function countUniquePhones(Event $event): int
    {
        if (Schema::hasColumn('surveys', 'event_id')) {
            return Survey::query()
                ->where('event_id', $event->id)
                ->distinct('phone')
                ->count('phone');
        }

        if (! $event->starts_at || ! $event->ends_at) {
            return 0;
        }

        $start = $event->starts_at->copy()->startOfDay();
        $end = $event->ends_at->copy()->endOfDay();

        return Survey::query()
            ->whereBetween('created_at', [$start, $end])
            ->distinct('phone')
            ->count('phone');
    }
}
