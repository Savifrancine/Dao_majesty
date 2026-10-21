<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DossierDocument extends Model
{
    protected $table = 'dossier_documents';

    protected $fillable = [
        'dossier_id',
        'type_document_id',
        'ordre',
        'statut',
        'content',
        'reference_model',
        'paysage',
        'linked_entreprise_document_ids',
        'excluded_entreprise_fichier_ids',
        'designations_supprimees',
    ];

    protected $casts = [
        'linked_entreprise_document_ids' => 'array',
        'excluded_entreprise_fichier_ids' => 'array',
        'designations_supprimees' => 'array',
        'paysage' => 'boolean',
    ];

    /**
     * Get the dossier that owns this document.
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Get the type document.
     */
    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class);
    }

    /**
     * Get all valeurs for this dossier document.
     */
    public function valeurs(): HasMany
    {
        return $this->hasMany(ValeurDocument::class);
    }

    /**
     * Get all fichiers for this dossier document.
     */
    public function fichiers(): HasMany
    {
        return $this->hasMany(DocumentFichier::class)->orderBy('id', 'desc');
    }

    /**
     * Get the bordereau if this is a bordereau document.
     */
    public function bordereau(): HasMany
    {
        return $this->hasMany(Bordereau::class);
    }

    /**
     * Fichiers des documents administratifs de l'entreprise (RCCM, IFU,
     * etats financiers...) lies a cette piece du dossier - references
     * directement, jamais copies, moins ceux explicitement exclus pour ce
     * dossier. Meme principe que FormulairePER::cvFichiers().
     */
    public function entrepriseFichiers(): \Illuminate\Support\Collection
    {
        $linkedIds = $this->linked_entreprise_document_ids ?? [];
        if (empty($linkedIds)) {
            return collect();
        }

        $excluded = $this->excluded_entreprise_fichier_ids ?? [];

        return EntrepriseDocument::whereIn('id', $linkedIds)
            ->with('fichiers')
            ->get()
            ->flatMap(fn (EntrepriseDocument $d) => $d->fichiers)
            ->reject(fn (EntrepriseDocumentFichier $f) => in_array($f->id, $excluded, true))
            ->values();
    }
}
