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
        // Table nommee « medias » et non « media » : le modele Media porte un
        // $table explicite, l'inflecteur Laravel rendant « media » au singulier.
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('chemin');
            $table->enum('type', ['image', 'video', 'audio', 'document']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
