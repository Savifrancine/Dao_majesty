<?php

namespace App\Http\Controllers;

use App\Models\Signataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignataireController extends Controller
{
    public function index()
    {
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();
        return view('signataires.index', compact('signataires'));
    }

    public function create()
    {
        return view('signataires.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'signature_path' => ['nullable', 'string'],
            'cachet_path' => ['nullable', 'string'],
        ]);

        $signataire = Signataire::create($data);

        return redirect()->route('signataires.index')->with('success', 'Signataire créé avec succès.');
    }

    public function edit(Signataire $signataire)
    {
        return view('signataires.edit', compact('signataire'));
    }

    public function update(Request $request, Signataire $signataire)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'signature_path' => ['nullable', 'string'],
            'cachet_path' => ['nullable', 'string'],
        ]);

        $signataire->update($data);

        return redirect()->route('signataires.index')->with('success', 'Signataire mis à jour.');
    }

    public function destroy(Signataire $signataire)
    {
        // détacher relations pivot
        $signataire->dossiers()->detach();

        // supprimer fichiers associés s'ils existent
        if (!empty($signataire->signature_path)) {
            Storage::disk('public')->delete(ltrim($signataire->signature_path, '/'));
        }
        if (!empty($signataire->cachet_path)) {
            Storage::disk('public')->delete(ltrim($signataire->cachet_path, '/'));
        }

        $signataire->delete();

        return redirect()->route('signataires.index')->with('success', 'Signataire supprimé.');
    }
}
