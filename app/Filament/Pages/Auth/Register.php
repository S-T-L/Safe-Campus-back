<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\DB;

/**
 * Demande de compte au back-office. Le compte est cree sans role : il reste
 * inactif (connexion refusee) jusqu'a ce qu'un admin lui en attribue un.
 * Contrairement a la page Filament d'origine, l'inscrit n'est pas connecte.
 */
class Register extends BaseRegister
{
    /**
     * Mobile ou fixe caledonien : 6 chiffres commencant par 2 a 9, prefixe
     * +687 / 00687 facultatif. Espaces, points et tirets toleres a la saisie.
     */
    public const TELEPHONE_NC = '/^(?:(?:\+|00)687)?[2-9]\d{5}$/';

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $data = $this->form->getState();

            return $this->handleRegistration($data);
        });

        event(new Registered($user));

        Notification::make()
            ->title('Demande de compte enregistrée')
            ->body('Votre compte ne sera actif qu\'après validation par un administrateur. Vous pourrez alors vous connecter.')
            ->success()
            ->persistent()
            ->send();

        $this->redirect(filament()->getLoginUrl());

        return null;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Grid::make(2)->schema([
                            $this->getPrenomFormComponent(),
                            $this->getNomFormComponent(),
                        ]),
                        $this->getEmailFormComponent(),
                        $this->getTelephoneFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getPrenomFormComponent(): Component
    {
        return TextInput::make('prenom')
            ->label('Prénom')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getNomFormComponent(): Component
    {
        return TextInput::make('nom')
            ->label('Nom')
            ->required()
            ->maxLength(255);
    }

    /**
     * Email stocke en minuscules, unicite verifiee sans tenir compte de la
     * casse : PostgreSQL compare les chaines a l'identique, `->unique()`
     * laisserait passer JEAN@test.nc a cote de jean@test.nc.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->dehydrateStateUsing(fn (string $state): string => mb_strtolower(trim($state)))
            ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                if (! is_string($value)) {
                    $fail('L\'adresse email est invalide.');

                    return;
                }

                $existe = DB::table('users')
                    ->whereRaw('lower(email) = ?', [mb_strtolower(trim($value))])
                    ->exists();

                if ($existe) {
                    $fail('Cette adresse email est déjà utilisée.');
                }
            });
    }

    protected function getTelephoneFormComponent(): Component
    {
        return TextInput::make('telephone')
            ->label('Téléphone')
            ->tel()
            ->required()
            ->placeholder('ex. 77 12 34')
            ->helperText('Numéro calédonien à 6 chiffres, +687 facultatif.')
            ->dehydrateStateUsing(fn (string $state): string => self::normaliserTelephone($state))
            ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                // is_string : une requete Livewire forgee peut envoyer un tableau.
                if (! is_string($value) || ! preg_match(self::TELEPHONE_NC, self::nettoyerTelephone($value))) {
                    $fail('Le numéro doit être un numéro calédonien à 6 chiffres.');
                }
            });
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->helperText('14 caractères minimum, dont une majuscule, une minuscule et un caractère spécial.');
    }

    /**
     * Format de stockage E.164 : +687XXXXXX.
     */
    public static function normaliserTelephone(string $saisie): string
    {
        return '+687'.substr(self::nettoyerTelephone($saisie), -6);
    }

    private static function nettoyerTelephone(string $saisie): string
    {
        return preg_replace('/[\s.\-]/', '', $saisie);
    }
}
