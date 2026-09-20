<?php

namespace App\Filament\Resources;

use App\Enums\EtatHistoire;
use App\Filament\Resources\HistoireResource\Pages;
use App\Filament\Resources\HistoireResource\RelationManagers\ScenesRelationManager;
use App\Filament\Resources\HistoireResource\RelationManagers\SousThemesRelationManager;
use App\Models\Histoire;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HistoireResource extends Resource
{
    protected static ?string $model = Histoire::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('titre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('etat')
                            ->options(array_combine(
                                array_map(fn (EtatHistoire $etat) => $etat->value, EtatHistoire::cases()),
                                array_map(fn (EtatHistoire $etat) => $etat->libelle(), EtatHistoire::cases()),
                            ))
                            ->required()
                            ->native(false)
                            ->default(EtatHistoire::Brouillon->value)
                            ->helperText('Circuit editorial : brouillon -> relecture -> validé -> publié.'),
                    ]),
                Forms\Components\Select::make('user_id')
                    ->label('Auteur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->native(false)
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ref')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('etat')
                    ->badge()
                    ->formatStateUsing(fn (EtatHistoire $state) => $state->libelle())
                    ->color(fn (EtatHistoire $state): string => match ($state) {
                        EtatHistoire::Brouillon => 'gray',
                        EtatHistoire::Relecture => 'warning',
                        EtatHistoire::Valide => 'info',
                        EtatHistoire::Publie => 'success',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Auteur'),
                Tables\Columns\TextColumn::make('scenes_count')
                    ->label('Scènes')
                    ->counts('scenes'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('etat')
                    ->options(array_combine(
                        array_map(fn (EtatHistoire $etat) => $etat->value, EtatHistoire::cases()),
                        array_map(fn (EtatHistoire $etat) => $etat->libelle(), EtatHistoire::cases()),
                    )),
            ])
            ->actions([
                Tables\Actions\Action::make('previsualiser')
                    ->label('Prévisualiser')
                    ->icon('heroicon-o-play')
                    ->color('gray')
                    ->url(fn (Histoire $record): string => static::getUrl('preview', ['record' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ScenesRelationManager::class,
            SousThemesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistoires::route('/'),
            'create' => Pages\CreateHistoire::route('/create'),
            'edit' => Pages\EditHistoire::route('/{record}/edit'),
            'preview' => Pages\PreviewHistoire::route('/{record}/preview'),
        ];
    }
}
