<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$doc = App\Models\DossierDocument::find(83);
if (!$doc) {
    echo "no doc\n";
    exit(0);
}
echo 'id=' . $doc->id . ' statut=' . $doc->statut . "\n";
echo 'content=' . ($doc->content ? 'yes' : 'no') . "\n";
echo 'content_value=' . substr($doc->content, 0, 200) . "\n";
foreach ($doc->fichiers as $file) {
    echo 'file=' . $file->chemin_fichier . ' exists=' . (file_exists(__DIR__ . '/storage/app/public/' . $file->chemin_fichier) ? 'yes' : 'no') . "\n";
}
