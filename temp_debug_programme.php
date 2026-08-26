<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simule un POST pour le programme d'activités du dossier 40
$doc = App\Models\DossierDocument::find(43);
echo "DossierDocument #43 avant traitement:\n";
echo "  statut=" . $doc->statut . "\n";
echo "  type_document_id=" . $doc->type_document_id . "\n";
echo "  bordereaux=" . count($doc->bordereau) . "\n";

foreach ($doc->bordereau as $b) {
    echo "  Bordereau #" . $b->id . " titre=" . $b->titre . " lignes=" . count($b->lignes) . "\n";
    foreach ($b->lignes as $l) {
        echo "    Ligne: des=" . $l->designation . " qty=" . $l->quantite . " site=" . $l->site . " date=" . $l->date_prestation . "\n";
    }
}

$typeDoc = App\Models\TypeDocument::find(17);
echo "\nTypeDocument #17 nom=" . $typeDoc->nom . "\n";
echo "Vérification: nom === 'Programme d'activités' ? " . (trim($typeDoc->nom) === "Programme d'activités" ? 'OUI' : 'NON') . "\n";
