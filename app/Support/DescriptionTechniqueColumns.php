<?php

namespace App\Support;

/**
 * Colonnes "spécifications" (configurables par tableau) du Formulaire
 * Description technique des fournitures/services. Un tableau peut masquer
 * une colonne ou renommer son libellé pour reproduire un modèle papier
 * différent (ex: une seule colonne "Description détaillée des services").
 * Source unique de vérité pour la liste des 3 colonnes connues et leurs
 * libellés par défaut, partagée entre la sauvegarde (DossierController) et
 * l'affichage (formulaire step6 + PDF).
 */
class DescriptionTechniqueColumns
{
    public const DEFAULTS = [
        'specifications_techniques' => 'Spécifications techniques',
        'specifications_obligatoires' => 'Spécifications obligatoires',
        'specifications_proposees' => 'Spécifications proposées',
    ];

    /**
     * Normalise/complète column_defs (tel que stocké en base, potentiellement
     * null pour un ancien bordereau créé avant cette fonctionnalité) : garantit
     * les 3 clés connues, dans le même ordre, chacune avec un libellé et une
     * visibilité definis. Au moins une colonne reste toujours visible (sinon
     * le tableau n'aurait plus aucune colonne de contenu, en plus de N°/
     * Désignation).
     *
     * @return array<int,array{key:string,label:string,visible:bool}>
     */
    public static function resolve(?array $columnDefs): array
    {
        $byKey = [];
        foreach ($columnDefs ?? [] as $entry) {
            if (is_array($entry) && isset($entry['key'])) {
                $byKey[$entry['key']] = $entry;
            }
        }

        $resolved = [];
        foreach (self::DEFAULTS as $key => $defaultLabel) {
            $entry = $byKey[$key] ?? [];
            $label = trim((string) ($entry['label'] ?? ''));
            $resolved[] = [
                'key' => $key,
                'label' => $label !== '' ? $label : $defaultLabel,
                'visible' => array_key_exists('visible', $entry) ? (bool) $entry['visible'] : true,
            ];
        }

        if (!array_filter($resolved, fn ($c) => $c['visible'])) {
            $resolved[0]['visible'] = true;
        }

        return $resolved;
    }

    /**
     * @param array<int,array{key:string,label:string,visible:bool}> $resolved
     * @return array<int,array{key:string,label:string}>
     */
    public static function visibleOnly(array $resolved): array
    {
        return array_values(array_filter($resolved, fn ($c) => $c['visible']));
    }
}
