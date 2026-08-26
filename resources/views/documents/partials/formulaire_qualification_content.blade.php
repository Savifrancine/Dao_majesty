@php
    $societe = $societe ?? 'Société';
    $nombreMarches = isset($nombre_marches) && $nombre_marches !== '' ? (int) $nombre_marches : 0;
    $marches = is_array($marches ?? null) ? array_values(array_filter($marches, function ($m) {
        return !empty(trim($m['annee'] ?? '')) || !empty(trim($m['nom'] ?? '')) || !empty(trim($m['reference'] ?? ''));
    })) : [];
    // Les années résumées dans la phrase d'introduction sont déduites de l'année
    // de chaque marché, pour éviter de la saisir une deuxième fois séparément.
    $annees = collect($marches)->pluck('annee')->filter(fn($a) => trim((string) $a) !== '')->unique()->sort()->values()->all();

    $nombresEnLettres = [1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq', 6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix'];
    $nombreMarchesTexte = $nombresEnLettres[$nombreMarches] ?? ($nombreMarches > 0 ? (string) $nombreMarches : '');

    $hasCapaciteInfo = $nombreMarches > 0 || count($annees) > 0 || count($marches) > 0;
@endphp

<div style="margin-left:20px; margin-bottom:8px; line-height:1.6; text-align:justify;">
    a) nous sommes dûment autorisé par le fabriquant ou le producteur des Fournitures pour les fournir au Bénin ;
</div>

<div style="margin-left:20px; margin-bottom:8px; line-height:1.6; text-align:justify;">
    b) nous sommes ou serons (si notre offre est acceptée) représenté par un agent équipé et en mesure de répondre aux besoins en matière d'entretien, de réparations des équipements, et de fournitures de pièces détachées.
</div>

<div style="margin-left:20px; margin-bottom:8px; line-height:1.6; text-align:justify;">
    c) nous remplissons les conditions de qualification suivantes :
    <div style="font-weight:700; margin:10px 0 6px 0;">Capacité technique et expérience</div>

    @if($hasCapaciteInfo)
        <div>
            Nous avons exécuté {{ $nombreMarchesTexte ?: '[nombre non renseigné]' }} marché{{ $nombreMarches > 1 ? 's' : '' }} similaire{{ $nombreMarches > 1 ? 's' : '' }}, portant sur des fournitures ou des services de nature similaire au cours des années {{ count($annees) ? implode(', ', $annees) : '[années non renseignées]' }}. Ces marchés sont identifiés ci-après :
        </div>

        @if(count($marches))
            <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:10px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #000; padding:6px; background:#f3f4f6; text-align:left; width:8%;">N°</th>
                        <th style="border:1px solid #000; padding:6px; background:#f3f4f6; text-align:left; width:12%;">Année</th>
                        <th style="border:1px solid #000; padding:6px; background:#f3f4f6; text-align:left; width:44%;">Nom du marché</th>
                        <th style="border:1px solid #000; padding:6px; background:#f3f4f6; text-align:left; width:36%;">Référence du marché</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marches as $i => $m)
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ $i + 1 }}</td>
                            <td style="border:1px solid #000; padding:6px;">{{ $m['annee'] ?? '' }}</td>
                            <td style="border:1px solid #000; padding:6px;">{{ $m['nom'] ?? '' }}</td>
                            <td style="border:1px solid #000; padding:6px;">{{ $m['reference'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="margin-top:8px; font-style:italic; color:#555;">[le candidat doit documenter distinctement ces marchés]</div>
        @endif
    @else
        <div>
            Nous avons exécuté [insérer « un » ou « deux »] marchés similaires, portant sur des fournitures ou des services de nature similaire au cours des [insérer « trois » ou « quatre »] dernières années. Ces marchés sont identifiés ci-après : [le candidat doit documenter distinctement ces marchés]
        </div>
    @endif

    <div style="margin-top:8px;">
        [insérer toutes autres exigences en précisant la nature des documents justificatifs requis ; par exemple, lorsque le Fournisseur devra fabriquer tout ou partie des fournitures, il sera exigé qu'il apporte la preuve qu'il dispose des moyens techniques et humains nécessaires]
    </div>
</div>
