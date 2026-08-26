<div id="listes-fournitures-livraison-step6">
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
                                'date_livraison_plus_tot' => $parts[0] ?? '',
                                'date_livraison_plus_tard' => $parts[1] ?? '',
                                'date_livraison_offerte' => $parts[2] ?? '',
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            }
        }

        if (!empty($oldSections)) {
            foreach ($oldSections as $sKey => $sec) {
                if (!isset($oldSections[$sKey]['lignes']) || !is_array($oldSections[$sKey]['lignes'])) {
                    $oldSections[$sKey]['lignes'] = [['designation' => '', 'quantite' => 1, 'unite_physique' => '', 'site' => '', 'date_livraison_plus_tot' => '', 'date_livraison_plus_tard' => '', 'date_livraison_offerte' => '']];
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
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['date_livraison_plus_tot'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['date_livraison_plus_tot'] = $l['date_livraison_plus_tot'] ?? ($l['date_prestation'] ?? '');
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['date_livraison_plus_tard'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['date_livraison_plus_tard'] = $l['date_livraison_plus_tard'] ?? '';
                    }
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['date_livraison_offerte'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['date_livraison_offerte'] = $l['date_livraison_offerte'] ?? '';
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
                        ['designation' => '', 'quantite' => 1, 'unite_physique' => '', 'site' => '', 'date_livraison_plus_tot' => '', 'date_livraison_plus_tard' => '', 'date_livraison_offerte' => ''],
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
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="FOURNITURES" value="{{ $section['titre'] ?? '' }}">
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
                                <th style="border:1px solid #ccc; padding:8px; width:28%;">Description des Fournitures</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%;">Unité</th>
                                <th style="border:1px solid #ccc; padding:8px; width:20%;">Destination finale / Projet</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%;">Plus tôt</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%;">Plus tard</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%;">Offerte</th>
                                <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="2" class="form-control wizard-input" placeholder="Description de la fourniture">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" min="1" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][quantite]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['quantite'] ?? 1 }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][unite_physique]" class="form-control wizard-input" placeholder="U" value="{{ $ligne['unite_physique'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][site]" class="form-control wizard-input" placeholder="Ex : Destination" value="{{ $ligne['site'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_livraison_plus_tot]" class="form-control wizard-input" placeholder="Plus tôt" value="{{ $ligne['date_livraison_plus_tot'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_livraison_plus_tard]" class="form-control wizard-input" placeholder="Plus tard" value="{{ $ligne['date_livraison_plus_tard'] ?? '' }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][date_livraison_offerte]" class="form-control wizard-input" placeholder="Offerte" value="{{ $ligne['date_livraison_offerte'] ?? '' }}">
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
        Ajoutez un ou plusieurs tableaux. Chaque tableau possède son titre propre et ses lignes de désignation, quantité, unité, destination et dates de livraison. Le signataire est placé en fin de document dans le PDF.
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
                        'date_livraison_plus_tot',
                        'date_livraison_plus_tard',
                        'date_livraison_offerte',
                    ];

                    keys.forEach((key) => {
                        const field = row.querySelector(`[name*="[${key}]" ]`);
                        if (field) {
                            field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                        }
                    });
                });
            }

            // La destination et la date de livraison offerte sont en général les
            // mêmes pour toutes les lignes d'un tableau : ce qui est saisi sur la
            // première ligne se recopie automatiquement sur les lignes suivantes
            // (l'utilisateur peut toujours corriger une ligne individuellement après coup).
            function propagateFirstRowValue(sectionEl, key) {
                const rows = Array.from(sectionEl.querySelectorAll('.bordereau-lines tr'));
                if (rows.length < 2) return;
                const firstField = rows[0].querySelector(`[name*="[${key}]" ]`);
                if (!firstField) return;
                const value = firstField.value;
                rows.slice(1).forEach((row) => {
                    const field = row.querySelector(`[name*="[${key}]" ]`);
                    if (field) field.value = value;
                });
            }

            function createLineRow(sectionIndex, designation = '', quantity = '1', unit = '', site = '', datePlusTot = '', datePlusTard = '', dateOfferte = '') {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Description de la fourniture">${designation}</textarea>
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="number" min="1" name="sections[${sectionIndex}][lignes][0][quantite]" class="form-control wizard-input" placeholder="1" value="${quantity}">
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][unite_physique]" class="form-control wizard-input" placeholder="U" value="${unit}">
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][site]" class="form-control wizard-input" placeholder="Ex : Destination" value="${site}">
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][date_livraison_plus_tot]" class="form-control wizard-input" placeholder="Plus tôt" value="${datePlusTot}">
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][date_livraison_plus_tard]" class="form-control wizard-input" placeholder="Plus tard" value="${datePlusTard}">
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="text" name="sections[${sectionIndex}][lignes][0][date_livraison_offerte]" class="form-control wizard-input" placeholder="Offerte" value="${dateOfferte}">
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
                            <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="FOURNITURES" value="">
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
                                    <th style="border:1px solid #ccc; padding:8px; width:28%;">Description des Fournitures</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:10%;">Quantité</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:10%;">Unité</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:20%;">Destination finale / Projet</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:7%;">Plus tôt</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:7%;">Plus tard</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:7%;">Offerte</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                                </tr>
                            </thead>
                            <tbody class="bordereau-lines">
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary addBordereauLine">Ajouter une ligne</button>
                    </div>
                `;
                return section;
            }

            addSectionButton.addEventListener('click', function () {
                const newSectionIndex = sectionsContainer.querySelectorAll('.bordereau-section').length;
                const newSection = createSection(newSectionIndex);
                sectionsContainer.appendChild(newSection);
                attachSectionEvents(newSection, newSectionIndex);
                updateSectionIndexes();
            });

            function attachSectionEvents(sectionEl, sectionIndex) {
                const addLineButton = sectionEl.querySelector('.addBordereauLine');
                const removeSectionButton = sectionEl.querySelector('.removeBordereauSection');

                addLineButton.addEventListener('click', function () {
                    const tableBody = sectionEl.querySelector('.bordereau-lines');
                    const newLineIndex = tableBody.querySelectorAll('tr').length;
                    const newRow = createLineRow(sectionIndex);
                    tableBody.appendChild(newRow);
                    attachLineEvents(sectionEl, sectionIndex);
                    updateLineIndexes(sectionEl, sectionIndex);
                    propagateFirstRowValue(sectionEl, 'site');
                    propagateFirstRowValue(sectionEl, 'date_livraison_offerte');
                });

                removeSectionButton.addEventListener('click', function () {
                    sectionEl.remove();
                    updateSectionIndexes();
                });
            }

            function attachLineEvents(sectionEl, sectionIndex) {
                const removeButtons = sectionEl.querySelectorAll('.removeBordereauLine');
                removeButtons.forEach((btn) => {
                    if (!btn.hasListener) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.target.closest('tr').remove();
                            updateLineIndexes(sectionEl, sectionIndex);
                        });
                        btn.hasListener = true;
                    }
                });
            }

            Array.from(sectionsContainer.querySelectorAll('.bordereau-section')).forEach((sectionEl, idx) => {
                attachSectionEvents(sectionEl, idx);
                attachLineEvents(sectionEl, idx);
            });

            sectionsContainer.addEventListener('input', (event) => {
                const name = event.target.name || '';
                const isSite = name.includes('[site]');
                const isDateOfferte = name.includes('[date_livraison_offerte]');
                if (!isSite && !isDateOfferte) return;

                const row = event.target.closest('tr');
                const sectionEl = event.target.closest('.bordereau-section');
                if (!row || !sectionEl) return;

                const rows = Array.from(sectionEl.querySelectorAll('.bordereau-lines tr'));
                if (rows[0] !== row) return; // seule la première ligne propage sa valeur

                propagateFirstRowValue(sectionEl, isSite ? 'site' : 'date_livraison_offerte');
            });
        })();

        if (window.DaoTableImport) {
            window.DaoTableImport.setup({
                buttonId: 'importTableauBtn',
                statusId: 'importTableauStatus',
                sectionsContainerId: 'bordereauSections',
                importUrl: '{{ route('dossiers.importTableau') }}',
                fieldSynonyms: {
                    designation: ['description des fournitures', 'description', 'designation', 'objet'],
                    quantite: ['quantite nb d unites', 'quantite', 'qte'],
                    unite_physique: ['unite'],
                    site: ['site', 'destination', 'projet'],
                    date_livraison_plus_tot: ['date de livraison au plus tot', 'plus tot'],
                    date_livraison_plus_tard: ['date de livraison au plus tard', 'plus tard'],
                    date_livraison_offerte: ['date de livraison offerte', 'offerte'],
                },
            });
        }
    </script>
</div>
