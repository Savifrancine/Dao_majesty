<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE types_documents MODIFY type_formulaire ENUM('formulaire', 'fichier', 'bordereau', 'libre', 'piece_jointe') NOT NULL DEFAULT 'formulaire'");

        // Remarque : la valeur 'fichier' est utilisée de longue date pour de nombreux
        // documents legacy ayant leur propre rendu PDF dédié (Déclaration de garantie,
        // Formulaire de qualification, etc.), sans rapport avec la fonctionnalité
        // "Pièce jointe" des documents personnalisés. On ne migre donc PAS les données
        // existantes automatiquement ici (un correctif ponctuel a été appliqué en
        // production pour le seul document personnalisé concerné) ; les nouveaux
        // documents personnalisés de type pièce jointe utilisent désormais directement
        // 'piece_jointe' dès leur création.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('types_documents')
            ->where('type_formulaire', 'piece_jointe')
            ->update(['type_formulaire' => 'fichier']);

        DB::statement("ALTER TABLE types_documents MODIFY type_formulaire ENUM('formulaire', 'fichier', 'bordereau', 'libre') NOT NULL DEFAULT 'formulaire'");
    }
};
