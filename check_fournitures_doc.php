<?php
require 'bootstrap/app.php';

use App\Models\TypeDocument;

$doc = TypeDocument::where('nom', 'Listes des Fournitures et Calendrier de livraison')->first();
if ($doc) {
    echo "✓ Document trouvé:\n";
    echo "  ID: " . $doc->id . "\n";
    echo "  Nom: " . $doc->nom . "\n";
    echo "  Type formulaire: " . $doc->type_formulaire . "\n";
} else {
    echo "✗ Document non trouvé!\n";
}
