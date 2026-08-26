<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_exp_4_2_as', function (Blueprint $table) {
            if (!Schema::hasColumn('formulaire_exp_4_2_as', 'dossier_id')) {
                $table->foreignId('dossier_id')->nullable()->constrained('dossiers')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('formulaire_exp_4_2_as', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_exp_4_2_as', 'dossier_id')) {
                $table->dropConstrainedForeignId('dossier_id');
            }
        });
    }
};
