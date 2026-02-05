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
                    <!-- Étape 1/6 -->
                    <div class="mb-4">
                        <h5>Étape 1/6 : Catégorie de dossier</h5>
                        <p class="text-muted">Sélectionnez la catégorie de dossier que vous souhaitez créer.</p>
                    </div>

                    <form action="{{ route('dossiers.step2') }}" method="POST">
                        @csrf

                        <div class="form-group mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="categorie" id="public" value="public" checked>
                                <label class="form-check-label" for="public">
                                    <strong>📋 Public</strong>
                                    <small class="d-block text-muted">DAO, DRP, Demande de cotation</small>
                                </label>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="radio" name="categorie" id="prive" value="prive">
                                <label class="form-check-label" for="prive">
                                    <strong>🔒 Privé</strong>
                                    <small class="d-block text-muted">Autres formats (à compléter)</small>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('home') }}" class="btn btn-secondary">← Annuler</a>
                            <button type="submit" class="btn btn-primary">Continuer →</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
