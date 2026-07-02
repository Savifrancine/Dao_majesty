<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Déclaration de garantie d'offre</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; background: #fff; }
        .page { width: 100%; margin: 0; padding: 0; }
        .date-box { text-align: right; font-size: 10px; margin-bottom: 20px; }
        .title { text-align: center; font-size: 14px; font-weight: 700; margin-bottom: 20px; text-transform: uppercase; }
        .intro { font-size: 11px; margin-bottom: 12px; line-height: 1.5; }
        .paragraph { margin-bottom: 12px; line-height: 1.6; text-align: justify; }
        .numbered { margin-left: 20px; margin-bottom: 8px; }
        .reference { margin-top: 20px; font-size: 10px; }
        .signatory { margin-top: 30px; }
        .signatory-line { margin-top: 40px; font-size: 10px; }
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
    
    <div class="page">
        <div class="date-box">Date : {{ $date }}</div>
        
        <div class="title">Déclaration de garantie d'offre</div>
        
        <div class="intro">
            À l'attention de l'Autorité contractante,
        </div>
        
        <div class="paragraph">
            Nous, soussignés <strong>{{ $societe }}</strong>, déclarons que les offres doivent être accompagnées d'une déclaration de garantie d'offre.
        </div>
        
        <div class="paragraph">
            Nous acceptons que nous ferons l'objet d'une suspension du droit de participer à des marchés publics pour une période ne saurait être inférieure à un (01) an si nous n'exécutons pas une des obligations auxquelles nous sommes tenus en vertu de l'offre, à savoir :
        </div>
        
        <div class="numbered">
            1. Si nous retirons l'offre pendant la période de validité spécifiée dans la lettre de soumission de l'offre ;
        </div>
        
        <div class="numbered">
            2. Si nous ne respectons pas les obligations prévues par l'Autorité contractante ;
        </div>
        
        <div class="numbered">
            3. Si nous ne fournissons pas la garantie d'exécution lorsque requis ;
        </div>
        
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
            <div class="signatory-line">Signature</div>
        </div>
    </div>
</body>
</html>
