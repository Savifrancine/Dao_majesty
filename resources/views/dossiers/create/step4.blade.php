@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📁 Créer un nouveau dossier</h4>
                </div>
                <div class="card-body">
                    <!-- Étape 4/6 -->
                    <div class="mb-4">
                        <h5>Étape 4/6 : Informations du dossier</h5>
                        <p class="text-muted">Ces informations seront utilisées pour la page de garde et le sommaire.</p>
                    </div>

                    <form action="{{ route('dossiers.step5') }}" method="POST">
                        @csrf
                        <input type="hidden" name="categorie" value="{{ $categorie }}">
                        <input type="hidden" name="type_dossier_id" value="{{ $type_dossier_id }}">
                        <input type="hidden" name="entreprise_id" value="{{ $entreprise_id }}">

                        <div class="form-group mb-3">
                            <label for="nom_dossier" class="form-label">Nom du dossier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nom_dossier') is-invalid @enderror" 
                                   id="nom_dossier" name="nom_dossier" placeholder="Ex: DAO Fournitures 2026" required value="{{ old('nom_dossier') }}">
                            @error('nom_dossier')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="lot" class="form-label">Lot</label>
                            <input type="text" class="form-control @error('lot') is-invalid @enderror" 
                                   id="lot" name="lot" placeholder="Ex: Lot 1, Lot 2, etc." value="{{ old('lot') }}">
                            @error('lot')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="objectif" class="form-label">Objectif du dossier</label>
                            <textarea class="form-control @error('objectif') is-invalid @enderror" 
                                      id="objectif" name="objectif" rows="4" placeholder="Décrivez l'objectif du dossier...">{{ old('objectif') }}</textarea>
                            @error('objectif')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">← Retour</button>
                            <button type="submit" class="btn btn-primary">Continuer →</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
