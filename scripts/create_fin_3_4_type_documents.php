<?php
// Script to create missing FIN 3.4 TypeDocument entries
require __DIR__ . '/../bootstrap/app.php';

use App\Models\TypeDocument;

$app = app();

$documents = [
    [
        'nom' => 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière',
        'type_formulaire' => 'fichier'
    ],
    [
        'nom' => 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière',
        'type_formulaire' => 'fichier'
    ]
];

foreach ($documents as $doc) {
    $existing = TypeDocument::where('nom', $doc['nom'])->first();
    if (!$existing) {
        $created = TypeDocument::create([
            'nom' => $doc['nom'],
            'type_formulaire' => $doc['type_formulaire']
        ]);
        echo "Created: ID {$created->id} | nom: [{$doc['nom']}] | type_formulaire: {$doc['type_formulaire']}\n";
    } else {
        echo "Exists: ID {$existing->id} | nom: [{$doc['nom']}]\n";
    }
}
