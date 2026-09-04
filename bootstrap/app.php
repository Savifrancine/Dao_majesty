<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Some shared hosts can't set the document root to public/, which makes the
// real storage/ folder collide with the public /storage/ URL. Setting
// APP_STORAGE_PATH in .env moves the internal storage folder out of the way
// so /storage can be a symlink to storage/app/public instead.
if ($storagePath = env('APP_STORAGE_PATH')) {
    $app->useStoragePath($app->basePath($storagePath));
}

return $app;
