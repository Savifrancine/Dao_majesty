<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bordereau extends Model
{
    protected $table = 'bordereaux';

    protected $fillable = [
        'dossier_document_id',
        'titre',
        'column_defs',
    ];

    protected $casts = [
        'column_defs' => 'json',
    ];

    /**
     * Get the dossier document that owns this bordereau.
     */
    public function dossierDocument(): BelongsTo
    {
        return $this->belongsTo(DossierDocument::class);
    }

    /**
     * Get all lignes for this bordereau.
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(BordereauLigne::class);
    }
}
