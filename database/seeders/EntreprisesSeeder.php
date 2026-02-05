<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntreprisesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('entreprises')->insert([
            [
                'nom' => 'MAJESTY',
                'sigle' => 'MAJ',
                'adresse' => '123 Avenue Principale, Yaoundé',
                'telephone' => '+237 222 111 222',
                'email' => 'contact@majesty.cm',
                'logo' => '/images/logo-majesty.png',
                'responsable' => 'Jean Dupont',
                'fonction_responsable' => 'Directeur Général',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
