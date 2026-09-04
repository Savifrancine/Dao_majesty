@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Modifier l'entreprise</h1>
    <form action="{{ route('entreprises.update', $entreprise) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $entreprise->nom) }}" required>
            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="sigle" class="form-label">Sigle</label>
            <input id="sigle" name="sigle" class="form-control" value="{{ old('sigle', $entreprise->sigle) }}">
        </div>

        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse</label>
            <input id="adresse" name="adresse" class="form-control" value="{{ old('adresse', $entreprise->adresse) }}">
        </div>

        <div class="mb-3">
            <label for="adresse_officielle" class="form-label">Adresse officielle</label>
            <input id="adresse_officielle" name="adresse_officielle" class="form-control" value="{{ old('adresse_officielle', $entreprise->adresse_officielle) }}">
        </div>

        <div class="mb-3">
            <label for="annee_enregistrement" class="form-label">Année d'enregistrement</label>
            <input id="annee_enregistrement" name="annee_enregistrement" type="number" min="1900" max="2100" class="form-control @error('annee_enregistrement') is-invalid @enderror" value="{{ old('annee_enregistrement', $entreprise->annee_enregistrement) }}">
            @error('annee_enregistrement') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="pays" class="form-label">Pays</label>
            <select id="pays" name="pays" class="form-control">
                @include('partials.pays_options', ['selectedPays' => old('pays', $entreprise->pays)])
            </select>
        </div>

        <div class="mb-3">
            <label for="ifu" class="form-label">IFU</label>
            <input id="ifu" name="ifu" class="form-control" value="{{ old('ifu', $entreprise->ifu) }}">
        </div>

        <div class="mb-3">
            <label for="rccm" class="form-label">Numéro d'enregistrement RCCM</label>
            <input id="rccm" name="rccm" class="form-control @error('rccm') is-invalid @enderror" value="{{ old('rccm', $entreprise->rccm) }}" required>
            @error('rccm') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="registre" class="form-label">Registre de commerce (PDF/JPG/PNG)</label>
            <input id="registre" name="registre" type="file" class="form-control">
            @if(!empty($entreprise->registre_path))
                <p class="mt-2">Fichier actuel: <a href="{{ asset('media-files/' . ltrim($entreprise->registre_path, '/')) }}" target="_blank">Voir</a></p>
            @endif
        </div>

        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input id="telephone" name="telephone" class="form-control" value="{{ old('telephone', $entreprise->telephone) }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $entreprise->email) }}">
        </div>

        <div class="mb-3">
            <label for="responsable" class="form-label">Responsable</label>
            <input id="responsable" name="responsable" class="form-control" value="{{ old('responsable', $entreprise->responsable) }}">
        </div>

        <div class="mb-3">
            <label for="fonction_responsable" class="form-label">Fonction responsable</label>
            <input id="fonction_responsable" name="fonction_responsable" class="form-control" value="{{ old('fonction_responsable', $entreprise->fonction_responsable) }}">
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('entreprises.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
