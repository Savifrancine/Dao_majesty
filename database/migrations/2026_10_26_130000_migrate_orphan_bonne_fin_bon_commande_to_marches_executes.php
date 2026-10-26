<?php

use App\Models\EntrepriseDocument;
use App\Models\MarcheExecute;
use Illuminate\Database\Migrations\Migration;

/**
 * "Bon de commande et contrats" et "Attestation de bonne fin" ne se saisissent
 * plus depuis "Documents administratifs de l'entreprise" (section retiree,
 * voir documents_administratifs_section.blade.php) : ils se saisissent
 * desormais uniquement depuis "Nos marches executes", rattaches a un marche
 * precis (marche_execute_id). Les entrees deja existantes, saisies avant ce
 * changement (marche_execute_id encore null), ne doivent pas disparaitre :
 * chacune devient son propre marche execute (identification_marche = ancien
 * intitule, les autres champs restent a completer), sans toucher aux
 * fichiers deja attaches (jamais copies, juste rattaches a ce nouveau marche).
 */
return new class extends Migration
{
    public function up(): void
    {
        EntrepriseDocument::whereIn('type', ['bonne_fin', 'bon_commande_contrats'])
            ->whereNull('marche_execute_id')
            ->get()
            ->each(function (EntrepriseDocument $document) {
                $marche = MarcheExecute::create([
                    'entreprise_id' => $document->entreprise_id,
                    'identification_marche' => $document->intitule,
                ]);

                $document->update(['marche_execute_id' => $marche->id]);
            });
    }

    public function down(): void
    {
        // Pas de retour en arriere : la section d'origine est retiree de
        // l'interface, il n'y a plus de place pour re-afficher ces entrees
        // sans marche_execute_id.
    }
};
