<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BordereauLigne;

echo "=== Dernières 5 lignes Cadres de sous détails ===\n";

$lines = BordereauLigne::whereHas('bordereau.dossierDocument.typeDocument', function($q) {
    $q->where('nom', 'like', '%Cadres%');
})->latest('id')->limit(5)->get([
    'id', 'designation', 'matiere_frais', 'deborse_sec', 'coef_c1', 'coef_k', 
    'prix_vente_htva', 'total_materiel', 'location_amort', 'main_oeuvre'
]);

foreach($lines as $l) {
    echo "\n--- Ligne ID: {$l->id} ---\n";
    echo "  Designation: {$l->designation}\n";
    echo "  Total Materiel: '{$l->total_materiel}'\n";
    echo "  Location Amort: '{$l->location_amort}'\n";
    echo "  Matiere Frais: '{$l->matiere_frais}'\n";
    echo "  Main Oeuvre: '{$l->main_oeuvre}'\n";
    echo "  ---\n";
    echo "  Deborse Sec: '{$l->deborse_sec}'\n";
    echo "  Coef C1: '{$l->coef_c1}'\n";
    echo "  Coef K: '{$l->coef_k}'\n";
    echo "  Prix Vente HTVA: '{$l->prix_vente_htva}'\n";
    
    // Vérifier les calculs
    if (!empty($l->total_materiel) && !empty($l->location_amort) && !empty($l->matiere_frais) && !empty($l->main_oeuvre)) {
        $calc_deborse = floatval($l->total_materiel) + floatval($l->location_amort) + floatval($l->matiere_frais) + floatval($l->main_oeuvre);
        $calc_coef_c1 = floatval($l->total_materiel) > 0 ? ((floatval($l->deborse_sec ?? 0) - floatval($l->total_materiel)) / floatval($l->total_materiel)) * 100 : 0;
        $calc_coef_k = !empty($l->coef_c1) && floatval($l->coef_c1) != 100 ? 100 / (100 - floatval($l->coef_c1)) : 0;
        
        echo "  === Calculs attendus ===\n";
        echo "  Deborse Sec attendu: {$calc_deborse}\n";
        echo "  Coef C1 attendu: {$calc_coef_c1}\n";
        echo "  Coef K attendu: {$calc_coef_k}\n";
    }
}

echo "\n=== Total de lignes Cadres: " . BordereauLigne::whereHas('bordereau.dossierDocument.typeDocument', function($q) {
    $q->where('nom', 'like', '%Cadres%');
})->count() . " ===\n";
