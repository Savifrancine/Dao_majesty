<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            $table->decimal('transport', 15, 2)->nullable()->after('cout_benin');
            $table->string('cout_main_oeuvre_locale', 255)->nullable()->after('transport');
            $table->decimal('taxe_vente', 15, 2)->nullable()->after('cout_main_oeuvre_locale');
        });
    }

    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            $table->dropColumn(['transport', 'cout_main_oeuvre_locale', 'taxe_vente']);
        });
    }
};
