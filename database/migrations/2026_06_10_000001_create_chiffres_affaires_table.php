<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chiffres_affaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_id')->nullable();
            $table->integer('annee');
            $table->decimal('montant', 20, 2)->default(0);
            $table->string('monnaie')->default('F CFA');
            $table->timestamps();

            $table->foreign('dossier_id')->references('id')->on('dossiers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chiffres_affaires');
    }
};
