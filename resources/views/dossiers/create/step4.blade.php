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

                    <form action="{{ route('dossiers.step5') }}" method="POST" enctype="multipart/form-data">
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
                            <label for="titre_dossier" class="form-label">Titre principal du dossier</label>
                            <input type="text" class="form-control @error('titre_dossier') is-invalid @enderror"
                                   id="titre_dossier" name="titre_dossier" placeholder="Ex: DOSSIER D’APPEL D’OFFRES" value="{{ old('titre_dossier') }}">
                            @error('titre_dossier')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="republique" class="form-label">République</label>
                                <input type="text" name="republique" id="republique" class="form-control" value="{{ old('republique') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ministere" class="form-label">Ministère</label>
                                <input type="text" name="ministere" id="ministere" class="form-control" value="{{ old('ministere') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="direction" class="form-label">Direction</label>
                                <input type="text" name="direction" id="direction" class="form-control" value="{{ old('direction') }}">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="services_projet" class="form-label">Services / Projet</label>
                            <input type="text" name="services_projet" id="services_projet" class="form-control" value="{{ old('services_projet') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="destinataires" class="form-label">Destinataires</label>
                            <textarea name="destinataires" id="destinataires" rows="2" class="form-control">{{ old('destinataires') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reference_dossier" class="form-label">Référence du dossier</label>
                                <input type="text" name="reference_dossier" id="reference_dossier" class="form-control" value="{{ old('reference_dossier') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_lancement" class="form-label">Date de lancement</label>
                                <input type="date" name="date_lancement" id="date_lancement" class="form-control" value="{{ old('date_lancement') }}">
                            </div>
                        </div>

                                                <!-- modals moved after the form to avoid nested forms -->

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="type_offre" class="form-label">Type d'offres</label>
                                <input type="text" name="type_offre" id="type_offre" class="form-control @error('type_offre') is-invalid @enderror" value="{{ old('type_offre') }}" placeholder="Ex: Offre technique, Offre financière">
                                @error('type_offre')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- removed AO reference and date as requested -->

                        <div class="form-group mb-3">
                            <label for="lots" class="form-label">Lots concernés</label>
                            <input type="text" class="form-control @error('lots') is-invalid @enderror" 
                                   id="lots" name="lots" placeholder="Ex: Lot 1, Lot 2, etc." value="{{ old('lots', old('lot')) }}">
                            @error('lots')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="titre_lot" class="form-label">Titre du lot</label>
                            <input type="text" name="titre_lot" id="titre_lot" class="form-control" value="{{ old('titre_lot') }}">
                        </div>

                        <!-- types_offres removed; use single free-text `type_offre` above -->

                        <div class="form-group mb-3">
                            <label for="autres_details" class="form-label">Autres détails</label>
                            <textarea name="autres_details" id="autres_details" rows="3" class="form-control">{{ old('autres_details') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mois_depot" class="form-label">Mois de dépôt</label>
                                <input type="text" name="mois_depot" id="mois_depot" class="form-control" value="{{ old('mois_depot') }}" placeholder="Ex: Septembre">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="annee_depot" class="form-label">Année de dépôt</label>
                                <input type="number" name="annee_depot" id="annee_depot" class="form-control" value="{{ old('annee_depot') }}" placeholder="2025">
                            </div>
                        </div>

                        <!-- Removed fields after année de dépôt as requested -->

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
                <!-- lookup modals (outside main form) -->
                <!-- Type d'offres modal removed; using free-text field instead -->

                <!-- Procedure / Autorite / Source modals removed as requested -->

                <script>
                document.addEventListener('DOMContentLoaded', function(){
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    async function postLookup(kind, nom){
                        const url = `{{ url('lookups') }}/${kind}`;
                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({nom})
                            });

                            const json = await res.json().catch(()=>({success:false,message:'Réponse invalide'}));
                            return {status: res.status, body: json};
                        } catch (e) {
                            return {status: 0, body: {success:false, message: 'Erreur réseau'}};
                        }
                    }

                    // Type d'offres modal removed — no handler needed

                    // Procedure/Autorite/Source handlers removed per request

                    // Helpers to show/clear errors under inputs
                    function showLookupError(inputId, message){
                        const input = document.getElementById(inputId);
                        if(!input) return;
                        input.classList.add('is-invalid');
                        let fb = input.nextElementSibling;
                        if(!fb || !fb.classList || !fb.classList.contains('invalid-feedback')){
                            fb = document.createElement('div');
                            fb.className = 'invalid-feedback d-block';
                            input.parentNode.insertBefore(fb, input.nextSibling);
                        }
                        fb.textContent = message;
                    }

                    function clearLookupError(inputId){
                        const input = document.getElementById(inputId);
                        if(!input) return;
                        input.classList.remove('is-invalid');
                        const fb = input.parentNode.querySelector('.invalid-feedback.d-block');
                        if(fb) fb.remove();
                    }
                });
                </script>

                @endsection
