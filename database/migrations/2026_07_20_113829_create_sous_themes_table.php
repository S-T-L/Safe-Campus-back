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
            $table->string('nom');
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->boolean('permet_signalement')->default(false);
            $table->timestamps();
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
