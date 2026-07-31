<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Compte webmaster de demonstration pour le dev local, provisionne au
 * migrate pour que chaque dev l'ait sans etape manuelle — WEBMASTER_DEMO_EMAIL
 * / WEBMASTER_DEMO_PASSWORD dans .env.example (identifiants bidon, commites
 * volontairement), lus via config/webmaster_demo.php. Ne s'execute jamais en
 * production.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $email = config('webmaster_demo.email');

        if (User::where('email', $email)->exists()) {
            return;
        }

        User::factory()->create([
            'name' => 'Webmaster (demo)',
            'email' => $email,
            'password' => config('webmaster_demo.password'),
            'role' => UserRole::Webmaster,
        ]);
    }

    public function down(): void
    {
        User::where('email', config('webmaster_demo.email'))->delete();
    }
};
