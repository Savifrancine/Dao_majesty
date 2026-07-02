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
