<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Vérifier MTC/FIN 3.5
$mtc = DB::table('types_documents')
    ->where('nom', 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours')
    ->first();

echo "=== MTC/FIN 3.5 ===\n";
if ($mtc) {
    echo "ID: " . $mtc->id . "\n";
    echo "Nom: " . $mtc->nom . "\n";
    echo "Type: " . $mtc->type_formulaire . "\n";
} else {
    echo "Non trouvé\n";
}

echo "\n=== EXP 4.1 ===\n";
$exp = DB::table('types_documents')
    ->where('nom', 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services')
    ->first();

if ($exp) {
    echo "ID: " . $exp->id . "\n";
    echo "Nom: " . $exp->nom . "\n";
    echo "Type: " . $exp->type_formulaire . "\n";
} else {
    echo "Non trouvé\n";
}

// Afficher tous les types_documents pour vérifier
echo "\n=== Total types_documents ===\n";
$count = DB::table('types_documents')->count();
echo "Total: $count\n";
