<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulairePerFichier extends Model
{
    protected $table = 'formulaire_per_fichiers';

    protected $fillable = [
        'formulaire_per_id',
        'chemin_fichier',
        'nom_original',
        'utilisateur_id',
    ];

    public function formulairePer(): BelongsTo
    {
        return $this->belongsTo(FormulairePER::class, 'formulaire_per_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
