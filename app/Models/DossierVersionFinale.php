<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierVersionFinale extends Model
{
    protected $table = 'dossier_versions_finales';

    protected $fillable = [
        'dossier_id',
        'chemin_fichier',
        'nom_original',
        'commentaire',
        'utilisateur_id',
    ];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
