<div id="formulaire-mtc-fin-3-5-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $stored = [];
        if ($doc && !empty($doc->content)) {
            $decoded = json_decode($doc->content, true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $intitules = old('intitule', $stored['intitule'] ?? []);
        $autorites = old('autorite_contact', $stored['autorite_contact'] ?? []);
        $valeurs = old('valeur_restante', $stored['valeur_restante'] ?? []);
        $dates = old('date_achevement', $stored['date_achevement'] ?? []);
        $montants = old('montant_mensuel', $stored['montant_mensuel'] ?? []);

        $rows = max(6, max(count($intitules), count($autorites), count($valeurs), count($dates), count($montants)));
        while (count($intitules) < $rows) { $intitules[] = ''; }
        while (count($autorites) < $rows) { $autorites[] = ''; }
        while (count($valeurs) < $rows) { $valeurs[] = ''; }
        while (count($dates) < $rows) { $dates[] = ''; }
        while (count($montants) < $rows) { $montants[] = ''; }

        $nom_signataire = old('nom_signataire', $stored['nom_signataire'] ?? '');
        $capacite_signataire = old('capacite_signataire', $stored['capacite_signataire'] ?? '');
        $signature_text = old('signature_text', $stored['signature_text'] ?? '');
        $signature_pouvoir = old('signature_pouvoir', $stored['signature_pouvoir'] ?? '');
        $signature_date = old('signature_date', $stored['signature_date'] ?? '');
    @endphp

    <div class="wizard-field" style="margin-bottom:12px;">
        <label class="wizard-label">Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours</label>
        <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.4; max-width:820px;">Complétez les informations pour les marchés en cours.</p>
    </div>

    <div style="overflow-x:auto; margin-bottom:12px;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:center; width:5%;">#</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:left; width:30%;">Intitulé du marché</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:left; width:25%;">Autorité contractante / contact</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:right; width:15%;">Valeur restante (FCFA)</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:center; width:12%;">Date d'achèvement prévue</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:right; width:13%;">Montant moyen mensuel (FCFA/mois)</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody id="mtc35-rows">
                @foreach(range(0, $rows - 1) as $i)
                    <tr>
                        <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">{{ $loop->iteration }}.</td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="intitule[]" value="{{ $intitules[$i] ?? '' }}" style="width:100%;"></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="autorite_contact[]" value="{{ $autorites[$i] ?? '' }}" style="width:100%;"></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="valeur_restante[]" value="{{ $valeurs[$i] ?? '' }}" style="width:100%; text-align:right;"></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="date" class="form-control wizard-input" name="date_achevement[]" value="{{ $dates[$i] ?? '' }}" style="width:100%;"></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="montant_mensuel[]" value="{{ $montants[$i] ?? '' }}" style="width:100%; text-align:right;"></td>
                        <td style="padding-left:8px; vertical-align:middle; width:40px;"><button type="button" class="btn-remove-row" style="background:#ef4444; color:#fff; border:none; padding:4px 8px; cursor:pointer;">Suppr</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:12px; display:flex; gap:12px; align-items:flex-start;">
        <div style="flex:1;">
            <button type="button" id="mtc35-add-row" style="background:#0ea5a4; color:#fff; border:none; padding:8px 12px; cursor:pointer;">Ajouter une ligne</button>
        </div>
    </div>

</div>

<script>
(function(){
    function renumberRows() {
        var rows = document.querySelectorAll('#mtc35-rows tr');
        rows.forEach(function(tr, idx){
            var firstCell = tr.querySelector('td');
            if(firstCell) {
                var textNode = firstCell.childNodes[0];
                if(textNode && textNode.nodeType === Node.TEXT_NODE) {
                    textNode.nodeValue = (idx+1) + '. ';
                }
            }
        });
    }

    var container = document.getElementById('mtc35-rows');
    var addBtn = document.getElementById('mtc35-add-row');
    if(addBtn && container) {
        addBtn.addEventListener('click', function(){
            var tr = document.createElement('tr');
            tr.innerHTML = '<td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">1.</td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="intitule[]" value="" style="width:100%;"></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="autorite_contact[]" value="" style="width:100%;"></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="valeur_restante[]" value="" style="width:100%; text-align:right;"></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="date" class="form-control wizard-input" name="date_achevement[]" value="" style="width:100%;"></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="montant_mensuel[]" value="" style="width:100%; text-align:right;"></td>' +
                           '<td style="padding-left:8px; vertical-align:middle; width:40px;"><button type="button" class="btn-remove-row" style="background:#ef4444; color:#fff; border:none; padding:4px 8px; cursor:pointer;">Suppr</button></td>';
            container.appendChild(tr);
            attachRemove(tr.querySelector('.btn-remove-row'));
            renumberRows();
        });
    }

    function attachRemove(btn) {
        if(!btn) return;
        btn.addEventListener('click', function(e){
            var row = e.target.closest('tr');
            if(row) { row.remove(); renumberRows(); }
        });
    }

    document.querySelectorAll('#mtc35-rows .btn-remove-row').forEach(function(b){ attachRemove(b); });
})();
</script>
