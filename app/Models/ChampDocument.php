<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChampDocument extends Model
{
    protected $table = 'champs_documents';

    protected $fillable = [
        'type_document_id',
        'nom_champ',
        'label',
        'type',
        'ordre',
    ];

    /**
     * Get the type document that owns this champ.
     */
    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class);
    }

    /**
     * Get all valeurs for this champ.
     */
    public function valeurs(): HasMany
    {
        return $this->hasMany(ValeurDocument::class);
    }
}
