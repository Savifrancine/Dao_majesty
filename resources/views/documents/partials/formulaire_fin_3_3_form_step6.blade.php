<div id="formulaire-fin-3-3-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $stored = [];
        if ($doc && !empty($doc->content)) {
            $decoded = json_decode($doc->content, true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $source_financement = old('source_financement', $stored['source_financement'] ?? []);
        $montant_fcfa = old('montant_fcfa', $stored['montant_fcfa'] ?? []);
        $rows = max(4, max(count($source_financement), count($montant_fcfa)));
        // normalize arrays to same length
        while (count($source_financement) < $rows) { $source_financement[] = ''; }
        while (count($montant_fcfa) < $rows) { $montant_fcfa[] = ''; }
    @endphp

    <div class="wizard-field" style="margin-bottom:16px;">
        <label class="wizard-label">Capacité de financement</label>
        <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Indiquer les sources de financement (liquidités, actifs réels non grevés, lignes de crédit et autres moyens financiers nécessaires pour les besoins de trésorerie liés aux services afférents aux marché(s) considéré(s), nets des engagements pris par le candidat au titre d'autres marchés comme requis.</p>
    </div>

    <div style="overflow-x:auto; margin-bottom:18px;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:10px; background:#f3f4f6; text-align:left; width:65%;">Source de financement</th>
                    <th style="border:1px solid #000; padding:10px; background:#f3f4f6; text-align:left; width:35%;">Montant (FCFA équivalents)</th>
                </tr>
            </thead>
            <tbody id="fin33-rows">
                @foreach(range(0, $rows - 1) as $i)
                    <tr>
                        <td style="border:1px solid #000; padding:10px; vertical-align:top;">{{ $loop->iteration }}.
                            <input type="text" class="form-control wizard-input" name="source_financement[]" value="{{ $source_financement[$i] ?? '' }}" style="width:100%; margin-top:6px;">
                        </td>
                        <td style="border:1px solid #000; padding:10px; vertical-align:top;">
                            <input type="text" class="form-control wizard-input" name="montant_fcfa[]" value="{{ $montant_fcfa[$i] ?? '' }}" style="width:100%;">
                        </td>
                        <td style="padding-left:8px; vertical-align:middle; width:40px;">
                            <button type="button" class="btn-remove-row" style="background:#ef4444; color:#fff; border:none; padding:4px 8px; cursor:pointer;">Suppr</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:14px;">
        <div style="margin-bottom:12px;">
            <button type="button" id="fin33-add-row" style="background:#0ea5a4; color:#fff; border:none; padding:8px 12px; cursor:pointer;">Ajouter une ligne</button>
        </div>
    </div>
</div>

<script>
    (function(){
        function renumberRows() {
            var rows = document.querySelectorAll('#fin33-rows tr');
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

        var container = document.getElementById('fin33-rows');
        var addBtn = document.getElementById('fin33-add-row');
        if(addBtn && container) {
            addBtn.addEventListener('click', function(){
                var tr = document.createElement('tr');
                tr.innerHTML = '<td style="border:1px solid #000; padding:10px; vertical-align:top;">1. <input type="text" class="form-control wizard-input" name="source_financement[]" value="" style="width:100%; margin-top:6px;"></td>' +
                               '<td style="border:1px solid #000; padding:10px; vertical-align:top;"><input type="text" class="form-control wizard-input" name="montant_fcfa[]" value="" style="width:100%;"></td>' +
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

        // attach existing remove buttons
        document.querySelectorAll('#fin33-rows .btn-remove-row').forEach(function(b){ attachRemove(b); });
    })();
</script>
