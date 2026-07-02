<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulairePER extends Model
{
    protected $table = 'formulaire_pers';

    protected $fillable = [
        'utilisateur_id',
        'nom_candidat',
        'poste',
        'nom_personnel',
        'date_naissance',
        'qualifications',
        'nom_employeur',
        'adresse_employeur',
        'telephone',
        'contact_personnel',
        'telecopie',
        'email',
        'emploi_tenu',
        'nombre_annees_employeur',
        'experiences', // JSON column pour stocker l'historique d'expérience
        'signature',
        'date_signature',
        'lieu_signature',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_signature' => 'date',
        'experiences' => 'array',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
