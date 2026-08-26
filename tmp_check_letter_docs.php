<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$type = App\Models\TypeDocument::where('nom','Lettre de soumission')->first();
if (!$type) {
    echo "no type\n";
    exit(0);
}
$docs = App\Models\DossierDocument::where('type_document_id', $type->id)->get();
echo 'count=' . count($docs) . "\n";
foreach ($docs as $doc) {
    echo 'id=' . $doc->id . ' statut=' . $doc->statut . ' content=' . ($doc->content ? 'yes' : 'no') . ' files=' . $doc->fichiers()->count() . "\n";
}
