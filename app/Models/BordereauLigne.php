<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BordereauLigne extends Model
{
    protected $table = 'bordereau_lignes';

    protected $fillable = [
        'bordereau_id',
        'designation',
        'unite_physique',
        'quantite',
        'prix_unitaire',
        'montant',
        'cout_benin',
        'site',
        'date_prestation',
        'frequence',
        'specifications_techniques',
        'specifications_obligatoires',
        'specifications_proposees',
        'total_materiel',
        'location_amort',
        'matiere_frais',
        'main_oeuvre',
        'deborse_sec',
        'coef_c1',
        'coef_k',
        'prix_vente_htva',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'montant' => 'decimal:2',
        'cout_benin' => 'decimal:2',
        'frequence' => 'string',
    ];

    /**
     * Get the bordereau that owns this ligne.
     */
    public function bordereau(): BelongsTo
    {
        return $this->belongsTo(Bordereau::class);
    }
}
