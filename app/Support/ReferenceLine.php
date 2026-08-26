<?php

namespace App\Support;

/**
 * Un même bloc "Numéro..." (référence/date/nom du dossier) apparaît avant le
 * tableau ou le contenu de plusieurs documents générés, mais chacun l'affichait
 * jusqu'ici avec une combinaison figée différente (référence seule, référence
 * + date, référence + date + nom du dossier, ou nom du dossier seul). Cette
 * classe centralise le rendu des 4 modèles, choisis par document via le champ
 * DossierDocument.reference_model — chaque appelant garde le libellé et le
 * format de date qu'il utilisait déjà, seule la combinaison affichée change.
 */
class ReferenceLine
{
    public const MODELS = ['full', 'ref_only', 'ref_date', 'nom_only'];

    public const DEFAULT_MODEL = 'full';

    public static function labels(): array
    {
        return [
            'full' => 'Référence + date + nom du dossier',
            'ref_only' => 'Référence seule',
            'ref_date' => 'Référence + date',
            'nom_only' => 'Nom du dossier seul',
        ];
    }

    public static function normalizeModel(?string $model): string
    {
        return in_array($model, self::MODELS, true) ? $model : self::DEFAULT_MODEL;
    }

    /**
     * Formate la date de lancement du dossier en français ("23 Juillet 2026"),
     * ou en majuscules si $uppercase est vrai ("23 JUILLET 2026").
     */
    public static function formatDate($dossier, bool $uppercase = false): ?string
    {
        if (empty($dossier->date_lancement)) {
            return null;
        }
        try {
            $c = \Illuminate\Support\Carbon::parse($dossier->date_lancement)->locale('fr');
            $formatted = $c->format('d') . ' ' . ucfirst($c->translatedFormat('F')) . ' ' . $c->format('Y');
            return $uppercase ? mb_strtoupper($formatted, 'UTF-8') : $formatted;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $label Préfixe déjà mis en forme par le document appelant
     *                       (ex: "AAO numéro :", "ADRP Numero", "DRP N°").
     * @param string $refValue Numéro de référence déjà nettoyé par l'appelant
     *                         (avec ou sans préfixe "ADRP N°" selon ce que ce
     *                         document affichait déjà).
     * @param string|null $dateStr Date déjà formatée par l'appelant (ou null).
     * @param string $dateJoiner Mot reliant la référence à la date ("du" ou "DU").
     * @param string $nomDossier Nom du dossier.
     * @param string|null $model Un des MODELS ci-dessus (par défaut "full").
     */
    public static function render(
        string $label,
        string $refValue,
        ?string $dateStr,
        string $nomDossier,
        ?string $model,
        string $dateJoiner = 'du'
    ): string {
        $model = self::normalizeModel($model);

        $refLine = trim(e($label) . ' ' . e($refValue));
        if (($model === 'full' || $model === 'ref_date') && $dateStr) {
            $refLine .= ' ' . e($dateJoiner) . ' ' . e($dateStr);
        }

        return match ($model) {
            'nom_only' => e($nomDossier),
            'full' => $refLine . ($nomDossier !== '' ? '<br>' . e($nomDossier) : ''),
            default => $refLine,
        };
    }
}
