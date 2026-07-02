<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DossierDocument;

// Get all Méthodes and Calendrier documents
$docs = DossierDocument::whereHas('typeDocument', function ($q) {
    $q->whereIn('nom', ['Méthodes d\'exécution', 'Calendrier d\'exécution']);
})
->with('bordereau.lignes')
->get();

echo "=== COMPARING METHODES vs CALENDRIER ===\n\n";

foreach ($docs as $doc) {
    echo "Document: " . $doc->typeDocument->nom . " (Dossier: " . $doc->dossier->nom_dossier . ")\n";
    
    foreach ($doc->bordereau as $b) {
        echo "  Bordereau: " . $b->titre . "\n";
        echo "  Lignes: " . $b->lignes->count() . "\n";
        
        foreach ($b->lignes as $idx => $ligne) {
            $qty = $ligne->quantite;
            $date = $ligne->date_prestation;
            $prix = $ligne->prix_unitaire;
            $montant = $ligne->montant;
            
            echo "    [$idx] Designation: " . substr($ligne->designation, 0, 20) . "\n";
            echo "         Quantite (DB value): " . var_export($qty, true) . "\n";
            echo "         Date_prestation (DB value): " . var_export($date, true) . "\n";
            echo "         Prix_unitaire: " . $prix . "\n";
            echo "         Montant: " . $montant . "\n\n";
        }
    }
    echo "---\n\n";
}
