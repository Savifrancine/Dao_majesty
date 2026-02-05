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
                    <!-- Étape 2/6 -->
                    <div class="mb-4">
                        <h5>Étape 2/6 : Quel type de dossier ?</h5>
                        <p class="text-muted">Sélectionnez le type de dossier que vous souhaitez créer.</p>
                    </div>

                    <form action="{{ route('dossiers.step3') }}" method="POST">
                        @csrf
                        <input type="hidden" name="categorie" value="{{ $categorie }}">

                        <div class="form-group mb-4">
                            <label for="type_dossier_id" class="form-label">Type de dossier <span class="text-danger">*</span></label>
                            <select class="form-select @error('type_dossier_id') is-invalid @enderror" name="type_dossier_id" id="type_dossier_id" required>
                                <option value="">-- Sélectionnez un type --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->nom }}</option>
                                @endforeach
                            </select>
                            @error('type_dossier_id')
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
