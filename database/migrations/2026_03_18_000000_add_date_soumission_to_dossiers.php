<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                if (!Schema::hasColumn('dossiers', 'date_soumission')) {
                    $table->date('date_soumission')->nullable()->after('date_lancement');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                if (Schema::hasColumn('dossiers', 'date_soumission')) {
                    $table->dropColumn('date_soumission');
                }
            });
        }
    }
};
