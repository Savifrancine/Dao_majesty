<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeMarche extends Model
{
    protected $table = 'types_marches';

    protected $fillable = ['nom'];

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'type_marche_id');
    }
}
