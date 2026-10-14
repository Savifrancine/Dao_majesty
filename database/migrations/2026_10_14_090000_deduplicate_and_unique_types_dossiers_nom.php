<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Des types de dossiers en double (meme "nom", plusieurs lignes - ex:
     * "BTP", "Demande de cotation" apparaissant deux fois dans la liste de
     * creation d'un dossier) ont pu etre inseres directement en base, hors
     * seeder/migration. Pour chaque nom en double, on garde la ligne la plus
     * ancienne (id le plus petit), on reaffecte les dossiers qui pointaient
     * sur les doublons vers cette ligne conservee, puis on supprime les
     * doublons. Une contrainte d'unicite sur "nom" empeche toute recidive.
     */
    public function up(): void
    {
        $duplicateNoms = DB::table('types_dossiers')
            ->select('nom')
            ->groupBy('nom')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nom');

        foreach ($duplicateNoms as $nom) {
            $rows = DB::table('types_dossiers')->where('nom', $nom)->orderBy('id')->get();
            $keepId = $rows->first()->id;
            $duplicateIds = $rows->slice(1)->pluck('id');

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            DB::table('dossiers')->whereIn('type_dossier_id', $duplicateIds)->update(['type_dossier_id' => $keepId]);
            DB::table('types_dossiers')->whereIn('id', $duplicateIds)->delete();
        }

        if (!$this->hasUniqueIndex('types_dossiers', 'nom')) {
            Schema::table('types_dossiers', function (Blueprint $table) {
                $table->unique('nom');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasUniqueIndex('types_dossiers', 'nom')) {
            Schema::table('types_dossiers', function (Blueprint $table) {
                $table->dropUnique(['nom']);
            });
        }
    }

    private function hasUniqueIndex(string $table, string $column): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['unique'] && $index['columns'] === [$column]) {
                return true;
            }
        }

        return false;
    }
};
