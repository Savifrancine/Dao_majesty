@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>{{ $titre }}</h1>
    <p class="text-muted">Dossier : {{ $dossier->nom_dossier ?? ('Dossier #' . $dossier->id) }}</p>
    <p class="text-muted">L'enveloppe extérieure est unique : pas de distinction Original/Copie. Les champs déjà connus sont pré-remplis ; vérifiez-les et complétez le reste avant de générer l'étiquette.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('etiquettes.externe.form.update', $dossier) }}" method="POST">
        @csrf
        @method('PUT')

        <h5 class="mt-3">Attention</h5>
        <div class="mb-3">
            <label for="prmp_titre" class="form-label">Fonction du destinataire</label>
            <input id="prmp_titre" name="prmp_titre" class="form-control @error('prmp_titre') is-invalid @enderror" value="{{ old('prmp_titre', $dossier->prmp_titre ?? 'Personne Responsable des Marchés Publics') }}">
            @error('prmp_titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="prmp_nom" class="form-label">Nom du destinataire</label>
            <input id="prmp_nom" name="prmp_nom" class="form-control @error('prmp_nom') is-invalid @enderror" value="{{ old('prmp_nom', $dossier->prmp_nom) }}" placeholder="Ex: Madame Eunice Sangninon Lihoussou">
            @error('prmp_nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="prmp_telephone" class="form-label">Téléphone</label>
                <input id="prmp_telephone" name="prmp_telephone" class="form-control @error('prmp_telephone') is-invalid @enderror" value="{{ old('prmp_telephone', $dossier->prmp_telephone) }}">
                @error('prmp_telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="prmp_email" class="form-label">Email</label>
                <input id="prmp_email" name="prmp_email" class="form-control @error('prmp_email') is-invalid @enderror" value="{{ old('prmp_email', $dossier->prmp_email) }}">
                @error('prmp_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <h5 class="mt-4">Institution</h5>
        <div class="mb-3">
            <label for="institution_nom" class="form-label">Nom de l'institution</label>
            <input id="institution_nom" name="institution_nom" class="form-control @error('institution_nom') is-invalid @enderror" value="{{ old('institution_nom', $dossier->institution_nom ?? $dossier->destinataires) }}" placeholder="Ex: Centre Hospitalier Universitaire de Zone Suru Léré">
            @error('institution_nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="secretariat_adresse" class="form-label">Adresse du secrétariat</label>
            <textarea id="secretariat_adresse" name="secretariat_adresse" rows="3" class="form-control @error('secretariat_adresse') is-invalid @enderror" placeholder="Ex: Secrétariat permanent de la Personne Responsable des Marchés Publics situé dans le bloc administratif du...">{{ old('secretariat_adresse', $dossier->secretariat_adresse) }}</textarea>
            @error('secretariat_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <h5 class="mt-4">Marché</h5>
        <div class="mb-3">
            <label for="ref" class="form-label">Numéro de référence</label>
            <input id="ref" name="ref" class="form-control @error('ref') is-invalid @enderror" value="{{ old('ref', $dossier->ref) }}" placeholder="Ex: F_PRMP_102619">
            @error('ref') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="nom_dossier" class="form-label">Objet du marché</label>
            <textarea id="nom_dossier" name="nom_dossier" rows="2" class="form-control @error('nom_dossier') is-invalid @enderror">{{ old('nom_dossier', $dossier->nom_dossier) }}</textarea>
            @error('nom_dossier') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="lot" class="form-label">Lots concernés</label>
                <input id="lots" name="lots" class="form-control @error('lots') is-invalid @enderror" value="{{ old('lots', $dossier->lots) }}" placeholder="Ex: Lot 1, Lot 2">
                @error('lots') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 mb-3">
                <label for="titre_lot" class="form-label">Titre du lot</label>
                <input id="titre_lot" name="titre_lot" class="form-control @error('titre_lot') is-invalid @enderror" value="{{ old('titre_lot', $dossier->titre_lot) }}">
                @error('titre_lot') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('etiquettes.externe.select') }}" class="btn btn-secondary">Retour à la liste</a>
    </form>

    <hr class="my-4">

    <h5>Générer l'étiquette</h5>
    <a href="{{ route('etiquettes.externe.generate', $dossier) }}" class="btn btn-secondary btn-sm">Télécharger l'étiquette</a>
</div>
@endsection
