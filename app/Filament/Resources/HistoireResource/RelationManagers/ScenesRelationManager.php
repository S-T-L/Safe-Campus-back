<?php

namespace App\Filament\Resources\HistoireResource\RelationManagers;

use App\Enums\ChoixIssue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Gere les scenes d'une histoire : creation de nouvelles scenes, ou
 * rattachement d'une scene existante (reutilisation entre histoires, voir
 * docs/schema_bd.md § Scene et partage entre histoires). Les choix d'une
 * scene se gerent ici meme, en repeater imbrique, comme les telephones d'un
 * contact (cf. ContactResource).
 *
 * Invariant non verifie ici (a faire cote application, voir doc) : toute
 * scene atteignable depuis la scene initiale d'une histoire doit rester liee
 * a cette histoire.
 */
class ScenesRelationManager extends RelationManager
{
    protected static string $relationship = 'scenes';

    protected static ?string $title = 'Scènes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('titre')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Libellé court, back-office uniquement — jamais affiché au joueur.'),
                        Forms\Components\TextInput::make('ordre')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Rang de la scène dans cette histoire, back-office uniquement.'),
                    ]),
                Forms\Components\Toggle::make('est_initiale')
                    ->label('Scène initiale')
                    ->helperText('Point de départ du parcours. Une seule scène initiale par histoire.'),
                Forms\Components\Textarea::make('dialogue_text')
                    ->label('Dialogue')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('sous_theme_id')
                            ->label('Sous-thème (bifurcation)')
                            ->relationship('sousTheme', 'libelle')
                            ->searchable()
                            ->native(false)
                            ->helperText('À renseigner seulement si cette scène bascule vers une autre thématique.'),
                        Forms\Components\Select::make('media_id')
                            ->label('Média')
                            ->relationship('media', 'libelle')
                            ->searchable()
                            ->native(false)
                            ->helperText('Image ou fond illustrant la scène, optionnel.'),
                    ]),
                Forms\Components\Repeater::make('choix')
                    ->relationship()
                    ->label('Choix')
                    ->defaultItems(0)
                    ->schema([
                        Forms\Components\TextInput::make('text_choix')
                            ->label('Texte du choix')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('next_scene_id')
                                    ->label('Scène suivante')
                                    ->relationship('nextScene', 'titre')
                                    ->searchable()
                                    ->native(false)
                                    ->helperText('Vide = sortie du parcours.'),
                                Forms\Components\Select::make('issue')
                                    ->label('Issue')
                                    ->options(array_combine(
                                        array_map(fn (ChoixIssue $issue) => $issue->value, ChoixIssue::cases()),
                                        array_map(fn (ChoixIssue $issue) => $issue->libelle(), ChoixIssue::cases()),
                                    ))
                                    ->native(false)
                                    ->helperText('Renseignée seulement si le choix termine le parcours.'),
                            ]),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['text_choix'] ?? null)
                    ->addActionLabel('Ajouter un choix')
                    ->collapsed(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titre')
            ->recordAction(Tables\Actions\EditAction::class)
            ->defaultSort('histoire_scene.ordre')
            ->columns([
                Tables\Columns\TextColumn::make('pivot.ordre')
                    ->label('Ordre'),
                Tables\Columns\TextColumn::make('titre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dialogue_text')
                    ->label('Dialogue')
                    ->limit(50),
                Tables\Columns\IconColumn::make('pivot.est_initiale')
                    ->label('Initiale')
                    ->boolean(),
                Tables\Columns\TextColumn::make('choix_count')
                    ->label('Choix')
                    ->counts('choix'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordTitleAttribute('titre')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('ordre')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('est_initiale')
                            ->label('Scène initiale'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
