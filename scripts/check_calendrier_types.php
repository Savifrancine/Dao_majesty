<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;

$terms = ['%calendrier%', '%Calendrier%'];
$qb = TypeDocument::query();
$qb->where('nom', 'like', $terms[0]);
$qb->orWhere('nom', 'like', $terms[1]);
$types = $qb->get();

echo "Found " . $types->count() . " TypeDocument(s) matching 'calendrier':\n\n";
foreach ($types as $t) {
    echo "- ID: {$t->id} | nom: [{$t->nom}] | type_formulaire: {$t->type_formulaire}\n";
}

if ($types->isEmpty()) {
    echo "\nAucun TypeDocument trouvé. Vous pouvez créer le type manuellement via le script create_type_document.php ou via 'php artisan documents:sync'.\n";
}

