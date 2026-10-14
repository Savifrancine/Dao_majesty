{{--
    Bloc reutilisable pour un type de document administratif "repetable" par
    intitule libre (pas de date d'expiration, pas d'annee) - ex: RIB (intitule
    = nom de la banque), Attestation de bonne fin, Bon de commande et
    contrats. Variables attendues : $items, $requestKey, $fichiersKey,
    $label, $description, $containerId, $addBtnId, $blockClass, $removeClass,
    $fieldName (ex: 'banque' ou 'intitule'), $fieldLabel, $fieldPlaceholder,
    $addBtnLabel.
--}}
<div class="fw-bold mt-2 mb-2">{{ $label }}</div>
<p class="text-muted" style="font-size:13px;">{{ $description }}</p>

<div id="{{ $containerId }}">
    @forelse($items as $item)
        <div class="{{ $blockClass }} card mb-2" data-key="{{ $item->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:260px;">
                        <label class="form-label">{{ $fieldLabel }}</label>
                        <input type="text" name="{{ $requestKey }}[{{ $item->id }}][{{ $fieldName }}]" value="{{ old("$requestKey.{$item->id}.$fieldName", $item->{$fieldName}) }}" class="form-control" placeholder="{{ $fieldPlaceholder }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger {{ $removeClass }} ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                @if($item->fichiers->isNotEmpty())
                    <div class="mb-2">
                        @foreach($item->fichiers as $fichier)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                <label class="text-danger" style="font-size:0.85rem;">
                                    <input type="checkbox" name="{{ $requestKey }}[{{ $item->id }}][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="{{ $fichiersKey }}[{{ $item->id }}][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @empty
        <div class="{{ $blockClass }} card mb-2" data-key="new_1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:260px;">
                        <label class="form-label">{{ $fieldLabel }}</label>
                        <input type="text" name="{{ $requestKey }}[new_1][{{ $fieldName }}]" class="form-control" placeholder="{{ $fieldPlaceholder }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger {{ $removeClass }} ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <input type="file" name="{{ $fichiersKey }}[new_1][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @endforelse
</div>

<button type="button" id="{{ $addBtnId }}" class="btn btn-outline-secondary mb-4">{{ $addBtnLabel }}</button>
