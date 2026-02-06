<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procedure extends Model
{
    protected $table = 'procedures';

    protected $fillable = ['nom'];

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'procedure_id');
    }
}
