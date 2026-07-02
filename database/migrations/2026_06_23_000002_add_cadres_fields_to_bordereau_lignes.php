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
            if (!Schema::hasColumn('bordereau_lignes', 'total_materiel')) {
                $table->string('total_materiel')->nullable()->after('unite_physique');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'location_amort')) {
                $table->string('location_amort')->nullable()->after('total_materiel');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'matiere_frais')) {
                $table->string('matiere_frais')->nullable()->after('location_amort');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'main_oeuvre')) {
                $table->string('main_oeuvre')->nullable()->after('matiere_frais');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'deborse_sec')) {
                $table->string('deborse_sec')->nullable()->after('main_oeuvre');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'coef_c1')) {
                $table->string('coef_c1')->nullable()->after('deborse_sec');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'coef_k')) {
                $table->string('coef_k')->nullable()->after('coef_c1');
            }
            if (!Schema::hasColumn('bordereau_lignes', 'prix_vente_htva')) {
                $table->string('prix_vente_htva')->nullable()->after('coef_k');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('bordereau_lignes', 'prix_vente_htva')) {
                $table->dropColumn('prix_vente_htva');
            }
            if (Schema::hasColumn('bordereau_lignes', 'coef_k')) {
                $table->dropColumn('coef_k');
            }
            if (Schema::hasColumn('bordereau_lignes', 'coef_c1')) {
                $table->dropColumn('coef_c1');
            }
            if (Schema::hasColumn('bordereau_lignes', 'deborse_sec')) {
                $table->dropColumn('deborse_sec');
            }
            if (Schema::hasColumn('bordereau_lignes', 'main_oeuvre')) {
                $table->dropColumn('main_oeuvre');
            }
            if (Schema::hasColumn('bordereau_lignes', 'matiere_frais')) {
                $table->dropColumn('matiere_frais');
            }
            if (Schema::hasColumn('bordereau_lignes', 'location_amort')) {
                $table->dropColumn('location_amort');
            }
            if (Schema::hasColumn('bordereau_lignes', 'total_materiel')) {
                $table->dropColumn('total_materiel');
            }
        });
    }
};
