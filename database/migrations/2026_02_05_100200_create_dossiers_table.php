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
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_dossier_id')->constrained('types_dossiers');
            $table->foreignId('entreprise_id')->constrained('entreprises');
            $table->string('nom_dossier');
            $table->text('objectif')->nullable();
            $table->string('lot')->nullable();
            $table->enum('public_prive', ['public', 'prive'])->default('public');
            $table->enum('statut', ['en_cours', 'termine', 'genere'])->default('en_cours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
