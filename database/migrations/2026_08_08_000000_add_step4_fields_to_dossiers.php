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
                // 'reference_step' existe déjà (colonne héritée) : réutilisée pour "Ref STEP".
                if (!Schema::hasColumn('dossiers', 'source_financement')) {
                    $table->string('source_financement')->nullable();
                }
                if (!Schema::hasColumn('dossiers', 'gestion')) {
                    $table->string('gestion')->nullable();
                }
                if (!Schema::hasColumn('dossiers', 'imputation_budgetaire')) {
                    $table->string('imputation_budgetaire')->nullable();
                }
                if (!Schema::hasColumn('dossiers', 'accord_pret')) {
                    $table->string('accord_pret')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                $cols = ['source_financement', 'gestion', 'imputation_budgetaire', 'accord_pret'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('dossiers', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
