<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire PER - {{ $formulaire_per->nom_candidat }}</title>
    <style>
        @page { margin: 25mm 20mm 20mm 20mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; margin: 0; padding: 0; }
        .page { width: 100%; padding: 0; margin: 0; }
        .header-block { font-family: Calibri, Segoe UI, Arial, sans-serif; color:#1f78d1; width:100%; margin-bottom:16px; }
        .header-table { width:100%; border-collapse:collapse; }
        .header-table td { vertical-align:top; }
        .header-logo { width:115px; padding-right:14px; }
        .header-logo img { height:100px; width:auto; display:block; }
        .header-info { text-align:center; }
        .header-info .company-name { font-size:19px; font-weight:700; line-height:1.15; margin-bottom:2px; }
        .header-info .company-details { font-size:11px; line-height:1; margin-bottom:2px; }
        .header-info .company-contact { font-size:10.5px; line-height:1; }
        .header-separator { width:85%; margin:2px auto 4px auto; border-top:1px solid #1f78d1; }
        .title-section { text-align:center; font-size:18px; font-weight:700; color:#0b63b5; margin:16px 0 8px 0; }
        .subtitle { text-align:center; font-size:12px; color:#0b63b5; margin-bottom:16px; }
        .main-table, .nested-table { width:100%; border-collapse:collapse; }
        .main-table th, .main-table td, .nested-table th, .nested-table td { border:1px solid #000; padding:8px; font-size:11px; }
        .main-table .section-title { background:#f5f5f5; font-weight:700; }
        .nested-table td { font-size:10px; }
        .experience-table { width:100%; border-collapse:collapse; margin-top:10px; }
        .experience-table th, .experience-table td { border:1px solid #000; padding:8px; font-size:10px; }
        .signature-section { margin-top:24px; display:flex; justify-content:space-between; gap:16px; }
        .signature-block { flex:1; font-size:11px; line-height:1.4; }
        .signature-box { text-align:center; }
        .signature-line { width:220px; border-bottom:1px solid #000; margin:40px auto 6px auto; height:1px; }
        .footer { text-align:center; font-size:9px; color:#999; margin-top:22px; padding-top:10px; border-top:1px solid #ddd; }
    </style>
</head>
<body>
    @php
        $signatureName = $formulaire_per->nom_personnel ?: $formulaire_per->nom_candidat;
        $signatureTitle = $formulaire_per->poste ?: 'PERSONNEL PROPOSÉ';
        $logoDataUri = null;
        $logoPaths = [
            storage_path('app/public/entreprises/logos/logo.jpeg'),
            storage_path('app/public/entreprises/logo.jpeg'),
            public_path('logo.jpeg'),
            public_path('images/logo.jpeg'),
        ];
        foreach ($logoPaths as $path) {
            if (file_exists($path)) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $mime = in_array(strtolower($ext), ['png','jpg','jpeg','gif']) ? 'image/' . strtolower($ext) : 'image/png';
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                break;
            }
        }
    @endphp

    <div class="page">
        <div class="header-block">
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($logoDataUri)
                            <img src="{{ $logoDataUri }}" alt="Logo Majesty">
                        @else
                            <div style="color:#888; font-size:10px; text-align:center; width:115px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO</div>
                        @endif
                    </td>
                    <td class="header-info">
                        <div class="company-name">MAJESTY SERVICES ET EQUIPEMENTS</div>
                        <div class="company-details">Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire</div>
                        <div class="company-details">Maintenance – Service Apres Vente</div>
                        <div class="header-separator"></div>
                        <div class="company-contact">06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – République du Bénin</div>
                        <div class="company-contact">IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 - email : contact@majestyse.com / majestyse@gmail.com</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="title-section">Formulaire PER</div>
        <div class="subtitle">Curriculum Vitae du Personnel Proposé</div>

        <table class="main-table">
            <tr>
                <th class="section-title" colspan="4">Nom du candidat</th>
            </tr>
            <tr>
                <td colspan="4">{{ $formulaire_per->nom_candidat }}</td>
            </tr>
            <tr>
                <th class="section-title" colspan="4">Poste</th>
            </tr>
            <tr>
                <td colspan="4">{{ $formulaire_per->poste }}</td>
            </tr>
            <tr>
                <th class="section-title" colspan="2">Nom</th>
                <th class="section-title" colspan="2">Date de naissance</th>
            </tr>
            <tr>
                <td colspan="2">{{ $formulaire_per->nom_personnel }}</td>
                <td colspan="2">{{ $formulaire_per->date_naissance?->format('d/m/Y') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th class="section-title" colspan="4">Qualifications professionnelles</th>
            </tr>
            <tr>
                <td colspan="4">{{ $formulaire_per->qualifications ?? '' }}</td>
            </tr>
            <tr>
                <th class="section-title" colspan="4">Employeur actuel</th>
            </tr>
            <tr>
                <td colspan="2"><strong>Nom de l'employeur :</strong></td>
                <td colspan="2">{{ $formulaire_per->nom_employeur }}</td>
            </tr>
            <tr>
                <td colspan="4"><strong>Adresse de l'employeur :</strong> {{ $formulaire_per->adresse_employeur }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Téléphone :</strong> {{ $formulaire_per->telephone }}</td>
                <td colspan="2"><strong>Contact :</strong> {{ $formulaire_per->contact_personnel }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Télécopie :</strong> {{ $formulaire_per->telecopie ?? '' }}</td>
                <td colspan="2"><strong>Email :</strong> {{ $formulaire_per->email }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Emploi tenu :</strong> {{ $formulaire_per->emploi_tenu }}</td>
                <td colspan="2"><strong>Nombre d'années avec le présent employeur :</strong> {{ $formulaire_per->nombre_annees_employeur }} ans</td>
            </tr>
        </table>

        <div style="margin-top:16px; font-size:11px; font-weight:700;">Résumé de l'expérience professionnelle des dix (10) dernières années</div>
        <div style="margin-bottom:8px; font-size:10px;">Indiquer l'expérience pertinente pour le projet en ordre chronologique inverse.</div>
        <table class="experience-table">
            <thead>
                <tr>
                    <th style="width:14%;">De</th>
                    <th style="width:14%;">À</th>
                    <th style="width:72%;">Société / projet / position / expérience pertinente</th>
                </tr>
            </thead>
            <tbody>
                @if($formulaire_per->experiences && count($formulaire_per->experiences) > 0)
                    @foreach($formulaire_per->experiences as $exp)
                    <tr>
                        <td>{{ $exp['de'] ?? '' }}</td>
                        <td>{{ $exp['a'] ?? '' }}</td>
                        <td>{{ $exp['description'] ?? '' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" style="height:80px;"></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Signature section removed as requested -->

        <div class="footer">
            <div>Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</body>
</html>
