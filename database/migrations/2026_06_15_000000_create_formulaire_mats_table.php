<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('formulaire_mats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id');
            $table->string('piece_materiel');
            $table->string('fabricant')->nullable();
            $table->string('modele_puissance')->nullable();
            $table->string('capacite')->nullable();
            $table->string('annee_fabrication')->nullable();
            $table->string('localisation')->nullable();
            $table->text('engagements')->nullable();
            $table->string('provenance')->nullable();
            $table->string('nom_signataire')->nullable();
            $table->string('lieu_fait')->nullable();
            $table->date('date_fait')->nullable();
            $table->timestamps();

            $table->foreign('utilisateur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('formulaire_mats');
    }
};
