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
                if (!Schema::hasColumn('dossiers', 'type_offre')) {
                    $table->string('type_offre')->nullable();
                }
                if (Schema::hasColumn('dossiers', 'types_offres')) {
                    $table->dropColumn('types_offres');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                if (!Schema::hasColumn('dossiers', 'types_offres')) {
                    $table->string('types_offres')->nullable();
                }
                if (Schema::hasColumn('dossiers', 'type_offre')) {
                    $table->dropColumn('type_offre');
                }
            });
        }
    }
};
