<?php

namespace App\Filament\Resources\HistoireResource\Pages;

use App\Filament\Resources\HistoireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHistoire extends EditRecord
{
    protected static string $resource = HistoireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previsualiser')
                ->label('Prévisualiser')
                ->icon('heroicon-o-play')
                ->color('gray')
                ->url(fn (): string => HistoireResource::getUrl('preview', ['record' => $this->getRecord()])),
            Actions\DeleteAction::make(),
        ];
    }
}
