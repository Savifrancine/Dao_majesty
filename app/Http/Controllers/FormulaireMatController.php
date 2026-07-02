<?php

namespace App\Http\Controllers;

use App\Models\FormulaireMat;
use App\Models\Signataire;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

class FormulaireMatController extends Controller
{
    public function index()
    {
        $formulaires = FormulaireMat::with('signataire')
            ->where('utilisateur_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('formulaire_mat.index', compact('formulaires'));
    }

    public function create()
    {
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();
        return view('formulaire_mat.create', compact('signataires'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'piece_materiel' => ['required', 'string', 'max:255'],
            'fabricant' => ['nullable', 'string', 'max:255'],
            'modele_puissance' => ['nullable', 'string', 'max:255'],
            'capacite' => ['nullable', 'string', 'max:1000'],
            'annee_fabrication' => ['nullable', 'string', 'max:10'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'engagements' => ['nullable', 'string'],
            'provenance' => ['nullable', 'string', 'max:50'],
            'signataire_id' => ['nullable', 'exists:signataires,id'],
            'lieu_fait' => ['nullable', 'string', 'max:255'],
            'date_fait' => ['nullable', 'date'],
        ]);

        $validated['utilisateur_id'] = auth()->id();

        FormulaireMat::create($validated);

        return redirect()->route('formulaire_mat.index')->with('success', 'Formulaire MAT créé avec succès.');
    }

    public function edit(FormulaireMat $formulaireMat)
    {
        $this->authorizeForm($formulaireMat);

        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();
        return view('formulaire_mat.edit', compact('formulaireMat', 'signataires'));
    }

    public function generateForm()
    {
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();
        return view('formulaire_mat.generate', compact('signataires'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'piece_materiel' => ['required', 'string', 'max:255'],
            'fabricant' => ['nullable', 'string', 'max:255'],
            'modele_puissance' => ['nullable', 'string', 'max:255'],
            'capacite' => ['nullable', 'string', 'max:1000'],
            'annee_fabrication' => ['nullable', 'string', 'max:10'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'engagements' => ['nullable', 'string'],
            'provenance' => ['nullable', 'string', 'max:50'],
            'signataire_id' => ['nullable', 'exists:signataires,id'],
            'lieu_fait' => ['nullable', 'string', 'max:255'],
            'date_fait' => ['nullable', 'date'],
        ]);

        // Save FormulaireMat to database
        $validated['utilisateur_id'] = auth()->id();
        $formulaireMat = FormulaireMat::create($validated);
        $formulaireMat->load('signataire');

        // Prepare signature data if present
        $signataireDataUri = null;
        if (!empty($formulaireMat->signataire?->signature_path)) {
            $signaturePath = storage_path('app/public/' . ltrim($formulaireMat->signataire->signature_path, '/'));
            if (file_exists($signaturePath)) {
                $mime = mime_content_type($signaturePath) ?: 'image/png';
                $signataireDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($signaturePath));
            }
        }

        // Generate PDF
        $html = view('formulaire_mat.pdf', compact('formulaireMat', 'signataireDataUri'))->render();
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'formulaire_mat_' . $formulaireMat->id . '.pdf';

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function update(Request $request, FormulaireMat $formulaireMat)
    {
        $this->authorizeForm($formulaireMat);

        $validated = $request->validate([
            'piece_materiel' => ['required', 'string', 'max:255'],
            'fabricant' => ['nullable', 'string', 'max:255'],
            'modele_puissance' => ['nullable', 'string', 'max:255'],
            'capacite' => ['nullable', 'string', 'max:1000'],
            'annee_fabrication' => ['nullable', 'string', 'max:10'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'engagements' => ['nullable', 'string'],
            'provenance' => ['nullable', 'string', 'max:50'],
            'signataire_id' => ['nullable', 'exists:signataires,id'],
            'lieu_fait' => ['nullable', 'string', 'max:255'],
            'date_fait' => ['nullable', 'date'],
        ]);

        $formulaireMat->update($validated);

        return redirect()->route('formulaire_mat.index')->with('success', 'Formulaire MAT mis à jour avec succès.');
    }

    public function destroy(FormulaireMat $formulaireMat)
    {
        $this->denyEmployeeDeletion();
        $this->authorizeForm($formulaireMat);

        $formulaireMat->delete();

        return redirect()->route('formulaire_mat.index')->with('success', 'Formulaire MAT supprimé avec succès.');
    }

    public function download(FormulaireMat $formulaireMat)
    {
        $this->authorizeForm($formulaireMat);

        $formulaireMat->load('signataire');
        $signataireDataUri = null;
        if (!empty($formulaireMat->signataire?->signature_path)) {
            $signaturePath = storage_path('app/public/' . ltrim($formulaireMat->signataire->signature_path, '/'));
            if (file_exists($signaturePath)) {
                $mime = mime_content_type($signaturePath) ?: 'image/png';
                $content = file_get_contents($signaturePath);
                $signataireDataUri = 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        }

        $html = view('formulaire_mat.pdf', compact('formulaireMat', 'signataireDataUri'))->render();
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'formulaire_mat_' . $formulaireMat->id . '.pdf';

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function authorizeForm(FormulaireMat $formulaireMat)
    {
        if ($formulaireMat->utilisateur_id !== auth()->id()) {
            abort(403);
        }
    }
}
