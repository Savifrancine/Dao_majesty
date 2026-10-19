<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_technique_fichiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_document_id')->constrained('dossier_documents')->onDelete('cascade');
            // Position de l'article dans le tableau "Description technique des
            // fournitures/services" du MEME dossier (0-based) - pas une cle
            // etrangere vers bordereau_lignes.id, car ces lignes sont
            // entierement supprimees et recreees a chaque enregistrement de ce
            // tableau (voir DossierController::descriptionTechniqueArticles()).
            $table->unsignedInteger('article_index');
            $table->string('chemin_fichier');
            $table->string('nom_original')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();

            $table->unique(['dossier_document_id', 'article_index'], 'fiche_tech_fichiers_doc_article_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_technique_fichiers');
    }
};
