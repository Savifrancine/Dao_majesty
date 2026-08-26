<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Entreprise;
use App\Models\FormulaireExp42B;
use App\Models\Signataire;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

class FormulaireExp42BController extends Controller
{
    public function index()
    {
        $formulaires = FormulaireExp42B::where('utilisateur_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('formulaire_exp_4_2_b.index', compact('formulaires'));
    }

    public function create()
    {
        $dossiers = Dossier::with('entreprise')->orderByDesc('created_at')->get();
        $entreprises = Entreprise::orderBy('nom')->get();
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('formulaire_exp_4_2_b.create', compact('dossiers', 'entreprises', 'signataires'));
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

        return view('formulaire_exp_4_2_b.generate', [
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
        $validated['formulaire_type'] = 'B';
        $validated['montant_part'] = $this->computeMontantPart($validated);

        FormulaireExp42B::create($validated);

        return redirect()->route('formulaire_exp_4_2_b.index')->with('success', 'Formulaire EXP-4.2 b) créé avec succès.');
    }

    public function edit(FormulaireExp42B $formulaireExp42B)
    {
        $this->authorizeForm($formulaireExp42B);

        $dossiers = Dossier::with('entreprise')->orderByDesc('created_at')->get();
        $entreprises = Entreprise::orderBy('nom')->get();
        $signataires = Signataire::orderBy('nom')->orderBy('prenom')->get();

        return view('formulaire_exp_4_2_b.edit', compact('formulaireExp42B', 'dossiers', 'entreprises', 'signataires'));
    }

    public function update(Request $request, FormulaireExp42B $formulaireExp42B)
    {
        $this->authorizeForm($formulaireExp42B);

        $validated = $this->validateForm($request);
        $validated = $this->applyDossierDefaults($validated);
        $validated['montant_part'] = $this->computeMontantPart($validated);

        $formulaireExp42B->update($validated);

        return redirect()->route('formulaire_exp_4_2_b.index')->with('success', 'Formulaire EXP-4.2 b) mis à jour avec succès.');
    }

    public function generate(Request $request)
    {
        $validated = $this->validateForm($request);
        $validated = $this->applyDossierDefaults($validated);
        $validated['utilisateur_id'] = auth()->id();
        $validated['formulaire_type'] = 'B';
        $validated['montant_part'] = $this->computeMontantPart($validated);

        $formulaireExp42B = FormulaireExp42B::create($validated);
        $formulaireExp42B->loadMissing('entreprise', 'signataire');
        $dossier = !empty($validated['dossier_id']) ? Dossier::with('entreprise', 'signataires')->find($validated['dossier_id']) : null;

        $logoDataUri = '';
        $logo = optional($dossier->entreprise)->logo ?? optional($formulaireExp42B->entreprise)->logo ?? null;
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

        $html = view('formulaire_exp_4_2_b.pdf', compact('formulaireExp42B', 'dossier', 'logoDataUri'))->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'formulaire_exp_4_2_b_' . $formulaireExp42B->id . '.pdf';

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function download(FormulaireExp42B $formulaireExp42B)
    {
        $this->authorizeForm($formulaireExp42B);

        $formulaireExp42B->loadMissing('entreprise', 'signataire', 'dossier.entreprise', 'dossier.signataires');
        $dossier = $formulaireExp42B->dossier;

        $logoDataUri = '';
        $logo = optional($dossier->entreprise)->logo ?? optional($formulaireExp42B->entreprise)->logo ?? null;
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

        $html = view('formulaire_exp_4_2_b.pdf', compact('formulaireExp42B', 'dossier', 'logoDataUri'))->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'formulaire_exp_4_2_b_' . $formulaireExp42B->id . '.pdf';

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function destroy(FormulaireExp42B $formulaireExp42B)
    {
        $this->authorizeForm($formulaireExp42B);

        $formulaireExp42B->delete();

        return redirect()->route('formulaire_exp_4_2_b.index')->with('success', 'Formulaire EXP-4.2 b) supprimé avec succès.');
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
            'identification_marche' => ['nullable', 'string'],
            'date_attribution' => ['nullable', 'date'],
            'date_achevement' => ['nullable', 'date'],
            'role_marche' => ['nullable', 'string', 'max:255'],
            'montant_total' => ['nullable', 'string', 'max:255'],
            'participation_pourcentage' => ['nullable', 'string', 'max:255'],
            'montant_part' => ['nullable', 'string', 'max:255'],
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

    private function authorizeForm(FormulaireExp42B $formulaireExp42B): void
    {
        if ($formulaireExp42B->utilisateur_id !== auth()->id()) {
            abort(403);
        }
    }

    private function computeMontantPart(array $data): string
    {
        $montant = $this->parseNumber($data['montant_total'] ?? '');
        $pourcentage = $this->parseNumber($data['participation_pourcentage'] ?? '');

        if ($montant === null || $pourcentage === null) {
            return $data['montant_part'] ?? '';
        }

        return $montant > 0 ? number_format($montant * ($pourcentage / 100), 0, ',', ' ') : '0';
    }

    private function parseNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = str_replace([' ', 'FCFA', 'fcfa', ','], ['', '', '', '.'], $value);
        $clean = preg_replace('/[^0-9.-]/', '', $clean);

        return is_numeric($clean) ? (float)$clean : null;
    }
}
