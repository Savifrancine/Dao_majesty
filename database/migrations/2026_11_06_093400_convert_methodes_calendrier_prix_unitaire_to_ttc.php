<?php

use App\Models\BordereauLigne;
use App\Models\TypeDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Jusqu'ici, le prix unitaire des lignes de "Méthodes d'exécution" et
 * "Calendrier d'exécution" était stocké HT en base (comme les autres
 * bordereaux de prix) et la conversion en TTC (x1.18) n'était appliquée qu'à
 * l'affichage, uniquement dans le PDF. La reprise automatique entre
 * documents stocke désormais directement le prix TTC pour ces deux types
 * (voir App\Support\PrixUnitaireTtc), et le PDF n'applique plus cette
 * conversion à l'affichage : les lignes déjà enregistrées doivent donc être
 * converties une bonne fois en base pour continuer à afficher exactement le
 * même montant qu'avant.
 */
return new class extends Migration
{
    private const DOC_NAMES = ["Méthodes d'exécution", "Calendrier d'exécution"];
    private const MULTIPLICATEUR = 1.18;

    public function up(): void
    {
        $typeIds = TypeDocument::whereIn('nom', self::DOC_NAMES)->pluck('id');
        if ($typeIds->isEmpty()) {
            return;
        }

        $dossierDocumentIds = DB::table('dossier_documents')
            ->whereIn('type_document_id', $typeIds)
            ->pluck('id');

        $bordereauIds = DB::table('bordereaux')
            ->whereIn('dossier_document_id', $dossierDocumentIds)
            ->pluck('id');

        BordereauLigne::whereIn('bordereau_id', $bordereauIds)
            ->whereNotNull('prix_unitaire')
            ->where('prix_unitaire', '>', 0)
            ->each(function (BordereauLigne $ligne) {
                $ligne->prix_unitaire = round((float) $ligne->prix_unitaire * self::MULTIPLICATEUR, 2);
                $quantite = (float) ($ligne->quantite ?: 1);
                $ligne->montant = round($ligne->prix_unitaire * $quantite, 2);
                $ligne->save();
            });
    }

    public function down(): void
    {
        $typeIds = TypeDocument::whereIn('nom', self::DOC_NAMES)->pluck('id');
        if ($typeIds->isEmpty()) {
            return;
        }

        $dossierDocumentIds = DB::table('dossier_documents')
            ->whereIn('type_document_id', $typeIds)
            ->pluck('id');

        $bordereauIds = DB::table('bordereaux')
            ->whereIn('dossier_document_id', $dossierDocumentIds)
            ->pluck('id');

        BordereauLigne::whereIn('bordereau_id', $bordereauIds)
            ->whereNotNull('prix_unitaire')
            ->where('prix_unitaire', '>', 0)
            ->each(function (BordereauLigne $ligne) {
                $ligne->prix_unitaire = round((float) $ligne->prix_unitaire / self::MULTIPLICATEUR, 2);
                $quantite = (float) ($ligne->quantite ?: 1);
                $ligne->montant = round($ligne->prix_unitaire * $quantite, 2);
                $ligne->save();
            });
    }
};
