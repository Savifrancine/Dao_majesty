<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $templates = [
            [
                'nom' => "Modèle - Déclaration de garantie d'offre",
                'type' => 'declaration',
                'content' => "<p>Nous, <strong>{{societe}}</strong>, déclarons ...</p>",
            ],
            [
                'nom' => "Modèle - Formulaire de qualification",
                'type' => 'qualification',
                'content' => "Nous soussignés, {{societe}}, certifions l'exactitude des informations ci-après, attestant que nous remplissons les conditions de qualifications requises pour exécuter le Marché, fixées par l'Autorité contractante, à savoir :\n\na) nous sommes dûment autorisé par le fabriquant ou le producteur des Fournitures pour les fournir au Bénin ;\nb) nous sommes ou serons (si notre offre est acceptée) représenté par un agent équipé et en mesure de répondre aux besoins en matière d'entretien, de réparations des équipements, et de fournitures de pièces détachées.\nc) nous remplissons les conditions de qualification suivantes :\n\nCapacité technique et expérience\nNous avons exécuté [insérer « un » ou « deux »] marchés similaires, portant sur des fournitures ou des services de nature similaire au cours des [insérer « trois » ou « quatre »] dernières années. Ces marchés sont identifiés ci-après : [le candidat doit documenter distinctement ces marchés]\n[insérer toutes autres exigences en précisant la nature des documents justificatifs requis]",
            ],
            [
                'nom' => "Modèle - Lettre de soumission",
                'type' => 'lettre',
                'content' => "<p>Lettre de soumission pour {{societe}}</p>",
            ],
        ];

        foreach ($templates as $t) {
            DB::table('templates')->updateOrInsert(
                ['nom' => $t['nom']],
                [
                    'type' => $t['type'],
                    'content' => $t['content'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
