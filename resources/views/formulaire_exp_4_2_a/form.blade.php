@php
    $dossier = $dossier ?? null;
    $dossierEntreprise = optional($dossier)->entreprise;

    // Charger les entreprises si elles n'ont pas été fournies par le contrôleur
    $entreprises = $entreprises ?? \App\Models\Entreprise::orderBy('nom')->get();

    // Charger les dossiers si ils n'ont pas été fournis par le contrôleur
    $dossiers = $dossiers ?? \App\Models\Dossier::with('entreprise')->orderByDesc('created_at')->get();

    // Sélection courante de l'entreprise (old() > formulaire existant > dossier)
    $selectedEntrepriseId = old('entreprise_id', optional($formulaireExp42A)->entreprise_id ?? optional($dossier)->entreprise->id ?? '');
    $selectedDossierId = old('dossier_id', $dossier_id ?? $formulaireExp42A->dossier_id ?? '');
@endphp

<div style="margin-top:10px;">
    <form action="{{ $action }}" method="{{ in_array($method, ['PUT','PATCH']) ? 'POST' : $method }}">
        @csrf
        @if(in_array($method, ['PUT','PATCH']))
            @method($method)
        @endif

        <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:18px;">
            <div style="flex:1; min-width:220px;">
                <label for="dossier_id" class="wizard-label">Dossier concern (Step 4)</label>
                <select class="form-select wizard-input @error('dossier_id') is-invalid @enderror" name="dossier_id" id="dossier_id">
                    <option value="">-- Selectionnez un dossier --</option>
                    @forelse($dossiers as $dosOption)
                        <option value="{{ $dosOption->id }}" {{ (string)$selectedDossierId === (string)$dosOption->id ? 'selected' : '' }}
                            data-reference="{{ e($dosOption->reference_dossier ?? $dosOption->ref ?? '') }}"
                            data-date-lancement="{{ $dosOption->date_lancement ? $dosOption->date_lancement->format('d/m/Y') : '' }}"
                            data-titre="{{ e($dosOption->titre_dossier ?? $dosOption->titre_lot ?? '') }}"
                            data-date-soumission="{{ $dosOption->date_soumission ? $dosOption->date_soumission->format('d/m/Y') : '' }}">
                            {{ $dosOption->nom_dossier }} {{ $dosOption->reference_dossier ? '(' . $dosOption->reference_dossier . ')' : '' }}
                        </option>
                    @empty
                        <option disabled>Aucun dossier disponible</option>
                    @endforelse
                </select>
                @error('dossier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div style="flex:1; min-width:220px;">
                <label for="entreprise_id" class="wizard-label">Entreprise <span class="text-danger">*</span></label>
                <select class="form-select wizard-input @error('entreprise_id') is-invalid @enderror" name="entreprise_id" id="entreprise_id" required>
                    <option value="">-- Selectionnez une entreprise --</option>
                    @forelse($entreprises as $entreprise)
                        <option value="{{ $entreprise->id }}" {{ (string)$selectedEntrepriseId === (string)$entreprise->id ? 'selected' : '' }}
                            data-nom="{{ e($entreprise->responsable ?? $entreprise->nom) }}"
                            data-adresse="{{ e($entreprise->adresse_officielle ?? $entreprise->adresse) }}"
                            data-telephone="{{ e($entreprise->telephone) }}"
                            data-email="{{ e($entreprise->email) }}">
                            {{ $entreprise->nom }} @if($entreprise->sigle)({{ $entreprise->sigle }})@endif
                        </option>
                    @empty
                        <option disabled>Aucune entreprise disponible</option>
                    @endforelse
                </select>
                @error('entreprise_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if(isset($signataires))
                <div style="flex:1; min-width:220px;">
                    <label class="form-label">Signataire</label>
                    <select name="signataire_id" class="form-select @error('signataire_id') is-invalid @enderror">
                        <option value="">-- Choisir un signataire --</option>
                        @foreach($signataires as $signataireOption)
                            <option value="{{ $signataireOption->id }}"
                                {{ old('signataire_id') == $signataireOption->id ? 'selected' : '' }}>
                                {{ $signataireOption->nom }} {{ $signataireOption->prenom }}{{ $signataireOption->fonction ? ' – ' . $signataireOption->fonction : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('signataire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
        </div>

        <div style="font-weight:700; font-size:15px; margin-bottom:8px; text-align:center;">{{ $formTitle ?? 'Formulaire EXP – 4.2 a) : Expérience spécifique de fournitures/services' }}</div>

        <div style="width:100%; overflow-x:auto;">
            <table id="exp42-table" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#f3f4f6; font-weight:700;">
                    <th style="border:1px solid #000; padding:6px; width:6%; text-align:center;">N°</th>
                    <th style="border:1px solid #000; padding:6px; text-align:left;">Numéro de marché similaire et identification</th>
                    <th style="border:1px solid #000; padding:6px; text-align:center; width:18%;">Rôle dans le marché</th>
                    <th style="border:1px solid #000; padding:6px; text-align:right; width:18%;">Montant total du marché (FCFA)</th>
                    <th style="border:1px solid #000; padding:6px; text-align:left; width:22%;">Autorité contractante (Nom & contact)</th>
                    <th style="border:1px solid #000; padding:6px; text-align:center; width:6%;">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid #000; padding:6px; text-align:center; vertical-align:top;">1</td>
                    <td style="border:1px solid #000; padding:6px; vertical-align:top;">
                        <div style="margin-bottom:10px;">
                            <label style="display:block; font-weight:700; margin-bottom:4px;">Numéro de marché similaire</label>
                            <input type="text" name="numero_marche" class="form-control" placeholder="Numéro de marché similaire" style="width:100%;" value="{{ old('numero_marche', optional($formulaireExp42A)->numero_marche ?? '') }}">
                        </div>
                        <textarea name="identification_marche" class="form-control" rows="3" placeholder="Identification du marché (Titre, détails...)" style="width:100%;">{{ old('identification_marche', optional($formulaireExp42A)->identification_marche ?? '') }}</textarea>
                        <div style="display:flex; gap:8px; margin-top:6px;">
                            <input type="date" name="date_attribution" placeholder="Date d'attribution" style="flex:1;" value="{{ old('date_attribution', optional($formulaireExp42A)->date_attribution?->format('Y-m-d') ?? '') }}">
                            <input type="date" name="date_achevement" placeholder="Date d'achèvement" style="flex:1;" value="{{ old('date_achevement', optional($formulaireExp42A)->date_achevement?->format('Y-m-d') ?? '') }}">
                        </div>
                    </td>
                    <td style="border:1px solid #000; padding:6px; vertical-align:top; text-align:center;">
                        <select name="role_marche" style="width:100%;">
                            <option value="">--Rôle--</option>
                            <option value="Fournisseur/Prestataire" {{ old('role_marche', optional($formulaireExp42A)->role_marche ?? '') === 'Fournisseur/Prestataire' ? 'selected' : '' }}>Fournisseur/Prestataire</option>
                            <option value="Ensemblier" {{ old('role_marche', optional($formulaireExp42A)->role_marche ?? '') === 'Ensemblier' ? 'selected' : '' }}>Ensemblier</option>
                            <option value="Sous-traitant" {{ old('role_marche', optional($formulaireExp42A)->role_marche ?? '') === 'Sous-traitant' ? 'selected' : '' }}>Sous-traitant</option>
                        </select>
                    </td>
                    <td style="border:1px solid #000; padding:6px; vertical-align:top; text-align:right;">
                        <input type="text" name="montant_total" placeholder="Montant" style="width:100%; text-align:right;" value="{{ old('montant_total', optional($formulaireExp42A)->montant_total ?? '') }}">
                        <input type="text" name="participation_pourcentage" placeholder="% participation" style="width:100%; margin-top:6px;" value="{{ old('participation_pourcentage', optional($formulaireExp42A)->participation_pourcentage ?? '') }}">
                        <input type="text" name="montant_part" placeholder="Montant part (auto)" style="width:100%; margin-top:6px; text-align:right;" readonly value="{{ old('montant_part', optional($formulaireExp42A)->montant_part ?? '') }}">
                        <input type="hidden" name="monnaie" value="{{ old('monnaie', optional($formulaireExp42A)->monnaie ?? 'FCFA') }}">
                    </td>
                    <td style="border:1px solid #000; padding:6px; vertical-align:top;">
                        <input type="text" name="autorite_nom" placeholder="Nom autorité" style="width:100%; margin-bottom:6px;" value="{{ old('autorite_nom', optional($formulaireExp42A)->autorite_nom ?? '') }}">
                        <input type="text" name="autorite_adresse" placeholder="Adresse" style="width:100%; margin-bottom:6px;" value="{{ old('autorite_adresse', optional($formulaireExp42A)->autorite_adresse ?? '') }}">
                        <input type="text" name="autorite_telephone" placeholder="Téléphone" style="width:100%; margin-bottom:6px;" value="{{ old('autorite_telephone', optional($formulaireExp42A)->autorite_telephone ?? '') }}">
                        <input type="text" name="autorite_email" placeholder="Email" style="width:100%;" value="{{ old('autorite_email', optional($formulaireExp42A)->autorite_email ?? '') }}">
                    </td>
                    <td style="border:1px solid #000; padding:6px; vertical-align:top; text-align:center;">&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>


    <script>
        (function(){
            const dosselect = document.getElementById('dossier_id');

            dosselect?.addEventListener('change', function(){
                const selected = this.options[this.selectedIndex];
                if(!selected || !selected.value) return;

                const dossierData = {
                    reference: selected.getAttribute('data-reference'),
                    date_lancement: selected.getAttribute('data-date-lancement'),
                    titre: selected.getAttribute('data-titre'),
                    date_soumission: selected.getAttribute('data-date-soumission')
                };

                console.log('Dossier sélectionné:', dossierData);
            });
        })();
    </script>
    <script>
        (function(){
            const montantInput = document.querySelector('input[name="montant_total"]');
            const participationInput = document.querySelector('input[name="participation_pourcentage"]');
            const montantPartInput = document.querySelector('input[name="montant_part"]');

            function parseNumber(v){
                if(!v) return 0;
                v = String(v).replace(/\s+/g,'').replace(/\./g,'').replace(/,/g,'.');
                v = v.replace(/[^0-9.\-]/g,'');
                return parseFloat(v) || 0;
            }

            function formatNumber(n){
                if(isNaN(n)) return '';
                return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            }

            function updateMontantPart(){
                if (!montantInput || !participationInput || !montantPartInput) return;
                const montant = parseNumber(montantInput.value);
                const part = parseNumber(participationInput.value);
                const calc = montant * (part / 100);
                montantPartInput.value = calc ? formatNumber(calc) : '';
            }

            if (montantInput) montantInput.addEventListener('input', updateMontantPart);
            if (participationInput) participationInput.addEventListener('input', updateMontantPart);
            updateMontantPart();
        })();
    </script>

        <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">{{ $buttonLabel }}</button>
        </div>
    </form>
</div>
