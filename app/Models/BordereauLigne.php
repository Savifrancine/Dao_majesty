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
        'quantite',
        'prix_unitaire',
        'montant',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'montant' => 'decimal:2',
    ];

    /**
     * Get the bordereau that owns this ligne.
     */
    public function bordereau(): BelongsTo
    {
        return $this->belongsTo(Bordereau::class);
    }
}
