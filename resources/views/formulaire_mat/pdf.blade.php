<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire MAT</title>
    <style>
        @page { margin: 25mm 20mm 20mm 20mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; margin: 0; padding: 0; }
        .page-wrapper { width: 100%; }
        
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
        .main-title { text-align:center; font-size:18px; font-weight:700; color:#0b63b5; margin:16px 0 8px 0; }
        .subtitle { text-align:center; font-size:12px; color:#0b63b5; margin-bottom:16px; }
        
        /* Titre */
        .title-section { text-align: center; font-size: 18px; font-weight: 700; margin: 12px 0 14px; }
        
        /* Table principale */
        .main-table { width: 100%; border-collapse: collapse; border: 2px solid #000; }
        .main-table td { border: 1px solid #000; padding: 8px; font-size: 11px; }
        .main-table .label { font-weight: 700; background: #f5f5f5; width: 20%; }
        .main-table .content { width: 80%; }
        .main-table .subrow { display: flex; gap: 8px; }
        .main-table .subcol { flex: 1; }
        
        /* Sections internes */
        .inner-table { width: 100%; border-collapse: collapse; }
        .inner-table td { border: 1px solid #000; padding: 6px; font-size: 10px; }
        .inner-table .label { font-weight: 700; background: #f5f5f5; width: 30%; }
        
        /* Signature */
        .signature-section { margin-top: 14px; }
        .sig-row { display: flex; gap: 16px; align-items: flex-start; }
        .sig-text { flex: 1; font-size: 11px; }
        .sig-block { text-align: center; }
        .sig-title { font-weight: 700; margin-bottom: 8px; font-size: 11px; }
        .sig-image { max-width: 180px; max-height: 100px; margin-bottom: 4px; }
        .sig-line { width: 180px; border-bottom: 1px solid #000; height: 2px; }
        
        /* Checkbox */
        .checkbox { display: inline-block; border: 1px solid #000; width: 14px; height: 14px; margin: 0 4px; vertical-align: middle; }
        .checkbox.checked::after { content: "✓"; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    @php
        $signataire = $formulaireMat->signataire;
        $provenance_labels = [
            'en_possession' => 'En possession',
            'en_location' => 'En location',
            'en_location_vente' => 'En location-vente',
            'fabrique_specialement' => 'Fabriqué spécialement',
        ];
    @endphp
    
    <div class="page-wrapper">
        <!-- Entête -->
        <div class="header-block">
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @php
                            $logoDataUri = '';
                            $logo = optional($formulaireMat->signataire)->logo ?? null;

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

                            if (!$logoDataUri) {
                                $fallback = storage_path('app/public/entreprises/logos/logo.jpeg');
                                if (file_exists($fallback)) {
                                    $ext = pathinfo($fallback, PATHINFO_EXTENSION);
                                    $data = base64_encode(file_get_contents($fallback));
                                    $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
                                }
                            }

                            if (!$logoDataUri) {
                                $fallback = storage_path('app/public/entreprises/logo.jpeg');
                                if (file_exists($fallback)) {
                                    $ext = pathinfo($fallback, PATHINFO_EXTENSION);
                                    $data = base64_encode(file_get_contents($fallback));
                                    $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
                                }
                            }

                            if (!$logoDataUri) {
                                $fallback2 = public_path('logo.jpeg');
                                if (file_exists($fallback2)) {
                                    $ext = pathinfo($fallback2, PATHINFO_EXTENSION);
                                    $data = base64_encode(file_get_contents($fallback2));
                                    $logoDataUri = 'data:image/' . $ext . ';base64,' . $data;
                                }
                            }
                        @endphp

                        @if($logoDataUri)
                            <img src="{{ $logoDataUri }}" alt="Logo">
                        @else
                            <div style="color:#888; font-size:10px; text-align:center; width:130px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO</div>
                        @endif
                    </td>
                    <td class="header-info">
                        <div class="company-name">MAJESTY SERVICES ET EQUIPEMENTS</div>
                        <div class="company-details">Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire</div>
                        <div class="company-details">Maintenance – Service Apres Vente</div>
                        <div class="header-separator"></div>
                        <div class="company-contact">06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – Republique du Benin</div>
                        <div class="company-contact">IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 - email : contact@majestyse.com / majestyse@gmail.com</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Titre -->
        <div class="title-section">Formulaire MAT</div>
        
        <!-- Table principale -->
        <table class="main-table">
            <!-- Pièce de matériel -->
            <tr>
                <td class="label">Pièce de matériel :</td>
                <td class="content">
                    <strong>{{ strtoupper($formulaireMat->piece_materiel ?? '') }}</strong>
                </td>
            </tr>
            
            <!-- Renseignement sur le matériel -->
            <tr>
                <td class="label" style="vertical-align: top;">Renseignement<br>sur le matériel</td>
                <td class="content">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50%; padding: 0 6px 4px 0; border: none; border-right: 1px solid #000; border-bottom: 1px solid #000;"><strong>Nom du fabricant :</strong> {{ $formulaireMat->fabricant ?? '-' }}</td>
                            <td style="width: 50%; padding: 0 0 4px 6px; border: none; border-bottom: 1px solid #000;"><strong>Modèle et puissance :</strong> {{ $formulaireMat->modele_puissance ?? '-' }}</td>
                        </tr>
                    </table>
                    <div style="margin-top: 6px;">
                        <strong>Capacité / Série de clés différentes taille :</strong> {{ $formulaireMat->capacite ?? '-' }}
                        <span style="float: right;"><strong>Année de fabrication :</strong> {{ $formulaireMat->annee_fabrication ?? '-' }}</span>
                    </div>
                </td>
            </tr>
            
            <!-- Position courante -->
            <tr>
                <td class="label" style="vertical-align: top;">Position<br>courante</td>
                <td class="content">
                    <strong>Localisation présente :</strong> {{ $formulaireMat->localisation ?? '-' }}
                    <div style="margin-top: 6px;"><strong>Détails sur les engagements courants :</strong></div>
                    <div style="margin-top: 4px; padding: 6px; background: #fafafa; border: 1px solid #ddd; min-height: 40px;">
                        {{ $formulaireMat->engagements ?? '' }}
                    </div>
                </td>
            </tr>
            
            <!-- Provenance -->
            <tr>
                <td class="label" style="vertical-align: top;">Provenance</td>
                <td class="content">
                    <strong>Indiquer la provenance du matériel :</strong><br><br>
                    <div style="margin-top: 4px;">
                        <span class="checkbox @if($formulaireMat->provenance === 'en_possession') checked @endif"></span> en possession
                        <span class="checkbox @if($formulaireMat->provenance === 'en_location') checked @endif"></span> en location
                        <span class="checkbox @if($formulaireMat->provenance === 'en_location_vente') checked @endif"></span> en location-vente
                        <span class="checkbox @if($formulaireMat->provenance === 'fabrique_specialement') checked @endif"></span> fabriqué spécialement
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Signature -->
        <div class="signature-section">
            <div style="font-weight: 700; margin-bottom: 8px; font-size: 12px;">
                Nom : <u>{{ $signataire ? strtoupper($signataire->nom . ' ' . $signataire->prenom) : '' }}</u> agissant au<br>
                nom et pour le compte de <u>MAJESTY SERVICES & EQUIPEMENTS SARL</u> en qualité de <u>{{ $signataire->fonction ?? '' }}</u>
            </div>
            
            <div class="sig-row" style="margin-top: 16px;">
                <div class="sig-text">
                    <div><strong>Signature</strong></div>
                    <br><br>
                    <div style="margin-top: 20px;">
                        <strong>Fait à</strong> {{ $formulaireMat->lieu_fait ?? 'Cotonou' }} <strong>le</strong> {{ $formulaireMat->date_fait ? $formulaireMat->date_fait->format('d/m/Y') : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
