<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $utilisateurs = Utilisateur::orderBy('nom')->orderBy('prenom')->get();
        return view('utilisateurs.index', compact('utilisateurs'));
    }

    public function create()
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        return view('utilisateurs.create');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:utilisateurs,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:directeur,employe',
        ]);

        Utilisateur::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'mot_de_passe' => bcrypt($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(Utilisateur $utilisateur)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        return view('utilisateurs.edit', compact('utilisateur'));
    }

    public function update(Request $request, Utilisateur $utilisateur)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:utilisateurs,email,' . $utilisateur->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:directeur,employe',
            'actif' => 'required|boolean',
        ]);

        $utilisateur->nom = $data['nom'];
        $utilisateur->prenom = $data['prenom'];
        $utilisateur->email = $data['email'];
        $utilisateur->role = $data['role'];
        $utilisateur->actif = $data['actif'];
        if (! empty($data['password'])) {
            $utilisateur->mot_de_passe = bcrypt($data['password']);
        }
        $utilisateur->save();

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(Utilisateur $utilisateur)
    {
        if (! auth()->user()->isAdminOrDirecteur()) {
            abort(403, 'Accès refusé');
        }

        if ($utilisateur->id === auth()->id()) {
            return redirect()->route('utilisateurs.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $utilisateur->delete();

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur supprimé.');
    }
}
