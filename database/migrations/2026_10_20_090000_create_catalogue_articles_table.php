<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('catalogue_articles', function (Blueprint $table) {
            $table->id();
            $table->string('categorie')->nullable();
            $table->string('designation');
            $table->string('marque_type')->nullable();
            $table->text('commentaire')->nullable();
            $table->string('chemin_prospectus')->nullable();
            $table->string('nom_original_prospectus')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('catalogue_articles');
    }
};
