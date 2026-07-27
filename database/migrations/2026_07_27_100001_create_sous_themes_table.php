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
        Schema::create('sous_themes', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->unique();
            $table->string('libelle');
            $table->text('article')->nullable();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->boolean('permet_signalement')->default(false);
            $table->timestamps();

            // PostgreSQL n'indexe pas la colonne referencante d'une FK.
            $table->index('theme_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sous_themes');
    }
};
