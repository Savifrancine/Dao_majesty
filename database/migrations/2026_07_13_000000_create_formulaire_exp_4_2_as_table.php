<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('formulaire_exp_4_2_as', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('nom_candidat');
            $table->date('date_formulaire')->nullable();
            $table->string('numero_adrp')->nullable();
            $table->text('objet_marche')->nullable();
            $table->string('numero_marche')->nullable();
            $table->text('identification_marche')->nullable();
            $table->date('date_attribution')->nullable();
            $table->date('date_achevement')->nullable();
            $table->string('role_marche')->nullable();
            $table->string('montant_total')->nullable();
            $table->string('participation_pourcentage')->nullable();
            $table->string('montant_part')->nullable();
            $table->string('autorite_nom')->nullable();
            $table->text('autorite_adresse')->nullable();
            $table->string('autorite_telephone')->nullable();
            $table->string('autorite_email')->nullable();
            $table->string('nom_signataire')->nullable();
            $table->string('fonction_signataire')->nullable();
            $table->string('lieu_fait')->nullable();
            $table->date('date_fait')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('formulaire_exp_4_2_as');
    }
};
