<!doctype html>
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
    </style>
    <style>
        /* Top logos and header */
        .top-logos { display:flex; justify-content:space-between; align-items:center; width:100%; }
        .top-logos .left, .top-logos .right { width:28%; }
        .top-logos .center { width:44%; text-align:center; }
        .top-logos .left img, .top-logos .right img { max-height:120px; width:auto; display:block; }
        .top-logos .center { padding-top:8px; }
        .blue-rule { height:6px; background:#0b63b5; margin:10px 0 10px 0; }

        .head-title { font-size:22px; font-weight:700; margin-top:6px; }
        .head-sub { font-size:13px; margin-top:6px; }

        .framed { margin:18px auto; max-width:88%; border:14px solid #0b63b5; padding:6px; background:#0b63b5; }
        .framed .inner { background:#fff; padding:20px; text-align:center; color:#0b63b5; }
        .framed .inner .main { font-size:20px; font-weight:800; }
        .framed .inner .sub { margin-top:10px; font-size:15px; }

        .big-subtitle { color:#0b63b5; font-weight:800; font-size:16px; margin-top:12px; }

        .meta-block { width:80%; margin:18px auto 6px; font-size:11px; color:#222; text-align:left; }
        .meta-block .line { margin-bottom:6px }

        .center-date { text-align:center; margin-top:28px; font-weight:700; }
    </style>
</head>
<body>

    <div class="cover">
        @if(!empty($pageGardeDataUri) && str_starts_with($pageGardeDataUri,'data:image'))
            <img src="{{ $pageGardeDataUri }}" alt="Page de garde" style="max-width:100%; height:auto;" />
        @else
            @php
                $logoDataUri = '';
                $logo = optional($dossier->entreprise)->logo ?? null;
                if ($logo) {
                    if (str_starts_with($logo, 'data:') || str_starts_with($logo, 'http')) {
                        $logoDataUri = $logo;
                    } else {
                        $candidate = storage_path('app/public/' . ltrim($logo, '/'));
                        if (file_exists($candidate)) {
                            $ext = pathinfo($candidate, PATHINFO_EXTENSION);
                            $data = base64_encode(file_get_contents($candidate));
                            $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
                        }
                    }
                }
            @endphp
            <div>
                <div class="top-logos">
                    <div class="left">
                        @if(!empty($logoDataUri))
                            <img src="{{ $logoDataUri }}" alt="Logo">
                        @endif
                    </div>
                    <div class="center">
                        <div style="font-weight:700;">Groupement</div>
                    </div>
                    <div class="right" style="text-align:right;">
                        @if(optional($dossier->entreprise)->sigle)
                            <div style="font-weight:700">{{ optional($dossier->entreprise)->sigle }}</div>
                        @endif
                    </div>
                </div>

                <div class="blue-rule"></div>

                <div style="text-align:center;">
                    <div class="head-title">{{ strtoupper($dossier->republique ?? 'REPUBLIQUE DU BENIN') }}</div>
                    @if($dossier->ministere)
                        <div class="head-sub">{{ strtoupper($dossier->ministere) }}</div>
                    @endif
                    @if($dossier->services_projet)
                        <div class="head-sub">{{ strtoupper($dossier->services_projet) }}</div>
                    @endif
                </div>

                @if($dossier->nom_dossier)
                    <div style="text-align:center; margin-top:12px; font-size:12px;">LA PERSONNE RESPONSABLE DES MARCHÉS PUBLICS</div>
                @endif

                {{-- Destinataires, référence et date de lancement (comme sur l'exemple) --}}
                @if($dossier->destinataires)
                    <div style="text-align:center; margin-top:8px; font-size:12px;">{{ $dossier->destinataires }}</div>
                @endif

                @if($dossier->reference_dossier || $dossier->date_lancement)
                    <div style="text-align:center; margin-top:10px; font-size:12px;">
                        AAO {{ $dossier->reference_dossier ?? '' }}
                        @if($dossier->date_lancement)
                            @php
                                try {
                                    $dt = \Illuminate\Support\Carbon::parse($dossier->date_lancement);
                                    $dateStr = strtoupper($dt->translatedFormat('d F Y'));
                                } catch (\Throwable $e) {
                                    $dateStr = $dossier->date_lancement;
                                }
                            @endphp
                            DU {{ $dateStr }}
                        @endif
                        PORTANT
                    </div>
                @endif

                <div class="framed">
                    <div class="inner">
                        <div class="main">{{ strtoupper($dossier->nom_dossier ?? '') }}</div>
                        @if($dossier->titre_lot)
                            <div class="sub">{{ strtoupper($dossier->titre_lot) }}</div>
                        @endif
                        @if($dossier->lots)
                            <div style="margin-top:12px; font-size:12px;">➤ {{ $dossier->lots }}</div>
                        @endif
                    </div>
                </div>

                <div class="big-subtitle">OFFRES ADMINISTRATIVE, TECHNIQUE ET FINANCIÈRE</div>

                <div class="meta-block">
                    @if($dossier->reference_dossier)
                        <div class="line"><strong>Ref STEF :</strong> {{ $dossier->reference_dossier }}</div>
                    @endif
                    @if($dossier->autres_details)
                        <div class="line"><strong>Source de financement :</strong> {{ $dossier->autres_details }}</div>
                    @endif
                    <div class="line"><strong>Gestion :</strong> {{ $dossier->annee_depot ?? '' }}</div>
                </div>

                <div class="center-date">{{ strtoupper($dossier->mois_depot ?? '') }} {{ $dossier->annee_depot ?? '' }}</div>
            </div>
        @endif
    </div>

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

    {{-- entreprise info removed as requested --}}

</body>
</html>
