<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_mats', function (Blueprint $table) {
            $table->string('nom_proprietaire')->nullable()->after('provenance');
            $table->text('adresse_proprietaire')->nullable()->after('nom_proprietaire');
            $table->string('telephone_proprietaire')->nullable()->after('adresse_proprietaire');
            $table->string('contact_nom_titre')->nullable()->after('telephone_proprietaire');
            $table->string('telecopie_proprietaire')->nullable()->after('contact_nom_titre');
            $table->string('telex_proprietaire')->nullable()->after('telecopie_proprietaire');
            $table->text('accords_details')->nullable()->after('telex_proprietaire');
        });
    }

    public function down()
    {
        Schema::table('formulaire_mats', function (Blueprint $table) {
            $table->dropColumn([
                'nom_proprietaire',
                'adresse_proprietaire',
                'telephone_proprietaire',
                'contact_nom_titre',
                'telecopie_proprietaire',
                'telex_proprietaire',
                'accords_details',
            ]);
        });
    }
};
