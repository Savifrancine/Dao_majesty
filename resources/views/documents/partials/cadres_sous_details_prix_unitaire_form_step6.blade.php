<div id="cadres-sous-details-prix-unitaires-step6">
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
                                'unite' => $ligne->unite_physique ?? '',
                                'total_materiel' => $ligne->total_materiel ?? '',
                                'location_amort' => $ligne->location_amort ?? '',
                                'matiere_frais' => $ligne->matiere_frais ?? '',
                                'main_oeuvre' => $ligne->main_oeuvre ?? '',
                                'deborse_sec' => $ligne->deborse_sec ?? '',
                                'coef_c1' => $ligne->coef_c1 ?? '',
                                'coef_k' => $ligne->coef_k ?? '',
                                'prix_vente_htva' => $ligne->prix_vente_htva ?? '',
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
                ['titre' => '', 'lignes' => [
                    ['designation' => '', 'unite' => '', 'total_materiel' => '', 'location_amort' => '', 'matiere_frais' => '', 'main_oeuvre' => '', 'deborse_sec' => '', 'coef_c1' => '', 'coef_k' => '', 'prix_vente_htva' => '']
                ]],
            ];
        }
    @endphp

    <div class="mb-3" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <button type="button" id="addCadreSection" class="btn btn-secondary">Ajouter un tableau</button>
        <button type="button" id="importTableauBtn" class="btn btn-outline-secondary">Importer depuis un fichier (Excel/CSV)</button>
        <span id="importTableauStatus" style="font-size:0.8rem;"></span>
    </div>

    <div id="cadreSections">
        @foreach($oldSections as $sectionIndex => $section)
            <div class="cadre-section" data-section-index="{{ $sectionIndex }}" style="border:1px solid #ccc; padding:12px; margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                    <div style="flex:1;">
                        <label class="wizard-label">Titre du tableau</label>
                        <input type="text" class="form-control wizard-input" name="sections[{{ $sectionIndex }}][titre]" placeholder="CADRE" value="{{ $section['titre'] ?? '' }}">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="btn btn-danger removeCadreSection" style="margin-top:24px;">Supprimer le tableau</button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:8px; width:4%; text-align:center;">N°</th>
                                <th style="border:1px solid #ccc; padding:8px; width:26%;">DESCRIPTION</th>
                                <th style="border:1px solid #ccc; padding:8px; width:10%; text-align:center;">Unit</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%; text-align:center;">Total Materiel</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%; text-align:center;">Location Amort. Materiel</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%; text-align:center;">Matière Et Frais divers</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%; text-align:center;">Main d'oeuvre</th>
                                <th style="border:1px solid #ccc; padding:8px; width:7%; text-align:center;">Déboursé Sec</th>
                                <th style="border:1px solid #ccc; padding:8px; width:6%; text-align:center;">C1=coef.frais</th>
                                <th style="border:1px solid #ccc; padding:8px; width:6%; text-align:center;">Coef.de vente</th>
                                <th style="border:1px solid #ccc; padding:8px; width:9%; text-align:center;">Prix de vente HTVA</th>
                                <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                            </tr>
                        </thead>
                        <tbody class="cadre-lines">
                            @foreach($section['lignes'] as $lineIndex => $ligne)
                                <tr>
                                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;">{{ $lineIndex + 1 }}</td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <textarea name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][designation]" rows="3" class="form-control wizard-input" style="font-size:0.9rem; min-height:65px;">{{ $ligne['designation'] ?? '' }}</textarea>
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][unite]" class="form-control wizard-input unite-input" value="{{ $ligne['unite'] ?? '' }}" style="font-size:0.95rem; height:45px;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" inputmode="decimal" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][total_materiel]" class="form-control wizard-input total-materiel-input" value="{{ $ligne['total_materiel'] ?? '' }}" data-section="{{ $sectionIndex }}" data-line="{{ $lineIndex }}" style="height:45px; color:#0f172a;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" inputmode="decimal" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][location_amort]" class="form-control wizard-input location-amort-input" value="{{ $ligne['location_amort'] ?? '' }}" data-section="{{ $sectionIndex }}" data-line="{{ $lineIndex }}" style="height:45px; color:#0f172a;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" inputmode="decimal" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][matiere_frais]" class="form-control wizard-input matiere-frais-input" value="{{ $ligne['matiere_frais'] ?? '' }}" data-section="{{ $sectionIndex }}" data-line="{{ $lineIndex }}" style="height:45px; color:#0f172a;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" inputmode="decimal" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][main_oeuvre]" class="form-control wizard-input main-oeuvre-input" value="{{ $ligne['main_oeuvre'] ?? '' }}" data-section="{{ $sectionIndex }}" data-line="{{ $lineIndex }}" style="height:45px; color:#0f172a;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" step="0.01" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][deborse_sec]" class="form-control wizard-input deborse-sec-input" value="{{ $ligne['deborse_sec'] ?? '' }}" readonly style="background:#f0f0f0; cursor:not-allowed; height:45px;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="number" step="0.0001" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][coef_c1]" class="form-control wizard-input coef-c1-input" value="{{ $ligne['coef_c1'] ?? '' }}" readonly style="background:#f0f0f0; cursor:not-allowed; height:45px;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][coef_k]" class="form-control wizard-input coef-k-input" value="{{ $ligne['coef_k'] ?? '' }}" readonly style="background:#f0f0f0; cursor:not-allowed; height:45px; color:#0f172a; -webkit-text-fill-color: #0f172a !important; opacity:1 !important;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                        <input type="text" inputmode="decimal" name="sections[{{ $sectionIndex }}][lignes][{{ $lineIndex }}][prix_vente_htva]" class="form-control wizard-input" value="{{ $ligne['prix_vente_htva'] ?? '' }}" style="height:45px; color:#0f172a !important; -webkit-text-fill-color: #0f172a !important; background:#fff !important; opacity:1 !important;">
                                    </td>
                                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                                        <button type="button" class="btn btn-sm btn-danger removeCadreLine" style="padding:0.25rem 0.45rem;">×</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary addCadreLine">Ajouter une ligne</button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            const sectionsContainer = document.getElementById('cadreSections');
            const addSectionButton = document.getElementById('addCadreSection');

            function updateSectionIndexes() {
                Array.from(sectionsContainer.querySelectorAll('.cadre-section')).forEach((sectionEl, sectionIndex) => {
                    sectionEl.dataset.sectionIndex = sectionIndex;
                    sectionEl.querySelectorAll('input, textarea').forEach((field) => {
                        field.name = field.name.replace(/sections\[\d+\]/, `sections[${sectionIndex}]`);
                    });
                    updateLineIndexes(sectionEl, sectionIndex);
                });
            }

            function updateLineIndexes(sectionEl, sectionIndex) {
                Array.from(sectionEl.querySelectorAll('.cadre-lines tr')).forEach((row, lineIndex) => {
                    row.cells[0].textContent = lineIndex + 1;
                    row.querySelectorAll('input, textarea').forEach((field) => {
                        if (!field.name) {
                            return;
                        }
                        field.name = field.name.replace(/sections\[\d+\]\[lignes\]\[\d+\]/g, `sections[${sectionIndex}][lignes][${lineIndex}]`);
                    });
                });
            }

            function createLineRow(sectionIndex) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border:1px solid #ccc; padding:8px; text-align:center; vertical-align:top;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><textarea name="sections[${sectionIndex}][lignes][0][designation]" rows="2" class="form-control wizard-input"></textarea></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" name="sections[${sectionIndex}][lignes][0][unite]" class="form-control wizard-input unite-input" style="font-size:0.95rem; height:38px;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" inputmode="decimal" name="sections[${sectionIndex}][lignes][0][total_materiel]" class="form-control wizard-input total-materiel-input" data-section="${sectionIndex}" data-line="0" style="color:#0f172a;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" inputmode="decimal" name="sections[${sectionIndex}][lignes][0][location_amort]" class="form-control wizard-input location-amort-input" data-section="${sectionIndex}" data-line="0" style="color:#0f172a;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" inputmode="decimal" name="sections[${sectionIndex}][lignes][0][matiere_frais]" class="form-control wizard-input matiere-frais-input" data-section="${sectionIndex}" data-line="0" style="color:#0f172a;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" inputmode="decimal" name="sections[${sectionIndex}][lignes][0][main_oeuvre]" class="form-control wizard-input main-oeuvre-input" data-section="${sectionIndex}" data-line="0" style="color:#0f172a;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="number" step="0.01" name="sections[${sectionIndex}][lignes][0][deborse_sec]" class="form-control wizard-input deborse-sec-input" readonly style="background:#f0f0f0; cursor:not-allowed;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="number" step="0.0001" name="sections[${sectionIndex}][lignes][0][coef_c1]" class="form-control wizard-input coef-c1-input" readonly style="background:#f0f0f0; cursor:not-allowed;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" name="sections[${sectionIndex}][lignes][0][coef_k]" class="form-control wizard-input coef-k-input" readonly style="background:#f0f0f0; cursor:not-allowed; color:#0f172a; -webkit-text-fill-color: #0f172a !important; opacity:1 !important;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;"><input type="text" inputmode="decimal" name="sections[${sectionIndex}][lignes][0][prix_vente_htva]" class="form-control wizard-input" style="height:38px; color:#0f172a; -webkit-text-fill-color: #0f172a !important; background:#fff !important; opacity:1 !important;"></td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;"><button type="button" class="btn btn-sm btn-danger removeCadreLine" style="padding:0.25rem 0.45rem;">×</button></td>
                `;
                return row;
            }

            function calculateFormulas(row) {
                const totalMaterielInput = row.querySelector('.total-materiel-input');
                const locationAmortInput = row.querySelector('.location-amort-input');
                const matiereFraisInput = row.querySelector('.matiere-frais-input');
                const mainOeuvreInput = row.querySelector('.main-oeuvre-input');
                const deborseSecInput = row.querySelector('.deborse-sec-input');
                const coefC1Input = row.querySelector('.coef-c1-input');
                const coefKInput = row.querySelector('.coef-k-input');

                const parseDecimal = (value) => {
                    if (typeof value !== 'string') {
                        return parseFloat(value) || 0;
                    }
                    const normalized = value.replace(/\s+/g, '').replace(',', '.');
                    return parseFloat(normalized) || 0;
                };

                if (totalMaterielInput && locationAmortInput && matiereFraisInput && mainOeuvreInput && deborseSecInput && coefC1Input && coefKInput) {
                    const total_materiel = parseDecimal(totalMaterielInput.value);
                    const location_amort = parseDecimal(locationAmortInput.value);
                    const matiere_frais = parseDecimal(matiereFraisInput.value);
                    const main_oeuvre = parseDecimal(mainOeuvreInput.value);

                    // Déboursé Sec = Total Matériel + Location Amort. + Matière Et Frais divers + Main d'oeuvre
                    const deborse_sec = total_materiel + location_amort + matiere_frais + main_oeuvre;
                    deborseSecInput.value = deborse_sec > 0 ? deborse_sec.toFixed(2) : '';

                    // C1 = coefficient frais généraux = (Déboursé Sec - Total Matériel) / Total Matériel (ratio)
                    const coef_c1 = total_materiel > 0 ? (deborse_sec - total_materiel) / total_materiel : 0;
                    coefC1Input.value = !Number.isNaN(coef_c1) ? coef_c1.toFixed(4) : '';

                    // Coefficient de vente k = 1 + C1
                    const coef_k = !Number.isNaN(coef_c1) ? (1 + coef_c1) : 0;
                    coefKInput.value = !Number.isNaN(coef_k) && coef_k > 0 ? coef_k.toFixed(4) : '';
                }
            }

            function createSection(sectionIndex) {
                const section = document.createElement('div');
                section.className = 'cadre-section';
                section.dataset.sectionIndex = sectionIndex;
                section.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                        <div style="flex:1;"><label class="wizard-label">Titre du tableau</label><input type="text" class="form-control wizard-input" name="sections[${sectionIndex}][titre]" placeholder="CADRE" value=""></div>
                        <div style="text-align:right;"><button type="button" class="btn btn-danger removeCadreSection" style="margin-top:24px;">Supprimer le tableau</button></div>
                    </div>
                    <div style="overflow-x:auto;"><table class="table" style="width:100%; border-collapse:collapse; font-size:12px;"><thead><tr>
                        <th style="border:1px solid #ccc; padding:8px; width:4%;">N°</th>
                        <th style="border:1px solid #ccc; padding:8px; width:28%;">DESCRIPTION</th>
                        <th style="border:1px solid #ccc; padding:8px; width:8%;">Unit</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">Total Materiel</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">Location Amort. Materiel</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">Matière Et Frais divers</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">Main d'oeuvre</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">Déboursé Sec</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">C1=coef.frais</th>
                        <th style="border:1px solid #ccc; padding:8px; width:6%;">Coef.de vente k=100/(100-C)</th>
                        <th style="border:1px solid #ccc; padding:8px; width:10%;">Prix de vente HTVA en chiffre</th>
                        <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                    </tr></thead><tbody class="cadre-lines"></tbody></table></div><div class="mb-3"><button type="button" class="btn btn-secondary addCadreLine">Ajouter une ligne</button></div>`;
                return section;
            }

            addSectionButton.addEventListener('click', function () {
                const newSectionIndex = sectionsContainer.querySelectorAll('.cadre-section').length;
                const newSection = createSection(newSectionIndex);
                sectionsContainer.appendChild(newSection);
                attachSectionEvents(newSection, newSectionIndex);
                updateSectionIndexes();
            });

            function attachSectionEvents(sectionEl, sectionIndex) {
                const addLineButton = sectionEl.querySelector('.addCadreLine');
                const removeSectionButton = sectionEl.querySelector('.removeCadreSection');

                addLineButton.addEventListener('click', function () {
                    const tableBody = sectionEl.querySelector('.cadre-lines');
                    const newRow = createLineRow(sectionIndex);
                    tableBody.appendChild(newRow);
                    attachLineEvents(sectionEl, sectionIndex);
                    updateLineIndexes(sectionEl, sectionIndex);
                });

                removeSectionButton.addEventListener('click', function () {
                    sectionEl.remove();
                    updateSectionIndexes();
                });

                attachLineEvents(sectionEl, sectionIndex);
            }

            function attachLineEvents(sectionEl, sectionIndex) {
                sectionEl.querySelectorAll('.removeCadreLine').forEach(btn => btn.addEventListener('click', function (e) {
                    e.target.closest('tr').remove();
                    updateLineIndexes(sectionEl, sectionIndex);
                }));

                // Attach calculation formulas to input fields
                sectionEl.querySelectorAll('.total-materiel-input, .location-amort-input, .matiere-frais-input, .main-oeuvre-input').forEach(input => {
                    input.addEventListener('input', function () {
                        const row = this.closest('tr');
                        calculateFormulas(row);
                    });
                });
            }

            // Attach existing sections
            Array.from(document.querySelectorAll('.cadre-section')).forEach((secEl, idx) => {
                attachSectionEvents(secEl, idx);
                // Initialize calculations for existing rows
                secEl.querySelectorAll('tbody tr').forEach(row => {
                    calculateFormulas(row);
                });
            });
            updateSectionIndexes();

            if (window.DaoTableImport) {
                window.DaoTableImport.setup({
                    buttonId: 'importTableauBtn',
                    statusId: 'importTableauStatus',
                    sectionsContainerId: 'cadreSections',
                    sectionSelector: '.cadre-section',
                    linesSelector: '.cadre-lines',
                    addLineButtonSelector: '.addCadreLine',
                    importUrl: '{{ route('dossiers.importTableau') }}',
                    fieldSynonyms: {
                        designation: ['description', 'designation', 'objet'],
                        unite: ['unit', 'unite'],
                        total_materiel: ['total materiel'],
                        location_amort: ['location amort'],
                        matiere_frais: ['matiere et frais', 'matiere frais'],
                        main_oeuvre: ['main d oeuvre', 'main oeuvre'],
                        prix_vente_htva: ['prix de vente htva', 'prix vente htva'],
                    },
                });
            }
        })();
    </script>
</div>
