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
            $table->dropColumn(['etiquette_complement_interne', 'etiquette_complement_externe']);
            $table->string('destinataire_adresse')->nullable()->after('destinataires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->text('etiquette_complement_interne')->nullable();
            $table->text('etiquette_complement_externe')->nullable();
            $table->dropColumn('destinataire_adresse');
        });
    }
};
