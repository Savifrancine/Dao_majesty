<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;
$name = "Bordereau des prix et calendrier d'exécution des services connexes";
$doc = TypeDocument::firstOrCreate(['nom' => $name], ['type_formulaire' => 'bordereau']);
if ($doc->wasRecentlyCreated) {
    echo "Created: " . $doc->id . " | " . $doc->nom . "\n";
} else {
    echo "Exists: " . $doc->id . " | " . $doc->nom . "\n";
}
