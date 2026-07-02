<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TypeDocument;

function norm($s) {
    $s = trim((string)$s);
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '', $s);
    return $s;
}

$target = "Tableau de résumé des bordereaux de prix";
echo "Checking TypeDocument table for target: {$target}\n\n";

$all = TypeDocument::orderBy('id')->get();
echo "Total TypeDocument rows: " . $all->count() . "\n\n";
$found = null;
$suggestions = [];
foreach ($all as $t) {
    $n = $t->nom;
    $nn = norm($n);
    echo sprintf("%4s | ID=%-4d | %s\n", '', $t->id, $n);
    if ($n === $target) {
        $found = $t;
    }
    if ($nn === norm($target)) {
        $suggestions[] = $t;
    }
}

if ($found) {
    echo "\nExact match found: ID={$found->id} name='{$found->nom}'\n";
    exit(0);
}

if (!empty($suggestions)) {
    echo "\nNo exact match, but found normalized match(es):\n";
    foreach ($suggestions as $s) {
        echo "  - ID={$s->id} name='{$s->nom}'\n";
    }
    echo "\nIf these are acceptable, you can update the UI to use that TypeDocument id.\n";
    exit(0);
}

// Not found: create new TypeDocument with a valid enum value
try {
    $new = TypeDocument::create(['nom' => $target, 'type_formulaire' => 'formulaire']);
    echo "\nCreated new TypeDocument: ID={$new->id} name='{$new->nom}'\n";
} catch (\Throwable $e) {
    echo "\nFailed to create TypeDocument: " . $e->getMessage() . "\n";
    exit(2);
}

echo "\nDone.\n";
