@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">📝 Remplissage du dossier</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>📂 {{ $dossier->nom_dossier }}</strong> - {{ $dossier->typeDossier->nom }}
                    </div>

                    <!-- Récapitulatif documents -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">📋 Sommaire</h6>
                        </div>
                        <div class="card-body">
                            <ol>
                                @forelse($dossier->documents as $index => $doc)
                                <li>{{ $doc->typeDocument->nom }}</li>
                                @empty
                                <li class="text-muted">Aucun document sélectionné</li>
                                @endforelse
                            </ol>
                        </div>
                    </div>

                    <!-- Formulaires pour chaque document -->
                    <div id="documentForms"></div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">← Retour</button>
                        <button type="button" class="btn btn-success" id="generateBtn">
                            ✅ Générer le dossier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Les formulaires pour chaque document seront chargés dynamiquement
const documentForms = document.getElementById('documentForms');
const documents = {!! json_encode($dossier->documents->map(function($doc) {
    return [
        'id' => $doc->id,
        'name' => $doc->typeDocument->nom,
        'champs' => $doc->typeDocument->champs
    ];
})) !!};

documents.forEach((doc, index) => {
    const form = document.createElement('div');
    form.className = 'card mb-3';
    form.innerHTML = `
        <div class="card-header bg-light">
            <h6 class="mb-0">${index + 1}. ${doc.name}</h6>
        </div>
        <div class="card-body">
            <form class="documentForm" data-doc-id="${doc.id}">
                ${doc.champs.map(champ => `
                    <div class="form-group mb-3">
                        <label class="form-label">${champ.label} 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" 
                               name="champ_${champ.id}" 
                               placeholder="${champ.label}" required>
                    </div>
                `).join('')}
            </form>
        </div>
    `;
    documentForms.appendChild(form);
});

document.getElementById('generateBtn').addEventListener('click', function() {
    const forms = document.querySelectorAll('.documentForm');
    let allValid = true;
    const data = {};

    forms.forEach(form => {
        if (!form.checkValidity()) {
            allValid = false;
            form.classList.add('was-validated');
        }
        // Collecter les données
        const formData = new FormData(form);
        for (let [key, value] of formData) {
            data[key] = value;
        }
    });

    if (!allValid) {
        alert('Veuillez remplir tous les champs requis');
        return;
    }

    // TODO: Envoyer les données et générer PDF
    alert('✅ Dossier prêt à être généré!');
});
</script>
@endsection
