<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dossier->nom_dossier ?? 'Dossier' }}</title>
    <style>
        @page { margin: 12mm 20mm 20mm 20mm; }
        @page :first { margin-top: 10mm; }
        body { font-family: 'DejaVu Sans', Calibri, Segoe UI, Arial, Helvetica, sans-serif; color: #222; margin: 0; font-size: 17px; }

        header.pdf-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 55px;
            background: #fafbfc;
            border-bottom: 1px solid #0b63b5;
            padding: 8px 16px;
            font-size: 11px;
            color: #1f78d1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        .pdf-header .title { font-weight: 700; }
        .pdf-header .subtitle { font-size: 10px; color: #0b63b5; }

        .cover { text-align:center; padding: 10mm 18px 12mm; }

        .title-page {
            width: 100%;
            margin: 0;
            padding: 0;
            page-break-after: auto;
            page-break-inside: avoid;
        }

        .title-page .title-text {
            margin-top: 125mm;
            text-align: center;
            font-size: 22px;
            font-weight: 900;
            color: #0b63b5;
            line-height: 1.15;
            width: 100%;
        }

        .page-break-after { page-break-after: always; }

        table,
        table th,
        table td {
            font-size: 12px !important;
        }

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

        .framed { margin:17px auto 17px; max-width:90%; border:12px solid #0b63b5; padding:6px; background:#0b63b5; }
        .framed .inner { background:#fff; padding:19px 19px; text-align:center; color:#0b63b5; }
        .framed .inner .main { font-size:25px; font-weight:800; line-height:1.2; }
        .framed .inner .sub { margin-top:10px; font-size:16px; line-height:1.25; }

        .big-subtitle { color:#0b63b5; font-weight:800; font-size:19px; margin-top:13px; letter-spacing:0.4px; }

        .meta-block { width:80%; margin:15px auto 7px; font-size:14px; color:#222; text-align:left; }
        .meta-block .line { margin-bottom:7px; line-height:1.45; }

        .center-date { text-align:center; margin-top:15px; font-weight:700; font-size:17px; }
        .cover-info-line { margin-top: 5px; }
        .cover-info-lines .cover-info-line:first-child { margin-top: 0; }
    </style>
</head>
<body>
    @php
        $renderMode = $renderMode ?? 'full';
        $logoDataUri = '';
        $logo = optional($dossier->entreprise)->logo ?? null;

        // Priorité : logo de l'entreprise (chemin stocké dans base de données)
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

        // fallback : storage/app/public/entreprises/logos/logo.jpeg (emplacement existant)
        if (!$logoDataUri) {
            $fallback = storage_path('app/public/entreprises/logos/logo.jpeg');
            if (file_exists($fallback)) {
                $ext = pathinfo($fallback, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($fallback));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }

        // fallback secondaire : storage/app/public/entreprises/logo.jpeg
        if (!$logoDataUri) {
            $fallback = storage_path('app/public/entreprises/logo.jpeg');
            if (file_exists($fallback)) {
                $ext = pathinfo($fallback, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($fallback));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }

        // fallback alternatif (public/logo.jpeg)
        if (!$logoDataUri) {
            $fallback2 = public_path('logo.jpeg');
            if (file_exists($fallback2)) {
                $ext = pathinfo($fallback2, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($fallback2));
                $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
            }
        }

        // construction du tag HTML de logo
        if ($logoDataUri) {
            $imgTag = '<img src="' . $logoDataUri . '" style="height:100px; width:auto; display:block;" alt="Logo">';
        } else {
            $imgTag = '<div style="color:#888; font-size:10px; text-align:center; width:130px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO MANQUANT</div>';
        }

        $date_envoi = now()->format('d/m/Y');
    @endphp

    @if(in_array($renderMode, ['full', 'cover'], true))
    <div class="cover" style="{{ $renderMode === 'full' ? 'page-break-after:always;' : '' }}">
        @if(!empty($pageGardeDataUri) && str_starts_with($pageGardeDataUri,'data:image'))
            <img src="{{ $pageGardeDataUri }}" alt="Page de garde" style="max-width:100%; height:auto;" />
        @else
            <div style="font-family: Calibri, Segoe UI, Arial, sans-serif; color:#1f78d1; width:100%; margin-bottom:16px;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:115px; vertical-align:top; padding-right:14px;">{!! $imgTag !!}</td>
                        <td style="vertical-align:top; text-align:center;">
                            <div style="font-size:19px; font-weight:700; line-height:1.15; margin-bottom:2px;">MAJESTY SERVICES ET EQUIPEMENTS</div>
                            <div style="font-size:11px; line-height:1; margin-bottom:2px;">Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire</div>
                            <div style="font-size:11px; line-height:1; margin-bottom:2px;">Maintenance – Service Apres Vente</div>
                            <div style="width:85%; margin:2px auto 4px auto; border-top:1px solid #1f78d1;"></div>
                            <div style="font-size:10.5px; line-height:1; margin-bottom:2px;">06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – Republique du Benin</div>
                            <div style="font-size:10.5px; line-height:1;">IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 - email : contact@majestyse.com / majestyse@gmail.com</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="cover-info-lines" style="margin-top:15px; font-size:15px; text-align:center;">
                <div class="cover-info-line" style="font-weight:700;">{{ strtoupper($dossier->republique ?? 'REPUBLIQUE DU BENIN') }}</div>
                @if($dossier->ministere)
                    <div class="cover-info-line">{{ strtoupper($dossier->ministere) }}</div>
                @endif
                @if($dossier->services_projet)
                    <div class="cover-info-line">{{ strtoupper($dossier->services_projet) }}</div>
                @endif
                @if($dossier->titre_dossier)
                    <div class="cover-info-line" style="font-weight:700; margin-top:9px;">{{ strtoupper($dossier->titre_dossier) }}</div>
                @endif
                @if($dossier->ref)
                    <div class="cover-info-line" style="margin-top:9px;">REFERENCE DU DOSSIER: {{ $dossier->ref }}</div>
                @endif
            </div>

                @if($dossier->nom_dossier)
                    <div style="text-align:center; margin-top:7mm; font-size:15px; font-weight:700;">LA PERSONNE RESPONSABLE DES MARCHÉS PUBLICS</div>
                @endif

                @if($dossier->destinataires)
                    <div style="text-align:center; margin-top:3.5mm; font-size:15px;">{{ $dossier->destinataires }}</div>
                @endif

                @if($dossier->reference_dossier || $dossier->date_lancement)
                    <div style="text-align:center; margin-top:3.5mm; font-size:15px;">
                        {{ $dossier->reference_dossier ?? '' }}
                        @if($dossier->date_lancement)
                            @php
                                try {
                                    $dt = \Illuminate\Support\Carbon::parse($dossier->date_lancement)->locale('fr');
                                    $dateStr = strtoupper($dt->translatedFormat('d F Y'));
                                } catch (\Throwable $e) {
                                    $dateStr = $dossier->date_lancement;
                                }
                            @endphp
                            DU {{ $dateStr }}
                        @endif
                        PORTANT :
                    </div>
                @endif

                <div class="framed">
                    <div class="inner">
                        <div class="main">{{ strtoupper($dossier->nom_dossier ?? '') }}</div>
                        @if($dossier->titre_lot)
                            <div class="sub">{{ strtoupper($dossier->titre_lot) }}</div>
                        @endif
                        @if($dossier->lots)
                            <div style="margin-top:12px; font-size:14px; color:#000;">➤ {{ $dossier->lots }}</div>
                        @endif
                    </div>
                </div>

                <div class="big-subtitle">{{ strtoupper($dossier->type_offre ?? '') }}</div>

                <div class="meta-block">
                    @if($dossier->reference_step)
                        <div class="line"><strong>Ref STEP :</strong> {{ $dossier->reference_step }}</div>
                    @endif
                    @if($dossier->source_financement)
                        <div class="line"><strong>Source de financement :</strong> {{ $dossier->source_financement }}</div>
                    @endif
                    @if($dossier->gestion)
                        <div class="line"><strong>Gestion :</strong> {{ $dossier->gestion }}</div>
                    @endif
                    @if($dossier->imputation_budgetaire)
                        <div class="line"><strong>Imputation Budgétaire :</strong> {{ $dossier->imputation_budgetaire }}</div>
                    @endif
                    @if($dossier->accord_pret)
                        <div class="line"><strong>Accord de Prêt :</strong> {{ $dossier->accord_pret }}</div>
                    @endif
                </div>

                <div class="center-date">{{ strtoupper($dossier->mois_depot ?? '') }} {{ $dossier->annee_depot ?? '' }}</div>
            </div>
        @endif
    </div>
    @endif

    @if(in_array($renderMode, ['full', 'summary'], true) && $documents->count() > 0)
    <div style="{{ $renderMode === 'full' ? 'page-break-after:always; ' : '' }}margin-top:18px;">
        <h2 style="color:#0b63b5; margin-bottom:8px; font-size:18px; border-bottom:1px solid #0b63b5; padding-bottom:6px;">📋 SOMMAIRE</h2>
        <ol style="margin-left:16px; font-size:12px;">
            @foreach($documents as $doc)
                <li>{{ $doc->typeDocument->nom }}</li>
            @endforeach
        </ol>
    </div>
    @endif

    @php
        $numberToWords = fn ($value) => \App\Support\NumberToWords::french($value);
    @endphp

    {{-- Documents content --}}
    @if(!in_array($renderMode, ['cover', 'summary'], true))
        @foreach($documents as $doc)

            @php
                $docNameClean = trim($doc->typeDocument->nom ?? '');
                $isExp42a = strcasecmp($docNameClean, 'Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services') === 0;
                $isExp42b = strcasecmp($docNameClean, 'Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)') === 0;
                $isExp42_variant_a_suite = strcasecmp($docNameClean, 'Formulaire EXP – 4.2 a) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)') === 0;
                $isExp42_variant_b_simple = strcasecmp($docNameClean, 'Formulaire EXP – 4.2 b)  Expérience spécifique de fournitures') === 0;
                $isExp42 = $isExp42a || $isExp42b || $isExp42_variant_a_suite || $isExp42_variant_b_simple;
                $isUploadOnly = App\Models\TypeDocument::isUploadOnlyName($docNameClean, $doc->typeDocument->type_formulaire ?? null);
                $isExp42OrUploadOnly = $isExp42 || $isUploadOnly;
                $isLibre = trim($doc->typeDocument->type_formulaire ?? '') === 'libre';
                // Ensure common variables used by signature table are defined for every document
                $entreprise = $dossier->entreprise ?? null;
                $companyName = trim(optional($entreprise)->nom ?? optional($entreprise)->responsable ?? ($dossier->nom_dossier ?? ''));
                $date_soumission_display = optional($dossier)->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : '-';
                // Ces variables ne sont réellement renseignées que par le bloc du formulaire
                // ELI (candidat) plus bas ; on les initialise ici pour qu'elles existent
                // toujours, quel que soit le document en cours de traitement.
                $formSignataire = null;
                $formSignatureData = null;
                $formCachetData = null;
                // choose signatory: prefer form's signataire if present, else dossier's designated signataire
                $signataire = $formSignataire ?? ($dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first() ?? null);
                $signataire_nom = $signataire ? trim($signataire->nom . ' ' . ($signataire->prenom ?? '')) : trim(optional($entreprise)->responsable ?? '');
                $signataire_role = $signataire ? ($signataire->fonction ?: (optional($entreprise)->fonction_responsable ?? '')) : (optional($entreprise)->fonction_responsable ?? '');
            @endphp
            @if($renderMode !== 'doc')
            <div class="title-page">
                <div class="title-text">{{ $doc->typeDocument->nom }}</div>
            </div>
            @endif
            @if($renderMode !== 'title')
            <div style="margin-top:2px;">
            @if(!$isUploadOnly)
                @include('documents.partials.majesty_header', ['entreprise' => $entreprise])
            @endif
            @php
                $docName = trim($doc->typeDocument->nom ?? '');
                $isPermat = strcasecmp($docName, 'Formulaire PER') === 0 || strcasecmp($docName, 'Formulaire MAT') === 0;
                $isFormulaire = strcasecmp(trim($doc->typeDocument->type_formulaire ?? ''), 'formulaire') === 0;
            @endphp
            {{-- Display uploaded image files immediately after title for non-form and non-upload-only documents only --}}
            @if(!$isPermat && $doc->fichiers && $doc->fichiers->count() > 0 && !($isExp42 || $isUploadOnly) && !(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière') === 0 || strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière') === 0))
                <div style="margin-top:10px; margin-bottom:10px;">
                    @foreach($doc->fichiers as $f)
                        @php
                            $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                            $mime = file_exists($full) ? mime_content_type($full) : null;
                        @endphp
                        @if(file_exists($full) && str_starts_with($mime, 'image/'))
                            @php $data = base64_encode(file_get_contents($full)); @endphp
                            <div style="margin-bottom:12px; text-align:center;"><img src="data:{{ $mime }};base64,{{ $data }}" style="max-width:100%; height:auto;"></div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services') === 0)
                @php
                    $rows = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $intitules = $decoded['intitule'] ?? [];
                            $autorites = $decoded['autorite_contact'] ?? [];
                            $valeurs = $decoded['valeur_restante'] ?? [];
                            $dates = $decoded['date_achevement'] ?? [];
                            $montants = $decoded['montant_mensuel'] ?? [];
                            $count = max(count($intitules), count($autorites), count($valeurs), count($dates), count($montants));
                            for ($i = 0; $i < $count; $i++) {
                                $title = trim($intitules[$i] ?? '');
                                $autorite = trim($autorites[$i] ?? '');
                                $valeur = trim($valeurs[$i] ?? '');
                                $date = trim($dates[$i] ?? '');
                                $montant = trim($montants[$i] ?? '');
                                if ($title !== '' || $autorite !== '' || $valeur !== '' || $date !== '' || $montant !== '') {
                                    $rows[] = [
                                        'title' => $title,
                                        'autorite' => $autorite,
                                        'valeur' => $valeur,
                                        'date' => $date,
                                        'montant' => $montant,
                                    ];
                                }
                            }
                        }
                    }
                @endphp
                <div style="margin-top:10px; font-size:11px; line-height:1.4; page-break-inside:avoid; text-align:center;">
                    <div style="font-weight:700; font-size:15px; margin-bottom:8px;">Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours</div>
                    <div style="font-size:11px; text-align:left; margin:6px auto 12px auto; width:95%;">Les candidats et chaque membre de groupement doivent fournir les renseignements concernant leurs engagements courants pour tous les marchés attribués ou en cours.</div>

                    <div style="width:100%; overflow-x:auto; margin:10px 0;">
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead>
                                <tr style="background:#f3f4f6; font-weight:700;">
                                    <th style="border:1px solid #000; padding:8px; text-align:center; width:5%;">N°</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:left; width:30%;">Intitulé du marché</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:left; width:25%;">L'Autorité contractante, contact adresse/tél/télécopie</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:right; width:15%;">Valeur des services restant à exécuter (FCFA équivalents)</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:center; width:12%;">Date d'achèvement prévue</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:right; width:13%;">Montant moyen mensuel facturé (FCFA/mois)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $index => $row)
                                    <tr>
                                        <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">{{ $index + 1 }}.</td>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['title'] }}</td>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['autorite'] }}</td>
                                        <td style="border:1px solid #000; padding:8px; text-align:right; vertical-align:top;">{{ $row['valeur'] }}</td>
                                        <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">{{ $row['date'] }}</td>
                                        <td style="border:1px solid #000; padding:8px; text-align:right; vertical-align:top;">{{ $row['montant'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="border:1px solid #000; padding:12px; text-align:center; color:#444;">Aucune ligne renseignée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            @if(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services') === 0)
                @php
                    $rowsExp = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $deps = $decoded['mois_depart'] ?? [];
                            $fins = $decoded['mois_final'] ?? [];
                            $idents = $decoded['identification'] ?? [];
                            $roles = $decoded['role_candidat'] ?? [];
                            $count = max(count($deps), count($fins), count($idents), count($roles));
                            for ($i = 0; $i < $count; $i++) {
                                $d = trim($deps[$i] ?? '');
                                $f = trim($fins[$i] ?? '');
                                $ident = trim($idents[$i] ?? '');
                                $role = trim($roles[$i] ?? '');
                                if ($d !== '' || $f !== '' || $ident !== '' || $role !== '') {
                                    $rowsExp[] = ['depart' => $d, 'final' => $f, 'ident' => $ident, 'role' => $role];
                                }
                            }
                        }
                    }
                @endphp
                <div style="margin-top:10px; font-size:11px; line-height:1.4; page-break-inside:avoid; text-align:center;">
                    <div style="font-weight:700; font-size:15px; margin-bottom:8px;">Formulaire EXP – 4.1 : Expérience générale de fournitures/services</div>
                    <div style="width:100%; max-width:100%; margin:10px 0; text-align:left;">
                        <style>
                            .pdf-exp41-table th,
                            .pdf-exp41-table td {
                                word-break: break-word;
                                overflow-wrap: break-word;
                                white-space: normal;
                            }
                        </style>
                        <table class="pdf-exp41-table" style="width:100%; border-collapse:collapse; font-size:11px; table-layout:fixed;">
                            <thead>
                                <tr style="background:#f3f4f6; font-weight:700;">
                                    <th style="border:1px solid #000; padding:8px; text-align:center; width:16%;">Mois/année départ</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:center; width:16%;">Mois/année final</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:left; width:48%;">Identification du marché</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:center; width:20%;">Rôle du candidat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rowsExp as $idx => $r)
                                    <tr>
                                        <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">{{ $r['depart'] }}</td>
                                        <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">{{ $r['final'] }}</td>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;">{!! nl2br(e($r['ident'])) !!}</td>
                                        <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:top;">{{ $r['role'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="border:1px solid #000; padding:12px; text-align:center; color:#444;">Aucune expérience renseignée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($isLibre)
                @php
                    $libreContent = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $libreContent = $decoded;
                        }
                    }
                    $libreTexte = $libreContent['texte'] ?? '';
                    $libreTableaux = $libreContent['tableaux'] ?? [];
                    $libreDrpNumero = trim(preg_replace('/^\s*A?DRP\s*N°\s*/i', '', $dossier->reference_dossier ?? ''));
                    $libreDrp = $dossier->ref ?: 'N/A';
                    $libreRefLine = \App\Support\ReferenceLine::render(
                        'Référence :',
                        $libreDrpNumero !== '' ? $libreDrpNumero : $libreDrp,
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    <div style="text-align:center; font-weight:700; font-size:14px; text-transform:uppercase; margin-bottom:6px;">{{ $doc->typeDocument->nom }}</div>
                    <div style="text-align:right; font-size:11px; margin-bottom:12px;">{!! $libreRefLine !!}</div>

                    @if(trim($libreTexte) !== '')
                        <div style="text-align:justify; margin-bottom:14px;">{!! nl2br(e($libreTexte)) !!}</div>
                    @endif

                    @foreach($libreTableaux as $tableau)
                        @php
                            $libreColonnes = $tableau['colonnes'] ?? [];
                            $libreLignes = $tableau['lignes'] ?? [];
                        @endphp
                        @if(!empty($libreColonnes))
                            @if(!empty($tableau['titre']))
                                <div style="font-weight:700; margin-bottom:6px;">{{ $tableau['titre'] }}</div>
                            @endif
                            <table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:16px;">
                                <thead>
                                    <tr style="background:#f3f4f6; font-weight:700;">
                                        @foreach($libreColonnes as $col)
                                            <th style="border:1px solid #000; padding:6px; text-align:left;">{{ $col }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($libreLignes as $ligne)
                                        <tr>
                                            @foreach($libreColonnes as $ci => $col)
                                                <td style="border:1px solid #000; padding:6px;">{{ $ligne[$ci] ?? '' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endforeach
                </div>
            @elseif($isExp42OrUploadOnly)
                @php
                    $attachments = $doc->fichiers ?? collect();
                @endphp
                <div style="margin-top:10px; font-size:11px; line-height:1.4; page-break-inside:avoid; text-align:center;">
                    {{-- Display attached files like EXP 4.2 a) --}}
                    @php
                        $fileAttachments = $attachments->filter(function ($f) {
                            $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                            return in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true);
                        });
                    @endphp

                    @if($fileAttachments->isNotEmpty())
                        <div style="margin-top:14px; page-break-inside:avoid; text-align:center;">
                    @foreach($fileAttachments as $f)
                                @php
                                    $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                                    $mime = file_exists($full) ? mime_content_type($full) : null;
                                    $data = file_exists($full) ? base64_encode(file_get_contents($full)) : null;
                                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                                @endphp
                                @if($data && $mime && str_starts_with($mime, 'image/'))
                                    <div style="margin-bottom:14px; page-break-inside:avoid; text-align:center;">
                                        <img src="data:{{ $mime }};base64,{{ $data }}" alt="{{ basename($f->chemin_fichier) }}" style="display:block; margin:0 auto; max-width:100%; height:auto;" />
                                    </div>
                                @elseif($ext === 'pdf' && $data && $mime === 'application/pdf')
                                    @php
                                        $tempImagePath = null;
                                        try {
                                            // Essayer avec spatie/pdf-to-image
                                            if (class_exists('Spatie\PdfToImage\Pdf')) {
                                                $tempImagePath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.png';
                                                $pdf = new \Spatie\PdfToImage\Pdf($full);
                                                $pdf->setPage(1)
                                                    ->setResolution(150, 150)
                                                    ->save($tempImagePath);
                                            }
                                            // Fallback: Imagick
                                            elseif (extension_loaded('imagick')) {
                                                $imagick = new \Imagick();
                                                $imagick->setResolution(150, 150);
                                                $imagick->readImage($full . '[0]');
                                                $imagick->setImageFormat('png');
                                                $imagick->stripImage();

                                                $tempImagePath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.png';
                                                $imagick->writeImage($tempImagePath);
                                                $imagick->destroy();
                                            }
                                            // Fallback: Ghostscript
                                            else {
                                                $tempImagePath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.png';
                                                $gsPath = null;
                                                $gsCommands = [
                                                    'where gswin64c 2>nul',
                                                    'where gswin32c 2>nul',
                                                    'where gs 2>nul',
                                                    'which gswin64c 2>/dev/null',
                                                    'which gswin32c 2>/dev/null',
                                                    'which gs 2>/dev/null',
                                                ];
                                                foreach ($gsCommands as $command) {
                                                    $result = trim(shell_exec($command));
                                                    if ($result !== '') {
                                                        $gsPath = explode(PHP_EOL, $result)[0];
                                                        break;
                                                    }
                                                }
                                                if (!$gsPath) {
                                                    $commonPaths = [
                                                        'C:/Program Files/gs/*/bin/gswin64c.exe',
                                                        'C:/Program Files/gs/*/bin/gswin64.exe',
                                                        'C:/Program Files/gs/*/bin/gs.exe',
                                                        'C:/Program Files (x86)/gs/*/bin/gswin32c.exe',
                                                        'C:/Program Files (x86)/gs/*/bin/gswin32.exe',
                                                        'C:/Program Files (x86)/gs/*/bin/gs.exe',
                                                    ];
                                                    foreach ($commonPaths as $pattern) {
                                                        foreach (glob($pattern) as $candidate) {
                                                            if (file_exists($candidate)) {
                                                                $gsPath = $candidate;
                                                                break 2;
                                                            }
                                                        }
                                                    }
                                                }
                                                if (!$gsPath) {
                                                    $gsPath = 'gs';
                                                }
                                                $cmd = '"' . str_replace('\\', '/', $gsPath) . '" -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=png16m -r150 -sOutputFile="' . str_replace('\\', '/', $tempImagePath) . '" "' . str_replace('\\', '/', $full) . '" 2>&1';
                                                exec($cmd, $output, $returnCode);

                                                if ($returnCode !== 0 || !file_exists($tempImagePath)) {
                                                    $tempImagePath = null;
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            $tempImagePath = null;
                                        }
                                    @endphp

                                    @if($tempImagePath && file_exists($tempImagePath))
                                        <div style="margin-bottom:14px; page-break-inside:avoid; text-align:center;">
                                            @php
                                                $pdfImageData = base64_encode(file_get_contents($tempImagePath));
                                                @unlink($tempImagePath);
                                            @endphp
                                            <img src="data:image/png;base64,{{ $pdfImageData }}" alt="Page 1" style="display:block; margin:0 auto; max-width:100%; height:auto;" />
                                        </div>
                                    @else
                                        <div style="margin-bottom:8px; padding:12px; background:#fff3cd; border:1px solid #ffc107; border-radius:3px;">
                                            <div style="font-size:11px; font-weight:700; margin-bottom:6px; color:#856404;">📎 {{ basename($f->chemin_fichier) }}</div>
                                            <div style="font-size:9px; color:#856404; line-height:1.4;">Les outils de conversion PDF ne sont pas disponibles. Installer <strong>Ghostscript</strong>, <strong>ImageMagick</strong>, ou <code>composer require spatie/pdf-to-image</code></div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @elseif($attachments->isEmpty())
                        <div style="margin-top:8px; font-style:italic; color:#444;">Aucun fichier attaché pour ce document.</div>
                    @endif
                </div>
            @elseif($doc->typeDocument && $doc->typeDocument->type_formulaire === 'formulaire' && !$isExp42 && !$isUploadOnly && strcasecmp(trim($doc->typeDocument->nom), 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services') !== 0 && strcasecmp(trim($doc->typeDocument->nom), 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services') !== 0)
                @php
                    $attachments = $doc->fichiers ?? collect();
                    $imageAttachments = $attachments->filter(function ($f) {
                        $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
                    });
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    <div style="font-weight:700; margin-bottom:8px;">{{ $doc->typeDocument->nom }}</div>

                    @if($imageAttachments->isNotEmpty())
                        <div style="margin-top:10px;">
                            @foreach($imageAttachments as $f)
                                @php
                                    $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                                    $mime = file_exists($full) ? mime_content_type($full) : null;
                                    $data = file_exists($full) ? base64_encode(file_get_contents($full)) : null;
                                @endphp
                                @if($data && str_starts_with($mime, 'image/'))
                                    <div style="margin-bottom:14px;">
                                        <img src="data:{{ $mime }};base64,{{ $data }}" alt="{{ basename($f->chemin_fichier) }}" style="max-width:100%; height:auto; border:1px solid #ccc;" />
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($attachments->isEmpty())
                        {{-- Pas de rendu de liste de fichiers pour ce document --}}
                    @endif
                </div>
            @elseif($doc->fichiers && $doc->fichiers->count() > 0 && !(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière') === 0 || strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière') === 0 || $isExp42 || $isUploadOnly))
                @php
                    $attachments = $doc->fichiers;
                    $imageAttachments = $attachments->filter(function ($f) {
                        $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
                    });
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    <div style="font-weight:700; margin-bottom:8px;">{{ $doc->typeDocument->nom }}</div>

                    @if($imageAttachments->isNotEmpty())
                        <div style="margin-top:10px;">
                            @foreach($imageAttachments as $f)
                                @php
                                    $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                                    $mime = file_exists($full) ? mime_content_type($full) : null;
                                    $data = file_exists($full) ? base64_encode(file_get_contents($full)) : null;
                                @endphp
                                @if($data && str_starts_with($mime, 'image/'))
                                    <div style="margin-bottom:14px;">
                                        <img src="data:{{ $mime }};base64,{{ $data }}" alt="{{ basename($f->chemin_fichier) }}" style="max-width:100%; height:auto; border:1px solid #ccc;" />
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                </div>
            @endif

            @if(!$isUploadOnly)
                <br><br>
                <table class="signature-table">
                <tr>
                    <td style="width:25%;"><strong>Nom :</strong></td>
                    <td style="width:75%;">{{ strtoupper($signataire_nom ?? '') }}</td>
                </tr>
                <tr>
                    <td><strong>En tant que :</strong></td>
                    <td>{{ $signataire_role ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>Signature :</strong></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:12px;">
                        <div style="margin-top:6px;">Ayant pouvoir de signer l'offre pour et au nom et pour le compte de <strong>{{ optional($entreprise)->nom ?? $companyName ?: '...' }}</strong>.</div>
                        <div style="margin-top:6px;">En date du {{ $date_soumission_display }}.</div>
                    </td>
                </tr>
            </table>
            @endif
        </div>
            @endif
    @endforeach
@endif

    {{-- Tableau Description technique des fournitures/services avec meilleure mise en page --}}
    @if($documents->count() > 0)
        @foreach($documents as $doc)
            @php
                $isDescriptionTechnique = strcasecmp(trim($doc->typeDocument->nom ?? ''), 'Description technique des fournitures/services') === 0;
            @endphp
            @if($isDescriptionTechnique && $doc->bordereau && $doc->bordereau->count() > 0)
                <div style="page-break-before:always; padding:20mm;">
                    <h2 style="color:#0b63b5; text-align:center; margin-bottom:12px; font-size:16px; font-weight:700;">DESCRIPTION TECHNIQUE DES FOURNITURES/SERVICES</h2>
                    
                    <style>
                        .pdf-description-technique-table {
                            table-layout: fixed;
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 9px;
                            line-height: 1.3;
                        }
                        
                        .pdf-description-technique-table th,
                        .pdf-description-technique-table td {
                            padding: 6px;
                            vertical-align: top;
                            word-wrap: break-word;
                            overflow-wrap: break-word;
                            white-space: normal;
                            word-break: break-word;
                            page-break-inside: avoid;
                        }
                        
                        .pdf-description-technique-table thead tr {
                            background: #f3f4f6;
                            font-weight: 700;
                        }
                        
                        .pdf-description-technique-table tbody tr:nth-child(even) {
                            background: #fafbfc;
                        }
                    </style>

                    @foreach($doc->bordereau as $bordereau)
                        @if($bordereau->titre)
                            <div style="font-weight:700; font-size:11px; text-align:center; margin:14px 0 10px 0; background:#f3f4f6; padding:6px; border:1px solid #ddd;">
                                {{ strtoupper($bordereau->titre) }}
                            </div>
                        @endif

                        <table class="pdf-description-technique-table" style="margin-bottom:16px; border:1px solid #000;">
                            <thead>
                                <tr style="background:#f3f4f6;">
                                    <th style="border:1px solid #000; text-align:center; width:5%;">N°</th>
                                    <th style="border:1px solid #000; text-align:left; width:18%;">Désignation</th>
                                    <th style="border:1px solid #000; text-align:left; width:25%;">Spécifications techniques</th>
                                    <th style="border:1px solid #000; text-align:left; width:25%;">Spécifications obligatoires</th>
                                    <th style="border:1px solid #000; text-align:left; width:27%;">Spécifications proposées</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bordereau->lignes as $index => $ligne)
                                    <tr>
                                        <td style="border:1px solid #000; text-align:center;">{{ $index + 1 }}</td>
                                        <td style="border:1px solid #000;">{!! nl2br(e($ligne->designation ?? '')) !!}</td>
                                        <td style="border:1px solid #000;">{!! nl2br(e($ligne->specifications_techniques ?? '')) !!}</td>
                                        <td style="border:1px solid #000;">{!! nl2br(e($ligne->specifications_obligatoires ?? '')) !!}</td>
                                        <td style="border:1px solid #000;">{!! nl2br(e($ligne->specifications_proposees ?? '')) !!}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="border:1px solid #000; padding:12px; text-align:center; color:#666;">Aucune ligne renseignée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endforeach
                </div>
            @endif
        @endforeach
    @endif

    {{-- entreprise info removed as requested --}}
</body>
</html>
