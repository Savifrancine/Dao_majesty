@php
    $storedPersonnel = $personnelData ?? collect();
@endphp

<h5 class="mt-4 mb-3">Les personnels de l'entreprise</h5>
<p class="text-muted" style="font-size:13px;">Mêmes informations que le Formulaire PER : une fiche par CV, à ajouter ou retirer librement. Une même personne peut avoir plusieurs CV (un intitulé différent pour chacun) : le bon sera choisi dossier par dossier.</p>

<div id="personnelBlocks">
    @forelse($storedPersonnel as $p)
        @php
            $key = $p->id;
            $expValue = old("personnel.$key.experiences");
            $exps = $expValue ? (json_decode($expValue, true) ?: []) : ($p->experiences ?? []);
        @endphp
        <div class="personnel-block card mb-3" data-key="{{ $key }}">
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label">Intitulé du CV</label>
                    <input type="text" name="personnel[{{ $key }}][intitule]" value="{{ old("personnel.$key.intitule", $p->intitule) }}" class="form-control" placeholder="Ex : CV - Projets hospitaliers">
                </div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 gx-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nom</label>
                            <input type="text" name="personnel[{{ $key }}][nom]" value="{{ old("personnel.$key.nom", $p->nom) }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Poste</label>
                            <input type="text" name="personnel[{{ $key }}][poste]" value="{{ old("personnel.$key.poste", $p->poste) }}" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-personnel ms-2" style="margin-top:28px;">Supprimer</button>
                </div>

                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="personnel[{{ $key }}][date_naissance]" value="{{ old("personnel.$key.date_naissance", optional($p->date_naissance)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Qualifications professionnelles</label>
                        <textarea name="personnel[{{ $key }}][qualifications]" class="form-control" rows="2">{{ old("personnel.$key.qualifications", $p->qualifications) }}</textarea>
                    </div>
                </div>

                <div class="fw-bold mt-2 mb-2">Employeur actuel</div>
                <div class="row gx-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nom de l'employeur</label>
                        <input type="text" name="personnel[{{ $key }}][nom_employeur]" value="{{ old("personnel.$key.nom_employeur", $p->nom_employeur) }}" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Emploi tenu</label>
                        <input type="text" name="personnel[{{ $key }}][emploi_tenu]" value="{{ old("personnel.$key.emploi_tenu", $p->emploi_tenu) }}" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Adresse de l'employeur</label>
                    <textarea name="personnel[{{ $key }}][adresse_employeur]" class="form-control" rows="2">{{ old("personnel.$key.adresse_employeur", $p->adresse_employeur) }}</textarea>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="personnel[{{ $key }}][telephone]" value="{{ old("personnel.$key.telephone", $p->telephone) }}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Télécopie</label>
                        <input type="text" name="personnel[{{ $key }}][telecopie]" value="{{ old("personnel.$key.telecopie", $p->telecopie) }}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="personnel[{{ $key }}][email]" value="{{ old("personnel.$key.email", $p->email) }}" class="form-control">
                    </div>
                </div>
                <div class="row gx-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Contact (responsable / chargé du personnel)</label>
                        <input type="text" name="personnel[{{ $key }}][contact_personnel]" value="{{ old("personnel.$key.contact_personnel", $p->contact_personnel) }}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Nombre d'années avec cet employeur</label>
                        <input type="number" min="0" name="personnel[{{ $key }}][nombre_annees_employeur]" value="{{ old("personnel.$key.nombre_annees_employeur", $p->nombre_annees_employeur) }}" class="form-control">
                    </div>
                </div>

                <div class="fw-bold mt-2 mb-2">Expérience professionnelle des dix (10) dernières années</div>
                <div class="experiences-container d-flex flex-column gap-2">
                    @forelse($exps as $exp)
                        <div class="experience-row d-flex gap-2 flex-wrap">
                            <input type="text" class="form-control exp-de" placeholder="De (année)" value="{{ $exp['de'] ?? '' }}" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-a" placeholder="À (année)" value="{{ $exp['a'] ?? '' }}" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" value="{{ $exp['description'] ?? '' }}" style="flex:3; min-width:220px;">
                            <button type="button" class="btn btn-sm btn-danger remove-experience">Supprimer</button>
                        </div>
                    @empty
                        <div class="experience-row d-flex gap-2 flex-wrap">
                            <input type="text" class="form-control exp-de" placeholder="De (année)" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-a" placeholder="À (année)" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" style="flex:3; min-width:220px;">
                            <button type="button" class="btn btn-sm btn-danger remove-experience">Supprimer</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-experience mt-2">+ Ajouter une expérience</button>
                <input type="hidden" name="personnel[{{ $key }}][experiences]" class="experiences-input" value="{{ old("personnel.$key.experiences") }}">

                <div class="mt-3">
                    <label class="form-label">Pièces jointes (diplômes, certificats...)</label>
                    @if($p->fichiers->isNotEmpty())
                        <div class="mb-2">
                            @foreach($p->fichiers as $fichier)
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <a href="{{ asset('storage/' . $fichier->chemin_fichier) }}" target="_blank" rel="noopener noreferrer">{{ $fichier->nom_original ?: basename($fichier->chemin_fichier) }}</a>
                                    <label class="text-danger" style="font-size:0.85rem;">
                                        <input type="checkbox" name="personnel[{{ $key }}][delete_fichier_ids][]" value="{{ $fichier->id }}"> Supprimer
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <input type="file" name="personnel_fichiers[{{ $key }}][]" multiple class="form-control" style="max-width:420px;">
                </div>
            </div>
        </div>
    @empty
        <div class="personnel-block card mb-3" data-key="new_1">
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label">Intitulé du CV</label>
                    <input type="text" name="personnel[new_1][intitule]" class="form-control" placeholder="Ex : CV - Projets hospitaliers">
                </div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 gx-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nom</label>
                            <input type="text" name="personnel[new_1][nom]" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Poste</label>
                            <input type="text" name="personnel[new_1][poste]" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-personnel ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="personnel[new_1][date_naissance]" class="form-control">
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Qualifications professionnelles</label>
                        <textarea name="personnel[new_1][qualifications]" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="fw-bold mt-2 mb-2">Employeur actuel</div>
                <div class="row gx-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nom de l'employeur</label>
                        <input type="text" name="personnel[new_1][nom_employeur]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Emploi tenu</label>
                        <input type="text" name="personnel[new_1][emploi_tenu]" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Adresse de l'employeur</label>
                    <textarea name="personnel[new_1][adresse_employeur]" class="form-control" rows="2"></textarea>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="personnel[new_1][telephone]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Télécopie</label>
                        <input type="text" name="personnel[new_1][telecopie]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="personnel[new_1][email]" class="form-control">
                    </div>
                </div>
                <div class="row gx-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Contact (responsable / chargé du personnel)</label>
                        <input type="text" name="personnel[new_1][contact_personnel]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Nombre d'années avec cet employeur</label>
                        <input type="number" min="0" name="personnel[new_1][nombre_annees_employeur]" class="form-control">
                    </div>
                </div>
                <div class="fw-bold mt-2 mb-2">Expérience professionnelle des dix (10) dernières années</div>
                <div class="experiences-container d-flex flex-column gap-2">
                    <div class="experience-row d-flex gap-2 flex-wrap">
                        <input type="text" class="form-control exp-de" placeholder="De (année)" style="flex:1; min-width:100px;">
                        <input type="text" class="form-control exp-a" placeholder="À (année)" style="flex:1; min-width:100px;">
                        <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" style="flex:3; min-width:220px;">
                        <button type="button" class="btn btn-sm btn-danger remove-experience">Supprimer</button>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-experience mt-2">+ Ajouter une expérience</button>
                <input type="hidden" name="personnel[new_1][experiences]" class="experiences-input" value="">
                <div class="mt-3">
                    <label class="form-label">Pièces jointes (diplômes, certificats...)</label>
                    <input type="file" name="personnel_fichiers[new_1][]" multiple class="form-control" style="max-width:420px;">
                </div>
            </div>
        </div>
    @endforelse
</div>

<button type="button" id="addPersonnelBtn" class="btn btn-outline-secondary mb-4">+ Ajouter une personne / un CV</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('personnelBlocks');
    const addBtn = document.getElementById('addPersonnelBtn');
    let newCounter = container.querySelectorAll('.personnel-block').length + 1;

    function syncExperiencesInput(block) {
        const rows = block.querySelectorAll('.experience-row');
        const experiences = [];
        rows.forEach((row) => {
            const de = row.querySelector('.exp-de').value;
            const a = row.querySelector('.exp-a').value;
            const description = row.querySelector('.exp-description').value;
            if (de || a || description) {
                experiences.push({ de, a, description });
            }
        });
        block.querySelector('.experiences-input').value = JSON.stringify(experiences);
    }

    function createExperienceRow() {
        const row = document.createElement('div');
        row.className = 'experience-row d-flex gap-2 flex-wrap';
        row.innerHTML = `
            <input type="text" class="form-control exp-de" placeholder="De (année)" style="flex:1; min-width:100px;">
            <input type="text" class="form-control exp-a" placeholder="À (année)" style="flex:1; min-width:100px;">
            <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" style="flex:3; min-width:220px;">
            <button type="button" class="btn btn-sm btn-danger remove-experience">Supprimer</button>
        `;
        return row;
    }

    function attachBlockEvents(block) {
        syncExperiencesInput(block);

        block.querySelector('.add-experience').addEventListener('click', () => {
            block.querySelector('.experiences-container').appendChild(createExperienceRow());
        });

        block.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-experience')) {
                event.target.closest('.experience-row').remove();
                syncExperiencesInput(block);
            }
            if (event.target.classList.contains('remove-personnel')) {
                if (container.querySelectorAll('.personnel-block').length > 1) {
                    block.remove();
                } else {
                    block.querySelectorAll('input[type="text"], input[type="date"], input[type="email"], input[type="number"], textarea').forEach((f) => { f.value = ''; });
                    block.querySelectorAll('input[type="checkbox"]').forEach((f) => { f.checked = false; });
                    block.querySelector('.experiences-container').innerHTML = '';
                    block.querySelector('.experiences-container').appendChild(createExperienceRow());
                    syncExperiencesInput(block);
                }
            }
        });

        block.addEventListener('input', (event) => {
            if (event.target.classList.contains('exp-de') || event.target.classList.contains('exp-a') || event.target.classList.contains('exp-description')) {
                syncExperiencesInput(block);
            }
        });
    }

    function createBlock(key) {
        const block = document.createElement('div');
        block.className = 'personnel-block card mb-3';
        block.dataset.key = key;
        block.innerHTML = `
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label">Intitulé du CV</label>
                    <input type="text" name="personnel[${key}][intitule]" class="form-control" placeholder="Ex : CV - Projets hospitaliers">
                </div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 gx-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nom</label>
                            <input type="text" name="personnel[${key}][nom]" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Poste</label>
                            <input type="text" name="personnel[${key}][poste]" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-personnel ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="personnel[${key}][date_naissance]" class="form-control">
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Qualifications professionnelles</label>
                        <textarea name="personnel[${key}][qualifications]" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="fw-bold mt-2 mb-2">Employeur actuel</div>
                <div class="row gx-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nom de l'employeur</label>
                        <input type="text" name="personnel[${key}][nom_employeur]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Emploi tenu</label>
                        <input type="text" name="personnel[${key}][emploi_tenu]" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Adresse de l'employeur</label>
                    <textarea name="personnel[${key}][adresse_employeur]" class="form-control" rows="2"></textarea>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="personnel[${key}][telephone]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Télécopie</label>
                        <input type="text" name="personnel[${key}][telecopie]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="personnel[${key}][email]" class="form-control">
                    </div>
                </div>
                <div class="row gx-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Contact (responsable / chargé du personnel)</label>
                        <input type="text" name="personnel[${key}][contact_personnel]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Nombre d'années avec cet employeur</label>
                        <input type="number" min="0" name="personnel[${key}][nombre_annees_employeur]" class="form-control">
                    </div>
                </div>
                <div class="fw-bold mt-2 mb-2">Expérience professionnelle des dix (10) dernières années</div>
                <div class="experiences-container d-flex flex-column gap-2"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-experience mt-2">+ Ajouter une expérience</button>
                <input type="hidden" name="personnel[${key}][experiences]" class="experiences-input" value="">
                <div class="mt-3">
                    <label class="form-label">Pièces jointes (diplômes, certificats...)</label>
                    <input type="file" name="personnel_fichiers[${key}][]" multiple class="form-control" style="max-width:420px;">
                </div>
            </div>
        `;
        block.querySelector('.experiences-container').appendChild(createExperienceRow());
        return block;
    }

    container.querySelectorAll('.personnel-block').forEach(attachBlockEvents);

    addBtn.addEventListener('click', () => {
        newCounter += 1;
        const block = createBlock('new_' + newCounter);
        container.appendChild(block);
        attachBlockEvents(block);
    });

    document.querySelector('form')?.addEventListener('submit', () => {
        container.querySelectorAll('.personnel-block').forEach(syncExperiencesInput);
    });
});
</script>
