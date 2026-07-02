<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\ChiffreAffaire;

class ChiffreAffaireController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $dossiers = Dossier::with('entreprise')->orderBy('created_at', 'desc')->get();

        // For the main page, show global chiffres (dossier_id = null)
        $selected = null;
        $chiffres = ChiffreAffaire::whereNull('dossier_id')->orderBy('annee')->get();

        return view('chiffres.index', compact('dossiers', 'selected', 'chiffres'));
    }

    /**
     * Store a global chiffre (applies to all dossiers)
     */
    public function storeGlobal(Request $request)
    {
        $data = $request->validate([
            'annee' => 'required|integer',
            'montant' => 'required|numeric',
            'monnaie' => 'nullable|string',
        ]);

        $data['monnaie'] = $data['monnaie'] ?? 'F CFA';
        $data['dossier_id'] = null;
        ChiffreAffaire::create($data);

        return redirect()->route('chiffres.index')->with('success', 'Chiffre global ajouté');
    }

    public function manage(Dossier $dossier)
    {
        $chiffres = $dossier->chiffresAffaires()->orderBy('annee')->get();
        return view('chiffres.manage', compact('dossier', 'chiffres'));
    }

    public function store(Request $request, Dossier $dossier)
    {
        $data = $request->validate([
            'annee' => 'required|integer',
            'montant' => 'required|numeric',
            'monnaie' => 'nullable|string',
        ]);

        $data['monnaie'] = $data['monnaie'] ?? 'F CFA';
        $dossier->chiffresAffaires()->create($data);

        return redirect()->route('chiffres.manage', $dossier)->with('success', 'Chiffre ajouté');
    }

    public function pdfForm(Dossier $dossier)
    {
        return redirect()->route('dossiers.show', $dossier)->with('error', 'La génération de PDF du chiffre d’affaires est désormais intégrée au PDF du dossier.');
    }

    public function pdfFormGlobal()
    {
        return redirect()->route('chiffres.index')->with('error', 'La génération de PDF globale de chiffre d’affaires est désactivée. Utilisez le PDF du dossier.');
    }

    public function generatePdf(Request $request, Dossier $dossier)
    {
        return redirect()->route('chiffres.index')->with('error', 'La génération externe de PDF pour le Chiffre d’affaires est désactivée. Utilisez le PDF du dossier.');
    }

    public function generatePdfGlobal(Request $request)
    {
        return redirect()->route('chiffres.index')->with('error', 'La génération externe de PDF pour le Chiffre d’affaires global est désactivée. Utilisez le PDF du dossier.');
    }
}
