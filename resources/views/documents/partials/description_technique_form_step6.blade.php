<div id="description-technique-step6">
    @php
        $oldSections = old('sections', []);
        if (empty($oldSections) && isset($bordereaux)) {
            if (is_array($bordereaux)) {
                $oldSections = $bordereaux;
            } elseif ($bordereaux && $bordereaux->count() > 0) {
                $oldSections = $bordereaux->map(function ($bordereau) {
                    return [
                        'titre' => $bordereau->titre,
                        'lignes' => $bordereau->lignes->map(function ($ligne) {
                            return [
                                'designation' => $ligne->designation,
                                'specifications_techniques' => $ligne->specifications_techniques ?? '',
                                'specifications_obligatoires' => $ligne->specifications_obligatoires ?? '',
                                'specifications_proposees' => $ligne->specifications_proposees ?? '',
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            }
        }

        // Ensure every ligne has required specification fields
        if (!empty($oldSections)) {
            foreach ($oldSections as $sKey => $sec) {
                if (!isset($oldSections[$sKey]['lignes']) || !is_array($oldSections[$sKey]['lignes'])) {
                    $oldSections[$sKey]['lignes'] = [ ['designation' => '', 'specifications_techniques' => '', 'specifications_obligatoires' => '', 'specifications_proposees' => ''] ];
                    continue;
                }
                foreach ($oldSections[$sKey]['lignes'] as $lKey => $l) {
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['specifications_techniques'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['specifications_techniques'] = '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['specifications_obligatoires'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['specifications_obligatoires'] = '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['specifications_proposees'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['specifications_proposees'] = '';
                    }
                }
            }
        }

        if (empty($oldSections) && !empty($prefillSections)) {
            $oldSections = $prefillSections;
        }

        if (empty($oldSections)) {
            $oldSections = [
                [
                    'titre' => '',
                    'lignes' => [
                        ['designation' => '', 'specifications_techniques' => '', 'specifications_obligatoires' => '', 'specifications_proposees' => ''],
                    ],
                ],
            ];
        }
    @endphp

    <div class="mb-3" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <button type="button" id="addBordereauSection" class="btn btn-secondary">Ajouter un tableau</button>
        <button type="button" id="importTableauBtn" class="btn btn-outline-secondary">Importer depuis un fichier (Excel/CSV)</button>
        <span id="importTableauStatus" style="font-size:0.8rem;"></span>
    </div>

    <div id="bordereauSections">
        @foreach($oldSections as $sectionIndex => $section)
            <div class="bordereau-section" data-section-index="{{ $sectionIndex }}" style="border:1px solid #ccc; padding:12px; margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                    <div style="flex:1;">
                        <label class="wizard-label">Titre du tableau</label>
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="Résumé des Spécifications Techniques" value="{{ $section['titre'] ?? '' }}">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:11px;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th style="border:1px solid #ccc; padding:8px; width:3%; text-align:center;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:22%;">Désignation</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Spécifications techniques</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Spécifications obligatoires</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Spécifications proposées</th>
                                <th style="border:1px solid #ccc; padding:8px; width:5%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top; font-weight:bold;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="2" class="form-control wizard-input" placeholder="Ex: Automate d'immuno-hématologie" style="font-size:10px;">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][specifications_techniques]" rows="2" class="form-control wizard-input" placeholder="- Marque et modèle&#10;- Fonctionnement..." style="font-size:10px;">{{ $ligne['specifications_techniques'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][specifications_obligatoires]" rows="2" class="form-control wizard-input" placeholder="- Spécifications obligatoires&#10;- ..." style="font-size:10px;">{{ $ligne['specifications_obligatoires'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][specifications_proposees]" rows="2" class="form-control wizard-input" placeholder="- Spécifications proposées&#10;- ..." style="font-size:10px;">{{ $ligne['specifications_proposees'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                                        <button type="button" class="btn btn-sm btn-danger removeBordereauLine" style="padding:0.25rem 0.45rem;">×</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary addBordereauLine">Ajouter une ligne</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mb-3" style="font-size:0.85rem; color:#555;">
        Décrivez les spécifications techniques pour chaque service. Chaque ligne comporte la désignation du service et ses spécifications (techniques, obligatoires, proposées).
    </div>
</div>

<script>
    (function () {
        const sectionsContainer = document.getElementById('bordereauSections');
        const addSectionButton = document.getElementById('addBordereauSection');

        function updateSectionIndexes() {
            Array.from(sectionsContainer.querySelectorAll('.bordereau-section')).forEach((sectionEl, sectionIndex) => {
                sectionEl.dataset.sectionIndex = sectionIndex;
                sectionEl.querySelectorAll('input, textarea').forEach((field) => {
                    const name = field.name.replace(/sections\[\d+\]/, `sections[${sectionIndex}]`);
                    field.name = name;
                });
                updateLineIndexes(sectionEl, sectionIndex);
            });
        }

        function updateLineIndexes(sectionEl, sectionIndex) {
            Array.from(sectionEl.querySelectorAll('.bordereau-lines tr')).forEach((row, lineIndex) => {
                row.cells[0].textContent = lineIndex + 1;
                const definitions = [
                    'designation',
                    'specifications_techniques',
                    'specifications_obligatoires',
                    'specifications_proposees',
                ];

                definitions.forEach((key) => {
                    const field = row.querySelector(`[name*="[${key}]" ]`);
                    if (field) {
                        field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                    }
                });
            });
        }

        function createLineRow(sectionIndex, designation = '', techSpecs = '', mandatorySpecs = '', proposedSpecs = '') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top; font-weight:bold;"></td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Ex: Automate d'immuno-hématologie" style="font-size:10px;">${designation}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][specifications_techniques]" rows="2" class="form-control wizard-input" placeholder="- Marque et modèle&#10;- Fonctionnement..." style="font-size:10px;">${techSpecs}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][specifications_obligatoires]" rows="2" class="form-control wizard-input" placeholder="- Spécifications obligatoires&#10;- ..." style="font-size:10px;">${mandatorySpecs}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][specifications_proposees]" rows="2" class="form-control wizard-input" placeholder="- Spécifications proposées&#10;- ..." style="font-size:10px;">${proposedSpecs}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                    <button type="button" class="btn btn-sm btn-danger removeBordereauLine" style="padding:0.25rem 0.45rem;">×</button>
                </td>
            `;
            return row;
        }

        function createSection(sectionIndex) {
            const section = document.createElement('div');
            section.className = 'bordereau-section';
            section.dataset.sectionIndex = sectionIndex;
            section.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                    <div style="flex:1;">
                        <label class="wizard-label">Titre du tableau</label>
                        <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="Résumé des Spécifications Techniques" value="">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:11px;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th style="border:1px solid #ccc; padding:8px; width:3%; text-align:center;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:22%;">Désignation</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Spécifications techniques</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Spécifications obligatoires</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Spécifications proposées</th>
                                <th style="border:1px solid #ccc; padding:8px; width:5%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines"></tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary addBordereauLine">Ajouter une ligne</button>
                </div>
            `;
            const tbody = section.querySelector('.bordereau-lines');
            tbody.appendChild(createLineRow(sectionIndex, '', '', '', ''));
            return section;
        }

        function addSection() {
            const sectionIndex = sectionsContainer.querySelectorAll('.bordereau-section').length;
            const section = createSection(sectionIndex);
            sectionsContainer.appendChild(section);
            updateSectionIndexes();
        }

        function removeSection(button) {
            const section = button.closest('.bordereau-section');
            if (!section) return;
            section.remove();
            if (sectionsContainer.querySelectorAll('.bordereau-section').length === 0) {
                addSection();
            }
            updateSectionIndexes();
        }

        addSectionButton.addEventListener('click', addSection);

        sectionsContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('removeBordereauSection')) {
                removeSection(event.target);
            }
            if (event.target.classList.contains('addBordereauLine')) {
                const section = event.target.closest('.bordereau-section');
                const sectionIndex = Array.from(sectionsContainer.querySelectorAll('.bordereau-section')).indexOf(section);
                const tbody = section.querySelector('.bordereau-lines');
                const row = createLineRow(sectionIndex, '', '', '', '');
                tbody.appendChild(row);
                updateLineIndexes(section, sectionIndex);
            }
            if (event.target.classList.contains('removeBordereauLine')) {
                const row = event.target.closest('tr');
                if (!row) return;
                const section = event.target.closest('.bordereau-section');
                row.remove();
                const sectionIndex = Array.from(sectionsContainer.querySelectorAll('.bordereau-section')).indexOf(section);
                const tbody = section.querySelector('.bordereau-lines');
                if (tbody.rows.length === 0) {
                    tbody.appendChild(createLineRow(sectionIndex, '', '', '', ''));
                }
                updateLineIndexes(section, sectionIndex);
            }
        });

        updateSectionIndexes();

        if (window.DaoTableImport) {
            window.DaoTableImport.setup({
                buttonId: 'importTableauBtn',
                statusId: 'importTableauStatus',
                sectionsContainerId: 'bordereauSections',
                importUrl: '{{ route('dossiers.importTableau') }}',
                fieldSynonyms: {
                    designation: ['designation', 'description', 'objet'],
                    specifications_techniques: ['specifications techniques', 'specification technique'],
                    specifications_obligatoires: ['specifications obligatoires', 'specification obligatoire'],
                    specifications_proposees: ['specifications proposees', 'specification proposee'],
                },
            });
        }
    })();
</script>

