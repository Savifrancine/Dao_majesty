<div id="liste-materiel-step6">
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
                                'quantite' => (int) ($ligne->quantite ?? 1),
                                'nombre_propose' => (int) ($ligne->nombre_propose ?? 1),
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            }
        }

        if (!empty($oldSections)) {
            foreach ($oldSections as $sKey => $sec) {
                if (!isset($oldSections[$sKey]['lignes']) || !is_array($oldSections[$sKey]['lignes'])) {
                    $oldSections[$sKey]['lignes'] = [['designation' => '', 'quantite' => 1, 'nombre_propose' => 1]];
                    continue;
                }

                foreach ($oldSections[$sKey]['lignes'] as $lKey => $l) {
                    $q = isset($l['quantite']) ? (int) $l['quantite'] : 1;
                    if ($q < 1) {
                        $q = 1;
                    }
                    $oldSections[$sKey]['lignes'][$lKey]['quantite'] = $q;
                    if (!isset($oldSections[$sKey]['lignes'][$lKey]['nombre_propose'])) {
                        $oldSections[$sKey]['lignes'][$lKey]['nombre_propose'] = $l['nombre_propose'] ?? $q;
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
                        ['designation' => '', 'quantite' => 1, 'nombre_propose' => 1],
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
                        <label class="wizard-label">Titre du tableau (facultatif)</label>
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="MATERIEL" value="{{ $section['titre'] ?? '' }}">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:8px; width:6%;">Numéro</th>
                                <th style="border:1px solid #ccc; padding:8px; width:50%;">Type et caractéristiques du matériel</th>
                                <th style="border:1px solid #ccc; padding:8px; width:18%;">Nombre minimum requis</th>
                                <th style="border:1px solid #ccc; padding:8px; width:18%;">Nombre minimum proposé</th>
                                <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                            </tr>
                        </thead>
                        <tbody class="bordereau-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="2" class="form-control wizard-input" placeholder="Ex : Camion fourgon">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" min="0" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][quantite]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['quantite'] ?? 1 }}">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" min="0" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][nombre_propose]" class="form-control wizard-input" placeholder="1" value="{{ $ligne['nombre_propose'] ?? 1 }}">
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
        Ajoutez une ligne par type de matériel. Le nombre minimum proposé est pré-rempli avec le nombre requis : modifiez-le si vous proposez plus.
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
                    const keys = ['designation', 'quantite', 'nombre_propose'];

                    keys.forEach((key) => {
                        const field = row.querySelector(`[name*="[${key}]" ]`);
                        if (field) {
                            field.name = `sections[${sectionIndex}][lignes][${lineIndex}][${key}]`;
                        }
                    });
                });
            }

            function createLineRow(sectionIndex) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input" placeholder="Ex : Camion fourgon"></textarea>
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="number" min="0" name="sections[${sectionIndex}][lignes][0][quantite]" class="form-control wizard-input" placeholder="1" value="1">
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <input type="number" min="0" name="sections[${sectionIndex}][lignes][0][nombre_propose]" class="form-control wizard-input" placeholder="1" value="1">
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
                            <label class="wizard-label">Titre du tableau (facultatif)</label>
                            <input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="MATERIEL" value="">
                        </div>
                        <div style="text-align:right;">
                            <button type="button" class="btn btn-danger removeBordereauSection" style="margin-top:24px;">Supprimer le tableau</button>
                        </div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="border:1px solid #ccc; padding:8px; width:6%;">Numéro</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:50%;">Type et caractéristiques du matériel</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:18%;">Nombre minimum requis</th>
                                    <th style="border:1px solid #ccc; padding:8px; width:18%;">Nombre minimum proposé</th>
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
                    const newRow = createLineRow(sectionIndex);
                    tableBody.appendChild(newRow);
                    attachLineEvents(sectionEl, sectionIndex);
                    updateLineIndexes(sectionEl, sectionIndex);
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
        })();

        if (window.DaoTableImport) {
            window.DaoTableImport.setup({
                buttonId: 'importTableauBtn',
                statusId: 'importTableauStatus',
                sectionsContainerId: 'bordereauSections',
                importUrl: '{{ route('dossiers.importTableau') }}',
                fieldSynonyms: {
                    designation: ['type et caracteristiques du materiel', 'type et caracteristiques', 'designation', 'materiel'],
                    quantite: ['nombre minimum requis', 'nombre requis', 'quantite', 'qte'],
                    nombre_propose: ['nombre minimum propose', 'nombre propose', 'propose'],
                },
            });
        }
    </script>
</div>
