<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Signataire extends Model
{
    protected $table = 'signataires';

    protected $fillable = [
        'nom',
        'prenom',
        'fonction',
        'signature_path',
        'cachet_path',
    ];

    public function dossiers(): BelongsToMany
    {
        return $this->belongsToMany(Dossier::class, 'dossier_signataire', 'signataire_id', 'dossier_id')->withPivot('role_signataire')->withTimestamps();
    }
}
