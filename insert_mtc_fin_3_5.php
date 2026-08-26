<?php
require 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create Capsule Connection
use Illuminate\Database\Capsule\Manager as DB;

$db = new DB;
$db->addConnection([
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', 'dao'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$db->setAsGlobal();
$db->bootEloquent();

$nom = 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours';
$type_formulaire = 'formulaire';

try {
    // Check if document already exists
    $existing = DB::table('types_documents')
        ->where('nom', $nom)
        ->first();

    if ($existing) {
        echo "✓ Document MTC/FIN 3.5 already exists in database (ID: {$existing->id})\n";
        echo "  Nom: {$existing->nom}\n";
        echo "  Type: {$existing->type_formulaire}\n";
    } else {
        // Insert new document
        $id = DB::table('types_documents')->insertGetId([
            'nom' => $nom,
            'type_formulaire' => $type_formulaire,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        echo "✓ Document MTC/FIN 3.5 inserted successfully (ID: $id)\n";
        echo "  Nom: $nom\n";
        echo "  Type: $type_formulaire\n";
    }

    // List all documents containing 'MTC' or 'FIN' for verification
    echo "\n--- All documents with MTC or FIN in name ---\n";
    $docs = DB::table('types_documents')
        ->where('nom', 'like', '%MTC%')
        ->orWhere('nom', 'like', '%FIN%')
        ->orderBy('nom')
        ->get();

    foreach ($docs as $doc) {
        echo "- [{$doc->id}] {$doc->nom} ({$doc->type_formulaire})\n";
    }

} catch (Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
?>
