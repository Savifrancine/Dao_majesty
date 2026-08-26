<div style="font-family:Arial, sans-serif; font-size:11px; color:#000; line-height:1.4;">
    <h2 style="text-align:center; margin-bottom:4px;">Formulaire FIN 3.3</h2>
    <h3 style="text-align:center; margin-top:0; margin-bottom:14px;">Capacité de financement</h3>

    <div style="font-size:11px; margin-bottom:14px;">
        Indiquer les sources de financement (liquidités, actifs réels non grevés, lignes de crédit et autres moyens financiers nécessaires pour les besoins de trésorerie liés aux services afférents aux marché(s) considéré(s), nets des engagements pris par le candidat au titre d'autres marchés comme requis.
    </div>

    @php
        $rows = 4;
        $source_financement = $content['source_financement'] ?? [];
        $montant_fcfa = $content['montant_fcfa'] ?? [];
        $max = max($rows, max(count($source_financement), count($montant_fcfa)));
        while (count($source_financement) < $max) { $source_financement[] = ''; }
        while (count($montant_fcfa) < $max) { $montant_fcfa[] = ''; }

        // Build only non-empty rows for PDF (hide fully empty rows)
        $rowsData = [];
        for ($i = 0; $i < $max; $i++) {
            $s = trim((string)($source_financement[$i] ?? ''));
            $m = trim((string)($montant_fcfa[$i] ?? ''));
            if ($s !== '' || $m !== '') {
                $rowsData[] = ['source' => $s, 'montant' => $m];
            }
        }
    @endphp

    <table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:16px;">
        <thead>
            <tr>
                <th style="border:1px solid #000; padding:10px; background:#f3f4f6; text-align:left; width:70%;">Source de financement</th>
                <th style="border:1px solid #000; padding:10px; background:#f3f4f6; text-align:left; width:30%;">Montant (FCFA équivalents)</th>
            </tr>
        </thead>
        <tbody>
            @if(count($rowsData) > 0)
                @foreach($rowsData as $idx => $r)
                    <tr>
                        <td style="border:1px solid #000; padding:10px; vertical-align:top; min-height:28px;">{{ $idx + 1 }}. {!! nl2br(e($r['source'])) !!}</td>
                        <td style="border:1px solid #000; padding:10px; vertical-align:top;">{!! nl2br(e($r['montant'])) !!}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" style="border:1px solid #000; padding:10px; text-align:center; color:#666;">Aucune source de financement renseignée.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
