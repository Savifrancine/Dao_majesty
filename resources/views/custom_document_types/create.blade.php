@extends('layouts.app')

@section('content')
<style>
    .custom-document-create-page {
        min-height: calc(100vh - 88px);
        padding: 2rem 0 4rem;
        background:
            radial-gradient(ellipse at 12% 8%, rgba(16, 185, 129, 0.13), transparent 34rem),
            radial-gradient(ellipse at 92% 84%, rgba(59, 130, 246, 0.10), transparent 30rem),
            linear-gradient(145deg, #f8fafc 0%, #eef5f3 52%, #f1f5f9 100%);
    }

    .custom-document-create-card {
        max-width: 860px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.10);
    }

    .custom-document-create-header {
        padding: 1.75rem 2rem;
        color: #fff;
        background: linear-gradient(120deg, #047857, #10b981 62%, #34d399);
    }

    .custom-document-create-header h1 {
        margin: 0 0 .4rem;
        font-size: clamp(1.35rem, 3vw, 1.8rem);
        font-weight: 700;
    }

    .custom-document-create-header p {
        margin: 0;
        color: rgba(255, 255, 255, .9);
    }

    .custom-document-create-body {
        padding: 2rem;
    }

    .custom-document-create-body .form-control,
    .custom-document-create-body .form-select {
        width: 100%;
        min-width: 0;
        min-height: 48px;
        border-color: #cbd5e1;
        color: #1e293b;
        background-color: #fff;
    }

    .custom-document-create-body .form-select {
        min-height: 58px;
        padding: .8rem 2.75rem .8rem 1rem;
        font-size: .95rem;
        line-height: 1.45;
        text-overflow: ellipsis;
    }

    .custom-document-create-body .form-select option {
        white-space: normal;
    }

    .custom-document-dossier-preview {
        display: none;
        margin-top: .65rem;
        padding: .75rem .9rem;
        border: 1px solid #a7f3d0;
        border-radius: 9px;
        color: #065f46;
        background: #ecfdf5;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .custom-document-type-choice {
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .custom-document-type-choice + .custom-document-type-choice {
        margin-top: .65rem;
    }

    @media (max-width: 575.98px) {
        .custom-document-create-page {
            padding: 1rem 0 2rem;
        }

        .custom-document-create-header,
        .custom-document-create-body {
            padding: 1.25rem;
        }
    }
</style>

<div class="custom-document-create-page">
    <div class="container">
        <div class="custom-document-create-card">
            <div class="custom-document-create-header">
                <h1>Créer un document personnalisé</h1>
                <p>Configurez le document et choisissez le dossier auquel l’ajouter.</p>
            </div>

            <div class="custom-document-create-body">
                <form action="{{ route('custom-document-types.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="nom" class="form-label">Nom du document</label>
                        <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                        @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Ce nom sera le titre du document dans le PDF.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Type de document</label>
                        <div class="custom-document-type-choice form-check">
                            <input class="form-check-input" type="radio" name="type_formulaire" id="type_libre" value="libre" {{ old('type_formulaire', 'libre') === 'libre' ? 'checked' : '' }}>
                            <label class="form-check-label" for="type_libre">
                                <strong>Formulaire (texte et tableau)</strong><br>
                                Le document sera rédigé directement dans l’application.
                            </label>
                        </div>
                        <div class="custom-document-type-choice form-check">
                            <input class="form-check-input" type="radio" name="type_formulaire" id="type_fichier" value="piece_jointe" {{ old('type_formulaire') === 'piece_jointe' ? 'checked' : '' }}>
                            <label class="form-check-label" for="type_fichier">
                                <strong>Pièce jointe</strong><br>
                                Le document sera un fichier téléversé (PDF, image...).
                            </label>
                        </div>
                        @error('type_formulaire') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="dossier_id" class="form-label">Dossier</label>
                        <select id="dossier_id" name="dossier_id" class="form-select @error('dossier_id') is-invalid @enderror" aria-describedby="dossier-help dossier-selection-preview" required>
                            <option value="">-- Choisir un dossier --</option>
                            @foreach($dossiers as $d)
                                @php
                                    $dossierLabel = ($d->nom_dossier ?? ('Dossier #' . $d->id)) . ($d->reference_dossier ? ' — ' . $d->reference_dossier : '');
                                @endphp
                                <option value="{{ $d->id }}" title="{{ $dossierLabel }}" {{ (string) old('dossier_id') === (string) $d->id ? 'selected' : '' }}>{{ $dossierLabel }}</option>
                            @endforeach
                        </select>
                        @error('dossier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="dossier-selection-preview" class="custom-document-dossier-preview" aria-live="polite"></div>
                        <small id="dossier-help" class="text-muted d-block mt-2">Le document sera ajouté directement au sommaire de ce dossier.</small>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                        <a href="{{ route('custom-document-types.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dossierSelect = document.getElementById('dossier_id');
        const dossierPreview = document.getElementById('dossier-selection-preview');

        function updateDossierPreview() {
            const selectedOption = dossierSelect.options[dossierSelect.selectedIndex];
            const dossierName = dossierSelect.value ? selectedOption.textContent.trim() : '';
            dossierPreview.textContent = dossierName ? `Dossier sélectionné : ${dossierName}` : '';
            dossierPreview.style.display = dossierName ? 'block' : 'none';
        }

        dossierSelect.addEventListener('change', updateDossierPreview);
        updateDossierPreview();
    });
</script>
@endsection
