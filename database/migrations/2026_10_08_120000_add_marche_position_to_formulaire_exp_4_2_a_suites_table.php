<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_exp_4_2_a_suites', function (Blueprint $table) {
            if (!Schema::hasColumn('formulaire_exp_4_2_a_suites', 'marche_position')) {
                $table->unsignedSmallInteger('marche_position')->nullable()->after('dossier_id');
            }
        });
    }

    public function down()
    {
        Schema::table('formulaire_exp_4_2_a_suites', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_exp_4_2_a_suites', 'marche_position')) {
                $table->dropColumn('marche_position');
            }
        });
    }
};
