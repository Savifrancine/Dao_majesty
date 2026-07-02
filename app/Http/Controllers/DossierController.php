<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\TypeMarche;
use App\Models\Signataire;
use App\Models\Entreprise;
use App\Models\TypeDossier;
use App\Models\TypeDocument;
use App\Models\DossierDocument;
use App\Models\ChampDocument;
use App\Models\ValeurDocument;
use App\Models\DocumentFichier;
use App\Models\Bordereau;
use App\Models\BordereauLigne;
use App\Models\ChiffreAffaire;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class DossierController extends Controller
{
    /**
     * Afficher le dashboard home avec les dossiers
     */
    public function dashboardHome()
    {
        $dossiers = Dossier::all();
        return view('home', compact('dossiers'));
    }

    /**
     * Retourne la liste canonique des pièces de dossier supportées.
     */
    private function getDocumentPieceNames(): array
    {
        return [
            "Déclaration de garantie d'offre",
            "Lettre de soumission",
            "RCCM",
            "Copie legalisee de l'Extrait du RCCM",
            "Copie legalisee de l'Identifiant Fiscal Unique (IFU)",
            "Attestation de non-faillite datant de moins de trois (03) mois",
            "Attestation d'imposition ou de situation fiscale en cours de validite",
            "Attestation de regularite a la CNSS",
            "Formulaire de renseignements sur le candidat",
            "Formulaire MAT",
            "Formulaire PER",
            "Liste du personnel affecté à l'exécution du marché",
            "Chiffre d'affaires annuel moyen des activités de services",
            "Attestation de non-exclusion de la commande publique",
            "Engagement du soumissionnaire à respecter le code d'éthique et de déontologie",
            "Attestation de non-condamnation pour fraude, corruption ou fausse declaration",
            "Attestation de nationalite ou document de constitution legale de l'entreprise",
            "Statuts de la societe et PV de nomination du gerant",
            "Copie du quitus fiscal",
            "Attestation de situation reguliere vis-a-vis des organismes de credit",
            "Bordereau prix unitaire",
            "Bordereau des prix pour les fournitures à importer",
            "Tableau de résumé des bordereaux de prix",
            "Bordereau des prix et calendrier d'exécution des services connexes",
            "Listes des services connexes et calendrier de réalisation",
            "Listes des Fournitures et Calendrier de livraison",
            "Cadres de sous détails des prix unitaire",
            "Programme d'activités",
            "Méthodes d'exécution",
            "Calendrier d'exécution",
            "Description technique des services",
        ];
    }

    /**
     * Afficher le formulaire de création (Étape 1)
     * Choix Public/Privé
     */
    public function create()
    {
        return view('dossiers.create.step1');
    }

    /**
     * Étape 2 : Choix du type de dossier selon la catégorie
     */
    public function step2(Request $request)
    {
        $categorie = $request->validate([
            'categorie' => ['required', 'in:public,prive']
        ])['categorie'];

        // Récupérer les types pour cette catégorie
        $types = TypeDossier::where('categorie', $categorie)->get();

        return view('dossiers.create.step2', compact('categorie', 'types'));
    }

    /**
     * Étape 3 : Infos entreprise (Sélect ou Créer)
     */
    public function step3(Request $request)
    {
        $data = $request->validate([
            'categorie' => ['required', 'in:public,prive'],
            'type_dossier_id' => ['required', 'exists:types_dossiers,id']
        ]);

        $entreprises = Entreprise::all();
        $categorie = $data['categorie'];
        $typeDossierId = $data['type_dossier_id'];

        return view('dossiers.create.step3', compact('categorie', 'typeDossierId', 'entreprises'));
    }

    /**
     * Étape 3b : Créer une nouvelle entreprise
     */
    public function storeEntreprise(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'adresse_officielle' => ['nullable', 'string'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'pays' => ['nullable', 'string', 'max:255'],
            'ifu' => ['nullable', 'string', 'max:100'],
            'registre_path' => ['nullable', 'string', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'fonction_responsable' => ['nullable', 'string', 'max:255'],
            'annee_enregistrement' => ['nullable', 'digits:4'],
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $path = $request->file('logo')->store('entreprises/logos', 'public');
            $data['logo'] = $path;
        }

        $entreprise = Entreprise::create([
            'nom' => $data['nom'],
            'sigle' => $data['sigle'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'adresse_officielle' => $data['adresse_officielle'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'pays' => $data['pays'] ?? null,
            'ifu' => $data['ifu'] ?? null,
            'registre_path' => $data['registre_path'] ?? null,
            'logo' => $data['logo'] ?? null,
            'responsable' => $data['responsable'] ?? null,
            'fonction_responsable' => $data['fonction_responsable'] ?? null,
            'annee_enregistrement' => $data['annee_enregistrement'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'entreprise' => $entreprise
        ]);
    }

    /**
     * Étape 4 : Infos du dossier (Page de garde)
     */
    public function step4(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'categorie' => ['required', 'in:public,prive'],
                'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
                'entreprise_id' => ['required', 'exists:entreprises,id']
            ]);
        } else {
            // GET request: try to read from query string, else redirect to start
            $data = $request->only(['categorie', 'type_dossier_id', 'entreprise_id']);
            if (empty($data['categorie']) || empty($data['type_dossier_id']) || empty($data['entreprise_id'])) {
                return redirect()->route('dossiers.create')->with('error', 'Veuillez démarrer la création du dossier depuis l’étape 1.');
            }
            // Validate lightly the provided query values
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'categorie' => ['required', 'in:public,prive'],
                'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
                'entreprise_id' => ['required', 'exists:entreprises,id']
            ]);
            if ($validator->fails()) {
                return redirect()->route('dossiers.create')->with('error', 'Données de création invalides.');
            }
            $data = $validator->validated();
        }

        // No select list for type_marche anymore; using free text `type_offre`
        return view('dossiers.create.step4', $data);
    }

    /**
     * Étape 5 : Sélection des documents (Sommaire)
     */
    public function step5(Request $request)
    {
        $data = $request->validate([
            'categorie' => ['required', 'in:public,prive'],
            'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'entreprise_id' => ['required', 'exists:entreprises,id'],
            'nom_dossier' => ['required', 'string', 'max:255'],
            'objectif' => ['nullable', 'string'],
            'lot' => ['nullable', 'string'],
            // Page de garde (only fields up to année_depot)
            'titre_dossier' => ['nullable', 'string', 'max:255'],
            'type_offre' => ['nullable','string','max:255'],
            'lots' => ['nullable', 'string'],
            'titre_lot' => ['nullable','string','max:255'],
            'autres_details' => ['nullable','string'],
            'mois_depot' => ['nullable','string','max:255'],
            'annee_depot' => ['nullable','digits:4'],
            // new cover fields
            'republique' => ['nullable','string','max:255'],
            'ministere' => ['nullable','string','max:255'],
            'direction' => ['nullable','string','max:255'],
            'services_projet' => ['nullable','string','max:255'],
            'destinataires' => ['nullable','string'],
            'reference_dossier' => ['nullable','string','max:255'],
            'ref' => ['nullable','string','max:255'],
            'date_lancement' => ['nullable','date'],
            'date_soumission' => ['nullable','date'],
            'titre_lot' => ['nullable','string','max:255'],
            'autres_details' => ['nullable','string'],
            'mois_depot' => ['nullable','string','max:255'],
            'annee_depot' => ['nullable','digits:4'],
        ]);

        // Créer le dossier provisoirement (statut = en_cours)
        $dossier = Dossier::create([
            'type_dossier_id' => $data['type_dossier_id'],
            'entreprise_id' => $data['entreprise_id'],
            'nom_dossier' => $data['nom_dossier'],
            'objectif' => $data['objectif'] ?? null,
            'lot' => $data['lot'] ?? null,
            // Page de garde
            'titre_dossier' => $data['titre_dossier'] ?? null,
            'type_offre' => $data['type_offre'] ?? null,
            'lots' => $data['lots'] ?? ($data['lot'] ?? null),
            'titre_lot' => $data['titre_lot'] ?? null,
            'autres_details' => $data['autres_details'] ?? null,
            'mois_depot' => $data['mois_depot'] ?? null,
            'annee_depot' => $data['annee_depot'] ?? null,
            // new cover fields
            'republique' => $data['republique'] ?? null,
            'ministere' => $data['ministere'] ?? null,
            'direction' => $data['direction'] ?? null,
            'services_projet' => $data['services_projet'] ?? null,
            'destinataires' => $data['destinataires'] ?? null,
            'reference_dossier' => $data['reference_dossier'] ?? null,
            'ref' => $data['ref'] ?? null,
            'date_lancement' => $data['date_lancement'] ?? null,
            'date_soumission' => $data['date_soumission'] ?? null,
            'titre_lot' => $data['titre_lot'] ?? null,
            'types_offres' => $data['types_offres'] ?? null,
            'autres_details' => $data['autres_details'] ?? null,
            'mois_depot' => $data['mois_depot'] ?? null,
            'annee_depot' => $data['annee_depot'] ?? null,
            'public_prive' => $data['categorie'],
            'page_garde_path' => null,
            'statut' => 'en_cours',
        ]);

        $dossier->utilisateurs()->attach(auth()->id());

        // no file/page_garde handling here (removed)
        $pieceNames = $this->getDocumentPieceNames();

        $bordereauNames = [
            'Bordereau prix unitaire',
            'Bordereau des prix pour les fournitures à importer',
            'Bordereau des prix et calendrier d\'exécution des services connexes',
            'Listes des services connexes et calendrier de réalisation',
            'Listes des Fournitures et Calendrier de livraison',
            'Cadres de sous détails des prix unitaire',
            'Programme d\'activités',
            'Méthodes d\'exécution',
            'Calendrier d\'exécution',
            'Description technique des services',
        ];

        foreach ($pieceNames as $name) {
            $type = in_array($name, $bordereauNames, true) ? 'bordereau' : 'formulaire';
            TypeDocument::firstOrCreate(['nom' => $name], ['type_formulaire' => $type]);
        }

        $documentsByName = TypeDocument::whereIn('nom', $pieceNames)->get()->keyBy('nom');
        $documents = collect($pieceNames)
            ->map(fn ($name) => $documentsByName->get($name))
            ->filter();

        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('dossiers.create.step5', compact('dossier', 'documents', 'signataires'));
    }

    /**
     * Étape 6 : Créer les DossierDocument et afficher le formulaire de remplissage
     */
    public function step6(Request $request, $dossierId)
    {
        $pieceNames = $this->getDocumentPieceNames();

        $dossier = Dossier::findOrFail($dossierId);

        $sessionKeyDocs = 'step6_documents_' . $dossierId;
        $sessionKeySignataire = 'step6_signataire_' . $dossierId;

        $selectedDocumentIds = [];
        $signataireId = null;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'documents' => ['required', 'array', 'min:1'],
                'documents.*' => ['exists:types_documents,id'],
                'signataire_id' => ['nullable', 'exists:signataires,id'],
            ]);

            $selectedDocumentIds = $data['documents'];
            $signataireId = $data['signataire_id'] ?? null;

            Session::put($sessionKeyDocs, $selectedDocumentIds);
            Session::put($sessionKeySignataire, $signataireId);
        } else {
            $selectedDocumentIds = Session::get($sessionKeyDocs, []);
            $signataireId = Session::get($sessionKeySignataire, null);

            if (empty($selectedDocumentIds)) {
                return redirect()->route('dossiers.show', $dossier->id)
                    ->with('error', 'Aucun document sélectionné pour l’étape 6.');
            }

            $data = ['documents' => $selectedDocumentIds, 'signataire_id' => $signataireId];
        }

        $selectedTypes = TypeDocument::whereIn('id', $selectedDocumentIds)->get()->keyBy('id');

        $invalidSelection = $selectedTypes->contains(function ($doc) use ($pieceNames) {
            return !in_array($doc->nom, $pieceNames, true);
        });

        if ($invalidSelection) {
            return redirect()->route('dossiers.show', $dossier->id)->with('error', 'Sélection de pièces invalide.');
        }

        $excludedName = 'Lettre de soumission';
        $autoCompleteNames = [];
        $orderedSelected = collect($selectedDocumentIds)
            ->map(fn ($id) => $selectedTypes->get($id))
            ->filter();

        $uploadQueue = $orderedSelected
            ->filter(fn ($doc) => $doc->nom !== $excludedName && !in_array($doc->nom, $autoCompleteNames, true))
            ->values();

        foreach ($orderedSelected as $index => $doc) {
            $dossierDocument = DossierDocument::updateOrCreate(
                [
                    'dossier_id' => $dossier->id,
                    'type_document_id' => $doc->id,
                ],
                [
                    'ordre' => $index + 1,
                    'statut' => 'vide',
                ]
            );

            if (in_array($doc->nom, $autoCompleteNames, true)) {
                $dossierDocument->update(['statut' => 'complete']);
            }
        }

        if ($uploadQueue->isEmpty()) {
            $dossier->statut = 'termine';
            $dossier->save();
            Session::forget($sessionKeyDocs);
            Session::forget($sessionKeySignataire);
            return redirect()->route('dossiers.show', $dossier->id)->with('success', 'Aucune pièce à téléverser. Dossier terminé.');
        }

        $requestedIndex = (int) $request->query('current_index', $request->input('current_index', 0));
        $currentIndex = max(0, min($requestedIndex, $uploadQueue->count() - 1));

        // Skip already complete documents
        while ($currentIndex < $uploadQueue->count()) {
            $docToCheck = $uploadQueue->get($currentIndex);
            $docRec = DossierDocument::where('dossier_id', $dossier->id)
                ->where('type_document_id', $docToCheck->id)
                ->first();
            if ($docRec && $docRec->statut === 'complete') {
                $currentIndex++;
                continue;
            }
            break;
        }

        if ($currentIndex >= $uploadQueue->count()) {
            $dossier->statut = 'termine';
            $dossier->save();
            Session::forget($sessionKeyDocs);
            Session::forget($sessionKeySignataire);
            return redirect()->route('dossiers.show', $dossier->id)->with('success', 'Pièces jointes enregistrées — dossier terminé.');
        }

        $currentDocument = $uploadQueue->get($currentIndex);

        if ($request->input('upload_step') === '1') {
            $currentDocumentId = $request->validate([
                'current_document_id' => ['required', 'exists:types_documents,id']
            ])['current_document_id'];

            $currentDocument = $uploadQueue->firstWhere('id', $currentDocumentId);
            if (!$currentDocument) {
                return redirect()->route('dossiers.show', $dossier->id)->with('error', 'Pièce sélectionnée invalide.');
            }

            $currentIndex = $uploadQueue->search(fn ($doc) => $doc->id === $currentDocumentId);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }

            $dossierDocument = DossierDocument::updateOrCreate(
                [
                    'dossier_id' => $dossier->id,
                    'type_document_id' => $currentDocumentId,
                ],
                [
                    'ordre' => $currentIndex + 1,
                    'statut' => 'vide'
                ]
            );

            if (trim($currentDocument->nom) === "Déclaration de garantie d'offre") {
                $vals = $request->validate([
                    'societe' => ['required','string','max:255'],
                    'date' => ['required','date'],
                    'declarant' => ['required','string','max:255'],
                    'fonction' => ['nullable','string','max:255'],
                    'reference' => ['nullable','string','max:255'],
                    'template_id' => ['nullable','exists:templates,id'],
                ]);

                try {
                    // On ignore le modèle sélectionné pour éviter que son contenu ne s'infiltre
                    // dans le document final. Le rendu de la déclaration de garantie d'offre
                    // reste basé sur la vue standard et inchangé.
                    $html = view('documents.declaration_pdf', array_merge($vals, ['signatureDataUri' => null]))->render();
                    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $output = $dompdf->output();
                    $filename = 'dossiers/documents/dossier_' . $dossier->id . '_declaration_' . time() . '.pdf';
                    Storage::disk('public')->put($filename, $output);

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $filename,
                        'utilisateur_id' => auth()->id(),
                    ]);

                    $dossierDocument->update(['statut' => 'complete']);
                } catch (\Throwable $e) {
                    // ignore and continue with normal flow
                }
            }

            if (trim($currentDocument->nom) === 'Tableau de résumé des bordereaux de prix') {
                $values = $request->validate([
                    'tableau_resume' => ['required', 'array'],
                    'tableau_resume.a' => ['nullable', 'numeric'],
                    'tableau_resume.b' => ['nullable', 'numeric'],
                    'tableau_resume.c' => ['nullable', 'numeric'],
                    'tableau_resume.d' => ['nullable', 'numeric'],
                    'tableau_resume.e' => ['nullable', 'numeric'],
                    'tableau_resume.f' => ['nullable', 'numeric'],
                    'tableau_resume.g' => ['nullable', 'numeric'],
                    'tableau_resume.h' => ['nullable', 'numeric'],
                    'tableau_resume.i' => ['nullable', 'numeric'],
                ]);

                $data = $values['tableau_resume'] ?? [];
                $a = isset($data['a']) && is_numeric($data['a']) ? (float) $data['a'] : 0.0;
                $d = isset($data['d']) && is_numeric($data['d']) ? (float) $data['d'] : 0.0;

                $data['a'] = $a;
                $data['b'] = round($a * 0.18, 2);
                $data['c'] = round($a + $data['b'], 2);
                $data['d'] = $d;
                $data['e'] = round($d * 0.18, 2);
                $data['f'] = round($d + $data['e'], 2);
                $data['g'] = round($a + $d, 2);
                $data['h'] = round($data['g'] * 0.18, 2);
                $data['i'] = round($data['g'] + $data['h'], 2);

                $content = json_encode($data, JSON_UNESCAPED_UNICODE);
                if ($content === false) {
                    $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                }

                $dossierDocument->update([
                    'content' => $content,
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Chiffre d\'affaires annuel moyen des activités de services') {
                $values = $request->validate([
                    'years' => ['required', 'array', 'min:1'],
                    'years.*' => ['integer'],
                ]);

                // Créer des enregistrements de chiffre d'affaires pour le dossier à partir des années sélectionnées.
                // Le rendu final du PDF principal utilise ces chiffres à partir de $dossier->chiffresAffaires.
                $selectedEntries = \App\Models\ChiffreAffaire::whereNull('dossier_id')
                    ->whereIn('annee', $values['years'])
                    ->orderBy('annee')
                    ->get();

                \App\Models\ChiffreAffaire::where('dossier_id', $dossier->id)->delete();
                foreach ($selectedEntries as $entry) {
                    \App\Models\ChiffreAffaire::create([
                        'dossier_id' => $dossier->id,
                        'annee' => $entry->annee,
                        'montant' => $entry->montant,
                        'monnaie' => $entry->monnaie,
                    ]);
                }

                if ($selectedEntries->isNotEmpty()) {
                    $dossierDocument->update(['statut' => 'complete']);
                }
            }

            if (trim($currentDocument->nom) === 'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie') {
                $values = $request->validate([
                    'content' => ['required', 'string'],
                ]);

                $dossierDocument->update([
                    'content' => $values['content'],
                    'statut' => 'complete',
                ]);
            }

            if (trim($currentDocument->nom) === 'Liste du personnel affecté à l\'exécution du marché') {
                $personnel = $request->input('personnel', []);
                $savedPersonnel = [];

                if (is_array($personnel)) {
                    foreach ($personnel as $row) {
                        $poste = trim($row['poste'] ?? '');
                        $nom = trim($row['nom'] ?? '');
                        if ($poste === '' && $nom === '') {
                            continue;
                        }
                        $savedPersonnel[] = ['poste' => $poste, 'nom' => $nom];
                    }
                }

                if (!empty($savedPersonnel)) {
                    $dossierDocument->update([
                        'content' => json_encode($savedPersonnel, JSON_UNESCAPED_UNICODE),
                        'statut' => 'complete',
                    ]);
                }
            }

            if (trim($currentDocument->nom) === 'Formulaire de renseignements sur le candidat') {
                $rules = [
                    'use_existing_info' => ['required', 'in:yes,no'],
                ];

                if ($request->input('use_existing_info') === 'no') {
                    $rules = array_merge($rules, [
                        'nom_candidat' => ['required', 'string', 'max:255'],
                        'groupement_membres' => ['nullable', 'string'],
                        'pays_candidat' => ['required', 'string', 'max:255'],
                        'identification_nationale' => ['nullable', 'string', 'max:255'],
                        'annee_enregistrement' => ['nullable', 'digits:4'],
                        'adresse_officielle' => ['nullable', 'string'],
                        'nom_representant' => ['required', 'string', 'max:255'],
                        'fonction_representant' => ['nullable', 'string', 'max:255'],
                        'adresse_representant' => ['nullable', 'string'],
                        'telephone_representant' => ['nullable', 'string', 'max:255'],
                        'email_representant' => ['nullable', 'email', 'max:255'],
                    ]);
                }

                $values = $request->validate($rules);

                if ($values['use_existing_info'] === 'yes') {
                    $entreprise = $dossier->entreprise;
                    $pdfData = [
                        'nom_candidat' => $entreprise->nom,
                        'groupement_membres' => '',
                        'pays_candidat' => $entreprise->pays ?? '',
                        'identification_nationale' => $entreprise->ifu ?? '',
                        'annee_enregistrement' => $entreprise->annee_enregistrement ?? '',
                        'adresse_officielle' => $entreprise->adresse_officielle ?? $entreprise->adresse ?? '',
                        'nom_representant' => $entreprise->responsable ?? '',
                        'fonction_representant' => $entreprise->fonction_responsable ?? '',
                        'adresse_representant' => '',
                        'telephone_representant' => $entreprise->telephone ?? '',
                        'email_representant' => $entreprise->email ?? '',
                    ];
                } else {
                    $pdfData = [
                        'nom_candidat' => $values['nom_candidat'],
                        'groupement_membres' => $values['groupement_membres'] ?? '',
                        'pays_candidat' => $values['pays_candidat'],
                        'identification_nationale' => $values['identification_nationale'] ?? '',
                        'annee_enregistrement' => $values['annee_enregistrement'] ?? '',
                        'adresse_officielle' => $values['adresse_officielle'] ?? '',
                        'nom_representant' => $values['nom_representant'],
                        'fonction_representant' => $values['fonction_representant'] ?? '',
                        'adresse_representant' => $values['adresse_representant'] ?? '',
                        'telephone_representant' => $values['telephone_representant'] ?? '',
                        'email_representant' => $values['email_representant'] ?? '',
                    ];
                }

                try {
                    $pdfData['dossier'] = $dossier;
                    $pdfData['entreprise'] = $dossier->entreprise;
                    $pdfData['drp_number'] = $dossier->ref ?? 'S_DLCSSA_' . str_pad($dossier->id, 6, '0', STR_PAD_LEFT);

                    $html = view('documents.formulaire_renseignements_candidat_pdf', $pdfData)->render();
                    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $output = $dompdf->output();
                    $filename = 'dossiers/documents/dossier_' . $dossier->id . '_formulaire_candidat_' . time() . '.pdf';
                    Storage::disk('public')->put($filename, $output);

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $filename,
                        'utilisateur_id' => auth()->id(),
                    ]);

                    $dossierDocument->update(['statut' => 'complete']);
                } catch (\Throwable $e) {
                    // ignore and continue with normal flow
                }
            }

                if ($currentDocument->type_formulaire === 'bordereau' || trim($currentDocument->nom) === 'Bordereau prix unitaire' || trim($currentDocument->nom) === 'Bordereau des prix pour les fournitures à importer' || trim($currentDocument->nom) === 'Programme d\'activités' || trim($currentDocument->nom) === 'Méthodes d\'exécution' || trim($currentDocument->nom) === 'Calendrier d\'exécution' || trim($currentDocument->nom) === 'Description technique des services') {
                $isProgramme = trim($currentDocument->nom) === "Programme d'activités";
                $isMethodes = trim($currentDocument->nom) === "Méthodes d'exécution";
                $isCalendrier = trim($currentDocument->nom) === "Calendrier d'exécution";
                $isDescriptionTechnique = trim($currentDocument->nom) === "Description technique des services";
                $isBordereauFournitures = trim($currentDocument->nom) === "Bordereau des prix pour les fournitures à importer";
                $isBordereauPrixCalendrier = trim($currentDocument->nom) === "Bordereau des prix et calendrier d'exécution des services connexes";
                $isListesServicesConnexes = trim($currentDocument->nom) === "Listes des services connexes et calendrier de réalisation";
                $isListesFournituersLivraison = trim($currentDocument->nom) === "Listes des Fournitures et Calendrier de livraison";
                $isCadresSousDetailsPrix = trim($currentDocument->nom) === "Cadres de sous détails des prix unitaire";
                $rules = [
                    'sections' => ['required', 'array', 'min:1'],
                    'sections.*.titre' => ['nullable', 'string', 'max:255'],
                    'sections.*.lignes' => ['required', 'array', 'min:1'],
                    'sections.*.lignes.*.designation' => ['nullable', 'string', 'max:1000'],
                    'sections.*.lignes.*.prix_unitaire' => ['nullable', 'numeric'],
                ];

                if ($isCadresSousDetailsPrix) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.unite' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.total_materiel' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.location_amort' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.matiere_frais' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.main_oeuvre' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.deborse_sec' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.coef_c1' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.coef_k' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.prix_vente_htva' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isProgramme) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.unite_physique' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.quantite' => ['nullable', 'integer', 'min:1'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isMethodes || $isCalendrier || $isDescriptionTechnique) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                    ]);
                }

                if ($isDescriptionTechnique) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.specifications_techniques' => ['nullable', 'string', 'max:2000'],
                        'sections.*.lignes.*.specifications_obligatoires' => ['nullable', 'string', 'max:2000'],
                        'sections.*.lignes.*.specifications_proposees' => ['nullable', 'string', 'max:2000'],
                    ]);
                }

                if ($isBordereauFournitures) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.cout_benin' => ['nullable', 'numeric'],
                    ]);
                }

                if ($isBordereauPrixCalendrier) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.date_prestation' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.frequence' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isListesServicesConnexes) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.unite_physique' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_prestation_plus_tot' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_prestation_plus_tard' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                if ($isListesFournituersLivraison) {
                    $rules = array_merge($rules, [
                        'sections.*.lignes.*.quantite' => ['nullable', 'numeric'],
                        'sections.*.lignes.*.unite_physique' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.site' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_livraison_plus_tot' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_livraison_plus_tard' => ['nullable', 'string', 'max:255'],
                        'sections.*.lignes.*.date_livraison_offerte' => ['nullable', 'string', 'max:255'],
                    ]);
                }

                $values = $request->validate($rules);

                $oldBordereaux = Bordereau::where('dossier_document_id', $dossierDocument->id)->get();
                $oldBordereaux->each(function ($old) {
                    $old->lignes()->delete();
                });
                Bordereau::where('dossier_document_id', $dossierDocument->id)->delete();

                $validLineCreated = false;
                foreach ($values['sections'] as $section) {
                    $bordereau = Bordereau::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'titre' => trim($section['titre'] ?? '') !== '' ? $section['titre'] : $currentDocument->nom,
                    ]);

                    foreach ($section['lignes'] as $ligne) {
                        $designation = trim($ligne['designation'] ?? '');
                        $price = !empty($ligne['prix_unitaire']) ? (float) $ligne['prix_unitaire'] : 0;

                        if ($isBordereauFournitures) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $coutBenin = !empty($ligne['cout_benin']) ? (float) $ligne['cout_benin'] : 0;
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                                'cout_benin' => $coutBenin,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isBordereauPrixCalendrier) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $frequence = trim($ligne['frequence'] ?? '');
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '' && $frequence === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                                'frequence' => $frequence,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isListesServicesConnexes) {
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $unitePhysique = trim($ligne['unite_physique'] ?? '');
                            $site = trim($ligne['site'] ?? '');
                            $datePrestationPlusTot = trim($ligne['date_prestation_plus_tot'] ?? '');
                            $datePrestationPlusTard = trim($ligne['date_prestation_plus_tard'] ?? '');
                            $datePrestation = trim(implode(' / ', array_filter([$datePrestationPlusTot, $datePrestationPlusTard], function ($value) {
                                return $value !== '';
                            })));

                            if ($designation === '' && $quantite <= 0 && $unitePhysique === '' && $site === '' && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'unite_physique' => $unitePhysique,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                                'site' => $site,
                                'date_prestation' => $datePrestation,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isListesFournituersLivraison) {
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 0;
                            $unitePhysique = trim($ligne['unite_physique'] ?? '');
                            $site = trim($ligne['site'] ?? '');
                            $dateLivraisonPlusTot = trim($ligne['date_livraison_plus_tot'] ?? '');
                            $dateLivraisonPlusTard = trim($ligne['date_livraison_plus_tard'] ?? '');
                            $dateLivraisonOfferte = trim($ligne['date_livraison_offerte'] ?? '');
                            $datePrestation = trim(implode(' / ', array_filter([$dateLivraisonPlusTot, $dateLivraisonPlusTard, $dateLivraisonOfferte], function ($value) {
                                return $value !== '';
                            })));

                            if ($designation === '' && $quantite <= 0 && $unitePhysique === '' && $site === '' && $datePrestation === '') {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'unite_physique' => $unitePhysique,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                                'site' => $site,
                                'date_prestation' => $datePrestation,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isCadresSousDetailsPrix) {
                            $unite = trim($ligne['unite'] ?? '');
                            $total_materiel = trim($ligne['total_materiel'] ?? '');
                            $location_amort = trim($ligne['location_amort'] ?? '');
                            $matiere_frais = trim($ligne['matiere_frais'] ?? '');
                            $main_oeuvre = trim($ligne['main_oeuvre'] ?? '');
                            $deborse_sec = trim($ligne['deborse_sec'] ?? '');
                            $coef_c1 = trim($ligne['coef_c1'] ?? '');
                            $coef_k = trim($ligne['coef_k'] ?? '');
                            $prix_vente_htva = trim($ligne['prix_vente_htva'] ?? '');

                            if ($designation === '' && $unite === '' && $total_materiel === '' && $location_amort === '' && $matiere_frais === '' && $main_oeuvre === '' && $deborse_sec === '' && $coef_c1 === '' && $coef_k === '' && $prix_vente_htva === '') {
                                continue;
                            }

                                // Log raw incoming values for debugging
                                Log::info('cadres_sous_details_prix_unitaire - ligne raw', [
                                    'dossier_document_id' => $dossierDocument->id ?? null,
                                    'designation' => $designation,
                                    'ligne_raw' => $ligne,
                                ]);

                            // Normalize numeric-ish strings (remove spaces, convert comma decimals)
                            $norm = function ($v) {
                                $v = trim($v ?? '');
                                if ($v === '') {
                                    return '';
                                }
                                $v = str_replace(["\xc2\xa0", ' ', ','], ['', '', '.'], $v);
                                return $v;
                            };

                            $total_materiel_n = $norm($total_materiel);
                            $location_amort_n = $norm($location_amort);
                            $matiere_frais_n = $norm($matiere_frais);
                            $main_oeuvre_n = $norm($main_oeuvre);
                            $deborse_sec_n = $norm($deborse_sec);
                            $coef_c1_n = $norm($coef_c1);
                            $coef_k_n = $norm($coef_k);
                            $prix_vente_htva_n = $norm($prix_vente_htva);

                            $total_materiel_value = is_numeric($total_materiel_n) ? (float) $total_materiel_n : null;
                            $location_amort_value = is_numeric($location_amort_n) ? (float) $location_amort_n : 0.0;
                            $matiere_frais_value = is_numeric($matiere_frais_n) ? (float) $matiere_frais_n : 0.0;
                            $main_oeuvre_value = is_numeric($main_oeuvre_n) ? (float) $main_oeuvre_n : 0.0;

                            if ($total_materiel_value !== null) {
                                $calculated_deborse_sec = $total_materiel_value + $location_amort_value + $matiere_frais_value + $main_oeuvre_value;
                                $calculated_coef_c1 = $total_materiel_value > 0 ? ($calculated_deborse_sec - $total_materiel_value) / $total_materiel_value : 0;
                                $calculated_coef_k = 1 + $calculated_coef_c1;

                                $deborse_sec_n = number_format($calculated_deborse_sec, 2, '.', '');
                                $coef_c1_n = number_format($calculated_coef_c1, 4, '.', '');
                                $coef_k_n = number_format($calculated_coef_k, 4, '.', '');
                            }

                            // Log normalized values
                            Log::info('cadres_sous_details_prix_unitaire - ligne normalized', [
                                'dossier_document_id' => $dossierDocument->id ?? null,
                                'matiere_frais_n' => $matiere_frais_n,
                                'total_materiel_n' => $total_materiel_n,
                                'deborse_sec_n' => $deborse_sec_n,
                                'coef_c1_n' => $coef_c1_n,
                                'coef_k_n' => $coef_k_n,
                                'prix_vente_htva_n' => $prix_vente_htva_n,
                            ]);

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'unite_physique' => $unite,
                                'quantite' => 1,
                                'prix_unitaire' => 0,
                                'montant' => 0,
                                'total_materiel' => $total_materiel_n,
                                'location_amort' => $location_amort_n,
                                'matiere_frais' => $matiere_frais_n,
                                'main_oeuvre' => $main_oeuvre_n,
                                'deborse_sec' => $deborse_sec_n,
                                'coef_c1' => $coef_c1_n,
                                'coef_k' => $coef_k_n,
                                'prix_vente_htva' => $prix_vente_htva_n,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if (!$isProgramme && !$isMethodes && !$isCalendrier && !$isDescriptionTechnique) {
                            if ($designation === '' && $price <= 0) {
                                continue;
                            }

                            BordereauLigne::create([
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => 1,
                                'prix_unitaire' => $price,
                                'montant' => $price,
                            ]);
                            $validLineCreated = true;
                            continue;
                        }

                        if ($isMethodes || $isCalendrier || $isDescriptionTechnique) {
                            $datePrestation = trim($ligne['date_prestation'] ?? '');
                            $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 1;
                            $montant = $price * max(1, $quantite);

                            if ($designation === '' && $price <= 0 && $datePrestation === '') {
                                continue;
                            }

                            $lignData = [
                                'bordereau_id' => $bordereau->id,
                                'designation' => $designation,
                                'quantite' => $quantite,
                                'prix_unitaire' => $price,
                                'montant' => $montant,
                                'date_prestation' => $datePrestation,
                            ];

                            if ($isDescriptionTechnique) {
                                $lignData['specifications_techniques'] = trim($ligne['specifications_techniques'] ?? '');
                                $lignData['specifications_obligatoires'] = trim($ligne['specifications_obligatoires'] ?? '');
                                $lignData['specifications_proposees'] = trim($ligne['specifications_proposees'] ?? '');
                            }

                            BordereauLigne::create($lignData);
                            $validLineCreated = true;
                            continue;
                        }

                        // Programme d'activités (dernier cas)
                        $unitePhysique = trim($ligne['unite_physique'] ?? '');
                        $quantite = !empty($ligne['quantite']) ? (float) $ligne['quantite'] : 1;
                        $site = trim($ligne['site'] ?? '');
                        $datePrestation = trim($ligne['date_prestation'] ?? '');
                        $montant = $price * max(1, $quantite);

                        if ($designation === '' && $price <= 0 && $site === '' && $datePrestation === '') {
                            continue;
                        }

                        BordereauLigne::create([
                            'bordereau_id' => $bordereau->id,
                            'designation' => $designation,
                            'unite_physique' => $unitePhysique,
                            'quantite' => $quantite,
                            'prix_unitaire' => $price,
                            'montant' => $montant,
                            'site' => $site,
                            'date_prestation' => $datePrestation,
                        ]);
                        $validLineCreated = true;
                    }
                }

                if ($validLineCreated) {
                    $dossierDocument->update(['statut' => 'complete']);
                }
            }

            if ($request->hasFile("fichiers.$currentDocumentId")) {
                foreach ($request->file("fichiers.$currentDocumentId") as $fichier) {
                    if (!$fichier) {
                        continue;
                    }

                    $chemin = $fichier->store('dossiers/documents', 'public');

                    DocumentFichier::create([
                        'dossier_document_id' => $dossierDocument->id,
                        'chemin_fichier' => $chemin,
                        'utilisateur_id' => auth()->id(),
                    ]);
                }
            }

            try {
                $hasFiles = DocumentFichier::where('dossier_document_id', $dossierDocument->id)->exists();
                if ($hasFiles) {
                    $dossierDocument->update(['statut' => 'complete']);
                }
            } catch (\Throwable $e) {
                // ignore
            }

            if ($request->filled('delete_file_ids')) {
                $deleteIds = $request->input('delete_file_ids', []);
                foreach ($deleteIds as $fid) {
                    $file = DocumentFichier::find($fid);
                    if ($file && $file->dossier_document_id === $dossierDocument->id) {
                        try {
                            Storage::disk('public')->delete($file->chemin_fichier);
                        } catch (\Throwable $e) {
                            // ignore deletion errors
                        }
                        $file->delete();
                    }
                }
            }

            $nextIndex = $currentIndex + 1;
            if ($nextIndex >= $uploadQueue->count()) {
                $dossier->statut = 'termine';
                $dossier->save();
                Session::forget($sessionKeyDocs);
                Session::forget($sessionKeySignataire);
                return redirect()->route('dossiers.show', $dossier->id)->with('success', 'Pièces jointes enregistrées — dossier terminé.');
            }

            return redirect()->route('dossiers.step6', ['dossierId' => $dossier->id, 'current_index' => $nextIndex]);
        }

        $globalChiffres = ChiffreAffaire::whereNull('dossier_id')->orderBy('annee')->get();

        return view('dossiers.create.step6', [
            'dossier' => $dossier,
            'uploadQueue' => $uploadQueue,
            'currentDocument' => $currentDocument,
            'currentIndex' => $currentIndex,
            'totalCount' => $uploadQueue->count(),
            'selectedDocumentIds' => $selectedDocumentIds,
            'globalChiffres' => $globalChiffres,
        ]);
    }

    /**
     * Sauvegarder les valeurs d'un document
     */
    public function saveDocumentValues(Request $request, $dossierId)
    {
        $dossier = Dossier::findOrFail($dossierId);
        $dossierDocumentId = $request->validate(['dossier_document_id' => 'required|exists:dossier_documents,id'])['dossier_document_id'];

        $dossierDocument = DossierDocument::findOrFail($dossierDocumentId);

        // Sauvegarder les valeurs pour chaque champ
        foreach ($request->all() as $key => $value) {
            if ($key !== 'dossier_document_id' && $key !== '_token') {
                // Le nom de champ est en format: champ_{id}
                if (preg_match('/champ_(\d+)/', $key, $matches)) {
                    $champId = $matches[1];

                    ValeurDocument::updateOrCreate(
                        [
                            'dossier_document_id' => $dossierDocumentId,
                            'champ_document_id' => $champId,
                        ],
                        [
                            'valeur' => $value,
                            'utilisateur_id' => auth()->id(),
                        ]
                    );
                }
            }
        }

        $dossierDocument->update(['statut' => 'complete']);

        // Determine next index in the uploadable queue so the UI can navigate
        $excludedName = 'Lettre de soumission';
        $uploadable = $dossier->documents->filter(fn($dd) => $dd->typeDocument && $dd->typeDocument->nom !== $excludedName)->sortBy('ordre')->values();
        $currentPos = $uploadable->pluck('id')->search($dossierDocument->id);
        $nextIndex = ($currentPos === false) ? 0 : ($currentPos + 1);

        if ($nextIndex >= $uploadable->count()) {
            $dossier->statut = 'termine';
            $dossier->save();
            $redirectUrl = route('dossiers.index');
        } else {
            $redirectUrl = route('dossiers.step6', ['dossierId' => $dossier->id, 'current_index' => $nextIndex]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'redirect' => $redirectUrl]);
        }

        return redirect($redirectUrl);
    }

    /**
     * Générer le PDF du dossier final
     */
    public function generatePDF(Dossier $dossier)
    {
        $dossier->load([
            'documents.typeDocument.champs',
            'documents.bordereau.lignes',
            'documents.valeurs',
            'documents.fichiers',
            'entreprise',
            'typeDossier',
            'signataires'
        ]);

        $excludedDocNames = [
            'Déclaration de garantie',
            "Déclaration de garantie d'offre",
            "Declaration de garantie d'offre",
        ];

        $documents = $dossier->documents->filter(function ($doc) use ($excludedDocNames) {
            return $doc->typeDocument && !in_array(trim($doc->typeDocument->nom), $excludedDocNames, true);
        })->sortBy('ordre')->values();

        // Préparer le data URI de l'image/PDF de la page de garde pour l'inclure dans le HTML
        $pageGardeDataUri = null;
        if (!empty($dossier->page_garde_path)) {
            $fullPath = storage_path('app/public/' . $dossier->page_garde_path);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
                $content = file_get_contents($fullPath);
                $base64 = base64_encode($content);
                $pageGardeDataUri = "data:{$mime};base64,{$base64}";
            }
        }

        $pdfAttachmentsExist = $documents->flatMap(function ($doc) {
            return $doc->fichiers ?? collect();
        })->filter(function ($f) {
            return strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)) === 'pdf';
        })->isNotEmpty();

        if (!$pdfAttachmentsExist) {
            $html = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))->render();

            try {
                $dompdf = new Dompdf(['isRemoteEnabled' => true]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $output = $dompdf->output();
                $finalFilename = 'dossiers/documents/dossier_' . $dossier->id . '_base_' . time() . '.pdf';
                Storage::disk('public')->put($finalFilename, $output);
                $finalFull = storage_path('app/public/' . $finalFilename);

                try {
                    $dossier->statut = 'genere';
                    $dossier->save();
                } catch (\Throwable $e) {}

                return response($output, 200)->header('Content-Type', 'application/pdf');
            } catch (\Throwable $e) {
                return view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'));
            }
        }

        if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('error', 'La fusion des PDFs nécessite la librairie setasign/fpdi. Exécutez : composer require setasign/fpdi setasign/fpdf');
        }

        $filesToMerge = [];
        $tempFiles = [];

        try {
            $htmlCover = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))->with('renderMode', 'cover')->render();
            $dompdfCover = new Dompdf(['isRemoteEnabled' => true]);
            $dompdfCover->loadHtml($htmlCover);
            $dompdfCover->setPaper('A4', 'portrait');
            $dompdfCover->render();
            $coverTemp = tempnam(sys_get_temp_dir(), 'dossier_cover_') . '.pdf';
            file_put_contents($coverTemp, $dompdfCover->output());
            $filesToMerge[] = $coverTemp;
            $tempFiles[] = $coverTemp;

            $htmlSummary = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'))
                ->with(['renderMode' => 'summary'])
                ->render();
            $dompdfSummary = new Dompdf(['isRemoteEnabled' => true]);
            $dompdfSummary->loadHtml($htmlSummary);
            $dompdfSummary->setPaper('A4', 'portrait');
            $dompdfSummary->render();
            $summaryTemp = tempnam(sys_get_temp_dir(), 'dossier_summary_') . '.pdf';
            file_put_contents($summaryTemp, $dompdfSummary->output());
            $filesToMerge[] = $summaryTemp;
            $tempFiles[] = $summaryTemp;

            foreach ($documents as $doc) {
                $pdfAttachments = $doc->fichiers->filter(function ($f) {
                    return strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION)) === 'pdf';
                });
                $imageAttachments = $doc->fichiers->filter(function ($f) {
                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                    return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
                });
                $otherAttachments = $doc->fichiers->filter(function ($f) {
                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                    return !in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true);
                });

                $skipHtmlDocPage = $pdfAttachments->isNotEmpty()
                    && $imageAttachments->isEmpty()
                    && $otherAttachments->isEmpty()
                    && $doc->valeurs->isEmpty()
                    && $doc->bordereau->isEmpty()
                    && empty($doc->content)
                    && $doc->typeDocument
                    && !in_array(trim($doc->typeDocument->nom), [
                        "Déclaration de garantie d'offre",
                        'Declaration de garantie d\'offre',
                        'Formulaire de renseignements sur le candidat',
                    ], true);

                $htmlTitle = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                    'documents' => collect([$doc]),
                    'renderMode' => 'title',
                ])->render();
                $dompdfTitle = new Dompdf(['isRemoteEnabled' => true]);
                $dompdfTitle->loadHtml($htmlTitle);
                $dompdfTitle->setPaper('A4', 'portrait');
                $dompdfTitle->render();
                $titleTemp = tempnam(sys_get_temp_dir(), 'dossier_title_') . '.pdf';
                file_put_contents($titleTemp, $dompdfTitle->output());
                $filesToMerge[] = $titleTemp;
                $tempFiles[] = $titleTemp;

                if (!$skipHtmlDocPage) {
                    $htmlDoc = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->with([
                        'documents' => collect([$doc]),
                        'renderMode' => 'doc',
                    ])->render();
                    $dompdfDoc = new Dompdf(['isRemoteEnabled' => true]);
                    $dompdfDoc->loadHtml($htmlDoc);
                    $dompdfDoc->setPaper('A4', 'portrait');
                    $dompdfDoc->render();
                    $docTemp = tempnam(sys_get_temp_dir(), 'dossier_doc_') . '.pdf';
                    file_put_contents($docTemp, $dompdfDoc->output());
                    $filesToMerge[] = $docTemp;
                    $tempFiles[] = $docTemp;
                }

                foreach ($pdfAttachments as $f) {
                    $path = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                    if (file_exists($path)) {
                        $filesToMerge[] = $path;
                    }
                }
            }

            $finalFilename = 'dossiers/documents/dossier_' . $dossier->id . '_merged_' . time() . '.pdf';
            $finalFull = storage_path('app/public/' . $finalFilename);
            $this->mergePdfs($filesToMerge, $finalFull);

            try {
                $dossier->statut = 'genere';
                $dossier->save();
            } catch (\Throwable $e) {}

            if (file_exists($finalFull)) {
                return response()->file($finalFull, ['Content-Type' => 'application/pdf']);
            }
        } catch (\Throwable $e) {
            return view('dossiers.pdf', compact('dossier', 'pageGardeDataUri', 'documents'));
        } finally {
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        return response()->file($finalFull, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Fusionne plusieurs fichiers PDF en un seul fichier de sortie en utilisant FPDI.
     * Nécessite l'installation de setasign/fpdi (+ fpdf ou tcpdf selon la configuration).
     * @param array $files Chemins absolus vers les fichiers PDF à fusionner
     * @param string $outputFull Chemin absolu du fichier de sortie
     * @throws \Throwable
     */
    private function mergePdfs(array $files, string $outputFull)
    {
        if (empty($files)) {
            throw new \InvalidArgumentException('Aucun fichier fourni pour la fusion.');
        }

        // Utiliser la classe FPDI si disponible
        if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
            throw new \RuntimeException('FPDI non disponible. Exécutez `composer require setasign/fpdi setasign/fpdf`');
        }

        $pdf = new \setasign\Fpdi\Fpdi();

        foreach ($files as $file) {
            if (!file_exists($file)) {
                // ignorer les fichiers manquants
                continue;
            }

            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $specs = $pdf->getTemplateSize($tplIdx);
                $orientation = $specs['orientation'] ?? ($specs['width'] > $specs['height'] ? 'L' : 'P');
                $pdf->AddPage($orientation, [$specs['width'], $specs['height']]);
                $pdf->useTemplate($tplIdx);
            }
        }

        // Sauvegarder le PDF fusionné
        $pdf->Output('F', $outputFull);
    }

    private function normalizeDocumentOrders(Dossier $dossier): void
    {
        $documents = $dossier->documents()->orderBy('created_at')->orderBy('id')->get();

        if ($documents->isEmpty()) {
            return;
        }

        $orders = $documents->pluck('ordre')->filter(function ($value) {
            return is_numeric($value) && (int) $value > 0;
        })->values();

        $hasMeaningfulOrder = $documents->contains(function ($doc) {
            return is_numeric($doc->ordre) && (int) $doc->ordre > 1;
        });
        $hasDuplicateOrder = $orders->count() !== $orders->unique()->count();

        if (!$hasMeaningfulOrder || $hasDuplicateOrder) {
            foreach ($documents as $index => $document) {
                $document->update(['ordre' => $index + 1]);
            }
        }
    }

    /**
     * Afficher un dossier
     */
    public function show(Dossier $dossier)
    {
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'entreprise', 'typeDossier', 'utilisateurs']);

        $hasSharedUsers = $dossier->utilisateurs->isNotEmpty();
        if (! auth()->user()->isAdminOrDirecteur() && $hasSharedUsers && ! $dossier->utilisateurs->contains(auth()->id())) {
            abort(403, 'Accès refusé');
        }

        $this->normalizeDocumentOrders($dossier);
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'entreprise', 'typeDossier']);

        $excludedName = 'Lettre de soumission';
        $uploadableDocs = $dossier->documents
            ->filter(fn ($doc) => $doc->typeDocument && $doc->typeDocument->nom !== $excludedName)
            ->sortBy('ordre')
            ->values();

        // Prefer a document with status 'en_cours', otherwise take the first empty document
        $inProgressIndex = $uploadableDocs->search(fn ($doc) => $doc->statut === 'en_cours');
        if ($inProgressIndex !== false) {
            $resumeIndex = $inProgressIndex;
        } else {
            $firstEmpty = $uploadableDocs->search(fn ($doc) => $doc->fichiers->isEmpty());
            $resumeIndex = $firstEmpty === false ? null : $firstEmpty;
        }

        $resumeDocumentIds = $uploadableDocs->map(fn ($doc) => $doc->type_document_id)->values()->all();

        return view('dossiers.show', compact('dossier', 'resumeIndex', 'resumeDocumentIds'));
    }

    /**
     * Reprendre la creation d'un dossier a la piece en cours
     */
    public function continueCreation(Request $request, Dossier $dossier)
    {
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'typeDossier']);
        $this->normalizeDocumentOrders($dossier);
        $dossier->load(['documents.typeDocument', 'documents.fichiers', 'typeDossier']);

        $excludedName = 'Lettre de soumission';
        $uploadableDocs = $dossier->documents
            ->filter(fn ($doc) => $doc->typeDocument && $doc->typeDocument->nom !== $excludedName)
            ->sortBy('ordre')
            ->values();

        if ($uploadableDocs->isEmpty()) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('error', 'Impossible de reprendre: aucune piece a televerser.');
        }

        $resumeIndex = $uploadableDocs->search(fn ($doc) => $doc->fichiers->isEmpty() || $doc->statut !== 'complete');

        // Allow overriding the resume index when coming from the 'Modifier' action
        if ($request->has('current_index')) {
            $override = (int) $request->query('current_index');
            if ($override >= 0 && $override < $uploadableDocs->count()) {
                $resumeIndex = $override;
            }
        }

        if ($resumeIndex === false) {
            return redirect()->route('dossiers.show', $dossier->id)
                ->with('success', 'Toutes les pieces sont deja televersees.');
        }

        $uploadQueue = $uploadableDocs->map(fn ($doc) => $doc->typeDocument)->values();
        $selectedDocumentIds = $uploadableDocs->pluck('type_document_id')->values()->all();
        $currentDocument = $uploadQueue->get($resumeIndex);
        $globalChiffres = ChiffreAffaire::whereNull('dossier_id')->orderBy('annee')->get();

        return view('dossiers.create.step6', [
            'dossier' => $dossier,
            'uploadQueue' => $uploadQueue,
            'currentDocument' => $currentDocument,
            'currentIndex' => $resumeIndex,
            'totalCount' => $uploadQueue->count(),
            'selectedDocumentIds' => $selectedDocumentIds,
            'globalChiffres' => $globalChiffres,
        ]);
    }

    /**
     * Afficher la page de selection des documents pour un dossier existant
     */
    public function selectDocuments(Dossier $dossier)
    {
        $pieceNames = $this->getDocumentPieceNames();

        $documentsByName = TypeDocument::whereIn('nom', $pieceNames)->get()->keyBy('nom');
        $documents = collect($pieceNames)
            ->map(fn ($name) => $documentsByName->get($name))
            ->filter();

        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('dossiers.create.step5', compact('dossier', 'documents', 'signataires'));
    }

    public function manageUsers(Dossier $dossier)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $users = Utilisateur::orderBy('nom')->orderBy('prenom')->get();
        $selectedUsers = $dossier->utilisateurs()->pluck('utilisateur_id')->all();

        return view('dossiers.manage_users', compact('dossier', 'users', 'selectedUsers'));
    }

    public function updateUsers(Request $request, Dossier $dossier)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $data = $request->validate([
            'utilisateurs' => ['required', 'array', 'min:1'],
            'utilisateurs.*' => ['required', 'exists:utilisateurs,id'],
        ]);

        $dossier->utilisateurs()->sync($data['utilisateurs']);

        return redirect()->route('dossiers.show', $dossier)->with('success', 'Contributeurs du dossier mis à jour.');
    }

    /**
     * Liste des dossiers
     */
    public function index()
    {
        if (auth()->user()->isAdminOrDirecteur()) {
            $dossiers = Dossier::with(['entreprise', 'typeDossier', 'documents'])->orderBy('created_at', 'desc')->get();
        } else {
            $userDossiers = auth()->user()->dossiers()->with(['entreprise', 'typeDossier', 'documents'])->orderBy('created_at', 'desc')->get();
            $dossiers = $userDossiers->isNotEmpty()
                ? $userDossiers
                : Dossier::with(['entreprise', 'typeDossier', 'documents'])->orderBy('created_at', 'desc')->get();
        }

        // For each dossier, compute resume info (index and document type ids) so list can offer direct "Continuer"
        $resumeInfo = [];
        foreach ($dossiers as $dossier) {
            $excludedName = 'Lettre de soumission';
            $uploadable = $dossier->documents
                ->filter(fn ($doc) => $doc->typeDocument && $doc->typeDocument->nom !== $excludedName)
                ->sortBy('ordre')
                ->values();

            if ($uploadable->isEmpty()) {
                $resumeInfo[$dossier->id] = null;
                continue;
            }

            $inProgress = $uploadable->search(fn ($doc) => $doc->statut === 'en_cours');
            if ($inProgress !== false) {
                $resumeIndex = $inProgress;
            } else {
                $firstEmpty = $uploadable->search(fn ($doc) => $doc->fichiers->isEmpty());
                $resumeIndex = $firstEmpty === false ? null : $firstEmpty;
            }

            $resumeInfo[$dossier->id] = $resumeIndex === null ? null : [
                'current_index' => $resumeIndex,
                'document_type_ids' => $uploadable->pluck('type_document_id')->all(),
            ];
        }

        return view('dossiers.index', compact('dossiers', 'resumeInfo'));
    }

    /**
     * Supprimer un dossier et ses relations si nécessaire.
     */
    /**
     * Supprimer un document d'un dossier
     */
    public function destroyDocument(Dossier $dossier, DossierDocument $document)
    {
        if (auth()->user()->isEmploye()) {
            abort(403, 'Suppression non autorisée pour ce rôle.');
        }

        if ($document->dossier_id !== $dossier->id) {
            return redirect()->back()->with('error', 'Document non trouvé.');
        }

        try {
            // Supprimer les fichiers associés
            foreach ($document->fichiers as $fichier) {
                if ($fichier->chemin_fichier && Storage::disk('public')->exists($fichier->chemin_fichier)) {
                    Storage::disk('public')->delete($fichier->chemin_fichier);
                }
                $fichier->delete();
            }

            // Supprimer les valeurs associées
            $document->valeurs()->delete();

            // Supprimer les bordereaux associés
            Bordereau::where('dossier_document_id', $document->id)->delete();

            // Supprimer le document
            $document->delete();

            return redirect()->route('dossiers.show', $dossier)->with('success', 'Document supprimé avec succès.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du document: ' . $e->getMessage());
        }
    }

    public function destroy(Dossier $dossier)
    {
        if (auth()->user()->isEmploye()) {
            abort(403, 'Suppression non autorisée pour ce rôle.');
        }

        try {
            $dossier->delete();
            return redirect()->route('dossiers.index')->with('success', 'Dossier supprimé.');
        } catch (\Throwable $e) {
            return redirect()->route('dossiers.index')->with('error', 'Impossible de supprimer le dossier.');
        }
    }

    /**
     * Sauvegarder les chiffres d'affaires pour un dossier (API JSON)
     */
    public function saveChiffres(Request $request)
    {
        $data = $request->validate([
            'dossier_id' => 'required|exists:dossiers,id',
            'chiffres' => 'required|array|min:1',
            'chiffres.*.annee' => 'required|integer|min:1900|max:2099',
            'chiffres.*.montant' => 'required|numeric|min:0',
            'chiffres.*.monnaie' => 'required|string|max:10',
        ]);

        $dossierId = $data['dossier_id'];

        // Supprimer les anciens chiffres pour ce dossier
        \App\Models\ChiffreAffaire::where('dossier_id', $dossierId)->delete();

        // Créer les nouveaux chiffres
        foreach ($data['chiffres'] as $chiffre) {
            \App\Models\ChiffreAffaire::create([
                'dossier_id' => $dossierId,
                'annee' => $chiffre['annee'],
                'montant' => $chiffre['montant'],
                'monnaie' => $chiffre['monnaie'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Chiffres d\'affaires sauvegardés']);
    }
}
