<div id="liste-personnel-step6">
    <div class="wizard-field" style="margin-bottom: 12px;">
        <p class="wizard-label">Liste du personnel affecté à l'exécution du marché</p>
        <p style="font-size:0.95rem;color:#475569;">Saisissez la désignation du poste puis le nom. Utilisez le bouton pour ajouter/supprimer des lignes.</p>
    </div>

    @php
        $existingPersonnel = [];
        if (!empty($dossierDocument?->content)) {
            $decoded = json_decode($dossierDocument->content, true);
            if (is_array($decoded)) {
                $existingPersonnel = array_values(array_map(function ($row) {
                    return [
                        'poste' => $row['poste'] ?? '',
                        'nom' => $row['nom'] ?? '',
                    ];
                }, $decoded));
            }
        }
    @endphp

    <div id="personnelRows" style="display:flex; flex-direction:column; gap:12px;">
        @foreach(range(1, max(3, count($existingPersonnel))) as $index)
            @php
                $row = $existingPersonnel[$index - 1] ?? ['poste' => '', 'nom' => ''];
            @endphp
            <div class="personnel-row-box" style="border:1px solid #000; padding:10px; display:flex; gap:12px; align-items:flex-start;">
                <div style="width:40px; text-align:center; font-weight:700;">{{ $index }}.</div>
                <div style="flex:1;">
                    <div style="font-weight:700;">
                        Désignation du poste :
                        <input type="text" name="personnel[{{ $index }}][poste]" value="{{ old('personnel.' . $index . '.poste', $row['poste']) }}" class="form-control" style="display:inline-block; width:70%; border:none; border-bottom:1px solid #000; padding:2px 6px; margin-left:8px;">
                    </div>
                    <div style="margin-top:8px; font-style:italic; color:#333;">
                        Nom :
                        <input type="text" name="personnel[{{ $index }}][nom]" value="{{ old('personnel.' . $index . '.nom', $row['nom']) }}" class="form-control" style="display:inline-block; width:60%; border:none; border-bottom:1px solid #000; padding:2px 6px; margin-left:8px;">
                    </div>
                </div>
                <div style="width:80px; display:flex; flex-direction:column; gap:6px; align-items:center;">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-personnel">Supprimer</button>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top:10px; display:flex; gap:8px;">
        <button type="button" class="btn btn-primary-custom" id="addPersonnelRow">Ajouter une ligne</button>
        <button type="button" class="btn btn-secondary" id="resetPersonnelRows">Réinitialiser</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rowsContainer = document.getElementById('personnelRows');
        const addButton = document.getElementById('addPersonnelRow');
        const resetButton = document.getElementById('resetPersonnelRows');

        function refreshRemoveHandlers() {
            rowsContainer.querySelectorAll('.remove-personnel').forEach(btn => {
                btn.removeEventListener('click', onRemoveClick);
                btn.addEventListener('click', onRemoveClick);
            });
        }

        function onRemoveClick(e) {
            const row = e.target.closest('.personnel-row-box');
            if (!row) return;
            row.remove();
            renumberRows();
        }

        function renumberRows() {
            const rows = rowsContainer.querySelectorAll('.personnel-row-box');
            rows.forEach((r, i) => {
                const idx = i + 1;
                r.querySelector('div[style*="width:40px"]').textContent = idx + '.';
                // update input names
                const poste = r.querySelector('input[name^="personnel"][name$="[poste]"]');
                const nom = r.querySelector('input[name^="personnel"][name$="[nom]"]');
                if (poste) poste.name = `personnel[${idx}][poste]`;
                if (nom) nom.name = `personnel[${idx}][nom]`;
            });
        }

        function createRow(index, poste = '', nom = '') {
            const wrapper = document.createElement('div');
            wrapper.className = 'personnel-row-box';
            wrapper.style.cssText = 'border:1px solid #000; padding:10px; display:flex; gap:12px; align-items:flex-start;';
            wrapper.innerHTML = `
                <div style="width:40px; text-align:center; font-weight:700;">${index}.</div>
                <div style="flex:1;">
                    <div style="font-weight:700;">Désignation du poste : <input type="text" name="personnel[${index}][poste]" value="${poste}" class="form-control" style="display:inline-block; width:70%; border:none; border-bottom:1px solid #000; padding:2px 6px; margin-left:8px;"></div>
                    <div style="margin-top:8px; font-style:italic; color:#333;">Nom : <input type="text" name="personnel[${index}][nom]" value="${nom}" class="form-control" style="display:inline-block; width:60%; border:none; border-bottom:1px solid #000; padding:2px 6px; margin-left:8px;"></div>
                </div>
                <div style="width:80px; display:flex; flex-direction:column; gap:6px; align-items:center;"><button type="button" class="btn btn-sm btn-outline-danger remove-personnel">Supprimer</button></div>
            `;
            return wrapper;
        }

        addButton.addEventListener('click', function() {
            const currentRows = rowsContainer.querySelectorAll('.personnel-row-box');
            const next = currentRows.length + 1;
            rowsContainer.appendChild(createRow(next));
            refreshRemoveHandlers();
        });

        resetButton.addEventListener('click', function() {
            // remove all and add three empty rows
            rowsContainer.innerHTML = '';
            for (let i = 1; i <= 3; i++) {
                rowsContainer.appendChild(createRow(i));
            }
            refreshRemoveHandlers();
        });

        // attach remove handlers initially
        refreshRemoveHandlers();
    });
</script>
