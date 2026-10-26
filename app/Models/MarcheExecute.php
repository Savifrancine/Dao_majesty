<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Marche deja execute par l'entreprise (bibliotheque au niveau entreprise,
 * meme principe que EntreprisePersonnel pour les CV) : sert a pre-remplir en
 * un clic les lignes "Formulaire EXP - 4.1" (et par ricochet le "Formulaire
 * de qualification", qui se resynchronise deja depuis EXP-4.1) d'un dossier,
 * sans jamais ecraser une ligne deja modifiee a la main ensuite. Les 2
 * pieces jointes (contrat/bon de commande, attestation de bonne fin) restent
 * des EntrepriseDocument classiques (type bon_commande_contrats / bonne_fin),
 * juste rattachees a ce marche precis via marche_execute_id - jamais
 * copiees, uniquement referencees depuis le dossier qui importe ce marche.
 */
class MarcheExecute extends Model
{
    protected $table = 'marches_executes';

    protected $fillable = [
        'entreprise_id',
        'mois_annee_depart',
        'mois_annee_fin',
        'reference_marche',
        'identification_marche',
        'role_candidat',
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EntrepriseDocument::class);
    }

    public function contratDocument(): HasOne
    {
        return $this->hasOne(EntrepriseDocument::class)->where('type', 'bon_commande_contrats');
    }

    public function attestationDocument(): HasOne
    {
        return $this->hasOne(EntrepriseDocument::class)->where('type', 'bonne_fin');
    }
}
