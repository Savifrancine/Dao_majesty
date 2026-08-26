<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulaire EXP-4.2 b) - {{ optional($formulaireExp42B->entreprise)->nom ?? $formulaireExp42B->nom_candidat ?? 'Formulaire' }}</title>
    <style>
        @page { margin: 12mm 20mm 20mm 20mm; }
        body { font-family: DejaVu Sans, Calibri, Segoe UI, Arial, Helvetica, sans-serif; color: #222; margin: 0; font-size: 12px; }
        .header-table { width:100%; border-collapse:collapse; margin-bottom:12px; }
        .header-table td { vertical-align:top; }
        .header-logo { width:115px; padding-right:14px; }
        .header-logo img { height:100px; width:auto; display:block; }
        .header-info { text-align:center; }
        .header-info .company-name { font-size:16px; font-weight:700; color:#1f78d1; }
        .header-info .company-details { font-size:10px; }
        .title { text-align:center; font-weight:700; font-size:15px; margin:6px 0 4px 0; text-transform:uppercase; }
        .subtitle { text-align:center; font-size:12px; margin:0 0 12px 0; }
        .section-table { width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px; }
        .section-table th, .section-table td { border:1px solid #000; padding:8px; }
        .section-table th { background:#f3f4f6; font-weight:700; }
        .block-table { width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px; }
        .block-table th, .block-table td { border:1px solid #000; padding:8px; vertical-align:top; }
        .block-table th { background:#f3f4f6; font-weight:700; text-align:center; }
        .signature-table { width:100%; border-collapse:collapse; margin-top:12px; font-size:11px; }
        .signature-table td { padding:8px; }
    </style>
</head>
<body>
    <style>
        @page { margin: 12mm 20mm 20mm 20mm; }
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
        .title-section { text-align:center; font-size:18px; font-weight:700; margin:6px 0 4px 0; }
        .subtitle { text-align:center; font-size:12px;  margin-bottom:12px; }
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
        .checkbox-box { display:inline-block; width:14px; height:14px; border:1px solid #000; margin-bottom:4px; line-height:14px; font-size:10px; text-align:center; }
    </style>

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
                        <div class="company-details">Maintenance – Service Après Vente</div>
                        <div class="header-separator"></div>
                        <div class="company-contact">06 BP 358 – Tel : +229 01 97 77 25 04 - Cotonou – République du Bénin</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="title-section">Formulaire EXP-4.2 b)</div>
        <div class="subtitle">Expérience spécifique de fournitures/services dans les principales activités</div>

    @php
        $dossier = $dossier ?? ($formulaireExp42B->dossier ?? null);
        $entreprise = $formulaireExp42B->entreprise ?? optional($dossier)->entreprise;

        $companyName = trim(
            optional($entreprise)->nom
            ?? optional($entreprise)->responsable
            ?? $formulaireExp42B->nom_candidat
            ?? ''
        );

        $numero_adpr_full = $formulaireExp42B->buildNumeroAdrp($dossier);

        $date_soumission_display = optional($dossier)->date_soumission ? optional($dossier)->date_soumission->format('d/m/Y') : '-';

        $autorite_nom = trim($formulaireExp42B->autorite_nom ?? '');
        $autorite_adresse = trim($formulaireExp42B->autorite_adresse ?? '');
        $autorite_telephone = trim($formulaireExp42B->autorite_telephone ?? '');
        $autorite_email = trim($formulaireExp42B->autorite_email ?? '');

        $signataire = $formulaireExp42B->signataire ?? null;
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

    <div style="margin-bottom:16px; font-size:11px; line-height:1.4;">
        <div style="display:flex; justify-content:center; align-items:flex-start; gap:12px; margin-bottom:6px;">
            <div style="flex:1; text-align:left;"><strong>Nom du candidat :</strong> {{ $companyName }}</div>
            <div style="flex:0 0 220px; text-align:right;"><strong>Date :</strong> {{ $date_soumission_display }}</div>
        </div>
        <div style="text-align:center; margin-top:6px; margin-left:8px; padding-left:16px;">
            <strong>N° ADRP :</strong>{{ $numero_adpr_full }}
        </div>
    </div>

    <table class="block-table">
        <tr>
            <td style="width:35%; vertical-align:top;"><strong>Numéro de marché similaire :</strong> {!! nl2br(e($formulaireExp42B->numero_marche ?? '')) !!}</td>
            <td colspan="3" style="vertical-align:top;"><strong>Informations</strong></td>
        </tr>
        <tr>
            <td style="width:35%;"><strong>Identification du marché :</strong></td>
            <td colspan="3" style="vertical-align:top;">{!! nl2br(e($formulaireExp42B->identification_marche ?? '')) !!}</td>
        </tr>
        <tr>
            <td><strong>Date d'attribution :</strong></td>
            <td colspan="3">{{ optional($formulaireExp42B->date_attribution)->format('d/m/Y') ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Date d'achèvement :</strong></td>
            <td colspan="3">{{ optional($formulaireExp42B->date_achevement)->format('d/m/Y') ?? '' }}</td>
        </tr>
        @php
            $montantTotal = '';
            if (!empty($formulaireExp42B->montant_total)) {
                $montantTotal = number_format((float) str_replace(["\xc2\xa0"," ",','], ['', '', '.'], $formulaireExp42B->montant_total), 0, ',', ' ');
            }
        @endphp
        <tr>
            <td><strong>Rôle dans le marché</strong></td>
            <td style="width:21%; text-align:center;">
                <div style="display:inline-block; width:14px; height:14px; border:1px solid #000; margin-bottom:4px; line-height:14px; font-size:10px; text-align:center; font-family: DejaVu Sans, Arial, sans-serif;">{{ trim($formulaireExp42B->role_marche) === 'Fournisseur/Prestataire' ? '✔' : '' }}</div>
                <div style="font-size:10px; line-height:1.1;">Fournisseur/<br>Prestataire</div>
            </td>
            <td style="width:21%; text-align:center;">
                <div style="display:inline-block; width:14px; height:14px; border:1px solid #000; margin-bottom:4px; line-height:14px; font-size:10px; text-align:center; font-family: DejaVu Sans, Arial, sans-serif;">{{ trim($formulaireExp42B->role_marche) === 'Ensemblier' ? '✔' : '' }}</div>
                <div style="font-size:10px; line-height:1.1;">Ensemblier</div>
            </td>
            <td style="width:23%; text-align:center;">
                <div style="display:inline-block; width:14px; height:14px; border:1px solid #000; margin-bottom:4px; line-height:14px; font-size:10px; text-align:center; font-family: DejaVu Sans, Arial, sans-serif;">{{ trim($formulaireExp42B->role_marche) === 'Sous-traitant' ? '✔' : '' }}</div>
                <div style="font-size:10px; line-height:1.1;">Sous-traitant</div>
            </td>
        </tr>
        <tr>
            <td><strong>Montant total du marché</strong></td>
            <td colspan="2" style="text-align:right; font-weight:700;">{{ $montantTotal ?: 'NEANT' }}</td>
            <td style="text-align:center; font-weight:700;">{{ $formulaireExp42B->monnaie ?? 'FCFA' }}</td>
        </tr>
        <tr>
            <td><strong>Dans le cas d’une partie à un GE ou d’un sous-traitant, préciser la participation au montant total du marché</strong></td>
            <td style="text-align:center;">{{ $formulaireExp42B->participation_pourcentage ? $formulaireExp42B->participation_pourcentage . ' %' : 'NEANT' }}</td>
            <td style="text-align:center;">{{ $formulaireExp42B->montant_part ? number_format((float) str_replace(["\xc2\xa0"," ",','], ['', '', '.'], $formulaireExp42B->montant_part), 0, ',', ' ') : 'NEANT' }}</td>
            <td style="text-align:center;">{{ $formulaireExp42B->monnaie ?? 'FCFA' }}</td>
        </tr>
        <tr>
            <td><strong>Nom de l'Autorité contractante :</strong></td>
            <td colspan="3" style="vertical-align:top;">{{ $autorite_nom ?: 'NEANT' }}</td>
        </tr>
        <tr>
            <td style="width:35%; vertical-align:top;">
                <strong>Adresse :</strong><br>
                <strong>Numéro de téléphone/Télécopie :</strong><br>
                <strong>Adresse électronique :</strong>
            </td>
            <td colspan="3" style="vertical-align:top;">
                {!! nl2br(e($autorite_adresse ?: 'NEANT')) !!}<br>
                {{ $autorite_telephone ?: 'NEANT' }}<br>
                {{ $autorite_email ?: 'NEANT' }}
            </td>
        </tr>
    </table>

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

