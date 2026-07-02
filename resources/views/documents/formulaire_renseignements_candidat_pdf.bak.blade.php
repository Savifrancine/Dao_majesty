<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Formulaire de renseignements sur le candidat</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; margin:0; padding:0; background:#fff; }
        .page { width:100%; max-width:840px; margin:0 auto; padding:18px 22px; }
        .top-header { display:flex; justify-content:space-between; gap:12px; border:2px solid #0b63b5; padding:16px; }
        .brand { width:62%; font-size:10.5px; color:#0b63b5; line-height:1.35; }
        .brand .name { font-size:15px; font-weight:700; color:#0b63b5; margin-bottom:4px; }
        .brand .line { margin-bottom:3px; }
        .brand .separator { display:block; width:120px; height:1px; background:#0b63b5; margin:10px 0; }
        .contact-box { width:34%; border:1px solid #0b63b5; padding:10px 12px; font-size:10px; color:#0b63b5; line-height:1.35; text-align:right; }
        .contact-box .title { font-weight:700; margin-bottom:6px; }
        .document-title { text-align:center; font-weight:700; font-size:16px; color:#0b263e; margin:16px 0 8px; }
        .meta-row { display:flex; justify-content:flex-end; gap:10px; margin-bottom:8px; font-size:11px; }
        .meta-item { min-width:160px; padding:8px 10px; border:1px solid #0b63b5; border-radius:4px; background:#eef4ff; }
        .meta-item .label { display:block; font-weight:700; margin-bottom:4px; }
        .main-table { width:100%; border-collapse:collapse; margin-bottom:14px; font-size:11px; border:2px solid #0b63b5; }
        .main-table td { border:1px solid #0b63b5; padding:10px 10px; vertical-align:top; }
        .main-table .number { width:30px; font-weight:700; text-align:center; }
        .main-table .label { width:220px; font-weight:700; }
        .main-table .value { font-size:11px; }
        .section { margin-bottom:18px; }
        .section-title { font-weight:700; margin-bottom:6px; font-size:12px; }
        .section-text { margin-bottom:10px; font-size:10.5px; font-style:italic; }
        .checkbox-table { width:100%; border-collapse:collapse; font-size:11px; }
        .checkbox-table td { border:1px solid #0b63b5; padding:10px 10px; vertical-align:top; }
        .checkbox-cell { width:28px; text-align:center; font-size:14px; }
        .signature-block { border:2px solid #0b63b5; padding:16px; margin-top:16px; }
        .signature-text { font-size:11px; margin-bottom:10px; }
        .signature-row { display:flex; gap:18px; margin-top:16px; }
        .signature-box { flex:1; border-top:1px solid #0b63b5; padding-top:12px; min-height:72px; font-size:11px; }
        .footer-note { font-size:10px; margin-top:20px; color:#333; }
    </style>
</head>
<body>
    <div class="page">
        <div class="top-header">
            <div class="brand">
                <div class="name">{{ optional($entreprise)->nom ? strtoupper(optional($entreprise)->nom) : 'MAJESTY SERVICES ET EQUIPEMENTS SARL' }}</div>
                <div class="line">Medical &amp; Laboratoires - Equipements Pétroliers</div>
                <div class="line">Ingénierie Informatique - Environnement &amp; Eaux</div>
                <div class="line">Maintenance &amp; SAV</div>
                <span class="separator"></span>
                <div class="line">{{ optional($entreprise)->adresse ?? '06 BP 358 Cotonou - BENIN' }}</div>
                <div class="line">Tel: {{ optional($entreprise)->telephone ?? '+229 97 77 25 04' }}</div>
                <div class="line">Email: {{ optional($entreprise)->email ?? 'majestyse@gmail.com' }}</div>
                <div class="line">IFU: {{ optional($entreprise)->ifu ?? '3201300353515' }}</div>
            </div>
            <div class="contact-box">
                <div class="title">C/918 Agblodjèdo</div>
                <div>06 BP 358 Cotonou - BENIN</div>
                <div>Tél : +229 97 77 25 04</div>
                <div>Email : majestyse@gmail.com</div>
                <div>IFU : 3201300353515</div>
            </div>
        </div>

        <div class="document-title">FORMULAIRE DE RENSEIGNEMENTS SUR LE CANDIDAT</div>

        <div class="meta-row">
            <div class="meta-item">
                <span class="label">Date</span>
                {{ now()->format('d/m/Y') }}
            </div>
            <div class="meta-item">
                <span class="label">DRP N°</span>
                {{ $drp_number ?? 'N/A' }}
            </div>
        </div>

        <table class="main-table">
            <tr>
                <td class="number">1.</td>
                <td class="label">Nom du candidat :</td>
                <td class="value" colspan="4">{{ $nom_candidat ?? optional($entreprise)->nom ?? '' }}</td>
            </tr>
            <tr>
                <td class="number">2.</td>
                <td class="label">En cas de groupement, noms de tous les membres :</td>
                <td class="value" colspan="4">{{ $groupement_membres ?: 'Néant' }}</td>
            </tr>
            <tr>
                <td class="number">3.</td>
                <td class="label">Pays où le candidat est, ou sera légalement enregistré :</td>
                <td class="value">{{ $pays_candidat ?? '' }}</td>
                <td class="number">4.</td>
                <td class="label">Numéro d'identification nationale des entreprises :</td>
                <td class="value">{{ $identification_nationale ?: optional($entreprise)->ifu ?: '-' }}</td>
            </tr>
            <tr>
                <td class="number">5.</td>
                <td class="label">Année d'enregistrement du candidat :</td>
                <td class="value" colspan="4">{{ $annee_enregistrement ?: optional($entreprise)->annee_enregistrement ?: '-' }}</td>
            </tr>
            <tr>
                <td class="number">6.</td>
                <td class="label">Adresse officielle du candidat dans le pays d'enregistrement :</td>
                <td class="value" colspan="4">{{ $adresse_officielle ?: optional($entreprise)->adresse_officielle ?? optional($entreprise)->adresse ?? '-' }}</td>
            </tr>
            <tr>
                <td class="number">7.</td>
                <td class="label">Renseignement sur le représentant dûment habilité du candidat :</td>
                <td class="value" colspan="4">
                    Nom : {{ $nom_representant ?? optional($entreprise)->responsable ?? '-' }}<br>
                    Adresse : {{ $adresse_representant ?: optional($entreprise)->adresse_officielle ?? optional($entreprise)->adresse ?? '-' }}<br>
                    Téléphone/Fac-simile : {{ $telephone_representant ?: optional($entreprise)->telephone ?: '-' }}<br>
                    Adresse électronique : {{ $email_representant ?: optional($entreprise)->email ?: '-' }}
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Ci-joint copie des originaux des documents ci-après :</div>
            <div class="section-text">[cocher la(les) case(s)] correspondant aux documents originaux joints</div>
            <table class="checkbox-table">
                <tr>
                    <td class="checkbox-cell">☐</td>
                    <td>Document d'enregistrement, d'inscription ou de constitution de la firme nommée en 1 ci-dessus, en conformité avec les clauses 3.1 et 3.2 des IC.</td>
                </tr>
                <tr>
                    <td class="checkbox-cell">☐</td>
                    <td>En cas de groupement, lettre d'intention de constituer un groupement, ou accord de groupement, en conformité avec la clause 3.1 des IC.</td>
                </tr>
            </table>
        </div>

        <div class="signature-block">
            <div class="signature-text"><strong>Nom :</strong> {{ $nom_representant ?? optional($entreprise)->responsable ?? '________________' }}</div>
            <div class="signature-text"><strong>En tant que :</strong> {{ $fonction_representant ?? 'LE GERANT' }}</div>
            <div class="signature-text"><strong>En date du :</strong> {{ now()->format('d/m/Y') }}</div>
            <div class="signature-row">
                <div class="signature-box">Signature</div>
                <div class="signature-box">Cachet</div>
            </div>
        </div>

        <div class="footer-note">Ce formulaire est conçu pour respecter le format et l’aspect visuel du document fourni.</div>
    </div>
</body>
</html>
