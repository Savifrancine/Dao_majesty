<?php

namespace App\Console\Commands;

use App\Models\TypeDocument;
use Illuminate\Console\Command;

class SyncDocumentTypes extends Command
{
    protected $signature = 'documents:sync';
    protected $description = 'Synchronize all document types in the database';

    public function handle()
    {
        $pieceNames = [
            "Déclaration de garantie d'offre",
            "Lettre de soumission",
            "Copie legalisee de l'Extrait du RCCM",
            "Copie legalisee de l'Identifiant Fiscal Unique (IFU)",
            "Attestation de non-faillite datant de moins de trois (03) mois",
            "Attestation d'imposition ou de situation fiscale en cours de validite",
            "Attestation de regularite a la CNSS",
            "Formulaire de renseignements sur le candidat",
            "Formulaire MAT",
            "Formulaire PER",
            "Chiffre d'affaires annuel moyen des activités de services",
            "Attestation de non-exclusion de la commande publique",
            "Engagement a respecter le code d'ethique et de deontologie de la commande publique",
            "Attestation de non-condamnation pour fraude, corruption ou fausse declaration",
            "Attestation de nationalite ou document de constitution legale de l'entreprise",
            "Statuts de la societe et PV de nomination du gerant",
            "Copie du quitus fiscal",
            "Attestation de situation reguliere vis-a-vis des organismes de credit",
            "Bordereau prix unitaire",
            "Bordereau des prix pour les fournitures à importer",
            "Bordereau des prix et calendrier d'exécution des services connexes",
            "Listes des services connexes et calendrier de réalisation",
            "Listes des Fournitures et Calendrier de livraison",
            "Cadres de sous détails des prix unitaire",
            "Programme d'activités",
            "Méthodes d'exécution",
            "Calendrier d'exécution",
            "Description technique des services",
        ];

        $created = 0;
        $bordereauNames = [
            'Bordereau prix unitaire',
            'Bordereau des prix pour les fournitures à importer',
            'Bordereau des prix et calendrier d\'exécution des services connexes',
            'Listes des services connexes et calendrier de réalisation',
            'Listes des Fournitures et Calendrier de livraison',
            'Cadres de sous détails des prix unitaire',
            'Programme d\'activités',
            'Méthodes d\'exécution',
            'Calendrier d\'exécution',
            'Description technique des services',
        ];

        foreach ($pieceNames as $name) {
            $type = in_array($name, $bordereauNames, true) ? 'bordereau' : 'formulaire';
            $result = TypeDocument::firstOrCreate(
                ['nom' => $name],
                ['type_formulaire' => $type]
            );
            if ($result->type_formulaire !== $type) {
                $result->update(['type_formulaire' => $type]);
            }
            if ($result->wasRecentlyCreated) {
                $created++;
                $this->line("✓ Créé : {$name}");
            } else {
                $this->line("✔ Existe : {$name}");
            }
        }

        $this->info("\n✅ Synchronisation complète : {$created} type(s) créé(s), " . (count($pieceNames) - $created) . " existant(s).");
    }
}
