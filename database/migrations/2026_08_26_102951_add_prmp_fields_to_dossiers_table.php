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
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('prmp_titre')->nullable();
            $table->string('prmp_nom')->nullable();
            $table->string('prmp_telephone')->nullable();
            $table->string('prmp_email')->nullable();
            $table->string('institution_nom')->nullable();
            $table->text('secretariat_adresse')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn(['prmp_titre', 'prmp_nom', 'prmp_telephone', 'prmp_email', 'institution_nom', 'secretariat_adresse']);
        });
    }
};
