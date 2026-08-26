<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entreprise extends Model
{
    protected $table = 'entreprises';

    protected $fillable = [
        'nom',
        'sigle',
        'adresse',
        'telephone',
        'email',
        'logo',
        'pays',
        'ifu',
        'rccm',
        'registre_path',
        'adresse_officielle',
        'annee_enregistrement',
        'responsable',
        'fonction_responsable',
    ];

    /**
     * Get all dossiers for this entreprise.
     */
    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }
}
