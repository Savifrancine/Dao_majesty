<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bordereau;
use App\Models\BordereauLigne;

// Trouver le dernier bordereau fournitures à importer
$latestBordereau = Bordereau::whereHas('dossierDocument', function ($q) {
    $q->whereHas('typeDocument', function ($q2) {
        $q2->where('nom', 'Bordereau des prix pour les fournitures à importer');
    });
})->latest()->first();

if (!$latestBordereau) {
    echo "❌ Aucun bordereau 'Bordereau des prix pour les fournitures à importer' trouvé\n";
    exit(1);
}

echo "✅ Bordereau trouvé (ID: {$latestBordereau->id})\n";
echo "   Titre: {$latestBordereau->titre}\n";
echo "   Créé: {$latestBordereau->created_at}\n\n";

// Afficher les lignes
$lignes = $latestBordereau->lignes()->get();
echo "Lignes sauvegardées (" . $lignes->count() . "):\n";
echo str_repeat("-", 120) . "\n";
printf("%-3s | %-25s | %-12s | %-8s | %-12s | %-12s | %-12s\n", 
    "N°", "Désignation", "Date", "Quantité", "Prix Unit.", "Montant", "Coût Bénin");
echo str_repeat("-", 120) . "\n";

foreach ($lignes as $idx => $ligne) {
    $designationShort = substr($ligne->designation, 0, 25);
    $datePrestation = $ligne->date_prestation ?? '(vide)';
    $quantite = $ligne->quantite ?? '(null)';
    $prix = number_format($ligne->prix_unitaire, 2, '.', '');
    $montant = number_format($ligne->montant, 2, '.', '');
    $coutBenin = number_format($ligne->cout_benin, 2, '.', '');
    
    printf("%-3d | %-25s | %-12s | %-8s | %-12s | %-12s | %-12s\n",
        $idx + 1,
        $designationShort,
        $datePrestation,
        $quantite,
        $prix,
        $montant,
        $coutBenin
    );
}

echo str_repeat("-", 120) . "\n\n";

// Vérifier s'il y a des problèmes
$problemes = [];
foreach ($lignes as $ligne) {
    if ($ligne->designation === '') {
        $problemes[] = "Ligne vide (designation)";
    }
    if ($ligne->date_prestation === '' || $ligne->date_prestation === null) {
        $problemes[] = "Date de prestation vide/null";
    }
    if ($ligne->quantite === 0 || $ligne->quantite === null) {
        $problemes[] = "Quantité = 0 ou null";
    }
}

if (!empty($problemes)) {
    echo "⚠️  Problèmes détectés:\n";
    foreach (array_unique($problemes) as $pb) {
        echo "   - $pb\n";
    }
} else {
    echo "✅ Toutes les lignes semblent correctes!\n";
}
