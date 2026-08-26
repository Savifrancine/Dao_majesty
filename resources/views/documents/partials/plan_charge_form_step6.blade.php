<div id="plan-charge-step6">
    @php
        $existing = [];
        $candidate = '';
        if (isset($dossierDocument) && $dossierDocument && !empty($dossierDocument->content)) {
            $decoded = json_decode($dossierDocument->content, true);
            if (is_array($decoded)) {
                $existing = $decoded['plan_charge_rows'] ?? [];
                $candidate = $decoded['plan_charge_candidat'] ?? '';
            }
        }
    @endphp

    <div class="wizard-field">
        <label class="wizard-label">Nom du candidat</label>
        <input type="text" name="plan_charge_candidat" class="form-control wizard-input" value="{{ old('plan_charge_candidat', $candidate) }}">
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Plan de charge</strong></div>
        <div class="card-body" style="overflow-x:auto">
            <table class="table" style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#f3f4f6; font-weight:700;">
                        <th style="border:1px solid #000; padding:8px; width:4%">N°</th>
                        <th style="border:1px solid #000; padding:8px;">Nature des services*</th>
                        <th style="border:1px solid #000; padding:8px;">Montant HT et référence du marché</th>
                        <th style="border:1px solid #000; padding:8px;">Délai (mois)</th>
                        <th style="border:1px solid #000; padding:8px;">Date démarrage</th>
                        <th style="border:1px solid #000; padding:8px;">Date fin</th>
                        <th style="border:1px solid #000; padding:8px;">Taux exécution physique</th>
                        <th style="border:1px solid #000; padding:8px;">Taux exécution financière</th>
                        <th style="border:1px solid #000; padding:8px;">Autorité contractante / Bailleur</th>
                        <th style="border:1px solid #000; padding:8px;">Observations</th>
                    </tr>
                </thead>
                <tbody id="planChargeRows">
                    @if(count($existing) === 0)
                        @php $existing = [['nature' => 'Néant','marche' => 'Néant','delai'=>'Néant','date_demarrage'=>'Néant','date_fin'=>'Néant','taux_physique'=>'Néant','taux_financier'=>'Néant','autorite'=>'Néant','observations'=>'Néant']]; @endphp
                    @endif

                    @foreach($existing as $i => $row)
                        <tr class="plan-charge-row">
                            <td style="border:1px solid #000; padding:6px; text-align:center;">{{ $i + 1 }}</td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][nature]" value="{{ $row['nature'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][marche]" value="{{ $row['marche'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][delai]" value="{{ $row['delai'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][date_demarrage]" value="{{ $row['date_demarrage'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][date_fin]" value="{{ $row['date_fin'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][taux_physique]" value="{{ $row['taux_physique'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][taux_financier]" value="{{ $row['taux_financier'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][autorite]" value="{{ $row['autorite'] ?? '' }}" class="form-control" /></td>
                            <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[{{ $i }}][observations]" value="{{ $row['observations'] ?? '' }}" class="form-control" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex gap-2" style="margin-top:8px;">
                <button type="button" id="addPlanChargeRow" class="btn btn-ghost">Ajouter une ligne</button>
                <button type="button" id="removePlanChargeRow" class="btn btn-ghost">Supprimer la dernière</button>
                <button type="button" id="fillAllNeant" class="btn btn-ghost">Remplir tout NÉANT</button>
                <button type="button" id="clearAll" class="btn btn-ghost">Effacer tout</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        const addBtn = document.getElementById('addPlanChargeRow');
        const removeBtn = document.getElementById('removePlanChargeRow');
        const fillBtn = document.getElementById('fillAllNeant');
        const clearBtn = document.getElementById('clearAll');
        const tbody = document.getElementById('planChargeRows');

        function rowCount(){ return tbody.querySelectorAll('.plan-charge-row').length; }

        function createRow(index, data = {}){
            const tr = document.createElement('tr');
            tr.className = 'plan-charge-row';
            tr.innerHTML = `
                <td style="border:1px solid #000; padding:6px; text-align:center;">${index+1}</td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][nature]" value="${data.nature||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][marche]" value="${data.marche||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][delai]" value="${data.delai||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][date_demarrage]" value="${data.date_demarrage||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][date_fin]" value="${data.date_fin||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][taux_physique]" value="${data.taux_physique||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][taux_financier]" value="${data.taux_financier||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][autorite]" value="${data.autorite||''}" class="form-control" /></td>
                <td style="border:1px solid #000; padding:6px;"><input type="text" name="plan_charge_rows[${index}][observations]" value="${data.observations||''}" class="form-control" /></td>
            `;
            return tr;
        }

        addBtn.addEventListener('click', function(){
            const idx = rowCount();
            tbody.appendChild(createRow(idx));
            rebuildNames();
        });

        removeBtn.addEventListener('click', function(){
            const rows = tbody.querySelectorAll('.plan-charge-row');
            if(rows.length > 1){ rows[rows.length-1].remove(); rebuildNames(); }
        });

        fillBtn.addEventListener('click', function(){
            tbody.querySelectorAll('input').forEach(i => i.value = 'Néant');
        });

        clearBtn.addEventListener('click', function(){
            tbody.querySelectorAll('input').forEach(i => i.value = '');
        });

        function rebuildNames(){
            const rows = tbody.querySelectorAll('.plan-charge-row');
            rows.forEach((tr, idx) => {
                tr.querySelectorAll('input').forEach(input => {
                    const name = input.name.split(']')[0];
                    const field = input.name.match(/\[(.*?)\]$/)[1];
                    input.name = `plan_charge_rows[${idx}][${field}]`;
                });
                tr.children[0].textContent = idx+1;
            });
        }

    })();
</script>