<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marches_executes', function (Blueprint $table) {
            $table->string('reference_marche')->nullable()->after('mois_annee_fin');
        });
    }

    public function down(): void
    {
        Schema::table('marches_executes', function (Blueprint $table) {
            $table->dropColumn('reference_marche');
        });
    }
};
