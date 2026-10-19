<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\EntrepriseDocument;
use App\Models\EntrepriseDocumentFichier;
use App\Models\EntreprisePersonnel;
use App\Models\EntreprisePersonnelFichier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EntrepriseController extends Controller
{
    public function index()
    {
        $entreprises = Entreprise::orderBy('nom')->get();
        return view('entreprises.index', compact('entreprises'));
    }

    public function create()
    {
        return view('entreprises.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'adresse_officielle' => ['nullable', 'string'],
            'annee_enregistrement' => ['nullable', 'integer'],
            'pays' => ['nullable', 'string', 'max:100'],
            'ifu' => ['nullable', 'string', 'max:100'],
            'rccm' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'fonction_responsable' => ['nullable', 'string', 'max:255'],
        ]);

        // handle registre upload if provided
        if ($request->hasFile('registre')) {
            $path = $request->file('registre')->store('entreprises/registres', 'public');
            $data['registre_path'] = $path;
        }

        $data['finance_data'] = $this->extractFinanceData($request);
        $this->syncChiffresAffairesFromFinance($data['finance_data']);
        $data['beneficiaires_effectifs_data'] = $this->extractBeneficiairesEffectifsData($request);

        $entreprise = Entreprise::create($data);
        $this->syncPersonnels($request, $entreprise);
        $this->syncDocumentsAdministratifs($request, $entreprise);

        return redirect()->route('entreprises.index')->with('success', 'Entreprise créée avec succès.');
    }

    public function edit(Entreprise $entreprise)
    {
        return view('entreprises.edit', compact('entreprise'));
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'adresse_officielle' => ['nullable', 'string'],
            'annee_enregistrement' => ['nullable', 'integer'],
            'pays' => ['nullable', 'string', 'max:100'],
            'ifu' => ['nullable', 'string', 'max:100'],
            'rccm' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'fonction_responsable' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('registre')) {
            // delete old
            if (!empty($entreprise->registre_path)) {
                Storage::disk('public')->delete(ltrim($entreprise->registre_path, '/'));
            }
            $path = $request->file('registre')->store('entreprises/registres', 'public');
            $data['registre_path'] = $path;
        }

        $data['finance_data'] = $this->extractFinanceData($request);
        $this->syncChiffresAffairesFromFinance($data['finance_data']);
        $data['beneficiaires_effectifs_data'] = $this->extractBeneficiairesEffectifsData($request);

        $entreprise->update($data);
        $this->syncPersonnels($request, $entreprise);
        $this->syncDocumentsAdministratifs($request, $entreprise);

        return redirect()->route('entreprises.index')->with('success', 'Entreprise mise à jour.');
    }

    public function destroy(Entreprise $entreprise)
    {
        // si des dossiers rattachés existent, empêcher suppression
        if ($entreprise->dossiers()->exists()) {
            return redirect()->route('entreprises.index')->with('error', 'Impossible de supprimer : des dossiers sont liés à cette entreprise.');
        }

        if (!empty($entreprise->registre_path)) {
            Storage::disk('public')->delete(ltrim($entreprise->registre_path, '/'));
        }

        foreach ($entreprise->personnels()->with('fichiers')->get() as $personnel) {
            foreach ($personnel->fichiers as $fichier) {
                Storage::disk('public')->delete($fichier->chemin_fichier);
            }
        }

        foreach ($entreprise->documentsAdministratifs()->with('fichiers')->get() as $document) {
            foreach ($document->fichiers as $fichier) {
                Storage::disk('public')->delete($fichier->chemin_fichier);
            }
        }

        $entreprise->delete();

        return redirect()->route('entreprises.index')->with('success', 'Entreprise supprimée.');
    }

    /**
     * Memes champs/regles que la section Finance (etape 6, Formulaire FIN
     * - 3.1 Situation financiere) : on ne modifie pas ce formulaire ici,
     * on se contente de capturer les memes informations sur l'entreprise
     * pour pouvoir les reutiliser plus tard.
     */
    private function extractFinanceData(Request $request): array
    {
        return $request->validate([
            'year_labels' => ['nullable', 'array'],
            'year_labels.*' => ['nullable', 'string', 'max:255'],
            'total_actif' => ['nullable', 'array'],
            'total_actif.*' => ['nullable', 'string', 'max:255'],
            'total_passif' => ['nullable', 'array'],
            'total_passif.*' => ['nullable', 'string', 'max:255'],
            'patrimoine_net' => ['nullable', 'array'],
            'patrimoine_net.*' => ['nullable', 'string', 'max:255'],
            'disponibilites' => ['nullable', 'array'],
            'disponibilites.*' => ['nullable', 'string', 'max:255'],
            'engagements' => ['nullable', 'array'],
            'engagements.*' => ['nullable', 'string', 'max:255'],
            'recettes_totales' => ['nullable', 'array'],
            'recettes_totales.*' => ['nullable', 'string', 'max:255'],
            'benefices_avant_impots' => ['nullable', 'array'],
            'benefices_avant_impots.*' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * Les beneficiaires effectifs (actionnariat/controle) d'une entreprise
     * ne changent pas d'un dossier a l'autre : saisis une fois ici, ils sont
     * ensuite repris automatiquement dans le "Formulaire de divulgation des
     * beneficiaires effectifs" de chaque nouveau dossier (voir
     * DossierController::syncFormulaireBeneficiairesFromEntreprise()).
     */
    private function extractBeneficiairesEffectifsData(Request $request): array
    {
        $validated = $request->validate([
            'beneficiaires_option' => ['nullable', 'in:i,ii'],
            'beneficiaires_effectifs' => ['nullable', 'array'],
            'beneficiaires_effectifs.*.identite' => ['nullable', 'string', 'max:1000'],
            'beneficiaires_effectifs.*.action_25' => ['nullable', 'in:Oui,Non,'],
            'beneficiaires_effectifs.*.vote_25' => ['nullable', 'in:Oui,Non,'],
            'beneficiaires_effectifs.*.pouvoir_nomination' => ['nullable', 'in:Oui,Non,'],
        ]);

        $beneficiaires = array_values(array_filter($validated['beneficiaires_effectifs'] ?? [], function ($b) {
            return trim($b['identite'] ?? '') !== '';
        }));

        return [
            'option' => $validated['beneficiaires_option'] ?? 'i',
            'beneficiaires' => $beneficiaires,
        ];
    }

    /**
     * Les Recettes totales (RT) saisies dans la section Finance alimentent
     * la bibliotheque globale de Chiffre d'affaires (dossier_id null, geree
     * jusqu'ici a la main sur /chiffres) : une annee deja presente dans
     * cette bibliotheque n'est jamais ecrasee, seules les annees absentes
     * sont ajoutees. Le choix de quelles annees s'appliquent a quel dossier
     * reste inchange (etape 6, formulaire "Chiffre d'affaires annuel moyen").
     */
    private function syncChiffresAffairesFromFinance(array $financeData): void
    {
        $years = $financeData['year_labels'] ?? [];
        $recettes = $financeData['recettes_totales'] ?? [];

        foreach ($years as $index => $yearLabel) {
            $year = trim((string) $yearLabel);
            if (!preg_match('/^\d{4}$/', $year)) {
                continue;
            }

            $montant = trim((string) ($recettes[$index] ?? ''));
            $montant = str_replace([' ', "\u{A0}", ','], ['', '', '.'], $montant);
            if ($montant === '' || !is_numeric($montant)) {
                continue;
            }

            \App\Models\ChiffreAffaire::firstOrCreate(
                ['dossier_id' => null, 'annee' => (int) $year],
                ['montant' => (float) $montant, 'monnaie' => 'F CFA']
            );
        }
    }

    /**
     * Memes champs que le Formulaire PER (etape 6), plus intitule (titre du
     * CV, puisqu'une meme personne peut avoir plusieurs CV distincts) et
     * nom/poste puisqu'il n'y a pas ici de Liste du personnel externe pour
     * les fournir. Chaque bloc est un "CV" ajoute/modifie/retire librement -
     * identifie par son id (champ cache) s'il existe deja, sinon cree. Les
     * blocs entierement vides (ni nom ni poste) ou retires du formulaire
     * sont supprimes (avec leurs pieces jointes, fichiers compris).
     */
    private function syncPersonnels(Request $request, Entreprise $entreprise): void
    {
        $validated = $request->validate([
            'personnel' => ['nullable', 'array'],
            'personnel.*.intitule' => ['nullable', 'string', 'max:255'],
            'personnel.*.nom' => ['nullable', 'string', 'max:255'],
            'personnel.*.poste' => ['nullable', 'string', 'max:255'],
            'personnel.*.date_naissance' => ['nullable', 'date'],
            'personnel.*.qualifications' => ['nullable', 'string', 'max:4000'],
            'personnel.*.nom_employeur' => ['nullable', 'string', 'max:255'],
            'personnel.*.emploi_tenu' => ['nullable', 'string', 'max:255'],
            'personnel.*.adresse_employeur' => ['nullable', 'string', 'max:4000'],
            'personnel.*.telephone' => ['nullable', 'string', 'max:20'],
            'personnel.*.telecopie' => ['nullable', 'string', 'max:20'],
            'personnel.*.email' => ['nullable', 'email', 'max:255'],
            'personnel.*.contact_personnel' => ['nullable', 'string', 'max:255'],
            'personnel.*.nombre_annees_employeur' => ['nullable', 'integer', 'min:0'],
            'personnel.*.experiences' => ['nullable', 'string'],
            'personnel.*.delete_fichier_ids' => ['nullable', 'array'],
            'personnel.*.delete_fichier_ids.*' => ['integer'],
        ]);

        $submittedIds = [];

        foreach ($validated['personnel'] ?? [] as $key => $row) {
            $nom = trim((string) ($row['nom'] ?? ''));
            $poste = trim((string) ($row['poste'] ?? ''));
            if ($nom === '' && $poste === '') {
                continue;
            }

            $experiences = [];
            if (!empty($row['experiences'])) {
                $decoded = json_decode((string) $row['experiences'], true);
                $experiences = is_array($decoded) ? $decoded : [];
            }

            $data = [
                'entreprise_id' => $entreprise->id,
                'intitule' => $row['intitule'] ?? null,
                'nom' => $nom,
                'poste' => $poste,
                'date_naissance' => $row['date_naissance'] ?? null,
                'qualifications' => $row['qualifications'] ?? null,
                'nom_employeur' => $row['nom_employeur'] ?? null,
                'emploi_tenu' => $row['emploi_tenu'] ?? null,
                'adresse_employeur' => $row['adresse_employeur'] ?? null,
                'telephone' => $row['telephone'] ?? null,
                'telecopie' => $row['telecopie'] ?? null,
                'email' => $row['email'] ?? null,
                'contact_personnel' => $row['contact_personnel'] ?? null,
                'nombre_annees_employeur' => $row['nombre_annees_employeur'] ?? null,
                'experiences' => $experiences,
            ];

            // La cle du tableau EST l'id reel (comme pour le Formulaire PER :
            // personnel[{id}][...]) pour les blocs existants ; les nouveaux
            // blocs utilisent une cle non numerique (ex: "new_1") cote JS.
            $personnel = is_numeric($key)
                ? EntreprisePersonnel::where('entreprise_id', $entreprise->id)->find((int) $key)
                : null;

            if ($personnel) {
                $personnel->update($data);
            } else {
                $personnel = EntreprisePersonnel::create($data);
            }

            $submittedIds[] = $personnel->id;

            foreach ($row['delete_fichier_ids'] ?? [] as $fichierId) {
                $fichier = EntreprisePersonnelFichier::where('entreprise_personnel_id', $personnel->id)->find($fichierId);
                if ($fichier) {
                    Storage::disk('public')->delete($fichier->chemin_fichier);
                    $fichier->delete();
                }
            }

            foreach ($request->file("personnel_fichiers.$key", []) as $uploaded) {
                if (!$uploaded) {
                    continue;
                }
                $chemin = $uploaded->store('entreprise_personnels/pieces', 'public');
                EntreprisePersonnelFichier::create([
                    'entreprise_personnel_id' => $personnel->id,
                    'chemin_fichier' => $chemin,
                    'nom_original' => $uploaded->getClientOriginalName(),
                    'utilisateur_id' => auth()->id(),
                ]);
            }
        }

        $removed = EntreprisePersonnel::where('entreprise_id', $entreprise->id)
            ->whereNotIn('id', $submittedIds)
            ->with('fichiers')
            ->get();
        foreach ($removed as $personnel) {
            foreach ($personnel->fichiers as $fichier) {
                Storage::disk('public')->delete($fichier->chemin_fichier);
            }
            $personnel->delete();
        }
    }

    /**
     * Documents administratifs de l'entreprise : RCCM et IFU (un seul
     * emplacement chacun, plusieurs fichiers possibles - ex. plusieurs
     * pages scannees) + des types "repetables" : Etats financiers (un
     * emplacement par annee), Attestation de non-exclusion (renouvelee
     * chaque annee) et Attestation de categorisation (renouvelee tous les
     * 3 ans) - un emplacement par date d'expiration, pour garder l'historique
     * au lieu d'ecraser l'ancienne piece a chaque renouvellement. Ces
     * documents sont ensuite references (jamais copies) depuis un dossier,
     * sur le meme principe que les CV des personnels.
     */
    private function syncDocumentsAdministratifs(Request $request, Entreprise $entreprise): void
    {
        $validated = $request->validate([
            'documents_administratifs' => ['nullable', 'array'],
            'documents_administratifs.*.delete_fichier_ids' => ['nullable', 'array'],
            'documents_administratifs.*.delete_fichier_ids.*' => ['integer'],
            'etats_financiers' => ['nullable', 'array'],
            'etats_financiers.*.annee' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'etats_financiers.*.delete_fichier_ids' => ['nullable', 'array'],
            'etats_financiers.*.delete_fichier_ids.*' => ['integer'],
            'non_exclusion' => ['nullable', 'array'],
            'non_exclusion.*.date_delivrance' => ['nullable', 'date'],
            'non_exclusion.*.delete_fichier_ids' => ['nullable', 'array'],
            'non_exclusion.*.delete_fichier_ids.*' => ['integer'],
            'categorisation' => ['nullable', 'array'],
            'categorisation.*.date_delivrance' => ['nullable', 'date'],
            'categorisation.*.delete_fichier_ids' => ['nullable', 'array'],
            'categorisation.*.delete_fichier_ids.*' => ['integer'],
            'regularite_fiscale' => ['nullable', 'array'],
            'regularite_fiscale.*.date_delivrance' => ['nullable', 'date'],
            'regularite_fiscale.*.delete_fichier_ids' => ['nullable', 'array'],
            'regularite_fiscale.*.delete_fichier_ids.*' => ['integer'],
            'cnss' => ['nullable', 'array'],
            'cnss.*.date_delivrance' => ['nullable', 'date'],
            'cnss.*.delete_fichier_ids' => ['nullable', 'array'],
            'cnss.*.delete_fichier_ids.*' => ['integer'],
            'non_faillite' => ['nullable', 'array'],
            'non_faillite.*.date_delivrance' => ['nullable', 'date'],
            'non_faillite.*.delete_fichier_ids' => ['nullable', 'array'],
            'non_faillite.*.delete_fichier_ids.*' => ['integer'],
            'rib' => ['nullable', 'array'],
            'rib.*.banque' => ['nullable', 'string', 'max:255'],
            'rib.*.delete_fichier_ids' => ['nullable', 'array'],
            'rib.*.delete_fichier_ids.*' => ['integer'],
            'bonne_fin' => ['nullable', 'array'],
            'bonne_fin.*.intitule' => ['nullable', 'string', 'max:255'],
            'bonne_fin.*.delete_fichier_ids' => ['nullable', 'array'],
            'bonne_fin.*.delete_fichier_ids.*' => ['integer'],
            'bon_commande_contrats' => ['nullable', 'array'],
            'bon_commande_contrats.*.intitule' => ['nullable', 'string', 'max:255'],
            'bon_commande_contrats.*.delete_fichier_ids' => ['nullable', 'array'],
            'bon_commande_contrats.*.delete_fichier_ids.*' => ['integer'],
        ]);

        foreach (['rccm', 'ifu', 'identification_statut', 'arrete_ministre_sante', 'piece_identite_responsable'] as $type) {
            $document = EntrepriseDocument::firstOrCreate([
                'entreprise_id' => $entreprise->id,
                'type' => $type,
            ]);

            $row = $validated['documents_administratifs'][$type] ?? [];
            $this->deleteEntrepriseDocumentFichiers($document, $row['delete_fichier_ids'] ?? []);
            $this->storeEntrepriseDocumentFichiers($request, $document, "documents_administratifs.$type.fichiers");
        }

        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'etat_financier', 'annee', $validated['etats_financiers'] ?? [], 'etats_financiers_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'non_exclusion', 'date_delivrance', $validated['non_exclusion'] ?? [], 'non_exclusion_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'categorisation', 'date_delivrance', $validated['categorisation'] ?? [], 'categorisation_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'regularite_fiscale', 'date_delivrance', $validated['regularite_fiscale'] ?? [], 'regularite_fiscale_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'cnss', 'date_delivrance', $validated['cnss'] ?? [], 'cnss_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'non_faillite', 'date_delivrance', $validated['non_faillite'] ?? [], 'non_faillite_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'rib', 'banque', $validated['rib'] ?? [], 'rib_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'bonne_fin', 'intitule', $validated['bonne_fin'] ?? [], 'bonne_fin_fichiers');
        $this->syncRepeatableEntrepriseDocuments($request, $entreprise, 'bon_commande_contrats', 'intitule', $validated['bon_commande_contrats'] ?? [], 'bon_commande_contrats_fichiers');
    }

    /**
     * Synchronise un type de document "repetable" (plusieurs emplacements
     * distincts pour un meme type, un par annee ou par date d'expiration) :
     * cree/met a jour chaque bloc soumis (cle = id existant ou "new_N"),
     * puis supprime (avec ses fichiers) tout emplacement existant qui n'a
     * pas ete soumis a nouveau.
     */
    private function syncRepeatableEntrepriseDocuments(Request $request, Entreprise $entreprise, string $type, string $column, array $rows, string $fichiersField): void
    {
        $submittedIds = [];

        foreach ($rows as $key => $row) {
            $value = $row[$column] ?? null;
            if (!$value) {
                continue;
            }

            $document = is_numeric($key)
                ? EntrepriseDocument::where('entreprise_id', $entreprise->id)->where('type', $type)->find((int) $key)
                : null;

            if ($document) {
                $document->update([$column => $value]);
            } else {
                $document = EntrepriseDocument::create([
                    'entreprise_id' => $entreprise->id,
                    'type' => $type,
                    $column => $value,
                ]);
            }

            $submittedIds[] = $document->id;

            $this->deleteEntrepriseDocumentFichiers($document, $row['delete_fichier_ids'] ?? []);
            $this->storeEntrepriseDocumentFichiers($request, $document, "$fichiersField.$key");
        }

        $removed = EntrepriseDocument::where('entreprise_id', $entreprise->id)
            ->where('type', $type)
            ->whereNotIn('id', $submittedIds)
            ->with('fichiers')
            ->get();
        foreach ($removed as $document) {
            foreach ($document->fichiers as $fichier) {
                Storage::disk('public')->delete($fichier->chemin_fichier);
            }
            $document->delete();
        }
    }

    private function deleteEntrepriseDocumentFichiers(EntrepriseDocument $document, array $fichierIds): void
    {
        foreach ($fichierIds as $fichierId) {
            $fichier = EntrepriseDocumentFichier::where('entreprise_document_id', $document->id)->find($fichierId);
            if ($fichier) {
                Storage::disk('public')->delete($fichier->chemin_fichier);
                $fichier->delete();
            }
        }
    }

    private function storeEntrepriseDocumentFichiers(Request $request, EntrepriseDocument $document, string $fileInputKey): void
    {
        foreach ($request->file($fileInputKey, []) as $uploaded) {
            if (!$uploaded) {
                continue;
            }
            $chemin = $uploaded->store('entreprise_documents/pieces', 'public');
            EntrepriseDocumentFichier::create([
                'entreprise_document_id' => $document->id,
                'chemin_fichier' => $chemin,
                'nom_original' => $uploaded->getClientOriginalName(),
                'utilisateur_id' => auth()->id(),
            ]);
        }
    }
}
