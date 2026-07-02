<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$docs = Illuminate\Support\Facades\DB::table('types_documents')->select('id','nom')->orderBy('id')->get();
foreach ($docs as $doc) {
    echo $doc->id . ' => ' . $doc->nom . PHP_EOL;
}
