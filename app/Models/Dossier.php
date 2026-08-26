<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dossier extends Model
{
    protected $table = 'dossiers';

    protected $fillable = [
        'type_dossier_id',
        'entreprise_id',
        'nom_dossier',
        'objectif',
        'lot',
        'titre_dossier',
        'type_offre',
        'procedure_id',
        'numero_ao',
        'date_ao',
        'objet_marche',
        'lots',
        'autorite_contractante_id',
        'source_financement_id',
        'reference_step',
        'ref',
        'annee_gestion',
        'ville_signature',
        'date_signature',
        'mois_edition',
        'public_prive',
        'page_garde_path',
        'republique',
        'ministere',
        'direction',
        'services_projet',
        'destinataires',
        'reference_dossier',
        'date_lancement',
        'titre_lot',
        'autres_details',
        'mois_depot',
        'annee_depot',
        'date_soumission',
        'statut',
        'source_financement',
        'gestion',
        'imputation_budgetaire',
        'accord_pret',
        'destinataire_adresse',
        'prmp_titre',
        'prmp_nom',
        'prmp_telephone',
        'prmp_email',
        'institution_nom',
        'secretariat_adresse',
    ];

    protected $casts = [
        'date_lancement' => 'date',
        'date_soumission' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the type dossier that owns this dossier.
     */
    public function typeDossier(): BelongsTo
    {
        return $this->belongsTo(TypeDossier::class, 'type_dossier_id');
    }

    /**
     * Get the entreprise that owns this dossier.
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Get all documents for this dossier.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(DossierDocument::class);
    }

    

    public function signataires()
    {
        return $this->belongsToMany(\App\Models\Signataire::class, 'dossier_signataire', 'dossier_id', 'signataire_id')->withPivot('role_signataire')->withTimestamps();
    }

    public function chiffresAffaires()
    {
        return $this->hasMany(\App\Models\ChiffreAffaire::class, 'dossier_id');
    }

    /**
     * Get users who can access this dossier.
     */
    public function utilisateurs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Utilisateur::class, 'dossier_utilisateurs', 'dossier_id', 'utilisateur_id')->withTimestamps();
    }
}
