<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntreprisePersonnel extends Model
{
    protected $table = 'entreprise_personnels';

    protected $fillable = [
        'entreprise_id',
        'intitule',
        'nom',
        'poste',
        'date_naissance',
        'qualifications',
        'nom_employeur',
        'emploi_tenu',
        'adresse_employeur',
        'telephone',
        'telecopie',
        'email',
        'contact_personnel',
        'nombre_annees_employeur',
        'experiences',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'experiences' => 'array',
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function fichiers(): HasMany
    {
        return $this->hasMany(EntreprisePersonnelFichier::class)->orderBy('id', 'desc');
    }
}
