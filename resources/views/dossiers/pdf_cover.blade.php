<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page de garde - {{ $dossier->nom_dossier }}</title>
    <style>
        @page { margin: 20mm }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .cover { text-align:center; padding: 10px 18px; }
        .blue-rule { height:6px; background:#0b63b5; margin:10px 0 10px 0; }
        .head-title { font-size:22px; font-weight:700; margin-top:6px; }
        .head-sub { font-size:13px; margin-top:6px; }
        .framed { margin:18px auto; max-width:88%; border:14px solid #0b63b5; padding:6px; background:#0b63b5; }
        .framed .inner { background:#fff; padding:20px; text-align:center; color:#0b63b5; }
        .framed .main { font-size:20px; font-weight:800; }
        .framed .sub { margin-top:10px; font-size:15px; }
        .big-subtitle { color:#0b63b5; font-weight:800; font-size:16px; margin-top:12px; }
    </style>
</head>
<body>
    <div class="cover">
        <h1>{{ strtoupper($dossier->nom_dossier ?? 'DOSSIER') }}</h1>
        <div style="margin-top:20px; font-size:14px;">Type: {{ optional($dossier->typeDossier)->nom }}</div>
        <div style="margin-top:10px; font-size:12px;">Entreprise: {{ optional($dossier->entreprise)->nom }}</div>
        <div style="margin-top:10px; font-size:12px;">Date de lancement: {{ optional($dossier->date_lancement)->format('d/m/Y') ?? '-' }}</div>
        <div style="margin-top:10px; font-size:12px;">Date de soumission: {{ optional($dossier->date_soumission)->format('d/m/Y') ?? '-' }}</div>
        <div style="margin-top:10px; font-size:12px;">Date créa : {{ now()->format('d/m/Y') }}</div>
    </div>
    <div style="page-break-after:always;"></div>

    <div>
        <h2 style="color:#0b63b5;">📋 SOMMAIRE</h2>
        <ol>
            @foreach($dossier->documents->sortBy('ordre') as $doc)
                <li>{{ $doc->typeDocument->nom }}</li>
            @endforeach
        </ol>
    </div>
    <div style="page-break-after:always;"></div>
</body>
</html>