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
        'beneficiaires_effectifs_data',
    ];

    protected $casts = [
        'finance_data' => 'array',
        'beneficiaires_effectifs_data' => 'array',
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

    /**
     * Documents administratifs de l'entreprise (RCCM, IFU, etats financiers
     * par annee...) : references directement depuis un dossier, jamais
     * copies, sur le meme principe que les CV des personnels.
     */
    public function documentsAdministratifs(): HasMany
    {
        return $this->hasMany(EntrepriseDocument::class);
    }

    /**
     * Marches deja executes par l'entreprise (bibliotheque reutilisable
     * depuis n'importe quel dossier pour pre-remplir Formulaire EXP - 4.1 /
     * Formulaire de qualification, et relier les pieces jointes du marche
     * choisi - voir MarcheExecute).
     */
    public function marchesExecutes(): HasMany
    {
        return $this->hasMany(MarcheExecute::class);
    }
}
