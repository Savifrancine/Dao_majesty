<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dossier = App\Models\Dossier::find(40);
$dossier->load([
    'documents.typeDocument.champs',
    'documents.bordereau.lignes',
    'documents.valeurs',
    'documents.fichiers',
    'entreprise',
    'typeDossier',
    'signataires'
]);

$excludedDocNames = [
    'Déclaration de garantie',
    "Déclaration de garantie d'offre",
    "Declaration de garantie d'offre",
];

$documents = $dossier->documents->filter(function ($doc) use ($excludedDocNames) {
    return $doc->typeDocument && !in_array(trim($doc->typeDocument->nom), $excludedDocNames, true);
})->sortBy('ordre')->values();

foreach ($documents as $doc) {
    if (trim($doc->typeDocument->nom) === "Calendrier d'exécution") {
        echo "CALENDRIER D'EXÉCUTION\n";
        foreach ($doc->bordereau as $b) {
            foreach ($b->lignes as $l) {
                echo "L: {$l->designation} | q={$l->quantite} | d={$l->date_prestation}\n";
            }
        }
        break;
    }
}
?>
