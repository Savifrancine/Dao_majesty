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

            @if(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours') === 0)
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

            @if($isExp42OrUploadOnly)
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
                    @endif
                </div>
            @endif

            @php
                $isGarantie = strcasecmp(trim($doc->typeDocument->nom), "Déclaration de garantie d'offre") === 0 || strcasecmp(trim($doc->typeDocument->nom), 'Declaration de garantie d\'offre') === 0;
                $isQualification = strcasecmp(trim($doc->typeDocument->nom), 'Formulaire de qualification') === 0;
                $isCandidateForm = strcasecmp(trim($doc->typeDocument->nom), 'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat') === 0;
                $isLettreSoumission = strcasecmp(trim($doc->typeDocument->nom), 'Lettre de soumission') === 0;
                $isPlanCharge = strcasecmp(trim($doc->typeDocument->nom), 'Plan de charge') === 0;
                $isLibre = trim($doc->typeDocument->type_formulaire ?? '') === 'libre';
                $formattedDateSoumission = $dossier->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : '-';
                $formattedDateLancement = $dossier->date_lancement ? \Illuminate\Support\Carbon::parse($dossier->date_lancement)->format('d/m/Y') : '-';
                $attention = $dossier->services_projet ?: $dossier->destinataires ?: 'AGENCE NON DEFINIE';
                $drp = $dossier->ref ?: 'N/A';
                // Reprend le numéro et la date de l'ADRP tels qu'affichés sur la page de
                // garde ("ADRP N° ... DU {date}"), mais avec le libellé "DRP N°" utilisé
                // sur les pages intérieures du dossier.
                $drpNumero = trim(preg_replace('/^\s*A?DRP\s*N°\s*/i', '', $dossier->reference_dossier ?? ''));
                $drpDateStr = \App\Support\ReferenceLine::formatDate($dossier, true);
                $drpFormatted = \App\Support\ReferenceLine::render(
                    'DRP N°',
                    $drpNumero !== '' ? $drpNumero : $drp,
                    $drpDateStr,
                    $dossier->nom_dossier ?? '',
                    $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom),
                    'DU'
                );
                $entreprise = $dossier->entreprise;
            @endphp

            @if($isGarantie)
                <style>
                    .pdf-garantie p { margin: 5px 0; }
                    .pdf-garantie ol, .pdf-garantie ul { margin: 4px 0 6px 20px; }
                    .pdf-garantie li { margin-bottom: 2px; }
                </style>
                <div class="pdf-garantie" style="font-size:11px; line-height:1.38; margin-top:10px;">
                    <div style="text-align:center; font-weight:700; font-size:14px; text-transform:uppercase; margin-bottom:10px;">Déclaration de garantie d'offre</div>
                    <div style="text-align:right; font-weight:700;">Date : {{ $formattedDateSoumission }}</div>
                    <div style="text-align:right; font-weight:700; margin-bottom:10px;">{{ $drpFormatted }}</div>

                    <p><strong>A l'attention de {{ strtoupper($attention) }}</strong></p>

                    <p><strong>Nous, soussignés, déclarons que :</strong></p>

                    <p>1. Nous reconnaissons que les offres doivent être accompagnées d'une déclaration de garantie d'offre.</p>

                    <p>2. Nous acceptons que nous ferons l'objet d'une suspension du droit de participer à la commande publique pour une période qui ne saurait être inférieure à un (01) an, si nous n'exécutons pas une des obligations auxquelles nous sommes tenus en vertu de l'offre, à savoir :</p>
                    <ol type="a" style="margin-left:20px;">
                        <li>Si nous retirons l'offre pendant la période de validité spécifiée dans la lettre de soumission de l'offre ; ou</li>
                        <li>s'étant vu notifier l'acceptation de l'offre par l'Autorité contractante pendant la période de validité telle qu'indiquée dans la lettre de soumission de l'offre ou prorogée par l'Autorité contractante avant l'expiration de cette période ;
                            <ul style="margin-left:20px;">
                                <li>si nous n'acceptons pas les modifications de notre offre suite à la correction des erreurs de calcul ; ou</li>
                                <li>si nous ne signons pas le marché ; ou</li>
                                <li>si nous signons le marché et refusons de l'exécuter ; ou</li>
                                <li>si nous ne fournissons pas la garantie de bonne exécution du marché, si nous sommes tenus de le faire ainsi qu'il est prévu dans les Instructions aux candidats ; ou</li>
                            </ul>
                        </li>
                        <li>si nous sommes sous le coup d'une sanction de l'Autorité de régulation des marchés publics ou d'une juridiction administrative compétente, ayant pour objet la confiscation des garanties que nous avons constituées dans le cadre de la passation du marché, conformément à l'article 123 de la loi n°2020-26 du 29 septembre 2020 portant code des marchés publics en République du Bénin.</li>
                    </ol>

                    <p>3. La présente lettre de déclaration de garantie expirera si le marché ne nous est pas attribué, à la première des dates suivantes : (i) lorsque nous recevrons copie de votre notification du nom du soumissionnaire retenu, ou (ii) trente (30) jours suivant l'expiration du délai de validité de notre offre.</p>

                    <p>4. Il est entendu que si nous sommes un groupement d'entreprises, la déclaration de garantie d'offre doit être au nom du groupement qui soumet l'offre. Si le groupement n'a pas été formellement constitué lors du dépôt d'offre, la déclaration de garantie de l'offre doit être au nom de tous les futurs membres du groupement nommés dans la lettre de déclaration.</p>

                </div>
            @elseif($isQualification)
                @php
                    $qualificationVals = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $qualificationVals = $decoded;
                        }
                    }
                    $qSociete = $qualificationVals['societe'] ?? optional($entreprise)->nom ?? '';
                    $qDate = !empty($qualificationVals['date']) ? \Illuminate\Support\Carbon::parse($qualificationVals['date'])->format('d/m/Y') : $formattedDateSoumission;
                    $qReference = $qualificationVals['reference'] ?? $drp;
                    $qReferenceLine = \App\Support\ReferenceLine::render(
                        'Référence :',
                        $qReference,
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    <div style="text-align:right; font-weight:700;">Date : {{ $qDate }}</div>
                    <div style="text-align:center; font-weight:700; font-size:14px; margin:10px 0 16px;">FORMULAIRE DE QUALIFICATION</div>

                    <p style="text-align:justify;">Nous soussignés, <strong>{{ $qSociete }}</strong>, certifions l'exactitude des informations ci-après, attestant que nous remplissons les conditions de qualifications requises pour exécuter le Marché, fixées par l'Autorité contractante, à savoir :</p>

                    <div style="text-align:justify;">
                        {!! view('documents.partials.formulaire_qualification_content', [
                            'societe' => $qSociete,
                            'nombre_marches' => $qualificationVals['nombre_marches'] ?? null,
                            'marches' => $qualificationVals['marches'] ?? [],
                        ])->render() !!}
                    </div>

                    <div style="margin-top:20px; font-size:10px;">{!! $qReferenceLine !!}</div>
                </div>
            @elseif($isLettreSoumission)
                @php
                    $content = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $content = $decoded;
                        }
                    }
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px; text-align:left;">
                    {!! view('documents.partials.lettre_soumission_pdf_content', array_merge($content, ['dossier' => $dossier]))->render() !!}
                </div>
            @elseif($isPlanCharge)
                @php
                    $content = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $content = $decoded;
                        }
                    }
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px; text-align:left; page-break-inside:avoid;">
                    {!! view('documents.plan_charge_pdf', array_merge($content, ['dossier' => $dossier]))->render() !!}
                </div>
            @elseif($isCandidateForm)
                @php
                    // Do NOT pre-fill form signatory from dossier signataires here.
                    // The form's signatory data should come from the saved form content only.
                    $formSignataire = null;
                    $formSignatureData = null;
                    $formCachetData = null;
                    $eliRefLine = \App\Support\ReferenceLine::render(
                        'ADRP Numéro :',
                        trim(preg_replace('/^\s*A?DRP\s*N°\s*/i', '', $dossier->reference_dossier ?? '')) ?: ($dossier->reference_dossier ?? 'N/A'),
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    <div style="text-align:center; font-weight:700; font-size:14px; margin-bottom:10px;">FORMULAIRE DE RENSEIGNEMENTS SUR LE CANDIDAT</div>
                    <div style="text-align:right; font-weight:700; margin-bottom:12px;">Date : {{ $formattedDateSoumission }}</div>
                    <div style="text-align:right; font-weight:700; margin-bottom:18px;">{!! $eliRefLine !!}</div>
                    <table style="width:100%; border-collapse:collapse; font-size:11px;">
                        <tr>
                            <td style="border:1px solid #000; padding:8px; width:40px; font-weight:700;">1.</td>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">Nom du candidat :</td>
                            <td style="border:1px solid #000; padding:8px;">{{ optional($entreprise)->nom ?? 'MAJESTY SERVICES ET EQUIPEMENTS SARL' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">2.</td>
                            <td colspan="2" style="border:1px solid #000; padding:8px;">En cas de groupement, noms de tous les membres : Néant</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">3.</td>
                            <td style="border:1px solid #000; padding:8px; width:50%;"><strong>Pays où le candidat est, ou sera légalement enregistré :</strong> {{ optional($entreprise)->pays ?? 'Benin' }}</td>
                            <td style="border:1px solid #000; padding:8px; width:50%;"><strong>Numéro d'identification nationale des entreprises :</strong> {{ optional($entreprise)->rccm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">4.</td>
                            <td colspan="2" style="border:1px solid #000; padding:8px;"><strong>Année d'enregistrement du candidat :</strong> {{ optional($entreprise)->annee_enregistrement ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">5.</td>
                            <td colspan="2" style="border:1px solid #000; padding:8px;"><strong>Adresse officielle du candidat dans le pays d'enregistrement :</strong> {{ optional($entreprise)->adresse_officielle ?? optional($entreprise)->adresse ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">6.</td>
                            <td colspan="2" style="border:1px solid #000; padding:8px; font-weight:700;">Renseignement sur le représentant dûment habilité du candidat :
                                <div style="font-weight:normal; margin-top:6px;">
                                    <div><strong>Nom :</strong> {{ optional($entreprise)->responsable ?? '-' }}</div>
                                    <div><strong>Adresse :</strong> {{ optional($entreprise)->adresse_officielle ?? optional($entreprise)->adresse ?? '-' }}</div>
                                    <div><strong>Téléphone/Fac-similé :</strong> {{ optional($entreprise)->telephone ?? '-' }}</div>
                                    <div><strong>Adresse électronique :</strong> {{ optional($entreprise)->email ?? '-' }}</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">7.</td>
                            <td colspan="2" style="border:1px solid #000; padding:8px; font-weight:700;">Ci-joint copie des originaux des documents ci-après : [cocher la (les) case(s) correspondant aux documents originaux joints]
                                <div style="font-weight:normal; margin-top:6px;">
                                    <div style="margin-bottom:6px;">☑ Document d'enregistrement, d'inscription ou de constitution de la firme nommée en 1 ci-dessus, en conformité avec les clauses 3.1 et 3.2 des IC</div>
                                    <div>☐ En cas de groupement, lettre d'intention de constituer un groupement, ou accord de groupement, en conformité avec la clause 3.1 des IC.</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN – 3.1 Situation financière') === 0)
                @php
                    $content = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $content = $decoded;
                        }
                    }
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    {!! view('documents.formulaire_fin_3_1_pdf', ['content' => $content, 'dossier' => $dossier, 'formSignataire' => $formSignataire, 'formSignatureData' => $formSignatureData])->render() !!}
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.3') === 0)
                @php
                    $content = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $content = $decoded;
                        }
                    }
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    {!! view('documents.formulaire_fin_3_3_pdf', ['content' => $content, 'formSignataire' => $formSignataire, 'formSignatureData' => $formSignatureData])->render() !!}
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière') === 0)
                @php
                    $attachments = $doc->fichiers ?? collect();
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    @include('documents.partials.formulaire_fin_3_4_a_pdf', compact('attachments'))
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière') === 0)
                @php
                    $attachments = $doc->fichiers ?? collect();
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    @include('documents.partials.formulaire_fin_3_4_b_pdf', compact('attachments'))
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges') === 0)
                @php
                    $content = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $content = $decoded;
                        }
                    }

                    $antRefNumero = trim(preg_replace('/^\s*A?DRP\s*N°\s*/i', '', $dossier->reference_dossier ?? ''));
                    $antDateLancement = \App\Support\ReferenceLine::formatDate($dossier);
                    $antRefLine = \App\Support\ReferenceLine::render(
                        'ADRP Numero',
                        $antRefNumero !== '' ? $antRefNumero : ($dossier->reference_dossier ?? 'N/A'),
                        $antDateLancement,
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                    $antDateSoumission = $dossier->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : now()->format('d/m/Y');
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    <div style="text-align:center; font-weight:700; font-size:14px; margin-bottom:10px;">FORMULAIRE ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d'antécédents de litiges</div>
                    <table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:12px;">
                        <tr>
                            <td style="border:none; padding:0;">&nbsp;</td>
                            <td style="border:none; padding:0; text-align:right; font-weight:700; white-space:nowrap;">{{ $companyName ?: optional($entreprise)->nom }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:0; vertical-align:top;">{!! $antRefLine !!}</td>
                            <td style="border:none; padding:0; text-align:right; vertical-align:top;">{{ $antDateSoumission }}<br>Page 1/1</td>
                        </tr>
                    </table>
                        <style>
                            .pdf-ant-table th,
                            .pdf-ant-table td {
                                word-break: break-word;
                                overflow-wrap: break-word;
                                white-space: normal;
                            }
                        </style>
                        <table class="pdf-ant-table" style="width:100%; border-collapse:collapse; font-size:11px; table-layout:fixed; margin-bottom:16px;">
                        <thead>
                            <tr style="background:#f3f4f6; font-weight:700;">
                                <th colspan="4" style="border:1px solid #000; padding:8px; text-align:left;">Marchés non exécutés selon les dispositions de la sous-section C, Critères d'évaluation et de qualification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" style="border:1px solid #000; padding:8px;">Il n’y a pas eu de marché non exécuté depuis le 1er janvier {{ $content['marche_non_execute_since_year'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="border:1px solid #000; padding:8px;">Marché(s) non exécuté(s) depuis le 1er janvier {{ $content['marches_non_execute_since_year'] ?? '' }} :</td>
                            </tr>
                            <tr style="background:#f3f4f6; font-weight:700;">
                                <td style="border:1px solid #000; padding:8px; width:12%;">Année</td>
                                <td style="border:1px solid #000; padding:8px; width:20%;">Fraction non exécutée du contrat</td>
                                <td style="border:1px solid #000; padding:8px; width:38%;">Identification du contrat</td>
                                <td style="border:1px solid #000; padding:8px; width:30%;">Montant total du contrat (montant en FCFA)</td>
                            </tr>
                            @php
                                $marchesRows = [];
                                if (!empty($content['marches_non_executes']) && is_array($content['marches_non_executes'])) {
                                    foreach ($content['marches_non_executes'] as $row) {
                                        $hasValue = false;
                                        foreach (['annee', 'fraction', 'identification', 'montant_fcfa'] as $field) {
                                            if (!empty(trim($row[$field] ?? ''))) {
                                                $hasValue = true;
                                                break;
                                            }
                                        }
                                        if ($hasValue) {
                                            $marchesRows[] = $row;
                                        }
                                    }
                                }
                            @endphp
                            @foreach($marchesRows as $row)
                                <tr>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['annee'] ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['fraction'] ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top; white-space:pre-line;">{{ $row['identification'] ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['montant_fcfa'] ?? '' }}</td>
                                </tr>
                            @endforeach
                            <tr style="background:#f3f4f6; font-weight:700;">
                                <td colspan="4" style="border:1px solid #000; padding:8px; text-align:left;">Litiges en instance, en vertu de la sous-section C, Critères d'évaluation et de qualification</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="border:1px solid #000; padding:8px;">Pas de litige en instance<br>Litige(s) en : {{ $content['litiges_since_year'] ?? '' }}</td>
                            </tr>
                            <tr style="background:#f3f4f6; font-weight:700;">
                                <td style="border:1px solid #000; padding:8px; width:15%;">Année du litige</td>
                                <td style="border:1px solid #000; padding:8px; width:25%;">Montant de la réclamation (monnaie)</td>
                                <td style="border:1px solid #000; padding:8px; width:35%;">Identification du marché</td>
                                <td style="border:1px solid #000; padding:8px; width:25%;">Montant total du marché (monnaie, équivalent en FCFA)</td>
                            </tr>
                            @php
                                $litigesRows = [];
                                if (!empty($content['litiges_en_instance_rows']) && is_array($content['litiges_en_instance_rows'])) {
                                    foreach ($content['litiges_en_instance_rows'] as $row) {
                                        $hasValue = false;
                                        foreach (['annee', 'montant_reclamation', 'identification_marche', 'montant_total'] as $field) {
                                            if (!empty(trim($row[$field] ?? ''))) {
                                                $hasValue = true;
                                                break;
                                            }
                                        }
                                        if ($hasValue) {
                                            $litigesRows[] = $row;
                                        }
                                    }
                                }
                            @endphp
                            @foreach($litigesRows as $row)
                                <tr>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['annee'] ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['montant_reclamation'] ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top; white-space:pre-line;">{{ $row['identification_marche'] ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $row['montant_total'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="pdf-ant-table" style="width:100%; border-collapse:collapse; font-size:11px; table-layout:fixed;">
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700; width:28%;">Antécédents de litiges</td>
                            <td style="border:1px solid #000; padding:8px;">{!! nl2br(e($content['antecedents_litiges'] ?? '')) !!}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:8px; font-weight:700;">Commentaires complémentaires</td>
                            <td style="border:1px solid #000; padding:8px;">{!! nl2br(e($content['autres_details'] ?? '')) !!}</td>
                        </tr>
                    </table>
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), "Chiffre d'affaires annuel moyen des activités de services") === 0)
`                @php
                    $chiffres = $dossier->chiffresAffaires->sortBy('annee');
                    if ($chiffres->isEmpty()) {
                        $chiffres = \App\Models\ChiffreAffaire::whereNull('dossier_id')->orderBy('annee')->get();
                    }
                    $total = $chiffres->sum('montant');
                    $count = $chiffres->count();
                    $average = $count ? ($total / $count) : 0;
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    <div style="font-weight:700; font-size:14px; text-align:center; margin-bottom:10px;">CHIFFRE D'AFFAIRES ANNUEL MOYEN DES ACTIVITÉS DE SERVICES</div>
                    @if($chiffres->isNotEmpty())
                        <table style="width:100%; border-collapse:collapse; font-size:11px; margin-top:10px;">
                            <thead>
                                <tr style="background:#f3f4f6; font-weight:700;">
                                    <th style="border:1px solid #000; padding:8px; text-align:left; width:25%;">Année</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:left; width:45%;">Montant et monnaie</th>
                                    <th style="border:1px solid #000; padding:8px; text-align:right; width:30%;">Equivalent FCFA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chiffres as $entry)
                                    <tr>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ $entry->annee }}</td>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;">{{ number_format($entry->montant, 0, ',', ' ') }} {{ $entry->monnaie }}</td>
                                        <td style="border:1px solid #000; padding:8px; text-align:right; vertical-align:top;">{{ number_format($entry->montant, 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="border:1px solid #000; padding:8px; font-weight:700;">Chiffre d'affaires moyen des activités de services</td>
                                    <td style="border:1px solid #000; padding:8px; font-weight:700;">{{ number_format($average, 0, ',', ' ') }}</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right; font-weight:700;">{{ number_format($average, 0, ',', ' ') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div style="margin-top:10px; font-style:italic; color:#444;">Aucun chiffre d'affaires enregistré pour ce dossier.</div>
                    @endif
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie') === 0)
                @php
                    $engagementSociete = optional($entreprise)->nom ?: '[Insérer le nom du soumissionnaire]';
                    $sousTraitance = '[Insérer, en cas de sous-traitance : « ainsi qu’au nom de nos sous-traitants »]';
                @endphp
                <style>
                    .pdf-engagement ul { margin: 3px 0 4px 18px; padding: 0; }
                    .pdf-engagement ul ul { margin: 1px 0 1px 20px; list-style-type: circle; }
                    .pdf-engagement li { margin-bottom: 2px; text-align: justify; }
                    .pdf-engagement p { margin: 3px 0; }
                </style>
                <div class="pdf-engagement" style="margin-top:4px; font-size:12px; line-height:1.28;">
                    <div style="text-align:center; font-weight:700; font-size:15px; margin-bottom:3px;">ENGAGEMENT DU SOUMISSIONNAIRE A RESPECTER LE CODE D'ETHIQUE ET DE DEONTOLOGIE DANS LA COMMANDE PUBLIQUE</div>
                    <div style="text-align:center; margin-bottom:4px;">**********</div>

                    <p>Nous soussigné <strong>{{ $engagementSociete }}</strong>, ci-après dénommé « le Soumissionnaire » :</p>

                    <ul>
                        <li><strong>attestons</strong> avoir pris connaissance des dispositions relatives à la lutte contre la corruption, les conflits d'intérêt, la répression de l'enrichissement illicite, l'éthique professionnelle et tous autres actes similaires prévus au code d'éthique et de déontologie dans la commande publique en République du Bénin et prenons solennellement l'Engagement de les respecter sous peine de subir les sanctions prévues à cet effet.</li>
                        <li><strong>déclarons</strong> sur l'honneur n'avoir pratiqué dans le cadre du présent marché, aucune collusion avec d'autres soumissionnaires en vue de présenter des offres dont les montants seraient anormalement élevés.</li>
                        <li><strong>nous engageons</strong>, en notre nom propre, au nom de notre société et de nos préposés, {{ $sousTraitance }}, à nous abstenir de toute pratique liée à la corruption active et ou passive dans le cadre de marché.</li>
                        <li><strong>nous engageons</strong> personnellement et engageons notre société ainsi que nos préposés, {{ $sousTraitance }}, à communiquer par écrit à l'Autorité contractante, à la Direction nationale de contrôle des marchés publics (DNCMP) et à l'Autorité de régulation des marchés publics (ARMP) et ce, en toute bonne foi :
                            <ul>
                                <li>tout incident mettant en cause, de quelque manière que ce soit, l'exécution du présent marché ;</li>
                                <li>l'existence d'un éventuel conflit d'intérêt.</li>
                            </ul>
                        </li>
                        <li><strong>nous engageons</strong> personnellement et engageons notre société ainsi que nos préposés, {{ $sousTraitance }}, à nous abstenir de proposer ou de donner, directement ou indirectement, des avantages en nature et en espèces, antérieurement ou postérieurement à la soumission de notre candidature.</li>
                    </ul>

                    <ul>
                        <li><strong>reconnaissons</strong> qu'en cas de manquement aux engagements ci-dessus, nous nous exposons aux sanctions prévues à l'article 123 de la loi n°2020-26 du 29 septembre 2020 portant Code des marchés publics de la République du Bénin, ainsi que par tous les autres textes réglementaires en République du Bénin, ainsi qu'aux sanctions de disqualification ou d'exclusion de toute activité en matière de marchés publics que pourrait prononcer l'Autorité de régulation des marchés publics (ARMP).</li>
                    </ul>

                    <p>Le présent engagement fait partie intégrante du marché.</p>
                </div>
            @elseif($isLibre)
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
                    $libreRefLine = \App\Support\ReferenceLine::render(
                        'Référence :',
                        $drpNumero !== '' ? $drpNumero : $drp,
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
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Formulaire de divulgation des bénéficiaires effectifs') === 0)
                @php
                    $divulgationVals = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $divulgationVals = $decoded;
                        }
                    }
                @endphp
                <div style="margin-top:10px;">
                    {!! view('documents.partials.formulaire_divulgation_beneficiaires_pdf_content', $divulgationVals)->render() !!}
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Liste du personnel affecté à l\'exécution du marché') === 0)
                @php
                    $personnel = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $personnel = $decoded;
                        }
                    }
                    $objetMarche = trim($dossier->objectif ?? '') ?: trim($dossier->lot ?? '') ?: 'N/A';
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.4;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; font-size:12px;">
                        <div style="font-weight:700;">MAJESTY SERVICES &amp; ÉQUIPEMENTS SARL</div>
                        <div style="text-align:right;">Date&nbsp;: {{ now()->format('d/m/Y') }}</div>
                    </div>
                    <div style="font-weight:700; text-align:center; font-size:14px; margin-bottom:18px;">Liste du personnel affecté à l'exécution du marché</div>
                    <div style="border:1px solid #000; background:#f8f8f8; padding:12px; margin-bottom:14px;">
                        <div><strong>Dossier :</strong> {{ $dossier->nom_dossier ?? 'N/A' }}</div>
                        <div><strong>Objet du marché :</strong> {{ $objetMarche }}</div>
                    </div>
                    @if(count($personnel) > 0)
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <tbody>
                                @foreach($personnel as $index => $item)
                                    <tr>
                                        <td style="border:1px solid #000; padding:8px; width:8%; font-weight:700; vertical-align:top;">{{ $index + 1 }}.</td>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><strong>Désignation du poste :</strong> {{ $item['poste'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"></td>
                                        <td style="border:1px solid #000; padding:8px; vertical-align:top;"><strong>Nom :</strong> {{ $item['nom'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="font-style:italic; color:#444;">Aucun personnel enregistré pour ce document.</div>
                    @endif
                </div>
            @elseif($doc->valeurs && $doc->valeurs->count() > 0)
                <table style="width:100%; border-collapse:collapse; margin-top:8px;">
                    <tbody>
                        @foreach($doc->valeurs as $val)
                            <tr>
                                <td style="width:35%; padding:6px; vertical-align:top; font-weight:700;">{{ optional($val->champDocument)->label ?? optional($val->champDocument)->nom_champ ?? 'Champ' }}</td>
                                <td style="padding:6px; vertical-align:top;">{!! nl2br(e($val->valeur)) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Tableau de résumé des bordereaux de prix') === 0)
                @php
                    $data = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $data = $decoded;
                        }
                    }

                    if (!array_key_exists('a', $data) || !array_key_exists('d', $data)) {
                        $bordereauFournituresDoc = $dossier->documents->first(function ($doc) {
                            return optional($doc->typeDocument)->nom === 'Bordereau des prix pour les fournitures à importer';
                        });
                        if ($bordereauFournituresDoc && $bordereauFournituresDoc->bordereau->count() > 0) {
                            $aTotal = $bordereauFournituresDoc->bordereau->sum(function ($bordereau) {
                                return $bordereau->lignes->sum('montant');
                            });
                            if ($aTotal > 0) {
                                $data['a'] = $aTotal;
                            }
                        }

                        $prixCalendrierDoc = $dossier->documents->first(function ($doc) {
                            return optional($doc->typeDocument)->nom === "Bordereau des prix et calendrier d'exécution des services connexes";
                        });
                        if ($prixCalendrierDoc && $prixCalendrierDoc->bordereau->count() > 0) {
                            $dTotal = $prixCalendrierDoc->bordereau->sum(function ($bordereau) {
                                return $bordereau->lignes->sum('montant');
                            });
                            if ($dTotal > 0) {
                                $data['d'] = $dTotal;
                            }
                        }
                    }
                    $a = isset($data['a']) && is_numeric($data['a']) ? (float) $data['a'] : 0;
                    $d = isset($data['d']) && is_numeric($data['d']) ? (float) $data['d'] : 0;
                    $b = $a * 0.18;
                    $c = $a + $b;
                    $e = $d * 0.18;
                    $f = $d + $e;
                    $g = $a + $d;
                    $h = $g * 0.18;
                    $i = $g + $h;
                    $amountText = trim(($numberToWords)($i));
                @endphp
                <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                    <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">TABLEAU DE RÉSUMÉ DES BORDEREAUX DE PRIX</div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead>
                                <tr style="background:#f3f4f6; font-weight:700;">
                                    <th style="border:1px solid #000; padding:8px; width:18%;">Réf.</th>
                                    <th style="border:1px solid #000; padding:8px; width:52%;">Libellé</th>
                                    <th style="border:1px solid #000; padding:8px; width:30%; text-align:right;">Montant (F CFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;" rowspan="3">Fournitures</td>
                                    <td style="border:1px solid #000; padding:8px;">A — Prix Total F CFA HTVA</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right;">{{ isset($data['a']) && is_numeric($data['a']) ? number_format($a, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:8px;">B = A * 18% — TVA</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right;">{{ isset($data['b']) && is_numeric($data['b']) ? number_format($b, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:8px;">C = A + B — Prix total F CFA TTC</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right;">{{ isset($data['c']) && is_numeric($data['c']) ? number_format($c, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:8px; vertical-align:top;" rowspan="3">Services connexes</td>
                                    <td style="border:1px solid #000; padding:8px;">D — Prix total F CFA HTVA</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right;">{{ isset($data['d']) && is_numeric($data['d']) ? number_format($d, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:8px;">E = D * 18% — TVA</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right;">{{ isset($data['e']) && is_numeric($data['e']) ? number_format($e, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:8px;">F = D + E — Prix total F CFA TTC</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right;">{{ isset($data['f']) && is_numeric($data['f']) ? number_format($f, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td colspan="2" style="border:1px solid #000; padding:8px; font-weight:700;">G = A + D — Montant Total F CFA HTVA</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right; font-weight:700;">{{ isset($data['g']) && is_numeric($data['g']) ? number_format($g, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td colspan="2" style="border:1px solid #000; padding:8px; font-weight:700;">H = G * 18% — TVA total</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right; font-weight:700;">{{ isset($data['h']) && is_numeric($data['h']) ? number_format($h, 0, ',', ' ') : '' }}</td>
                                </tr>
                                <tr style="background:#e8e8e8;">
                                    <td colspan="2" style="border:1px solid #000; padding:8px; font-weight:700;">I = G + H — Montant Total F CFA TTC</td>
                                    <td style="border:1px solid #000; padding:8px; text-align:right; font-weight:700;">{{ isset($data['i']) && is_numeric($data['i']) ? number_format($i, 0, ',', ' ') : '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:10px; font-size:12px;">
                        <p style="margin:0 0 6px 0;"><strong>Arrêté le présent résumé des bordereaux de prix à la somme toutes taxes comprises de</strong></p>
                        <p style="margin:0; font-weight:700;">{{ $amountText ? ucfirst($amountText) : '...' }} Francs CFA.</p>
                    </div>
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Bordereau des prix pour les fournitures à importer') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $bordAaoLine = \App\Support\ReferenceLine::render(
                        'AAO numéro :',
                        $dossier->reference_dossier ?? 'N/A',
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">BORDEREAU DES PRIX POUR LES FOURNITURES À IMPORTER</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>{!! $bordAaoLine !!}</strong> <br>
                            <strong>Variante N° :</strong>
                        </div>

                        @foreach($bordereaux as $bordereau)
                            <div style="overflow-x:auto; margin-bottom:20px;">
                                <table style="width:100%; border-collapse:collapse; font-size:9px;">
                                    <thead>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:4%;">1</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:24%;">2</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:10%;">3</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:8%;">4</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:13%;">5</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:13%;">6</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:16%;">7</th>
                                        </tr>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:5px; text-align:center;">Article</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:left;">Description</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center;">Date de livraison</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center;">Quantité (Nb. d'unités)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right;">Prix unitaire HTVA (RETROCESSION L'INCOTERM APPLICABLE)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right;">Prix total HTVA (selon l'incoterm applicable) par article (cols 4 x 5)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right;">Coût main-d'œuvre locale, matière premières et composants provenant du Bénin ou de l'UEMOA de col.5</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalHTVA = 0; @endphp
                                        @foreach($bordereau->lignes as $index => $ligne)
                                            @php
                                                $prix = (float) ($ligne->prix_unitaire ?? 0);
                                                $quantite = (float) ($ligne->quantite ?? 1);
                                                $montant = $prix * $quantite;
                                                $coutBenin = (float) ($ligne->cout_benin ?? 0);
                                                $totalHTVA += $montant;
                                            @endphp
                                            <tr>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td style="border:1px solid #000; padding:5px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->date_prestation ?? '' }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ number_format($quantite, 2, '.', ' ') }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ $prix ? number_format($prix, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ $montant ? number_format($montant, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ number_format($coutBenin, 0, ',', ' ') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr style="font-weight:700; background:#f9f9f9;">
                                            <td colspan="5" style="border:1px solid #000; padding:5px; text-align:right;">Prix Total HTVA</td>
                                            <td style="border:1px solid #000; padding:5px; text-align:right;">{{ number_format($totalHTVA, 0, ',', ' ') }}</td>
                                            <td style="border:1px solid #000; padding:5px;">&nbsp;</td>
                                        </tr>
                                        @php $tva = round($totalHTVA * 0.18); @endphp
                                        <tr style="font-weight:700; background:#f9f9f9;">
                                            <td colspan="5" style="border:1px solid #000; padding:5px; text-align:right;">TVA</td>
                                            <td style="border:1px solid #000; padding:5px; text-align:right;">{{ number_format($tva, 0, ',', ' ') }}</td>
                                            <td style="border:1px solid #000; padding:5px;">&nbsp;</td>
                                        </tr>
                                        @php $totalTTC = $totalHTVA + $tva; @endphp
                                        <tr style="font-weight:700; background:#e8e8e8;">
                                            <td colspan="5" style="border:1px solid #000; padding:5px; text-align:right;">Prix total TTC</td>
                                            <td style="border:1px solid #000; padding:5px; text-align:right;">{{ number_format($totalTTC, 0, ',', ' ') }}</td>
                                            <td style="border:1px solid #000; padding:5px;">&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Bordereau des prix des fournitures, déjà importées') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();

                    $djiContent = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $djiContent = $decoded;
                        }
                    }
                    $djiDate = $dossier->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : '';
                    $djiVariante = trim($djiContent['variante'] ?? '');
                    $djiRefNumero = trim(preg_replace('/^\s*A?DRP\s*N°\s*/i', '', $dossier->reference_dossier ?? '')) ?: ($dossier->reference_dossier ?? 'N/A');
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:14px; text-align:center; margin-bottom:14px;">Bordereau des prix des fournitures, déjà importées</div>

                        <div style="margin-bottom:14px;">
                            <div style="text-align:right; font-size:10px;">
                                <div>Date : {{ $djiDate }}</div>
                                <div>DRP numéro : {{ $djiRefNumero }}</div>
                                <div>Variante No. : {{ $djiVariante }}</div>
                            </div>
                            <div style="font-size:10px; margin-top:6px;">Monnaie de l'offre en conformité avec la clause 15.1 des IC</div>
                        </div>

                        @foreach($bordereaux as $bordereau)
                            @php
                                $totalGeneral = 0;
                            @endphp
                            <div style="overflow-x:auto; margin-bottom:20px;">
                                <style>
                                    .pdf-dji-table th,
                                    .pdf-dji-table td {
                                        word-break: break-word;
                                        overflow-wrap: break-word;
                                        white-space: normal;
                                    }
                                </style>
                                <table class="pdf-dji-table" style="width:100%; border-collapse:collapse; font-size:8px; table-layout:fixed;">
                                    <thead>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:5%;">1</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:12%;">2</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:8%;">3</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:8%;">4</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:7%;">5</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:9%;">6</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:9%;">7</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:9%;">8</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:9%;">9</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:8%;">10</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:8%;">11</th>
                                            <th style="border:1px solid #000; padding:2px; text-align:center; width:8%;">12</th>
                                        </tr>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:4px;">Article No.</th>
                                            <th style="border:1px solid #000; padding:4px;">Description des Fournitures</th>
                                            <th style="border:1px solid #000; padding:4px;">Pays d'origine</th>
                                            <th style="border:1px solid #000; padding:4px;">Date de livraison selon définition des Incoterms</th>
                                            <th style="border:1px solid #000; padding:4px;">Quantité (Nb. d'unités)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix unitaire incluant droits de douanes et taxes d'importations en conformité avec IC 14.6(a) (i)</th>
                                            <th style="border:1px solid #000; padding:4px;">Droits de douanes et taxes d'importations par unité en conformité avec IC 14.6(a) (ii)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix unitaire net de droits de douanes et taxes d'importations en conformité avec IC 14.6(a) (iii) (col.6 moins col.7)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix par article net de droits de douanes et taxes d'importations en conformité avec IC 14.6(a) (i) (col.5x8)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix par article du transport terrestre et autres services requis dans le pays de l'Acheteur pour acheminer les fournitures jusqu'à destination finale (en conformité avec IC 14.6(a) (v)</th>
                                            <th style="border:1px solid #000; padding:4px;">Taxes de vente et autres taxes payées ou à payer si le marché est attribué (en conformité avec IC 14.6(a) (iv)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix total par article (col 9+10)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bordereau->lignes as $index => $ligne)
                                            @php
                                                $prix = (float) ($ligne->prix_unitaire ?? 0);
                                                $droits = (float) ($ligne->droits_douane ?? 0);
                                                $quantite = (float) ($ligne->quantite ?? 1);
                                                $prixNet = $prix - $droits;
                                                $montantNet = $prixNet * $quantite;
                                                $transport = (float) ($ligne->transport ?? 0);
                                                $prixTotalArticle = $montantNet + $transport;
                                                $totalGeneral += $prixTotalArticle;
                                            @endphp
                                            <tr>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td style="border:1px solid #000; padding:4px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ $ligne->site ?? '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ $ligne->date_prestation ?? '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ number_format($quantite, 2, '.', ' ') }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $prix ? number_format($prix, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $droits ? number_format($droits, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $prixNet ? number_format($prixNet, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $montantNet ? number_format($montantNet, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $transport ? number_format($transport, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $ligne->taxe_vente ? number_format($ligne->taxe_vente, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $prixTotalArticle ? number_format($prixTotalArticle, 0, ',', ' ') : '' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr style="font-weight:700; background:#f9f9f9;">
                                            <td colspan="9" style="border:1px solid #000; padding:4px;"></td>
                                            <td colspan="2" style="border:1px solid #000; padding:4px; text-align:right;">Prix total</td>
                                            <td style="border:1px solid #000; padding:4px; text-align:right;">{{ number_format($totalGeneral, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Bordereau des prix pour les fournitures fabriquées au Bénin') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();

                    $beninContent = [];
                    if (!empty($doc->content)) {
                        $decoded = json_decode($doc->content, true);
                        if (is_array($decoded)) {
                            $beninContent = $decoded;
                        }
                    }
                    $beninDateRemise = $dossier->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : '';
                    $beninVariante = trim($beninContent['variante'] ?? '');
                    $beninRefNumero = trim(preg_replace('/^\s*A?DRP\s*N°\s*/i', '', $dossier->reference_dossier ?? '')) ?: ($dossier->reference_dossier ?? 'N/A');
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:14px; text-align:center; margin-bottom:14px;">Bordereau des prix pour les fournitures fabriquées au Bénin</div>

                        <div style="margin-bottom:14px;">
                            <div style="text-align:right; font-size:10px;">
                                <div>Date : {{ $beninDateRemise }}</div>
                                <div>Avis de la demande de renseignements et de prix No. : {{ $beninRefNumero }}</div>
                                <div>Variante No. : {{ $beninVariante }}</div>
                            </div>
                            <div style="font-size:10px; margin-top:6px;">Monnaie de l'offre en conformité avec la clause 15.1 des IC</div>
                        </div>

                        @foreach($bordereaux as $bordereau)
                            @php
                                $totalGeneral = 0;
                            @endphp
                            <div style="overflow-x:auto; margin-bottom:20px;">
                                <table style="width:100%; border-collapse:collapse; font-size:8px; table-layout:fixed;">
                                    <thead>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:5%;">1</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:18%;">2</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:9%;">3</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:7%;">4</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:9%;">5</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:9%;">6</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:11%;">7</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:10%;">8</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:11%;">9</th>
                                            <th style="border:1px solid #000; padding:4px; text-align:center; width:8%;">10</th>
                                        </tr>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:4px;">Article</th>
                                            <th style="border:1px solid #000; padding:4px;">Description</th>
                                            <th style="border:1px solid #000; padding:4px;">Date de livraison selon définition des Incoterms</th>
                                            <th style="border:1px solid #000; padding:4px;">Quantité (Nb. d'unités)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix unitaire EXW</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix total EXW par article (cols.4 x 5)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix unitaire du transport terrestre et autres services requis dans le pays de l'Acheteur pour acheminer les fournitures jusqu'à destination finale comme indiquée aux DPDRP</th>
                                            <th style="border:1px solid #000; padding:4px;">Coût Main-d'œuvre locale, matières premières et composants provenant du Pays de l'Acheteur % de Col.5</th>
                                            <th style="border:1px solid #000; padding:4px;">Taxe de vente et autres taxes si le marché est attribué (selon IS 14.8(a)(ii)</th>
                                            <th style="border:1px solid #000; padding:4px;">Prix total par article (col 6+7)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bordereau->lignes as $index => $ligne)
                                            @php
                                                $prix = (float) ($ligne->prix_unitaire ?? 0);
                                                $quantite = (float) ($ligne->quantite ?? 1);
                                                $montantExw = $prix * $quantite;
                                                $transport = (float) ($ligne->transport ?? 0);
                                                $prixTotalArticle = $montantExw + $transport;
                                                $totalGeneral += $prixTotalArticle;
                                            @endphp
                                            <tr>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td style="border:1px solid #000; padding:4px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ $ligne->date_prestation ?? '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ number_format($quantite, 2, '.', ' ') }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $prix ? number_format($prix, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $montantExw ? number_format($montantExw, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $transport ? number_format($transport, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ $ligne->cout_main_oeuvre_locale ?? '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $ligne->taxe_vente ? number_format($ligne->taxe_vente, 0, ',', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:4px; text-align:right; vertical-align:top;">{{ $prixTotalArticle ? number_format($prixTotalArticle, 0, ',', ' ') : '' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr style="font-weight:700; background:#f9f9f9;">
                                            <td colspan="7" style="border:1px solid #000; padding:4px;"></td>
                                            <td colspan="2" style="border:1px solid #000; padding:4px; text-align:right;">Prix total</td>
                                            <td style="border:1px solid #000; padding:4px; text-align:right;">{{ number_format($totalGeneral, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Listes des Fournitures et Calendrier de livraison') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $bordAaoLine = \App\Support\ReferenceLine::render(
                        'AAO numéro :',
                        $dossier->reference_dossier ?? 'N/A',
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">LISTE DES FOURNITURES ET CALENDRIER DE LIVRAISON</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>{!! $bordAaoLine !!}</strong>
                        </div>

                        <div style="overflow-x:auto; margin-bottom:20px;">
                            <table style="width:100%; border-collapse:collapse; font-size:9px;">
                                <thead>
                                    <tr style="background:#f3f4f6; font-weight:700;">
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:4%;" rowspan="2">Article numéro</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:left; width:24%;" rowspan="2">Description des Fournitures</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:10%;" rowspan="2">Quantité (Nb. d'unité)</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:8%;" rowspan="2">Unité</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:21%;" rowspan="2">(Site Projet) ou Destination finale comme indiqués aux DPAO</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:33%;" colspan="3">Date de livraison</th>
                                    </tr>
                                    <tr style="background:#f3f4f6; font-weight:700;">
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:11%;">Date de livraison au plus tôt</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:11%;">Date de livraison au plus tard</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:11%;">Date de livraison offerte par le candidat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $lineNumber = 1; @endphp
                                    @foreach($bordereaux as $bordereau)
                                            @if(false)
                                                <tr>
                                                    <td colspan="8" style="border:1px solid #000; padding:8px; text-align:center; font-weight:700; background:#f3f4f6; text-transform:uppercase;">{{ $bordereau->titre }}</td>
                                                </tr>
                                            @endif
                                        @foreach($bordereau->lignes as $ligne)
                                            <tr>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ str_pad($lineNumber++, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td style="border:1px solid #000; padding:5px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->quantite ? number_format($ligne->quantite, 0, '.', ' ') : '' }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->unite_physique ?? '' }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{!! nl2br(e($ligne->site ?? '')) !!}</td>
                                                @php
                                                    $dateParts = array_map('trim', preg_split('/\s*\/\s*/', trim($ligne->date_prestation ?? '')));
                                                    $datePlusTot = $dateParts[0] ?? '';
                                                    $datePlusTard = $dateParts[1] ?? '';
                                                    $dateOfferte = $dateParts[2] ?? '';
                                                @endphp
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ e($datePlusTot) }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ e($datePlusTard) }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ e($dateOfferte) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Cadres de sous détails des prix unitaire') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $bordAaoLine = \App\Support\ReferenceLine::render(
                        'AAO numéro :',
                        $dossier->reference_dossier ?? 'N/A',
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">CADRE DE SOUS DETAILS DES PRIX UNITAIRES</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>{!! $bordAaoLine !!}</strong>
                        </div>

                        <div style="overflow-x:auto; margin-bottom:20px;">
                            <table style="width:100%; border-collapse:collapse; font-size:9px;">
                                <thead>
                                    <tr style="background:#f3f4f6; font-weight:700;">
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:4%;">N°</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:left; width:28%;">DESCRIPTION</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:8%;">Unit</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:30%;" colspan="5">Frais généraux de site</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:6%;">C1 = frais généraux de site</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:6%;">Coef. vente k = 1 + C1</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center; width:10%;">Prix de vente HTVA en chiffre</th>
                                    </tr>
                                    <tr style="background:#f3f4f6; font-weight:700;">
                                        <th style="border:1px solid #000; padding:5px; text-align:center;"></th>
                                        <th style="border:1px solid #000; padding:5px; text-align:left;"></th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;"></th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">Total Materiel</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">Location Amort. Materiel</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">Matière Et Frais divers</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">Main d'oeuvre</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">Déboursé Sec</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">C1 = (Déboursé Sec - Total Matériel) / Total Matériel</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">k = 1 + C1</th>
                                        <th style="border:1px solid #000; padding:5px; text-align:center;">Prix de vente HTVA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bordereaux as $bordereau)
                                        @foreach($bordereau->lignes as $index => $ligne)
                                            <tr>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ str_pad($index+1,2,'0',STR_PAD_LEFT) }}</td>
                                                <td style="border:1px solid #000; padding:5px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->unite_physique ?? '' }}</td>
                                                @php
                                                    $fmt = function($v) {
                                                        $v = trim(str_replace(["\xc2\xa0", ','], [' ', '.'], (string) $v));
                                                        return (is_numeric($v) && floatval($v) != 0) ? number_format(floatval($v), 0, ',', ' ') : $v;
                                                    };
                                                @endphp
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ is_numeric($ligne->total_materiel) ? number_format($ligne->total_materiel, 2, ',', ' ') : $ligne->total_materiel }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ is_numeric($ligne->location_amort) ? number_format($ligne->location_amort, 2, ',', ' ') : $ligne->location_amort }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ is_numeric($ligne->matiere_frais) ? number_format($ligne->matiere_frais, 2, ',', ' ') : $ligne->matiere_frais }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ is_numeric($ligne->main_oeuvre) ? number_format($ligne->main_oeuvre, 2, ',', ' ') : $ligne->main_oeuvre }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ is_numeric($ligne->deborse_sec) ? number_format($ligne->deborse_sec, 2, ',', ' ') : $ligne->deborse_sec }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ is_numeric($ligne->coef_c1) ? number_format($ligne->coef_c1, 4, ',', ' ') : $ligne->coef_c1 }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ is_numeric($ligne->coef_k) ? number_format($ligne->coef_k, 4, ',', ' ') : (is_numeric($ligne->coef_c1) ? number_format(1 + floatval($ligne->coef_c1), 4, ',', ' ') : '') }}</td>
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ is_numeric($ligne->prix_vente_htva) ? number_format($ligne->prix_vente_htva, 2, ',', ' ') : $ligne->prix_vente_htva }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-bottom:20px; font-size:10px;">
                            <strong>Remarque :</strong> Cadre de sous détails des prix unitaires.
                        </div>
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Bordereau des prix et calendrier d\'exécution des services connexes') === 0 || strcasecmp(trim($doc->typeDocument->nom), 'Listes des services connexes et calendrier de réalisation') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $isListesServicesConnexes = strcasecmp(trim($doc->typeDocument->nom), 'Listes des services connexes et calendrier de réalisation') === 0;
                    $documentTitle = $isListesServicesConnexes
                        ? 'LISTES DES SERVICES CONNEXES ET CALENDRIER DE RÉALISATION'
                        : 'BORDEREAU DES PRIX ET CALENDRIER D\'EXÉCUTION DES SERVICES CONNEXES';
                    $bordAaoLine = \App\Support\ReferenceLine::render(
                        'AAO numéro :',
                        $dossier->reference_dossier ?? 'N/A',
                        \App\Support\ReferenceLine::formatDate($dossier),
                        $dossier->nom_dossier ?? '',
                        $doc->reference_model ?? \App\Models\TypeDocument::defaultReferenceModelFor($doc->typeDocument->nom)
                    );
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">{{ $documentTitle }}</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>{!! $bordAaoLine !!}</strong>
                            @if(!$isListesServicesConnexes)
                                <br><strong>Variante N° :</strong>
                            @endif
                        </div>

                        @if($isListesServicesConnexes)
                            <div style="overflow-x:auto; margin-bottom:20px;">
                                <table style="width:100%; border-collapse:collapse; font-size:9px;">
                                    <thead>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:4%;">Article</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:left; width:28%;">Description du Service</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:10%;">Quantité (Nb. d'unités)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:12%;">Unité physique</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:17%;">Site ou lieu où les Services doivent être exécutés</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:14.5%;">Plus tôt</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:14.5%;">Plus tard</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $lineNumber = 1; @endphp
                                        @foreach($bordereaux as $bordereau)
                                            @if($bordereau->titre)
                                                <tr>
                                                    <td colspan="7" style="border:1px solid #000; padding:8px; text-align:center; font-weight:700; background:#f3f4f6; text-transform:uppercase;">{{ $bordereau->titre }}</td>
                                                </tr>
                                            @endif
                                            @foreach($bordereau->lignes as $ligne)
                                                <tr>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ str_pad($lineNumber++, 2, '0', STR_PAD_LEFT) }}</td>
                                                    <td style="border:1px solid #000; padding:5px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->quantite ? number_format($ligne->quantite, 0, '.', ' ') : '' }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->unite_physique ?? '' }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{!! nl2br(e($ligne->site ?? '')) !!}</td>
                                                    @php
                                                        $dateParts = array_map('trim', preg_split('/\s*\/\s*/', trim($ligne->date_prestation ?? '')));
                                                        $datePlusTot = $dateParts[0] ?? '';
                                                        $datePlusTard = $dateParts[1] ?? '';
                                                    @endphp
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ e($datePlusTot) }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ e($datePlusTard) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-bottom:20px; font-size:10px;">
                                <strong>Remarque :</strong> Les dates de réalisation sont indiquées sous la forme "Plus tôt / Plus tard".
                            </div>
                        @else
                            @php $totalHTVA = 0; $lineNumber = 1; @endphp
                            <div style="overflow-x:auto; margin-bottom:20px;">
                                <table style="width:100%; border-collapse:collapse; font-size:9px;">
                                    <thead>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:4%;">Article</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:left; width:30%;">Description des Services</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:16%;">Date de réalisation au lieu de destination finale</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:16%;">Fréquence</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:10%;">Quantité (Nb. d'unités)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right; width:12%;">Prix unitaire</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right; width:16%;">Prix total par article (col 4x5)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bordereaux as $bordereau)
                                            @if($bordereau->titre)
                                                <tr>
                                                    <td colspan="7" style="border:1px solid #000; padding:8px; text-align:center; font-weight:700; background:#f3f4f6; text-transform:uppercase;">{{ $bordereau->titre }}</td>
                                                </tr>
                                            @endif
                                            @foreach($bordereau->lignes as $ligne)
                                                @php
                                                    $prix = (float) ($ligne->prix_unitaire ?? 0);
                                                    $quantite = (float) ($ligne->quantite ?? 1);
                                                    $montant = $prix * $quantite;
                                                    $totalHTVA += $montant;
                                                @endphp
                                                <tr>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ str_pad($lineNumber++, 2, '0', STR_PAD_LEFT) }}</td>
                                                    <td style="border:1px solid #000; padding:5px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->date_prestation ?? '' }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $ligne->frequence ?? '' }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ number_format($quantite, 0, '.', ' ') }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top; width:14%;">{{ $prix ? number_format($prix, 0, ',', ' ') : '' }}</td>
                                                    <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top; width:16%;">{{ $montant ? number_format($montant, 0, ',', ' ') : '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr style="font-weight:700; background:#f9f4f2;">
                                            <td colspan="6" style="border:1px solid #000; padding:5px; text-align:right;">Prix Total HTVA</td>
                                            <td style="border:1px solid #000; padding:5px; text-align:right;">{{ number_format($totalHTVA, 0, ',', ' ') }}</td>
                                        </tr>
                                        @php $tva = round($totalHTVA * 0.18); @endphp
                                        <tr style="font-weight:700; background:#f9f4f2;">
                                            <td colspan="6" style="border:1px solid #000; padding:5px; text-align:right;">TVA</td>
                                            <td style="border:1px solid #000; padding:5px; text-align:right;">{{ number_format($tva, 0, ',', ' ') }}</td>
                                        </tr>
                                        @php $totalTTC = $totalHTVA + $tva; @endphp
                                        <tr style="font-weight:700; background:#e8e8e8;">
                                            <td colspan="6" style="border:1px solid #000; padding:5px; text-align:right;">Prix total TTC</td>
                                            <td style="border:1px solid #000; padding:5px; text-align:right;">{{ number_format($totalTTC, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif($doc->typeDocument->type_formulaire === 'bordereau')
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $isProgramme = strcasecmp(trim($doc->typeDocument->nom), "Programme d'activités") === 0;
                    $isMethodes = strcasecmp(trim($doc->typeDocument->nom), "Méthodes d'exécution") === 0;
                    $isCalendrier = strcasecmp(trim($doc->typeDocument->nom), "Calendrier d'exécution") === 0;
                    $isDescriptionTechnique = strcasecmp(trim($doc->typeDocument->nom), "Description technique des fournitures/services") === 0;
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:12px; line-height:1.4;">
                        <div style="font-weight:700; font-size:14px; text-align:center; margin-bottom:6px;">{{ $isMethodes ? 'MÉTHODOLOGIE D\'EXÉCUTION' : ($isProgramme ? 'PROGRAMME D\'ACTIVITÉS' : ($isCalendrier ? 'CALENDRIER D\'EXÉCUTION' : ($isDescriptionTechnique ? 'DESCRIPTION TECHNIQUE DES FOURNITURES/SERVICES' : 'BORDEREAU DES PRIX UNITAIRES')) ) }}</div>

                        @foreach($bordereaux as $bordereau)
                            {{-- Le Bordereau prix unitaire (entretien/maintenance) n'a pas de sous-titre de lot :
                                 le titre "BORDEREAU DES PRIX UNITAIRES" ci-dessus suffit. --}}
                            @if($isMethodes || $isProgramme || $isCalendrier)
                                <div style="font-weight:700; font-size:12px; text-align:center; margin-bottom:10px;">{{ strtoupper($bordereau->titre ?: ($isMethodes ? 'MÉTHODOLOGIE D\'EXÉCUTION' : ($isProgramme ? 'PROGRAMME D\'ACTIVITÉS' : 'CALENDRIER D\'EXÉCUTION'))) }}</div>
                            @endif
                            {{--
                                "overflow-x:auto" ne veut rien dire dans un PDF statique (pas de
                                scroll), mais dompdf le traite comme un "overflow:hidden" : tout
                                contenu qui dépasserait la page en cours est alors tronqué au lieu
                                de se poursuivre sur la page suivante. Pour Description technique,
                                dont les cellules peuvent être longues, on utilise donc "visible".
                            --}}
                            <div style="overflow-x:{{ $isDescriptionTechnique ? 'visible' : 'auto' }}; margin-bottom:18px;">
                                @if($isProgramme)
                                    <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                        <thead>
                                            <tr style="background:#f3f4f6; font-weight:700;">
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:3%;">No</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:left; width:42%;">DESIGNATION</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:8%;">Unité physique</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:6%;">Quantité</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:12%;">Prix Unit HTVA</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:12%;">Total</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:11%;">Site ou lieu de services</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:10%;">Date finale de prestation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bordereau->lignes as $index => $ligne)
                                                <tr>
                                                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                                                    <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $ligne->unite_physique ?? '' }}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $ligne->quantite ?? 1 }}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:right; vertical-align:top;">{{ (int)$ligne->prix_unitaire ? number_format($ligne->prix_unitaire, 0, ',', ' ') : '' }}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:right; vertical-align:top;">{{ (int)$ligne->montant ? number_format($ligne->montant, 0, ',', ' ') : '' }}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{!! nl2br(e($ligne->site)) !!}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{!! nl2br(e($ligne->date_prestation)) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @elseif($isMethodes || $isCalendrier || $isDescriptionTechnique)
                                    @if($isDescriptionTechnique)
                                        {{--
                                            Tableau HTML classique, à la demande explicite de l'utilisateur.
                                            Limite connue et acceptée : dompdf peut perdre silencieusement le
                                            texte d'une cellule qui dépasse environ une page (aucun réglage
                                            CSS ne corrige ce comportement — voir historique des échanges).
                                        --}}
                                        <style>
                                            .pdf-description-technique-table th,
                                            .pdf-description-technique-table td {
                                                word-break: break-word;
                                                overflow-wrap: break-word;
                                                white-space: normal;
                                            }
                                        </style>
                                        <table class="pdf-description-technique-table" style="width:100%; border-collapse:collapse; font-size:10px;">
                                            <thead>
                                                <tr style="background:#f3f4f6; font-weight:700;">
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:5%;">N°</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:18%;">Désignation</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:25%;">Spécifications techniques</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:25%;">Spécifications obligatoires</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:27%;">Spécifications proposées</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bordereau->lignes as $index => $ligne)
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                                                        <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                        <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->specifications_techniques ?? '')) !!}</td>
                                                        <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->specifications_obligatoires ?? '')) !!}</td>
                                                        <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->specifications_proposees ?? '')) !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        @php
                                            $designationLabel = $isMethodes ? 'Désignation des services (Liste des équipements à maintenir)' : 'Désignation des produits';
                                            $dateLabel = ($isMethodes || $isCalendrier) ? 'Date de réalisation au lieu de destination finale' : 'Date de réalisation n.a';
                                        @endphp
                                        <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                            <thead>
                                                <tr style="background:#f3f4f6; font-weight:700;">
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:5%;">1</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:35%;">2</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:15%;">3</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:10%;">4</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:15%;">5</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:20%;">6</th>
                                                </tr>
                                                <tr style="background:#f3f4f6; font-weight:700;">
                                                    <th style="border:1px solid #000; padding:6px; text-align:center;">N° d'ordre</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left;">{{ $designationLabel }}</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center;">{{ $dateLabel }}</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center;">Quantité (nb d'unités)</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center;">Prix unitaire</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:center;">Prix total par article (Col 4*5)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bordereau->lignes as $index => $ligne)
                                                    @php
                                                        $prixHt = (float) ($ligne->prix_unitaire ?? 0);
                                                        $quantite = (int) ($ligne->quantite ?? 1);
                                                        // Pour Méthodes et Calendrier, appliquer la TVA au prix unitaire
                                                        $prix = ($isMethodes || $isCalendrier) ? round($prixHt * 1.18) : $prixHt;
                                                        $montantCalcule = $prix * $quantite;
                                                        $montant = (int)$ligne->montant ? $ligne->montant : $montantCalcule;
                                                    @endphp
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                                                        <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $ligne->date_prestation && trim($ligne->date_prestation) !== '' ? $ligne->date_prestation : '' }}</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ ($ligne->quantite && $ligne->quantite > 0) ? $ligne->quantite : 1 }}</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:right; vertical-align:top;">{{ $prix ? number_format($prix, 0, ',', ' ') : '' }}</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:right; vertical-align:top;">{{ $montant ? number_format($montant, 0, ',', ' ') : '' }}</td>
                                                    </tr>
                                                @endforeach
                                                @if($isMethodes || $isCalendrier)
                                                    @php
                                                        $totalHtCol6 = $bordereau->lignes->sum(function ($ligne) {
                                                            $prix = (float) ($ligne->prix_unitaire ?? 0);
                                                            $quantite = (int) ($ligne->quantite ?? 1);
                                                            $montantCalcule = $prix * $quantite;
                                                            return (int)$ligne->montant ? $ligne->montant : $montantCalcule;
                                                        });
                                                        $tvaCol6 = round($totalHtCol6 * 0.18);
                                                        $totalCol6 = $totalHtCol6 + $tvaCol6;
                                                    @endphp
                                                    <tr>
                                                        <td colspan="5" style="border:1px solid #000; padding:6px; font-weight:700; text-align:right;">TOTAL TTC Colonne 6</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:right; font-weight:700;">{{ number_format($totalCol6, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5" style="border:1px solid #000; padding:6px; font-weight:700; text-align:right;">TVA Colonne 6</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:right; font-weight:700;">{{ number_format($tvaCol6, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5" style="border:1px solid #000; padding:6px; font-weight:700; text-align:right;">Total HT Colonne 6</td>
                                                        <td style="border:1px solid #000; padding:6px; text-align:right; font-weight:700;">{{ number_format($totalHtCol6, 0, ',', ' ') }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    @endif
                                @else
                                    <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                        <thead>
                                            <tr style="background:#f3f4f6; font-weight:700;">
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:5%;" rowspan="2">N°</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:left; width:55%;" rowspan="2">{{ $bordereau->designation_label ?: 'Désignation des produits' }}</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:40%;" colspan="2">Prix unitaire Hors TVA (FCFA)</th>
                                            </tr>
                                            <tr style="background:#f3f4f6; font-weight:700;">
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:20%;">En lettres</th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:20%;">En chiffres</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bordereau->lignes as $index => $ligne)
                                                <tr>
                                                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                                                    <td style="border:1px solid #000; padding:6px; vertical-align:top;">{!! nl2br(e($ligne->designation)) !!}</td>
                                                    <td style="border:1px solid #000; padding:6px; vertical-align:top;">{{ $ligne->prix_unitaire ? mb_strtoupper($numberToWords($ligne->prix_unitaire)) . ' FCFA' : '' }}</td>
                                                    <td style="border:1px solid #000; padding:6px; text-align:right; vertical-align:top;">{{ $ligne->prix_unitaire ? number_format($ligne->prix_unitaire, 0, ',', ' ') : '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif($doc->typeDocument && $isPermat)
                @php
                    $attachments = $doc->fichiers ?? collect();
                    $fileAttachments = $attachments->filter(function ($f) {
                        $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'pdf'], true);
                    });
                @endphp
                <div style="margin-top:10px; font-size:11px; line-height:1.4; page-break-inside:avoid; text-align:left;">
                    @if($fileAttachments->isNotEmpty())
                        <div style="margin-top:14px; page-break-inside:avoid;">
                    @foreach($fileAttachments as $f)
                                @php
                                    $full = storage_path('app/public/' . ltrim($f->chemin_fichier, '/'));
                                    $mime = file_exists($full) ? mime_content_type($full) : null;
                                    $data = file_exists($full) ? base64_encode(file_get_contents($full)) : null;
                                    $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                                @endphp
                                @if($data && $mime && str_starts_with($mime, 'image/'))
                                    <div style="margin-bottom:14px; page-break-inside:avoid;">
                                        <img src="data:{{ $mime }};base64,{{ $data }}" alt="{{ basename($f->chemin_fichier) }}" style="max-width:100%; height:auto; border:1px solid #ccc; padding:4px;" />
                                    </div>
                                @elseif($ext === 'pdf' && $data && $mime === 'application/pdf')
                                    @php
                                        $tempImagePath = null;
                                        try {
                                            if (class_exists('Spatie\\PdfToImage\\Pdf')) {
                                                $tempImagePath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.png';
                                                $pdf = new \Spatie\PdfToImage\Pdf($full);
                                                $pdf->setPage(1)
                                                    ->setResolution(150, 150)
                                                    ->save($tempImagePath);
                                            } elseif (extension_loaded('imagick')) {
                                                $imagick = new \Imagick();
                                                $imagick->setResolution(150, 150);
                                                $imagick->readImage($full . '[0]');
                                                $imagick->setImageFormat('png');
                                                $imagick->stripImage();
                                                $tempImagePath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.png';
                                                $imagick->writeImage($tempImagePath);
                                                $imagick->destroy();
                                            } else {
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
                                        <div style="margin-bottom:14px; page-break-inside:avoid;">
                                            @php
                                                $pdfImageData = base64_encode(file_get_contents($tempImagePath));
                                                @unlink($tempImagePath);
                                            @endphp
                                            <img src="data:image/png;base64,{{ $pdfImageData }}" alt="Page 1" style="max-width:100%; height:auto; border:1px solid #ccc; padding:4px;" />
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
            @elseif($doc->typeDocument && $doc->typeDocument->type_formulaire === 'formulaire' && !$isExp42 && !$isUploadOnly && strcasecmp(trim($doc->typeDocument->nom), 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours') !== 0 && strcasecmp(trim($doc->typeDocument->nom), 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services') !== 0)
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

    {{-- entreprise info removed as requested --}}
</body>
</html>
