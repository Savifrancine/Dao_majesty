<?php

namespace App\Http\Controllers;

use App\Models\CatalogueArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $catalogueArticles = CatalogueArticle::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('designation', 'like', "%{$search}%")
                        ->orWhere('categorie', 'like', "%{$search}%")
                        ->orWhere('marque_fabricant', 'like', "%{$search}%")
                        ->orWhere('marque_type', 'like', "%{$search}%");
                });
            })
            ->orderBy('designation')
            ->paginate(20)
            ->withQueryString();

        return view('catalogue.index', compact('catalogueArticles', 'search'));
    }

    public function create()
    {
        return view('catalogue.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categorie' => ['nullable', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'marque_fabricant' => ['nullable', 'string', 'max:255'],
            'marque_type' => ['nullable', 'string', 'max:255'],
            'commentaire' => ['nullable', 'string'],
            'prospectus' => ['nullable', 'file', 'max:20480'],
        ]);

        if ($request->hasFile('prospectus')) {
            $data['chemin_prospectus'] = $request->file('prospectus')->store('catalogue/prospectus', 'public');
            $data['nom_original_prospectus'] = $request->file('prospectus')->getClientOriginalName();
        }
        unset($data['prospectus']);

        $data['utilisateur_id'] = auth()->id();

        CatalogueArticle::create($data);

        return redirect()->route('catalogue.index')->with('success', 'Article ajouté au catalogue.');
    }

    public function edit(CatalogueArticle $catalogueArticle)
    {
        return view('catalogue.edit', compact('catalogueArticle'));
    }

    public function update(Request $request, CatalogueArticle $catalogueArticle)
    {
        $data = $request->validate([
            'categorie' => ['nullable', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'marque_fabricant' => ['nullable', 'string', 'max:255'],
            'marque_type' => ['nullable', 'string', 'max:255'],
            'commentaire' => ['nullable', 'string'],
            'prospectus' => ['nullable', 'file', 'max:20480'],
            'delete_prospectus' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('prospectus')) {
            if (!empty($catalogueArticle->chemin_prospectus)) {
                Storage::disk('public')->delete(ltrim($catalogueArticle->chemin_prospectus, '/'));
            }
            $data['chemin_prospectus'] = $request->file('prospectus')->store('catalogue/prospectus', 'public');
            $data['nom_original_prospectus'] = $request->file('prospectus')->getClientOriginalName();
        } elseif ($request->boolean('delete_prospectus')) {
            if (!empty($catalogueArticle->chemin_prospectus)) {
                Storage::disk('public')->delete(ltrim($catalogueArticle->chemin_prospectus, '/'));
            }
            $data['chemin_prospectus'] = null;
            $data['nom_original_prospectus'] = null;
        }
        unset($data['prospectus'], $data['delete_prospectus']);

        $catalogueArticle->update($data);

        return redirect()->route('catalogue.index')->with('success', 'Article du catalogue mis à jour.');
    }

    public function destroy(CatalogueArticle $catalogueArticle)
    {
        $this->denyEmployeeDeletion();

        if (!empty($catalogueArticle->chemin_prospectus)) {
            Storage::disk('public')->delete(ltrim($catalogueArticle->chemin_prospectus, '/'));
        }

        $catalogueArticle->delete();

        return redirect()->route('catalogue.index')->with('success', 'Article retiré du catalogue.');
    }
}
