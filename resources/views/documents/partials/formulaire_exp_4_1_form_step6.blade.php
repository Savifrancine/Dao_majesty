<div id="formulaire-exp-4-1-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $stored = [];
        if ($doc && !empty($doc->content)) {
            $decoded = json_decode($doc->content, true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $mois_depart = old('mois_depart', $stored['mois_depart'] ?? []);
        $mois_final = old('mois_final', $stored['mois_final'] ?? []);
        $identification = old('identification', $stored['identification'] ?? []);
        $role_candidat = old('role_candidat', $stored['role_candidat'] ?? []);

        // Complète automatiquement avec les marchés similaires déjà saisis dans le
        // Formulaire de qualification, pour ne pas avoir à les ressaisir ici — sans
        // écraser les lignes déjà présentes (celles-ci restent inchangées).
        if (!old('identification')) {
            $qualificationDoc = $dossier->documents->first(fn ($d) => $d->typeDocument && trim($d->typeDocument->nom) === 'Formulaire de qualification');
            if ($qualificationDoc && !empty($qualificationDoc->content)) {
                $qDecoded = json_decode($qualificationDoc->content, true);
                $qMarches = is_array($qDecoded) && is_array($qDecoded['marches'] ?? null) ? $qDecoded['marches'] : [];
                foreach ($qMarches as $m) {
                    $qIdent = \App\Support\MarcheIdentification::format($m['nom'] ?? '', $m['reference'] ?? '');
                    $qAnnee = trim($m['annee'] ?? '');
                    if ($qIdent === '' && $qAnnee === '') {
                        continue;
                    }
                    if ($qIdent !== '' && in_array($qIdent, $identification, true)) {
                        continue;
                    }
                    $mois_depart[] = $qAnnee;
                    $mois_final[] = '';
                    $identification[] = $qIdent;
                    $role_candidat[] = '';
                }
            }
        }

        $rows = max(6, max(count($mois_depart), count($mois_final), count($identification), count($role_candidat)));
        while (count($mois_depart) < $rows) { $mois_depart[] = ''; }
        while (count($mois_final) < $rows) { $mois_final[] = ''; }
        while (count($identification) < $rows) { $identification[] = ''; }
        while (count($role_candidat) < $rows) { $role_candidat[] = ''; }
    @endphp

    <div class="wizard-field" style="margin-bottom:12px;">
        <label class="wizard-label">Formulaire EXP – 4.1 : Expérience générale de fournitures/services</label>
        <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.4; max-width:820px;">Complétez les informations sur les marchés réalisés.</p>
    </div>

    <div style="overflow-x:auto; margin-bottom:12px;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:center; width:8%;">Mois/année de départ</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:center; width:8%;">Mois/année final(e)</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:left; width:60%;">Identification du marché</th>
                    <th style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:center; width:24%;">Rôle du candidat</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody id="exp41-rows">
                @foreach(range(0, $rows - 1) as $i)
                    <tr>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="mois_depart[]" value="{{ $mois_depart[$i] ?? '' }}" style="width:100%;"></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="mois_final[]" value="{{ $mois_final[$i] ?? '' }}" style="width:100%;"></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><textarea class="form-control wizard-input" name="identification[]" style="width:100%; min-height:60px;" placeholder="Ex: Marché n°2024-001 - Fourniture de fournitures de bureau&#10;Autorité contractante: Ministère de l'Éducation&#10;Valeur: 5 000 000 FCFA&#10;Date d'achèvement: 30 juin 2024&#10;Montant mensuel: 500 000 FCFA">{{ $identification[$i] ?? '' }}</textarea></td>
                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="role_candidat[]" value="{{ $role_candidat[$i] ?? '' }}" style="width:100%;"></td>
                        <td style="padding-left:8px; vertical-align:middle; width:40px;"><button type="button" class="btn-remove-row" style="background:#ef4444; color:#fff; border:none; padding:4px 8px; cursor:pointer;">Suppr</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:12px; display:flex; gap:12px; align-items:flex-start;">
        <div style="flex:1;"><button type="button" id="exp41-add-row" style="background:#0ea5a4; color:#fff; border:none; padding:8px 12px; cursor:pointer;">Ajouter une ligne</button></div>
    </div>

</div>

<script>
(function(){
    function renumberRows() { }
    var container = document.getElementById('exp41-rows');
    var addBtn = document.getElementById('exp41-add-row');
    if(addBtn && container) {
        addBtn.addEventListener('click', function(){
            var tr = document.createElement('tr');
            tr.innerHTML = '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="mois_depart[]" value="" style="width:100%;"></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="mois_final[]" value="" style="width:100%;"></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><textarea class="form-control wizard-input" name="identification[]" style="width:100%; min-height:60px;" placeholder="Ex: Marché n°2024-001 - Fourniture de fournitures de bureau&#10;Autorité contractante: Ministère de l\'Éducation&#10;Valeur: 5 000 000 FCFA&#10;Date d\'achèvement: 30 juin 2024&#10;Montant mensuel: 500 000 FCFA"></textarea></td>' +
                           '<td style="border:1px solid #000; padding:8px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="role_candidat[]" value="" style="width:100%;"></td>' +
                           '<td style="padding-left:8px; vertical-align:middle; width:40px;"><button type="button" class="btn-remove-row" style="background:#ef4444; color:#fff; border:none; padding:4px 8px; cursor:pointer;">Suppr</button></td>';
            container.appendChild(tr);
            attachRemove(tr.querySelector('.btn-remove-row'));
        });
    }
    function attachRemove(btn) { if(!btn) return; btn.addEventListener('click', function(e){ var row = e.target.closest('tr'); if(row) row.remove(); }); }
    document.querySelectorAll('#exp41-rows .btn-remove-row').forEach(function(b){ attachRemove(b); });
})();
</script>