@php
    $formattedDate = !empty($date) ? (new \Carbon\Carbon($date))->format('d/m/Y') : now()->format('d/m/Y');

    $objetMarche = trim(optional($dossier ?? null)->nom_dossier ?? '');
    $objetMarcheDisplay = $objetMarche !== '' ? ucfirst(mb_strtolower($objetMarche, 'UTF-8')) : '[insérer l’objet du marché]';

    $destinataireText = trim($destinataire ?? '');

    $addendaText = trim($point_a ?? '') !== '' ? $point_a : '[Neant]';
    $delaiText = trim($point_b ?? '') !== '' ? $point_b : 'Deux (02) mois';

    $montantHtLettres = trim($montant_ht_lettres ?? '');
    $montantHtChiffres = trim($montant_ht_total ?? '');
    $montantTtcLettres = trim($montant_lettres ?? '');
    $montantTtcChiffres = trim($montant_chiffres ?? '');
    $tvaChiffres = trim($tva_valeur ?? '');
    $tvaNumeric = (float) str_replace([' ', ' '], '', $tvaChiffres);
    $tvaLettres = $tvaChiffres !== '' ? ucfirst(\App\Support\NumberToWords::french($tvaNumeric)) : '';

    $rabaisText = trim($rabais ?? '') !== '' ? $rabais : "Rabais : Les rabais ci-après sont accordés comme suit : [NEANT] ;\n\nModalités d’application des rabais : [NEANT] ;";
@endphp

<div style="margin-top:6px;">
    <div style="text-align:center; font-size:15px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:6px;">Lettre de soumission</div>
    <div style="text-align:right; font-size:11px; margin-bottom:2px;"><strong>Date :</strong> {{ $formattedDate }}</div>
    <div style="text-align:right; font-size:11px; margin-bottom:8px;"><strong>Variante N° :</strong> {{ trim($variante ?? '') }}</div>

    <div style="font-size:12px; margin-bottom:10px;">Avis de demande de renseignements et de prix numero: {{ $objetMarcheDisplay }}</div>

    @if($destinataireText)
        <div style="margin-bottom:10px; font-size:12px;"><strong>A :</strong> {{ $destinataireText }}</div>
    @endif

    <div style="margin-bottom:6px; font-size:12px;">Monsieur et /ou Madame,</div>

    <div style="margin-bottom:16px; font-size:12px;">
        <div>Nous, les soussignés, attestons que :</div>
    </div>

    <div style="font-size:12px; line-height:1.6; text-align:justify;">
        <div style="margin-bottom:10px;"><strong>a)</strong> Nous avons examiné le Dossier de demande de renseignements et de prix, y compris l’addendum/les addenda numéro : {!! nl2br(e($addendaText)) !!} ; et n’avons aucune réserve à leur égard ;</div>

        <div style="margin-bottom:10px;"><strong>b)</strong> Nous nous engageons à fournir ou exécuter conformément au Dossier de demande de renseignements et de prix et aux spécifications techniques et plans, les Fournitures ou Services ci-après : {{ $objetMarcheDisplay }} dans le délai d’exécution de {!! nl2br(e($delaiText)) !!} ;</div>

        <div style="margin-bottom:2px;"><strong>c)</strong> Le prix total de notre offre, hors rabais offert à la clause (d) ci-après est de : <strong>{{ $montantHtLettres }}{{ $montantHtChiffres ? ' (' . $montantHtChiffres . ')' : '' }} francs CFA Hors TVA</strong> soit <strong>{{ $montantTtcLettres }}{{ $montantTtcChiffres ? ' (' . $montantTtcChiffres . ')' : '' }} francs CFA toutes taxes comprises</strong>.</div>
        @if($tvaChiffres !== '')
            <div style="margin-bottom:10px;">La valeur de la TVA est alors de {{ ucfirst($tvaLettres) }} ({{ $tvaChiffres }}) francs CFA.</div>
        @endif

        <div style="margin-bottom:10px;"><strong>d)</strong> {!! nl2br(e($rabaisText)) !!}</div>

        <div style="margin-bottom:10px;"><strong>e)</strong> Notre offre demeurera valide pendant la période requise à la clause 18.1 des Données particulières de la demande de renseignements et de prix à compter de la date limite fixée pour la remise des offres à la clause 21.1 des Instructions aux candidats ; cette offre continuera de nous engager et pourra être acceptée à tout moment avant l’expiration de cette période ;</div>

        <div style="margin-bottom:10px;"><strong>f)</strong> Si notre offre est acceptée, nous nous engageons à fournir une garantie de bonne exécution du marché conformément à la clause 38 des Instructions aux candidats ;</div>

        <div style="margin-bottom:10px;"><strong>g)</strong> Notre candidature, ainsi que tous sous-traitants ou fournisseurs/prestataires de services intervenant en rapport avec une quelconque partie du marché, ne tombent pas sous les conditions d’exclusion des clauses 3.2 et 4.3 des Instructions aux candidats.</div>

        <div style="margin-bottom:10px;"><strong>h)</strong> Nous ne nous trouvons pas dans une situation de conflit d’intérêt définie à la clause 4.2 des Instructions aux candidats.</div>

        <div style="margin-bottom:10px;"><strong>i)</strong> Nous ne participons pas, en qualité de candidats ou sous-traitant, à plus d’une offre dans le cadre de la présente demande de renseignements et de prix conformément à la clause 4.4 des Instructions aux candidats, autre que des offres variantes présentées conformément à la clause 11 des Instructions aux candidats ;</div>

        <div style="margin-bottom:10px;"><strong>j)</strong> Nous nous engageons à ne pas octroyer ou promettre d’octroyer à toute personne intervenant à quelque titre que ce soit dans la procédure de passation du marché un avantage indu, pécuniaire ou autre, directement ou par des intermédiaires, en vue d’obtenir le marché, et en général à respecter les dispositions relatives à la lutte contre la corruption, les conflits d’intérêt, la répression de l’enrichissement illicite, l’éthique professionnelle et tous autres actes similaires, prévus au Code d’éthique et de déontologie dans la commande publique comme en atteste le formulaire d’engagement ci-joint, signé par nous.</div>

        <div style="margin-bottom:10px;"><strong>k)</strong> Il est entendu que la présente offre, et votre acceptation écrite de ladite offre figurant dans la notification d’attribution du marché que vous nous adresserez, tiendra lieu de contrat entre nous, jusqu’à ce qu’un marché formel soit établi et signé.</div>

        <div style="margin-bottom:10px;"><strong>l)</strong> Il est entendu par nous que vous n’êtes pas tenus d’accepter l’offre évaluée économiquement la plus avantageuse, ni l’une quelconque des offres que vous pouvez recevoir.</div>
    </div>
</div>
