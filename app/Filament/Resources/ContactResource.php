<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers\SousThemesRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\TelephonesRelationManager;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Annuaire';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ref')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Clé de dédoublonnage du seeder. Non modifiable après création.')
                    ->disabled(fn (?Contact $record) => $record !== null)
                    ->dehydrated(fn (?Contact $record) => $record === null),
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prenom')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mail')
                    ->maxLength(255),
                Forms\Components\TextInput::make('localisation')
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->helperText('Coordonnée GPS. Vide tant que l\'adresse n\'est pas géocodée — la carte du front reste masquée sans elle.'),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\TextInput::make('site_web')
                    ->maxLength(255),
                Forms\Components\TextInput::make('horaires')
                    ->maxLength(255),
                Forms\Components\Textarea::make('remarques')
                    ->helperText('Public visé et conditions d\'accueil (ex. "réservé aux moins de 25 ans").')
                    ->columnSpanFull(),
                Forms\Components\Select::make('gratuit')
                    ->label('Gratuit')
                    ->options(['1' => 'Oui', '0' => 'Non'])
                    ->helperText('Non renseigné = inconnu, distinct de "Non".')
                    ->native(false),
                Forms\Components\Select::make('anonyme')
                    ->label('Anonyme')
                    ->options(['1' => 'Oui', '0' => 'Non'])
                    ->helperText('Non renseigné = inconnu, distinct de "Non".')
                    ->native(false),
                Forms\Components\Toggle::make('actif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ref')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('localisation')
                    ->searchable(),
                Tables\Columns\IconColumn::make('latitude')
                    ->label('Géocodé')
                    ->boolean()
                    ->getStateUsing(fn (Contact $record) => $record->latitude !== null && $record->longitude !== null)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('site_web')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('horaires')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('gratuit')
                    ->boolean(),
                Tables\Columns\IconColumn::make('anonyme')
                    ->boolean(),
                Tables\Columns\IconColumn::make('actif')
                    ->boolean(),
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
                Tables\Filters\TernaryFilter::make('actif'),
            ])
            ->actions([
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
            TelephonesRelationManager::class,
            SousThemesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
