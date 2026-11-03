@extends('layouts.app')

@section('content')
<style>
    .custom-document-edit-dossier-select {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 58px;
        padding: .8rem 2.75rem .8rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #1e293b;
        background-color: #fff;
        font-size: .95rem;
        line-height: 1.45;
        text-overflow: ellipsis;
    }

    .custom-document-edit-dossier-preview {
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
</style>
<div class="container py-4">
    <h1>Modifier le document personnalisé</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('custom-document-types.update', $type) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nom" class="form-label">Nom du document</label>
            <input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $type->nom) }}" required>
            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Type de document</label>
            <p class="form-control-plaintext">{{ $type->type_formulaire === 'piece_jointe' ? 'Pièce jointe' : 'Formulaire (texte et tableau)' }}</p>
            <small class="text-muted">Le type ne peut pas être changé après création.</small>
        </div>

        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('custom-document-types.index') }}" class="btn btn-secondary">Annuler</a>
    </form>

    <hr class="my-4">

    <section aria-labelledby="add-document-to-dossier-title">
        <h2 id="add-document-to-dossier-title" class="h4">Ajouter ce document à un autre dossier</h2>
        <p class="text-muted">Le document sera ajouté au dossier choisi, avec des données indépendantes de celles des autres dossiers.</p>

        <div class="mb-3">
            <strong>Dossiers où ce document est déjà présent :</strong>
            <div class="mt-2">
                @forelse($type->dossierDocuments as $dossierDocument)
                    <span class="badge bg-light text-dark border mb-1">
                        {{ $dossierDocument->dossier?->nom_dossier ?? ('Dossier #' . $dossierDocument->dossier_id) }}{{ $dossierDocument->dossier?->reference_dossier ? ' — ' . $dossierDocument->dossier->reference_dossier : '' }}
                    </span>
                @empty
                    <span class="text-muted">Aucun dossier pour le moment.</span>
                @endforelse
            </div>
        </div>

        <form action="{{ route('custom-document-types.add-to-dossier', $type) }}" method="POST" class="row g-2">
            @csrf
            <div class="col-12 col-md-8">
                <label for="add_dossier_id" class="form-label">Choisir un autre dossier</label>
                <select id="add_dossier_id" name="dossier_id" class="custom-document-edit-dossier-select @error('dossier_id') is-invalid @enderror" aria-describedby="add-dossier-selection-preview" required>
                    <option value="">-- Choisir un dossier --</option>
                    @foreach($dossiers as $dossier)
                        @php
                            $alreadyAssigned = $type->dossierDocuments->contains(fn ($assigned) => (int) $assigned->dossier_id === (int) $dossier->id);
                        @endphp
                        <option value="{{ $dossier->id }}" {{ $alreadyAssigned ? 'disabled' : '' }}>
                            {{ $dossier->nom_dossier ?? ('Dossier #' . $dossier->id) }}{{ $dossier->reference_dossier ? ' — ' . $dossier->reference_dossier : '' }}{{ $alreadyAssigned ? ' (déjà ajouté)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('dossier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div id="add-dossier-selection-preview" class="custom-document-edit-dossier-preview" aria-live="polite"></div>
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary mt-md-4">Ajouter à ce dossier</button>
            </div>
        </form>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dossierSelect = document.getElementById('add_dossier_id');
        const dossierPreview = document.getElementById('add-dossier-selection-preview');

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
