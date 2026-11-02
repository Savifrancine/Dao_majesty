<div id="document-libre-step6">
    @php
        $libreValues = [];
        if (!empty($dossierDocument?->content)) {
            $decoded = json_decode($dossierDocument->content, true);
            if (is_array($decoded)) {
                $libreValues = $decoded;
            }
        }
        $libreTexte = old('texte', $libreValues['texte'] ?? '');
        $libreTableaux = $libreValues['tableaux'] ?? [];
    @endphp

    <div class="mb-3">
        <label class="wizard-label">Choisir un modèle</label>
        <select id="libre-template-select" class="form-control wizard-input">
            <option value="">-- Aucun --</option>
            @foreach(App\Models\Template::whereRaw('LOWER(TRIM(type)) = ?', ['libre'])->get() as $t)
                <option value="{{ $t->id }}">{{ $t->nom }}</option>
            @endforeach
        </select>
        <small style="color:#64748b;">Sélectionner un modèle remplace le texte ci-dessous par son contenu, modifiable ensuite. Les modèles se gèrent dans la page "Modèles".</small>
    </div>

    <div class="mb-3">
        <label class="wizard-label" for="libre_texte">Contenu</label>
        <textarea name="texte" id="libre_texte" class="form-control wizard-input" rows="10" placeholder="Saisir le texte du document...">{{ $libreTexte }}</textarea>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Tableaux (facultatif)</label>
        <div id="libreTablesContainer"></div>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:8px;">
            <button type="button" id="libreAddTable" class="btn btn-secondary btn-sm">+ Ajouter un tableau</button>
            <button type="button" id="libreImportBtn" class="btn btn-outline-secondary btn-sm">Importer un tableau depuis un fichier (Excel/Word/PDF/image)</button>
            <input type="file" id="libreImportFile" accept=".csv,.txt,.xlsx,.docx,.pdf,.png,.jpg,.jpe,.jpeg,.webp,.bmp,image/*,application/pdf" style="display:none;">
            <span id="libreImportStatus" style="font-size:0.8rem;"></span>
        </div>
        <small style="color:#64748b;">Donnez un nom à chaque colonne directement dans son en-tête, ou importez un fichier Excel, Word, PDF ou une image de tableau pour créer un tableau automatiquement. Après l'import, vous pourrez aussi ajouter les désignations déjà présentes dans le dossier. Vérifiez les données reconnues avant l'enregistrement.</small>
    </div>

    <div class="mb-3" id="librePaysageWrapper" style="display:none;">
        <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
            <input type="checkbox" name="paysage" value="1" id="libre_paysage" {{ old('paysage', $dossierDocument?->paysage) ? 'checked' : '' }}>
            Afficher ce document en orientation paysage dans le PDF
        </label>
        <small style="color:#64748b;">Un de vos tableaux comporte plus de 3 colonnes : cochez cette case si vous préférez l'afficher en largeur (paysage) plutôt qu'en hauteur (portrait).</small>
    </div>

    <script>
        (function(){
            const container = document.getElementById('libreTablesContainer');
            const initialTableaux = @json($libreTableaux ?: []);
            const dossierDesignations = @json($dossierDesignations ?? []);

            function reindexAll() {
                Array.from(container.querySelectorAll('.libre-tableau-block')).forEach((block, t) => {
                    const titreInput = block.querySelector('.libre-titre-input');
                    if (titreInput) titreInput.name = `tableaux[${t}][titre]`;

                    block.querySelectorAll('.libre-header-row th.libre-col input').forEach((input, c) => {
                        input.name = `tableaux[${t}][colonnes][${c}]`;
                    });

                    Array.from(block.querySelectorAll('.libre-body tr')).forEach((row, r) => {
                        row.querySelectorAll('td.libre-cell textarea').forEach((input, c) => {
                            input.name = `tableaux[${t}][lignes][${r}][${c}]`;
                        });
                    });
                });
                updatePaysageVisibility();
            }

            function columnCount(block) {
                return block.querySelectorAll('.libre-header-row th.libre-col').length;
            }

            function updatePaysageVisibility() {
                const wrapper = document.getElementById('librePaysageWrapper');
                const maxColumns = Array.from(container.querySelectorAll('.libre-tableau-block'))
                    .reduce((max, block) => Math.max(max, columnCount(block)), 0);
                const shouldShow = maxColumns > 3;
                wrapper.style.display = shouldShow ? '' : 'none';
                if (!shouldShow) {
                    document.getElementById('libre_paysage').checked = false;
                }
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ? String(value) : '';
                return div.innerHTML;
            }

            function addCellToRow(row, value) {
                const td = document.createElement('td');
                td.className = 'libre-cell';
                td.style.cssText = 'border:1px solid #ccc; padding:6px;';
                td.innerHTML = `<textarea class="form-control wizard-input" rows="1" style="resize:vertical;">${escapeHtml(value)}</textarea>`;
                row.insertBefore(td, row.lastElementChild);
            }

            function addColumn(block, label) {
                const headerRow = block.querySelector('.libre-header-row');
                const th = document.createElement('th');
                th.className = 'libre-col';
                th.style.cssText = 'border:1px solid #ccc; padding:6px; background:#f3f4f6;';
                th.innerHTML = `<div style="display:flex; gap:4px; align-items:center;">
                    <input type="text" class="form-control wizard-input" style="font-weight:700;" placeholder="Nom de la colonne" value="${label ? String(label).replace(/"/g, '&quot;') : ''}">
                    <button type="button" class="btn btn-sm btn-danger libreRemoveColumn" style="padding:0.25rem 0.45rem;">×</button>
                </div>`;
                if (!headerRow.querySelector('th.libre-actions')) {
                    const actionsTh = document.createElement('th');
                    actionsTh.className = 'libre-actions';
                    actionsTh.style.cssText = 'border:1px solid #ccc; padding:6px; width:5%;';
                    headerRow.appendChild(actionsTh);
                }
                headerRow.insertBefore(th, headerRow.lastElementChild);

                Array.from(block.querySelectorAll('.libre-body tr')).forEach((row) => addCellToRow(row, ''));

                reindexAll();
            }

            function addRow(block, values) {
                const body = block.querySelector('.libre-body');
                const row = document.createElement('tr');
                const count = columnCount(block);
                for (let i = 0; i < count; i++) {
                    const td = document.createElement('td');
                    td.className = 'libre-cell';
                    td.style.cssText = 'border:1px solid #ccc; padding:6px;';
                    const v = values && values[i] ? values[i] : '';
                    td.innerHTML = `<textarea class="form-control wizard-input" rows="1" style="resize:vertical;">${escapeHtml(v)}</textarea>`;
                    row.appendChild(td);
                }
                const actionTd = document.createElement('td');
                actionTd.style.cssText = 'border:1px solid #ccc; padding:6px; text-align:center;';
                actionTd.innerHTML = '<button type="button" class="btn btn-sm btn-danger libreRemoveRow" style="padding:0.25rem 0.45rem;">×</button>';
                row.appendChild(actionTd);
                body.appendChild(row);
                reindexAll();
            }

            function normalizeDesignation(value) {
                return String(value || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, ' ')
                    .trim();
            }

            function findDesignationColumn(block) {
                return Array.from(block.querySelectorAll('.libre-header-row th.libre-col input'))
                    .findIndex((input) => /(^|\s)(designation|description|article|produit|service|objet)(\s|$)/.test(normalizeDesignation(input.value)));
            }

            function addDossierDesignations(block, designations) {
                let columnIndex = findDesignationColumn(block);
                const createdColumn = columnIndex === -1;
                if (createdColumn) {
                    addColumn(block, 'Désignation');
                    columnIndex = columnCount(block) - 1;
                }

                const existing = new Set();
                block.querySelectorAll('.libre-body tr').forEach((row) => {
                    const value = row.querySelectorAll('td.libre-cell textarea')[columnIndex]?.value;
                    const normalized = normalizeDesignation(value);
                    if (normalized) existing.add(normalized);
                });

                let added = 0;
                Array.from(new Set(designations.map((value) => String(value || '').trim()).filter(Boolean)))
                    .forEach((designation) => {
                        const normalized = normalizeDesignation(designation);
                        if (!normalized || existing.has(normalized)) return;

                        const emptyRow = Array.from(block.querySelectorAll('.libre-body tr')).find((row) =>
                            Array.from(row.querySelectorAll('td.libre-cell textarea')).every((input) => !input.value.trim())
                        );
                        if (emptyRow) {
                            emptyRow.querySelectorAll('td.libre-cell textarea')[columnIndex].value = designation;
                        } else {
                            const values = Array(columnCount(block)).fill('');
                            values[columnIndex] = designation;
                            addRow(block, values);
                        }
                        existing.add(normalized);
                        added++;
                    });

                return { added, createdColumn };
            }

            function createTableBlock(titre, colonnes, lignes) {
                const block = document.createElement('div');
                block.className = 'libre-tableau-block';
                block.style.cssText = 'border:1px solid #ccc; padding:12px; margin-bottom:16px;';
                block.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                        <div style="flex:1;">
                            <label class="wizard-label">Titre du tableau (facultatif)</label>
                            <input type="text" class="form-control wizard-input libre-titre-input" placeholder="Ex: Liste des équipements" value="${titre ? String(titre).replace(/"/g, '&quot;') : ''}">
                        </div>
                        <button type="button" class="btn btn-danger btn-sm libreRemoveTable" style="margin-top:24px;">Supprimer ce tableau</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead><tr class="libre-header-row"></tr></thead>
                            <tbody class="libre-body"></tbody>
                        </table>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:8px;">
                        <button type="button" class="btn btn-secondary btn-sm libreAddColumn">+ Colonne</button>
                        <button type="button" class="btn btn-secondary btn-sm libreAddRow">+ Ligne</button>
                        <button type="button" class="btn btn-outline-primary btn-sm libreBringDesignations">Ramener les désignations du dossier</button>
                    </div>
                `;
                container.appendChild(block);

                const cols = (colonnes && colonnes.length > 0) ? colonnes : ['', ''];
                cols.forEach((c) => addColumn(block, c));

                if (lignes && lignes.length > 0) {
                    lignes.forEach((l) => addRow(block, l));
                } else {
                    addRow(block, []);
                }

                reindexAll();
                return block;
            }

            document.getElementById('libreAddTable').addEventListener('click', () => createTableBlock('', [], []));

            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('libreRemoveTable')) {
                    e.target.closest('.libre-tableau-block').remove();
                    reindexAll();
                    return;
                }
                if (e.target.classList.contains('libreAddColumn')) {
                    addColumn(e.target.closest('.libre-tableau-block'), '');
                    return;
                }
                if (e.target.classList.contains('libreAddRow')) {
                    addRow(e.target.closest('.libre-tableau-block'), []);
                    return;
                }
                if (e.target.classList.contains('libreBringDesignations')) {
                    if (dossierDesignations.length === 0) {
                        importStatus.textContent = 'Aucune désignation existante n’a été trouvée dans le dossier.';
                        return;
                    }
                    if (!confirm('Voulez-vous ramener dans ce tableau les désignations déjà présentes dans le dossier ?')) {
                        return;
                    }
                    const result = addDossierDesignations(e.target.closest('.libre-tableau-block'), dossierDesignations);
                    importStatus.textContent = result.added > 0
                        ? `${result.added} désignation(s) ajoutée(s) depuis le dossier.${result.createdColumn ? ' Une colonne « Désignation » a été créée.' : ''}`
                        : 'Toutes les désignations du dossier sont déjà présentes dans ce tableau.';
                    return;
                }
                if (e.target.classList.contains('libreRemoveColumn')) {
                    const block = e.target.closest('.libre-tableau-block');
                    const th = e.target.closest('th.libre-col');
                    const index = Array.from(block.querySelectorAll('th.libre-col')).indexOf(th);
                    th.remove();
                    Array.from(block.querySelectorAll('.libre-body tr')).forEach((row) => {
                        const cells = row.querySelectorAll('td.libre-cell');
                        if (cells[index]) cells[index].remove();
                    });
                    reindexAll();
                    return;
                }
                if (e.target.classList.contains('libreRemoveRow')) {
                    e.target.closest('tr').remove();
                    reindexAll();
                }
            });

            // Initialisation à partir des valeurs existantes
            if (initialTableaux.length > 0) {
                initialTableaux.forEach((t) => createTableBlock(t.titre || '', t.colonnes || [], t.lignes || []));
            } else {
                createTableBlock('', [], []);
            }

            // L'import garde le traitement local des PDF et images et conserve
            // le parseur existant pour Excel, CSV et Word.
            const importBtn = document.getElementById('libreImportBtn');
            const importFile = document.getElementById('libreImportFile');
            const importStatus = document.getElementById('libreImportStatus');

            importBtn.addEventListener('click', () => importFile.click());

            importFile.addEventListener('change', async function(){
                const file = this.files[0];
                if (!file) return;
                importStatus.textContent = 'Import en cours...';
                try {
                    const json = await window.DaoTableImport.readFile(
                        file,
                        '{{ route('dossiers.importTableau') }}',
                        (message) => { importStatus.textContent = message; },
                    );
                    const headers = json.headers || [];
                    const rows = json.rows || [];
                    if (headers.length === 0) {
                        importStatus.textContent = 'Aucune colonne détectée dans le fichier.';
                        return;
                    }
                    createTableBlock(file.name.replace(/\.[^.]+$/, ''), headers, rows);
                    importStatus.textContent = `Tableau importé (${rows.length} ligne(s)).`;
                } catch (e) {
                    importStatus.textContent = e.message || 'Impossible d\'importer ce fichier.';
                } finally {
                    importFile.value = '';
                }
            });

            const templateSelect = document.getElementById('libre-template-select');
            const texteField = document.getElementById('libre_texte');
            templateSelect.addEventListener('change', async function(){
                const id = this.value;
                if (!id) return;
                try {
                    const res = await fetch(`{{ url('templates') }}/${id}/json`, { headers: {'X-Requested-With':'XMLHttpRequest'} });
                    if (!res.ok) throw new Error('Erreur');
                    const json = await res.json();
                    if (texteField.value.trim() !== '' && !confirm('Remplacer le contenu actuel par celui du modèle ?')) {
                        return;
                    }
                    texteField.value = json.content || '';
                } catch (e) {
                    alert('Impossible de charger le modèle');
                }
            });
        })();
    </script>
</div>
