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

        // Totaux HTVA fournitures/services connexes : meme source que le
        // "Tableau de resume des bordereaux de prix" (voir
        // App\Support\BordereauTotals), pour que les deux ne se desynchronisent
        // jamais. Exclut volontairement les documents dont la quantite est
        // toujours figee a 1 (Bordereau prix unitaire, Cadres de sous details) :
        // ils ne font pas de vrai calcul prix x quantite.
        $calendrierHT = \App\Support\BordereauTotals::fournituresHT($dossier);
        $calendrierTVA = round($calendrierHT * $tvaRate);

        $servicesHT = \App\Support\BordereauTotals::servicesConnexesHT($dossier);
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

        // Le delai d'execution (point b) doit correspondre a la "Date de
        // livraison" deja saisie dans les bordereaux de prix du dossier,
        // jamais a un texte fige sans rapport avec le dossier (voir
        // App\Support\BordereauDelai).
        $defaultDelai = \App\Support\BordereauDelai::find($dossier) ?: 'Deux (02) mois';
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
            <input id="point_b" type="text" name="point_b" class="form-control wizard-input" readonly value="{{ $value('point_b', $defaultDelai) }}">
            <small style="color:#64748b;">Repris automatiquement de la « Date de livraison » saisie dans les bordereaux de prix ; se met à jour tout seul si elle change là-bas.</small>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_ht_calendrier">Montant HT – fournitures (bordereaux de prix)</label>
                <input id="montant_ht_calendrier" type="text" name="montant_ht_calendrier" class="form-control wizard-input" readonly value="{{ $defaultTotalHtCalendrier }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_ttc_calendrier">Montant TTC – fournitures (bordereaux de prix)</label>
                <input id="montant_ttc_calendrier" type="text" name="montant_ttc_calendrier" class="form-control wizard-input" readonly value="{{ $defaultTotalTtcCalendrier }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_ht_services">Montant HT – services connexes</label>
                <input id="montant_ht_services" type="text" name="montant_ht_services" class="form-control wizard-input" readonly value="{{ $defaultTotalHtServices }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_ttc_services">Montant TTC – services connexes</label>
                <input id="montant_ttc_services" type="text" name="montant_ttc_services" class="form-control wizard-input" readonly value="{{ $defaultTotalTtcServices }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_ht_total">Total HT (bordereau + services)</label>
                <input id="montant_ht_total" type="text" name="montant_ht_total" class="form-control wizard-input" readonly value="{{ $defaultTotalHt }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_ht_lettres">Total HT en lettres</label>
                <input id="montant_ht_lettres" type="text" name="montant_ht_lettres" class="form-control wizard-input" readonly value="{{ $defaultTotalHtLettres }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label class="wizard-label" for="montant_chiffres">Montant total TTC en chiffres</label>
                <input id="montant_chiffres" type="text" name="montant_chiffres" class="form-control wizard-input" readonly value="{{ $defaultTotalTtc }}">
            </div>
            <div>
                <label class="wizard-label" for="montant_lettres">Montant total TTC en lettres</label>
                <input id="montant_lettres" type="text" name="montant_lettres" class="form-control wizard-input" readonly value="{{ $defaultTotalTtcLettres }}">
            </div>
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label" for="tva_valeur">TVA (valeur)</label>
            <input id="tva_valeur" type="text" name="tva_valeur" class="form-control wizard-input" readonly value="{{ $defaultTvaValue }}">
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
