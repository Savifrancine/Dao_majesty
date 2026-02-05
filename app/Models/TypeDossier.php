<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDossier extends Model
{
    protected $table = 'types_dossiers';

    protected $fillable = [
        'nom',
    ];

    /**
     * Get all dossiers for this type.
     */
    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }
}
