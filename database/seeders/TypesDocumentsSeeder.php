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
            ['nom' => 'Formulaire de qualification', 'type_formulaire' => 'fichier'],
            ['nom' => 'Lettre de soumission', 'type_formulaire' => 'fichier'],
            ['nom' => 'Déclaration de l\'autorité contractante', 'type_formulaire' => 'fichier'],
            ['nom' => 'Fiche technique de chaque article, délivrée par le fabricant', 'type_formulaire' => 'fichier'],
            ['nom' => 'Copie de l\'arrêté du Ministre de la Santé portant autorisation d\'importation, de détention et de vente des équipements médicaux', 'type_formulaire' => 'fichier'],
            ['nom' => 'Copie legalisee de l\'Identifiant Fiscal Unique (IFU)', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de non-faillite datant de moins de trois (03) mois', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation d\'imposition ou de situation fiscale en cours de validite', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de regularite a la CNSS', 'type_formulaire' => 'fichier'],
            ['nom' => 'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire FIN 3.3', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière', 'type_formulaire' => 'fichier'],
            ['nom' => 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière', 'type_formulaire' => 'fichier'],
            ['nom' => 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire MAT', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Formulaire PER', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Liste du personnel affecté à l\'exécution du marché', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Attestation de non-exclusion de la commande publique', 'type_formulaire' => 'fichier'],
            ['nom' => 'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie', 'type_formulaire' => 'formulaire'],
            ['nom' => 'Attestation de non-condamnation pour fraude, corruption ou fausse declaration', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de nationalite ou document de constitution legale de l\'entreprise', 'type_formulaire' => 'fichier'],
            ['nom' => 'Statuts de la societe et PV de nomination du gerant', 'type_formulaire' => 'fichier'],
            ['nom' => 'Copie du quitus fiscal', 'type_formulaire' => 'fichier'],
            ['nom' => 'Attestation de situation reguliere vis-a-vis des organismes de credit', 'type_formulaire' => 'fichier'],
            ['nom' => 'RCCM', 'type_formulaire' => 'fichier'],
            ['nom' => 'Relevé d\'identité bancaire (RIB)', 'type_formulaire' => 'fichier'],
            ['nom' => 'Formulaire de divulgation des bénéficiaires effectifs', 'type_formulaire' => 'fichier'],
            ['nom' => 'Pièce d\'identité du premier responsable', 'type_formulaire' => 'fichier'],
            ['nom' => 'Bordereau prix unitaire', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Bordereau des prix pour les fournitures à importer', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Bordereau des prix des fournitures, déjà importées', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Bordereau des prix pour les fournitures fabriquées au Bénin', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Bordereau des prix et calendrier d\'exécution des services connexes', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Listes des Fournitures et Calendrier de livraison', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Cadres de sous détails des prix unitaire', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Programme d\'activités', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Méthodes d\'exécution', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Calendrier d\'exécution', 'type_formulaire' => 'bordereau'],
            ['nom' => 'Description technique des fournitures/services', 'type_formulaire' => 'bordereau'],
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
