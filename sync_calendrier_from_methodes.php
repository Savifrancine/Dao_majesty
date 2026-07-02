<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DossierDocument;
use App\Models\Bordereau;
use App\Models\BordereauLigne;

// Find the Calendrier d'exécution for dossier Test 3
$dossier = \App\Models\Dossier::where('nom_dossier', 'Test 3')->first();
if (!$dossier) {
    echo "Dossier 'Test 3' not found\n";
    exit;
}

echo "Found dossier: " . $dossier->nom_dossier . " (ID: " . $dossier->id . ")\n\n";

// Get Méthodes and Calendrier documents
$methodesDoc = $dossier->documents->firstWhere('typeDocument.nom', "Méthodes d'exécution");
$calendrierDoc = $dossier->documents->firstWhere('typeDocument.nom', "Calendrier d'exécution");

if (!$methodesDoc) {
    echo "Méthodes d'exécution not found\n";
    exit;
}
if (!$calendrierDoc) {
    echo "Calendrier d'exécution not found\n";
    exit;
}

echo "Méthodes d'exécution ID: " . $methodesDoc->id . "\n";
echo "Calendrier d'exécution ID: " . $calendrierDoc->id . "\n\n";

// Get Méthodes data
$methodesBordereaux = $methodesDoc->bordereau;
echo "Méthodes bordereaux: " . $methodesBordereaux->count() . "\n";

// Get Calendrier data
$calendrierBordereaux = $calendrierDoc->bordereau;
echo "Calendrier bordereaux: " . $calendrierBordereaux->count() . "\n\n";

// Copy Méthodes data to Calendrier
foreach ($methodesBordereaux as $methodesB) {
    echo "Processing Méthodes bordereau: " . $methodesB->titre . "\n";
    
    // Find or create matching Calendrier bordereau
    $calendrierB = $calendrierBordereaux->firstWhere('titre', $methodesB->titre);
    
    if (!$calendrierB) {
        echo "  Creating new Calendrier bordereau with titre: " . $methodesB->titre . "\n";
        $calendrierB = Bordereau::create([
            'dossier_document_id' => $calendrierDoc->id,
            'titre' => $methodesB->titre,
        ]);
    }
    
    // Delete existing lignes
    $oldCount = $calendrierB->lignes->count();
    $calendrierB->lignes()->delete();
    echo "  Deleted $oldCount old lignes\n";
    
    // Copy lignes from Méthodes
    foreach ($methodesB->lignes as $methodesLigne) {
        BordereauLigne::create([
            'bordereau_id' => $calendrierB->id,
            'designation' => $methodesLigne->designation,
            'quantite' => $methodesLigne->quantite,
            'prix_unitaire' => $methodesLigne->prix_unitaire,
            'montant' => $methodesLigne->montant,
            'date_prestation' => $methodesLigne->date_prestation,
        ]);
        echo "    Copied: " . substr($methodesLigne->designation, 0, 20) . " | qty: " . $methodesLigne->quantite . " | date: " . ($methodesLigne->date_prestation ?? 'NULL') . "\n";
    }
}

echo "\nDone! Calendrier d'exécution has been updated with Méthodes d'exécution data.\n";

// Verify
echo "\n=== VERIFICATION ===\n";
$calendrierDoc->refresh();
foreach ($calendrierDoc->bordereau as $b) {
    echo "Bordereau: " . $b->titre . "\n";
    foreach ($b->lignes as $ligne) {
        $qty = $ligne->quantite;
        $date = $ligne->date_prestation;
        echo "  - " . substr($ligne->designation, 0, 20) . " | qty: $qty | date: " . ($date ?: 'NULL') . "\n";
    }
}
