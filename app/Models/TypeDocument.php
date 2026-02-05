<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDocument extends Model
{
    protected $table = 'types_documents';

    protected $fillable = [
        'nom',
        'type_formulaire',
    ];

    /**
     * Get all champs for this type document.
     */
    public function champs(): HasMany
    {
        return $this->hasMany(ChampDocument::class);
    }

    /**
     * Get all dossier documents for this type.
     */
    public function dossierDocuments(): HasMany
    {
        return $this->hasMany(DossierDocument::class);
    }
}
