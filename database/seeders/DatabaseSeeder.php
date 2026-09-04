<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TypesDossiersSeeder::class,
            EntreprisesSeeder::class,
            TypesDocumentsSeeder::class,
        ]);

        Utilisateur::create([
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
            'mot_de_passe' => Hash::make('password'),
            'role' => 'employe',
            'actif' => true,
        ]);
    }
}
