@php
    $numeroAvis = trim($numero_avis ?? '');
    $destinataireText = trim($destinataire ?? '');
    $optionValue = ($option ?? 'i') === 'ii' ? 'ii' : 'i';
    $beneficiairesList = is_array($beneficiaires ?? null) ? array_values(array_filter($beneficiaires, function ($b) {
        return trim($b['identite'] ?? '') !== '';
    })) : [];

@endphp

<div style="font-size:12px; line-height:1.5;">
    <div style="text-align:center; font-size:15px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:14px;">Formulaire de divulgation des bénéficiaires effectifs</div>

    <div style="margin-bottom:8px;"><strong>Numéro de l'Avis de la demande de renseignements et de prix :</strong> {{ $numeroAvis ?: '-' }}</div>
    <div style="margin-bottom:12px;"><strong>A :</strong> {{ $destinataireText ?: '-' }}</div>

    <div style="margin-bottom:10px;">En réponse à l'obligation de fournir les renseignements sur les bénéficiaires effectifs :</div>

    @if($optionValue === 'i')
        <div style="margin-bottom:10px;">(i) nous fournissons les renseignements sur les bénéficiaires effectifs ci-après :</div>
    @else
        <div style="margin-bottom:10px;">(ii) nous déclarons qu'il n'y a aucun bénéficiaire effectif qui remplisse l'une au moins des conditions ci-après :</div>
        <ul style="margin:0 0 12px 20px; padding:0;">
            <li>détient directement ou indirectement 25% ou plus des actions ;</li>
            <li>détient directement ou indirectement 25% ou plus des droits de vote ;</li>
            <li>détient directement ou indirectement le pouvoir de nommer la majorité des membres du conseil d'administration ou autorité équivalente du soumissionnaire.</li>
        </ul>
    @endif

    <div style="font-weight:700; margin-bottom:8px;">Détails des bénéficiaires effectifs</div>

    <table style="width:100%; border-collapse:collapse; font-size:10.5px; margin-bottom:14px;">
        <thead>
            <tr style="background:#f3f4f6; font-weight:700;">
                <th style="border:1px solid #000; padding:6px; width:34%; text-align:left;">Identité du propriétaire bénéficiaire effectif</th>
                <th style="border:1px solid #000; padding:6px; width:22%;">Détient directement ou indirectement 25% ou plus des actions (Oui / Non)</th>
                <th style="border:1px solid #000; padding:6px; width:22%;">Détient directement ou indirectement 25% ou plus des droits de vote (Oui / Non)</th>
                <th style="border:1px solid #000; padding:6px; width:22%;">Détient directement ou indirectement le droit de nommer la majorité des membres du conseil d'administration ou autorité équivalente du soumissionnaire (Oui / Non)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($beneficiairesList as $b)
                <tr>
                    <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e(trim($b['identite'] ?? ''))) !!}</td>
                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $b['action_25'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $b['vote_25'] ?? '' }}</td>
                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $b['pouvoir_nomination'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border:1px solid #000; padding:6px; text-align:center; font-style:italic;">Aucun bénéficiaire effectif renseigné.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
