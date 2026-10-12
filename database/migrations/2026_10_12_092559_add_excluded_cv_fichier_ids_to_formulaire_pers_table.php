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
        Schema::table('formulaire_pers', function (Blueprint $table) {
            $table->json('excluded_cv_fichier_ids')->nullable()->after('source_entreprise_personnel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formulaire_pers', function (Blueprint $table) {
            $table->dropColumn('excluded_cv_fichier_ids');
        });
    }
};
