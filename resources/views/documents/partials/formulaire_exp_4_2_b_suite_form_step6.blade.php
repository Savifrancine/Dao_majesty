<div id="formulaire-exp-4-2-b-suite-step6">
    @php
        $formulairesExp42bSuite = \App\Models\FormulaireExp42BSuite::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get();
        $nomsParPosition = \App\Models\FormulaireExp42B::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->get()
            ->keyBy('marche_position')
            ->map(fn ($f) => $f->identification_marche);
        $cellStyle = 'border:1px solid #000; padding:6px; vertical-align:top; font-size:12px;';
        $inputStyle = 'width:100%;';
    @endphp

    <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Un formulaire est créé pour chaque marché déjà renseigné dans le Formulaire EXP – 4.2 b) : le numéro de marché le suit automatiquement. Complétez les informations propres à chaque marché, puis enregistrez.</p>

    @if($formulairesExp42bSuite->isEmpty())
        <div style="font-size:13px; color:#6b7280; font-style:italic;">Aucun marché trouvé. Renseignez d'abord le Formulaire EXP – 4.2 b).</div>
    @else
        @foreach($formulairesExp42bSuite as $f)
            <div style="margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
                <div style="font-weight:700; margin-bottom:8px;">Marché {{ $f->marche_position + 1 }}{{ $nomsParPosition[$f->marche_position] ?? null ? ' : ' . $nomsParPosition[$f->marche_position] : '' }}</div>

                <div class="wizard-field" style="margin-bottom:10px;">
                    <label class="wizard-label">Numéro de marché similaire</label>
                    <input type="text" value="{{ $f->numero_marche }}" class="form-control" style="{{ $inputStyle }}" readonly disabled>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f5f5f5;">
                                <th style="{{ $cellStyle }} width:28%; text-align:left;">Description de la similitude conformément au sous-critère 4.2 b)</th>
                                <th style="{{ $cellStyle }} text-align:center;">Montant</th>
                                <th style="{{ $cellStyle }} text-align:center;">Taille physique</th>
                                <th style="{{ $cellStyle }} text-align:center;">Complexité</th>
                                <th style="{{ $cellStyle }} text-align:center;">Méthodes/technologie</th>
                                <th style="{{ $cellStyle }} text-align:center;">Autres caractéristiques</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="{{ $cellStyle }}">
                                    <textarea name="exp42b_suite[{{ $f->id }}][description_similitude]" placeholder="Description" class="form-control" style="{{ $inputStyle }} min-height:60px;">{{ old("exp42b_suite.$f->id.description_similitude", $f->description_similitude) }}</textarea>
                                </td>
                                <td style="{{ $cellStyle }}">
                                    <input type="text" name="exp42b_suite[{{ $f->id }}][montant]" value="{{ old("exp42b_suite.$f->id.montant", $f->montant) }}" placeholder="Montant" class="form-control" style="{{ $inputStyle }}">
                                </td>
                                <td style="{{ $cellStyle }}">
                                    <input type="text" name="exp42b_suite[{{ $f->id }}][taille_physique]" value="{{ old("exp42b_suite.$f->id.taille_physique", $f->taille_physique) }}" placeholder="Taille physique" class="form-control" style="{{ $inputStyle }}">
                                </td>
                                <td style="{{ $cellStyle }}">
                                    <input type="text" name="exp42b_suite[{{ $f->id }}][complexite]" value="{{ old("exp42b_suite.$f->id.complexite", $f->complexite) }}" placeholder="Complexité" class="form-control" style="{{ $inputStyle }}">
                                </td>
                                <td style="{{ $cellStyle }}">
                                    <input type="text" name="exp42b_suite[{{ $f->id }}][methodes_technologie]" value="{{ old("exp42b_suite.$f->id.methodes_technologie", $f->methodes_technologie) }}" placeholder="Méthodes" class="form-control" style="{{ $inputStyle }}">
                                </td>
                                <td style="{{ $cellStyle }}">
                                    <input type="text" name="exp42b_suite[{{ $f->id }}][autres_caracteristiques]" value="{{ old("exp42b_suite.$f->id.autres_caracteristiques", $f->autres_caracteristiques) }}" placeholder="Autres" class="form-control" style="{{ $inputStyle }}">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="text-align:right; margin-top:6px;">
                    <a href="{{ route('formulaire_exp_4_2_b_suite.download', $f->id) }}" target="_blank" rel="noopener noreferrer">PDF</a>
                </div>
            </div>
        @endforeach
    @endif
</div>
