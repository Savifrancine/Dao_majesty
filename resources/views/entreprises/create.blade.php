@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Ajouter une entreprise</h1>
    <form action="{{ route('entreprises.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="sigle" class="form-label">Sigle</label>
            <input id="sigle" name="sigle" class="form-control" value="{{ old('sigle') }}">
        </div>

        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse</label>
            <input id="adresse" name="adresse" class="form-control" value="{{ old('adresse') }}">
        </div>

        <div class="mb-3">
            <label for="adresse_officielle" class="form-label">Adresse officielle</label>
            <input id="adresse_officielle" name="adresse_officielle" class="form-control" value="{{ old('adresse_officielle') }}">
        </div>

        <div class="mb-3">
            <label for="annee_enregistrement" class="form-label">Année d'enregistrement</label>
            <input id="annee_enregistrement" name="annee_enregistrement" type="number" min="1900" max="2100" class="form-control @error('annee_enregistrement') is-invalid @enderror" value="{{ old('annee_enregistrement') }}">
            @error('annee_enregistrement') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="pays" class="form-label">Pays</label>
            <select id="pays" name="pays" class="form-control">
                @include('partials.pays_options', ['selectedPays' => old('pays')])
            </select>
        </div>

        <div class="mb-3">
            <label for="ifu" class="form-label">IFU</label>
            <input id="ifu" name="ifu" class="form-control" value="{{ old('ifu') }}">
        </div>

        <div class="mb-3">
            <label for="rccm" class="form-label">Numéro d'enregistrement RCCM</label>
            <input id="rccm" name="rccm" class="form-control @error('rccm') is-invalid @enderror" value="{{ old('rccm') }}" required>
            @error('rccm') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="registre" class="form-label">Registre de commerce (PDF/JPG/PNG)</label>
            <input id="registre" name="registre" type="file" class="form-control">
        </div>

        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input id="telephone" name="telephone" class="form-control" value="{{ old('telephone') }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label for="responsable" class="form-label">Responsable</label>
            <input id="responsable" name="responsable" class="form-control" value="{{ old('responsable') }}">
        </div>

        <div class="mb-3">
            <label for="fonction_responsable" class="form-label">Fonction responsable</label>
            <input id="fonction_responsable" name="fonction_responsable" class="form-control" value="{{ old('fonction_responsable') }}">
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('entreprises.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
