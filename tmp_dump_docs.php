<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dossier;
use App\Models\DossierDocument;

$dossiers = Dossier::with(['documents.typeDocument'])->get();
foreach ($dossiers as $d) {
    echo "Dossier {$d->id} - {$d->nom_dossier} (statut: {$d->statut})\n";
    foreach ($d->documents as $doc) {
        $len = is_null($doc->content) ? 'null' : strlen($doc->content);
        $preview = is_null($doc->content) ? 'NULL' : substr($doc->content, 0, 200);
        echo "  Doc id={$doc->id} type_id={$doc->type_document_id} nom=" . ($doc->typeDocument?->nom ?? 'N/A') . " statut={$doc->statut} content_len={$len}\n";
        echo "    preview: " . preg_replace('/\s+/', ' ', $preview) . "\n";
    }
    echo "\n";
}
