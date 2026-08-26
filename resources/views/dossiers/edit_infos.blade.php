@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Modifier les informations du dossier</h1>
            <p class="text-muted">Ces informations sont utilisées pour la page de garde et le sommaire du dossier.</p>
        </div>
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-secondary">Retour au dossier</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dossiers.updateInfos', $dossier) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nom_dossier" class="form-label">Nom du dossier *</label>
            <input type="text" class="form-control" id="nom_dossier" name="nom_dossier" required value="{{ old('nom_dossier', $dossier->nom_dossier) }}">
        </div>

        <div class="mb-3">
            <label for="titre_dossier" class="form-label">Titre principal du dossier</label>
            <input type="text" class="form-control" id="titre_dossier" name="titre_dossier" value="{{ old('titre_dossier', $dossier->titre_dossier) }}">
        </div>

        <div class="mb-3">
            <label for="republique" class="form-label">République</label>
            <input type="text" class="form-control" id="republique" name="republique" value="{{ old('republique', $dossier->republique) }}">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="ministere" class="form-label">Ministère</label>
                <input type="text" class="form-control" id="ministere" name="ministere" value="{{ old('ministere', $dossier->ministere) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="direction" class="form-label">Direction</label>
                <input type="text" class="form-control" id="direction" name="direction" value="{{ old('direction', $dossier->direction) }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="services_projet" class="form-label">Services / Projet</label>
            <input type="text" class="form-control" id="services_projet" name="services_projet" value="{{ old('services_projet', $dossier->services_projet) }}">
        </div>

        <div class="mb-3">
            <label for="destinataires" class="form-label">Destinataires</label>
            <textarea class="form-control" id="destinataires" name="destinataires" rows="2">{{ old('destinataires', $dossier->destinataires) }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="reference_dossier" class="form-label">Référence du dossier</label>
                    <input type="text" class="form-control" id="reference_dossier" name="reference_dossier" value="{{ old('reference_dossier', $dossier->reference_dossier) }}">
                </div>
                <div class="mb-3">
                    <label for="ref" class="form-label">Ref</label>
                    <input type="text" class="form-control" id="ref" name="ref" placeholder="Ex: REF-123" value="{{ old('ref', $dossier->ref) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="date_lancement" class="form-label">Date de lancement</label>
                    <input type="date" class="form-control" id="date_lancement" name="date_lancement" value="{{ old('date_lancement', optional($dossier->date_lancement)->format('Y-m-d')) }}">
                </div>
                <div class="mb-3">
                    <label for="date_soumission" class="form-label">Date de soumission</label>
                    <input type="date" class="form-control" id="date_soumission" name="date_soumission" value="{{ old('date_soumission', optional($dossier->date_soumission)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="reference_step" class="form-label">Ref STEP</label>
                    <input type="text" class="form-control" id="reference_step" name="reference_step" value="{{ old('reference_step', $dossier->reference_step) }}">
                </div>
                <div class="mb-3">
                    <label for="source_financement" class="form-label">Source de financement</label>
                    <input type="text" class="form-control" id="source_financement" name="source_financement" value="{{ old('source_financement', $dossier->source_financement) }}">
                </div>
                <div class="mb-3">
                    <label for="gestion" class="form-label">Gestion</label>
                    <input type="text" class="form-control" id="gestion" name="gestion" value="{{ old('gestion', $dossier->gestion) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="imputation_budgetaire" class="form-label">Imputation Budgétaire</label>
                    <input type="text" class="form-control" id="imputation_budgetaire" name="imputation_budgetaire" value="{{ old('imputation_budgetaire', $dossier->imputation_budgetaire) }}">
                </div>
                <div class="mb-3">
                    <label for="accord_pret" class="form-label">Accord de Prêt</label>
                    <input type="text" class="form-control" id="accord_pret" name="accord_pret" value="{{ old('accord_pret', $dossier->accord_pret) }}">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="type_offre" class="form-label">Type d'offres</label>
            <input type="text" class="form-control" id="type_offre" name="type_offre" placeholder="Ex: Offre technique, Offre financière" value="{{ old('type_offre', $dossier->type_offre) }}">
        </div>

        <div class="mb-3">
            <label for="lots" class="form-label">Lots concernés</label>
            <input type="text" class="form-control" id="lots" name="lots" placeholder="Ex: Lot 1, Lot 2" value="{{ old('lots', $dossier->lots) }}">
        </div>

        <div class="mb-3">
            <label for="titre_lot" class="form-label">Titre du lot</label>
            <input type="text" class="form-control" id="titre_lot" name="titre_lot" value="{{ old('titre_lot', $dossier->titre_lot) }}">
        </div>

        <div class="mb-3">
            <label for="autres_details" class="form-label">Autres détails</label>
            <textarea class="form-control" id="autres_details" name="autres_details" rows="3">{{ old('autres_details', $dossier->autres_details) }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="mois_depot" class="form-label">Mois de dépôt</label>
                @php
                    $moisListe = [
                        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
                    ];
                    $selectedMois = old('mois_depot', $dossier->mois_depot);
                @endphp
                <select name="mois_depot" id="mois_depot" class="form-control">
                    <option value="">-- Sélectionner --</option>
                    @foreach($moisListe as $moisOption)
                        <option value="{{ $moisOption }}" @selected($selectedMois === $moisOption)>{{ $moisOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="annee_depot" class="form-label">Année de dépôt</label>
                @php
                    $anneeBaseDepot = (int) now()->format('Y');
                    $selectedAnneeDepot = old('annee_depot', $dossier->annee_depot);
                @endphp
                <select name="annee_depot" id="annee_depot" class="form-control">
                    <option value="">-- Sélectionner --</option>
                    @for($anneeOption = $anneeBaseDepot - 1; $anneeOption <= $anneeBaseDepot + 5; $anneeOption++)
                        <option value="{{ $anneeOption }}" @selected((string) $selectedAnneeDepot === (string) $anneeOption)>{{ $anneeOption }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
