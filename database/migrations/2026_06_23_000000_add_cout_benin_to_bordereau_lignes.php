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
            $table->decimal('cout_benin', 18, 2)->nullable()->after('montant')->comment('Coût main-d\'œuvre locale, matière premières et composants du Bénin/UEMOA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            $table->dropColumn('cout_benin');
        });
    }
};
