<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::orderBy('created_at', 'desc')->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'content' => 'nullable|string'
        ]);

        Template::create($data);
        return redirect()->route('templates.index')->with('success', 'Modèle enregistré');
    }

    public function edit(Template $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'content' => 'nullable|string'
        ]);

        $template->update($data);
        return redirect()->route('templates.index')->with('success', 'Modèle mis à jour');
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Modèle supprimé');
    }

    public function showJson(Template $template)
    {
        return response()->json(['id' => $template->id, 'nom' => $template->nom, 'type' => $template->type, 'content' => $template->content]);
    }
}
