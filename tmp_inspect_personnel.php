<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;
use App\Models\DossierDocument;

$type = TypeDocument::where('nom', "Liste du personnel affecté à l'exécution du marché")->first();
if (!$type) {
    echo "Type introuvable\n";
    exit(1);
}

echo "type_id={$type->id} nom={$type->nom}\n";

$docs = DossierDocument::where('type_document_id', $type->id)->get();
if ($docs->isEmpty()) {
    echo "Aucun document trouvé pour ce type\n";
    exit(0);
}

foreach ($docs as $doc) {
    echo "doc id={$doc->id} dossier={$doc->dossier_id} statut={$doc->statut} content_len=" . (is_null($doc->content) ? 'NULL' : strlen($doc->content)) . "\n";
    if (!is_null($doc->content) && $doc->content !== '') {
        echo substr($doc->content, 0, 200) . "\n";
    }
}
