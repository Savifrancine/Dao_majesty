{{--
    Bloc reutilisable pour un type de document administratif "repetable"
    (plusieurs emplacements distincts, un par renouvellement) dont la date
    d'expiration est calculee automatiquement depuis la date d'obtention.
    Variables attendues : $items, $requestKey, $fichiersKey, $label,
    $description, $containerId, $addBtnId, $blockClass, $removeClass,
    $expirationBadge (closure).
--}}
<div class="fw-bold mt-2 mb-2">{{ $label }}</div>
<p class="text-muted" style="font-size:13px;">{{ $description }}</p>

<div id="{{ $containerId }}">
    @forelse($items as $doc)
        <div class="{{ $blockClass }} card mb-2" data-key="{{ $doc->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:220px;">
                        <label class="form-label">Date d'obtention</label>
                        <input type="date" name="{{ $requestKey }}[{{ $doc->id }}][date_delivrance]" value="{{ old("$requestKey.{$doc->id}.date_delivrance", optional($doc->date_delivrance)->format('Y-m-d')) }}" class="form-control">
                        <div class="mt-1">{!! $expirationBadge($doc) !!}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger {{ $removeClass }} ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                @if($doc->fichiers->isNotEmpty())
                    <div class="mb-2">
                        @foreach($doc->fichiers as $fichier)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                <label class="text-danger" style="font-size:0.85rem;">
                                    <input type="checkbox" name="{{ $requestKey }}[{{ $doc->id }}][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="{{ $fichiersKey }}[{{ $doc->id }}][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @empty
        <div class="{{ $blockClass }} card mb-2" data-key="new_1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:220px;">
                        <label class="form-label">Date d'obtention</label>
                        <input type="date" name="{{ $requestKey }}[new_1][date_delivrance]" class="form-control">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger {{ $removeClass }} ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <input type="file" name="{{ $fichiersKey }}[new_1][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @endforelse
</div>

<button type="button" id="{{ $addBtnId }}" class="btn btn-outline-secondary mb-4">+ Ajouter un renouvellement</button>
