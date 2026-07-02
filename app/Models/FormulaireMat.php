<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaireMat extends Model
{
    protected $table = 'formulaire_mats';

    protected $fillable = [
        'utilisateur_id',
        'piece_materiel',
        'fabricant',
        'modele_puissance',
        'capacite',
        'annee_fabrication',
        'localisation',
        'engagements',
        'provenance',
        'signataire_id',
        'lieu_fait',
        'date_fait',
    ];

    protected $casts = [
        'date_fait' => 'date',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function signataire(): BelongsTo
    {
        return $this->belongsTo(Signataire::class);
    }
}
