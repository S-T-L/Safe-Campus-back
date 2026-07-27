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
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();

            // Token libre (empreinte, cookie, IP hachee). Aucune FK vers users :
            // le signalement est anonyme par construction.
            $table->string('token_antispam');

            $table->foreignId('sous_theme_id')->constrained()->cascadeOnDelete();
            $table->text('texte');
            $table->timestamp('date_heure');
            $table->timestamps();

            // PostgreSQL n'indexe pas la colonne referencante d'une FK.
            $table->index('sous_theme_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
