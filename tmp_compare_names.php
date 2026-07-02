<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pieceNames = [
    "Déclaration de garantie d'offre",
    "Lettre de soumission",
    "Copie legalisee de l'Extrait du RCCM",
    "Copie legalisee de l'Identifiant Fiscal Unique (IFU)",
    "Attestation de non-faillite datant de moins de trois (03) mois",
    "Attestation d'imposition ou de situation fiscale en cours de validite",
    "Attestation de regularite a la CNSS",
    "Formulaire de renseignements sur le candidat",
    "Liste du personnel affecté à l'exécution du marché",
    "Attestation de non-exclusion de la commande publique",
    "Engagement du soumissionnaire à respecter le code d'éthique et de déontologie",
    "Attestation de non-condamnation pour fraude, corruption ou fausse declaration",
    "Attestation de nationalite ou document de constitution legale de l'entreprise",
    "Statuts de la societe et PV de nomination du gerant",
    "Copie du quitus fiscal",
    "Attestation de situation reguliere vis-a-vis des organismes de credit",
    "Bordereau prix unitaire",
    "Programme d'activités",
    "Méthodes d'exécution",
    "Calendrier d'exécution",
    "Description technique des services",
];

$doc = App\Models\TypeDocument::where('nom', 'like', '%personnel%')->first();
if (!$doc) {
    echo "No doc found\n";
    exit(1);
}

$needle = $pieceNames[8];
$haystack = $doc->nom;

echo "needle: [$needle]\n";
echo "haystack: [$haystack]\n";
echo "needle len=" . strlen($needle) . " hex=" . bin2hex($needle) . "\n";
echo "haystack len=" . strlen($haystack) . " hex=" . bin2hex($haystack) . "\n";
echo "equal? ";
echo ($needle === $haystack) ? "yes\n" : "no\n";
