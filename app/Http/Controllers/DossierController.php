<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Entreprise;
use App\Models\TypeDossier;
use App\Models\TypeDocument;
use App\Models\DossierDocument;
use App\Models\ChampDocument;
use App\Models\ValeurDocument;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:10'],
            'adresse' => ['nullable', 'string'],
            'telephone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'responsable' => ['nullable', 'string'],
            'fonction_responsable' => ['nullable', 'string'],
        ]);

        $entreprise = Entreprise::create($data);

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
        $data = $request->validate([
            'categorie' => ['required', 'in:public,prive'],
            'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'entreprise_id' => ['required', 'exists:entreprises,id']
        ]);

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
        ]);

        // Créer le dossier provisoirement (statut = en_cours)
        $dossier = Dossier::create([
            'type_dossier_id' => $data['type_dossier_id'],
            'entreprise_id' => $data['entreprise_id'],
            'nom_dossier' => $data['nom_dossier'],
            'objectif' => $data['objectif'],
            'lot' => $data['lot'],
            'public_prive' => $data['categorie'],
            'statut' => 'en_cours',
        ]);

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

        // TODO: Implémenter la génération PDF
        // Utiliser une libraire comme DOMPDF ou TCPDF

        return view('dossiers.pdf', compact('dossier'));
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
