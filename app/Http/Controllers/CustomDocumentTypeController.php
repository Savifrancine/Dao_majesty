<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\DossierDocument;
use App\Models\TypeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomDocumentTypeController extends Controller
{
    public function index()
    {
        $types = TypeDocument::whereIn('type_formulaire', ['libre', 'piece_jointe'])
            ->orderBy('nom')
            ->get();

        return view('custom_document_types.index', compact('types'));
    }

    public function create()
    {
        $dossiers = Dossier::orderByDesc('id')->get();

        return view('custom_document_types.create', compact('dossiers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:types_documents,nom'],
            'dossier_id' => ['required', 'exists:dossiers,id'],
            'type_formulaire' => ['required', 'in:libre,piece_jointe'],
        ]);

        $typeDocument = TypeDocument::create([
            'nom' => $data['nom'],
            'type_formulaire' => $data['type_formulaire'],
        ]);

        $dossier = Dossier::findOrFail($data['dossier_id']);
        $maxOrdre = (int) ($dossier->documents()->max('ordre') ?? 0);

        DossierDocument::create([
            'dossier_id' => $dossier->id,
            'type_document_id' => $typeDocument->id,
            'ordre' => $maxOrdre + 1,
            'statut' => 'vide',
        ]);

        $newIndex = $dossier->documents()->count() - 1;

        return redirect(route('dossiers.continuer', $dossier->id) . '?current_index=' . $newIndex)
            ->with('success', 'Document personnalisé créé et ajouté au dossier. Vous pouvez maintenant le remplir.');
    }

    public function addToDossier(Request $request, TypeDocument $customDocumentType)
    {
        abort_unless(in_array($customDocumentType->type_formulaire, ['libre', 'piece_jointe'], true), 404);

        $data = $request->validate([
            'dossier_id' => ['required', 'exists:dossiers,id'],
        ]);

        $dossier = DB::transaction(function () use ($data, $customDocumentType) {
            $dossier = Dossier::whereKey($data['dossier_id'])->lockForUpdate()->firstOrFail();
            $alreadyAssigned = DossierDocument::where('dossier_id', $dossier->id)
                ->where('type_document_id', $customDocumentType->id)
                ->exists();

            if ($alreadyAssigned) {
                return null;
            }

            $maxOrdre = (int) ($dossier->documents()->max('ordre') ?? 0);
            DossierDocument::create([
                'dossier_id' => $dossier->id,
                'type_document_id' => $customDocumentType->id,
                'ordre' => $maxOrdre + 1,
                'statut' => 'vide',
            ]);

            return $dossier;
        });

        if (!$dossier) {
            return redirect()->route('custom-document-types.edit', $customDocumentType)
                ->with('error', 'Ce document est déjà présent dans le dossier sélectionné.');
        }

        $newIndex = $dossier->documents()->count() - 1;

        return redirect(route('dossiers.continuer', $dossier->id) . '?current_index=' . $newIndex)
            ->with('success', 'Document personnalisé ajouté au dossier. Vous pouvez maintenant le remplir.');
    }

    public function edit(TypeDocument $customDocumentType)
    {
        abort_unless(in_array($customDocumentType->type_formulaire, ['libre', 'piece_jointe'], true), 404);

        $customDocumentType->load('dossierDocuments.dossier:id,nom_dossier,reference_dossier');
        $dossiers = Dossier::select('id', 'nom_dossier', 'reference_dossier')->orderByDesc('id')->get();

        return view('custom_document_types.edit', [
            'type' => $customDocumentType,
            'dossiers' => $dossiers,
        ]);
    }

    public function update(Request $request, TypeDocument $customDocumentType)
    {
        abort_unless(in_array($customDocumentType->type_formulaire, ['libre', 'piece_jointe'], true), 404);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:types_documents,nom,' . $customDocumentType->id],
        ]);

        $customDocumentType->update(['nom' => $data['nom']]);

        return redirect()->route('custom-document-types.index')->with('success', 'Document personnalisé mis à jour.');
    }

    public function destroy(TypeDocument $customDocumentType)
    {
        abort_unless(in_array($customDocumentType->type_formulaire, ['libre', 'piece_jointe'], true), 404);

        if ($customDocumentType->dossierDocuments()->exists()) {
            return redirect()->route('custom-document-types.index')->with('error', 'Impossible de supprimer : ce document est déjà utilisé dans un ou plusieurs dossiers.');
        }

        $customDocumentType->delete();

        return redirect()->route('custom-document-types.index')->with('success', 'Document personnalisé supprimé.');
    }
}
