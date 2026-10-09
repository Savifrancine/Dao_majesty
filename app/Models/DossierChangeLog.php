<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierChangeLog extends Model
{
    protected $table = 'dossier_change_logs';

    protected $fillable = [
        'dossier_id',
        'utilisateur_id',
        'type_document_id',
        'document_nom',
        'action',
        'resume',
    ];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class);
    }
}
