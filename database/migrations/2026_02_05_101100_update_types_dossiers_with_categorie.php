<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter la colonne si elle n'existe pas
        if (!Schema::hasColumn('types_dossiers', 'categorie')) {
            Schema::table('types_dossiers', function (Blueprint $table) {
                $table->enum('categorie', ['public', 'prive'])->default('public')->after('nom');
            });
        }

        // Mettre à jour les types existants avec les bonnes catégories
        DB::table('types_dossiers')
            ->where('nom', 'DAO')
            ->update(['categorie' => 'public']);

        DB::table('types_dossiers')
            ->where('nom', 'DRP')
            ->update(['categorie' => 'public']);

        DB::table('types_dossiers')
            ->where('nom', 'Demande de cotation')
            ->update(['categorie' => 'public']);

        DB::table('types_dossiers')
            ->where('nom', 'Appel à manifestation d\'intérêt')
            ->update(['categorie' => 'prive']);

        DB::table('types_dossiers')
            ->where('nom', 'Consultation restreinte')
            ->update(['categorie' => 'prive']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('types_dossiers', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }
};
