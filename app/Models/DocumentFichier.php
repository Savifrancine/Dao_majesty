<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentFichier extends Model
{
    protected $table = 'documents_fichiers';

    protected $fillable = [
        'dossier_document_id',
        'chemin_fichier',
        'utilisateur_id',
    ];

    /**
     * Get the dossier document that owns this fichier.
     */
    public function dossierDocument(): BelongsTo
    {
        return $this->belongsTo(DossierDocument::class);
    }

    /**
     * Get the utilisateur who uploaded this file.
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
