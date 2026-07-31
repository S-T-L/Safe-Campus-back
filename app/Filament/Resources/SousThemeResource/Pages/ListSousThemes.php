<?php

namespace App\Filament\Resources\SousThemeResource\Pages;

use App\Filament\Resources\SousThemeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSousThemes extends ListRecords
{
    protected static string $resource = SousThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
