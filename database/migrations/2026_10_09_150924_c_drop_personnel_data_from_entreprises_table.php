<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * personnel_data (JSON) est remplace par la table relationnelle
     * entreprise_personnels, qui permet d'y rattacher des pieces jointes
     * et plusieurs "CV" distincts par personne. Aucune donnee de
     * production n'existe encore sur cette colonne (fonctionnalite ajoutee
     * et jamais deployee dans la meme session).
     */
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            if (Schema::hasColumn('entreprises', 'personnel_data')) {
                $table->dropColumn('personnel_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            if (!Schema::hasColumn('entreprises', 'personnel_data')) {
                $table->json('personnel_data')->nullable()->after('finance_data');
            }
        });
    }
};
