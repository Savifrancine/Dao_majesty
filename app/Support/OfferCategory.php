<?php

namespace App\Support;

/**
 * Categories d'offre reconnues pour un meme marche (technique/financiere/
 * administrative - voir DossierController::OFFER_CATEGORIES, qui reste la
 * source de verite pour la detection/duplication d'offres). Extrait ici en
 * classe partagee car certaines vues (ex: lettre_soumission_pdf_content)
 * doivent elles aussi retirer le suffixe " — Offre financiere" etc. ajoute
 * au nom du dossier par DossierController::renameForOfferCategory(), sans
 * pouvoir appeler une methode privee du controleur depuis une vue.
 */
class OfferCategory
{
    public const LABELS = [
        'Offre financière',
        'Offre technique',
        'Offre administrative',
    ];

    /**
     * Retire un suffixe " — <libelle de categorie>" (ex: "POUELLE CALAVI —
     * Offre financière" devient "POUELLE CALAVI") : la categorie est deja
     * visible ailleurs (badges de la liste des dossiers, type_offre...) et
     * n'a pas besoin d'etre repetee dans les textes qui reprennent le nom du
     * dossier/l'objet du marche (ex: la Lettre de soumission).
     */
    public static function stripSuffix(?string $nom): string
    {
        $nom = trim((string) $nom);

        foreach (self::LABELS as $label) {
            $suffix = ' — ' . $label;
            if (str_ends_with(mb_strtolower($nom, 'UTF-8'), mb_strtolower($suffix, 'UTF-8'))) {
                return trim(mb_substr($nom, 0, mb_strlen($nom) - mb_strlen($suffix), 'UTF-8'));
            }
        }

        return $nom;
    }

    /**
     * Retrouve le suffixe " — <libelle de categorie>" (ex: " — Offre
     * financière") actuellement present a la fin de $nom, ou null si aucun.
     * Utilise pour re-appliquer le suffixe apres une modification du nom qui
     * ne doit pas en avoir connaissance (ex: formulaires d'etiquette
     * d'enveloppe, qui n'affichent/ne modifient jamais ce suffixe).
     */
    public static function extractSuffix(?string $nom): ?string
    {
        $nom = trim((string) $nom);

        foreach (self::LABELS as $label) {
            $suffix = ' — ' . $label;
            if (str_ends_with(mb_strtolower($nom, 'UTF-8'), mb_strtolower($suffix, 'UTF-8'))) {
                return mb_substr($nom, mb_strlen($nom) - mb_strlen($suffix), null, 'UTF-8');
            }
        }

        return null;
    }
}
