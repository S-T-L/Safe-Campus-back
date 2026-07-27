<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->unique();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('mail')->nullable();
            $table->string('localisation')->nullable();
            $table->string('site_web')->nullable();
            $table->string('horaires')->nullable();
            $table->text('remarques')->nullable();

            // Nullable et non false : « inconnu » n'est pas « payant » ni « nominatif ».
            $table->boolean('gratuit')->nullable();
            $table->boolean('anonyme')->nullable();

            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
