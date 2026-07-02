<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $typeDoc->nom }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #10b981;
            margin-bottom: 30px;
        }
        .info-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-label {
            font-weight: bold;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $typeDoc->nom }}</h1>
        
        <div class="info-box">
            <p><span class="info-label">Type :</span> {{ $typeDoc->type_formulaire }}</p>
            <p><span class="info-label">Statut :</span> {{ ucfirst($document->statut) }}</p>
            <p><span class="info-label">Créé le :</span> {{ $document->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="info-box">
            <p>{{ $message }}</p>
            @if($document->chemin_fichier)
                <p><span class="info-label">Fichier :</span> {{ basename($document->chemin_fichier) }}</p>
            @endif
        </div>
    </div>
</body>
</html>
