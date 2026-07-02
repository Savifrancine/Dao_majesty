<?php

namespace App\Http\Controllers;

use App\Models\Signataire;
use Illuminate\Http\Request;

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
}
