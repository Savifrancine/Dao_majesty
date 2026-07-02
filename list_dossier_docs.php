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
echo "Documents:\n";

foreach ($dossier->documents as $doc) {
    echo "  - " . $doc->typeDocument->nom . "\n";
    if ($doc->typeDocument->nom === 'Bordereau des prix pour les fournitures à importer') {
        echo "    [FOUND THE BORDEREAU]\n";
        echo "    Bordereaux count: " . $doc->bordereau->count() . "\n";
        foreach ($doc->bordereau as $b) {
            echo "    Bordereau " . $b->id . " has " . $b->lignes->count() . " lines\n";
            foreach ($b->lignes as $l) {
                echo "      - " . $l->designation . " | Date: " . ($l->date_prestation ?? 'NULL') . " | Qty: " . ($l->quantite ?? 'NULL') . "\n";
            }
        }
    }
}
?>
