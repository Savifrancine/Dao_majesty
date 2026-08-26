<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $documents = [
            "Fiche technique de chaque article, délivrée par le fabricant",
            "Copie de l'arrêté du Ministre de la Santé portant autorisation d'importation, de détention et de vente des équipements médicaux",
        ];

        foreach ($documents as $nom) {
            if (!DB::table('types_documents')->where('nom', $nom)->exists()) {
                DB::table('types_documents')->insert([
                    'nom' => $nom,
                    'type_formulaire' => 'fichier',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('types_documents')->whereIn('nom', [
            "Fiche technique de chaque article, délivrée par le fabricant",
            "Copie de l'arrêté du Ministre de la Santé portant autorisation d'importation, de détention et de vente des équipements médicaux",
        ])->delete();
    }
};
