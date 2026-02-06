<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoriteContractante extends Model
{
    protected $table = 'autorites_contractantes';

    protected $fillable = ['nom'];

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'autorite_contractante_id');
    }
}
