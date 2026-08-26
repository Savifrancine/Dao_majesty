@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Créer un document personnalisé</h1>
    <form action="{{ route('custom-document-types.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nom" class="form-label">Nom du document</label>
            <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="text-muted">Ce nom sera le titre du document dans le PDF.</small>
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Type de document</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="type_formulaire" id="type_libre" value="libre" {{ old('type_formulaire', 'libre') === 'libre' ? 'checked' : '' }}>
                <label class="form-check-label" for="type_libre">
                    Formulaire (texte et tableau) — le document sera rédigé directement dans l'application (texte libre, tableaux personnalisés).
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="type_formulaire" id="type_fichier" value="piece_jointe" {{ old('type_formulaire') === 'piece_jointe' ? 'checked' : '' }}>
                <label class="form-check-label" for="type_fichier">
                    Pièce jointe — le document sera un fichier téléversé (PDF, image...), affiché comme les autres pièces jointes.
                </label>
            </div>
            @error('type_formulaire') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="dossier_id" class="form-label">Dossier</label>
            <select id="dossier_id" name="dossier_id" class="form-control @error('dossier_id') is-invalid @enderror" required>
                <option value="">-- Choisir un dossier --</option>
                @foreach($dossiers as $d)
                    <option value="{{ $d->id }}" {{ (string) old('dossier_id') === (string) $d->id ? 'selected' : '' }}>{{ $d->nom_dossier ?? ('Dossier #' . $d->id) }}{{ $d->reference_dossier ? ' — ' . $d->reference_dossier : '' }}</option>
                @endforeach
            </select>
            @error('dossier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="text-muted">Le document sera ajouté directement au sommaire de ce dossier.</small>
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('custom-document-types.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
