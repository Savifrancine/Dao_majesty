<div class="mb-3">
    <label for="categorie" class="form-label">Catégorie</label>
    <input id="categorie" name="categorie" class="form-control @error('categorie') is-invalid @enderror" value="{{ old('categorie', $catalogueArticle->categorie ?? '') }}">
    @error('categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="designation" class="form-label">Désignation / nom</label>
    <input id="designation" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $catalogueArticle->designation ?? '') }}" required>
    @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <small class="form-text text-muted">Doit correspondre exactement au nom de l'article dans la "Description technique des fournitures/services" d'un dossier pour que le prospectus soit ramené automatiquement.</small>
</div>

<div class="mb-3">
    <label for="marque_type" class="form-label">Marque / type</label>
    <input id="marque_type" name="marque_type" class="form-control @error('marque_type') is-invalid @enderror" value="{{ old('marque_type', $catalogueArticle->marque_type ?? '') }}">
    @error('marque_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="commentaire" class="form-label">Commentaire</label>
    <textarea id="commentaire" name="commentaire" rows="3" class="form-control @error('commentaire') is-invalid @enderror" placeholder="Pour aider à faire les recherches">{{ old('commentaire', $catalogueArticle->commentaire ?? '') }}</textarea>
    @error('commentaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="prospectus" class="form-label">Le prospectus (pièce jointe)</label>

    @if(!empty($catalogueArticle->chemin_prospectus ?? null))
        <div class="mb-2 d-flex align-items-center" style="gap:10px;">
            <a href="{{ asset('storage/' . $catalogueArticle->chemin_prospectus) }}" target="_blank" rel="noopener noreferrer">{{ $catalogueArticle->nom_original_prospectus ?: basename($catalogueArticle->chemin_prospectus) }}</a>
            <label class="form-check-label text-danger" style="font-size:0.85rem;">
                <input type="checkbox" name="delete_prospectus" value="1" class="form-check-input"> Supprimer
            </label>
        </div>
        <small class="form-text text-muted d-block mb-1">Remplacer par un autre fichier :</small>
    @endif

    <input type="file" id="prospectus" name="prospectus" class="form-control @error('prospectus') is-invalid @enderror" accept=".pdf,.png,.jpg,.jpeg,.gif">
    @error('prospectus') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
