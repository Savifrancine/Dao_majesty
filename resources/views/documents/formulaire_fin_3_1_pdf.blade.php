<div style="font-family:Arial, sans-serif; font-size:11px; color:#000; line-height:1.35;">
    <h2 style="text-align:center; margin-bottom:6px;">Formulaire FIN – 3.1</h2>
    <h3 style="text-align:center; margin-top:0; margin-bottom:14px;">Situation financière</h3>

    @php
        $displayDate = optional($dossier)->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : '';
        $nomCandidat = optional(optional($dossier)->entreprise)->nom ?? '';
    @endphp
    <table style="width:100%; margin-bottom:10px; font-size:11px; border-collapse:collapse;">
        <tr>
            <td style="padding:6px; border:1px solid #000; width:50%;"><strong>Nom du candidat :</strong> {{ $nomCandidat }}</td>
            <td style="padding:6px; border:1px solid #000; width:50%;"><strong>Date :</strong> {{ $displayDate }}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding:6px; border:1px solid #000;"><strong>Numéro Avis de demande de renseignements et de prix :</strong> {{ $dossier->reference_dossier ?? '' }}</td>
        </tr>
    </table>

    @php
        $years = $content['year_labels'] ?? [];
        // Ne garde que les colonnes dont l'année a été renseignée : une colonne
        // vide (année non remplie) ne doit pas s'afficher dans le PDF.
        $activeCols = [];
        foreach ($years as $i => $y) {
            if (trim((string) $y) !== '') {
                $activeCols[] = $i;
            }
        }
        if (empty($activeCols)) {
            $activeCols = array_keys(array_slice($years, 0, 4)) ?: [0, 1, 2, 3];
        }
        $cols = count($activeCols);
    @endphp

    <table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:12px;">
        <thead>
            <tr>
                <th rowspan="2" style="border:1px solid #000; padding:8px; background:#f0f0f0; text-align:left; width:35%;">Données financières en équivalent FCFA</th>
                <th colspan="{{ $cols }}" style="border:1px solid #000; padding:8px; background:#f0f0f0;">Antécédents pour les dernières années</th>
            </tr>
            <tr>
                @foreach($activeCols as $i)
                    <th style="border:1px solid #000; padding:8px; background:#f0f0f0;">{{ $years[$i] ?? 'Année '.($i+1) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:1px solid #000; padding:8px; font-weight:700; text-align:center; vertical-align:middle; background:#f3f4f6;" colspan="{{ $cols + 1 }}">Information du bilan</td>
            </tr>
            @php $keys = ['total_actif','total_passif','patrimoine_net','disponibilites','engagements'];
                  $labels = ['Total actif (TA)','Total passif (TP)','Patrimoine net (PN)','Disponibilités (D)','Engagements (E)'];
            @endphp
            @foreach($keys as $kIndex => $key)
                <tr>
                    <td style="border:1px solid #000; padding:8px;">{{ $labels[$kIndex] }}</td>
                    @foreach($activeCols as $i)
                        <td style="border:1px solid #000; padding:8px;">{{ $content[$key][$i] ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td style="border:1px solid #000; padding:8px; font-weight:700; text-align:center; vertical-align:middle; background:#f3f4f6;" colspan="{{ $cols + 1 }}">Information des comptes de résultats</td>
            </tr>
            @php $resKeys = ['recettes_totales','benefices_avant_impots']; $resLabels = ['Recettes totales (RT)','Bénéfices avant impôts (BAI)']; @endphp
            @foreach($resKeys as $rIndex => $rKey)
                <tr>
                    <td style="border:1px solid #000; padding:8px;">{{ $resLabels[$rIndex] }}</td>
                    @foreach($activeCols as $i)
                        <td style="border:1px solid #000; padding:8px;">{{ $content[$rKey][$i] ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size:10pt; margin-top:8px;">
        <p style="margin:0 0 6px 0;">2. Documents financiers</p>
        <p style="margin:0 0 6px 0;">On trouvera ci-après les copies des états financiers certifiés (y compris toutes les notes y afférentes, et comptes de résultats) pour les années spécifiées ci-dessus et qui satisfont aux conditions suivantes :</p>
        <ol style="margin:6px 0 6px 20px; padding:0;">
            <li>Ils doivent refléter la situation financière du candidat ou de la partie au GE, et non pas celle de la maison-mère ou de filiales.</li>
            <li>Les états financiers des trois dernières années présentées par un comptable employé de l'entreprise et attestés par un membre de l'Ordre des Experts Comptables et Comptables Agréés et portant la mention DGI. Pour les entreprises naissantes, les justificatifs requis de leurs capacités financières (bilan d'ouverture) ;</li>
            <li>Les états financiers doivent être complets et inclure toutes les notes qui leur ont été ajoutées.</li>
            <li>Les états financiers doivent correspondre aux périodes comptables déjà terminées et vérifiées (les états financiers de périodes partielles ne seront ni demandés ni acceptés).</li>
        </ol>
    </div>
</div>
