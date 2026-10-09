<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entreprise_personnel_fichiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_personnel_id')->constrained('entreprise_personnels')->onDelete('cascade');
            $table->string('chemin_fichier');
            $table->string('nom_original')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entreprise_personnel_fichiers');
    }
};
