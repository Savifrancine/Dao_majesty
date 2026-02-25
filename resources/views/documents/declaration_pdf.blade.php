<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Déclaration de garantie d'offre</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; }
        .header { display:flex; justify-content:space-between; align-items:center; }
        .title { text-align:center; margin-top:20px; margin-bottom:20px; font-weight:700; }
        .content { margin: 20px; font-size:12pt; line-height:1.5; }
        .signature { margin-top:40px; }
        .seal { opacity:0.9; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <!-- Left side: company box or logo placeholder -->
        </div>
        <div style="text-align:right; font-size:12px;">
            Date : {{ 
                \Carbon\Carbon::parse($date)->format('d/m/Y')
            }}
        </div>
    </div>

    <h2 class="title">DECLARATION DE GARANTIE D'OFFRE</h2>

    <div class="content">
        <p>A l'attention de l'Autorité contractante,</p>

        <p>Nous, soussignés : <strong>{{ $societe }}</strong>, déclarons que les offres doivent être accompagnées d'une déclaration de garantie d'offre.</p>

        <p>Nous acceptons que nous ferons l'objet d'une suspension du droit de participer à des marchés publics pour une période qui ne saurait être inférieure à un (01) an si nous n’exécutons pas une des obligations auxquelles nous sommes tenus en vertu de l'offre, à savoir :</p>

        <ol>
            <li>Si nous retirons l'offre pendant la période de validité spécifiée dans la lettre de soumission de l'offre ;</li>
            <li>Si nous ne respectons pas les obligations prévues par l'Autorité contractante ;</li>
            <li>Si nous ne fournissons pas la garantie d'exécution lorsque requis ;</li>
        </ol>

        <p>Référence : {{ $reference ?? '-' }}</p>

        <div class="signature">
            <p>Nom : <strong>{{ $declarant }}</strong></p>
            <p>Fonction : {{ $fonction ?? '-' }}</p>

            @if(!empty($signatureDataUri))
                <div style="margin-top:10px">
                    <img src="{{ $signatureDataUri }}" style="max-width:240px; max-height:120px;" alt="signature">
                </div>
            @endif

            <p style="margin-top:6px">Signature</p>
        </div>
    </div>

</body>
</html>
