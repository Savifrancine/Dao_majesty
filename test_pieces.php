<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TypeDocument;

$pieceNames = [
    "Déclaration de garantie d'offre",
    "Lettre de soumission",
    "Bordereau prix unitaire",
    "Programme d'activités",
    "Méthodes d'exécution",
];

$documentsByName = TypeDocument::whereIn('nom', $pieceNames)->get()->keyBy('nom');
$documents = collect($pieceNames)
    ->map(fn ($name) => $documentsByName->get($name))
    ->filter();

echo "PieceNames count: " . count($pieceNames) . "\n";
echo "Documents found: " . $documents->count() . "\n";
echo "\nDocuments in collection:\n";
foreach ($documents as $doc) {
    echo " - {$doc->id}: {$doc->nom}\n";
}

echo "\nMissing documents:\n";
foreach ($pieceNames as $name) {
    if (!isset($documentsByName[$name])) {
        echo " - {$name}\n";
    }
}
