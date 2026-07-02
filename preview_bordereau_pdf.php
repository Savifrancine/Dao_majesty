<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dossier;

// Dossier 40 qui a le bordereau
$dossier = Dossier::find(40);
if (!$dossier) {
    echo "Dossier not found\n";
    exit(1);
}

$dossier->load([
    'documents.typeDocument.champs',
    'documents.bordereau.lignes',
    'documents.valeurs',
    'documents.fichiers',
    'entreprise',
    'typeDossier',
    'signataires'
]);

$excludedDocNames = [
    'Déclaration de garantie',
    "Déclaration de garantie d'offre",
];

$documents = $dossier->documents->filter(function ($doc) use ($excludedDocNames) {
    return $doc->typeDocument && !in_array(trim($doc->typeDocument->nom), $excludedDocNames, true);
})->sortBy('ordre')->values();

// Chercher le bordereau fournitures
$foundBordereau = false;
foreach ($documents as $doc) {
    if (strcasecmp(trim($doc->typeDocument->nom), 'Bordereau des prix pour les fournitures à importer') === 0) {
        $foundBordereau = true;
        echo "Found bordereau document\n";
        echo "Bordereaux count: " . $doc->bordereau->count() . "\n\n";
        
        foreach ($doc->bordereau as $bordereau) {
            echo "=== BORDEREAU TABLE HTML OUTPUT ===\n";
            echo "<table>\n";
            echo "  <tbody>\n";
            
            foreach ($bordereau->lignes as $index => $ligne) {
                $prix = (float) ($ligne->prix_unitaire ?? 0);
                $quantite = (float) ($ligne->quantite ?? 1);
                $montant = $prix * $quantite;
                $coutBenin = (float) ($ligne->cout_benin ?? 0);
                
                echo "    <tr>\n";
                echo "      <td>" . ($index + 1) . "</td>\n";
                echo "      <td>" . $ligne->designation . "</td>\n";
                echo "      <td>" . ($ligne->date_prestation ?? '') . "</td>\n";
                echo "      <td>" . number_format($quantite, 2, '.', ' ') . "</td>\n";
                echo "      <td>" . ($prix ? number_format($prix, 0, ',', ' ') : '') . "</td>\n";
                echo "      <td>" . ($montant ? number_format($montant, 0, ',', ' ') : '') . "</td>\n";
                echo "      <td>" . ($coutBenin ? number_format($coutBenin, 0, ',', ' ') : '') . "</td>\n";
                echo "    </tr>\n";
            }
            
            echo "  </tbody>\n";
            echo "</table>\n";
            echo "=== END TABLE ===\n";
        }
        break;
    }
}

if (!$foundBordereau) {
    echo "Bordereau document not found in filtered documents\n";
}
?>
