<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('documents_textes')) {
            Schema::create('documents_textes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dossier_document_id')->constrained('dossier_documents')->onDelete('cascade');
                $table->longText('contenu')->nullable();
                $table->foreignId('utilisateur_id')->constrained('utilisateurs');
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents_textes');
    }
};
