@php
    $articlesRaw = \App\Support\FicheTechniqueArticles::forDossier($dossier);
    $articles = array_map(fn ($a) => $a['designation'], $articlesRaw);

    $existingFiches = $dossierDocument
        ? \App\Models\FicheTechniqueFichier::where('dossier_document_id', $dossierDocument->id)->get()->keyBy('article_index')
        : collect();
@endphp

<div class="wizard-field" style="margin-bottom: 12px;">
    <p class="wizard-label">Fiche technique de chaque article, délivrée par le fabricant</p>
    <p style="font-size:0.95rem;color:#475569;">Le prospectus/catalogue du fabricant pour chaque article du bordereau déjà rempli du dossier (« Description technique des fournitures/services », ou à défaut tout autre bordereau renseigné, ex: « Bordereau prix unitaire »). Un article peut rester sans prospectus si ce n'est pas exigé. Vous pouvez les ajouter dans n'importe quel ordre : dans le PDF final, ils apparaîtront dans l'ordre des articles, chacun précédé d'une page de séparation.</p>
</div>

@if(empty($articles))
    <div class="wizard-alert" style="margin-bottom:12px;">
        Ce dossier n'a pas encore de tableau de bordereau (Description technique, Bordereau prix unitaire, Liste de Matériel...) rempli avec au moins un article. Ajoutez d'abord des articles à l'un de ces tableaux, puis revenez ici pour joindre leurs fiches techniques.
    </div>
@else
    <div style="border:1px solid #d1d5db; padding:16px; background:#ffffff;">
        @foreach($articles as $index => $designation)
            @php $existing = $existingFiches->get($index); @endphp
            <div style="display:flex; align-items:flex-start; gap:14px; padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid #e5e7eb;' : '' }}">
                <div style="flex:0 0 40px; font-weight:700; color:#0b63b5;">#{{ $index + 1 }}</div>
                <div style="flex:1;">
                    <div style="font-weight:600; margin-bottom:6px;">{{ $designation !== '' ? $designation : ('Article n°' . ($index + 1)) }}</div>

                    @if($existing)
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:6px;">
                            <a href="{{ asset('storage/' . $existing->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $existing->nom_original ?: basename($existing->chemin_fichier) }}</a>
                            <label style="font-size:0.85rem; color:#b91c1c;">
                                <input type="checkbox" name="delete_fiche_technique_indexes[]" value="{{ $index }}"> Supprimer
                            </label>
                        </div>
                        <small style="color:#64748b;">Remplacer par un autre fichier :</small>
                    @endif

                    <input type="file" name="fiche_technique_fichiers[{{ $index }}]" class="form-control wizard-input" accept=".pdf,.png,.jpg,.jpeg,.gif" style="margin-top:4px;">
                </div>
            </div>
        @endforeach
    </div>
@endif
