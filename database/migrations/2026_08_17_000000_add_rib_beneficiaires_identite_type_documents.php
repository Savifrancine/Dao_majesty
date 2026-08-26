<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $noms = [
        "Relevé d'identité bancaire (RIB)",
        "Formulaire de divulgation des bénéficiaires effectifs",
        "Pièce d'identité du premier responsable",
    ];

    public function up(): void
    {
        foreach ($this->noms as $nom) {
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
        DB::table('types_documents')->whereIn('nom', $this->noms)->delete();
    }
};
