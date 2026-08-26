<div id="divulgation-beneficiaires-step6">
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

        $option = old('option', $existingValues['option'] ?? 'i');
        $defaultAnswer = $option === 'ii' ? 'Non' : 'Oui';

        $beneficiaires = old('beneficiaires', $existingValues['beneficiaires'] ?? []);
        if (empty($beneficiaires)) {
            $beneficiaires = [['identite' => '', 'action_25' => $defaultAnswer, 'vote_25' => $defaultAnswer, 'pouvoir_nomination' => $defaultAnswer]];
        }
    @endphp

    <div class="wizard-field" style="margin-bottom: 12px;">
        <p class="wizard-label">Formulaire de divulgation des bénéficiaires effectifs</p>
        <p style="font-size:0.95rem;color:#475569;">Complétez les champs ci-dessous. Le texte légal (circulaire, conditions) est fixe et s'affiche automatiquement dans le PDF.</p>
    </div>

    <div style="border:1px solid #d1d5db; padding:16px; background:#ffffff;">
        <div style="margin-bottom:16px;">
            <label class="wizard-label" for="numero_avis">Numéro de l'Avis de la demande de renseignements et de prix</label>
            <input id="numero_avis" type="text" name="numero_avis" class="form-control wizard-input" value="{{ $value('numero_avis', $dossier->reference_dossier ?? '') }}">
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label" for="destinataire">A (Autorité contractante)</label>
            <input id="destinataire" type="text" name="destinataire" class="form-control wizard-input" value="{{ $value('destinataire', $dossier->destinataires ?? '') }}">
        </div>

        <div style="margin-bottom:18px;">
            <label class="wizard-label">En réponse à l'obligation de fournir les renseignements sur les bénéficiaires effectifs</label>
            <div style="display:flex; flex-direction:column; gap:8px; margin-top:6px;">
                <label style="font-weight:400;">
                    <input type="radio" name="option" value="i" {{ $option === 'i' ? 'checked' : '' }} id="option_i">
                    (i) Nous fournissons les renseignements sur les bénéficiaires effectifs ci-après
                </label>
                <label style="font-weight:400;">
                    <input type="radio" name="option" value="ii" {{ $option === 'ii' ? 'checked' : '' }} id="option_ii">
                    (ii) Nous déclarons qu'il n'y a aucun bénéficiaire effectif remplissant l'une des conditions
                </label>
            </div>
        </div>

        <div id="beneficiairesBlock">
            <label class="wizard-label">Détails des bénéficiaires effectifs</label>
            <div style="overflow-x:auto; margin-top:8px;">
                <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr>
                            <th style="border:1px solid #ccc; padding:8px; width:34%;">Identité (nom complet, nationalité, pays de résidence)</th>
                            <th style="border:1px solid #ccc; padding:8px; width:22%;">≥ 25% des actions</th>
                            <th style="border:1px solid #ccc; padding:8px; width:22%;">≥ 25% des droits de vote</th>
                            <th style="border:1px solid #ccc; padding:8px; width:18%;">Pouvoir de nommer la majorité du conseil</th>
                            <th style="border:1px solid #ccc; padding:8px; width:4%;"></th>
                        </tr>
                    </thead>
                    <tbody id="beneficiairesLines">
                        @foreach($beneficiaires as $index => $b)
                            <tr>
                                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                    <textarea name="beneficiaires[{{ $index }}][identite]" rows="2" class="form-control wizard-input" placeholder="Nom, prénom, nationalité, pays de résidence">{{ $b['identite'] ?? '' }}</textarea>
                                </td>
                                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                    <select name="beneficiaires[{{ $index }}][action_25]" class="form-control wizard-input beneficiaire-answer">
                                        <option value="">-</option>
                                        <option value="Oui" {{ ($b['action_25'] ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                        <option value="Non" {{ ($b['action_25'] ?? '') === 'Non' ? 'selected' : '' }}>Non</option>
                                    </select>
                                </td>
                                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                    <select name="beneficiaires[{{ $index }}][vote_25]" class="form-control wizard-input beneficiaire-answer">
                                        <option value="">-</option>
                                        <option value="Oui" {{ ($b['vote_25'] ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                        <option value="Non" {{ ($b['vote_25'] ?? '') === 'Non' ? 'selected' : '' }}>Non</option>
                                    </select>
                                </td>
                                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                                    <select name="beneficiaires[{{ $index }}][pouvoir_nomination]" class="form-control wizard-input beneficiaire-answer">
                                        <option value="">-</option>
                                        <option value="Oui" {{ ($b['pouvoir_nomination'] ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                        <option value="Non" {{ ($b['pouvoir_nomination'] ?? '') === 'Non' ? 'selected' : '' }}>Non</option>
                                    </select>
                                </td>
                                <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                                    <button type="button" class="btn btn-sm btn-danger removeBeneficiaireLine" style="padding:0.25rem 0.45rem;">×</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mb-3" style="margin-top:8px;">
                <button type="button" id="addBeneficiaireLine" class="btn btn-secondary">Ajouter un bénéficiaire</button>
            </div>
        </div>

        <div class="wizard-alert" style="margin:8px 0 18px; font-size:0.85rem; color:#475569;">
            NB : à défaut de personne physique répondant à ces critères, indiquez les coordonnées de la personne physique occupant la fonction de cadre dirigeant.
        </div>

        <div class="wizard-alert" style="margin-top:8px; font-size:0.85rem; color:#475569;">
            Le nom du soumissionnaire, le signataire, son titre et la date de signature sont repris automatiquement du bloc de signature en fin de document.
        </div>
    </div>

    <script>
        (function () {
            const optionRadios = document.querySelectorAll('input[name="option"]');
            const linesBody = document.getElementById('beneficiairesLines');
            const addButton = document.getElementById('addBeneficiaireLine');

            function currentDefaultAnswer() {
                const selected = document.querySelector('input[name="option"]:checked');
                return (selected && selected.value === 'ii') ? 'Non' : 'Oui';
            }

            // Cocher (i) ou (ii) préremplit automatiquement les 3 colonnes Oui/Non
            // de toutes les lignes déjà saisies (Oui pour (i), Non pour (ii)).
            function applyDefaultAnswers() {
                const answer = currentDefaultAnswer();
                linesBody.querySelectorAll('select.beneficiaire-answer').forEach((select) => {
                    select.value = answer;
                });
            }

            optionRadios.forEach((radio) => radio.addEventListener('change', applyDefaultAnswers));

            function updateIndexes() {
                Array.from(linesBody.querySelectorAll('tr')).forEach((row, index) => {
                    row.querySelectorAll('[name*="beneficiaires["]').forEach((field) => {
                        field.name = field.name.replace(/beneficiaires\[\d+\]/, `beneficiaires[${index}]`);
                    });
                });
            }

            function createRow() {
                const answer = currentDefaultAnswer();
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <textarea name="beneficiaires[0][identite]" rows="2" class="form-control wizard-input" placeholder="Nom, prénom, nationalité, pays de résidence"></textarea>
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <select name="beneficiaires[0][action_25]" class="form-control wizard-input beneficiaire-answer">
                            <option value="">-</option>
                            <option value="Oui" ${answer === 'Oui' ? 'selected' : ''}>Oui</option>
                            <option value="Non" ${answer === 'Non' ? 'selected' : ''}>Non</option>
                        </select>
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <select name="beneficiaires[0][vote_25]" class="form-control wizard-input beneficiaire-answer">
                            <option value="">-</option>
                            <option value="Oui" ${answer === 'Oui' ? 'selected' : ''}>Oui</option>
                            <option value="Non" ${answer === 'Non' ? 'selected' : ''}>Non</option>
                        </select>
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                        <select name="beneficiaires[0][pouvoir_nomination]" class="form-control wizard-input beneficiaire-answer">
                            <option value="">-</option>
                            <option value="Oui" ${answer === 'Oui' ? 'selected' : ''}>Oui</option>
                            <option value="Non" ${answer === 'Non' ? 'selected' : ''}>Non</option>
                        </select>
                    </td>
                    <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                        <button type="button" class="btn btn-sm btn-danger removeBeneficiaireLine" style="padding:0.25rem 0.45rem;">×</button>
                    </td>
                `;
                return row;
            }

            addButton.addEventListener('click', function () {
                linesBody.appendChild(createRow());
                updateIndexes();
            });

            linesBody.addEventListener('click', function (event) {
                if (event.target.classList.contains('removeBeneficiaireLine')) {
                    const row = event.target.closest('tr');
                    row.remove();
                    if (!linesBody.querySelector('tr')) {
                        linesBody.appendChild(createRow());
                    }
                    updateIndexes();
                }
            });
        })();
    </script>
</div>
