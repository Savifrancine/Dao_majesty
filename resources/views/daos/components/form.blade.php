{{-- Formulaire réutilisable pour créer/éditer un DAO --}}

<form action="{{ $action }}" method="POST" style="padding: 20px;">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label for="nom" class="form-label">Nom du DAO <span style="color: #ef4444;">*</span></label>
        <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $dao->nom ?? '') }}" required>
        @error('nom')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="mb-4">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $dao->description ?? '') }}</textarea>
        @error('description')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $dao->email ?? '') }}">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="col-md-6 mb-4">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $dao->telephone ?? '') }}">
            @error('telephone')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <label for="adresse" class="form-label">Adresse</label>
        <input type="text" class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" value="{{ old('adresse', $dao->adresse ?? '') }}">
        @error('adresse')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <label for="ville" class="form-label">Ville</label>
            <input type="text" class="form-control @error('ville') is-invalid @enderror" id="ville" name="ville" value="{{ old('ville', $dao->ville ?? '') }}">
            @error('ville')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="col-md-6 mb-4">
            <label for="code_postal" class="form-label">Code Postal</label>
            <input type="text" class="form-control @error('code_postal') is-invalid @enderror" id="code_postal" name="code_postal" value="{{ old('code_postal', $dao->code_postal ?? '') }}">
            @error('code_postal')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <div class="form-check" style="display: flex; align-items: center; gap: 12px;">
            <input type="checkbox" class="form-check-input" id="actif" name="actif" value="1" {{ old('actif', $dao->actif ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="actif" style="cursor: pointer; margin: 0;">
                Marquer comme actif
            </label>
        </div>
    </div>

    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
        <button type="submit" class="btn btn-primary">
            {{ $buttonLabel }}
        </button>
        <a href="{{ $cancelUrl }}" class="btn btn-secondary">
            Annuler
        </a>
    </div>
</form>
