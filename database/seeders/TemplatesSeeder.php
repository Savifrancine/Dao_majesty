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
