<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_pers', function (Blueprint $table) {
            if (!Schema::hasColumn('formulaire_pers', 'dossier_id')) {
                $table->unsignedBigInteger('dossier_id')->nullable()->index()->after('utilisateur_id');
            }
            if (!Schema::hasColumn('formulaire_pers', 'personnel_position')) {
                $table->unsignedSmallInteger('personnel_position')->nullable()->after('dossier_id');
            }
        });

        Schema::table('formulaire_pers', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_pers', 'dossier_id')) {
                $table->foreign('dossier_id')->references('id')->on('dossiers')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('formulaire_pers', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_pers', 'dossier_id')) {
                $table->dropForeign(['dossier_id']);
                $table->dropColumn('dossier_id');
            }
            if (Schema::hasColumn('formulaire_pers', 'personnel_position')) {
                $table->dropColumn('personnel_position');
            }
        });
    }
};
