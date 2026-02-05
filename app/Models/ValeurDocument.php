<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValeurDocument extends Model
{
    protected $table = 'valeurs_documents';

    protected $fillable = [
        'dossier_document_id',
        'champ_document_id',
        'valeur',
        'utilisateur_id',
    ];

    /**
     * Get the dossier document that owns this valeur.
     */
    public function dossierDocument(): BelongsTo
    {
        return $this->belongsTo(DossierDocument::class);
    }

    /**
     * Get the champ document.
     */
    public function champDocument(): BelongsTo
    {
        return $this->belongsTo(ChampDocument::class);
    }

    /**
     * Get the utilisateur who entered this value.
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
