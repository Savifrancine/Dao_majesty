<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('dossier_utilisateurs')) {
            Schema::create('dossier_utilisateurs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
                $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_utilisateurs');
    }
};
