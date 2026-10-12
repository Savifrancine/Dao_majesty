<?php /** @var \Illuminate\Support\Collection $entrepriseDocuments */ ?>
@php
    $allDocs = $entrepriseDocuments ?? collect();
    $rccmDoc = $allDocs->firstWhere('type', 'rccm');
    $ifuDoc = $allDocs->firstWhere('type', 'ifu');
    $etatsFinanciers = $allDocs->where('type', 'etat_financier')->sortByDesc('annee')->values();
    $nonExclusions = $allDocs->where('type', 'non_exclusion')->sortByDesc('date_delivrance')->values();
    $categorisations = $allDocs->where('type', 'categorisation')->sortByDesc('date_delivrance')->values();
    $regularitesFiscales = $allDocs->where('type', 'regularite_fiscale')->sortByDesc('date_delivrance')->values();
    $cnss = $allDocs->where('type', 'cnss')->sortByDesc('date_delivrance')->values();
    $nonFaillites = $allDocs->where('type', 'non_faillite')->sortByDesc('date_delivrance')->values();
    $ribs = $allDocs->where('type', 'rib')->sortBy('banque')->values();

    $expirationBadge = function ($doc) {
        if (!$doc->date_expiration) {
            return '';
        }
        if ($doc->estExpire()) {
            return '<span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.78rem;font-weight:600;color:#991b1b;background:#fee2e2;">⚠ Expiré le ' . $doc->date_expiration->format('d/m/Y') . '</span>';
        }
        if ($doc->expireBientot()) {
            return '<span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.78rem;font-weight:600;color:#9a3412;background:#ffedd5;">⚠ Expire le ' . $doc->date_expiration->format('d/m/Y') . '</span>';
        }
        return '<span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.78rem;font-weight:600;color:#166534;background:#dcfce7;">Valide jusqu\'au ' . $doc->date_expiration->format('d/m/Y') . '</span>';
    };
@endphp

<h5 class="mt-4 mb-3">Documents administratifs de l'entreprise</h5>
<p class="text-muted" style="font-size:13px;">RCCM, IFU, états financiers, et les attestations qui se renouvellent : à téléverser une seule fois ici, puis à choisir directement dans chaque dossier sans avoir à les re-téléverser.</p>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-bold mb-2">RCCM</div>
        @if($rccmDoc && $rccmDoc->fichiers->isNotEmpty())
            <div class="mb-2">
                @foreach($rccmDoc->fichiers as $fichier)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                        <label class="text-danger" style="font-size:0.85rem;">
                            <input type="checkbox" name="documents_administratifs[rccm][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        <input type="file" name="documents_administratifs[rccm][fichiers][]" multiple class="form-control" style="max-width:420px;">
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-bold mb-2">IFU</div>
        @if($ifuDoc && $ifuDoc->fichiers->isNotEmpty())
            <div class="mb-2">
                @foreach($ifuDoc->fichiers as $fichier)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                        <label class="text-danger" style="font-size:0.85rem;">
                            <input type="checkbox" name="documents_administratifs[ifu][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        <input type="file" name="documents_administratifs[ifu][fichiers][]" multiple class="form-control" style="max-width:420px;">
    </div>
</div>

<div class="fw-bold mt-2 mb-2">Relevé d'identité bancaire (RIB)</div>
<p class="text-muted" style="font-size:13px;">Ne change pas, comme l'IFU — mais une entreprise peut avoir plusieurs RIB (une banque différente pour chacun) : ajoutez un bloc par banque, le bon sera choisi dossier par dossier.</p>

<div id="ribBlocks">
    @forelse($ribs as $rib)
        <div class="rib-block card mb-2" data-key="{{ $rib->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:260px;">
                        <label class="form-label">Nom de la banque</label>
                        <input type="text" name="rib[{{ $rib->id }}][banque]" value="{{ old("rib.{$rib->id}.banque", $rib->banque) }}" class="form-control" placeholder="Ex : Ecobank">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-rib ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                @if($rib->fichiers->isNotEmpty())
                    <div class="mb-2">
                        @foreach($rib->fichiers as $fichier)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                <label class="text-danger" style="font-size:0.85rem;">
                                    <input type="checkbox" name="rib[{{ $rib->id }}][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="rib_fichiers[{{ $rib->id }}][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @empty
        <div class="rib-block card mb-2" data-key="new_1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:260px;">
                        <label class="form-label">Nom de la banque</label>
                        <input type="text" name="rib[new_1][banque]" class="form-control" placeholder="Ex : Ecobank">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-rib ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <input type="file" name="rib_fichiers[new_1][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @endforelse
</div>

<button type="button" id="addRibBtn" class="btn btn-outline-secondary mb-4">+ Ajouter une banque</button>

<div class="fw-bold mt-2 mb-2">États financiers</div>
<p class="text-muted" style="font-size:13px;">Souvent demandés sur les 3 dernières années : ajoutez un bloc par exercice.</p>

<div id="etatsFinanciersBlocks">
    @forelse($etatsFinanciers as $ef)
        <div class="etat-financier-block card mb-2" data-key="{{ $ef->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:160px;">
                        <label class="form-label">Année</label>
                        <input type="number" name="etats_financiers[{{ $ef->id }}][annee]" value="{{ old("etats_financiers.{$ef->id}.annee", $ef->annee) }}" class="form-control" min="1900" max="2100">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-etat-financier ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                @if($ef->fichiers->isNotEmpty())
                    <div class="mb-2">
                        @foreach($ef->fichiers as $fichier)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                <label class="text-danger" style="font-size:0.85rem;">
                                    <input type="checkbox" name="etats_financiers[{{ $ef->id }}][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="etats_financiers_fichiers[{{ $ef->id }}][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @empty
        <div class="etat-financier-block card mb-2" data-key="new_1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:160px;">
                        <label class="form-label">Année</label>
                        <input type="number" name="etats_financiers[new_1][annee]" class="form-control" min="1900" max="2100">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-etat-financier ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <input type="file" name="etats_financiers_fichiers[new_1][]" multiple class="form-control" style="max-width:420px;">
            </div>
        </div>
    @endforelse
</div>

<button type="button" id="addEtatFinancierBtn" class="btn btn-outline-secondary mb-4">+ Ajouter une année</button>

@include('entreprises.partials._repeatable_expiring_document_block', [
    'items' => $nonExclusions,
    'requestKey' => 'non_exclusion',
    'fichiersKey' => 'non_exclusion_fichiers',
    'label' => "Attestation de non-exclusion de la commande publique",
    'description' => "Valable 1 an : indiquez la date à laquelle elle a été obtenue, la plateforme calcule elle-même la date d'expiration et vous prévient quand elle approche ou est dépassée. Ajoutez un nouveau bloc à chaque renouvellement, l'ancien reste disponible pour l'historique.",
    'containerId' => 'nonExclusionBlocks',
    'addBtnId' => 'addNonExclusionBtn',
    'blockClass' => 'non-exclusion-block',
    'removeClass' => 'remove-non-exclusion',
    'expirationBadge' => $expirationBadge,
])

@include('entreprises.partials._repeatable_expiring_document_block', [
    'items' => $categorisations,
    'requestKey' => 'categorisation',
    'fichiersKey' => 'categorisation_fichiers',
    'label' => "Attestation de catégorisation",
    'description' => "Valable 5 ans : indiquez la date à laquelle elle a été obtenue, la plateforme calcule elle-même la date d'expiration et vous prévient quand elle approche ou est dépassée. Ajoutez un nouveau bloc à chaque renouvellement, l'ancien reste disponible pour l'historique.",
    'containerId' => 'categorisationBlocks',
    'addBtnId' => 'addCategorisationBtn',
    'blockClass' => 'categorisation-block',
    'removeClass' => 'remove-categorisation',
    'expirationBadge' => $expirationBadge,
])

@include('entreprises.partials._repeatable_expiring_document_block', [
    'items' => $regularitesFiscales,
    'requestKey' => 'regularite_fiscale',
    'fichiersKey' => 'regularite_fiscale_fichiers',
    'label' => "Attestation de régularité fiscale",
    'description' => "Valable 3 mois : indiquez la date à laquelle elle a été obtenue, la plateforme calcule elle-même la date d'expiration et vous prévient quand elle approche ou est dépassée. Ajoutez un nouveau bloc à chaque renouvellement, l'ancien reste disponible pour l'historique.",
    'containerId' => 'regulariteFiscaleBlocks',
    'addBtnId' => 'addRegulariteFiscaleBtn',
    'blockClass' => 'regularite-fiscale-block',
    'removeClass' => 'remove-regularite-fiscale',
    'expirationBadge' => $expirationBadge,
])

@include('entreprises.partials._repeatable_expiring_document_block', [
    'items' => $cnss,
    'requestKey' => 'cnss',
    'fichiersKey' => 'cnss_fichiers',
    'label' => "Attestation de régularité à la CNSS",
    'description' => "Valable 3 mois : indiquez la date à laquelle elle a été obtenue, la plateforme calcule elle-même la date d'expiration et vous prévient quand elle approche ou est dépassée. Ajoutez un nouveau bloc à chaque renouvellement, l'ancien reste disponible pour l'historique.",
    'containerId' => 'cnssBlocks',
    'addBtnId' => 'addCnssBtn',
    'blockClass' => 'cnss-block',
    'removeClass' => 'remove-cnss',
    'expirationBadge' => $expirationBadge,
])

@include('entreprises.partials._repeatable_expiring_document_block', [
    'items' => $nonFaillites,
    'requestKey' => 'non_faillite',
    'fichiersKey' => 'non_faillite_fichiers',
    'label' => "Attestation de non-faillite",
    'description' => "Valable 3 mois : indiquez la date à laquelle elle a été obtenue, la plateforme calcule elle-même la date d'expiration et vous prévient quand elle approche ou est dépassée. Ajoutez un nouveau bloc à chaque renouvellement, l'ancien reste disponible pour l'historique.",
    'containerId' => 'nonFailliteBlocks',
    'addBtnId' => 'addNonFailliteBtn',
    'blockClass' => 'non-faillite-block',
    'removeClass' => 'remove-non-faillite',
    'expirationBadge' => $expirationBadge,
])

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupRepeatableBlocks(containerId, addBtnId, blockClass, removeClass, buildHtml) {
        const container = document.getElementById(containerId);
        const addBtn = document.getElementById(addBtnId);
        let newCounter = container.querySelectorAll('.' + blockClass).length + 1;

        function attachBlockEvents(block) {
            block.addEventListener('click', (event) => {
                if (event.target.classList.contains(removeClass)) {
                    block.remove();
                }
            });
        }

        container.querySelectorAll('.' + blockClass).forEach(attachBlockEvents);

        addBtn.addEventListener('click', () => {
            newCounter += 1;
            const key = 'new_' + newCounter;
            const block = document.createElement('div');
            block.className = blockClass + ' card mb-2';
            block.dataset.key = key;
            block.innerHTML = buildHtml(key);
            container.appendChild(block);
            attachBlockEvents(block);
        });
    }

    setupRepeatableBlocks('ribBlocks', 'addRibBtn', 'rib-block', 'remove-rib', (key) => `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="mb-0" style="max-width:260px;">
                    <label class="form-label">Nom de la banque</label>
                    <input type="text" name="rib[${key}][banque]" class="form-control" placeholder="Ex : Ecobank">
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-rib ms-2" style="margin-top:28px;">Supprimer</button>
            </div>
            <input type="file" name="rib_fichiers[${key}][]" multiple class="form-control" style="max-width:420px;">
        </div>
    `);

    setupRepeatableBlocks('etatsFinanciersBlocks', 'addEtatFinancierBtn', 'etat-financier-block', 'remove-etat-financier', (key) => `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="mb-0" style="max-width:160px;">
                    <label class="form-label">Année</label>
                    <input type="number" name="etats_financiers[${key}][annee]" class="form-control" min="1900" max="2100">
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-etat-financier ms-2" style="margin-top:28px;">Supprimer</button>
            </div>
            <input type="file" name="etats_financiers_fichiers[${key}][]" multiple class="form-control" style="max-width:420px;">
        </div>
    `);

    function buildDateDelivranceHtml(requestKey, fichiersKey, removeClass) {
        return (key) => `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="mb-0" style="max-width:220px;">
                        <label class="form-label">Date d'obtention</label>
                        <input type="date" name="${requestKey}[${key}][date_delivrance]" class="form-control">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger ${removeClass} ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <input type="file" name="${fichiersKey}[${key}][]" multiple class="form-control" style="max-width:420px;">
            </div>
        `;
    }

    setupRepeatableBlocks('nonExclusionBlocks', 'addNonExclusionBtn', 'non-exclusion-block', 'remove-non-exclusion', buildDateDelivranceHtml('non_exclusion', 'non_exclusion_fichiers', 'remove-non-exclusion'));
    setupRepeatableBlocks('categorisationBlocks', 'addCategorisationBtn', 'categorisation-block', 'remove-categorisation', buildDateDelivranceHtml('categorisation', 'categorisation_fichiers', 'remove-categorisation'));
    setupRepeatableBlocks('regulariteFiscaleBlocks', 'addRegulariteFiscaleBtn', 'regularite-fiscale-block', 'remove-regularite-fiscale', buildDateDelivranceHtml('regularite_fiscale', 'regularite_fiscale_fichiers', 'remove-regularite-fiscale'));
    setupRepeatableBlocks('cnssBlocks', 'addCnssBtn', 'cnss-block', 'remove-cnss', buildDateDelivranceHtml('cnss', 'cnss_fichiers', 'remove-cnss'));
    setupRepeatableBlocks('nonFailliteBlocks', 'addNonFailliteBtn', 'non-faillite-block', 'remove-non-faillite', buildDateDelivranceHtml('non_faillite', 'non_faillite_fichiers', 'remove-non-faillite'));
});
</script>
