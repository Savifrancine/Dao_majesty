<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogueArticle extends Model
{
    protected $fillable = [
        'categorie',
        'designation',
        'marque_type',
        'commentaire',
        'chemin_prospectus',
        'nom_original_prospectus',
        'utilisateur_id',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
