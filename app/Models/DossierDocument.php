<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DossierDocument extends Model
{
    protected $table = 'dossier_documents';

    protected $fillable = [
        'dossier_id',
        'type_document_id',
        'ordre',
        'statut',
        'content',
        'reference_model',
    ];

    /**
     * Get the dossier that owns this document.
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Get the type document.
     */
    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class);
    }

    /**
     * Get all valeurs for this dossier document.
     */
    public function valeurs(): HasMany
    {
        return $this->hasMany(ValeurDocument::class);
    }

    /**
     * Get all fichiers for this dossier document.
     */
    public function fichiers(): HasMany
    {
        return $this->hasMany(DocumentFichier::class)->orderBy('id', 'desc');
    }

    /**
     * Get the bordereau if this is a bordereau document.
     */
    public function bordereau(): HasMany
    {
        return $this->hasMany(Bordereau::class);
    }
}
