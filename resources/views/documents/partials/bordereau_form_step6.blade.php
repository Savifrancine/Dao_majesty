<div id="bordereau-step6">
    @php
        $oldSections = old('sections', []);
        if (empty($oldSections) && isset($bordereaux) && $bordereaux->count() > 0) {
            $oldSections = $bordereaux->map(function ($bordereau) {
                return [
                    'titre' => $bordereau->titre,
                    'designation_label' => $bordereau->designation_label,
                    'lignes' => $bordereau->lignes->map(function ($ligne) {
                        return [
                            'designation' => $ligne->designation,
                            'prix_unitaire' => $ligne->prix_unitaire,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        if (empty($oldSections) && !empty($prefillSections)) {
            $oldSections = $prefillSections;
        }

        if (empty($oldSections)) {
            $oldSections = [
                [
                    'titre' => '',
                    'designation_label' => '',
                    'lignes' => [
                        ['designation' => '', 'prix_unitaire' => ''],
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
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="Lot 1 : Entretien et maintenance..." value="{{ $section['titre'] ?? '' }}">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:8px; width:5%;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:70%;">
                                    <input type="text" class="form-control wizard-input" style="font-weight:700;" name="sections[{{ $sectionIndex }}][designation_label]" placeholder="Désignation des produits" value="{{ $section['designation_label'] ?? '' }}">
                                </th>
                                <th style="border:1px solid #ccc; padding:8px; width:25%;">Prix unitaire Hors TVA (FCFA)</th>
                                <th style="border:1px solid #ccc; padding:8px; width:5%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="2" class="form-control wizard-input" placeholder="Désignation de l'équipement ou service">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][prix_unitaire]" class="form-control wizard-input" placeholder="150000" value="{{ $ligne['prix_unitaire'] ?? '' }}">
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
        Ajoutez un ou plusieurs tableaux. Chaque tableau possède son titre propre et ses lignes de désignation et prix unitaire. Le signataire est placé en fin de bordereau dans le PDF.
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
                    'unite_physique',
                    'quantite',
                    'prix_unitaire',
                    'site',
                    'date_prestation',
                ];

                definitions.forEach((key) => {
                    const field = row.querySelector(`[name*="[${key}]"]`);
                    if (field) {
                        field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                    }
                });
            });
        }

        function createLineRow(sectionIndex, designation = '', price = '') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Désignation de l'équipement ou service">${designation}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][prix_unitaire]" class="form-control wizard-input" placeholder="150000" value="${price}">
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
                        <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="Lot 1 : Entretien et maintenance..." value="">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:8px; width:5%;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:70%;">
                                    <input type="text" class="form-control wizard-input" style="font-weight:700;" name="sections[${sectionIndex}][designation_label]" placeholder="Désignation des produits" value="">
                                </th>
                                <th style="border:1px solid #ccc; padding:8px; width:25%;">Prix unitaire Hors TVA (FCFA)</th>
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
            tbody.appendChild(createLineRow(sectionIndex, '', ''));
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
                const row = createLineRow(sectionIndex, '', '');
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
                    tbody.appendChild(createLineRow(sectionIndex, '', ''));
                }
                updateLineIndexes(section, sectionIndex);
            }
        });

        function updateRowTotal(row) {
            const priceInput = row.querySelector('input[name*="[prix_unitaire]"]');
            const totalDisplay = row.querySelector('input[disabled]');
            const price = Number(priceInput?.value || 0);
            if (totalDisplay) {
                totalDisplay.value = price ? price : '';
            }
        }

        sectionsContainer.addEventListener('input', (event) => {
            const row = event.target.closest('tr');
            if (!row) return;
            if (event.target.name.includes('[prix_unitaire]')) {
                updateRowTotal(row);
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
                    designation: ['designation des services', 'designation', 'description', 'objet'],
                    prix_unitaire: ['prix unitaire'],
                },
            });
        }
    })();
</script>
