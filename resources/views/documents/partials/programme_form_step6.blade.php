<div id="programme-step6">
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
                                'unite_physique' => $ligne->unite_physique,
                                'quantite' => $ligne->quantite,
                                'prix_unitaire' => $ligne->prix_unitaire,
                                'montant' => $ligne->montant,
                                'site' => $ligne->site,
                                'date_prestation' => $ligne->date_prestation,
                            ];
                        })->toArray(),
                    ];
                })->toArray();
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
                        ['designation' => '', 'unite_physique' => '', 'quantite' => 1, 'prix_unitaire' => '', 'montant' => '', 'site' => '', 'date_prestation' => ''],
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
                                <th style="border:1px solid #ccc; padding:8px; width:4%;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:35%;">Désignation des services / équipements</th>
                                <th style="border:1px solid #ccc; padding:8px; width:12%;">Unité physique</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité</th>
                                <th style="border:1px solid #ccc; padding:8px; width:13%;">Prix unitaire HTVA</th>
                                <th style="border:1px solid #ccc; padding:8px; width:13%;">Total</th>
                                <th style="border:1px solid #ccc; padding:8px; width:13%;">Site ou lieu de services</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Date finale de prestation</th>
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
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][unite_physique]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['unite_physique'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" min="1" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][quantite]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['quantite'] ?? 1 }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][prix_unitaire]" class="form-control wizard-input" placeholder="150000" value="{{ $ligne['prix_unitaire'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" class="form-control wizard-input programme-total" value="{{ $ligne['montant'] ?? ($ligne['prix_unitaire'] ?? '') }}" disabled>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][site]" class="form-control wizard-input" placeholder="DLCSSA" value="{{ $ligne['site'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_prestation]" class="form-control wizard-input" placeholder="12 Mois" value="{{ $ligne['date_prestation'] ?? '' }}">
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
        Ajoutez un ou plusieurs tableaux. Chaque tableau possède son titre propre et ses lignes de descriptif et prix unitaire. Le signataire est placé en fin de bordereau dans le PDF.
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
                    const field = row.querySelector(`[name*="[${key}]" ]`);
                    if (field) {
                        field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                    }
                });
            });
        }

        function createLineRow(sectionIndex, designation = '', unit = '', quantity = '1', price = '', site = '', date = '') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Désignation de l'équipement ou service">${designation}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][unite_physique]" class="form-control wizard-input" placeholder="1" value="${unit}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="number" min="1" name="sections[${sectionIndex}][lignes][0][quantite]" class="form-control wizard-input" placeholder="1" value="${quantity}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][prix_unitaire]" class="form-control wizard-input programme-price" placeholder="150000" value="${price}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" class="form-control wizard-input programme-total" value="" disabled>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][site]" class="form-control wizard-input" placeholder="DLCSSA" value="${site}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][date_prestation]" class="form-control wizard-input" placeholder="12 Mois" value="${date}">
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
                                <th style="border:1px solid #ccc; padding:8px; width:4%;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:35%;">Désignation des services / équipements</th>
                                <th style="border:1px solid #ccc; padding:8px; width:12%;">Unité physique</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité</th>
                                <th style="border:1px solid #ccc; padding:8px; width:13%;">Prix unitaire HTVA</th>
                                <th style="border:1px solid #ccc; padding:8px; width:13%;">Total</th>
                                <th style="border:1px solid #ccc; padding:8px; width:13%;">Site ou lieu de services</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Date finale de prestation</th>
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
            tbody.appendChild(createLineRow(sectionIndex, '', '', '1', '', '', ''));
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
            const quantityInput = row.querySelector('input[name*="[quantite]"]');
            const totalDisplay = row.querySelector('input.programme-total');
            const price = Number(priceInput?.value || 0);
            const quantity = Number(quantityInput?.value || 1);
            const total = price && quantity ? price * quantity : (price ? price : '');
            if (totalDisplay) {
                totalDisplay.value = total ? total : '';
            }
        }

        sectionsContainer.addEventListener('input', (event) => {
            const row = event.target.closest('tr');
            if (!row) return;
            if (event.target.name.includes('[prix_unitaire]') || event.target.name.includes('[quantite]')) {
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
                    unite_physique: ['unite physique', 'unite'],
                    quantite: ['quantite', 'qte'],
                    prix_unitaire: ['prix unitaire'],
                    site: ['site ou lieu', 'site', 'lieu'],
                    date_prestation: ['date finale de prestation', 'date'],
                },
            });
        }
    })();
</script>
