<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bordereau_lignes') && !Schema::hasColumn('bordereau_lignes', 'frequence')) {
            Schema::table('bordereau_lignes', function (Blueprint $table) {
                $table->string('frequence')->nullable()->after('date_prestation')->comment('Fréquence de réalisation du service');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bordereau_lignes') && Schema::hasColumn('bordereau_lignes', 'frequence')) {
            Schema::table('bordereau_lignes', function (Blueprint $table) {
                $table->dropColumn('frequence');
            });
        }
    }
};
