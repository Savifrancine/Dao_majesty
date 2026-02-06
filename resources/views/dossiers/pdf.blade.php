<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dossier->nom_dossier ?? 'Dossier' }}</title>
    <style>
        @page { margin: 20mm }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .cover { text-align:center; padding: 10px 18px; }
        .top-row { display:flex; justify-content:space-between; align-items:center; }
        .top-left, .top-right { width:30%; }
        .top-center { width:40%; text-align:center; }
        .gov { font-size:12px; letter-spacing:1px; font-weight:700; }
        .ministry { font-size:14px; margin-top:6px; }
        .project { font-size:11px; margin-top:8px; color:#444 }
        .aao-box { border:6px solid #0b63b5; padding:18px; margin:20px auto; max-width:85%; background:#fff; }
        .aao-title { font-size:16px; font-weight:700; color:#0b63b5; }
        .aao-sub { font-size:13px; margin-top:8px }
        .big-title { font-size:20px; font-weight:700; margin-top:18px; color:#0b63b5 }
        .subtitle { font-size:14px; color:#0b63b5; margin-top:14px }
        .meta { margin-top:16px; text-align:left; display:inline-block; width:100%; }
        .meta .row { margin-bottom:6px }
        .footer { margin-top:32px; font-size:12px; color:#666 }
    </style>
</head>
<body>

    <div class="cover">
        @if(!empty($pageGardeDataUri) && str_starts_with($pageGardeDataUri,'data:image'))
            <img src="{{ $pageGardeDataUri }}" alt="Page de garde" style="max-width:100%; height:auto;" />
        @else
            <div class="top-row">
                <div class="top-left">
                    @if(optional($dossier->entreprise)->logo)
                        <img src="{{ optional($dossier->entreprise)->logo }}" style="max-width:120px;" alt="Logo">
                    @endif
                </div>
                <div class="top-center">
                    <div class="gov">{{ strtoupper($dossier->republique ?? 'RÉPUBLIQUE') }}</div>
                    <div class="ministry">{{ $dossier->ministere ?? '' }}</div>
                    <div class="project">{{ $dossier->services_projet ?? '' }}</div>
                </div>
                <div class="top-right" style="text-align:right">
                    {{-- reserved for partner logo --}}
                </div>
            </div>

            <div class="aao-box">
                <div class="aao-title">{{ strtoupper($dossier->nom_dossier ?? '') }}</div>
                @if($dossier->reference_dossier)
                    <div class="aao-sub">Réf: {{ $dossier->reference_dossier }}</div>
                @endif
                @if($dossier->titre_lot)
                    <div style="margin-top:8px;">{{ $dossier->titre_lot }}</div>
                @endif
            </div>

            <div class="big-title">OFFRES ADMINISTRATIVE, TECHNIQUE ET FINANCIÈRE</div>

            <div class="meta">
                @if($dossier->type_offre)
                    <div class="row"><strong>Type d'offre :</strong> {{ $dossier->type_offre }}</div>
                @endif
                @if($dossier->lots)
                    <div class="row"><strong>Lots concernés :</strong> {{ $dossier->lots }}</div>
                @endif
                @if($dossier->autres_details)
                    <div class="row"><strong>Autres détails :</strong> {{ $dossier->autres_details }}</div>
                @endif
                @if($dossier->destinataires)
                    <div class="row"><strong>Destinataires :</strong> {{ $dossier->destinataires }}</div>
                @endif
            </div>

            <div class="footer">
                @if($dossier->mois_depot || $dossier->annee_depot)
                    <div>{{ strtoupper($dossier->mois_depot ?? '') }} {{ $dossier->annee_depot ?? '' }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Sommaire and rest of document (kept minimal) --}}
    @if($dossier->documents->count() > 0)
    <div style="page-break-after:always; margin-top:20px;">
        <h2 style="color:#0b63b5;">📋 SOMMAIRE</h2>
        <ol>
            @foreach($dossier->documents->sortBy('ordre') as $doc)
                <li>{{ $doc->typeDocument->nom }}</li>
            @endforeach
        </ol>
    </div>
    @endif

    <div>
        <h3>Informations entreprise</h3>
        <table style="width:100%; border-collapse:collapse;">
            <tr><th style="text-align:left">Nom</th><td>{{ optional($dossier->entreprise)->nom }}</td></tr>
            <tr><th style="text-align:left">Sigle</th><td>{{ optional($dossier->entreprise)->sigle ?? '—' }}</td></tr>
            <tr><th style="text-align:left">Email</th><td>{{ optional($dossier->entreprise)->email ?? '—' }}</td></tr>
        </table>
    </div>

</body>
</html>
