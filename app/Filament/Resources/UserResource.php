<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    /**
     * Roles que l'admin peut attribuer. Visiteur exclu : compte technique de
     * l'API front, jamais un compte de personne.
     *
     * @return array<string, string>
     */
    public static function rolesAttribuables(): array
    {
        return collect([UserRole::Webmaster, UserRole::Redacteur, UserRole::Admin])
            ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->libelle()])
            ->all();
    }

    /**
     * Le compte visiteur n'apparait jamais dans la gestion des utilisateurs.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $query) => $query
                ->whereNull('role')
                ->orWhere('role', '!=', UserRole::Visiteur->value));
    }

    /**
     * Nombre de demandes d'inscription en attente, a cote de l'entree du menu.
     */
    public static function getNavigationBadge(): ?string
    {
        $enAttente = User::whereNull('role')->count();

        return $enAttente > 0 ? (string) $enAttente : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Demandes d\'inscription en attente';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')
                    ->description('Renseignée par la personne à l\'inscription, non modifiable ici.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('prenom')
                                    ->label('Prénom')
                                    ->disabled(),
                                Forms\Components\TextInput::make('nom')
                                    ->disabled(),
                                Forms\Components\TextInput::make('email')
                                    ->disabled(),
                                Forms\Components\TextInput::make('telephone')
                                    ->label('Téléphone')
                                    ->disabled(),
                            ]),
                    ]),
                Forms\Components\Select::make('role')
                    ->label('Rôle')
                    ->options(self::rolesAttribuables())
                    // Le Select n'impose pas ses options cote serveur : sans
                    // ce `in`, une requete forgee pourrait poser 'visiteur'.
                    ->in(array_keys(self::rolesAttribuables()))
                    ->placeholder('Aucun — accès refusé')
                    ->helperText('Sans rôle, le compte ne peut pas se connecter. Retirer le rôle coupe l\'accès sans supprimer le compte.')
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (?UserRole $state) => $state?->libelle())
                    ->placeholder('En attente')
                    ->color(fn (?UserRole $state) => match ($state) {
                        UserRole::Admin => 'danger',
                        null => 'warning',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscription')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('en_attente')
                    ->label('En attente de validation')
                    ->query(fn (Builder $query) => $query->whereNull('role')),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options(self::rolesAttribuables()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Refuser')
                    ->modalHeading('Refuser la demande')
                    ->modalDescription('Le compte en attente sera supprimé. La personne pourra refaire une demande.')
                    ->successNotificationTitle('Demande refusée'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
