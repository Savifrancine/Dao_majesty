<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;

$name = $argv[1] ?? 'Listes des services connexes et calendrier de réalisation';
$type = $argv[2] ?? 'bordereau';

$td = TypeDocument::firstOrCreate(
    ['nom' => $name],
    ['type_formulaire' => $type]
);

$status = $td->wasRecentlyCreated ? 'Created' : 'Exists';
echo "{$status}: ID {$td->id} | nom: [{$td->nom}] | type_formulaire: {$td->type_formulaire}\n";

if (!$td->wasRecentlyCreated && $td->type_formulaire !== $type) {
    $td->update(['type_formulaire' => $type]);
    echo "Updated type_formulaire to '{$type}'.\n";
}

