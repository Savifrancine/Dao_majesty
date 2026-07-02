<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $doc->typeDocument->nom }}</title>
    <style>
        @page { margin: 20mm }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .heading { color:#0b63b5; margin-bottom:10px; }
        .section { margin-bottom:14px; }
        .text-block { margin-bottom:8px; }
        .img-preview { max-width:100%; height:auto; margin-bottom:10px; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    </style>
</head>
<body>
    <div>
        <h2 class="heading">{{ $doc->typeDocument->nom }}</h2>

        @if($doc->valeurs->count() > 0)
            <div class="section">
                <strong>Valeurs / fonctions :</strong>
                <table>
                    <tbody>
                        @foreach($doc->valeurs as $val)
                            <tr>
                                <td><strong>{{ optional($val->champDocument)->label ?? optional($val->champDocument)->nom_champ ?? 'Champ' }}</strong></td>
                                <td>{!! nl2br(e($val->valeur)) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($doc->fichiers->count() > 0)
            @foreach($doc->fichiers as $f)
                @php
                    $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                    $mime = file_exists($full) ? mime_content_type($full) : null;
                @endphp
                @if(file_exists($full) && str_starts_with($mime, 'image/'))
                    @php $data = base64_encode(file_get_contents($full)); @endphp
                    <img class="img-preview" src="data:{{ $mime }};base64,{{ $data }}" alt="Document image" />
                @endif
            @endforeach
        @endif

        @if($doc->typeDocument->nom === "Déclaration de garantie d'offre" && $doc->valeurs->count() > 0)
            <div class="section">
                <strong>Texte de la déclaration :</strong>
                @php
                    $lines = $doc->valeurs->map(fn($v)=>$v->champDocument->label.': '.$v->valeur)->implode("\n");
                @endphp
                <p>{!! nl2br(e($lines)) !!}</p>
            </div>
        @endif

    </div>
</body>
</html>