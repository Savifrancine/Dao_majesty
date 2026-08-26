<div id="lettre-soumission-step6">
    @php
        $existingValues = [];
        if (!empty($dossierDocument?->content)) {
            $decoded = json_decode($dossierDocument->content, true);
            if (is_array($decoded)) {
                $existingValues = $decoded;
            }
        }

        $value = function ($key, $default = '') use ($existingValues) {
            return old($key, $existingValues[$key] ?? $default);
        };

        $tvaRate = 0.18;

        $documents = $dossier->documents ?? collect();

        $sumMontantForNames = function ($names) use ($documents) {
            return $documents
                ->filter(fn($doc) => in_array(optional($doc->typeDocument)->nom, $names, true))
                ->sum(fn($doc) => $doc->bordereau->sum(fn($bordereau) => $bordereau->lignes->sum(fn($ligne) => (float) ($ligne->montant ?? 0))));
        };

        // Le prix total de l'offre correspond au montant des fournitures livrées
        // (Bordereau des prix pour les fournitures à importer), pas à la somme de
        // tous les bordereaux du dossier : additionner en plus le Bordereau prix
        // unitaire (qui chiffre l'entretien/la maintenance, une prestation distincte)
        // ou les "sous détails" (qui ne font que détailler des prix déjà comptés
        // ailleurs) double-compterait des montants et gonflerait le total soumis.
        // On ne se rabat sur les autres bordereaux "fournitures" que si le dossier
        // n'a pas de Bordereau des prix pour les fournitures à importer chiffré.
        $calendrierHT = $sumMontantForNames(['Bordereau des prix pour les fournitures à importer']);
        if ($calendrierHT <= 0) {
            $calendrierHT = $sumMontantForNames([
                'Bordereau prix unitaire',
                'Listes des Fournitures et Calendrier de livraison',
                'Cadres de sous détails des prix unitaire',
            ]);
        }
        $servicesConnexesDocNames = [
            "Bordereau des prix et calendrier d'exécution des services connexes",
            'Listes des services connexes et calendrier de réalisation',
        ];
        $calendrierTVA = round($calendrierHT * $tvaRate);

        $servicesHT = $sumMontantForNames($servicesConnexesDocNames);
        $servicesTVA = round($servicesHT * $tvaRate);

        $calendrierTTC = round($calendrierHT + $calendrierTVA);
        $servicesTTC = round($servicesHT + $servicesTVA);
        $totalHT = $calendrierHT + $servicesHT;
        $totalTTC = $calendrierTTC + $servicesTTC;
        $totalTVA = $calendrierTVA + $servicesTVA;

        $formatMoney = fn($amount) => $amount !== null ? number_format((float) $amount, 0, ',', ' ') : '';

        $formatLetters = function ($amount) {
            if ($amount === null || $amount === '' || floatval($amount) == 0) {
                return '';
            }
            return ucfirst(\App\Support\NumberToWords::french($amount));
        };

        $defaultTotalHtCalendrier = $formatMoney($calendrierHT);
        $defaultTotalTtcCalendrier = $formatMoney($calendrierTTC);
        $defaultTotalHtServices = $formatMoney($servicesHT);
        $defaultTotalTtcServices = $formatMoney($servicesTTC);
        $defaultTotalHt = $formatMoney($totalHT);
        $defaultTotalTtc = $formatMoney($totalTTC);
        $defaultTotalHtLettres = $formatLetters($totalHT);
        $defaultTotalTtcLettres = $formatLetters($totalTTC);
        $defaultTvaValue = $formatMoney($totalTVA);
    @endphp

    <div class="wizard-field" style="margin-bottom: 12px;">
        <p class="wizard-label">Lettre de soumission</p>
        <p style="font-size:0.95rem;color:#475569;">Complétez les champs ci-dessous. Les totaux HT / TTC et la TVA sont automatiquement remplis à partir des bordereaux de prix et des services connexes.</p>
    </div>

    <div style="border:1px solid #d1d5db; padding:16px; background:#ffffff;">
        <div style="margin-bottom:16px;">
            <label class="wizard-label" for="date">Date</label>
            <input id="date" type="date" name="date" class="form-control wizard-input" style="max-width:220px;" value="{{ $value('date', optional($dossier->date_soumission)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
        </div>

        <div style="margin-bottom:16px;">
            <label class="wizard-label" for="destinataire">À</label>
            <input id="destinataire" type="text" name="destinataire" class="form-control wizard-input" value="{{ $value('destinataire', $dossier->destinataires ?? '') }}">
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label" for="point_a">a) Numéro d'addenda (laisser "[Neant]" s'il n'y en a pas)</label>
            <input id="point_a" type="text" name="point_a" class="form-control wizard-input" value="{{ $value('point_a', '[Neant]') }}">
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label" for="point_b">b) Délai d'exécution des fournitures/services</label>
            <input id="point_b" type="text" name="point_b" class="form-control wizard-input" placeholder="Ex : Deux (02) mois" value="{{ $value('point_b', 'Deux (02) mois') }}">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_ht_calendrier">Montant HT – fournitures (bordereaux de prix)</label>
                <input id="montant_ht_calendrier" type="text" name="montant_ht_calendrier" class="form-control wizard-input" readonly value="{{ $value('montant_ht_calendrier', $defaultTotalHtCalendrier) }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_ttc_calendrier">Montant TTC – fournitures (bordereaux de prix)</label>
                <input id="montant_ttc_calendrier" type="text" name="montant_ttc_calendrier" class="form-control wizard-input" readonly value="{{ $value('montant_ttc_calendrier', $defaultTotalTtcCalendrier) }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_ht_services">Montant HT – services connexes</label>
                <input id="montant_ht_services" type="text" name="montant_ht_services" class="form-control wizard-input" readonly value="{{ $value('montant_ht_services', $defaultTotalHtServices) }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_ttc_services">Montant TTC – services connexes</label>
                <input id="montant_ttc_services" type="text" name="montant_ttc_services" class="form-control wizard-input" readonly value="{{ $value('montant_ttc_services', $defaultTotalTtcServices) }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_ht_total">Total HT (bordereau + services)</label>
                <input id="montant_ht_total" type="text" name="montant_ht_total" class="form-control wizard-input" readonly value="{{ $value('montant_ht_total', $defaultTotalHt) }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_ht_lettres">Total HT en lettres</label>
                <input id="montant_ht_lettres" type="text" name="montant_ht_lettres" class="form-control wizard-input" readonly value="{{ $value('montant_ht_lettres', $defaultTotalHtLettres) }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_chiffres">Montant total TTC en chiffres</label>
                <input id="montant_chiffres" type="text" name="montant_chiffres" class="form-control wizard-input" readonly value="{{ $value('montant_chiffres', $defaultTotalTtc) }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_lettres">Montant total TTC en lettres</label>
                <input id="montant_lettres" type="text" name="montant_lettres" class="form-control wizard-input" readonly value="{{ $value('montant_lettres', $defaultTotalTtcLettres) }}">
            </div>
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label" for="tva_valeur">TVA (valeur)</label>
            <input id="tva_valeur" type="text" name="tva_valeur" class="form-control wizard-input" readonly value="{{ $value('tva_valeur', $defaultTvaValue) }}">
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label" for="rabais">d) Rabais et modalités</label>
            <textarea id="rabais" name="rabais" rows="4" class="form-control wizard-input">{{ $value('rabais', "Rabais : Les rabais ci-après sont accordés comme suit : [NEANT] ;\n\nModalités d’application des rabais : [NEANT] ;") }}</textarea>
        </div>

        <div class="wizard-alert" style="margin-top:8px; font-size:0.85rem; color:#475569;">
            Les clauses e) à l) (validité de l'offre, garantie d'exécution, absence de conflit d'intérêt, engagement anti-corruption...) sont un texte légal fixe, identique pour tous les dossiers, et s'affichent automatiquement dans le PDF.
        </div>
    </div>
</div>
