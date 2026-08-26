<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulaire EXP-4.2 b) (suite) - {{ optional($formulaireExp42BSuite->entreprise)->nom ?? $formulaireExp42BSuite->nom_candidat ?? 'Formulaire' }}</title>
    <style>
        @page { margin: 12mm 20mm 20mm 20mm; }
        body { font-family: DejaVu Sans, Calibri, Segoe UI, Arial, Helvetica, sans-serif; color: #222; margin: 0; font-size: 12px; }
        .page { width: 100%; padding: 0; margin: 0; }
        .header-block { font-family: Calibri, Segoe UI, Arial, sans-serif; color:#1f78d1; width:100%; margin-bottom:16px; }
        .header-table { width:100%; border-collapse:collapse; }
        .header-table td { vertical-align:top; }
        .header-logo { width:115px; padding-right:14px; }
        .header-logo img { height:100px; width:auto; display:block; }
        .header-info { text-align:center; }
        .header-info .company-name { font-size:19px; font-weight:700; line-height:1.15; margin-bottom:2px; color:#1f78d1; }
        .header-info .company-details { font-size:11px; line-height:1; margin-bottom:2px; }
        .header-info .company-contact { font-size:10.5px; line-height:1; }
        .header-separator { width:85%; margin:2px auto 4px auto; border-top:1px solid #1f78d1; }
        .title-section { text-align:center; font-size:18px; font-weight:700; margin:6px 0 4px 0; }
        .subtitle { text-align:center; font-size:12px;  margin-bottom:12px; }
        .main-table, .nested-table { width:100%; border-collapse:collapse; }
        .main-table th, .main-table td, .nested-table th, .nested-table td { border:1px solid #000; padding:8px; font-size:11px; }
        .main-table .section-title { background:#f5f5f5; font-weight:700; }
        .nested-table td { font-size:10px; }
        .experience-table { width:100%; border-collapse:collapse; margin-top:10px; }
        .experience-table th, .experience-table td { border:1px solid #000; padding:8px; font-size:10px; }
        .suite-table { width:100%; border-collapse:collapse; margin-top:10px; }
        .suite-table th, .suite-table td { border:1px solid #000; padding:8px; font-size:10px; }
        .suite-table td:first-child { width:35%; }
        .suite-table th { background:#f3f4f6; font-weight:700; text-align:left; }
        .signature-section { margin-top:24px; display:flex; justify-content:space-between; gap:16px; }
        .signature-block { flex:1; font-size:11px; line-height:1.4; }
        .signature-box { text-align:center; }
        .signature-line { width:220px; border-bottom:1px solid #000; margin:40px auto 6px auto; height:1px; }
        .footer { text-align:center; font-size:9px; color:#999; margin-top:22px; padding-top:10px; border-top:1px solid #ddd; }
        .checkbox-box { display:inline-block; width:14px; height:14px; border:1px solid #000; margin-bottom:4px; line-height:14px; font-size:10px; text-align:center; }
    </style>
</head>
<body>
    <di class="page">
        <!-- Header Block -->
        <div class="header-block">
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($logoDataUri)
                            <img src="{{ $logoDataUri }}" alt="Logo">
                        @else
                            <div style="color:#888; font-size:10px; text-align:center; width:115px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO</div>
                        @endif
                    </td>
                    <td class="header-info">
                        <div class="company-name">MAJESTY SERVICES ET EQUIPEMENTS</div>
                        <div class="company-details">Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire</div>
                        <div class="company-details">Maintenance – Service Après Vente</div>
                        <div style="width:85%; margin:2px auto 4px auto; border-top:1px solid #1f78d1;"></div>
                        <div class="company-details">06 BP 358 – Tel : +229 01 97 77 25 04 - Cotonou – République du Bénin</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Title -->
        <div class="title-section">FORMULAIRE EXP-4.2 b) (suite)</div>
        <div class="subtitle">Expérience spécifique de fournitures/services dans les activités principales (suite)</div>

        @php
            $dossier = $dossier ?? ($formulaireExp42BSuite->dossier ?? null);
            $entreprise = $formulaireExp42BSuite->entreprise ?? optional($dossier)->entreprise;

            $companyName = trim(
                optional($entreprise)->nom
                ?? optional($entreprise)->responsable
                ?? $formulaireExp42BSuite->nom_candidat
                ?? ''
            );

            $numero_adrp_full = $formulaireExp42BSuite->buildNumeroAdrp($dossier);

            $date_soumission_display = optional($dossier)->date_soumission ? optional($dossier)->date_soumission->format('d/m/Y') : '-';

            $signataire = $formulaireExp42BSuite->signataire ?? null;
            if (!$signataire) {
                if ($dossier && $dossier->relationLoaded('signataires')) {
                    $signataire = $dossier->signataires->firstWhere('pivot.role_signataire', 'gerant') ?? $dossier->signataires->first();
                } elseif ($dossier) {
                    $signataire = $dossier->signataires()->wherePivot('role_signataire', 'gerant')->first() ?? $dossier->signataires()->first();
                }
            }

            $signataire_nom = $signataire ? trim($signataire->nom . ' ' . ($signataire->prenom ?? '')) : trim($entreprise->responsable ?? '');
            $signataire_role = $signataire ? ($signataire->fonction ?: optional($entreprise)->fonction_responsable ?? '') : optional($entreprise)->fonction_responsable ?? '';
        @endphp

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-row">
                <div class="info-field"><strong>Nom du candidat :</strong> {{ $companyName }}</div>
                <div class="info-field" style="text-align:right;"><strong>Date :</strong> {{ $date_soumission_display }}</div>
            </div>
            <div style="text-align:center; margin-top:6px;"><strong>N° ADRP :</strong> {{ $numero_adrp_full }}</div>
        </div>

        <!-- Experience Table (2 columns like screenshot) -->
        <table class="suite-table">
            <tbody>
                <tr>
                    <td style="vertical-align:top; font-weight:700;"><strong>Numéro du marché similaire :</strong> {!! nl2br(e($formulaireExp42BSuite->numero_marche ?? '')) !!}</td>
                    <td style="vertical-align:top;"><strong>Informations</strong></td>
                </tr>
                <tr>
                    <td style="vertical-align:top; font-weight:700;">Description de la similitude conformément au sous-critère 4.2 a) :</td>
                    <td style="vertical-align:top;">{!! nl2br(e($formulaireExp42BSuite->description_similitude ?? 'NEANT')) !!}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top; font-weight:700;">Montant</td>
                    <td style="vertical-align:top;">{{ $formulaireExp42BSuite->montant ? e($formulaireExp42BSuite->montant) . ' FCFA' : 'NEANT' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top; font-weight:700;">Taille physique</td>
                    <td style="vertical-align:top;">{{ $formulaireExp42BSuite->taille_physique ?? 'NEANT' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top; font-weight:700;">Complexité</td>
                    <td style="vertical-align:top;">{{ $formulaireExp42BSuite->complexite ?? 'NEANT' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top; font-weight:700;">Méthodes/technologie</td>
                    <td style="vertical-align:top;">{{ $formulaireExp42BSuite->methodes_technologie ?? 'NEANT' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top; font-weight:700;">Autres caractéristiques</td>
                    <td style="vertical-align:top;">{{ $formulaireExp42BSuite->autres_caracteristiques ?? 'NEANT' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Signature Section -->
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


</body>
</html>

