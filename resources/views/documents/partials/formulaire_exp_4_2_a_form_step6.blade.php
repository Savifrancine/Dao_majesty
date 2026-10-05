<div id="formulaire-exp-4-2-a-step6">
    @php
        $formulairesExp42a = \App\Models\FormulaireExp42A::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get();
        $rolesExp42a = ['Fournisseur/Prestataire', 'Ensemblier', 'Sous-traitant'];
        $cellStyle = 'border:1px solid #000; padding:6px; vertical-align:top; font-size:12px;';
        $inputStyle = 'width:100%; margin-bottom:4px;';
    @endphp

    <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Un formulaire est créé pour chaque marché listé dans le Formulaire EXP – 4.1. Les informations connues sont déjà renseignées ; complétez le reste puis enregistrez.</p>

    @if($formulairesExp42a->isEmpty())
        <div style="font-size:13px; color:#6b7280; font-style:italic;">Aucun marché trouvé. Renseignez d'abord le Formulaire EXP – 4.1 (expérience générale).</div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="{{ $cellStyle }} width:5%; text-align:center;">N°</th>
                        <th style="{{ $cellStyle }} width:30%; text-align:left;">Numéro de marché similaire et identification</th>
                        <th style="{{ $cellStyle }} width:14%; text-align:center;">Rôle dans le marché</th>
                        <th style="{{ $cellStyle }} width:17%; text-align:center;">Montant total du marché (FCFA)</th>
                        <th style="{{ $cellStyle }} width:26%; text-align:center;">Autorité contractante (Nom &amp; contact)</th>
                        <th style="{{ $cellStyle }} width:8%; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formulairesExp42a as $f)
                        <tr>
                            <td style="{{ $cellStyle }} text-align:center;">{{ $f->marche_position + 1 }}</td>
                            <td style="{{ $cellStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][numero_marche]" value="{{ old("exp42a.$f->id.numero_marche", $f->numero_marche) }}" placeholder="Numéro de marché similaire" class="form-control" style="{{ $inputStyle }}">
                                <textarea name="exp42a[{{ $f->id }}][identification_marche]" placeholder="Identification du marché (Titre, détails...)" class="form-control" style="{{ $inputStyle }} min-height:60px;">{{ old("exp42a.$f->id.identification_marche", $f->identification_marche) }}</textarea>
                                <input type="date" name="exp42a[{{ $f->id }}][date_attribution]" value="{{ old("exp42a.$f->id.date_attribution", optional($f->date_attribution)->format('Y-m-d')) }}" class="form-control" style="{{ $inputStyle }}" title="Date d'attribution">
                                <input type="date" name="exp42a[{{ $f->id }}][date_achevement]" value="{{ old("exp42a.$f->id.date_achevement", optional($f->date_achevement)->format('Y-m-d')) }}" class="form-control" style="{{ $inputStyle }}" title="Date d'achèvement">
                            </td>
                            <td style="{{ $cellStyle }}">
                                <select name="exp42a[{{ $f->id }}][role_marche]" class="form-control" style="{{ $inputStyle }}">
                                    <option value="">--Rôle--</option>
                                    @foreach($rolesExp42a as $role)
                                        <option value="{{ $role }}" {{ old("exp42a.$f->id.role_marche", $f->role_marche) === $role ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="{{ $cellStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][montant_total]" value="{{ old("exp42a.$f->id.montant_total", $f->montant_total) }}" placeholder="Montant total" class="form-control" style="{{ $inputStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][participation_pourcentage]" value="{{ old("exp42a.$f->id.participation_pourcentage", $f->participation_pourcentage) }}" placeholder="% participation" class="form-control" style="{{ $inputStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][montant_part]" value="{{ old("exp42a.$f->id.montant_part", $f->montant_part) }}" placeholder="Montant part" class="form-control" style="{{ $inputStyle }}">
                            </td>
                            <td style="{{ $cellStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][autorite_nom]" value="{{ old("exp42a.$f->id.autorite_nom", $f->autorite_nom) }}" placeholder="Nom autorité" class="form-control" style="{{ $inputStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][autorite_adresse]" value="{{ old("exp42a.$f->id.autorite_adresse", $f->autorite_adresse) }}" placeholder="Adresse" class="form-control" style="{{ $inputStyle }}">
                                <input type="text" name="exp42a[{{ $f->id }}][autorite_telephone]" value="{{ old("exp42a.$f->id.autorite_telephone", $f->autorite_telephone) }}" placeholder="Téléphone" class="form-control" style="{{ $inputStyle }}">
                                <input type="email" name="exp42a[{{ $f->id }}][autorite_email]" value="{{ old("exp42a.$f->id.autorite_email", $f->autorite_email) }}" placeholder="Email" class="form-control" style="{{ $inputStyle }}">
                            </td>
                            <td style="{{ $cellStyle }} text-align:center;">
                                <a href="{{ route('formulaire_exp_4_2_a.download', $f->id) }}" target="_blank" rel="noopener noreferrer">PDF</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
