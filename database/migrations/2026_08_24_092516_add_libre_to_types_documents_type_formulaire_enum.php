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
        // Syntaxe ENUM/MODIFY propre a MySQL : sans objet sur SQLite (utilise en
        // local pour les tests), qui ne contraint pas les valeurs d'une colonne
        // texte de la meme facon.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE types_documents MODIFY type_formulaire ENUM('formulaire', 'fichier', 'bordereau', 'libre') NOT NULL DEFAULT 'formulaire'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE types_documents MODIFY type_formulaire ENUM('formulaire', 'fichier', 'bordereau') NOT NULL DEFAULT 'formulaire'");
        }
    }
};
