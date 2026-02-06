<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('titre_dossier')->nullable()->after('nom_dossier');
            $table->foreignId('type_marche_id')->nullable()->constrained('types_marches')->after('titre_dossier');
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->after('type_marche_id');
            $table->string('numero_ao')->nullable()->after('procedure_id');
            $table->date('date_ao')->nullable()->after('numero_ao');
            $table->text('objet_marche')->nullable()->after('date_ao');
            $table->string('lots')->nullable()->after('objet_marche');
            $table->foreignId('autorite_contractante_id')->nullable()->constrained('autorites_contractantes')->after('lots');
            $table->foreignId('source_financement_id')->nullable()->constrained('sources_financement')->after('autorite_contractante_id');
            $table->string('reference_step')->nullable()->after('source_financement_id');
            $table->year('annee_gestion')->nullable()->after('reference_step');
            $table->string('ville_signature')->nullable()->after('annee_gestion');
            $table->date('date_signature')->nullable()->after('ville_signature');
            $table->string('mois_edition')->nullable()->after('date_signature');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'titre_dossier',
                'type_marche_id',
                'procedure_id',
                'numero_ao',
                'date_ao',
                'objet_marche',
                'lots',
                'autorite_contractante_id',
                'source_financement_id',
                'reference_step',
                'annee_gestion',
                'ville_signature',
                'date_signature',
                'mois_edition',
            ]);
        });
    }
};
