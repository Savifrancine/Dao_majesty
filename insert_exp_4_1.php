<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Insérer ou mettre à jour EXP 4.1
DB::table('types_documents')->updateOrInsert(
    ['nom' => 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services'],
    [
        'type_formulaire' => 'formulaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

echo "✓ Formulaire EXP – 4.1 inséré/mis à jour en base\n";

// Vérifier
$result = DB::table('types_documents')
    ->where('nom', 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services')
    ->first();

if ($result) {
    echo "ID: " . $result->id . "\n";
    echo "Nom: " . $result->nom . "\n";
    echo "Type: " . $result->type_formulaire . "\n";
} else {
    echo "✗ Erreur: document non trouvé\n";
}
