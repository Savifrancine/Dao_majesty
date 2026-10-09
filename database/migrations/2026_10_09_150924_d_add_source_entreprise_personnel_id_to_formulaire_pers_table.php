<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Garde la trace de quel "CV" (entreprise_personnels) est actuellement
     * applique sur ce Formulaire PER, pour afficher lequel est en cours
     * d'utilisation quand plusieurs CV portent le meme nom.
     */
    public function up(): void
    {
        Schema::table('formulaire_pers', function (Blueprint $table) {
            $table->foreignId('source_entreprise_personnel_id')->nullable()->after('personnel_position')->constrained('entreprise_personnels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('formulaire_pers', function (Blueprint $table) {
            $table->dropForeign(['source_entreprise_personnel_id']);
            $table->dropColumn('source_entreprise_personnel_id');
        });
    }
};
