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
                        'column_defs' => \App\Support\DescriptionTechniqueColumns::resolve($bordereau->column_defs),
                        'lignes' => $bordereau->lignes->map(function ($ligne) {
                            return [
                                'designation' => $ligne->designation,
                                'specifications_techniques' => $ligne->specifications_techniques ?? '',
                                'specifications_obligatoires' => $ligne->specifications_obligatoires ?? '',
                                'specifications_proposees' => $ligne->specifications_proposees ?? '',
                                'merge_rows' => $ligne->merge_rows ?? 1,
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            }
        }

        // Ensure every ligne/section has the fields this formulaire needs
        if (!empty($oldSections)) {
            foreach ($oldSections as $sKey => $sec) {
                if (!isset($oldSections[$sKey]['column_defs']) || !is_array($oldSections[$sKey]['column_defs'])) {
                    $oldSections[$sKey]['column_defs'] = \App\Support\DescriptionTechniqueColumns::resolve(null);
                } else {
                    $oldSections[$sKey]['column_defs'] = \App\Support\DescriptionTechniqueColumns::resolve($oldSections[$sKey]['column_defs']);
                }
                if (!isset($oldSections[$sKey]['lignes']) || !is_array($oldSections[$sKey]['lignes'])) {
                    $oldSections[$sKey]['lignes'] = [ ['designation' => '', 'specifications_techniques' => '', 'specifications_obligatoires' => '', 'specifications_proposees' => '', 'merge_rows' => 1] ];
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
                    if (empty($oldSections[$sKey]['lignes'][$lKey]['merge_rows'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['merge_rows'] = 1;
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
                    'column_defs' => \App\Support\DescriptionTechniqueColumns::resolve(null),
                    'lignes' => [
                        ['designation' => '', 'specifications_techniques' => '', 'specifications_obligatoires' => '', 'specifications_proposees' => '', 'merge_rows' => 1],
                    ],
                ],
            ];
        }

        // $prefillSections (import designations depuis un autre bordereau du
        // meme dossier) ne connait pas column_defs : on complete ici aussi,
        // au cas ou le bloc "empty($oldSections)" juste au-dessus n'ait pas
        // ete emprunte (old('sections') deja present par ex.).
        foreach ($oldSections as $sKey => $sec) {
            if (!isset($oldSections[$sKey]['column_defs']) || !is_array($oldSections[$sKey]['column_defs'])) {
                $oldSections[$sKey]['column_defs'] = \App\Support\DescriptionTechniqueColumns::resolve(null);
            }
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

                <div class="bordereau-column-config" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px; margin-bottom:10px; display:flex; gap:18px; flex-wrap:wrap;">
                    @foreach($section['column_defs'] as $col)
                        <div style="display:flex; align-items:center; gap:6px;">
                            <input type="hidden" name="sections[{{ $sectionIndex }}][column_defs][{{ $col['key'] }}][visible]" value="0">
                            <input type="checkbox" class="colVisibleToggle" data-col-key="{{ $col['key'] }}" name="sections[{{ $sectionIndex }}][column_defs][{{ $col['key'] }}][visible]" value="1" {{ $col['visible'] ? 'checked' : '' }} id="colVis-{{ $sectionIndex }}-{{ $col['key'] }}">
                            <label for="colVis-{{ $sectionIndex }}-{{ $col['key'] }}" style="font-size:11px; color:#555; margin:0;">Afficher</label>
                            <input type="text" class="form-control colLabelInput" data-col-key="{{ $col['key'] }}" name="sections[{{ $sectionIndex }}][column_defs][{{ $col['key'] }}][label]" value="{{ $col['label'] }}" style="font-size:11px; padding:3px 6px; width:170px;">
                        </div>
                    @endforeach
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:11px;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th style="border:1px solid #ccc; padding:8px; width:3%; text-align:center;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:22%;">Désignation</th>
                                @foreach($section['column_defs'] as $col)
                                    <th class="dt-col-th" data-col-key="{{ $col['key'] }}" style="border:1px solid #ccc; padding:8px; width:20%; {{ $col['visible'] ? '' : 'display:none;' }}">{{ $col['label'] }}</th>
                                @endforeach
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
                                    @foreach($section['column_defs'] as $col)
                                        <td class="dt-col-td" data-col-key="{{ $col['key'] }}" style="border:1px solid #ccc; padding:8px; vertical-align:top; {{ $col['visible'] ? '' : 'display:none;' }}">
                                            <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][{{ $col['key'] }}]" rows="2" class="form-control wizard-input dt-spec-input" placeholder="- ..." style="font-size:10px;">{{ $ligne[$col['key']] ?? '' }}</textarea>
                                        </td>
                                    @endforeach
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                                        <div style="margin-bottom:4px;">
                                            <input type="number" class="form-control mergeRowsInput" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][merge_rows]" value="{{ $ligne['merge_rows'] ?? 1 }}" min="1" max="50" style="font-size:10px; padding:2px; width:48px;" title="Fusionner la/les colonne(s) description avec les N lignes suivantes (1 = pas de fusion)">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger removeBordereauLine" style="padding:0.25rem 0.45rem;">×</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mb-3" style="font-size:0.78rem; color:#777;">« Fusionner » : indiquez, sur la première ligne d'un groupe, le nombre de lignes numérotées qu'elle couvre (ex: 17 pour couvrir les lignes 1 à 17) — les lignes suivantes de ce groupe voient alors leurs colonnes de description grisées (reprises automatiquement de la première ligne).</div>
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
        const DEFAULT_COLUMNS = [
            { key: 'specifications_techniques', label: 'Spécifications techniques' },
            { key: 'specifications_obligatoires', label: 'Spécifications obligatoires' },
            { key: 'specifications_proposees', label: 'Spécifications proposées' },
        ];

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

        // Renomme generiquement chaque champ d'une ligne a partir de SA
        // DERNIERE cle (ex: "...[specifications_proposees]"), plutot que
        // d'enumerer les colonnes : fonctionne quelle que soit la colonne
        // masquee/visible a cet instant.
        function updateLineIndexes(sectionEl, sectionIndex) {
            Array.from(sectionEl.querySelectorAll('.bordereau-lines tr')).forEach((row, lineIndex) => {
                row.cells[0].textContent = lineIndex + 1;
                row.querySelectorAll('[name]').forEach((field) => {
                    const match = field.name.match(/\[([a-zA-Z_]+)\]$/);
                    if (!match) return;
                    field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${match[1]}]`;
                });
            });
        }

        function getColumnDefs(sectionEl) {
            return Array.from(sectionEl.querySelectorAll('.colVisibleToggle')).map((cb) => {
                const labelInput = sectionEl.querySelector(`.colLabelInput[data-col-key="${cb.dataset.colKey}"]`);
                return {
                    key: cb.dataset.colKey,
                    visible: cb.checked,
                    label: labelInput ? labelInput.value : cb.dataset.colKey,
                };
            });
        }

        function applyColumnVisibility(sectionEl) {
            getColumnDefs(sectionEl).forEach((col) => {
                sectionEl.querySelectorAll(`.dt-col-th[data-col-key="${col.key}"], .dt-col-td[data-col-key="${col.key}"]`).forEach((el) => {
                    el.style.display = col.visible ? '' : 'none';
                });
            });
        }

        function applyColumnLabels(sectionEl) {
            getColumnDefs(sectionEl).forEach((col) => {
                const th = sectionEl.querySelector(`.dt-col-th[data-col-key="${col.key}"]`);
                if (th) th.textContent = col.label || col.key;
            });
        }

        // Une ligne dont le "merge_rows" est > 1 couvre les N-1 lignes
        // suivantes : leurs champs de description sont desactives (donc non
        // soumis -> vides en base) et grises, la vraie valeur restant portee
        // par la premiere ligne du groupe.
        function applyMergeState(sectionEl) {
            let remaining = 0;
            Array.from(sectionEl.querySelectorAll('.bordereau-lines tr')).forEach((row) => {
                const mergeInput = row.querySelector('.mergeRowsInput');
                const specInputs = row.querySelectorAll('.dt-spec-input');
                if (remaining > 0) {
                    specInputs.forEach((ta) => {
                        ta.disabled = true;
                        ta.value = '';
                        ta.placeholder = '(fusionné avec la ligne au-dessus)';
                    });
                    if (mergeInput) {
                        mergeInput.disabled = true;
                        mergeInput.value = 1;
                    }
                    remaining -= 1;
                } else {
                    specInputs.forEach((ta) => {
                        ta.disabled = false;
                        ta.placeholder = '- ...';
                    });
                    if (mergeInput) {
                        mergeInput.disabled = false;
                        remaining = Math.max(1, parseInt(mergeInput.value, 10) || 1) - 1;
                    }
                }
            });
        }

        function createLineRow(sectionEl, sectionIndex) {
            const columns = getColumnDefs(sectionEl);
            const row = document.createElement('tr');
            const colsHtml = columns.map((col) => `
                <td class="dt-col-td" data-col-key="${col.key}" style="border:1px solid #ccc; padding:8px; vertical-align:top; ${col.visible ? '' : 'display:none;'}">
                    <textarea name="sections[${sectionIndex}][lignes][0][${col.key}]" rows="2" class="form-control wizard-input dt-spec-input" placeholder="- ..." style="font-size:10px;"></textarea>
                </td>
            `).join('');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top; font-weight:bold;"></td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Ex: Automate d'immuno-hématologie" style="font-size:10px;"></textarea>
                </td>
                ${colsHtml}
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                    <div style="margin-bottom:4px;">
                        <input type="number" class="form-control mergeRowsInput" name="sections[${sectionIndex}][lignes][0][merge_rows]" value="1" min="1" max="50" style="font-size:10px; padding:2px; width:48px;" title="Fusionner la/les colonne(s) description avec les N lignes suivantes (1 = pas de fusion)">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger removeBordereauLine" style="padding:0.25rem 0.45rem;">×</button>
                </td>
            `;
            return row;
        }

        function createSection(sectionIndex) {
            const section = document.createElement('div');
            section.className = 'bordereau-section';
            section.dataset.sectionIndex = sectionIndex;
            const configHtml = DEFAULT_COLUMNS.map((col) => `
                <div style="display:flex; align-items:center; gap:6px;">
                    <input type="hidden" name="sections[${sectionIndex}][column_defs][${col.key}][visible]" value="0">
                    <input type="checkbox" class="colVisibleToggle" data-col-key="${col.key}" name="sections[${sectionIndex}][column_defs][${col.key}][visible]" value="1" checked>
                    <label style="font-size:11px; color:#555; margin:0;">Afficher</label>
                    <input type="text" class="form-control colLabelInput" data-col-key="${col.key}" name="sections[${sectionIndex}][column_defs][${col.key}][label]" value="${col.label}" style="font-size:11px; padding:3px 6px; width:170px;">
                </div>
            `).join('');
            const headersHtml = DEFAULT_COLUMNS.map((col) => `
                <th class="dt-col-th" data-col-key="${col.key}" style="border:1px solid #ccc; padding:8px; width:20%;">${col.label}</th>
            `).join('');
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
                <div class="bordereau-column-config" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px; margin-bottom:10px; display:flex; gap:18px; flex-wrap:wrap;">
                    ${configHtml}
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:11px;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th style="border:1px solid #ccc; padding:8px; width:3%; text-align:center;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:22%;">Désignation</th>
                                ${headersHtml}
                                <th style="border:1px solid #ccc; padding:8px; width:5%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines"></tbody>
                    </table>
                </div>
                <div class="mb-3" style="font-size:0.78rem; color:#777;">« Fusionner » : indiquez, sur la première ligne d'un groupe, le nombre de lignes numérotées qu'elle couvre (ex: 17 pour couvrir les lignes 1 à 17) — les lignes suivantes de ce groupe voient alors leurs colonnes de description grisées (reprises automatiquement de la première ligne).</div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary addBordereauLine">Ajouter une ligne</button>
                </div>
            `;
            const tbody = section.querySelector('.bordereau-lines');
            tbody.appendChild(createLineRow(section, sectionIndex));
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
                const row = createLineRow(section, sectionIndex);
                tbody.appendChild(row);
                updateLineIndexes(section, sectionIndex);
                applyMergeState(section);
            }
            if (event.target.classList.contains('removeBordereauLine')) {
                const row = event.target.closest('tr');
                if (!row) return;
                const section = event.target.closest('.bordereau-section');
                row.remove();
                const sectionIndex = Array.from(sectionsContainer.querySelectorAll('.bordereau-section')).indexOf(section);
                const tbody = section.querySelector('.bordereau-lines');
                if (tbody.rows.length === 0) {
                    tbody.appendChild(createLineRow(section, sectionIndex));
                }
                updateLineIndexes(section, sectionIndex);
                applyMergeState(section);
            }
        });

        sectionsContainer.addEventListener('change', (event) => {
            if (event.target.classList.contains('colVisibleToggle')) {
                const sectionEl = event.target.closest('.bordereau-section');
                const stillVisible = sectionEl.querySelectorAll('.colVisibleToggle:checked').length;
                if (stillVisible === 0) {
                    event.target.checked = true;
                    alert('Au moins une colonne doit rester visible.');
                    return;
                }
                applyColumnVisibility(sectionEl);
            }
            if (event.target.classList.contains('mergeRowsInput')) {
                applyMergeState(event.target.closest('.bordereau-section'));
            }
        });
        sectionsContainer.addEventListener('input', (event) => {
            if (event.target.classList.contains('colLabelInput')) {
                applyColumnLabels(event.target.closest('.bordereau-section'));
            }
        });

        updateSectionIndexes();
        Array.from(sectionsContainer.querySelectorAll('.bordereau-section')).forEach((sectionEl) => {
            applyMergeState(sectionEl);
        });

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

