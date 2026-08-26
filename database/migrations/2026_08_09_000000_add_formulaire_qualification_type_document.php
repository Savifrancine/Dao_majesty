<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('types_documents')->where('nom', 'Formulaire de qualification')->exists()) {
            DB::table('types_documents')->insert([
                'nom' => 'Formulaire de qualification',
                'type_formulaire' => 'fichier',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('templates')->updateOrInsert(
            ['nom' => 'Modèle - Formulaire de qualification'],
            [
                'type' => 'qualification',
                'content' => "Nous soussignés, {{societe}}, certifions l'exactitude des informations ci-après, attestant que nous remplissons les conditions de qualifications requises pour exécuter le Marché, fixées par l'Autorité contractante, à savoir :\n\na) nous sommes dûment autorisé par le fabriquant ou le producteur des Fournitures pour les fournir au Bénin ;\nb) nous sommes ou serons (si notre offre est acceptée) représenté par un agent équipé et en mesure de répondre aux besoins en matière d'entretien, de réparations des équipements, et de fournitures de pièces détachées.\nc) nous remplissons les conditions de qualification suivantes :\n\nCapacité technique et expérience\nNous avons exécuté [insérer « un » ou « deux »] marchés similaires, portant sur des fournitures ou des services de nature similaire au cours des [insérer « trois » ou « quatre »] dernières années. Ces marchés sont identifiés ci-après : [le candidat doit documenter distinctement ces marchés]\n[insérer toutes autres exigences en précisant la nature des documents justificatifs requis]",
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('types_documents')->where('nom', 'Formulaire de qualification')->delete();
        DB::table('templates')->where('nom', 'Modèle - Formulaire de qualification')->delete();
    }
};
