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
        Schema::create('formulaire_per_fichiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulaire_per_id')->constrained('formulaire_pers')->onDelete('cascade');
            $table->string('chemin_fichier');
            $table->string('nom_original')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulaire_per_fichiers');
    }
};
