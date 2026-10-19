<div id="formulaire-mat-step6">
    @php
        $formulairesMat = \App\Models\FormulaireMat::where('dossier_id', $dossier->id)
            ->whereNotNull('materiel_position')
            ->orderBy('materiel_position')
            ->get();
        $signatairesMat = \App\Models\Signataire::orderBy('nom')->orderBy('prenom')->get();
    @endphp

    <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Un Formulaire MAT est créé pour chaque matériel déjà renseigné dans la Liste de Matériel : le type et caractéristiques du matériel la suivent automatiquement. Complétez les informations propres à chaque matériel, puis enregistrez.</p>

    @if($formulairesMat->isEmpty())
        <div style="font-size:13px; color:#6b7280; font-style:italic;">Aucun matériel trouvé. Renseignez d'abord la Liste de Matériel.</div>
    @else
        @foreach($formulairesMat as $f)
            <div class="mat-block" data-record-id="{{ $f->id }}" style="margin-bottom:28px; padding:14px; border:1px solid #d1d5db; border-radius:6px;">
                <div style="font-weight:700; margin-bottom:10px;">Matériel {{ $f->materiel_position + 1 }} — {{ $f->piece_materiel }}</div>

                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Fabricant</label>
                        <input type="text" name="mat[{{ $f->id }}][fabricant]" value="{{ old("mat.$f->id.fabricant", $f->fabricant) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Modèle / Puissance</label>
                        <input type="text" name="mat[{{ $f->id }}][modele_puissance]" value="{{ old("mat.$f->id.modele_puissance", $f->modele_puissance) }}" class="form-control">
                    </div>
                </div>
                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Capacité</label>
                        <input type="text" name="mat[{{ $f->id }}][capacite]" value="{{ old("mat.$f->id.capacite", $f->capacite) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:160px;">
                        <label class="wizard-label">Année de fabrication</label>
                        <input type="text" name="mat[{{ $f->id }}][annee_fabrication]" value="{{ old("mat.$f->id.annee_fabrication", $f->annee_fabrication) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Localisation actuelle</label>
                        <input type="text" name="mat[{{ $f->id }}][localisation]" value="{{ old("mat.$f->id.localisation", $f->localisation) }}" class="form-control">
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label class="wizard-label">Engagements (contrats en cours utilisant ce matériel)</label>
                    <textarea name="mat[{{ $f->id }}][engagements]" class="form-control" rows="2">{{ old("mat.$f->id.engagements", $f->engagements) }}</textarea>
                </div>
                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Provenance</label>
                        @php $provenanceValue = old("mat.$f->id.provenance", $f->provenance); @endphp
                        <select name="mat[{{ $f->id }}][provenance]" class="form-control mat-provenance" data-record-id="{{ $f->id }}">
                            <option value="">-- Sélectionner --</option>
                            @foreach(['En possession' => 'en_possession', 'En location' => 'en_location', 'En location-vente' => 'en_location_vente', 'Fabriqué spécialement' => 'fabrique_specialement'] as $label => $value)
                                <option value="{{ $value }}" @selected($provenanceValue === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Signataire</label>
                        <select name="mat[{{ $f->id }}][signataire_id]" class="form-control">
                            <option value="">-- Aucun --</option>
                            @foreach($signatairesMat as $signataire)
                                <option value="{{ $signataire->id }}" @selected((string) old("mat.$f->id.signataire_id", $f->signataire_id) === (string) $signataire->id)>
                                    {{ $signataire->nom }} {{ $signataire->prenom }}{{ $signataire->fonction ? ' – ' . $signataire->fonction : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mat-proprietaire-block" data-record-id="{{ $f->id }}" style="{{ in_array($provenanceValue, ['en_location', 'en_location_vente', 'fabrique_specialement'], true) ? '' : 'display:none;' }} margin:10px 0; padding:10px; background:#f9fafb; border:1px dashed #cbd5e1; border-radius:6px;">
                    <div style="font-size:12px; color:#6b7280; margin-bottom:8px;">Ces renseignements sont omis si le matériel est « en possession » du candidat.</div>
                    <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <label class="wizard-label">Nom du Propriétaire</label>
                            <input type="text" name="mat[{{ $f->id }}][nom_proprietaire]" value="{{ old("mat.$f->id.nom_proprietaire", $f->nom_proprietaire) }}" class="form-control">
                        </div>
                        <div style="flex:1; min-width:200px;">
                            <label class="wizard-label">Adresse du Propriétaire</label>
                            <input type="text" name="mat[{{ $f->id }}][adresse_proprietaire]" value="{{ old("mat.$f->id.adresse_proprietaire", $f->adresse_proprietaire) }}" class="form-control">
                        </div>
                    </div>
                    <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:160px;">
                            <label class="wizard-label">Téléphone</label>
                            <input type="text" name="mat[{{ $f->id }}][telephone_proprietaire]" value="{{ old("mat.$f->id.telephone_proprietaire", $f->telephone_proprietaire) }}" class="form-control">
                        </div>
                        <div style="flex:1; min-width:200px;">
                            <label class="wizard-label">Nom et titre de la personne à contacter</label>
                            <input type="text" name="mat[{{ $f->id }}][contact_nom_titre]" value="{{ old("mat.$f->id.contact_nom_titre", $f->contact_nom_titre) }}" class="form-control">
                        </div>
                    </div>
                    <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:160px;">
                            <label class="wizard-label">Télécopie</label>
                            <input type="text" name="mat[{{ $f->id }}][telecopie_proprietaire]" value="{{ old("mat.$f->id.telecopie_proprietaire", $f->telecopie_proprietaire) }}" class="form-control">
                        </div>
                        <div style="flex:1; min-width:160px;">
                            <label class="wizard-label">Télex</label>
                            <input type="text" name="mat[{{ $f->id }}][telex_proprietaire]" value="{{ old("mat.$f->id.telex_proprietaire", $f->telex_proprietaire) }}" class="form-control">
                        </div>
                    </div>
                    <div>
                        <label class="wizard-label">Détails de la location / location-vente / accord de fabrication</label>
                        <textarea name="mat[{{ $f->id }}][accords_details]" class="form-control" rows="2">{{ old("mat.$f->id.accords_details", $f->accords_details) }}</textarea>
                    </div>
                </div>

                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Lieu (fait à)</label>
                        <input type="text" name="mat[{{ $f->id }}][lieu_fait]" value="{{ old("mat.$f->id.lieu_fait", $f->lieu_fait) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:160px;">
                        <label class="wizard-label">Date (fait le)</label>
                        <input type="date" name="mat[{{ $f->id }}][date_fait]" value="{{ old("mat.$f->id.date_fait", optional($f->date_fait)->format('Y-m-d')) }}" class="form-control">
                    </div>
                </div>

                <div style="text-align:right; margin-top:8px;">
                    <a href="{{ route('formulaire_mat.download', $f->id) }}" target="_blank" rel="noopener noreferrer">PDF</a>
                </div>
            </div>
        @endforeach

        <script>
            (function () {
                function toggle(select) {
                    var block = document.querySelector('.mat-proprietaire-block[data-record-id="' + select.dataset.recordId + '"]');
                    if (!block) return;
                    var show = ['en_location', 'en_location_vente', 'fabrique_specialement'].includes(select.value);
                    block.style.display = show ? '' : 'none';
                }
                document.querySelectorAll('.mat-provenance').forEach(function (select) {
                    toggle(select);
                    select.addEventListener('change', function () { toggle(select); });
                });
            })();
        </script>
    @endif
</div>
