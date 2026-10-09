<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trace quelle piece jointe "entreprise" (entreprise_personnel_fichiers)
     * a deja ete copiee vers ce dossier, pour ne jamais la recopier en
     * double a chaque resynchronisation ou nouvelle application d'un CV.
     */
    public function up(): void
    {
        Schema::table('formulaire_per_fichiers', function (Blueprint $table) {
            $table->foreignId('source_fichier_id')->nullable()->after('formulaire_per_id')->constrained('entreprise_personnel_fichiers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('formulaire_per_fichiers', function (Blueprint $table) {
            $table->dropForeign(['source_fichier_id']);
            $table->dropColumn('source_fichier_id');
        });
    }
};
