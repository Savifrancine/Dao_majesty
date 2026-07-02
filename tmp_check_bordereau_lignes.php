<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BordereauLigne;

$rows = BordereauLigne::with('bordereau')->latest()->take(15)->get(['id','bordereau_id','designation','quantite','date_prestation','prix_unitaire','montant','cout_benin']);
foreach ($rows as $row) {
    echo json_encode($row->toArray(), JSON_UNESCAPED_UNICODE) . "\n";
}
