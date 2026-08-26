@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Modifier le document personnalisé</h1>
    <form action="{{ route('custom-document-types.update', $type) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nom" class="form-label">Nom du document</label>
            <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $type->nom) }}" required>
            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Type de document</label>
            <p class="form-control-plaintext">{{ $type->type_formulaire === 'fichier' ? 'Pièce jointe' : 'Formulaire (texte et tableau)' }}</p>
            <small class="text-muted">Le type ne peut pas être changé après création.</small>
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('custom-document-types.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
