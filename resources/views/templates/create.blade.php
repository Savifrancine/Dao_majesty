@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Créer un modèle</h3>
    <form action="{{ route('templates.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="declaration">Déclaration de garantie d'offre</option>
                <option value="engagement">Engagement du soumissionnaire (code d'éthique et de déontologie)</option>
                <option value="libre">Document libre</option>
            </select>
            <small class="text-muted">Détermine où ce modèle apparaîtra dans la liste à choisir, à l'étape 6 d'un dossier.</small>
        </div>
        <div class="mb-3">
            <label>Contenu (texte)</label>
            <textarea name="content" rows="8" class="form-control"></textarea>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
    </form>
</div>
@endsection
