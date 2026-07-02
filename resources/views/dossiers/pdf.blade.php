<!doctype html>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dossier->nom_dossier ?? 'Dossier' }}</title>
    <style>
        @page { margin: 12mm 20mm 20mm 20mm; }
        @page :first { margin-top: 10mm; }
        body { font-family: Calibri, Segoe UI, Arial, Helvetica, sans-serif; color: #222; margin: 0; font-size: 17px; }

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
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 240mm;
            margin: 0;
            padding: 0;
            text-align: center;
            font-size: 50px;
            page-break-after: auto;
            page-break-inside: avoid;
        }

        .title-page .title-text {
            font-size: 22px;
            font-weight: 900;
            color: #0b63b5;
            line-height: 1.15;
            width: 100%;
            margin-bottom: 6px;
        }

        .page-break-after { page-break-after: always; }

        table,
        table th,
        table td {
            font-size: 12px !important;
        }

        footer.pdf-footer {
            position: fixed;
            bottom: 4mm;
            left: 0;
            right: 0;
            height: 18px;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
        footer.pdf-footer:after {
            content: "Page " counter(page);
        }

        @page {
            @bottom-center {
                content: "Page " counter(page);
                font-size: 10px;
                color: #555;
            }
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

        .framed { margin:20px auto 22px; max-width:90%; border:14px solid #0b63b5; padding:7px; background:#0b63b5; }
        .framed .inner { background:#fff; padding:24px 20px; text-align:center; color:#0b63b5; }
        .framed .inner .main { font-size:26px; font-weight:800; line-height:1.2; }
        .framed .inner .sub { margin-top:12px; font-size:17px; line-height:1.25; }

        .big-subtitle { color:#0b63b5; font-weight:800; font-size:19px; margin-top:14px; letter-spacing:0.4px; }

        .meta-block { width:76%; margin:18px auto 6px; font-size:14px; color:#222; text-align:left; }
        .meta-block .line { margin-bottom:8px; line-height:1.5; }

        .center-date { text-align:center; margin-top:20px; font-weight:700; font-size:17px; }
        .cover-info-line { margin-top: 4px; }
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

            <div class="cover-info-lines" style="margin-top:18px; font-size:12px; text-align:center;">
                <div class="cover-info-line" style="font-weight:700;">{{ strtoupper($dossier->republique ?? 'REPUBLIQUE DU BENIN') }}</div>
                @if($dossier->ministere)
                    <div class="cover-info-line">{{ strtoupper($dossier->ministere) }}</div>
                @endif
                @if($dossier->services_projet)
                    <div class="cover-info-line">{{ strtoupper($dossier->services_projet) }}</div>
                @endif
            </div>

            @if($dossier->destinataires)
                <div style="text-align:center; margin-top:6mm; font-size:13px;">{{ $dossier->destinataires }}</div>
            @endif

            <div style="text-align:center; margin-top:4mm; font-size:13px;">
                Ref : {{ $dossier->reference_dossier ?? $dossier->ref ?? 'N/A' }} | Date lancement : {{ optional($dossier->date_lancement)->format('d/m/Y') ?? '-' }}
            </div>

                @if($dossier->nom_dossier)
                    <div style="text-align:center; margin-top:6mm; font-size:13px;">LA PERSONNE RESPONSABLE DES MARCHÉS PUBLICS</div>
                @endif

                {{-- Destinataires, référence et date de lancement (comme sur l'exemple) --}}
                @if($dossier->destinataires)
                    <div style="text-align:center; margin-top:3mm; font-size:13px;">{{ $dossier->destinataires }}</div>
                @endif

                @if($dossier->reference_dossier || $dossier->date_lancement)
                    <div style="text-align:center; margin-top:4mm; font-size:13px;">
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
        $formatter = extension_loaded('intl') ? new \NumberFormatter('fr', \NumberFormatter::SPELLOUT) : null;
        $fallback = null;
        $fallback = function(int $number) use (&$fallback) {
            $units = [
                0 => 'zéro', 1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq',
                6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix',
                11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze',
                16 => 'seize', 17 => 'dix-sept', 18 => 'dix-huit', 19 => 'dix-neuf',
            ];
            $tens = [
                0 => '', 1 => 'dix', 2 => 'vingt', 3 => 'trente', 4 => 'quarante',
                5 => 'cinquante', 6 => 'soixante', 7 => 'soixante-dix', 8 => 'quatre-vingt',
                9 => 'quatre-vingt-dix',
            ];

            if ($number < 20) {
                return $units[$number];
            }
            if ($number < 100) {
                $d = (int) floor($number / 10);
                $u = $number % 10;
                if ($d === 7 || $d === 9) {
                    $base = $tens[$d - 1];
                    $unitNumber = 10 + $u;
                    return $base . '-' . $units[$unitNumber];
                }
                $separator = $u === 1 && ($d === 1 || $d === 7 || $d === 9) ? ' et ' : ($u > 0 ? '-' : '');
                $word = $tens[$d];
                if ($u === 0) {
                    return $word;
                }
                return $word . $separator . $units[$u];
            }
            if ($number < 1000) {
                $hundreds = (int) floor($number / 100);
                $remainder = $number % 100;
                $hundredsText = $hundreds === 1 ? 'cent' : $units[$hundreds] . ' cent';
                if ($remainder === 0) {
                    return $hundredsText;
                }
                return $hundredsText . ' ' . $fallback($remainder);
            }
            if ($number < 1000000) {
                $thousands = (int) floor($number / 1000);
                $remainder = $number % 1000;
                $prefix = $thousands === 1 ? 'mille' : $fallback($thousands) . ' mille';
                if ($remainder === 0) {
                    return $prefix;
                }
                return $prefix . ' ' . $fallback($remainder);
            }
            $millions = (int) floor($number / 1000000);
            $remainder = $number % 1000000;
            $prefix = $millions === 1 ? 'un million' : $fallback($millions) . ' millions';
            if ($remainder === 0) {
                return $prefix;
            }
            return $prefix . ' ' . $fallback($remainder);
        };

        $numberToWords = function($value) use ($formatter, $fallback) {
            $value = (int) round($value);
            if ($value === 0) {
                return 'zéro';
            }

            if (!$formatter) {
                return $fallback($value);
            }

            $formatted = $formatter->format($value);
            if (preg_match('/\d/', $formatted)) {
                return $fallback($value);
            }

            return trim(mb_strtolower($formatted));
        };
    @endphp

    {{-- Documents content --}}
    @if(!in_array($renderMode, ['cover', 'summary'], true))
        @foreach($documents as $doc)
            @if($renderMode !== 'doc')
            <div class="title-page">
                <div class="title-text">{{ $doc->typeDocument->nom }}</div>
            </div>
            @endif
            @if($renderMode !== 'title')
            <div style="margin-top:2px;">
            {{-- Header to appear on every page except cover and sommaire --}}
            <div style="font-family: Calibri, Segoe UI, Arial, sans-serif; color:#1f78d1; width:100%; margin-bottom:8px;">
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

            @php
                $docName = trim($doc->typeDocument->nom ?? '');
                $isPermat = strcasecmp($docName, 'Formulaire PER') === 0 || strcasecmp($docName, 'Formulaire MAT') === 0;
            @endphp
            {{-- Display uploaded image files immediately after title for non-PER/MAT documents --}}
            @if(!$isPermat && $doc->fichiers && $doc->fichiers->count() > 0)
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

            @php
                $isGarantie = strcasecmp(trim($doc->typeDocument->nom), "Déclaration de garantie d'offre") === 0 || strcasecmp(trim($doc->typeDocument->nom), 'Declaration de garantie d\'offre') === 0;
                $isCandidateForm = strcasecmp(trim($doc->typeDocument->nom), 'Formulaire de renseignements sur le candidat') === 0;
                $formattedDateSoumission = $dossier->date_soumission ? \Illuminate\Support\Carbon::parse($dossier->date_soumission)->format('d/m/Y') : '-';
                $formattedDateLancement = $dossier->date_lancement ? \Illuminate\Support\Carbon::parse($dossier->date_lancement)->format('d/m/Y') : '-';
                $attention = $dossier->services_projet ?: $dossier->destinataires ?: 'AGENCE NON DEFINIE';
                $drp = $dossier->ref ?: 'N/A';
                $entreprise = $dossier->entreprise;
            @endphp

            @if($isGarantie)
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    <div style="text-align:right; font-weight:700;">Date : {{ $formattedDateSoumission }}</div>
                    <div style="text-align:right; font-weight:700; margin-bottom:16px;">DRP N° {{ $drp }}</div>

                    <p><strong>A l'attention de {{ strtoupper($attention) }}</strong></p>

                    <p><strong>Nous, soussignés, déclarons que :</strong></p>

                    <p>1. Nous reconnaissons que les offres doivent être accompagnées d'une déclaration de garantie d'offre.</p>

                    <p>2. Nous acceptons que nous ferons l'objet d'une suspension du droit de participer à la commande publique pour une période qui ne saurait être inférieure à un (01) an, si nous n'exécutons pas une des obligations auxquelles nous sommes tenus en vertu de l'offre, à savoir :</p>
                    <ol type="a" style="margin-left:20px;">
                        <li>Si nous retirons l'offre pendant la période de validité spécifiée dans la lettre de soumission de l'offre ; ou</li>
                        <li>s'étant vu notifier l'acceptation de l'offre par l'Autorité contractante pendant la période de validité telle qu'indiquée dans la lettre de soumission de l'offre ou prorogée par l'Autorité contractante avant l'expiration de cette période :</li>
                    </ol>

                    <ul style="margin-left:20px;">
                        <li>si nous n'acceptons pas les modifications de notre offre suite à la correction des erreurs de calcul ; ou</li>
                        <li>si nous ne signons pas le marché ; ou</li>
                        <li>si nous signons le marché et refusons de l'exécuter ; ou</li>
                        <li>si nous ne fournissons pas la garantie de bonne exécution du marché, si nous sommes tenus de le faire ainsi qu'il est prévu dans les Instructions aux candidats ;</li>
                    </ul>

                    <p>c) si nous sommes sous le coup d'une sanction de l'Autorité de régulation des marchés publics ou d'une juridiction administrative compétente, ayant pour objet la confiscation des garanties que nous avons constituées dans le cadre de la passation du marché, conformément à l'article 123 de la loi n°2020-26 du 29 septembre 2020 portant code des marchés publics en République du Bénin.</p>

                    <p>La présente lettre de déclaration de garantie expirera si le marché ne nous est pas attribué, à la première des dates suivantes :</p>
                    <ol type="i" style="margin-left:20px;">
                        <li>lorsque nous recevrons copie de votre notification du nom du soumissionnaire retenu ;</li>
                        <li>trente (30) jours suivant l'expiration du délai de validité de notre offre.</li>
                    </ol>

                    <p>Il est entendu que si nous sommes un groupement d'entreprises, la déclaration de garantie d'offre doit être au nom du groupement qui soumet l'offre. Si le groupement n'a pas été formellement constitué lors du dépôt d'offre, la déclaration de garantie de l'offre doit être au nom de tous les futurs membres du groupement nommés dans la lettre de déclaration.</p>

                    @php
                        $garantieSignataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                        $signataireNom = $garantieSignataire ? ($garantieSignataire->nom . ' ' . ($garantieSignataire->prenom ?? '')) : 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun';
                        $signataireRole = $garantieSignataire ? ($garantieSignataire->fonction ?: 'LE GERANT') : 'LE GERANT';
                    @endphp
                    <div style="margin-top:18px;">
                        <div style="border:1px solid #000; padding:10px; font-size:11px;">
                            <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $signataireNom }}</p>
                            <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataireRole }}</p>
                            <p style="margin:0 0 6px 0;"><strong>Signature :</strong> ________________________</p>
                            <p style="margin:0; text-align:right;">Date : {{ $formattedDateSoumission }}</p>
                        </div>
                    </div>
                </div>
            @elseif($isCandidateForm)
                @php
                    $formSignataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $formSignatureData = null;
                    $formCachetData = null;
                    if ($formSignataire) {
                        if (!empty($formSignataire->signature_path)) {
                            $sigFull = storage_path('app/public/' . ltrim($formSignataire->signature_path, '/'));
                            if (file_exists($sigFull)) {
                                $m = mime_content_type($sigFull) ?: 'image/png';
                                $formSignatureData = 'data:' . $m . ';base64,' . base64_encode(file_get_contents($sigFull));
                            }
                        }
                        if (!empty($formSignataire->cachet_path)) {
                            $cFull = storage_path('app/public/' . ltrim($formSignataire->cachet_path, '/'));
                            if (file_exists($cFull)) {
                                $mc = mime_content_type($cFull) ?: 'image/png';
                                $formCachetData = 'data:' . $mc . ';base64,' . base64_encode(file_get_contents($cFull));
                            }
                        }
                    }
                @endphp
                <div style="font-size:12px; line-height:1.5; margin-top:10px;">
                    <div style="text-align:center; font-weight:700; font-size:14px; margin-bottom:10px;">FORMULAIRE DE RENSEIGNEMENTS SUR LE CANDIDAT</div>
                    <div style="text-align:right; font-weight:700; margin-bottom:12px;">Date : {{ $formattedDateSoumission }}</div>
                    <div style="text-align:right; font-weight:700; margin-bottom:18px;">DRP N° {{ $drp }}</div>
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
                            <td style="border:1px solid #000; padding:8px; width:50%;"><strong>Numéro d'identification nationale des entreprises :</strong> {{ optional($entreprise)->ifu ?? '-' }}</td>
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
                                    <div style="margin-bottom:6px;">☐ Document d'enregistrement, d'inscription ou de constitution de la firme nommée en 1 ci-dessus, en conformité avec les clauses 3.1 et 3.2 des IC</div>
                                    <div>☐ En cas de groupement, lettre d'intention de constituer un groupement, ou accord de groupement, en conformité avec la clause 3.1 des IC.</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top:18px;">
                        <div style="border:1px solid #000; padding:10px; font-size:11px;">
                            <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $formSignataire ? ($formSignataire->nom . ' ' . ($formSignataire->prenom ?? '')) : (optional($entreprise)->responsable ?? '-') }}</p>
                            <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $formSignataire ? ($formSignataire->fonction ?: (optional($entreprise)->fonction_responsable ?? 'LE GERANT')) : (optional($entreprise)->fonction_responsable ?? 'LE GERANT') }}</p>
                            <p style="margin:0 0 6px 0;"><strong>Signature :</strong>
                                @if($formSignatureData)
                                    <div style="margin-top:6px;"><img src="{{ $formSignatureData }}" style="max-height:80px; display:block;"></div>
                                @else
                                    ____________________________
                                @endif
                            </p>
                            <p style="margin:0; text-align:right;">Date : {{ optional($dossier)->date_signature ? \Illuminate\Support\Carbon::parse($dossier->date_signature)->format('d/m/Y') : now()->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), "Chiffre d'affaires annuel moyen des activités de services") === 0)
                @php
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
                    $content = $doc->content ?? '';
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    {!! nl2br(e($content)) !!}
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
                    <div style="margin-top:18px; font-size:11px;">
                        <div style="border:1px solid #000; padding:10px; font-size:11px;">
                            <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ optional($dossier->entreprise)->responsable ?? 'N/A' }}</p>
                            <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ optional($dossier->entreprise)->fonction_responsable ?? 'LE GERANT' }}</p>
                            <p style="margin:0 0 6px 0;"><strong>Signature :</strong> ___________________________</p>
                            <p style="margin:0; text-align:right;"><strong>Date :</strong> {{ now()->format('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Bordereau des prix pour les fournitures à importer') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">BORDEREAU DES PRIX POUR LES FOURNITURES À IMPORTER</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>AAO numéro :</strong> {{ $dossier->reference_dossier ?? 'N/A' }} <br>
                            <strong>Variante :</strong> (à compléter)
                        </div>

                        @foreach($bordereaux as $bordereau)
                            <div style="overflow-x:auto; margin-bottom:20px;">
                                <table style="width:100%; border-collapse:collapse; font-size:9px;">
                                    <thead>
                                        <tr style="background:#f3f4f6; font-weight:700;">
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:4%;">Article</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:left; width:24%;">Description</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:10%;">Date de livraison</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:center; width:8%;">Quantité (Nb. d'unités)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right; width:13%;">Prix unitaire HTVA (RETROCESSION L'INCOTERM APPLICABLE)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right; width:13%;">Prix total HTVA (selon l'incoterm applicable) par article (cols 4 x 5)</th>
                                            <th style="border:1px solid #000; padding:5px; text-align:right; width:16%;">Coût main-d'œuvre locale, matière premières et composants provenant du Bénin ou de l'UEMOA de col.5</th>
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
                                                <td style="border:1px solid #000; padding:5px; text-align:right; vertical-align:top;">{{ $coutBenin ? number_format($coutBenin, 0, ',', ' ') : '' }}</td>
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

                        <div style="margin-top:18px; padding-top:12px; border-top:1px solid #ccc; page-break-inside:avoid; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="width:50%; font-size:10px;">&nbsp;</div>
                            <div style="width:48%; text-align:center; font-size:10px;">
                                <div style="border:1px solid #000; padding:10px; font-size:11px; text-align:left; display:inline-block; width:100%;">
                                    <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ optional($dossier->entreprise)->nom ?? 'N/A' }}</p>
                                    <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataire ? ($signataire->fonction ?: 'LE GERANT') : 'LE GERANT' }}</p>
                                    <p style="margin:0 0 6px;"><strong>Signature :</strong> ________________________</p>
                                    <p style="margin:0 0 6px;"><strong>Cachet :</strong> ___________________________</p>
                                    <p style="margin:0; text-align:right;">Date : {{ now()->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Listes des Fournitures et Calendrier de livraison') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">LISTES DES FOURNITURES ET CALENDRIER DE LIVRAISON</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>AAO numéro :</strong> {{ $dossier->reference_dossier ?? 'N/A' }} <br>
                            <strong>Variante :</strong> (à compléter)
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
                        <div style="margin-bottom:20px; font-size:10px;">
                            <strong>Remarque :</strong> Les dates de livraison sont indiquées avec les colonnes "Plus tôt", "Plus tard" et "Offerte".
                        </div>
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif(strcasecmp(trim($doc->typeDocument->nom), 'Cadres de sous détails des prix unitaire') === 0)
                @php
                    $bordereaux = $doc->bordereau;
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">CADRE DE SOUS DETAILS DES PRIX UNITAIRES</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>AAO numéro :</strong> {{ $dossier->reference_dossier ?? 'N/A' }} <br>
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
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:11px; line-height:1.4;">
                        <div style="font-weight:700; font-size:13px; text-align:center; margin-bottom:12px;">{{ $documentTitle }}</div>
                        <div style="text-align:right; font-size:10px; margin-bottom:8px;">
                            <strong>Date :</strong> {{ now()->format('d/m/Y') }} <br>
                            <strong>AAO numéro :</strong> {{ $dossier->reference_dossier ?? 'N/A' }} <br>
                            <strong>Variante :</strong> (à compléter)
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

                        <div style="margin-top:18px; padding-top:12px; border-top:1px solid #ccc; page-break-inside:avoid; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="width:50%; font-size:10px;">&nbsp;</div>
                            <div style="width:48%; text-align:center; font-size:10px;">
                                <div style="border:1px solid #000; padding:10px; font-size:11px; text-align:left; display:inline-block; width:100%;">
                                    <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ optional($dossier->entreprise)->nom ?? 'N/A' }}</p>
                                    <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataire ? ($signataire->fonction ?: 'LE GERANT') : 'LE GERANT' }}</p>
                                    <p style="margin:0 0 6px;"><strong>Signature :</strong> ________________________</p>
                                    <p style="margin:0 0 6px;"><strong>Cachet :</strong> ___________________________</p>
                                    <p style="margin:0; text-align:right;">Date : {{ now()->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
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
                    $isDescriptionTechnique = strcasecmp(trim($doc->typeDocument->nom), "Description technique des services") === 0;
                @endphp
                @if($bordereaux && $bordereaux->count() > 0)
                    <div style="margin-top:10px; font-size:12px; line-height:1.4;">
                        <div style="font-weight:700; font-size:14px; text-align:center; margin-bottom:6px;">{{ $isMethodes ? 'MÉTHODOLOGIE D\'EXÉCUTION' : ($isProgramme ? 'PROGRAMME D\'ACTIVITÉS' : ($isCalendrier ? 'CALENDRIER D\'EXÉCUTION' : ($isDescriptionTechnique ? 'DESCRIPTION TECHNIQUE DES SERVICES' : 'BORDEREAU DES PRIX UNITAIRES')) ) }}</div>

                        @foreach($bordereaux as $bordereau)
                            <div style="font-weight:700; font-size:12px; text-align:center; margin-bottom:10px;">{{ strtoupper($bordereau->titre ?: ($isMethodes ? 'MÉTHODOLOGIE D\'EXÉCUTION' : ($isProgramme ? 'PROGRAMME D\'ACTIVITÉS' : ($isCalendrier ? 'CALENDRIER D\'EXÉCUTION' : 'LOT : BORDEREAU DES PRIX UNITAIRES')))) }}</div>
                            <div style="overflow-x:auto; margin-bottom:18px;">
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
                                        <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                            <thead>
                                                <tr style="background:#f3f4f6; font-weight:700;">
                                                    <th style="border:1px solid #000; padding:6px; text-align:center; width:5%;">N°</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:28%;">Désignation</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:22%;">Spécifications techniques</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:22%;">Spécifications obligatoires</th>
                                                    <th style="border:1px solid #000; padding:6px; text-align:left; width:23%;">Spécifications proposées</th>
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
                                        <div style="margin-top:18px;">
                                            <div style="border:1px solid #000; padding:10px; font-size:11px;">
                                                <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $signataire ? ($signataire->nom . ' ' . ($signataire->prenom ?? '')) : 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun' }}</p>
                                                <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataire ? ($signataire->fonction ?: 'LE GERANT') : 'LE GERANT' }}</p>
                                                <p style="margin:0 0 6px 0;"><strong>Signature :</strong> ____________________________</p>
                                                <p style="margin:0; text-align:right;">Date : {{ now()->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
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
                                                <th style="border:1px solid #000; padding:6px; text-align:left; width:55%;" rowspan="2">Désignation des services<br><span style="font-size:9px; color:#555;">(Liste des équipements à maintenir)</span></th>
                                                <th style="border:1px solid #000; padding:6px; text-align:center; width:40%;" colspan="2">Prix unitaire d'entretien et maintenance Hors TVA (FCFA)</th>
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

                        <div style="margin-top:18px; padding-top:12px; border-top:1px solid #ccc; page-break-inside:avoid;">
                            <div style="border:1px solid #000; padding:10px; font-size:11px;">
                                <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $signataire ? ($signataire->nom . ' ' . ($signataire->prenom ?? '')) : 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun' }}</p>
                                <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataire ? ($signataire->fonction ?: 'LE GERANT') : 'LE GERANT' }}</p>
                                <p style="margin:0 0 6px 0;"><strong>Signature :</strong> ________________________</p>
                                <p style="margin:0; text-align:right;">Date : {{ now()->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="margin-top:8px; font-style:italic; color:#444;">Aucun bordereau enregistré pour ce document.</div>
                @endif
            @elseif($doc->typeDocument && $isPermat)
                @php
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                    $attachments = $doc->fichiers ?? collect();
                    $imageAttachments = $attachments->filter(function ($f) {
                        $ext = strtolower(pathinfo($f->chemin_fichier, PATHINFO_EXTENSION));
                        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif'], true);
                    });
                @endphp
                <div style="margin-top:10px; font-size:12px; line-height:1.5;">
                    <div style="font-weight:700; font-size:13px; margin-bottom:8px;">{{ $doc->typeDocument->nom }}</div>

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
                        {{-- Pas de rendu de liste de fichiers pour les PER/MAT vides --}}
                    @endif

                    <div style="margin-top:18px; page-break-inside:avoid;">
                        <div style="border:1px solid #000; padding:12px; font-size:11px; max-width:700px;">
                            <p style="margin:0 0 6px 0; line-height:1.35;"><strong>Nom :</strong> {{ $signataire ? ($signataire->nom . ' ' . ($signataire->prenom ?? '')) : 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun' }} agissant au nom et pour le compte de <strong>{{ optional($dossier->entreprise)->nom ?? 'MAJESTY SERVICES & EQUIPEMENTS SARL' }}</strong></p>
                            <p style="margin:0 0 6px 0; line-height:1.35;">SARL en qualité de <strong>{{ $signataire ? ($signataire->fonction ?: 'GERANT') : 'GERANT' }}</strong></p>
                            <p style="margin:12px 0 6px 0; font-weight:700;">Signé</p>
                            <p style="margin:0; text-align:right;">Fait à Cotonou le {{ optional($dossier)->date_signature ? \Illuminate\Support\Carbon::parse($dossier->date_signature)->format('d/m/Y') : now()->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            @elseif($doc->typeDocument && $doc->typeDocument->type_formulaire === 'formulaire')
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

                    <div style="margin-top:18px;">
                        <div style="border:1px solid #000; padding:10px; font-size:11px;">
                            <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $formSignataire ? ($formSignataire->nom . ' ' . ($formSignataire->prenom ?? '')) : (optional($entreprise)->responsable ?? '-') }}</p>
                            <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $formSignataire ? ($formSignataire->fonction ?: (optional($entreprise)->fonction_responsable ?? 'LE GERANT')) : (optional($entreprise)->fonction_responsable ?? 'LE GERANT') }}</p>
                            <p style="margin:0 0 6px 0;"><strong>Signature :</strong>
                                @if($formSignatureData)
                                    <span style="display:block; margin-top:6px;"><img src="{{ $formSignatureData }}" style="max-height:80px; display:block;"></span>
                                @else
                                    ____________________________
                                @endif
                            </p>
                            <p style="margin:0; text-align:right;">Date : {{ optional($dossier)->date_signature ? \Illuminate\Support\Carbon::parse($dossier->date_signature)->format('d/m/Y') : now()->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div style="margin-top:18px;">
                        <div style="border:1px solid #000; padding:10px; font-size:11px;">
                            <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $signataire ? ($signataire->nom . ' ' . ($signataire->prenom ?? '')) : 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun' }}</p>
                            <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataire ? ($signataire->fonction ?: 'LE GERANT') : 'LE GERANT' }}</p>
                            <p style="margin:0 0 6px 0;"><strong>Signature :</strong> ________________________</p>
                            <p style="margin:0; text-align:right;">Date : {{ now()->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    @if($attachments->isEmpty())
                        {{-- Pas de rendu de liste de fichiers pour ce document --}}
                    @endif
                </div>
            @elseif($doc->fichiers && $doc->fichiers->count() > 0)
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

                    <div style="margin-top:18px;">
                        <div style="border:1px solid #000; padding:10px; font-size:11px;">
                            <p style="margin:0 0 6px 0;"><strong>Nom :</strong> {{ $signataire ? ($signataire->nom . ' ' . ($signataire->prenom ?? '')) : 'TCHABY Onésime Godwin Akambi Adjè Tèhègoun' }}</p>
                            <p style="margin:0 0 6px 0;"><strong>En tant que :</strong> {{ $signataire ? ($signataire->fonction ?: 'LE GERANT') : 'LE GERANT' }}</p>
                            <p style="margin:0 0 6px 0;"><strong>Signature :</strong> ________________________</p>
                            <p style="margin:0; text-align:right;">Date : {{ now()->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div style="margin-top:8px;">Aucun contenu disponible pour ce document.</div>
            @endif
        </div>
            @endif
    @endforeach
@endif

    {{-- entreprise info removed as requested --}}

    <footer class="pdf-footer"></footer>
</body>
</html>
