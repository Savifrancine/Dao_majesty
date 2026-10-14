@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Modifier le modèle</h3>
    <form action="{{ route('templates.update', $template) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" value="{{ $template->nom }}" required>
        </div>
        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="declaration" @selected($template->type === 'declaration')>Déclaration de garantie d'offre</option>
                <option value="engagement" @selected($template->type === 'engagement')>Engagement du soumissionnaire (code d'éthique et de déontologie)</option>
                <option value="libre" @selected($template->type === 'libre')>Document libre</option>
            </select>
            <small class="text-muted">Détermine où ce modèle apparaîtra dans la liste à choisir, à l'étape 6 d'un dossier.</small>
            @if(!in_array($template->type, ['declaration', 'engagement', 'libre'], true))
                <div class="form-text text-danger">Type actuel « {{ $template->type }} » non reconnu — ce modèle n'apparaît dans aucune liste. Choisissez un type ci-dessus puis enregistrez.</div>
            @endif
        </div>
        <div class="mb-3">
            <label>Contenu (texte)</label>
            <textarea name="content" rows="8" class="form-control">{{ $template->content }}</textarea>
        </div>
        <button class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
