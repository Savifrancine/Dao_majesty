@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>{{ $titre }}</h1>
    <p class="text-muted">Dossier : {{ $dossier->nom_dossier ?? ('Dossier #' . $dossier->id) }}</p>
    <p class="text-muted">Les champs déjà connus sont pré-remplis automatiquement. Vérifiez-les et complétez le reste avant de générer l'étiquette.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('etiquettes.' . $type . '.form.update', $dossier) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="destinataires" class="form-label">Nom du destinataire</label>
            <input id="destinataires" name="destinataires" class="form-control @error('destinataires') is-invalid @enderror" value="{{ old('destinataires', $dossier->destinataires) }}">
            @error('destinataires') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="destinataire_adresse" class="form-label">Adresse du destinataire</label>
            <input id="destinataire_adresse" name="destinataire_adresse" class="form-control @error('destinataire_adresse') is-invalid @enderror" value="{{ old('destinataire_adresse', $dossier->destinataire_adresse) }}" placeholder="Ex: Sise à Cotonou, quartier « MENONTIN » rue avant canal3, 1ère rue à droite">
            @error('destinataire_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="titre_dossier" class="form-label">Type de procédure</label>
            <input id="titre_dossier" name="titre_dossier" class="form-control @error('titre_dossier') is-invalid @enderror" value="{{ old('titre_dossier', $dossier->titre_dossier) }}" placeholder="Ex: Demande de proposition de prix">
            @error('titre_dossier') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="reference_dossier" class="form-label">Référence</label>
                <input id="reference_dossier" name="reference_dossier" class="form-control @error('reference_dossier') is-invalid @enderror" value="{{ old('reference_dossier', $dossier->reference_dossier) }}">
                @error('reference_dossier') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="date_lancement" class="form-label">Date</label>
                <input type="date" id="date_lancement" name="date_lancement" class="form-control @error('date_lancement') is-invalid @enderror" value="{{ old('date_lancement', optional($dossier->date_lancement)->format('Y-m-d')) }}">
                @error('date_lancement') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="nom_dossier" class="form-label">Objet du marché</label>
            <textarea id="nom_dossier" name="nom_dossier" rows="2" class="form-control @error('nom_dossier') is-invalid @enderror">{{ old('nom_dossier', $dossier->nom_dossier) }}</textarea>
            @error('nom_dossier') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('etiquettes.' . $type . '.select') }}" class="btn btn-secondary">Retour à la liste</a>
    </form>

    <hr class="my-4">

    <h5>Générer l'étiquette</h5>
    <a href="{{ route('etiquettes.' . $type . '.generate', ['dossier' => $dossier->id, 'variante' => 'original']) }}" class="btn btn-secondary btn-sm">Télécharger l'original</a>
    <a href="{{ route('etiquettes.' . $type . '.generate', ['dossier' => $dossier->id, 'variante' => 'copie']) }}" class="btn btn-outline-secondary btn-sm">Télécharger la copie</a>
</div>
@endsection
