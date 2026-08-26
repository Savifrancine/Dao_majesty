<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 15mm 20mm; }
        body { font-family: 'Times New Roman', Times, serif; color: #111; }

        .label-box {
            border: 2px solid #000;
            padding: 40px 50px;
            text-align: center;
        }

        .attention-titre {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .attention-info {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .institution-nom {
            font-size: 26px;
            font-weight: 700;
            margin-top: 26px;
            margin-bottom: 16px;
        }

        .secretariat-adresse {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 22px;
            line-height: 1.4;
        }

        .reference-ligne {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 22px;
        }

        .objet-ligne {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 22px;
            line-height: 1.4;
        }

        .lot-ligne {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 26px;
        }

        .avertissement {
            font-size: 26px;
            font-weight: 700;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="label-box">
        <div class="attention-titre">ATTENTION: {{ strtoupper($dossier->prmp_titre ?? 'PERSONNE RESPONSABLE DES MARCHES PUBLICS') }}</div>

        @if(!empty($dossier->prmp_nom))
            <div class="attention-info">{{ strtoupper($dossier->prmp_nom) }}.</div>
        @endif
        @if(!empty($dossier->prmp_telephone))
            <div class="attention-info">ADRESSE : TEL : {{ $dossier->prmp_telephone }}</div>
        @endif
        @if(!empty($dossier->prmp_email))
            <div class="attention-info">BOITE POSTALE : {{ strtoupper($dossier->prmp_email) }}</div>
        @endif

        @if(!empty($dossier->institution_nom ?: $dossier->destinataires))
            <div class="institution-nom">{{ strtoupper($dossier->institution_nom ?: $dossier->destinataires) }}</div>
        @endif

        @if(!empty($dossier->secretariat_adresse))
            <div class="secretariat-adresse">{{ strtoupper($dossier->secretariat_adresse) }}</div>
        @endif

        @if(!empty($dossier->ref))
            <div class="reference-ligne">{{ $dossier->ref }};</div>
        @endif

        @if(!empty($dossier->nom_dossier))
            <div class="objet-ligne">DRP &quot;{{ $dossier->nom_dossier }}&quot;</div>
        @endif

        @if(!empty($dossier->lots))
            <div class="lot-ligne">{{ $dossier->lots }}{{ !empty($dossier->titre_lot) ? ' : ' . $dossier->titre_lot : '' }}</div>
        @endif

        <div class="avertissement">&laquo;&laquo; A N'OUVRIR QU'EN SEANCE &raquo;&raquo;</div>
    </div>
</body>
</html>
