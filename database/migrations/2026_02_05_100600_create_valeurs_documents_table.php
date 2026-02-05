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
        Schema::create('valeurs_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_document_id')->constrained('dossier_documents')->onDelete('cascade');
            $table->foreignId('champ_document_id')->constrained('champs_documents')->onDelete('cascade');
            $table->text('valeur')->nullable();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valeurs_documents');
    }
};
