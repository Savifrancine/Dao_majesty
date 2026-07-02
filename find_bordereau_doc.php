<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dossier;
use App\Models\DossierDocument;

// Chercher un document "Bordereau des prix pour les fournitures à importer"
$doc = DossierDocument::whereHas('typeDocument', function ($q) {
    $q->where('nom', 'Bordereau des prix pour les fournitures à importer');
})->with('dossier', 'bordereau.lignes')->first();

if (!$doc) {
    echo "No 'Bordereau des prix pour les fournitures à importer' document found in database\n";
    echo "Creating a test or checking if the type exists...\n";
    
    $type = \App\Models\TypeDocument::where('nom', 'Bordereau des prix pour les fournitures à importer')->first();
    if ($type) {
        echo "Type exists (ID: " . $type->id . ")\n";
    } else {
        echo "Type does NOT exist in database\n";
    }
    exit(1);
}

echo "Found document!\n";
echo "Dossier: " . $doc->dossier->id . " - " . $doc->dossier->nom_dossier . "\n\n";

$bordereaux = $doc->bordereau;
echo "Bordereaux: " . $bordereaux->count() . "\n\n";

foreach ($bordereaux as $bIdx => $bordereau) {
    echo "Bordereau " . ($bIdx + 1) . " (ID: " . $bordereau->id . ")\n";
    echo "  Title: " . $bordereau->titre . "\n";
    echo "  Lines: " . $bordereau->lignes->count() . "\n\n";
    
    foreach ($bordereau->lignes as $lIdx => $ligne) {
        echo "  Line " . ($lIdx + 1) . ":\n";
        echo "    Designation: " . substr(trim($ligne->designation), 0, 50) . "\n";
        echo "    Date prestation: '" . $ligne->date_prestation . "'\n";
        echo "    Quantite: " . $ligne->quantite . "\n";
        echo "    Prix unitaire: " . $ligne->prix_unitaire . "\n";
        echo "    Montant: " . $ligne->montant . "\n";
        echo "    Cout benin: " . ($ligne->cout_benin ?? 'NULL') . "\n";
        echo "\n";
    }
}
?>
