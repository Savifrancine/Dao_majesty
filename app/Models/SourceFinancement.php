<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceFinancement extends Model
{
    protected $table = 'sources_financement';

    protected $fillable = ['nom'];

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'source_financement_id');
    }
}
