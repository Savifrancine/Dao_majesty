<?php

namespace App\Models;

use App\Models\Entreprise;
use App\Models\Signataire;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaireExp42BSuite extends Model
{
    protected $table = 'formulaire_exp_4_2_b_suites';

    protected $fillable = [
        'utilisateur_id',
        'dossier_id',
        'entreprise_id',
        'signataire_id',
        'formulaire_type',
        'nom_candidat',
        'date_formulaire',
        'numero_adrp',
        'numero_marche',
        'description_similitude',
        'montant',
        'taille_physique',
        'complexite',
        'methodes_technologie',
        'autres_caracteristiques',
        'autorite_nom',
        'autorite_adresse',
        'autorite_telephone',
        'autorite_email',
        'nom_signataire',
        'fonction_signataire',
        'lieu_fait',
        'date_fait',
    ];

    protected $casts = [
        'date_formulaire' => 'date',
        'date_fait' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('formulaire_type', function (Builder $builder) {
            $builder->where('formulaire_type', 'B_SUITE');
        });
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function signataire(): BelongsTo
    {
        return $this->belongsTo(Signataire::class);
    }

    public function buildNumeroAdrp(?Dossier $dossier = null): string
    {
        if ($dossier) {
            $reference = trim($dossier->reference_dossier ?? $dossier->ref ?? '');
            $dateLancement = null;

            if (!empty($dossier->date_lancement)) {
                try {
                    $dateLancement = \Carbon\Carbon::parse($dossier->date_lancement)->format('d/m/Y');
                } catch (\Throwable $e) {
                    $dateLancement = $dossier->date_lancement;
                }
            }

            $titre = trim($dossier->titre_dossier ?? $dossier->titre_lot ?? '');
            $parts = [];

            if ($reference !== '') {
                $parts[] = $reference;
            }
            if ($dateLancement) {
                $parts[] = 'du ' . $dateLancement;
            }
            if ($titre !== '') {
                $parts[] = 'relatif à ' . $titre;
            }

            $numero = trim(implode(' ', $parts));
            if ($numero !== '') {
                return $numero;
            }
        }

        return trim($this->numero_adrp ?? '');
    }
}
