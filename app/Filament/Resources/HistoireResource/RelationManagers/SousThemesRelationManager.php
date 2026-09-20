<?php

namespace App\Filament\Resources\HistoireResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Attache/detache des sous-themes existants a une histoire. Une histoire peut
 * porter plusieurs sous-themes (ex. alcool + violences_sexuelles) — voir
 * docs/schema_bd.md § Histoire.
 */
class SousThemesRelationManager extends RelationManager
{
    protected static string $relationship = 'sousThemes';

    protected static ?string $title = 'Sous-thèmes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle')
            ->columns([
                Tables\Columns\TextColumn::make('libelle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme.libelle')
                    ->label('Thème'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordTitleAttribute('libelle'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
