<?php

namespace App\Http\Controllers;

use App\Models\FormulairePER;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

class FormulairePERController extends Controller
{
    public function index()
    {
        $formulaires = FormulairePER::where('utilisateur_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('formulaire_per.index', compact('formulaires'));
    }

    public function create()
    {
        return view('formulaire_per.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_candidat' => ['required', 'string', 'max:255'],
            'poste' => ['required', 'string', 'max:255'],
            'nom_personnel' => ['required', 'string', 'max:255'],
            'date_naissance' => ['required', 'date'],
            'qualifications' => ['nullable', 'string'],
            'nom_employeur' => ['required', 'string', 'max:255'],
            'adresse_employeur' => ['required', 'string'],
            'telephone' => ['required', 'string', 'max:20'],
            'contact_personnel' => ['required', 'string', 'max:255'],
            'telecopie' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'emploi_tenu' => ['required', 'string', 'max:255'],
            'nombre_annees_employeur' => ['required', 'integer', 'min:0'],
            'experiences' => ['nullable', 'string'],
            'lieu_signature' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['utilisateur_id'] = auth()->id();

        // Traiter les expériences si elles sont fournies
        if (!empty($validated['experiences'])) {
            $experiences = json_decode($validated['experiences'], true);
            $validated['experiences'] = $experiences;
        } else {
            $validated['experiences'] = [];
        }

        FormulairePER::create($validated);

        return redirect()->route('formulaire_per.index')->with('success', 'Formulaire PER créé avec succès.');
    }

    public function edit(FormulairePER $formulaire_per)
    {
        // Vérifier que l'utilisateur est propriétaire du formulaire
        if ($formulaire_per->utilisateur_id !== auth()->id()) {
            abort(403);
        }

        return view('formulaire_per.edit', compact('formulaire_per'));
    }

    public function update(Request $request, FormulairePER $formulaire_per)
    {
        // Vérifier que l'utilisateur est propriétaire du formulaire
        if ($formulaire_per->utilisateur_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'nom_candidat' => ['required', 'string', 'max:255'],
            'poste' => ['required', 'string', 'max:255'],
            'nom_personnel' => ['required', 'string', 'max:255'],
            'date_naissance' => ['required', 'date'],
            'qualifications' => ['nullable', 'string'],
            'nom_employeur' => ['required', 'string', 'max:255'],
            'adresse_employeur' => ['required', 'string'],
            'telephone' => ['required', 'string', 'max:20'],
            'contact_personnel' => ['required', 'string', 'max:255'],
            'telecopie' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'emploi_tenu' => ['required', 'string', 'max:255'],
            'nombre_annees_employeur' => ['required', 'integer', 'min:0'],
            'experiences' => ['nullable', 'string'],
            'lieu_signature' => ['nullable', 'string', 'max:255'],
        ]);

        // Traiter les expériences si elles sont fournies
        if (!empty($validated['experiences'])) {
            $experiences = json_decode($validated['experiences'], true);
            $validated['experiences'] = $experiences;
        } else {
            $validated['experiences'] = [];
        }

        $formulaire_per->update($validated);

        return redirect()->route('formulaire_per.index')->with('success', 'Formulaire PER mis à jour avec succès.');
    }

    public function destroy(FormulairePER $formulaire_per)
    {
        $this->denyEmployeeDeletion();

        // Vérifier que l'utilisateur est propriétaire du formulaire
        if ($formulaire_per->utilisateur_id !== auth()->id()) {
            abort(403);
        }

        $formulaire_per->delete();

        return redirect()->route('formulaire_per.index')->with('success', 'Formulaire PER supprimé avec succès.');
    }

    public function downloadPDF(FormulairePER $formulaire_per)
    {
        // Vérifier que l'utilisateur est propriétaire du formulaire
        if ($formulaire_per->utilisateur_id !== auth()->id()) {
            abort(403);
        }

        $html = view('formulaire_per.pdf', compact('formulaire_per'))->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->stream('formulaire_per_' . $formulaire_per->id . '.pdf');
    }

    public function show(FormulairePER $formulaire_per)
    {
        // Vérifier que l'utilisateur est propriétaire du formulaire
        if ($formulaire_per->utilisateur_id !== auth()->id()) {
            abort(403);
        }

        return view('formulaire_per.show', compact('formulaire_per'));
    }
}
