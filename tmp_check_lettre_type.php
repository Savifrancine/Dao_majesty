<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$names = App\Models\TypeDocument::where('nom', 'like', '%Lettre%')->pluck('nom')->toArray();
echo implode(PHP_EOL, $names);
