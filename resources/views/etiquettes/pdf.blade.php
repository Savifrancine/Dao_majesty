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

        .destinataire-a {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .destinataire-nom {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .destinataire-adresse {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .procedure-titre {
            font-size: 22px;
            font-weight: 700;
            margin-top: 24px;
            margin-bottom: 8px;
        }

        .reference-ligne {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .objet-ligne {
            font-size: 22px;
            font-weight: 700;
            margin-top: 24px;
            margin-bottom: 30px;
            line-height: 1.4;
        }

        .entreprise-block {
            text-align: left;
            margin-top: 30px;
        }

        .entreprise-info {
            font-size: 19px;
            font-weight: 700;
            font-style: italic;
            margin-bottom: 4px;
        }

        .variante-titre {
            font-size: 42px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #c0392b;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $entreprise = $dossier->entreprise;
        $dateLancement = $dossier->date_lancement ? \Illuminate\Support\Carbon::parse($dossier->date_lancement)->format('d/m/Y') : null;
        $etiquetteA = $dossier->etiquette_a === null ? 'À' : $dossier->etiquette_a;
    @endphp
    <div class="label-box">
        @if($etiquetteA !== '')
            <div class="destinataire-a">{{ $etiquetteA }}</div>
        @endif
        <div class="destinataire-nom">{{ strtoupper($dossier->destinataires ?? '') }}</div>
        @if(!empty($dossier->destinataire_adresse))
            <div class="destinataire-adresse">{{ $dossier->destinataire_adresse }}</div>
        @endif

        <div class="procedure-titre">{{ strtoupper($dossier->titre_dossier ?? '') }}</div>
        <div class="reference-ligne">N° {{ $dossier->reference_dossier ?? '-' }}{{ $dateLancement ? ' DU ' . mb_strtoupper($dateLancement) : '' }}</div>

        <div class="objet-ligne">POUR {{ strtoupper($dossier->nom_dossier ?? '') }}</div>

        <div class="entreprise-block">
            <div class="entreprise-info">{{ strtoupper(optional($entreprise)->nom ?? 'MAJESTY SERVICES ET EQUIPEMENTS') }}</div>
            <div class="entreprise-info">C/763 KOWEGBO</div>
            <div class="entreprise-info">TEL : 0197772504</div>
            <div class="entreprise-info">Email : majestyse@gmail.com</div>
        </div>

        <div class="variante-titre">{{ $variante === 'original' ? 'Original' : 'Copie' }}</div>
    </div>
</body>
</html>
