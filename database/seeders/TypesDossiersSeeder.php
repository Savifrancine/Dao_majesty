<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesDossiersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            // Catégorie PUBLIC
            ['nom' => 'DAO', 'categorie' => 'public'],
            ['nom' => 'DRP', 'categorie' => 'public'],
            ['nom' => 'Demande de cotation', 'categorie' => 'public'],
            // Catégorie PRIVÉ (à compléter plus tard)
            ['nom' => 'Appel à manifestation d\'intérêt', 'categorie' => 'prive'],
            ['nom' => 'Consultation restreinte', 'categorie' => 'prive'],
        ];

        // updateOrInsert (plutôt qu'insert) : re-exécuter ce seeder ne doit
        // jamais créer de doublon si un type du même nom existe déjà.
        foreach ($types as $type) {
            DB::table('types_dossiers')->updateOrInsert(
                ['nom' => $type['nom']],
                ['categorie' => $type['categorie'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
