<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntreprisePersonnelFichier extends Model
{
    protected $table = 'entreprise_personnel_fichiers';

    protected $fillable = [
        'entreprise_personnel_id',
        'chemin_fichier',
        'nom_original',
        'utilisateur_id',
    ];

    public function entreprisePersonnel(): BelongsTo
    {
        return $this->belongsTo(EntreprisePersonnel::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
