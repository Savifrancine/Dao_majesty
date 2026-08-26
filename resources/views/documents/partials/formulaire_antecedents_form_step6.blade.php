<div id="formulaire-antecedents-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $stored = [];
        if ($doc && !empty($doc->content)) {
            $decoded = json_decode($doc->content, true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $marches = old('marches_non_executes', $stored['marches_non_executes'] ?? []);
        $litiges = old('litiges_en_instance_rows', $stored['litiges_en_instance_rows'] ?? []);
        $marche_non_execute_since_year = old('marche_non_execute_since_year', $stored['marche_non_execute_since_year'] ?? '');
        $marches_non_execute_since_year = old('marches_non_execute_since_year', $stored['marches_non_execute_since_year'] ?? '');
        $litiges_since_year = old('litiges_since_year', $stored['litiges_since_year'] ?? '');
        $antecedents_litiges = old('antecedents_litiges', $stored['antecedents_litiges'] ?? $stored['litiges_en_instance'] ?? '');
        $autres_details = old('autres_details', $stored['autres_details'] ?? '');

        if (count($marches) === 0) {
            $marches[] = ['annee' => '', 'fraction' => '', 'identification' => '', 'montant_fcfa' => ''];
        }
        if (count($litiges) === 0) {
            $litiges[] = ['annee' => '', 'montant_reclamation' => '', 'identification_marche' => '', 'montant_total' => ''];
        }
    @endphp

    <div style="overflow-x:auto; margin-bottom:24px;">
        <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr>
                    <th colspan="4" style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:left;">Marchés non exécutés selon les dispositions de la sous-section C, Critères d'évaluation et de qualification</th>
                </tr>
                <tr>
                    <td colspan="4" style="border:1px solid #000; padding:8px;">Il n’y a pas eu de marché non exécuté depuis le 1er janvier <input type="text" class="form-control wizard-input" name="marche_non_execute_since_year" value="{{ $marche_non_execute_since_year }}" placeholder="Année" style="display:inline-block; width:auto; margin-left:8px;"></td>
                </tr>
                <tr>
                    <td colspan="4" style="border:1px solid #000; padding:8px;">Marché(s) non exécuté(s) depuis le 1er janvier <input type="text" class="form-control wizard-input" name="marches_non_execute_since_year" value="{{ $marches_non_execute_since_year }}" placeholder="Année" style="display:inline-block; width:auto; margin-left:8px;"></td>
                </tr>
                <tr>
                    <th style="border:1px solid #000; padding:8px; width:12%;">Année</th>
                    <th style="border:1px solid #000; padding:8px; width:18%;">Fraction non exécutée du contrat</th>
                    <th style="border:1px solid #000; padding:8px; width:40%;">Identification du contrat</th>
                    <th style="border:1px solid #000; padding:8px; width:25%;">Montant total du contrat (FCFA)</th>
                    <th style="border:1px solid #000; padding:8px; width:5%;">Action</th>
                </tr>
            </thead>
            <tbody class="antecedents-marches-body" data-name-prefix="marches_non_executes" data-template-id="marches-row-template">
                @foreach($marches as $index => $row)
                    <tr>
                        <td style="border:1px solid #000; padding:8px;"><input data-field="annee" type="text" class="form-control wizard-input" name="marches_non_executes[{{ $index }}][annee]" value="{{ $row['annee'] ?? '' }}"></td>
                        <td style="border:1px solid #000; padding:8px;"><input data-field="fraction" type="text" class="form-control wizard-input" name="marches_non_executes[{{ $index }}][fraction]" value="{{ $row['fraction'] ?? '' }}"></td>
                        <td style="border:1px solid #000; padding:8px;"><textarea data-field="identification" class="form-control wizard-input" name="marches_non_executes[{{ $index }}][identification]" rows="2">{{ $row['identification'] ?? '' }}</textarea></td>
                        <td style="border:1px solid #000; padding:8px;"><input data-field="montant_fcfa" type="text" class="form-control wizard-input" name="marches_non_executes[{{ $index }}][montant_fcfa]" value="{{ $row['montant_fcfa'] ?? '' }}"></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><button type="button" class="btn btn-sm btn-danger remove-antecedent-row">X</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-bottom:16px;">
            <button type="button" class="btn btn-secondary add-antecedent-marche-line">Ajouter une ligne au tableau des marchés</button>
        </div>

        <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr>
                    <th colspan="4" style="border:1px solid #000; padding:8px; background:#f3f4f6; text-align:left;">Litiges en instance, en vertu de la sous-section C, Critères d'évaluation et de qualification</th>
                </tr>
                <tr>
                    <td colspan="4" style="border:1px solid #000; padding:8px;">
                        Pas de litige en instance <span style="margin-left:12px;">Litige(s) en : <input type="text" class="form-control wizard-input" name="litiges_since_year" value="{{ $litiges_since_year }}" placeholder="Année du litige" style="display:inline-block; width:auto;"></span>
                    </td>
                </tr>
                <tr>
                    <th style="border:1px solid #000; padding:8px; width:15%;">Année du litige</th>
                    <th style="border:1px solid #000; padding:8px; width:25%;">Montant de la réclamation (monnaie)</th>
                    <th style="border:1px solid #000; padding:8px; width:33%;">Identification du marché</th>
                    <th style="border:1px solid #000; padding:8px; width:22%;">Montant total du marché (monnaie, équivalent en FCFA)</th>
                    <th style="border:1px solid #000; padding:8px; width:5%;">Action</th>
                </tr>
            </thead>
            <tbody class="antecedents-litiges-body" data-name-prefix="litiges_en_instance_rows" data-template-id="litiges-row-template">
                @foreach($litiges as $index => $row)
                    <tr>
                        <td style="border:1px solid #000; padding:8px;"><input data-field="annee" type="text" class="form-control wizard-input" name="litiges_en_instance_rows[{{ $index }}][annee]" value="{{ $row['annee'] ?? '' }}"></td>
                        <td style="border:1px solid #000; padding:8px;"><input data-field="montant_reclamation" type="text" class="form-control wizard-input" name="litiges_en_instance_rows[{{ $index }}][montant_reclamation]" value="{{ $row['montant_reclamation'] ?? '' }}"></td>
                        <td style="border:1px solid #000; padding:8px;"><textarea data-field="identification_marche" class="form-control wizard-input" name="litiges_en_instance_rows[{{ $index }}][identification_marche]" rows="2">{{ $row['identification_marche'] ?? '' }}</textarea></td>
                        <td style="border:1px solid #000; padding:8px;"><input data-field="montant_total" type="text" class="form-control wizard-input" name="litiges_en_instance_rows[{{ $index }}][montant_total]" value="{{ $row['montant_total'] ?? '' }}"></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><button type="button" class="btn btn-sm btn-danger remove-antecedent-row">X</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-bottom:16px;">
            <button type="button" class="btn btn-secondary add-antecedent-litige-line">Ajouter une ligne au tableau des litiges</button>
        </div>
    </div>

    <template id="marches-row-template">
        <tr>
            <td style="border:1px solid #000; padding:8px;"><input data-field="annee" type="text" class="form-control wizard-input"></td>
            <td style="border:1px solid #000; padding:8px;"><input data-field="fraction" type="text" class="form-control wizard-input"></td>
            <td style="border:1px solid #000; padding:8px;"><textarea data-field="identification" class="form-control wizard-input" rows="2"></textarea></td>
            <td style="border:1px solid #000; padding:8px;"><input data-field="montant_fcfa" type="text" class="form-control wizard-input"></td>
            <td style="border:1px solid #000; padding:8px; text-align:center;"><button type="button" class="btn btn-sm btn-danger remove-antecedent-row">X</button></td>
        </tr>
    </template>

    <template id="litiges-row-template">
        <tr>
            <td style="border:1px solid #000; padding:8px;"><input data-field="annee" type="text" class="form-control wizard-input"></td>
            <td style="border:1px solid #000; padding:8px;"><input data-field="montant_reclamation" type="text" class="form-control wizard-input"></td>
            <td style="border:1px solid #000; padding:8px;"><textarea data-field="identification_marche" class="form-control wizard-input" rows="2"></textarea></td>
            <td style="border:1px solid #000; padding:8px;"><input data-field="montant_total" type="text" class="form-control wizard-input"></td>
            <td style="border:1px solid #000; padding:8px; text-align:center;"><button type="button" class="btn btn-sm btn-danger remove-antecedent-row">X</button></td>
        </tr>
    </template>

    <script>
        (function () {
            function updateRowNames(tableBody) {
                var prefix = tableBody.dataset.namePrefix;
                Array.from(tableBody.querySelectorAll('tr')).forEach(function (tr, rowIndex) {
                    Array.from(tr.querySelectorAll('[data-field]')).forEach(function (input) {
                        var field = input.dataset.field;
                        input.name = prefix + '[' + rowIndex + '][' + field + ']';
                    });
                });
            }

            function addRow(tableBody) {
                var template = document.getElementById(tableBody.dataset.templateId);
                if (!template) {
                    return;
                }
                var clone = template.content.firstElementChild.cloneNode(true);
                tableBody.appendChild(clone);
                updateRowNames(tableBody);
                attachRemoveButtons(tableBody);
            }

            function attachRemoveButtons(tableBody) {
                Array.from(tableBody.querySelectorAll('.remove-antecedent-row')).forEach(function (button) {
                    if (button._attached) {
                        return;
                    }
                    button.addEventListener('click', function () {
                        var row = button.closest('tr');
                        if (row) {
                            row.remove();
                            updateRowNames(tableBody);
                        }
                    });
                    button._attached = true;
                });
            }

            var marchesBody = document.querySelector('.antecedents-marches-body');
            var litigesBody = document.querySelector('.antecedents-litiges-body');

            if (marchesBody) {
                updateRowNames(marchesBody);
                attachRemoveButtons(marchesBody);
                var addMarche = document.querySelector('.add-antecedent-marche-line');
                if (addMarche) {
                    addMarche.addEventListener('click', function () {
                        addRow(marchesBody);
                    });
                }
            }

            if (litigesBody) {
                updateRowNames(litigesBody);
                attachRemoveButtons(litigesBody);
                var addLitige = document.querySelector('.add-antecedent-litige-line');
                if (addLitige) {
                    addLitige.addEventListener('click', function () {
                        addRow(litigesBody);
                    });
                }
            }
        })();
    </script>

    <div class="wizard-field" style="margin-bottom:18px;">
        <label for="antecedents_litiges" class="wizard-label">Antécédents de litiges</label>
        <textarea class="form-control wizard-input" id="antecedents_litiges" name="antecedents_litiges" rows="4">{{ $antecedents_litiges }}</textarea>
    </div>

    <div class="wizard-field" style="margin-bottom:18px;">
        <label for="autres_details" class="wizard-label">Commentaires complémentaires</label>
        <textarea class="form-control wizard-input" id="autres_details" name="autres_details" rows="3">{{ $autres_details }}</textarea>
    </div>
</div>
