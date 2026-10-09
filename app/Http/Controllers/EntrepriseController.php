<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EntrepriseController extends Controller
{
    public function index()
    {
        $entreprises = Entreprise::orderBy('nom')->get();
        return view('entreprises.index', compact('entreprises'));
    }

    public function create()
    {
        return view('entreprises.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'adresse_officielle' => ['nullable', 'string'],
            'annee_enregistrement' => ['nullable', 'integer'],
            'pays' => ['nullable', 'string', 'max:100'],
            'ifu' => ['nullable', 'string', 'max:100'],
            'rccm' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'fonction_responsable' => ['nullable', 'string', 'max:255'],
        ]);

        // handle registre upload if provided
        if ($request->hasFile('registre')) {
            $path = $request->file('registre')->store('entreprises/registres', 'public');
            $data['registre_path'] = $path;
        }

        $data['finance_data'] = $this->extractFinanceData($request);

        Entreprise::create($data);

        return redirect()->route('entreprises.index')->with('success', 'Entreprise créée avec succès.');
    }

    public function edit(Entreprise $entreprise)
    {
        return view('entreprises.edit', compact('entreprise'));
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'adresse_officielle' => ['nullable', 'string'],
            'annee_enregistrement' => ['nullable', 'integer'],
            'pays' => ['nullable', 'string', 'max:100'],
            'ifu' => ['nullable', 'string', 'max:100'],
            'rccm' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'fonction_responsable' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('registre')) {
            // delete old
            if (!empty($entreprise->registre_path)) {
                Storage::disk('public')->delete(ltrim($entreprise->registre_path, '/'));
            }
            $path = $request->file('registre')->store('entreprises/registres', 'public');
            $data['registre_path'] = $path;
        }

        $data['finance_data'] = $this->extractFinanceData($request);

        $entreprise->update($data);

        return redirect()->route('entreprises.index')->with('success', 'Entreprise mise à jour.');
    }

    public function destroy(Entreprise $entreprise)
    {
        // si des dossiers rattachés existent, empêcher suppression
        if ($entreprise->dossiers()->exists()) {
            return redirect()->route('entreprises.index')->with('error', 'Impossible de supprimer : des dossiers sont liés à cette entreprise.');
        }

        if (!empty($entreprise->registre_path)) {
            Storage::disk('public')->delete(ltrim($entreprise->registre_path, '/'));
        }

        $entreprise->delete();

        return redirect()->route('entreprises.index')->with('success', 'Entreprise supprimée.');
    }

    /**
     * Memes champs/regles que la section Finance (etape 6, Formulaire FIN
     * - 3.1 Situation financiere) : on ne modifie pas ce formulaire ici,
     * on se contente de capturer les memes informations sur l'entreprise
     * pour pouvoir les reutiliser plus tard.
     */
    private function extractFinanceData(Request $request): array
    {
        return $request->validate([
            'year_labels' => ['nullable', 'array'],
            'year_labels.*' => ['nullable', 'string', 'max:255'],
            'total_actif' => ['nullable', 'array'],
            'total_actif.*' => ['nullable', 'string', 'max:255'],
            'total_passif' => ['nullable', 'array'],
            'total_passif.*' => ['nullable', 'string', 'max:255'],
            'patrimoine_net' => ['nullable', 'array'],
            'patrimoine_net.*' => ['nullable', 'string', 'max:255'],
            'disponibilites' => ['nullable', 'array'],
            'disponibilites.*' => ['nullable', 'string', 'max:255'],
            'engagements' => ['nullable', 'array'],
            'engagements.*' => ['nullable', 'string', 'max:255'],
            'recettes_totales' => ['nullable', 'array'],
            'recettes_totales.*' => ['nullable', 'string', 'max:255'],
            'benefices_avant_impots' => ['nullable', 'array'],
            'benefices_avant_impots.*' => ['nullable', 'string', 'max:255'],
            'commentaire_complementaire' => ['nullable', 'string', 'max:4000'],
        ]);
    }
}
