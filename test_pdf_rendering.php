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
})->with('dossier', 'typeDocument', 'bordereau.lignes')->first();

if (!$doc) {
    echo "No document found\n";
    exit(1);
}

$dossier = $doc->dossier;
$bordereaux = $doc->bordereau;

echo "Testing PDF template logic for Bordereau\n";
echo "==========================================\n\n";

foreach ($bordereaux as $bordereau) {
    echo "Bordereau: " . $bordereau->titre . "\n";
    echo "\nTesting foreach on lignes:\n";
    
    if (is_iterable($bordereau->lignes)) {
        echo "  - lignes is iterable\n";
        echo "  - Count: " . count($bordereau->lignes) . "\n";
    } else {
        echo "  - ERROR: lignes is NOT iterable\n";
    }
    
    echo "\nLine details:\n";
    foreach ($bordereau->lignes as $index => $ligne) {
        echo "\n  Line " . ($index + 1) . ":\n";
        echo "    Ligne object exists: " . (is_object($ligne) ? 'YES' : 'NO') . "\n";
        echo "    designation: " . $ligne->designation . "\n";
        echo "    quantite raw: " . $ligne->quantite . "\n";
        
        // Simulate PDF calculation
        $prix = (float) ($ligne->prix_unitaire ?? 0);
        $quantite = (float) ($ligne->quantite ?? 1);
        $montant = $prix * $quantite;
        $coutBenin = (float) ($ligne->cout_benin ?? 0);
        
        echo "    quantite (float): " . $quantite . "\n";
        echo "    quantite formatted: " . number_format($quantite, 2, '.', ' ') . "\n";
        echo "    date_prestation: '" . $ligne->date_prestation . "'\n";
        echo "    date_prestation empty? " . (empty($ligne->date_prestation) ? 'YES' : 'NO') . "\n";
        echo "    cout_benin: " . ($ligne->cout_benin ?? 'NULL') . "\n";
    }
}

echo "\n\nTest complete.\n";
?>
