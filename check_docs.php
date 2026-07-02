<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TypeDocument;

$programme = TypeDocument::where('nom', 'Programme d\'activités')->first();
if ($programme) {
    echo "Programme d'activités EXISTS - ID: {$programme->id}, type_formulaire: {$programme->type_formulaire}\n";
} else {
    echo "Programme d'activités NOT FOUND\n";
}

$bordereau = TypeDocument::where('nom', 'Bordereau prix unitaire')->first();
if ($bordereau) {
    echo "Bordereau prix unitaire EXISTS - ID: {$bordereau->id}, type_formulaire: {$bordereau->type_formulaire}\n";
} else {
    echo "Bordereau prix unitaire NOT FOUND\n";
}

$methodes = TypeDocument::where('nom', 'Méthodes d\'exécution')->first();
if ($methodes) {
    echo "Méthodes d'exécution EXISTS - ID: {$methodes->id}, type_formulaire: {$methodes->type_formulaire}\n";
} else {
    echo "Méthodes d'exécution NOT FOUND\n";
}