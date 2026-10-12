<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntrepriseDocumentFichier extends Model
{
    protected $table = 'entreprise_document_fichiers';

    protected $fillable = [
        'entreprise_document_id',
        'chemin_fichier',
        'nom_original',
        'utilisateur_id',
    ];

    public function entrepriseDocument(): BelongsTo
    {
        return $this->belongsTo(EntrepriseDocument::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
