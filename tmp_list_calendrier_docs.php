<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;

$docs = TypeDocument::where('nom', 'like', '%calendrier%')->get();
if ($docs->isEmpty()) {
    echo "Aucun TypeDocument trouvé contenant 'calendrier'.\n";
    return;
}
foreach ($docs as $doc) {
    echo sprintf("%d | %s | %s\n", $doc->id, $doc->nom, $doc->type_formulaire);
}
