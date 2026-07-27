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
        Schema::create('contact_sous_theme', function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sous_theme_id')->constrained()->cascadeOnDelete();

            // Priorite editoriale d'affichage : contacts universitaires en tete.
            $table->unsignedInteger('ordre')->default(0);

            $table->primary(['contact_id', 'sous_theme_id']);

            // La PK composite couvre deja les acces par contact_id (prefixe gauche).
            $table->index('sous_theme_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_sous_theme');
    }
};
