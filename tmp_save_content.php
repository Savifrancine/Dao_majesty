<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DossierDocument;

// Optionnel: fournir l'ID du dossier_document en argument
$argvId = $argv[1] ?? null;

if ($argvId) {
    $doc = DossierDocument::find($argvId);
    if (!$doc) {
        echo "DossierDocument id={$argvId} introuvable\n";
        exit(1);
    }
} else {
    $doc = DossierDocument::whereNull('content')->first();
    if (!$doc) {
        echo "Aucun DossierDocument avec content NULL trouvé\n";
        exit(0);
    }
}

echo "Avant: doc id={$doc->id} statut={$doc->statut} content_len=" . (is_null($doc->content) ? 'NULL' : strlen($doc->content)) . "\n";

$payload = [
    ['poste' => 'Responsable', 'nom' => 'Test Pers'],
    ['poste' => 'Assistant', 'nom' => 'Pers 2'],
];
$doc->content = json_encode($payload, JSON_UNESCAPED_UNICODE);
$doc->statut = 'complete';
$ok = $doc->save();

echo "Sauvegarde retour: " . ($ok ? 'OK' : 'FAIL') . "\n";

$doc2 = DossierDocument::find($doc->id);
echo "Après: doc id={$doc2->id} statut={$doc2->statut} content_len=" . (is_null($doc2->content) ? 'NULL' : strlen($doc2->content)) . "\n";
if (!is_null($doc2->content)) {
    echo "Preview: " . substr($doc2->content, 0, 400) . "\n";
}

return 0;
