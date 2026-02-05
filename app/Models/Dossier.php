<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dossier extends Model
{
    protected $table = 'dossiers';

    protected $fillable = [
        'type_dossier_id',
        'entreprise_id',
        'nom_dossier',
        'objectif',
        'lot',
        'public_prive',
        'statut',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the type dossier that owns this dossier.
     */
    public function typeDossier(): BelongsTo
    {
        return $this->belongsTo(TypeDossier::class, 'type_dossier_id');
    }

    /**
     * Get the entreprise that owns this dossier.
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Get all documents for this dossier.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(DossierDocument::class);
    }
}
