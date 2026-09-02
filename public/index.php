<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Resolve the real path so this keeps working when public/ is served
// through a symlink (PHP's __DIR__ does not follow symlinks itself).
$baseDir = realpath(__DIR__);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $baseDir.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $baseDir.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $baseDir.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
