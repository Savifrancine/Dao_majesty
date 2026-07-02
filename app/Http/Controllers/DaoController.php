<?php

namespace App\Http\Controllers;

use App\Models\Dao;
use Illuminate\Http\Request;

class DaoController extends Controller
{
    /**
     * Get validation rules for DAO.
     */
    private function validationRules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string'],
            'ville' => ['nullable', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'max:10'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $daos = Dao::paginate(10);
        return view('daos.index', compact('daos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('daos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        Dao::create($validated);

        return redirect()->route('daos.index')->with('success', 'DAO créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dao $dao)
    {
        return view('daos.show', compact('dao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dao $dao)
    {
        return view('daos.edit', compact('dao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dao $dao)
    {
        $validated = $request->validate($this->validationRules());

        $dao->update($validated);

        return redirect()->route('daos.show', $dao)->with('success', 'DAO mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dao $dao)
    {
        $this->denyEmployeeDeletion();
        $dao->delete();
        return redirect()->route('daos.index')->with('success', 'DAO supprimé avec succès.');
    }
}
