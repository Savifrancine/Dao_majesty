@php
    $stored = $beneficiairesData ?? [];
    $beneficiairesOption = old('beneficiaires_option', $stored['option'] ?? 'i');
    $beneficiairesList = old('beneficiaires_effectifs', $stored['beneficiaires'] ?? []);
    if (empty($beneficiairesList)) {
        $beneficiairesList = [['identite' => '', 'action_25' => '', 'vote_25' => '', 'pouvoir_nomination' => '']];
    }
@endphp

<h5 class="mt-4 mb-3">Bénéficiaires effectifs</h5>
<p class="text-muted" style="font-size:13px;">L'actionnariat/le contrôle de l'entreprise ne change pas d'un dossier à l'autre : renseignés ici une fois, ces éléments sont repris automatiquement dans le « Formulaire de divulgation des bénéficiaires effectifs » de chaque nouveau dossier.</p>

<div id="beneficiairesEffectifsSection" style="border:1px solid #d1d5db; padding:16px; background:#fff; margin-bottom:12px;">
    <div style="margin-bottom:14px;">
        <label class="form-label d-block">En réponse à l'obligation de fournir les renseignements sur les bénéficiaires effectifs</label>
        <div style="display:flex; flex-direction:column; gap:6px; margin-top:4px;">
            <label style="font-weight:400;">
                <input type="radio" name="beneficiaires_option" value="i" {{ $beneficiairesOption === 'i' ? 'checked' : '' }}>
                (i) Nous fournissons les renseignements sur les bénéficiaires effectifs ci-après
            </label>
            <label style="font-weight:400;">
                <input type="radio" name="beneficiaires_option" value="ii" {{ $beneficiairesOption === 'ii' ? 'checked' : '' }}>
                (ii) Nous déclarons qu'il n'y a aucun bénéficiaire effectif remplissant l'une des conditions
            </label>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="table" id="beneficiairesEffectifsTable" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr>
                    <th style="border:1px solid #ccc; padding:8px; width:34%; background:#f3f4f6;">Identité (nom complet, nationalité, pays de résidence)</th>
                    <th style="border:1px solid #ccc; padding:8px; width:22%; background:#f3f4f6;">≥ 25% des actions</th>
                    <th style="border:1px solid #ccc; padding:8px; width:22%; background:#f3f4f6;">≥ 25% des droits de vote</th>
                    <th style="border:1px solid #ccc; padding:8px; width:18%; background:#f3f4f6;">Pouvoir de nommer la majorité du conseil</th>
                    <th style="border:1px solid #ccc; padding:8px; width:4%; background:#f3f4f6;"></th>
                </tr>
            </thead>
            <tbody id="beneficiairesEffectifsLines">
                @foreach($beneficiairesList as $index => $b)
                    <tr>
                        <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                            <textarea name="beneficiaires_effectifs[{{ $index }}][identite]" rows="2" class="form-control" placeholder="Nom, prénom, nationalité, pays de résidence">{{ $b['identite'] ?? '' }}</textarea>
                        </td>
                        <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                            <select name="beneficiaires_effectifs[{{ $index }}][action_25]" class="form-control">
                                <option value="">-</option>
                                <option value="Oui" {{ ($b['action_25'] ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                <option value="Non" {{ ($b['action_25'] ?? '') === 'Non' ? 'selected' : '' }}>Non</option>
                            </select>
                        </td>
                        <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                            <select name="beneficiaires_effectifs[{{ $index }}][vote_25]" class="form-control">
                                <option value="">-</option>
                                <option value="Oui" {{ ($b['vote_25'] ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                <option value="Non" {{ ($b['vote_25'] ?? '') === 'Non' ? 'selected' : '' }}>Non</option>
                            </select>
                        </td>
                        <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                            <select name="beneficiaires_effectifs[{{ $index }}][pouvoir_nomination]" class="form-control">
                                <option value="">-</option>
                                <option value="Oui" {{ ($b['pouvoir_nomination'] ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                <option value="Non" {{ ($b['pouvoir_nomination'] ?? '') === 'Non' ? 'selected' : '' }}>Non</option>
                            </select>
                        </td>
                        <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                            <button type="button" class="btn btn-sm btn-danger removeBeneficiaireEffectifLine" style="padding:0.25rem 0.45rem;">×</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-2">
        <button type="button" id="addBeneficiaireEffectifLine" class="btn btn-sm btn-outline-secondary">+ Ajouter un bénéficiaire</button>
    </div>
</div>

<script>
    (function () {
        const linesBody = document.getElementById('beneficiairesEffectifsLines');
        const addButton = document.getElementById('addBeneficiaireEffectifLine');
        if (!linesBody || !addButton) return;

        function updateIndexes() {
            Array.from(linesBody.querySelectorAll('tr')).forEach((row, index) => {
                row.querySelectorAll('[name*="beneficiaires_effectifs["]').forEach((field) => {
                    field.name = field.name.replace(/beneficiaires_effectifs\[\d+\]/, `beneficiaires_effectifs[${index}]`);
                });
            });
        }

        function createRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <textarea name="beneficiaires_effectifs[0][identite]" rows="2" class="form-control" placeholder="Nom, prénom, nationalité, pays de résidence"></textarea>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <select name="beneficiaires_effectifs[0][action_25]" class="form-control">
                        <option value="">-</option>
                        <option value="Oui">Oui</option>
                        <option value="Non">Non</option>
                    </select>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <select name="beneficiaires_effectifs[0][vote_25]" class="form-control">
                        <option value="">-</option>
                        <option value="Oui">Oui</option>
                        <option value="Non">Non</option>
                    </select>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top;">
                    <select name="beneficiaires_effectifs[0][pouvoir_nomination]" class="form-control">
                        <option value="">-</option>
                        <option value="Oui">Oui</option>
                        <option value="Non">Non</option>
                    </select>
                </td>
                <td style="border:1px solid #ccc; padding:8px; vertical-align:top; text-align:center;">
                    <button type="button" class="btn btn-sm btn-danger removeBeneficiaireEffectifLine" style="padding:0.25rem 0.45rem;">×</button>
                </td>
            `;
            return row;
        }

        addButton.addEventListener('click', function () {
            linesBody.appendChild(createRow());
            updateIndexes();
        });

        linesBody.addEventListener('click', function (event) {
            if (event.target.classList.contains('removeBeneficiaireEffectifLine')) {
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
