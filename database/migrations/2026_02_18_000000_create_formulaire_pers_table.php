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
        Schema::create('formulaire_pers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('nom_candidat')->nullable();
            $table->string('poste')->nullable();
            $table->string('nom_personnel')->nullable();
            $table->date('date_naissance')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('nom_employeur')->nullable();
            $table->text('adresse_employeur')->nullable();
            $table->string('telephone')->nullable();
            $table->string('contact_personnel')->nullable();
            $table->string('telecopie')->nullable();
            $table->string('email')->nullable();
            $table->string('emploi_tenu')->nullable();
            $table->integer('nombre_annees_employeur')->nullable();
            $table->json('experiences')->nullable();
            $table->text('signature')->nullable();
            $table->date('date_signature')->nullable();
            $table->string('lieu_signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulaire_pers');
    }
};
