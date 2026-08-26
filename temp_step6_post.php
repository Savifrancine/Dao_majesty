<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\DossierController;
use App\Models\Dossier;

$request = Request::create('/dossiers/step6/40?current_index=2', 'POST', [
    'upload_step' => '1',
    'current_index' => 2,
    'current_document_id' => 17,
    'content' => "Test d'engagement envoyé via temp_step6_post",
    'documents' => [3,18,17,19,20,21,16,22,23,24,25,2,26,27,28,29,33,34,35,36],
    'sections' => [
        [
            'titre' => 'Les boissons',
            'lignes' => [
                ['designation' => 'Fanta', 'unite_physique' => '', 'quantite' => 30, 'prix_unitaire' => 600, 'montant' => 18000, 'site' => 'Majesty', 'date_prestation' => '2 M'],
                ['designation' => 'coca', 'unite_physique' => '', 'quantite' => 50, 'prix_unitaire' => 500, 'montant' => 25000, 'site' => 'Majesty', 'date_prestation' => '2 M'],
                ['designation' => 'Savana', 'unite_physique' => '', 'quantite' => 80, 'prix_unitaire' => 2000, 'montant' => 160000, 'site' => 'Majesty', 'date_prestation' => '2 M'],
            ],
        ],
    ],
]);

// Start session and set required session values
$sessionManager = $app->make('session');
$sessionStore = $app->make('session.store');
$sessionStore->start();
$request->setLaravelSession($sessionStore);
$sessionStore->put('step6_documents_40', [3,18,17,19,20,21,16,22,23,24,25,2,26,27,28,29,33,34,35,36]);
$sessionStore->put('step6_signataire_40', null);

$controller = new DossierController();
$selectedDocumentIds = [3,18,17,19,20,21,16,22,23,24,25,2,26,27,28,29,33,34,35,36];
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
    "Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d'antécédents de litiges",
    "Formulaire MAT",
    "Formulaire PER",
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
    "Tableau de résumé des bordereaux de prix",
    "Bordereau des prix et calendrier d'exécution des services connexes",
    "Listes des services connexes et calendrier de réalisation",
    "Listes des Fournitures et Calendrier de livraison",
    "Cadres de sous détails des prix unitaire",
    "Programme d'activités",
    "Méthodes d'exécution",
    "Calendrier d'exécution",
    "Description technique des services",
];
$types = App\Models\TypeDocument::whereIn('id', $selectedDocumentIds)->get()->keyBy('id');
$orderedSelected = collect($selectedDocumentIds)->map(fn($id) => $types->get($id))->filter();
$uploadQueue = $orderedSelected->filter(fn($doc) => $doc->nom !== 'Lettre de soumission' && !in_array($doc->nom, [], true))->values();

foreach ($uploadQueue as $index => $doc) {
    echo "queue[$index] = {$doc->id} {$doc->nom}\n";
}

$currentIndex = 2;
for ($i = $currentIndex + 1; $i < $uploadQueue->count(); $i++) {
    $nextDoc = $uploadQueue->get($i);
    $docRec = App\Models\DossierDocument::where('dossier_id', 40)->where('type_document_id', $nextDoc->id)->first();
    $status = $docRec ? $docRec->statut : 'missing';
    echo "next candidate index $i = {$nextDoc->id} status=$status\n";
    if (!$docRec || $docRec->statut !== 'complete') {
        echo "choose nextIndex=$i\n";
        break;
    }
}

echo "---- now controller response ----\n";
$response = $controller->step6($request, 40);
echo 'Response class: '.get_class($response)."\n";
if (method_exists($response, 'getTargetUrl')) {
    echo 'Redirect to: '.$response->getTargetUrl()."\n";
}
if (method_exists($response, 'getStatusCode')) {
    echo 'Status: '.$response->getStatusCode()."\n";
}
