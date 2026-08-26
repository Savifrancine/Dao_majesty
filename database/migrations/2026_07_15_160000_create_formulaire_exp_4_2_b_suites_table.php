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
        Schema::create('formulaire_exp_4_2_b_suites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id')->index();
            $table->unsignedBigInteger('dossier_id')->nullable()->index();
            $table->unsignedBigInteger('entreprise_id')->nullable()->index();
            $table->unsignedBigInteger('signataire_id')->nullable()->index();
            $table->string('formulaire_type')->default('B_SUITE')->index();
            $table->string('nom_candidat')->nullable();
            $table->date('date_formulaire')->nullable();
            $table->string('numero_adrp')->nullable();
            $table->string('numero_marche')->nullable();
            $table->longText('description_similitude')->nullable();
            $table->string('montant')->nullable();
            $table->string('taille_physique')->nullable();
            $table->string('complexite')->nullable();
            $table->string('methodes_technologie')->nullable();
            $table->string('autres_caracteristiques')->nullable();
            $table->string('autorite_nom')->nullable();
            $table->longText('autorite_adresse')->nullable();
            $table->string('autorite_telephone')->nullable();
            $table->string('autorite_email')->nullable();
            $table->string('nom_signataire')->nullable();
            $table->string('fonction_signataire')->nullable();
            $table->string('lieu_fait')->nullable();
            $table->date('date_fait')->nullable();
            $table->timestamps();
            $table->foreign('utilisateur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
            $table->foreign('dossier_id')->references('id')->on('dossiers')->onDelete('set null');
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('set null');
            $table->foreign('signataire_id')->references('id')->on('signataires')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulaire_exp_4_2_b_suites');
    }
};
