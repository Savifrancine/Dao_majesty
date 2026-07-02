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
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            if (!Schema::hasColumn('bordereau_lignes', 'unite_physique')) {
                $table->string('unite_physique')->nullable()->after('designation');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'site')) {
                $table->string('site')->nullable()->after('montant');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'date_prestation')) {
                $table->string('date_prestation')->nullable()->after('site');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('bordereau_lignes', 'date_prestation')) {
                $table->dropColumn('date_prestation');
            }
            if (Schema::hasColumn('bordereau_lignes', 'site')) {
                $table->dropColumn('site');
            }
            if (Schema::hasColumn('bordereau_lignes', 'unite_physique')) {
                $table->dropColumn('unite_physique');
            }
        });
    }
};
