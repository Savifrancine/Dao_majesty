<div id="bordereau-prix-calendrier-step6">
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
                                'date_prestation' => $ligne->date_prestation,
                                'frequence' => $ligne->frequence ?? '',
                                'quantite' => (int) ($ligne->quantite ?? 1),
                                'prix_unitaire' => $ligne->prix_unitaire,
                                'montant' => $ligne->montant,
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            }
        }

        if (!empty($oldSections)) {
            foreach ($oldSections as $sKey => $sec) {
                if (!isset($oldSections[$sKey]['lignes']) || !is_array($oldSections[$sKey]['lignes'])) {
                    $oldSections[$sKey]['lignes'] = [['designation' => '', 'date_prestation' => '', 'frequence' => '', 'quantite' => 1, 'prix_unitaire' => '', 'montant' => '']];
                    continue;
                }
                foreach ($oldSections[$sKey]['lignes'] as $lKey => $l) {
                    $q = isset($l['quantite']) ? (int) $l['quantite'] : 1;
                    if ($q < 1) $q = 1;
                    $oldSections[$sKey]['lignes'][$lKey]['quantite'] = $q;
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['prix_unitaire'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['prix_unitaire'] = $l['prix_unitaire'] ?? '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['montant'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['montant'] = $l['montant'] ?? '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['frequence'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['frequence'] = $l['frequence'] ?? '';
                    }
                }
            }
        }

        if (empty($oldSections)) {
            $oldSections = [
                [
                    'titre' => '',
                    'lignes' => [
                        ['designation' => '', 'date_prestation' => '', 'frequence' => '', 'quantite' => 1, 'prix_unitaire' => '', 'montant' => ''],
                    ],
                ],
            ];
        }
    @endphp

    <div class="mb-3">
        <button type="button" id="addBordereauSection" class="btn btn-secondary">Ajouter un tableau</button>
    </div>

    <div id="bordereauSections">
        @foreach($oldSections as $sectionIndex => $section)
            <div class="bordereau-section" data-section-index="{{ $sectionIndex }}" style="border:1px solid #ccc; padding:12px; margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                    <div style="flex:1;">
                        <label class="wizard-label">Titre du tableau</label>
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="Lot 1 : Services connexes..." value="{{ $section['titre'] ?? '' }}">
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
                                <th style="border:1px solid #ccc; padding:8px; width:30%;">Description des services</th>
                                <th style="border:1px solid #ccc; padding:8px; width:16%;">Date de réalisation au lieu de destination finale</th>
                                <th style="border:1px solid #ccc; padding:8px; width:16%;">Fréquence</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité (Nb. d'unités)</th>
                                <th style="border:1px solid #ccc; padding:8px; width:12%;">Prix unitaire</th>
                                <th style="border:1px solid #ccc; padding:8px; width:12%;">Total par article</th>
                                <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="2" class="form-control wizard-input" placeholder="Description du service">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_prestation]" class="form-control wizard-input" placeholder="Ex : 2 mois" value="{{ $ligne['date_prestation'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][frequence]" class="form-control wizard-input" placeholder="Ex : Une seule fois" value="{{ $ligne['frequence'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" min="1" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][quantite]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['quantite'] ?? 1 }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][prix_unitaire]" class="form-control wizard-input" placeholder="150000" value="{{ $ligne['prix_unitaire'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" class="form-control wizard-input montant-display" value="{{ $ligne['montant'] ?? ($ligne['prix_unitaire'] ?? '') }}" disabled>
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
        Ajoutez un ou plusieurs tableaux. Chaque tableau possède son titre propre et ses lignes de désignation, date, fréquence, quantité et prix unitaire. Le signataire est placé en fin de document dans le PDF.
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
                    'frequence',
                    'quantite',
                    'prix_unitaire',
                ];

                definitions.forEach((key) => {
                    const field = row.querySelector(`[name*="[${key}]" ]`);
                    if (field) {
                        field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                    }
                });
            });
        }

        function createLineRow(sectionIndex, designation = '', date = '', frequence = '', quantity = '1', price = '') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Description du service">${designation}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][date_prestation]" class="form-control wizard-input" placeholder="Ex : 2 mois" value="${date}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][frequence]" class="form-control wizard-input" placeholder="Ex : Une seule fois" value="${frequence}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="number" min="1" name="sections[${sectionIndex}][lignes][0][quantite]" class="form-control wizard-input" placeholder="1" value="${quantity}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][prix_unitaire]" class="form-control wizard-input" placeholder="150000" value="${price}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" class="form-control wizard-input montant-display" value="" disabled>
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
                        <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="Lot 1 : Services connexes..." value="">
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
                                <th style="border:1px solid #ccc; padding:8px; width:30%;">Description des services</th>
                                <th style="border:1px solid #ccc; padding:8px; width:16%;">Date de réalisation au lieu de destination finale</th>
                                <th style="border:1px solid #ccc; padding:8px; width:16%;">Fréquence</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité (Nb. d'unités)</th>
                                <th style="border:1px solid #ccc; padding:8px; width:12%;">Prix unitaire</th>
                                <th style="border:1px solid #ccc; padding:8px; width:12%;">Total par article</th>
                                <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
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
            tbody.appendChild(createLineRow(sectionIndex, '', '', '', '1', ''));
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
                const row = createLineRow(sectionIndex, '', '', '', '1', '');
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
                    tbody.appendChild(createLineRow(sectionIndex, '', '', '', '1', ''));
                }
                updateLineIndexes(section, sectionIndex);
            }
        });

        function updateRowTotal(row) {
            const priceInput = row.querySelector('input[name*="[prix_unitaire]"]');
            const quantityInput = row.querySelector('input[name*="[quantite]"]');
            const totalDisplay = row.querySelector('input.montant-display');
            const price = Number(priceInput?.value || 0);
            const quantite = Number(quantityInput?.value || 1);
            if (totalDisplay) {
                totalDisplay.value = (price * quantite) ? (price * quantite) : '';
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
    })();
</script>
