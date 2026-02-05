<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $dossier->nom_dossier }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .page-break {
            page-break-after: always;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header {
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .cover-page {
            text-align: center;
            padding: 100px 20px;
        }
        .cover-page h1 {
            font-size: 36px;
            color: #0066cc;
            margin: 30px 0;
        }
        .cover-page p {
            font-size: 14px;
            color: #666;
            margin: 10px 0;
        }
        .toc {
            page-break-after: always;
            padding: 20px 0;
        }
        .toc h2 {
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
        }
        .toc ol {
            font-size: 14px;
        }
        .document-section {
            page-break-before: always;
            padding-top: 30px;
            padding-bottom: 30px;
            border-top: 3px solid #0066cc;
        }
        .document-section h2 {
            color: #0066cc;
            border-bottom: 1px solid #0066cc;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .page-number {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- PAGE DE GARDE -->
    <div class="cover-page">
        @if($dossier->entreprise->logo)
        <img src="{{ $dossier->entreprise->logo }}" alt="Logo" class="logo">
        @endif
        
        <h1>{{ $dossier->entreprise->nom }}</h1>
        @if($dossier->entreprise->sigle)
        <p><strong>{{ $dossier->entreprise->sigle }}</strong></p>
        @endif
        
        <div style="margin-top: 60px;">
            <h2>{{ $dossier->nom_dossier }}</h2>
            <p>Type: <strong>{{ $dossier->typeDossier->nom }}</strong></p>
            @if($dossier->lot)
            <p>Lot: <strong>{{ $dossier->lot }}</strong></p>
            @endif
        </div>

        @if($dossier->objectif)
        <div style="margin-top: 60px; text-align: left; display: inline-block;">
            <h4>Objectif:</h4>
            <p>{{ $dossier->objectif }}</p>
        </div>
        @endif

        <div style="margin-top: 80px; font-size: 12px; color: #999;">
            <p>{{ $dossier->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- SOMMAIRE -->
    @if($dossier->documents->count() > 0)
    <div class="page-break">
        <div class="toc">
            <h2>📋 SOMMAIRE</h2>
            <ol>
                @foreach($dossier->documents->sortBy('ordre') as $doc)
                <li>{{ $doc->typeDocument->nom }}</li>
                @endforeach
            </ol>
        </div>
    </div>
    @endif

    <!-- INFORMATIONS ENTREPRISE -->
    <div class="document-section">
        <h2>🏢 Informations de l'entreprise</h2>
        
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Nom</td>
                <td>{{ $dossier->entreprise->nom }}</td>
            </tr>
            <tr>
                <td>Sigle</td>
                <td>{{ $dossier->entreprise->sigle ?? '—' }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $dossier->entreprise->email ?? '—' }}</td>
            </tr>
            <tr>
                <td>Téléphone</td>
                <td>{{ $dossier->entreprise->telephone ?? '—' }}</td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td>{{ $dossier->entreprise->adresse ?? '—' }}</td>
            </tr>
            <tr>
                <td>Responsable</td>
                <td>{{ $dossier->entreprise->responsable ?? '—' }}</td>
            </tr>
            <tr>
                <td>Fonction</td>
                <td>{{ $dossier->entreprise->fonction_responsable ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <!-- DOCUMENTS -->
    @foreach($dossier->documents->sortBy('ordre') as $index => $document)
    <div class="document-section">
        <h2>{{ $index + 1 }}. {{ $document->typeDocument->nom }}</h2>

        @if($document->valeurs->count() > 0)
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            @foreach($document->valeurs as $valeur)
            <tr>
                <td>{{ $valeur->champDocument->label }}</td>
                <td>{{ $valeur->valeur }}</td>
            </tr>
            @endforeach
        </table>
        @else
        <p style="color: #999;">Aucune donnée remplie pour ce document.</p>
        @endif

        <div class="page-number">
            Page {{ $index + 2 }}
        </div>
    </div>
    @endforeach
</body>
</html>
