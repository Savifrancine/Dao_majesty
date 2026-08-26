<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Entreprise;
use App\Models\FormulaireExp42ASuite;
use App\Models\Signataire;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

class FormulaireExp42ASuiteController extends Controller
{
    public function index()
    {
        $formulaires = FormulaireExp42ASuite::where('utilisateur_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('formulaire_exp_4_2_a_suite.index', compact('formulaires'));
    }

    public function create()
    {
        $dossiers = Dossier::with('entreprise')->orderByDesc('created_at')->get();
        $entreprises = Entreprise::orderBy('nom')->get();
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('formulaire_exp_4_2_a_suite.create', compact('dossiers', 'entreprises', 'signataires'));
    }

    public function generateForm(Request $request)
    {
        $dossier = null;
        $dossierId = $request->query('dossier_id');

        if (!empty($dossierId)) {
            $dossier = Dossier::with('entreprise', 'signataires')->find($dossierId);
        }

        $dossiers = Dossier::with('entreprise')->orderByDesc('created_at')->get();
        $entreprises = Entreprise::orderBy('nom')->get();
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('formulaire_exp_4_2_a_suite.generate', [
            'dossier_id' => $dossierId,
            'dossier' => $dossier,
            'dossiers' => $dossiers,
            'entreprises' => $entreprises,
            'signataires' => $signataires,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);
        $validated = $this->applyDossierDefaults($validated);
        $validated['utilisateur_id'] = auth()->id();
        $validated['formulaire_type'] = 'A_SUITE';

        FormulaireExp42ASuite::create($validated);

        return redirect()->route('formulaire_exp_4_2_a_suite.index')->with('success', 'Formulaire EXP-4.2 a) (suite) créé avec succès.');
    }

    public function edit(FormulaireExp42ASuite $formulaireExp42ASuite)
    {
        $this->authorizeForm($formulaireExp42ASuite);

        $dossiers = Dossier::with('entreprise')->orderByDesc('created_at')->get();
        $entreprises = Entreprise::orderBy('nom')->get();
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('formulaire_exp_4_2_a_suite.edit', compact('formulaireExp42ASuite', 'dossiers', 'entreprises', 'signataires'));
    }

    public function update(Request $request, FormulaireExp42ASuite $formulaireExp42ASuite)
    {
        $this->authorizeForm($formulaireExp42ASuite);

        $validated = $this->validateForm($request);
        $validated = $this->applyDossierDefaults($validated);

        $formulaireExp42ASuite->update($validated);

        return redirect()->route('formulaire_exp_4_2_a_suite.index')->with('success', 'Formulaire EXP-4.2 a) (suite) mis à jour avec succès.');
    }

    public function generate(Request $request)
    {
        $validated = $this->validateForm($request);
        $validated = $this->applyDossierDefaults($validated);
        $validated['utilisateur_id'] = auth()->id();
        $validated['formulaire_type'] = 'A_SUITE';

        $formulaireExp42ASuite = FormulaireExp42ASuite::create($validated);
        $formulaireExp42ASuite->loadMissing('entreprise', 'signataire');
        $dossier = !empty($validated['dossier_id']) ? Dossier::with('entreprise', 'signataires')->find($validated['dossier_id']) : null;

        $logoDataUri = $this->getLogoDataUri($dossier, $formulaireExp42ASuite);

        $html = view('formulaire_exp_4_2_a_suite.pdf', compact('formulaireExp42ASuite', 'dossier', 'logoDataUri'))->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'formulaire_exp_4_2_a_suite_' . $formulaireExp42ASuite->id . '.pdf';

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function download(FormulaireExp42ASuite $formulaireExp42ASuite)
    {
        $this->authorizeForm($formulaireExp42ASuite);

        $formulaireExp42ASuite->loadMissing('entreprise', 'signataire', 'dossier.entreprise', 'dossier.signataires');
        $dossier = $formulaireExp42ASuite->dossier;

        $logoDataUri = $this->getLogoDataUri($dossier, $formulaireExp42ASuite);

        $html = view('formulaire_exp_4_2_a_suite.pdf', compact('formulaireExp42ASuite', 'dossier', 'logoDataUri'))->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'formulaire_exp_4_2_a_suite_' . $formulaireExp42ASuite->id . '.pdf';

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function destroy(FormulaireExp42ASuite $formulaireExp42ASuite)
    {
        $this->authorizeForm($formulaireExp42ASuite);

        $formulaireExp42ASuite->delete();

        return redirect()->route('formulaire_exp_4_2_a_suite.index')->with('success', 'Formulaire EXP-4.2 a) (suite) supprimé avec succès.');
    }

    private function validateForm(Request $request): array
    {
        return $request->validate([
            'dossier_id' => ['nullable', 'exists:dossiers,id'],
            'entreprise_id' => ['nullable', 'exists:entreprises,id'],
            'signataire_id' => ['nullable', 'exists:signataires,id'],
            'nom_candidat' => ['nullable', 'string', 'max:255'],
            'date_formulaire' => ['nullable', 'date'],
            'numero_adrp' => ['nullable', 'string', 'max:255'],
            'numero_marche' => ['nullable', 'string', 'max:255'],
            'description_similitude' => ['nullable', 'string'],
            'montant' => ['nullable', 'string', 'max:255'],
            'taille_physique' => ['nullable', 'string', 'max:255'],
            'complexite' => ['nullable', 'string', 'max:255'],
            'methodes_technologie' => ['nullable', 'string', 'max:255'],
            'autres_caracteristiques' => ['nullable', 'string', 'max:255'],
            'autorite_nom' => ['nullable', 'string', 'max:255'],
            'autorite_adresse' => ['nullable', 'string'],
            'autorite_telephone' => ['nullable', 'string', 'max:255'],
            'autorite_email' => ['nullable', 'email', 'max:255'],
        ]);
    }

    private function applyDossierDefaults(array $validated): array
    {
        $dossier = null;
        $entreprise = null;
        $signataire = null;

        if (!empty($validated['dossier_id'])) {
            $dossier = Dossier::with('entreprise', 'signataires')->find($validated['dossier_id']);
        }

        if (!empty($validated['entreprise_id'])) {
            $entreprise = Entreprise::find($validated['entreprise_id']);
        }

        if (!$entreprise && !empty($dossier)) {
            $entreprise = optional($dossier)->entreprise;
        }

        if (!empty($validated['signataire_id'])) {
            $signataire = Signataire::find($validated['signataire_id']);
        }

        if (!$signataire && !empty($dossier)) {
            $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
        }

        if (!empty($entreprise)) {
            if (empty($validated['nom_candidat'])) {
                $validated['nom_candidat'] = trim($entreprise->responsable ?? $entreprise->nom ?? '');
            }
        }

        if (!empty($signataire)) {
            $validated['nom_signataire'] = trim($signataire->nom . ' ' . ($signataire->prenom ?? ''));
            $validated['fonction_signataire'] = trim($signataire->fonction ?? '');
        }

        if (!empty($dossier)) {
            $company = trim(
                optional($dossier->entreprise)->responsable
                ?? optional($dossier->entreprise)->nom
                ?? ''
            );

            if (empty($validated['nom_candidat'])) {
                $validated['nom_candidat'] = $company ?: trim($dossier->reference_dossier ?? '');
            }

            if (empty($validated['numero_adrp'])) {
                $reference = trim(optional($dossier)->reference_dossier ?? '');
                $dateLancement = null;
                if (!empty(optional($dossier)->date_lancement)) {
                    try {
                        $dateLancement = \Carbon\Carbon::parse($dossier->date_lancement)->format('d/m/Y');
                    } catch (\Throwable $e) {
                        $dateLancement = $dossier->date_lancement;
                    }
                }
                $titre = trim(optional($dossier)->titre_dossier ?? optional($dossier)->titre_lot ?? '');
                $parts = [];
                if ($reference !== '') {
                    $parts[] = $reference;
                }
                if ($dateLancement) {
                    $parts[] = 'du ' . $dateLancement;
                }
                if ($titre !== '') {
                    $parts[] = 'relatif ' . $titre;
                }
                $validated['numero_adrp'] = trim(implode(' ', $parts));
            }
        }

        if (empty($validated['nom_candidat'])) {
            $validated['nom_candidat'] = 'Candidat non renseigné';
        }

        return $validated;
    }

    private function authorizeForm(FormulaireExp42ASuite $formulaireExp42ASuite): void
    {
        if ($formulaireExp42ASuite->utilisateur_id !== auth()->id()) {
            abort(403);
        }
    }

    private function getLogoDataUri($dossier, $formulaire): string
    {
        $logoDataUri = '';
        $logo = optional($dossier->entreprise)->logo ?? optional($formulaire->entreprise)->logo ?? null;
        if ($logo) {
            if (str_starts_with($logo, 'data:') || str_starts_with($logo, 'http')) {
                $logoDataUri = $logo;
            } else {
                $candidate = storage_path('app/public/' . ltrim($logo, '/'));
                if (file_exists($candidate)) {
                    $ext = pathinfo($candidate, PATHINFO_EXTENSION);
                    $data = base64_encode(file_get_contents($candidate));
                    $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
                }
            }
        }
        if (!$logoDataUri) {
            $fallback = storage_path('app/public/entreprises/logos/logo.jpeg');
            if (file_exists($fallback)) {
                $ext = pathinfo($fallback, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($fallback));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }
        if (!$logoDataUri) {
            $fallback = storage_path('app/public/entreprises/logo.jpeg');
            if (file_exists($fallback)) {
                $ext = pathinfo($fallback, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($fallback));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }
        if (!$logoDataUri) {
            $fallback2 = public_path('logo.jpeg');
            if (file_exists($fallback2)) {
                $ext = pathinfo($fallback2, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($fallback2));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }
        return $logoDataUri;
    }
}
