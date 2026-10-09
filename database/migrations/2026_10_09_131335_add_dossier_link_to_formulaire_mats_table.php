<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulaire_mats', function (Blueprint $table) {
            if (!Schema::hasColumn('formulaire_mats', 'dossier_id')) {
                $table->unsignedBigInteger('dossier_id')->nullable()->index()->after('utilisateur_id');
            }
            if (!Schema::hasColumn('formulaire_mats', 'materiel_position')) {
                $table->unsignedSmallInteger('materiel_position')->nullable()->after('dossier_id');
            }
        });

        Schema::table('formulaire_mats', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_mats', 'dossier_id')) {
                $table->foreign('dossier_id')->references('id')->on('dossiers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('formulaire_mats', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_mats', 'dossier_id')) {
                $table->dropForeign(['dossier_id']);
                $table->dropColumn('dossier_id');
            }
            if (Schema::hasColumn('formulaire_mats', 'materiel_position')) {
                $table->dropColumn('materiel_position');
            }
        });
    }
};
