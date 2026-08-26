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
            @foreach(App\Models\Template::where('type', 'libre')->get() as $t)
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
            <button type="button" id="libreImportBtn" class="btn btn-outline-secondary btn-sm">Importer un tableau depuis un fichier (Excel/CSV)</button>
            <input type="file" id="libreImportFile" accept=".csv,.txt,.xlsx" style="display:none;">
            <span id="libreImportStatus" style="font-size:0.8rem;"></span>
        </div>
        <small style="color:#64748b;">Donnez un nom à chaque colonne directement dans son en-tête, ou importez un fichier Excel/CSV pour créer un tableau automatiquement à partir de ses colonnes et lignes.</small>
    </div>

    <script>
        (function(){
            const container = document.getElementById('libreTablesContainer');
            const initialTableaux = @json($libreTableaux ?: []);

            function reindexAll() {
                Array.from(container.querySelectorAll('.libre-tableau-block')).forEach((block, t) => {
                    const titreInput = block.querySelector('.libre-titre-input');
                    if (titreInput) titreInput.name = `tableaux[${t}][titre]`;

                    block.querySelectorAll('.libre-header-row th.libre-col input').forEach((input, c) => {
                        input.name = `tableaux[${t}][colonnes][${c}]`;
                    });

                    Array.from(block.querySelectorAll('.libre-body tr')).forEach((row, r) => {
                        row.querySelectorAll('td.libre-cell input').forEach((input, c) => {
                            input.name = `tableaux[${t}][lignes][${r}][${c}]`;
                        });
                    });
                });
            }

            function columnCount(block) {
                return block.querySelectorAll('.libre-header-row th.libre-col').length;
            }

            function addCellToRow(row, value) {
                const td = document.createElement('td');
                td.className = 'libre-cell';
                td.style.cssText = 'border:1px solid #ccc; padding:6px;';
                td.innerHTML = `<input type="text" class="form-control wizard-input" value="${value ? String(value).replace(/"/g, '&quot;') : ''}">`;
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
                    const v = values && values[i] ? String(values[i]).replace(/"/g, '&quot;') : '';
                    td.innerHTML = `<input type="text" class="form-control wizard-input" value="${v}">`;
                    row.appendChild(td);
                }
                const actionTd = document.createElement('td');
                actionTd.style.cssText = 'border:1px solid #ccc; padding:6px; text-align:center;';
                actionTd.innerHTML = '<button type="button" class="btn btn-sm btn-danger libreRemoveRow" style="padding:0.25rem 0.45rem;">×</button>';
                row.appendChild(actionTd);
                body.appendChild(row);
                reindexAll();
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

            // Import depuis un fichier Excel/CSV : crée un nouveau tableau à partir
            // des en-têtes et des lignes du fichier importé.
            const importBtn = document.getElementById('libreImportBtn');
            const importFile = document.getElementById('libreImportFile');
            const importStatus = document.getElementById('libreImportStatus');

            importBtn.addEventListener('click', () => importFile.click());

            importFile.addEventListener('change', async function(){
                const file = this.files[0];
                if (!file) return;
                importStatus.textContent = 'Import en cours...';
                const formData = new FormData();
                formData.append('fichier', file);
                try {
                    const res = await fetch('{{ route('dossiers.importTableau') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Erreur lors de l\'import');
                    }
                    const json = await res.json();
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
