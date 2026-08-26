<div id="bordereau-fournitures-benin-step6">
    @php
        $existingValues = [];
        if (!empty($dossierDocument?->content)) {
            $decoded = json_decode($dossierDocument->content, true);
            if (is_array($decoded)) {
                $existingValues = $decoded;
            }
        }
        $value = function ($key, $default = '') use ($existingValues) {
            return old($key, $existingValues[$key] ?? $default);
        };

        $oldSections = old('sections', []);
        if (empty($oldSections) && isset($bordereaux) && $bordereaux->count() > 0) {
            $oldSections = $bordereaux->map(function ($bordereau) {
                return [
                    'titre' => $bordereau->titre,
                    'lignes' => $bordereau->lignes->map(function ($ligne) {
                        return [
                            'designation' => $ligne->designation,
                            'quantite' => $ligne->quantite,
                            'prix_unitaire' => $ligne->prix_unitaire,
                            'date_prestation' => $ligne->date_prestation,
                            'transport' => $ligne->transport ?? '',
                            'cout_main_oeuvre_locale' => $ligne->cout_main_oeuvre_locale ?? '',
                            'taxe_vente' => $ligne->taxe_vente ?? '',
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
                    'lignes' => [
                        ['designation' => '', 'quantite' => '', 'prix_unitaire' => '', 'date_prestation' => '', 'transport' => '', 'cout_main_oeuvre_locale' => '', 'taxe_vente' => ''],
                    ],
                ],
            ];
        }
    @endphp

    <div class="wizard-field" style="margin-bottom: 16px;">
        <p class="wizard-label">Bordereau des prix pour les fournitures fabriquées au Bénin</p>
    </div>

    <div style="border:1px solid #d1d5db; padding:16px; background:#ffffff; margin-bottom:16px;">
        <div class="wizard-alert" style="margin-bottom:16px; font-size:0.85rem; color:#475569;">
            La date de remise de l'offre reprend automatiquement la date de soumission du dossier.
        </div>
        <div style="margin-bottom:0;">
            <label class="wizard-label" for="variante">Variante No. (laisser vide si l'offre n'est pas une variante)</label>
            <input id="variante" type="text" name="variante" class="form-control wizard-input" value="{{ $value('variante', '') }}">
        </div>
    </div>

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
                        <label class="wizard-label">Titre du tableau (optionnel)</label>
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" value="{{ $section['titre'] ?? '' }}">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:10px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:5px; width:3%;">1</th>
                                <th style="border:1px solid #ccc; padding:5px; width:14%;">2<br>Description</th>
                                <th style="border:1px solid #ccc; padding:5px; width:9%;">3<br>Date de livraison (Incoterms)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:7%;">4<br>Quantité</th>
                                <th style="border:1px solid #ccc; padding:5px; width:9%;">5<br>Prix unitaire EXW</th>
                                <th style="border:1px solid #ccc; padding:5px; width:9%;">6<br>Prix total EXW (4×5)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:14%;">7<br>Prix transport terrestre et autres services jusqu'à destination finale</th>
                                <th style="border:1px solid #ccc; padding:5px; width:13%;">8<br>Coût main-d'œuvre locale, matières premières et composants du Pays de l'Acheteur (% de Col.5)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:11%;">9<br>Taxe de vente et autres taxes si le marché est attribué</th>
                                <th style="border:1px solid #ccc; padding:5px; width:8%;">10<br>Prix total par article (6+7)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:3%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:5px; text-align:center; vertical-align:top;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="2" class="form-control wizard-input">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_prestation]" class="form-control wizard-input" value="{{ $ligne['date_prestation'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="number" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][quantite]" class="form-control wizard-input" value="{{ $ligne['quantite'] ?? '' }}" step="0.01">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][prix_unitaire]" class="form-control wizard-input prix-input" value="{{ $ligne['prix_unitaire'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][montant]" class="form-control wizard-input montant-display" placeholder="Auto-calc" disabled value="">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][transport]" class="form-control wizard-input transport-input" value="{{ $ligne['transport'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][cout_main_oeuvre_locale]" class="form-control wizard-input" placeholder="Ex : 20%" value="{{ $ligne['cout_main_oeuvre_locale'] ?? '' }}" autocomplete="new-password" data-lpignore="true" data-form-type="other">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][taxe_vente]" class="form-control wizard-input" value="{{ $ligne['taxe_vente'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][prix_total]" class="form-control wizard-input prix-total-display" placeholder="Auto-calc" disabled value="">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:5px; vertical-align:top; text-align:center;">
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
        Les colonnes 6 (Prix total EXW) et 10 (Prix total par article) se calculent automatiquement (6 = 4×5, 10 = 6+7). Les montants se calculent automatiquement.
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
                    'date_prestation',
                    'quantite',
                    'prix_unitaire',
                    'montant',
                    'transport',
                    'cout_main_oeuvre_locale',
                    'taxe_vente',
                    'prix_total',
                ];

                definitions.forEach((key) => {
                    const field = row.querySelector(`[name*="[${key}]"]`);
                    if (field) {
                        field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                    }
                });
            });
        }

        function createLineRow(sectionIndex) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:5px; text-align:center; vertical-align:top;"></td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input"></textarea>
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][date_prestation]" class="form-control wizard-input">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="number" name="sections[${sectionIndex}][lignes][0][quantite]" class="form-control wizard-input" step="0.01">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][prix_unitaire]" class="form-control wizard-input prix-input">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][montant]" class="form-control wizard-input montant-display" placeholder="Auto-calc" disabled value="">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][transport]" class="form-control wizard-input transport-input">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][cout_main_oeuvre_locale]" class="form-control wizard-input" placeholder="Ex : 20%" autocomplete="new-password" data-lpignore="true" data-form-type="other">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][taxe_vente]" class="form-control wizard-input">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][prix_total]" class="form-control wizard-input prix-total-display" placeholder="Auto-calc" disabled value="">
                </td>
                <td style="border:1px solid #ccc; padding:5px; vertical-align:top; text-align:center;">
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
                        <label class="wizard-label">Titre du tableau (optionnel)</label>
                        <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" value="">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:10px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:5px; width:3%;">1</th>
                                <th style="border:1px solid #ccc; padding:5px; width:14%;">2<br>Description</th>
                                <th style="border:1px solid #ccc; padding:5px; width:9%;">3<br>Date de livraison (Incoterms)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:7%;">4<br>Quantité</th>
                                <th style="border:1px solid #ccc; padding:5px; width:9%;">5<br>Prix unitaire EXW</th>
                                <th style="border:1px solid #ccc; padding:5px; width:9%;">6<br>Prix total EXW (4×5)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:14%;">7<br>Prix transport terrestre et autres services jusqu'à destination finale</th>
                                <th style="border:1px solid #ccc; padding:5px; width:13%;">8<br>Coût main-d'œuvre locale, matières premières et composants du Pays de l'Acheteur (% de Col.5)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:11%;">9<br>Taxe de vente et autres taxes si le marché est attribué</th>
                                <th style="border:1px solid #ccc; padding:5px; width:8%;">10<br>Prix total par article (6+7)</th>
                                <th style="border:1px solid #ccc; padding:5px; width:3%;"></th>
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
            tbody.appendChild(createLineRow(sectionIndex));
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
                const row = createLineRow(sectionIndex);
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
                    tbody.appendChild(createLineRow(sectionIndex));
                }
                updateLineIndexes(section, sectionIndex);
            }
        });

        function updateRowTotals(row) {
            const priceInput = row.querySelector('input[name*="[prix_unitaire]"]');
            const quantiteInput = row.querySelector('input[name*="[quantite]"]');
            const transportInput = row.querySelector('input[name*="[transport]"]');
            const montantDisplay = row.querySelector('input.montant-display');
            const prixTotalDisplay = row.querySelector('input.prix-total-display');

            const price = Number(priceInput?.value || 0);
            const quantite = Number(quantiteInput?.value || 0);
            const transport = Number(transportInput?.value || 0);
            const montantExw = price * quantite;

            if (montantDisplay) {
                montantDisplay.value = montantExw ? montantExw : '';
            }
            if (prixTotalDisplay) {
                const total = montantExw + transport;
                prixTotalDisplay.value = total ? total : '';
            }
        }

        sectionsContainer.addEventListener('input', (event) => {
            const row = event.target.closest('tr');
            if (!row) return;
            if (event.target.name.includes('[prix_unitaire]') || event.target.name.includes('[quantite]') || event.target.name.includes('[transport]')) {
                updateRowTotals(row);
            }
        });

        // Initialize totals for pre-filled rows
        sectionsContainer.querySelectorAll('tbody tr').forEach((row) => {
            updateRowTotals(row);
        });

        updateSectionIndexes();

        if (window.DaoTableImport) {
            window.DaoTableImport.setup({
                buttonId: 'importTableauBtn',
                statusId: 'importTableauStatus',
                sectionsContainerId: 'bordereauSections',
                importUrl: '{{ route('dossiers.importTableau') }}',
                fieldSynonyms: {
                    designation: ['description', 'designation', 'objet'],
                    date_prestation: ['date de livraison', 'date'],
                    quantite: ['quantite', 'qte'],
                    prix_unitaire: ['prix unitaire exw', 'prix unitaire'],
                    transport: ['prix transport', 'transport'],
                    cout_main_oeuvre_locale: ['cout main d oeuvre', 'main d oeuvre locale'],
                    taxe_vente: ['taxe de vente', 'taxe vente'],
                },
            });
        }
    })();
</script>
