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
        DB::table('types_dossiers')->insert([
            // Catégorie PUBLIC
            ['nom' => 'DAO', 'categorie' => 'public', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'DRP', 'categorie' => 'public', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Demande de cotation', 'categorie' => 'public', 'created_at' => now(), 'updated_at' => now()],
            // Catégorie PRIVÉ (à compléter plus tard)
            ['nom' => 'Appel à manifestation d\'intérêt', 'categorie' => 'prive', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Consultation restreinte', 'categorie' => 'prive', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
