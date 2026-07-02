<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\TypeDocument::where('nom', 'like', '%personnel%')->get() as $doc) {
    echo '[' . addslashes($doc->nom) . ']\n';
}
