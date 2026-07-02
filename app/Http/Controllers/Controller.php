<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function denyEmployeeDeletion(): void
    {
        if (auth()->check() && auth()->user()->isEmploye()) {
            abort(403, 'Suppression non autorisée pour ce rôle.');
        }
    }
}
