<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entreprise_personnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->string('intitule')->nullable();
            $table->string('nom');
            $table->string('poste')->nullable();
            $table->date('date_naissance')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('nom_employeur')->nullable();
            $table->string('emploi_tenu')->nullable();
            $table->text('adresse_employeur')->nullable();
            $table->string('telephone')->nullable();
            $table->string('telecopie')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_personnel')->nullable();
            $table->unsignedInteger('nombre_annees_employeur')->nullable();
            $table->json('experiences')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entreprise_personnels');
    }
};
