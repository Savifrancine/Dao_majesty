<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach ([17, 19, 20, 18] as $typeId) {
    $rows = App\Models\DossierDocument::where('dossier_id', 40)->where('type_document_id', $typeId)->get();
    echo "TypeDocument $typeId rows=" . count($rows) . "\n";
    foreach ($rows as $r) {
        echo '  row id=' . $r->id . ' status=' . $r->statut . ' type=' . $r->type_document_id . "\n";
    }
}
