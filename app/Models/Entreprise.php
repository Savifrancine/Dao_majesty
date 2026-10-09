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
        'finance_data',
        'personnel_data',
    ];

    protected $casts = [
        'finance_data' => 'array',
        'personnel_data' => 'array',
    ];

    /**
     * Get all dossiers for this entreprise.
     */
    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }
}
