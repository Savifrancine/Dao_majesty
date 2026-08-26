<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDocument extends Model
{
    protected $table = 'types_documents';

    protected $fillable = [
        'nom',
        'type_formulaire',
    ];

    /**
     * Get all champs for this type document.
     */
    public function champs(): HasMany
    {
        return $this->hasMany(ChampDocument::class);
    }

    /**
     * Get all dossier documents for this type.
     */
    public function dossierDocuments(): HasMany
    {
        return $this->hasMany(DossierDocument::class);
    }

    /**
     * Noms des pièces qui sont de simples fichiers téléversés par le candidat
     * (aucun formulaire ni rendu généré ne leur correspond). Liste normalisée
     * en minuscules, utilisée à la fois pour le PDF du dossier et l'aperçu.
     */
    public static function uploadOnlyNames(): array
    {
        return [
            'rccm',
            'relevé d\'identité bancaire (rib)',
            'pièce d\'identité du premier responsable',
            'déclaration de l\'autorité contractante',
            'fiche technique de chaque article, délivrée par le fabricant',
            'copie de l\'arrêté du ministre de la santé portant autorisation d\'importation, de détention et de vente des équipements médicaux',
            'attestation d\'identification de statut',
            'attestation de visite de site',
            'attestation de bonne fin',
            'bon de commande et contrats',
            'preuves de propriété des matériels adéquats nécessaire à la bonne exécution du marché',
            'attestation / preuve de vente des équipements ou des pièces de recharge',
            'attestation / certificat de formation ou de qualifications en maintenance(sur au moins une équipements)',
            'etats financiers certifiés',
            'attestation de capacité financière',
            'copie legalisee de l\'identifiant fiscal unique (ifu)',
            'attestation de non-faillite datant de moins de trois (03) mois',
            'attestation d\'imposition ou de situation fiscale en cours de validite',
            'attestation de regularite a la cnss',
            'attestation de non imposition',
            'attestation de non-exclusion de la commande publique',
            'attestation de non-condamnation pour fraude, corruption ou fausse declaration',
            'attestation de nationalite ou document de constitution legale de l\'entreprise',
            'statuts de la societe et pv de nomination du gerant',
            'copie du quitus fiscal',
            'attestation de situation reguliere vis-a-vis des organismes de credit',
            'formulaire mat',
            'formulaire per',
            'formulaire fin 3.4 (a) modèle d\'attestation de capacité financière',
            'formulaire fin 3.4 (b) modèle de lettre de confirmation de la capacité financière',
            'formulaire exp – 4.2 a) expérience spécifique de fournitures/services',
            'formulaire exp – 4.2 a) (suite) expérience spécifique de fournitures/services dans les activités principales (suite)',
            'formulaire exp – 4.2 b)  expérience spécifique de fournitures',
            'formulaire exp – 4.2 b) (suite) expérience spécifique de fournitures/services dans les activités principales (suite)',
        ];
    }

    /**
     * Détermine si un nom de pièce correspond à un simple fichier téléversé
     * (sans formulaire ni rendu généré dédié).
     */
    public static function isUploadOnlyName(?string $nom, ?string $typeFormulaire = null): bool
    {
        if (trim($typeFormulaire ?? '') === 'fichier') {
            return true;
        }

        $lower = mb_strtolower(trim($nom ?? ''), 'UTF-8');

        return in_array($lower, self::uploadOnlyNames(), true)
            || str_contains($lower, 'attestation de situation reguliere')
            || str_contains($lower, 'attestation de non imposition');
    }

    /**
     * Noms des pièces dont le tableau du PDF comporte plus de 3 colonnes :
     * elles doivent être générées en orientation paysage.
     */
    public static function landscapeTableNames(): array
    {
        return [
            'Bordereau prix unitaire',
            'Bordereau des prix pour les fournitures à importer',
            'Bordereau des prix des fournitures, déjà importées',
            'Bordereau des prix pour les fournitures fabriquées au Bénin',
            'Bordereau des prix et calendrier d\'exécution des services connexes',
            'Listes des services connexes et calendrier de réalisation',
            'Listes des Fournitures et Calendrier de livraison',
            'Cadres de sous détails des prix unitaire',
            'Programme d\'activités',
            'Méthodes d\'exécution',
            'Calendrier d\'exécution',
            'Description technique des fournitures/services',
            'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours',
            'Plan de charge',
        ];
    }

    public static function isLandscapeTableName(?string $nom): bool
    {
        return in_array(trim($nom ?? ''), self::landscapeTableNames(), true);
    }

    /**
     * Noms des pièces qui affichent, avant leur tableau ou contenu, un bloc
     * "Numéro..." (référence du dossier, éventuellement avec la date et/ou le
     * nom du dossier) — voir App\Support\ReferenceLine pour les modèles
     * d'affichage possibles, choisis par document via reference_model.
     */
    public static function referenceLineNames(): array
    {
        return [
            'Déclaration de garantie d\'offre',
            'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges',
            'Formulaire de qualification',
            'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
            'Bordereau des prix pour les fournitures à importer',
            'Listes des Fournitures et Calendrier de livraison',
            'Cadres de sous détails des prix unitaire',
            'Bordereau des prix et calendrier d\'exécution des services connexes',
            'Listes des services connexes et calendrier de réalisation',
        ];
    }

    public static function isReferenceLineName(?string $nom): bool
    {
        return in_array(trim($nom ?? ''), self::referenceLineNames(), true);
    }

    /**
     * Modèle de référence par défaut pour chaque pièce, correspondant à ce qui
     * était déjà affiché avant que ce réglage soit configurable.
     */
    public static function defaultReferenceModelFor(?string $nom): string
    {
        $nom = trim($nom ?? '');

        return match (true) {
            $nom === 'Déclaration de garantie d\'offre' => 'ref_date',
            $nom === 'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges' => 'full',
            $nom === 'Formulaire de qualification' => 'ref_only',
            $nom === 'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat' => 'nom_only',
            default => 'ref_only',
        };
    }
}
