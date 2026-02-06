<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                if (!Schema::hasColumn('dossiers', 'republique')) {
                    $table->string('republique')->nullable()->after('page_garde_path');
                }
                if (!Schema::hasColumn('dossiers', 'ministere')) {
                    $table->string('ministere')->nullable()->after('republique');
                }
                if (!Schema::hasColumn('dossiers', 'direction')) {
                    $table->string('direction')->nullable()->after('ministere');
                }
                if (!Schema::hasColumn('dossiers', 'services_projet')) {
                    $table->string('services_projet')->nullable()->after('direction');
                }
                if (!Schema::hasColumn('dossiers', 'destinataires')) {
                    $table->text('destinataires')->nullable()->after('services_projet');
                }
                if (!Schema::hasColumn('dossiers', 'reference_dossier')) {
                    $table->string('reference_dossier')->nullable()->after('destinataires');
                }
                if (!Schema::hasColumn('dossiers', 'date_lancement')) {
                    $table->date('date_lancement')->nullable()->after('reference_dossier');
                }
                if (!Schema::hasColumn('dossiers', 'titre_lot')) {
                    $table->string('titre_lot')->nullable()->after('lots');
                }
                if (!Schema::hasColumn('dossiers', 'types_offres')) {
                    $table->string('types_offres')->nullable()->after('titre_lot');
                }
                if (!Schema::hasColumn('dossiers', 'autres_details')) {
                    $table->text('autres_details')->nullable()->after('types_offres');
                }
                if (!Schema::hasColumn('dossiers', 'mois_depot')) {
                    $table->string('mois_depot')->nullable()->after('autres_details');
                }
                if (!Schema::hasColumn('dossiers', 'annee_depot')) {
                    $table->string('annee_depot')->nullable()->after('mois_depot');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                $cols = [
                    'republique', 'ministere', 'direction', 'services_projet',
                    'destinataires', 'reference_dossier', 'date_lancement',
                    'titre_lot', 'types_offres', 'autres_details', 'mois_depot', 'annee_depot'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('dossiers', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
