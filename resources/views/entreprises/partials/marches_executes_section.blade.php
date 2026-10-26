<?php /** @var \Illuminate\Support\Collection $marchesExecutes */ ?>
<h5 class="mt-4 mb-3">Nos marchés exécutés</h5>
<p class="text-muted" style="font-size:13px;">Les marchés déjà réalisés par l'entreprise : à saisir une seule fois ici, puis à importer en un clic depuis n'importe quel dossier pour pré-remplir le Formulaire EXP – 4.1 et le Formulaire de qualification sans tout retaper, avec le contrat/bon de commande et l'attestation de bonne fin de ce marché précis déjà attachés.</p>

<div id="marchesExecutesBlocks">
    @forelse($marchesExecutes as $m)
        <div class="marche-execute-block card mb-3" data-key="{{ $m->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 g-2">
                        <div class="col-md-3">
                            <label class="form-label">Mois/année départ</label>
                            <input type="month" name="marches_executes[{{ $m->id }}][mois_annee_depart]" value="{{ old("marches_executes.{$m->id}.mois_annee_depart", $m->mois_annee_depart) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mois/année final(e)</label>
                            <input type="month" name="marches_executes[{{ $m->id }}][mois_annee_fin]" value="{{ old("marches_executes.{$m->id}.mois_annee_fin", $m->mois_annee_fin) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Référence du marché</label>
                            <input type="text" name="marches_executes[{{ $m->id }}][reference_marche]" value="{{ old("marches_executes.{$m->id}.reference_marche", $m->reference_marche) }}" class="form-control" placeholder="Ex : 153/2023/PRMP-CNHU-PPC/SP-PRMP">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rôle du candidat</label>
                            <input type="text" name="marches_executes[{{ $m->id }}][role_candidat]" value="{{ old("marches_executes.{$m->id}.role_candidat", $m->role_candidat) }}" class="form-control" placeholder="Ex : Prestataire">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-marche-execute ms-2" style="margin-top:28px;">Supprimer</button>
                </div>

                <div class="mb-2">
                    <label class="form-label">Identification du marché</label>
                    <textarea name="marches_executes[{{ $m->id }}][identification_marche]" rows="3" class="form-control" placeholder="Ex : Nom du marché : ... - Brève description : ... - Autorité contractante : ... - Adresse : ...">{{ old("marches_executes.{$m->id}.identification_marche", $m->identification_marche) }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Contrat ou bon de commande</label>
                        @if($m->contratDocument && $m->contratDocument->fichiers->isNotEmpty())
                            <div class="mb-1">
                                @foreach($m->contratDocument->fichiers as $fichier)
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                        <label class="text-danger" style="font-size:0.85rem;">
                                            <input type="checkbox" name="marches_executes[{{ $m->id }}][delete_contrat_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <input type="file" name="marches_executes_contrat_fichiers[{{ $m->id }}][]" multiple class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Attestation de bonne fin</label>
                        @if($m->attestationDocument && $m->attestationDocument->fichiers->isNotEmpty())
                            <div class="mb-1">
                                @foreach($m->attestationDocument->fichiers as $fichier)
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                        <label class="text-danger" style="font-size:0.85rem;">
                                            <input type="checkbox" name="marches_executes[{{ $m->id }}][delete_attestation_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <input type="file" name="marches_executes_attestation_fichiers[{{ $m->id }}][]" multiple class="form-control">
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="marche-execute-block card mb-3" data-key="new_1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 g-2">
                        <div class="col-md-3">
                            <label class="form-label">Mois/année départ</label>
                            <input type="month" name="marches_executes[new_1][mois_annee_depart]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mois/année final(e)</label>
                            <input type="month" name="marches_executes[new_1][mois_annee_fin]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Référence du marché</label>
                            <input type="text" name="marches_executes[new_1][reference_marche]" class="form-control" placeholder="Ex : 153/2023/PRMP-CNHU-PPC/SP-PRMP">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rôle du candidat</label>
                            <input type="text" name="marches_executes[new_1][role_candidat]" class="form-control" placeholder="Ex : Prestataire">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-marche-execute ms-2" style="margin-top:28px;">Supprimer</button>
                </div>

                <div class="mb-2">
                    <label class="form-label">Identification du marché</label>
                    <textarea name="marches_executes[new_1][identification_marche]" rows="3" class="form-control" placeholder="Ex : Nom du marché : ... - Brève description : ... - Autorité contractante : ... - Adresse : ..."></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Contrat ou bon de commande</label>
                        <input type="file" name="marches_executes_contrat_fichiers[new_1][]" multiple class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Attestation de bonne fin</label>
                        <input type="file" name="marches_executes_attestation_fichiers[new_1][]" multiple class="form-control">
                    </div>
                </div>
            </div>
        </div>
    @endforelse
</div>

<button type="button" id="addMarcheExecuteBtn" class="btn btn-outline-secondary mb-4">+ Ajouter un marché exécuté</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('marchesExecutesBlocks');
    const addBtn = document.getElementById('addMarcheExecuteBtn');
    let newCounter = container.querySelectorAll('.marche-execute-block').length + 1;

    function attachBlockEvents(block) {
        block.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-marche-execute')) {
                block.remove();
            }
        });
    }

    container.querySelectorAll('.marche-execute-block').forEach(attachBlockEvents);

    addBtn.addEventListener('click', () => {
        newCounter += 1;
        const key = 'new_' + newCounter;
        const block = document.createElement('div');
        block.className = 'marche-execute-block card mb-3';
        block.dataset.key = key;
        block.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 g-2">
                        <div class="col-md-3">
                            <label class="form-label">Mois/année départ</label>
                            <input type="month" name="marches_executes[${key}][mois_annee_depart]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mois/année final(e)</label>
                            <input type="month" name="marches_executes[${key}][mois_annee_fin]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Référence du marché</label>
                            <input type="text" name="marches_executes[${key}][reference_marche]" class="form-control" placeholder="Ex : 153/2023/PRMP-CNHU-PPC/SP-PRMP">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rôle du candidat</label>
                            <input type="text" name="marches_executes[${key}][role_candidat]" class="form-control" placeholder="Ex : Prestataire">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-marche-execute ms-2" style="margin-top:28px;">Supprimer</button>
                </div>

                <div class="mb-2">
                    <label class="form-label">Identification du marché</label>
                    <textarea name="marches_executes[${key}][identification_marche]" rows="3" class="form-control" placeholder="Ex : Nom du marché : ... - Brève description : ... - Autorité contractante : ... - Adresse : ..."></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Contrat ou bon de commande</label>
                        <input type="file" name="marches_executes_contrat_fichiers[${key}][]" multiple class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Attestation de bonne fin</label>
                        <input type="file" name="marches_executes_attestation_fichiers[${key}][]" multiple class="form-control">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(block);
        attachBlockEvents(block);
    });
});
</script>
