<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Refuser la demande')
                ->modalHeading('Refuser la demande')
                ->modalDescription('Le compte en attente sera supprimé. La personne pourra refaire une demande.')
                ->successNotificationTitle('Demande refusée'),
        ];
    }

    /**
     * Seul le role est modifiable : les champs d'identite sont desactives et
     * donc non envoyes, mais on filtre quand meme contre une requete forgee.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ['role' => $data['role'] ?? null];
    }
}
