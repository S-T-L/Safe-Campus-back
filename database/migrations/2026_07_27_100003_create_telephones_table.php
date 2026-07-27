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
        Schema::create('telephones', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->enum('type', ['mobile', 'fixe', 'sms', 'urgence']);
            $table->string('libelle')->nullable();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // PostgreSQL n'indexe pas la colonne referencante d'une FK.
            $table->index('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telephones');
    }
};
