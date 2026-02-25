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
        $now = now();

        $documents = [
            ['nom' => 'Déclaration de garantie d\'offre', 'type_formulaire' => 'fichier'],
            ['nom' => 'Lettre de soumission', 'type_formulaire' => 'fichier'],
            ['nom' => 'Copie legalisee de l\'Extrait du RCCM', 'type_formulaire' => 'fichier'],
            ['nom' => 'Copie legalisee de l\'Identifiant Fiscal Unique (IFU)', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de non-faillite datant de moins de trois (03) mois', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation d\'imposition ou de situation fiscale en cours de validite', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de regularite a la CNSS', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de non-exclusion de la commande publique', 'type_formulaire' => 'fichier'],
            ['nom' => 'Engagement a respecter le code d\'ethique et de deontologie de la commande publique', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de non-condamnation pour fraude, corruption ou fausse declaration', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de nationalite ou document de constitution legale de l\'entreprise', 'type_formulaire' => 'fichier'],
            ['nom' => 'Statuts de la societe et PV de nomination du gerant', 'type_formulaire' => 'fichier'],
            ['nom' => 'Copie du quitus fiscal', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de situation reguliere vis-a-vis des organismes de credit', 'type_formulaire' => 'fichier'],
            ['nom' => 'RCCM', 'type_formulaire' => 'fichier'],
            ['nom' => 'Bordereau prix unitaire', 'type_formulaire' => 'bordereau'],
        ];

        foreach ($documents as $document) {
            DB::table('types_documents')->updateOrInsert(
                ['nom' => $document['nom']],
                [
                    'type_formulaire' => $document['type_formulaire'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
