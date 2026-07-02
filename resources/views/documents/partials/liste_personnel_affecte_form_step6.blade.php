<div id="liste-personnel-step6">
    <div class="wizard-field" style="margin-bottom: 18px;">
        <p class="wizard-label">Liste du personnel affecté à l'exécution du marché</p>
        <p style="font-size:0.95rem;color:#475569;">Ajoutez le personnel avec le poste et le nom. Vous pouvez ajouter jusqu'à 8 lignes.</p>
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

    <div id="personnelRows" style="display:grid; gap:12px;">
        @foreach(range(1, max(6, count($existingPersonnel))) as $index)
            @php
                $row = $existingPersonnel[$index - 1] ?? ['poste' => '', 'nom' => ''];
            @endphp
            <div class="card mb-3 personnel-row" style="padding:16px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="wizard-label" for="personnel_{{ $index }}_poste">Poste</label>
                        <input type="text" id="personnel_{{ $index }}_poste" name="personnel[{{ $index }}][poste]" class="form-control wizard-input" value="{{ old('personnel.' . $index . '.poste', $row['poste']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="wizard-label" for="personnel_{{ $index }}_nom">Nom et prénom</label>
                        <input type="text" id="personnel_{{ $index }}_nom" name="personnel[{{ $index }}][nom]" class="form-control wizard-input" value="{{ old('personnel.' . $index . '.nom', $row['nom']) }}">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-primary-custom" id="addPersonnelRow" style="margin-bottom:16px;">
        Ajouter une ligne
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rowsContainer = document.getElementById('personnelRows');
        const addButton = document.getElementById('addPersonnelRow');

        function getNextIndex() {
            return rowsContainer.querySelectorAll('.personnel-row').length + 1;
        }

        function createRow(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'card mb-3 personnel-row';
            wrapper.style.padding = '16px';
            wrapper.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="wizard-label" for="personnel_${index}_poste">Poste</label>
                        <input type="text" id="personnel_${index}_poste" name="personnel[${index}][poste]" class="form-control wizard-input">
                    </div>
                    <div class="col-md-6">
                        <label class="wizard-label" for="personnel_${index}_nom">Nom et prénom</label>
                        <input type="text" id="personnel_${index}_nom" name="personnel[${index}][nom]" class="form-control wizard-input">
                    </div>
                </div>
            `;
            return wrapper;
        }

        addButton.addEventListener('click', function() {
            const currentRows = rowsContainer.querySelectorAll('.personnel-row');
            if (currentRows.length >= 12) {
                return;
            }
            rowsContainer.appendChild(createRow(currentRows.length + 1));
        });

        // Mark existing rows
        rowsContainer.querySelectorAll('.card.mb-3').forEach(function(card) {
            card.classList.add('personnel-row');
        });
    });
</script>
