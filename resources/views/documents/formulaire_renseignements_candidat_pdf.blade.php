<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Formulaire de renseignements sur le candidat</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size:12px; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; }
        .header .company { width:65%; }
        .header .meta { width:35%; text-align:right; font-size:11px; }
        .title { text-align:center; font-weight:700; font-size:16px; margin-bottom:18px; }
        .section { margin-bottom:16px; }
        .section h3 { margin-bottom:8px; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; }
        .field { display:flex; gap:12px; margin-bottom:6px; }
        .field label { width:180px; font-weight:700; }
        .field .value { flex:1; }
        .box { border:1px solid #666; padding:10px; border-radius:8px; background:#f8f8f8; }
        .signature { margin-top:24px; display:flex; justify-content:space-between; gap:12px; }
        .signature .block { width:48%; }
        .footer-note { font-size:10px; margin-top:14px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <strong>FORMULAIRE DE RENSEIGNEMENTS SUR LE CANDIDAT</strong>
        </div>
        <div class="meta">
            <div>Date : {{ now()->format('d/m/Y') }}</div>
            <div>Référence : {{ strtoupper(substr(md5($nom_candidat ?? ''), 0, 8)) }}</div>
        </div>
    </div>

    <div class="title">Informations sur le candidat</div>

    <div class="section box">
        <div class="field"><label>Nom du candidat :</label><div class="value">{{ $nom_candidat ?? '' }}</div></div>
        <div class="field"><label>En cas de groupement :</label><div class="value">{{ $groupement_membres ?: 'Néant' }}</div></div>
        <div class="field"><label>Pays d'enregistrement :</label><div class="value">{{ $pays_candidat ?? '' }}</div></div>
        <div class="field"><label>Numéro d'identification :</label><div class="value">{{ $identification_nationale ?: '-' }}</div></div>
        <div class="field"><label>Année d'enregistrement :</label><div class="value">{{ $annee_enregistrement ?: '-' }}</div></div>
        <div class="field"><label>Adresse officielle :</label><div class="value">{{ $adresse_officielle ?: '-' }}</div></div>
    </div>

    <div class="section">
        <h3>Représentant dûment habilité</h3>
        <div class="box">
            <div class="field"><label>Nom :</label><div class="value">{{ $nom_representant ?? '' }}</div></div>
            <div class="field"><label>Fonction :</label><div class="value">{{ $fonction_representant ?: '-' }}</div></div>
            <div class="field"><label>Adresse :</label><div class="value">{{ $adresse_representant ?: '-' }}</div></div>
            <div class="field"><label>Téléphone :</label><div class="value">{{ $telephone_representant ?: '-' }}</div></div>
            <div class="field"><label>Email :</label><div class="value">{{ $email_representant ?: '-' }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="field"><label>Déclare :</label><div class="value">Je certifie que les informations ci-dessus sont exactes et qu'elles correspondent aux pièces justificatives fournies.</div></div>
    </div>

    <div class="signature">
        <div class="block box">
            <div style="font-weight:700; margin-bottom:10px;">Nom et signature du représentant</div>
            <div style="min-height:60px;"></div>
        </div>
        <div class="block box">
            <div style="font-weight:700; margin-bottom:10px;">Cachet de l'entreprise</div>
            <div style="min-height:60px;"></div>
        </div>
    </div>

    <div class="footer-note">Ce formulaire est inspiré du formulaire de renseignements sur le candidat. Les informations fournies sont destinées à l'autorité contractante.</div>
</body>
</html>
