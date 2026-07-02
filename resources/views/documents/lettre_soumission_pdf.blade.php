<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Lettre de soumission</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#111 }
        .header { display:flex; align-items:center; }
        .logo { width:120px }
        .center { text-align:center }
        .title { font-size:16px; font-weight:bold; margin-top:10px }
        .section { margin-top:12px }
        .signature { margin-top:30px }
        .small { font-size:11px; color:#444 }
    </style>
</head>
<body>
<div class="header">
    <div style="flex:0 0 120px">
        @if(isset($entreprise) && $entreprise->logo)
            <img src="{{ storage_path('app/public/' . $entreprise->logo) }}" class="logo">
        @endif
    </div>
    <div style="flex:1;text-align:right">
        <div class="small">C/9/18 Agbalodebo<br/>Tel: +229 97 77 25 04</div>
    </div>
</div>

<div class="center title">LETTRE DE SOUMISSION</div>

<div class="section">
    <strong>Date :</strong> {{ $date ?? now()->format('d/m/Y') }}<br>
    <strong>Référence :</strong> {{ $drp_number ?? '' }}
</div>

<div class="section">
    <strong>À :</strong> {{ $destinataire ?? '' }}
</div>

<div class="section">
    {!! nl2br(e($introduction ?? '')) !!}
</div>

<div class="section">
    <strong>a)</strong>
    {!! nl2br(e($point_a ?? '')) !!}
</div>

<div class="section">
    <strong>b)</strong>
    {!! nl2br(e($point_b ?? '')) !!}
</div>

<div class="section">
    <strong>c)</strong><br>
    - Montant (chiffres) : {{ $montant_chiffres ?? '' }}<br>
    - Montant (lettres) : {{ $montant_lettres ?? '' }}
</div>

<div class="section">
    <strong>d)</strong> TVA: {{ $tva_valeur ?? '' }}
</div>

<div class="section small">
    {!! nl2br(e($rabais ?? '')) !!}
</div>

<div class="signature">
    <div><strong>{{ $signataire_nom ?? '' }}</strong></div>
    <div>{{ $signataire_fonction ?? '' }}</div>
    @if(!empty($signatureDataUri))
        <div style="margin-top:8px"><img src="{{ $signatureDataUri }}" style="max-width:200px;max-height:100px"/></div>
    @endif
</div>

</body>
</html>
