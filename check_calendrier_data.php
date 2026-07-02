<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TypeDocument;
use App\Models\DossierDocument;

// Check all dossiers with Calendrier
$typIds = TypeDocument::whereIn('nom', ['Calendrier d\'exécution', 'Méthodes d\'exécution'])->pluck('id')->toArray();
$docs = DossierDocument::whereIn('type_document_id', $typIds)->with('bordereau.lignes')->get();

echo "Found " . $docs->count() . " calendar/methods docs\n\n";

foreach ($docs->take(5) as $doc) {
    echo "Doc ID " . $doc->id . " (" . $doc->typeDocument->nom . ") - Dossier: " . $doc->dossier->nom_dossier . "\n";
    foreach ($doc->bordereau as $b) {
        foreach ($b->lignes as $idx => $ligne) {
            $qty = $ligne->quantite !== null ? $ligne->quantite : 'NULL';
            $date = $ligne->date_prestation !== null ? (trim($ligne->date_prestation) !== '' ? $ligne->date_prestation : 'EMPTY') : 'NULL';
            $prix = $ligne->prix_unitaire !== null ? $ligne->prix_unitaire : 'NULL';
            echo "  [$idx] " . substr($ligne->designation, 0, 20) . " | qty: $qty | date: $date | prix: $prix\n";
        }
    }
    echo "\n";
}


