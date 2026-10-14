{{--
    Bloc reutilisable pour un type de document administratif "singleton" (un
    seul emplacement, plusieurs fichiers possibles - ex: plusieurs pages
    scannees) qui n'expire jamais, sur le meme principe que RCCM/IFU.
    Variables attendues : $doc (EntrepriseDocument|null), $requestKey, $label.
--}}
<div class="card mb-3">
    <div class="card-body">
        <div class="fw-bold mb-2">{{ $label }}</div>
        @if($doc && $doc->fichiers->isNotEmpty())
            <div class="mb-2">
                @foreach($doc->fichiers as $fichier)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                        <label class="text-danger" style="font-size:0.85rem;">
                            <input type="checkbox" name="documents_administratifs[{{ $requestKey }}][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        <input type="file" name="documents_administratifs[{{ $requestKey }}][fichiers][]" multiple class="form-control" style="max-width:420px;">
    </div>
</div>
