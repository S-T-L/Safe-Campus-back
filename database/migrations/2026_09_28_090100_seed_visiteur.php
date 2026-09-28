<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Compte visiteur unique, provisionne au migrate dans tous les
 * environnements (y compris production) — c'est le compte que le front
 * utilise pour authentifier l'acces public au site. Identifiants pilotes
 * par VISITEUR_EMAIL / VISITEUR_PASSWORD, lus via config/visiteur.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        $email = config('visiteur.email');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = config('visiteur.password');

        if (empty($password)) {
            throw new \RuntimeException('VISITEUR_PASSWORD manquant : a definir dans .env avant migrate (min. 14 caracteres).');
        }

        User::factory()->create([
            'name' => 'Visiteur',
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Visiteur,
        ]);
    }

    public function down(): void
    {
        User::where('email', config('visiteur.email'))->delete();
    }
};
