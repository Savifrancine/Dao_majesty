<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Liste du personnel affecté à l'exécution du marché</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size:12px; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; }
        .title { text-align:center; font-weight:700; font-size:16px; margin-bottom:18px; }
        .box { border:1px solid #666; padding:12px; border-radius:8px; background:#f8f8f8; margin-bottom:14px; }
        .table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        .table th, .table td { border:1px solid #000; padding:8px; vertical-align:top; }
        .table th { background:#f3f4f6; font-weight:700; }
        .footer { font-size:11px; margin-top:12px; }
    </style>
</head>
<body>
    @include('documents.partials.majesty_header', ['entreprise' => $entreprise ?? optional($dossier)->entreprise])

    <div class="header">
        <div>
            {{ $dossier->ref ?? '' }}
        </div>
        <div style="text-align:right;">
            <div>Date : {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="title">Liste du personnel affecté à l'exécution du marché</div>

    <div class="box">
        <div><strong>Dossier :</strong> {{ $dossier->nom_dossier ?? 'N/A' }}</div>
        <div><strong>Objet du marché :</strong> {{ $dossier->objet ?? 'N/A' }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:6%;">N°</th>
                <th style="width:44%;">Poste</th>
                <th style="width:50%;">Nom et prénom</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personnel as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['poste'] ?? '-' }}</td>
                    <td>{{ $item['nom'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Ce document liste le personnel affecté aux tâches d'exécution du marché. Les informations recueillies sont fournies pour les besoins du dossier de soumission.
    </div>
</body>
</html>
