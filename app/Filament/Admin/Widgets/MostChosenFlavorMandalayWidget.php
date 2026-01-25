<?php

namespace App\Filament\Admin\Widgets;

class MostChosenFlavorMandalayWidget extends TopFlavorsByLocationWidget
{
    protected static bool $isDiscovered = true;

    protected static ?int $sort = 3;

    protected string $location = 'mandalay';

    protected string $heading = 'Mandalay';
}
