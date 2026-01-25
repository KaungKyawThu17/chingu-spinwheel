<?php

namespace App\Filament\Admin\Widgets;

class MostChosenFlavorWidget extends TopFlavorsByLocationWidget
{
    protected static bool $isDiscovered = true;

    protected static ?int $sort = 2;

    protected string $location = 'yangon';

    protected string $heading = 'Yangon';
}
