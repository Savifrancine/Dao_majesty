<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FicheTechniqueFichier extends Model
{
    protected $table = 'fiche_technique_fichiers';

    protected $fillable = [
        'dossier_document_id',
        'article_index',
        'chemin_fichier',
        'nom_original',
        'utilisateur_id',
    ];

    public function dossierDocument(): BelongsTo
    {
        return $this->belongsTo(DossierDocument::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
