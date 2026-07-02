<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TypeDocument;
$rows = TypeDocument::where('nom', 'like', '%calendrier%')->orWhere('nom', 'like', '%Bordereau des prix%')->get();
foreach ($rows as $r) {
    echo "ID:" . $r->id . " | " . $r->nom . "\n";
}
