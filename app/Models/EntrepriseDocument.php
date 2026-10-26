<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntrepriseDocument extends Model
{
    protected $table = 'entreprise_documents';

    protected $fillable = [
        'entreprise_id',
        'marche_execute_id',
        'type',
        'annee',
        'banque',
        'intitule',
        'date_delivrance',
    ];

    protected $casts = [
        'date_delivrance' => 'date',
    ];

    /**
     * Duree de validite (en mois) des types de documents qui expirent : la
     * personne saisit seulement la date a laquelle elle a obtenu le
     * document, la plateforme calcule elle-meme la date d'expiration et
     * alerte quand elle approche ou est depassee. Les types absents de
     * cette liste (rccm, ifu, etat_financier) n'expirent jamais.
     */
    private const DUREES_VALIDITE_MOIS = [
        'non_exclusion' => 12,
        'categorisation' => 36,
        'regularite_fiscale' => 3,
        'cnss' => 3,
        'non_faillite' => 3,
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function marcheExecute(): BelongsTo
    {
        return $this->belongsTo(MarcheExecute::class);
    }

    public function fichiers(): HasMany
    {
        return $this->hasMany(EntrepriseDocumentFichier::class)->orderBy('id', 'desc');
    }

    /**
     * Date d'expiration calculee a partir de la date de delivrance et de la
     * duree de validite du type de document (null si le type n'expire pas
     * ou si aucune date de delivrance n'a ete saisie).
     */
    public function getDateExpirationAttribute(): ?\Illuminate\Support\Carbon
    {
        $duree = self::DUREES_VALIDITE_MOIS[$this->type] ?? null;
        if (!$duree || !$this->date_delivrance) {
            return null;
        }

        return $this->date_delivrance->copy()->addMonths($duree);
    }

    /**
     * Vrai si la date d'expiration (calculee) est deja passee (ex:
     * renouvellement annuel de l'Attestation de non-exclusion, ou tous les
     * 3 ans pour l'Attestation de categorisation).
     */
    public function estExpire(): bool
    {
        return $this->date_expiration !== null && $this->date_expiration->isPast();
    }

    /**
     * Vrai si la date d'expiration (calculee) arrive dans les $joursSeuil
     * jours (mais n'est pas encore depassee) : sert a alerter avant qu'un
     * document devienne caduc.
     */
    public function expireBientot(int $joursSeuil = 30): bool
    {
        return $this->date_expiration !== null
            && !$this->estExpire()
            && now()->diffInDays($this->date_expiration, false) <= $joursSeuil;
    }
}
