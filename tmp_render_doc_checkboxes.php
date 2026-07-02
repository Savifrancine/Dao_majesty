<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;

// Reproduce controller logic
$pieceNames = [
    "Déclaration de garantie d'offre",
    "Lettre de soumission",
    "RCCM",
    "Copie legalisee de l'Extrait du RCCM",
    "Copie legalisee de l'Identifiant Fiscal Unique (IFU)",
    "Attestation de non-faillite datant de moins de trois (03) mois",
    "Attestation d'imposition ou de situation fiscale en cours de validite",
    "Attestation de regularite a la CNSS",
    "Formulaire de renseignements sur le candidat",
    "Liste du personnel affecté à l'exécution du marché",
    "Chiffre d'affaires annuel moyen des activités de services",
    "Attestation de non-exclusion de la commande publique",
    "Engagement du soumissionnaire à respecter le code d'éthique et de déontologie",
    "Attestation de non-condamnation pour fraude, corruption ou fausse declaration",
    "Attestation de nationalite ou document de constitution legale de l'entreprise",
    "Statuts de la societe et PV de nomination du gerant",
    "Copie du quitus fiscal",
    "Attestation de situation reguliere vis-a-vis des organismes de credit",
    "Bordereau prix unitaire",
    "Bordereau des prix pour les fournitures à importer",
    "Bordereau des prix et calendrier d'exécution des services connexes",
    "Programme d'activités",
    "Méthodes d'exécution",
    "Calendrier d'exécution",
    "Description technique des services",
];

$documentsByName = TypeDocument::whereIn('nom', $pieceNames)->get()->keyBy('nom');
$documents = collect($pieceNames)->map(fn($name) => $documentsByName->get($name))->filter();
$docsByName = $documents->keyBy('nom');

foreach ($pieceNames as $pieceName) {
    $doc = $docsByName->get($pieceName);
    if ($doc) {
        echo "{$pieceName} => ID={$doc->id}\n";
    } else {
        echo "{$pieceName} => (missing)\n";
    }
}
