<form action="{{ $action }}" method="POST">
    @csrf

    @if(in_array($method, ['PUT', 'PATCH']))
        @method($method)
    @endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Pièce de matériel</label>
        <input type="text" name="piece_materiel" class="form-control @error('piece_materiel') is-invalid @enderror" value="{{ old('piece_materiel', $formulaireMat->piece_materiel ?? '') }}" required>
        @error('piece_materiel')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Nom du fabricant</label>
        <input type="text" name="fabricant" class="form-control @error('fabricant') is-invalid @enderror" value="{{ old('fabricant', $formulaireMat->fabricant ?? '') }}">
        @error('fabricant')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Modèle et puissance</label>
        <input type="text" name="modele_puissance" class="form-control @error('modele_puissance') is-invalid @enderror" value="{{ old('modele_puissance', $formulaireMat->modele_puissance ?? '') }}">
        @error('modele_puissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Capacité / série</label>
        <input type="text" name="capacite" class="form-control @error('capacite') is-invalid @enderror" value="{{ old('capacite', $formulaireMat->capacite ?? '') }}">
        @error('capacite')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Année de fabrication</label>
        <input type="text" name="annee_fabrication" class="form-control @error('annee_fabrication') is-invalid @enderror" value="{{ old('annee_fabrication', $formulaireMat->annee_fabrication ?? '') }}">
        @error('annee_fabrication')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Localisation présente</label>
        <input type="text" name="localisation" class="form-control @error('localisation') is-invalid @enderror" value="{{ old('localisation', $formulaireMat->localisation ?? '') }}">
        @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Provenance</label>
        <select name="provenance" class="form-select @error('provenance') is-invalid @enderror">
            <option value="">Sélectionner</option>
            <option value="en_possession" {{ old('provenance', $formulaireMat->provenance ?? '') === 'en_possession' ? 'selected' : '' }}>En possession</option>
            <option value="en_location" {{ old('provenance', $formulaireMat->provenance ?? '') === 'en_location' ? 'selected' : '' }}>En location</option>
            <option value="en_location_vente" {{ old('provenance', $formulaireMat->provenance ?? '') === 'en_location_vente' ? 'selected' : '' }}>En location-vente</option>
            <option value="fabrique_specialement" {{ old('provenance', $formulaireMat->provenance ?? '') === 'fabrique_specialement' ? 'selected' : '' }}>Fabriqué spécialement</option>
        </select>
        @error('provenance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @php $provenanceValue = old('provenance', $formulaireMat->provenance ?? ''); @endphp
    <div class="col-12 mat-proprietaire-block" style="{{ in_array($provenanceValue, ['en_location', 'en_location_vente', 'fabrique_specialement'], true) ? '' : 'display:none;' }}">
        <div class="row g-3 p-3" style="background:#f9fafb; border:1px dashed #cbd5e1; border-radius:6px;">
            <div class="col-12" style="font-size:12px; color:#6b7280;">Ces renseignements sont omis si le matériel est « en possession » du candidat.</div>
            <div class="col-md-6">
                <label class="form-label">Nom du Propriétaire</label>
                <input type="text" name="nom_proprietaire" class="form-control @error('nom_proprietaire') is-invalid @enderror" value="{{ old('nom_proprietaire', $formulaireMat->nom_proprietaire ?? '') }}">
                @error('nom_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Adresse du Propriétaire</label>
                <input type="text" name="adresse_proprietaire" class="form-control @error('adresse_proprietaire') is-invalid @enderror" value="{{ old('adresse_proprietaire', $formulaireMat->adresse_proprietaire ?? '') }}">
                @error('adresse_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone_proprietaire" class="form-control @error('telephone_proprietaire') is-invalid @enderror" value="{{ old('telephone_proprietaire', $formulaireMat->telephone_proprietaire ?? '') }}">
                @error('telephone_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Nom et titre de la personne à contacter</label>
                <input type="text" name="contact_nom_titre" class="form-control @error('contact_nom_titre') is-invalid @enderror" value="{{ old('contact_nom_titre', $formulaireMat->contact_nom_titre ?? '') }}">
                @error('contact_nom_titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Télécopie</label>
                <input type="text" name="telecopie_proprietaire" class="form-control @error('telecopie_proprietaire') is-invalid @enderror" value="{{ old('telecopie_proprietaire', $formulaireMat->telecopie_proprietaire ?? '') }}">
                @error('telecopie_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Télex</label>
                <input type="text" name="telex_proprietaire" class="form-control @error('telex_proprietaire') is-invalid @enderror" value="{{ old('telex_proprietaire', $formulaireMat->telex_proprietaire ?? '') }}">
                @error('telex_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Détails de la location / location-vente / accord de fabrication</label>
                <textarea name="accords_details" rows="3" class="form-control @error('accords_details') is-invalid @enderror">{{ old('accords_details', $formulaireMat->accords_details ?? '') }}</textarea>
                @error('accords_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Détails sur les engagements courants</label>
        <textarea name="engagements" rows="4" class="form-control @error('engagements') is-invalid @enderror">{{ old('engagements', $formulaireMat->engagements ?? '') }}</textarea>
        @error('engagements')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Signataire</label>
        <select name="signataire_id" class="form-select @error('signataire_id') is-invalid @enderror">
            <option value="">Sélectionner un signataire</option>
            @foreach($signataires as $signataire)
                <option value="{{ $signataire->id }}" {{ old('signataire_id', $formulaireMat->signataire_id ?? '') == $signataire->id ? 'selected' : '' }}>
                    {{ $signataire->nom }} {{ $signataire->prenom }}{{ $signataire->fonction ? ' – ' . $signataire->fonction : '' }}
                </option>
            @endforeach
        </select>
        @error('signataire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Fait à</label>
        <input type="text" name="lieu_fait" class="form-control @error('lieu_fait') is-invalid @enderror" value="{{ old('lieu_fait', $formulaireMat->lieu_fait ?? '') }}">
        @error('lieu_fait')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date" name="date_fait" class="form-control @error('date_fait') is-invalid @enderror" value="{{ old('date_fait', optional($formulaireMat)->date_fait ? $formulaireMat->date_fait->format('Y-m-d') : '') }}">
        @error('date_fait')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    </div>

    <div class="mt-4" style="display:flex;gap:12px;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">{{ $buttonLabel }}</button>
        <a href="{{ route('formulaire_mat.index') }}" class="btn btn-secondary">Annuler</a>
    </div>
</form>

<script>
    (function () {
        var select = document.querySelector('select[name="provenance"]');
        var block = document.querySelector('.mat-proprietaire-block');
        if (!select || !block) return;
        function toggle() {
            block.style.display = ['en_location', 'en_location_vente', 'fabrique_specialement'].includes(select.value) ? '' : 'none';
        }
        select.addEventListener('change', toggle);
    })();
</script>
