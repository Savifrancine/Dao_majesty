<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dossier;

// Prendre le dernier dossier
$dossier = Dossier::latest()->with([
    'documents.typeDocument',
    'documents.bordereau.lignes'
])->first();

if (!$dossier) {
    echo "No dossier found\n";
    exit(1);
}

echo "Dossier: " . $dossier->id . " - " . $dossier->nom_dossier . "\n\n";

// Chercher le document "Bordereau des prix pour les fournitures à importer"
$doc = $dossier->documents()->whereHas('typeDocument', function ($q) {
    $q->where('nom', 'Bordereau des prix pour les fournitures à importer');
})->with('bordereau.lignes')->first();

if (!$doc) {
    echo "Document 'Bordereau des prix pour les fournitures à importer' not found\n";
    exit(1);
}

echo "Document found (ID: " . $doc->id . ")\n";
echo "Type: " . $doc->typeDocument->nom . "\n";
echo "Status: " . $doc->statut . "\n\n";

// Afficher les bordereaux
$bordereaux = $doc->bordereau;
echo "Number of bordereaux: " . $bordereaux->count() . "\n\n";

foreach ($bordereaux as $bIdx => $bordereau) {
    echo "Bordereau " . ($bIdx + 1) . " (ID: " . $bordereau->id . ")\n";
    echo "Title: " . $bordereau->titre . "\n";
    echo "Lines: " . $bordereau->lignes->count() . "\n\n";
    
    foreach ($bordereau->lignes as $lIdx => $ligne) {
        echo "  Line " . ($lIdx + 1) . ":\n";
        echo "    Designation: " . substr($ligne->designation, 0, 40) . "\n";
        echo "    Date: " . ($ligne->date_prestation ?? 'NULL') . "\n";
        echo "    Quantity: " . ($ligne->quantite ?? 'NULL') . "\n";
        echo "    Price: " . ($ligne->prix_unitaire ?? 0) . "\n";
        echo "    Amount: " . ($ligne->montant ?? 0) . "\n";
        echo "    Cost Benin: " . ($ligne->cout_benin ?? 0) . "\n";
        echo "\n";
    }
}
?>
