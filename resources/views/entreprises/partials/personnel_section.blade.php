@php
    $storedPersonnel = old('personnel', $personnelData ?? []);
    if (empty($storedPersonnel)) {
        $storedPersonnel = [
            ['nom' => '', 'poste' => '', 'experiences' => []],
        ];
    }
@endphp

<h5 class="mt-4 mb-3">Les personnels de l'entreprise</h5>
<p class="text-muted" style="font-size:13px;">Mêmes informations que le Formulaire PER : une fiche par personne, à ajouter ou retirer librement.</p>

<div id="personnelBlocks">
    @foreach($storedPersonnel as $index => $p)
        @php
            $expValue = old("personnel.$index.experiences");
            $exps = $expValue ? (json_decode($expValue, true) ?: []) : ($p['experiences'] ?? []);
        @endphp
        <div class="personnel-block card mb-3" data-index="{{ $index }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 gx-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nom</label>
                            <input type="text" name="personnel[{{ $index }}][nom]" value="{{ old("personnel.$index.nom", $p['nom'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Poste</label>
                            <input type="text" name="personnel[{{ $index }}][poste]" value="{{ old("personnel.$index.poste", $p['poste'] ?? '') }}" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-personnel ms-2" style="margin-top:28px;">Supprimer</button>
                </div>

                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="personnel[{{ $index }}][date_naissance]" value="{{ old("personnel.$index.date_naissance", $p['date_naissance'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Qualifications professionnelles</label>
                        <textarea name="personnel[{{ $index }}][qualifications]" class="form-control" rows="2">{{ old("personnel.$index.qualifications", $p['qualifications'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="fw-bold mt-2 mb-2">Employeur actuel</div>
                <div class="row gx-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nom de l'employeur</label>
                        <input type="text" name="personnel[{{ $index }}][nom_employeur]" value="{{ old("personnel.$index.nom_employeur", $p['nom_employeur'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Emploi tenu</label>
                        <input type="text" name="personnel[{{ $index }}][emploi_tenu]" value="{{ old("personnel.$index.emploi_tenu", $p['emploi_tenu'] ?? '') }}" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Adresse de l'employeur</label>
                    <textarea name="personnel[{{ $index }}][adresse_employeur]" class="form-control" rows="2">{{ old("personnel.$index.adresse_employeur", $p['adresse_employeur'] ?? '') }}</textarea>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="personnel[{{ $index }}][telephone]" value="{{ old("personnel.$index.telephone", $p['telephone'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Télécopie</label>
                        <input type="text" name="personnel[{{ $index }}][telecopie]" value="{{ old("personnel.$index.telecopie", $p['telecopie'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="personnel[{{ $index }}][email]" value="{{ old("personnel.$index.email", $p['email'] ?? '') }}" class="form-control">
                    </div>
                </div>
                <div class="row gx-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Contact (responsable / chargé du personnel)</label>
                        <input type="text" name="personnel[{{ $index }}][contact_personnel]" value="{{ old("personnel.$index.contact_personnel", $p['contact_personnel'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Nombre d'années avec cet employeur</label>
                        <input type="number" min="0" name="personnel[{{ $index }}][nombre_annees_employeur]" value="{{ old("personnel.$index.nombre_annees_employeur", $p['nombre_annees_employeur'] ?? '') }}" class="form-control">
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
                <input type="hidden" name="personnel[{{ $index }}][experiences]" class="experiences-input" value="{{ old("personnel.$index.experiences") }}">
            </div>
        </div>
    @endforeach
</div>

<button type="button" id="addPersonnelBtn" class="btn btn-outline-secondary mb-4">+ Ajouter une personne</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('personnelBlocks');
    const addBtn = document.getElementById('addPersonnelBtn');

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

    function reindexBlocks() {
        Array.from(container.querySelectorAll('.personnel-block')).forEach((block, index) => {
            block.dataset.index = index;
            block.querySelectorAll('[name^="personnel["]').forEach((field) => {
                field.name = field.name.replace(/personnel\[\d+\]/, `personnel[${index}]`);
            });
        });
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
                    reindexBlocks();
                } else {
                    block.querySelectorAll('input, textarea').forEach((f) => { f.value = ''; });
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

    function createBlock(index) {
        const block = document.createElement('div');
        block.className = 'personnel-block card mb-3';
        block.dataset.index = index;
        block.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="row flex-grow-1 gx-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nom</label>
                            <input type="text" name="personnel[${index}][nom]" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Poste</label>
                            <input type="text" name="personnel[${index}][poste]" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-personnel ms-2" style="margin-top:28px;">Supprimer</button>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="personnel[${index}][date_naissance]" class="form-control">
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Qualifications professionnelles</label>
                        <textarea name="personnel[${index}][qualifications]" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="fw-bold mt-2 mb-2">Employeur actuel</div>
                <div class="row gx-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nom de l'employeur</label>
                        <input type="text" name="personnel[${index}][nom_employeur]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Emploi tenu</label>
                        <input type="text" name="personnel[${index}][emploi_tenu]" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Adresse de l'employeur</label>
                    <textarea name="personnel[${index}][adresse_employeur]" class="form-control" rows="2"></textarea>
                </div>
                <div class="row gx-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="personnel[${index}][telephone]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Télécopie</label>
                        <input type="text" name="personnel[${index}][telecopie]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="personnel[${index}][email]" class="form-control">
                    </div>
                </div>
                <div class="row gx-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Contact (responsable / chargé du personnel)</label>
                        <input type="text" name="personnel[${index}][contact_personnel]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Nombre d'années avec cet employeur</label>
                        <input type="number" min="0" name="personnel[${index}][nombre_annees_employeur]" class="form-control">
                    </div>
                </div>
                <div class="fw-bold mt-2 mb-2">Expérience professionnelle des dix (10) dernières années</div>
                <div class="experiences-container d-flex flex-column gap-2"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-experience mt-2">+ Ajouter une expérience</button>
                <input type="hidden" name="personnel[${index}][experiences]" class="experiences-input" value="">
            </div>
        `;
        block.querySelector('.experiences-container').appendChild(createExperienceRow());
        return block;
    }

    container.querySelectorAll('.personnel-block').forEach(attachBlockEvents);

    addBtn.addEventListener('click', () => {
        const index = container.querySelectorAll('.personnel-block').length;
        const block = createBlock(index);
        container.appendChild(block);
        attachBlockEvents(block);
    });

    document.querySelector('form')?.addEventListener('submit', () => {
        container.querySelectorAll('.personnel-block').forEach(syncExperiencesInput);
    });
});
</script>
