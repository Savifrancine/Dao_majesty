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
                    <!-- Étape 3/6 -->
                    <div class="mb-4">
                        <h5>Étape 3/6 : Informations de l'entreprise</h5>
                        <p class="text-muted">Sélectionnez une entreprise existante ou créez-en une nouvelle.</p>
                    </div>

                    <form action="{{ route('dossiers.step4') }}" method="POST">
                        @csrf
                        <input type="hidden" name="categorie" value="{{ $categorie }}">
                        <input type="hidden" name="type_dossier_id" value="{{ $typeDossierId }}">

                        <div class="form-group mb-4">
                            <label for="entreprise_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                            <select class="form-select @error('entreprise_id') is-invalid @enderror" name="entreprise_id" id="entreprise_id" required>
                                <option value="">-- Sélectionnez une entreprise --</option>
                                @foreach($entreprises as $entreprise)
                                    <option value="{{ $entreprise->id }}">{{ $entreprise->nom }} @if($entreprise->sigle)({{ $entreprise->sigle }})@endif</option>
                                @endforeach
                            </select>
                            @error('entreprise_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-center mb-4">
                            <small class="text-muted">ou</small>
                        </div>

                        <!-- Form créer entreprise -->
                        <div id="newEntrepriseForm" class="card border-info mb-4" style="display: none;">
                            <div class="card-body">
                                <h6 class="card-title">➕ Créer une nouvelle entreprise</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_nom" class="form-label">Nom *</label>
                                            <input type="text" class="form-control" id="new_nom" name="new_nom">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_sigle" class="form-label">Sigle</label>
                                            <input type="text" class="form-control" id="new_sigle" name="new_sigle" maxlength="10">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="new_adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control" id="new_adresse" name="new_adresse">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_telephone" class="form-label">Téléphone</label>
                                            <input type="tel" class="form-control" id="new_telephone" name="new_telephone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="new_email" name="new_email">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_responsable" class="form-label">Responsable</label>
                                            <input type="text" class="form-control" id="new_responsable" name="new_responsable">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_fonction" class="form-label">Fonction</label>
                                            <input type="text" class="form-control" id="new_fonction" name="new_fonction">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-sm btn-info" id="saveNewEntreprise">Créer cette entreprise</button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-link btn-sm mb-4" id="toggleNewEntreprise">
                            ➕ Créer une nouvelle entreprise
                        </button>

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

<script>
document.getElementById('toggleNewEntreprise').addEventListener('click', function(e) {
    e.preventDefault();
    const form = document.getElementById('newEntrepriseForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('saveNewEntreprise').addEventListener('click', function() {
    const data = {
        nom: document.getElementById('new_nom').value,
        sigle: document.getElementById('new_sigle').value,
        adresse: document.getElementById('new_adresse').value,
        telephone: document.getElementById('new_telephone').value,
        email: document.getElementById('new_email').value,
        responsable: document.getElementById('new_responsable').value,
        fonction_responsable: document.getElementById('new_fonction').value,
    };

    if (!data.nom) {
        alert('Le nom de l\'entreprise est requis');
        return;
    }

    fetch('{{ route("dossiers.storeEntreprise") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const option = document.createElement('option');
            option.value = res.entreprise.id;
            option.textContent = res.entreprise.nom + (res.entreprise.sigle ? ` (${res.entreprise.sigle})` : '');
            document.getElementById('entreprise_id').appendChild(option);
            document.getElementById('entreprise_id').value = res.entreprise.id;
            document.getElementById('newEntrepriseForm').style.display = 'none';
            alert('Entreprise créée avec succès!');
        }
    });
});
</script>
@endsection
