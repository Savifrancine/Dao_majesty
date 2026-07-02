@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Ajouter un signataire</h1>
    <form action="{{ route('signataires.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input id="prenom" name="prenom" class="form-control" value="{{ old('prenom') }}">
        </div>

        <div class="mb-3">
            <label for="fonction" class="form-label">Fonction</label>
            <input id="fonction" name="fonction" class="form-control" value="{{ old('fonction') }}">
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('signataires.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection