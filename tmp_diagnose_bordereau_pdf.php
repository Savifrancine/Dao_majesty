<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dossier;
use App\Models\DossierDocument;

// Prendre le dernier dossier
$dossier = Dossier::latest()->with([
    'documents.typeDocument',
    'documents.bordereau.lignes'
])->first();

if (!$dossier) {
    echo "❌ Aucun dossier trouvé\n";
    exit(1);
}

echo "✅ Dossier: {$dossier->id} - {$dossier->nom_dossier}\n\n";

// Chercher le document "Bordereau des prix pour les fournitures à importer"
$doc = $dossier->documents()->whereHas('typeDocument', function ($q) {
    $q->where('nom', 'Bordereau des prix pour les fournitures à importer');
})->with('bordereau.lignes')->first();

if (!$doc) {
    echo "❌ Document 'Bordereau des prix pour les fournitures à importer' non trouvé\n";
    echo "\nDocuments du dossier:\n";
    foreach ($dossier->documents as $d) {
        echo "  - {$d->typeDocument->nom}\n";
    }
    exit(1);
}

echo "✅ Document trouvé (ID: {$doc->id})\n";
echo "   Type: {$doc->typeDocument->nom}\n";
echo "   Statut: {$doc->statut}\n\n";

// Afficher les bordereaux
$bordereaux = $doc->bordereau;
echo "Bordereaux: {$bordereaux->count()}\n";

foreach ($bordereaux as $idx => $bordereau) {
    echo "\n📋 Bordereau {$idx + 1} (ID: {$bordereau->id})\n";
    echo "   Titre: {$bordereau->titre}\n";
    echo "   Lignes: {$bordereau->lignes->count()}\n\n";
    
    echo "\nLignes détails:\n";
    foreach ($bordereau->lignes as $idx2 => $ligne) {
        echo "  Ligne " . ($idx2 + 1) . ":\n";
        echo "    - Designation: " . substr($ligne->designation, 0, 40) . "\n";
        echo "    - Date prestation: " . ($ligne->date_prestation ?? '(NULL)') . "\n";
        echo "    - Quantite: " . ($ligne->quantite ?? '(NULL)') . "\n";
        echo "    - Prix unitaire: " . ($ligne->prix_unitaire ?? 0) . "\n";
        echo "    - Montant: " . ($ligne->montant ?? 0) . "\n";
        echo "    - Cout benin: " . ($ligne->cout_benin ?? 0) . "\n";
    }
}

// Vérifier s'il y a des données
$hasData = false;
foreach ($bordereaux as $b) {
    foreach ($b->lignes as $l) {
        if ($l->date_prestation !== null || $l->quantite !== null) {
            $hasData = true;
            break;
        }
    }
}

echo "\n";
if (!$hasData) {
    echo "⚠️  PROBLÈME: Aucune date ou quantité trouvée dans les bordereaux!\n";
    echo "   Vérifiez que vous avez bien rempli et sauvegardé le formulaire.\n";
} else {
    echo "✅ Les données de date et quantité sont bien sauvegardées!\n";
    echo "   Si le PDF ne les affiche pas, c'est un problème de template.\n";
}
