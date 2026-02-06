<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LookupController extends Controller
{
    // Mapping safe des kinds vers modèles
    protected $mapping = [
        'types-marches' => \App\Models\TypeMarche::class,
        'procedures' => \App\Models\Procedure::class,
        'autorites' => \App\Models\AutoriteContractante::class,
        'sources' => \App\Models\SourceFinancement::class,
        // signataires requires more fields / upload -> not handled here
    ];

    public function store(Request $request, $kind)
    {
        if (!isset($this->mapping[$kind])) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), ['nom' => 'required|string|max:255']);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $modelClass = $this->mapping[$kind];
            $model = $modelClass::create(['nom' => $request->nom]);

            return response()->json(['success' => true, 'id' => $model->id, 'nom' => $model->nom]);
        } catch (\Exception $e) {
            \Log::error('Lookup store error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }
}
