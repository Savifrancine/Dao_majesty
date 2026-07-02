<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$table = 'dossier_documents';
$has = Schema::hasTable($table) ? Schema::hasColumn($table, 'content') : null;

echo "Table : $table\n";
if ($has === null) {
    echo "La table $table n'existe pas\n";
    exit(0);
}

echo "Colonne 'content' présente ? " . ($has ? 'OUI' : 'NON') . "\n\n";

$cols = DB::getSchemaBuilder()->getColumnListing($table);
foreach ($cols as $c) {
    echo "- $c\n";
}

// Show raw column types if possible (MySQL)
try {
    $driver = DB::getDriverName();
    echo "\nDriver DB: $driver\n";
    if ($driver === 'mysql') {
        $res = DB::select("SHOW COLUMNS FROM $table");
        foreach ($res as $r) {
            echo "{$r->Field} : {$r->Type} (Null: {$r->Null}) Default: {$r->Default}\n";
        }
    }
} catch (\Throwable $e) {
    // ignore
}

return 0;
