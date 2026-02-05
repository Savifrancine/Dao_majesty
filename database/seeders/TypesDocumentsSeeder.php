<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types_documents')->insert([
            ['nom' => 'Lettre de soumission', 'type_formulaire' => 'formulaire', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'RCCM', 'type_formulaire' => 'fichier', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Bordereau prix unitaire', 'type_formulaire' => 'bordereau', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
