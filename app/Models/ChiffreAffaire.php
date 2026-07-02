<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiffreAffaire extends Model
{
    protected $table = 'chiffres_affaires';

    protected $fillable = [
        'dossier_id',
        'annee',
        'montant',
        'monnaie',
    ];

    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }
}
