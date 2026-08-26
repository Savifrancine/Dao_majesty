<div id="formulaire-fin-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $stored = [];
        if ($doc && !empty($doc->content)) {
            $decoded = json_decode($doc->content, true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $year_labels = old('year_labels', $stored['year_labels'] ?? []);
        $minCols = 3;
        $maxCols = 8;
        $currentCols = max($minCols, count($year_labels) ?: 4);
        if ($currentCols > $maxCols) $currentCols = $maxCols;
        while (count($year_labels) < $currentCols) { $year_labels[] = ''; }

        $total_actif = old('total_actif', $stored['total_actif'] ?? []);
        $total_passif = old('total_passif', $stored['total_passif'] ?? []);
        $patrimoine_net = old('patrimoine_net', $stored['patrimoine_net'] ?? []);
        $disponibilites = old('disponibilites', $stored['disponibilites'] ?? []);
        $engagements = old('engagements', $stored['engagements'] ?? []);
        $recettes_totales = old('recettes_totales', $stored['recettes_totales'] ?? []);
        $benefices_avant_impots = old('benefices_avant_impots', $stored['benefices_avant_impots'] ?? []);

        $rows = [
            'total_actif' => $total_actif,
            'total_passif' => $total_passif,
            'patrimoine_net' => $patrimoine_net,
            'disponibilites' => $disponibilites,
            'engagements' => $engagements,
            'recettes_totales' => $recettes_totales,
            'benefices_avant_impots' => $benefices_avant_impots,
        ];

        foreach ($rows as $key => $values) {
            while (count($rows[$key]) < $maxCols) {
                $rows[$key][] = '';
            }
        }

        $total_actif = $rows['total_actif'];
        $total_passif = $rows['total_passif'];
        $patrimoine_net = $rows['patrimoine_net'];
        $disponibilites = $rows['disponibilites'];
        $engagements = $rows['engagements'];
        $recettes_totales = $rows['recettes_totales'];
        $benefices_avant_impots = $rows['benefices_avant_impots'];
    @endphp

    <div style="overflow-x:auto; margin-bottom:12px;">
        <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
            <button type="button" id="addYearBtn" class="btn btn-sm btn-ghost">+ Ajouter une année</button>
            <button type="button" id="removeYearBtn" class="btn btn-sm btn-ghost">- Retirer une année</button>
            <span style="color:#6b7280; font-size:12px;">Colonnes: <strong id="colsCount">{{ $currentCols }}</strong> (max {{ $maxCols }})</span>
        </div>

        <table class="table" id="finTable" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr>
                    <th rowspan="2" style="border:1px solid #000; padding:8px; width:30%; background:#f3f4f6;">Données financières en équivalent FCFA</th>
                    <th colspan="{{ $maxCols }}" style="border:1px solid #000; padding:8px; background:#f3f4f6;">Antécédents pour les dernières années (équivalent milliers de FCFA)</th>
                </tr>
                <tr id="yearHeaders">
                    @for($i=0;$i<$maxCols;$i++)
                        @php $hidden = $i >= $currentCols; @endphp
                        <th style="border:1px solid #000; padding:8px; width:120px;" class="year-col {{ $hidden ? 'hidden-col' : '' }}">
                            <input type="text" class="form-control wizard-input" name="year_labels[{{ $i }}]" value="{{ $year_labels[$i] ?? '' }}" {{ $hidden ? 'disabled' : '' }} placeholder="Année {{ $i+1 }}">
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid #000; padding:8px;">Information du bilan</td>
                    <td colspan="{{ $maxCols }}" style="border:1px solid #000; padding:8px;"></td>
                </tr>
                @php $metrics = ['total_actif' => ['Total actif (TA)', $total_actif], 'total_passif' => ['Total passif (TP)', $total_passif], 'patrimoine_net' => ['Patrimoine net (PN)', $patrimoine_net], 'disponibilites' => ['Disponibilités (D)', $disponibilites], 'engagements' => ['Engagements (E)', $engagements]]; @endphp
                @foreach($metrics as $key => [$label, $values])
                <tr>
                    <td style="border:1px solid #000; padding:8px;">{{ $label }}</td>
                    @for($i=0;$i<$maxCols;$i++)
                        @php $hidden = $i >= $currentCols; @endphp
                        <td style="border:1px solid #000; padding:8px;" class="year-col {{ $hidden ? 'hidden-col' : '' }}">
                            <input type="text" class="form-control wizard-input" name="{{ $key }}[{{ $i }}]" value="{{ $values[$i] ?? '' }}" {{ $hidden ? 'disabled' : '' }}>
                        </td>
                    @endfor
                </tr>
                @endforeach

                <tr>
                    <td style="border:1px solid #000; padding:8px;">Information des comptes de résultats</td>
                    <td colspan="{{ $maxCols }}" style="border:1px solid #000; padding:8px;"></td>
                </tr>
                @php $results = ['recettes_totales' => ['Recettes totales (RT)', $recettes_totales], 'benefices_avant_impots' => ['Bénéfices avant impôts (BAI)', $benefices_avant_impots]]; @endphp
                @foreach($results as $key => [$label, $values])
                <tr>
                    <td style="border:1px solid #000; padding:8px;">{{ $label }}</td>
                    @for($i=0;$i<$maxCols;$i++)
                        @php $hidden = $i >= $currentCols; @endphp
                        <td style="border:1px solid #000; padding:8px;" class="year-col {{ $hidden ? 'hidden-col' : '' }}">
                            <input type="text" class="form-control wizard-input" name="{{ $key }}[{{ $i }}]" value="{{ $values[$i] ?? '' }}" {{ $hidden ? 'disabled' : '' }}>
                        </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="wizard-field" style="margin-bottom:18px;">
        <label class="wizard-label">Commentaire complémentaire</label>
        <textarea class="form-control wizard-input" name="commentaire_complementaire" rows="4">{{ old('commentaire_complementaire', $stored['commentaire_complementaire'] ?? '') }}</textarea>
    </div>

</div>

<style>
.hidden-col { display:none; }
.year-col input[disabled] { background:#f9fafb; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const maxCols = {{ $maxCols }};
    let currentCols = {{ $currentCols }};
    const addBtn = document.getElementById('addYearBtn');
    const removeBtn = document.getElementById('removeYearBtn');
    const colsCountEl = document.getElementById('colsCount');

    function setCols(n) {
        currentCols = Math.max({{ $minCols }}, Math.min(maxCols, n));
        colsCountEl.textContent = currentCols;
        document.querySelectorAll('#finTable tr').forEach((row) => {
            row.querySelectorAll('.year-col').forEach((el, idx) => {
                const inputs = el.querySelectorAll('input');
                if (idx < currentCols) {
                    el.classList.remove('hidden-col');
                    inputs.forEach(i => i.disabled = false);
                } else {
                    el.classList.add('hidden-col');
                    inputs.forEach(i => i.disabled = true);
                }
            });
        });
    }

    addBtn.addEventListener('click', () => { setCols(currentCols + 1); });
    removeBtn.addEventListener('click', () => { setCols(currentCols - 1); });
});
</script>
