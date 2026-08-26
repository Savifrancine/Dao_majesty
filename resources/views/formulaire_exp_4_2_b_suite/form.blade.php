@php
    $dossier = $dossier ?? null;
    $dossierEntreprise = optional($dossier)->entreprise;

    // Charger les entreprises si elles n'ont pas été fournies par le contrôleur
    $entreprises = $entreprises ?? \App\Models\Entreprise::orderBy('nom')->get();

    // Charger les dossiers si ils n'ont pas été fournis par le contrôleur
    $dossiers = $dossiers ?? \App\Models\Dossier::with('entreprise')->orderByDesc('created_at')->get();

    // Charger les signataires si pas fournis
    $signataires = $signataires ?? \App\Models\Signataire::orderBy('nom')->orderBy('prenom')->get();

    // Sélection courante de l'entreprise (old() > formulaire existant > dossier)
    $selectedEntrepriseId = old('entreprise_id', optional($formulaireExp42BSuite)->entreprise_id ?? optional($dossier)->entreprise->id ?? '');
    $selectedDossierId = old('dossier_id', $dossier_id ?? $formulaireExp42BSuite->dossier_id ?? '');
    $selectedSignataireId = old('signataire_id', $formulaireExp42BSuite->signataire_id ?? '');
@endphp

<div style="margin-top:10px;">
    <form action="{{ $action }}" method="{{ in_array($method, ['PUT','PATCH']) ? 'POST' : $method }}">
        @csrf
        @if(in_array($method, ['PUT','PATCH']))
            @method($method)
        @endif

        <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:18px;">
            <div style="flex:1; min-width:220px;">
                <label for="dossier_id" class="wizard-label">Dossier concerné (Step 4)</label>
                <select class="form-select wizard-input @error('dossier_id') is-invalid @enderror" name="dossier_id" id="dossier_id">
                    <option value="">-- Sélectionnez un dossier --</option>
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
                    <option value="">-- Sélectionnez une entreprise --</option>
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

            <div style="flex:1; min-width:220px;">
                <label class="wizard-label">Signataire</label>
                <select name="signataire_id" class="form-select @error('signataire_id') is-invalid @enderror">
                    <option value="">-- Choisir un signataire --</option>
                    @foreach($signataires as $signataireOption)
                        <option value="{{ $signataireOption->id }}"
                            {{ (string)$selectedSignataireId === (string)$signataireOption->id ? 'selected' : '' }}>
                            {{ $signataireOption->nom }} {{ $signataireOption->prenom }}{{ $signataireOption->fonction ? ' – ' . $signataireOption->fonction : '' }}
                        </option>
                    @endforeach
                </select>
                @error('signataire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="font-weight:700; font-size:14px; margin:16px 0 12px 0; text-align:center; background:#f3f4f6; padding:8px; border-radius:4px;">
            {{ $formTitle ?? 'FORMULAIRE EXP-4.2 b) (suite)' }}
        </div>

        <div class="wizard-field" style="margin-bottom:18px;">
            <label class="wizard-label">Numéro de marché similaire</label>
            <input type="text" name="numero_marche" class="form-control" placeholder="Numéro de marché similaire" value="{{ old('numero_marche', optional($formulaireExp42BSuite)->numero_marche ?? '') }}">
        </div>

        <div style="width:100%; overflow-x:auto;">
            <table id="exp42-b-suite-table" style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#f3f4f6; font-weight:700; border-top:2px solid #000; border-bottom:2px solid #000;">
                    <th style="border:1px solid #000; padding:8px; text-align:left;">Description de la similitude conformément au sous-critère 4.2 a)</th>
                    <th style="border:1px solid #000; padding:8px; text-align:center; width:15%;">Montant</th>
                    <th style="border:1px solid #000; padding:8px; text-align:center; width:15%;">Taille physique</th>
                    <th style="border:1px solid #000; padding:8px; text-align:center; width:15%;">Complexité</th>
                    <th style="border:1px solid #000; padding:8px; text-align:center; width:20%;">Méthodes/technologie</th>
                    <th style="border:1px solid #000; padding:8px; text-align:center; width:20%;">Autres caractéristiques</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">
                        <textarea name="description_similitude" class="form-control form-control-sm" rows="3" placeholder="Description" style="font-size:12px;">{{ old('description_similitude', optional($formulaireExp42BSuite)->description_similitude ?? '') }}</textarea>
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">
                        <input type="text" name="montant" class="form-control form-control-sm" placeholder="Montant" value="{{ old('montant', optional($formulaireExp42BSuite)->montant ?? '') }}" style="font-size:12px;">
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">
                        <input type="text" name="taille_physique" class="form-control form-control-sm" placeholder="Taille physique" value="{{ old('taille_physique', optional($formulaireExp42BSuite)->taille_physique ?? '') }}" style="font-size:12px;">
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">
                        <input type="text" name="complexite" class="form-control form-control-sm" placeholder="Complexité" value="{{ old('complexite', optional($formulaireExp42BSuite)->complexite ?? '') }}" style="font-size:12px;">
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">
                        <input type="text" name="methodes_technologie" class="form-control form-control-sm" placeholder="Méthodes" value="{{ old('methodes_technologie', optional($formulaireExp42BSuite)->methodes_technologie ?? '') }}" style="font-size:12px;">
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align:top;">
                        <input type="text" name="autres_caracteristiques" class="form-control form-control-sm" placeholder="Autres" value="{{ old('autres_caracteristiques', optional($formulaireExp42BSuite)->autres_caracteristiques ?? '') }}" style="font-size:12px;">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

        <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">{{ $buttonLabel }}</button>
        </div>
    </form>
</div>
