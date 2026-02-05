@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📁 Créer un nouveau dossier</h4>
                </div>
                <div class="card-body">
                    <!-- Étape 5/6 -->
                    <div class="mb-4">
                        <h5>Étape 5/6 : Sélection des documents</h5>
                        <p class="text-muted">Sélectionnez les documents que vous souhaitez inclure dans ce dossier. Ils formeront le sommaire.</p>
                    </div>

                    <form action="{{ route('dossiers.step6', $dossier->id) }}" method="POST" id="documentsForm">
                        @csrf
                        <input type="hidden" name="dossier_id" value="{{ $dossier->id }}">

                        <div class="row">
                            @foreach($documents as $doc)
                            <div class="col-md-6 mb-3">
                                <div class="form-check border p-3 rounded">
                                    <input class="form-check-input" type="checkbox" name="documents[]" 
                                           value="{{ $doc->id }}" id="doc_{{ $doc->id }}">
                                    <label class="form-check-label w-100" for="doc_{{ $doc->id }}">
                                        <strong>📄 {{ $doc->nom }}</strong>
                                        <br>
                                        <small class="text-muted">Type: {{ ucfirst($doc->type_formulaire) }}</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div id="selectedList" class="alert alert-info mt-4" style="display: none;">
                            <strong>📋 Documents sélectionnés :</strong>
                            <ol id="documentsList"></ol>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">← Retour</button>
                            <button type="submit" class="btn btn-primary" id="continueBtn" disabled>Continuer →</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const checkboxes = document.querySelectorAll('input[name="documents[]"]');
const selectedList = document.getElementById('selectedList');
const documentsList = document.getElementById('documentsList');
const continueBtn = document.getElementById('continueBtn');

function updateList() {
    const selected = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => ({
            id: cb.value,
            label: cb.parentElement.querySelector('strong').textContent
        }));

    if (selected.length === 0) {
        selectedList.style.display = 'none';
        continueBtn.disabled = true;
    } else {
        documentsList.innerHTML = selected
            .map(s => `<li>${s.label}</li>`)
            .join('');
        selectedList.style.display = 'block';
        continueBtn.disabled = false;
    }
}

checkboxes.forEach(cb => cb.addEventListener('change', updateList));
</script>
@endsection
