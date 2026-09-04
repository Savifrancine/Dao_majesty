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
// real storage/ folder collide with the public /storage/ URL. If a
// storage_private/ folder exists alongside storage/, use it instead so
// /storage can be a symlink to storage/app/public. (.env isn't loaded yet
// at this point in the boot process, so this can't be an env() check.)
if (is_dir($privateStorage = $app->basePath('storage_private'))) {
    $app->useStoragePath($privateStorage);
}

return $app;
