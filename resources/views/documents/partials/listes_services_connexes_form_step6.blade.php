<div id="listes-services-connexes-step6">
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
                            $date = trim($ligne->date_prestation ?? '');
                            $date = str_replace(["\r\n", "\r"], "\n", $date);
                            $parts = preg_split('/\n|\s*\/\s*/', $date);

                            return [
                                'designation' => $ligne->designation,
                                'quantite' => (int) ($ligne->quantite ?? 1),
                                'unite_physique' => $ligne->unite_physique ?? '',
                                'site' => $ligne->site ?? '',
                                'date_prestation_plus_tot' => $parts[0] ?? '',
                                'date_prestation_plus_tard' => $parts[1] ?? '',
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            }
        }

        if (!empty($oldSections)) {
            foreach ($oldSections as $sKey => $sec) {
                if (!isset($oldSections[$sKey]['lignes']) || !is_array($oldSections[$sKey]['lignes'])) {
                    $oldSections[$sKey]['lignes'] = [['designation' => '', 'quantite' => 1, 'unite_physique' => '', 'site' => '', 'date_prestation_plus_tot' => '', 'date_prestation_plus_tard' => '']];
                    continue;
                }

                foreach ($oldSections[$sKey]['lignes'] as $lKey => $l) {
                    $q = isset($l['quantite']) ? (int) $l['quantite'] : 1;
                    if ($q < 1) {
                        $q = 1;
                    }
                    $oldSections[$sKey]['lignes'][$lKey]['quantite'] = $q;
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['unite_physique'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['unite_physique'] = $l['unite_physique'] ?? '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['site'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['site'] = $l['site'] ?? '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['date_prestation_plus_tot'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['date_prestation_plus_tot'] = $l['date_prestation_plus_tot'] ?? ($l['date_prestation'] ?? '');
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['date_prestation_plus_tard'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['date_prestation_plus_tard'] = $l['date_prestation_plus_tard'] ?? '';
                    }
                }
            }
        }

        if (empty($oldSections)) {
            $oldSections = [
                [
                    'titre' => '',
                    'lignes' => [
                        ['designation' => '', 'quantite' => 1, 'unite_physique' => '', 'site' => '', 'date_prestation_plus_tot' => '', 'date_prestation_plus_tard' => ''],
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
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="INSTALLATION ET MAINTENANCE" value="{{ $section['titre'] ?? '' }}">
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
                                <th style="border:1px solid #ccc; padding:8px; width:32%;">Description du service</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité</th>
                                <th style="border:1px solid #ccc; padding:8px; width:14%;">Unité physique</th>
                                <th style="border:1px solid #ccc; padding:8px; width:22%;">Site ou lieu où les Services doivent être exécutés</th>
                                <th style="border:1px solid #ccc; padding:8px; width:18%;">Date finale de réalisation des Services<br><span style="font-size:10px;">Plus tôt / Plus tard</span></th>
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
                                        <input type="number" min="1" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][quantite]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['quantite'] ?? 1 }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][unite_physique]" class="form-control wizard-input" placeholder="U" value="{{ $ligne['unite_physique'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][site]" class="form-control wizard-input" placeholder="Ex : CHU-MEL" value="{{ $ligne['site'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <div style="display:grid; gap:6px;">
                                            <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_prestation_plus_tot]" class="form-control wizard-input" placeholder="Plus tôt" value="{{ $ligne['date_prestation_plus_tot'] ?? '' }}">
                                            <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_prestation_plus_tard]" class="form-control wizard-input" placeholder="Plus tard" value="{{ $ligne['date_prestation_plus_tard'] ?? '' }}">
                                        </div>
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
        Ajoutez un ou plusieurs tableaux. Chaque tableau possède son titre propre et ses lignes de désignation, quantité, unité, site et date finale de réalisation. Le signataire est placé en fin de document dans le PDF.
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
                    field.name = field.name.replace(/sections\[\d+\]/, `sections[${sectionIndex}]`);
                });
                updateLineIndexes(sectionEl, sectionIndex);
            });
        }

        function updateLineIndexes(sectionEl, sectionIndex) {
            Array.from(sectionEl.querySelectorAll('.bordereau-lines tr')).forEach((row, lineIndex) => {
                row.cells[0].textContent = lineIndex + 1;
                const keys = [
                    'designation',
                    'quantite',
                    'unite_physique',
                    'site',
                    'date_prestation_plus_tot',
                    'date_prestation_plus_tard',
                ];

                keys.forEach((key) => {
                    const field = row.querySelector(`[name*="[${key}]" ]`);
                    if (field) {
                        field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                    }
                });
            });
        }

        function createLineRow(sectionIndex, designation = '', quantity = '1', unit = '', site = '', datePlusTot = '', datePlusTard = '') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Description du service">${designation}</textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="number" min="1" name="sections[${sectionIndex}][lignes][0][quantite]" class="form-control wizard-input" placeholder="1" value="${quantity}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][unite_physique]" class="form-control wizard-input" placeholder="U" value="${unit}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <input type="text" name="sections[${sectionIndex}][lignes][0][site]" class="form-control wizard-input" placeholder="Ex : CHU-MEL" value="${site}">
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <div style="display:grid; gap:6px;">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][date_prestation_plus_tot]" class="form-control wizard-input" placeholder="Plus tôt" value="${datePlusTot}">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][date_prestation_plus_tard]" class="form-control wizard-input" placeholder="Plus tard" value="${datePlusTard}">
                    </div>
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
                        <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="INSTALLATION ET MAINTENANCE" value="">
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
                                <th style="border:1px solid #ccc; padding:8px; width:32%;">Description du service</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité</th>
                                <th style="border:1px solid #ccc; padding:8px; width:14%;">Unité physique</th>
                                <th style="border:1px solid #ccc; padding:8px; width:22%;">Site ou lieu où les Services doivent être exécutés</th>
                                <th style="border:1px solid #ccc; padding:8px; width:18%;">Date finale de réalisation des Services<br><span style="font-size:10px;">Plus tôt / Plus tard</span></th>
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
            section.querySelector('.bordereau-lines').appendChild(createLineRow(sectionIndex));
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
            if (!sectionsContainer.querySelector('.bordereau-section')) {
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
                if (!tbody.querySelector('tr')) {
                    tbody.appendChild(createLineRow(sectionIndex));
                }
                updateLineIndexes(section, sectionIndex);
            }
        });

        updateSectionIndexes();
    })();
</script>
