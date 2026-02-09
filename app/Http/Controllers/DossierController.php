<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\TypeMarche;
// removed Procedure / Autorite / Source / Signataire imports (not used anymore)
use App\Models\Entreprise;
use App\Models\TypeDossier;
use App\Models\TypeDocument;
use App\Models\DossierDocument;
use App\Models\ChampDocument;
use App\Models\ValeurDocument;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

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
        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:10'],
            'adresse' => ['nullable', 'string'],
            'telephone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'responsable' => ['nullable', 'string'],
            'fonction_responsable' => ['nullable', 'string'],
            'pays' => ['nullable', 'string', 'max:255'],
            'ifu' => ['nullable', 'string', 'max:255'],
            'registre' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];

        $data = $request->validate($rules);

        // Handle registre upload
        if ($request->hasFile('registre')) {
            $path = $request->file('registre')->store('entreprises/registre', 'public');
            $data['registre_path'] = $path;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('entreprises/logos', 'public');
            $data['logo'] = $path;
        }

        // Map incoming names to entreprise columns
        $entreprise = Entreprise::create([
            'nom' => $data['nom'],
            'sigle' => $data['sigle'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'pays' => $data['pays'] ?? null,
            'ifu' => $data['ifu'] ?? null,
            'registre_path' => $data['registre_path'] ?? null,
            'logo' => $data['logo'] ?? null,
            'responsable' => $data['responsable'] ?? null,
            'fonction_responsable' => $data['fonction_responsable'] ?? null,
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
            'date_lancement' => ['nullable','date'],
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
            'date_lancement' => $data['date_lancement'] ?? null,
            'titre_lot' => $data['titre_lot'] ?? null,
            'types_offres' => $data['types_offres'] ?? null,
            'autres_details' => $data['autres_details'] ?? null,
            'mois_depot' => $data['mois_depot'] ?? null,
            'annee_depot' => $data['annee_depot'] ?? null,
            'public_prive' => $data['categorie'],
            'page_garde_path' => null,
            'statut' => 'en_cours',
        ]);

        // no file/page_garde handling here (removed)
        $documents = TypeDocument::all();

        return view('dossiers.create.step5', compact('dossier', 'documents'));
    }

    /**
     * Étape 6 : Créer les DossierDocument et afficher le formulaire de remplissage
     */
    public function step6(Request $request)
    {
        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['exists:types_documents,id']
        ]);

        // Récupérer le dossier depuis la session
        $dossierId = $request->query('dossier_id');
        if (!$dossierId) {
            // Essayer depuis le form POST
            $dossierId = $request->input('dossier_id');
        }

        if (!$dossierId) {
            return redirect()->route('dossiers.create')->with('error', 'Dossier non trouvé');
        }

        $dossier = Dossier::findOrFail($dossierId);

        // Créer les DossierDocument pour chaque document sélectionné
        foreach ($data['documents'] as $index => $typeDocumentId) {
            DossierDocument::updateOrCreate(
                [
                    'dossier_id' => $dossier->id,
                    'type_document_id' => $typeDocumentId,
                ],
                [
                    'ordre' => $index + 1,
                    'statut' => 'vide'
                ]
            );
        }

        // Recharger le dossier avec les documents
        $dossier->load(['documents.typeDocument.champs']);

        return view('dossiers.create.step6', compact('dossier'));
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

        return response()->json(['success' => true]);
    }

    /**
     * Générer le PDF du dossier final
     */
    public function generatePDF(Dossier $dossier)
    {
        $dossier->load([
            'documents.typeDocument.champs',
            'documents.valeurs',
            'entreprise',
            'typeDossier'
        ]);

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

        $html = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->render();

        // Générer le PDF via Dompdf (installer le package si nécessaire)
        try {
            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->stream($dossier->nom_dossier . '.pdf', ['Attachment' => false]), 200)
                ->header('Content-Type', 'application/pdf');
        } catch (\Throwable $e) {
            // Si Dompdf non installé ou erreur, tomber back sur la vue HTML
            
            
            return view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'));
        }
    }

    /**
     * Afficher un dossier
     */
    public function show(Dossier $dossier)
    {
        $dossier->load(['documents.typeDocument', 'entreprise', 'typeDossier']);
        return view('dossiers.show', compact('dossier'));
    }

    /**
     * Liste des dossiers
     */
    public function index()
    {
        $dossiers = auth()->user()->dossiers ?? Dossier::all();
        return view('dossiers.index', compact('dossiers'));
    }
}
