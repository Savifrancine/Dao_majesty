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
    ];

    protected $casts = [
        'finance_data' => 'array',
    ];

    /**
     * Get all dossiers for this entreprise.
     */
    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Les personnels de l'entreprise (un meme nom peut avoir plusieurs
     * entrees - plusieurs "CV" distingues par leur intitule).
     */
    public function personnels(): HasMany
    {
        return $this->hasMany(EntreprisePersonnel::class);
    }
}
