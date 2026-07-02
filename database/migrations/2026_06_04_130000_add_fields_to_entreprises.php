<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->text('adresse_officielle')->nullable()->after('adresse');
            $table->string('annee_enregistrement')->nullable()->after('registre_path');
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn(['adresse_officielle', 'annee_enregistrement']);
        });
    }
};
