@php
    $rows = [];
    $candidat = '';
    if (!empty($plan_charge_rows) && is_array($plan_charge_rows)) {
        $rows = $plan_charge_rows;
    }
    if (!empty($plan_charge_candidat)) {
        $candidat = $plan_charge_candidat;
    } else {
        $candidat = optional($dossier->entreprise)->nom ?? '';
    }

    if (empty($rows)) {
        $rows = [[
            'nature' => 'Néant',
            'marche' => 'Néant',
            'delai' => 'Néant',
            'date_demarrage' => 'Néant',
            'date_fin' => 'Néant',
            'taux_physique' => 'Néant',
            'taux_financier' => 'Néant',
            'autorite' => 'Néant',
            'observations' => 'Néant',
        ]];
    }
@endphp

<div style="text-align:center; margin-top:0; margin-bottom:12px;">
    <h2 style="text-decoration:underline; margin:0;">Plan de charge</h2>
    <div style="margin-top:8px;">Nom du candidat : <strong>{{ strtoupper($candidat) }}</strong></div>
</div>

<style>
    table.plan-charge-table,
    table.plan-charge-table th,
    table.plan-charge-table td {
        font-size: 9px !important;
    }
</style>

<div style="margin-top:14px; width:100%;">
    <table class="plan-charge-table" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <thead>
            <tr style="background:#f3f4f6; font-weight:700;">
                <th style="border:1px solid #000; padding:4px; width:3%; word-wrap:break-word;">N°</th>
                <th style="border:1px solid #000; padding:4px; width:15%; word-wrap:break-word;">Nature des services*</th>
                <th style="border:1px solid #000; padding:4px; width:12%; word-wrap:break-word;">Montant HT et réf. du marché</th>
                <th style="border:1px solid #000; padding:4px; width:7%; word-wrap:break-word;">Délai (mois)</th>
                <th style="border:1px solid #000; padding:4px; width:9%; word-wrap:break-word;">Date démarrage</th>
                <th style="border:1px solid #000; padding:4px; width:9%; word-wrap:break-word;">Date fin</th>
                <th style="border:1px solid #000; padding:4px; width:9%; word-wrap:break-word;">Taux d'exécution physique</th>
                <th style="border:1px solid #000; padding:4px; width:9%; word-wrap:break-word;">Taux d'exécution financière</th>
                <th style="border:1px solid #000; padding:4px; width:14%; word-wrap:break-word;">Autorité contractante / Bailleur</th>
                <th style="border:1px solid #000; padding:4px; width:13%; word-wrap:break-word;">Observations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $r)
                <tr>
                    <td style="border:1px solid #000; padding:4px 6px; text-align:center; vertical-align:top; word-wrap:break-word;">{{ $i + 1 }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; word-wrap:break-word;">{{ $r['nature'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; word-wrap:break-word;">{{ $r['marche'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; text-align:center; word-wrap:break-word;">{{ $r['delai'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; text-align:center; word-wrap:break-word;">{{ $r['date_demarrage'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; text-align:center; word-wrap:break-word;">{{ $r['date_fin'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; text-align:center; word-wrap:break-word;">{{ $r['taux_physique'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; text-align:center; word-wrap:break-word;">{{ $r['taux_financier'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; word-wrap:break-word;">{{ $r['autorite'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:4px 6px; vertical-align:top; word-wrap:break-word;">{{ $r['observations'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
