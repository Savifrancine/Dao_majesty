<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Formulaire de qualification</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; background: #fff; }
        .page { width: 100%; margin: 0; padding: 0; }
        .date-box { text-align: right; font-size: 10px; margin-bottom: 20px; }
        .title { text-align: center; font-size: 14px; font-weight: 700; margin-bottom: 20px; text-transform: uppercase; }
        .instructions { font-size: 10px; font-style: italic; color: #555; margin-bottom: 14px; line-height: 1.5; }
        .intro { font-size: 11px; margin-bottom: 12px; line-height: 1.5; }
        .paragraph { margin-bottom: 12px; line-height: 1.6; text-align: justify; }
        .lettered { margin-left: 20px; margin-bottom: 8px; line-height: 1.6; text-align: justify; }
        .subtitle { font-weight: 700; margin: 10px 0 6px 20px; }
        .reference { margin-top: 20px; font-size: 10px; }
        .signatory { margin-top: 30px; }
        .sig-row { display: flex; gap: 24px; margin-top: 16px; }
        .sig-block { flex: 1; text-align: center; }
        .sig-block-title { font-size: 10px; font-weight: 700; margin-bottom: 6px; }
        .sig-image { max-height: 70px; max-width: 100%; }
        .sig-line { border-bottom: 1px solid #999; height: 60px; }
    </style>
</head>
<body>
    @php
        $societe = $societe ?? 'Société';
        $date = $date ?? now()->format('d/m/Y');
        $declarant = $declarant ?? 'Déclarant';
        $fonction = $fonction ?? '';
        $reference = $reference ?? '';
    @endphp

    @include('documents.partials.majesty_header', ['entreprise' => $entreprise ?? null])

    <div class="page">
        <div class="date-box">Date : {{ $date }}</div>

        <div class="title">Formulaire de qualification</div>

        <div class="paragraph">
            Nous soussignés, <strong>{{ $societe }}</strong>, certifions l'exactitude des informations ci-après, attestant que nous remplissons les conditions de qualifications requises pour exécuter le Marché, fixées par l'Autorité contractante, à savoir :
        </div>

        @include('documents.partials.formulaire_qualification_content', [
            'societe' => $societe,
            'nombre_marches' => $nombre_marches ?? null,
            'marches' => $marches ?? [],
        ])

        <div class="reference">
            @if($reference)
                Référence : {{ $reference }}
            @endif
        </div>

        <div class="signatory">
            <div>Nom : {{ $declarant }}</div>
            @if($fonction)
                <div>Fonction : {{ $fonction }}</div>
            @endif

            <div class="sig-row">
                <div class="sig-block">
                    <div class="sig-block-title">Signature</div>
                    @if(!empty($signatureDataUri))
                        <img src="{{ $signatureDataUri }}" class="sig-image" alt="Signature">
                    @else
                        <div class="sig-line"></div>
                    @endif
                </div>
                <div class="sig-block">
                    <div class="sig-block-title">Cachet</div>
                    @if(!empty($cachetDataUri))
                        <img src="{{ $cachetDataUri }}" class="sig-image" alt="Cachet">
                    @else
                        <div class="sig-line"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
