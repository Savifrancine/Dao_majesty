<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BordereauLigne;

$lines = BordereauLigne::whereHas('bordereau.dossierDocument.typeDocument', function($q) {
    $q->where('nom', 'like', '%Cadres%');
})->where(function($q) {
    $q->where('matiere_frais', '0')
      ->orWhere('matiere_frais', '0.00')
      ->orWhere('matiere_frais', '')
      ->orWhereNull('matiere_frais');
})->get(['id', 'designation', 'matiere_frais', 'total_materiel', 'location_amort', 'main_oeuvre', 'deborse_sec', 'coef_c1', 'coef_k']);

echo "=== Cadres lines with matiere_frais zero/empty ===\n";
foreach ($lines as $l) {
    echo "ID: {$l->id} | designation: {$l->designation} | matiere_frais: '{$l->matiere_frais}' | total_materiel: '{$l->total_materiel}' | location_amort: '{$l->location_amort}' | main_oeuvre: '{$l->main_oeuvre}' | deborse_sec: '{$l->deborse_sec}' | coef_c1: '{$l->coef_c1}' | coef_k: '{$l->coef_k}'\n";
}
echo "count: " . $lines->count() . "\n";
