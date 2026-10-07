<div id="formulaire-per-step6">
    @php
        $formulairesPer = \App\Models\FormulairePER::where('dossier_id', $dossier->id)
            ->whereNotNull('personnel_position')
            ->orderBy('personnel_position')
            ->get();
    @endphp

    <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Un Formulaire PER est créé pour chaque personne déjà renseignée dans la Liste du personnel affecté à l'exécution du marché : le poste et le nom la suivent automatiquement. Complétez les informations propres à chaque personne, puis enregistrez.</p>

    @if($formulairesPer->isEmpty())
        <div style="font-size:13px; color:#6b7280; font-style:italic;">Aucune personne trouvée. Renseignez d'abord la Liste du personnel affecté à l'exécution du marché.</div>
    @else
        @foreach($formulairesPer as $f)
            @php $exps = old("per.$f->id.experiences") ? json_decode(old("per.$f->id.experiences"), true) : ($f->experiences ?: []); @endphp
            <div class="per-block" data-record-id="{{ $f->id }}" style="margin-bottom:28px; padding:14px; border:1px solid #d1d5db; border-radius:6px;">
                <div style="font-weight:700; margin-bottom:10px;">Personne {{ $f->personnel_position + 1 }} — {{ $f->poste }} : {{ $f->nom_personnel }}</div>

                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Date de naissance</label>
                        <input type="date" name="per[{{ $f->id }}][date_naissance]" value="{{ old("per.$f->id.date_naissance", optional($f->date_naissance)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div style="flex:2; min-width:260px;">
                        <label class="wizard-label">Qualifications professionnelles</label>
                        <textarea name="per[{{ $f->id }}][qualifications]" class="form-control" rows="2">{{ old("per.$f->id.qualifications", $f->qualifications) }}</textarea>
                    </div>
                </div>

                <div style="font-weight:700; margin:10px 0 6px;">Employeur actuel</div>
                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Nom de l'employeur</label>
                        <input type="text" name="per[{{ $f->id }}][nom_employeur]" value="{{ old("per.$f->id.nom_employeur", $f->nom_employeur) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Emploi tenu</label>
                        <input type="text" name="per[{{ $f->id }}][emploi_tenu]" value="{{ old("per.$f->id.emploi_tenu", $f->emploi_tenu) }}" class="form-control">
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label class="wizard-label">Adresse de l'employeur</label>
                    <textarea name="per[{{ $f->id }}][adresse_employeur]" class="form-control" rows="2">{{ old("per.$f->id.adresse_employeur", $f->adresse_employeur) }}</textarea>
                </div>
                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:160px;">
                        <label class="wizard-label">Téléphone</label>
                        <input type="text" name="per[{{ $f->id }}][telephone]" value="{{ old("per.$f->id.telephone", $f->telephone) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:160px;">
                        <label class="wizard-label">Télécopie</label>
                        <input type="text" name="per[{{ $f->id }}][telecopie]" value="{{ old("per.$f->id.telecopie", $f->telecopie) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Email</label>
                        <input type="email" name="per[{{ $f->id }}][email]" value="{{ old("per.$f->id.email", $f->email) }}" class="form-control">
                    </div>
                </div>
                <div class="row" style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:220px;">
                        <label class="wizard-label">Contact (responsable / chargé du personnel)</label>
                        <input type="text" name="per[{{ $f->id }}][contact_personnel]" value="{{ old("per.$f->id.contact_personnel", $f->contact_personnel) }}" class="form-control">
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label class="wizard-label">Nombre d'années avec cet employeur</label>
                        <input type="number" min="0" name="per[{{ $f->id }}][nombre_annees_employeur]" value="{{ old("per.$f->id.nombre_annees_employeur", $f->nombre_annees_employeur) }}" class="form-control">
                    </div>
                </div>

                <div style="font-weight:700; margin:10px 0 6px;">Expérience professionnelle des dix (10) dernières années</div>
                <div class="experiences-container" style="display:flex; flex-direction:column; gap:8px;">
                    @forelse($exps as $exp)
                        <div class="experience-row" style="display:flex; gap:8px; flex-wrap:wrap;">
                            <input type="text" class="form-control exp-de" placeholder="De (année)" value="{{ $exp['de'] ?? '' }}" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-a" placeholder="À (année)" value="{{ $exp['a'] ?? '' }}" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" value="{{ $exp['description'] ?? '' }}" style="flex:3; min-width:220px;">
                            <button type="button" class="btn-action remove-experience" style="background:#dc2626; color:#fff;">Supprimer</button>
                        </div>
                    @empty
                        <div class="experience-row" style="display:flex; gap:8px; flex-wrap:wrap;">
                            <input type="text" class="form-control exp-de" placeholder="De (année)" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-a" placeholder="À (année)" style="flex:1; min-width:100px;">
                            <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" style="flex:3; min-width:220px;">
                            <button type="button" class="btn-action remove-experience" style="background:#dc2626; color:#fff;">Supprimer</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn-action add-experience" style="margin-top:8px;">+ Ajouter une expérience</button>
                <input type="hidden" name="per[{{ $f->id }}][experiences]" class="experiences-input" value="{{ old("per.$f->id.experiences") }}">

                <div style="margin-top:10px;">
                    <label class="wizard-label">Lieu de signature</label>
                    <input type="text" name="per[{{ $f->id }}][lieu_signature]" value="{{ old("per.$f->id.lieu_signature", $f->lieu_signature) }}" class="form-control" style="max-width:260px;">
                </div>

                <div style="text-align:right; margin-top:8px;">
                    <a href="{{ route('formulaire_per.pdf', $f->id) }}" target="_blank" rel="noopener noreferrer">PDF</a>
                </div>
            </div>
        @endforeach
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function syncExperiencesInput(block) {
            const rows = block.querySelectorAll('.experience-row');
            const experiences = [];
            rows.forEach((row) => {
                const de = row.querySelector('.exp-de').value;
                const a = row.querySelector('.exp-a').value;
                const description = row.querySelector('.exp-description').value;
                if (de || a || description) {
                    experiences.push({ de, a, description });
                }
            });
            block.querySelector('.experiences-input').value = JSON.stringify(experiences);
        }

        function createExperienceRow() {
            const row = document.createElement('div');
            row.className = 'experience-row';
            row.style.cssText = 'display:flex; gap:8px; flex-wrap:wrap;';
            row.innerHTML = `
                <input type="text" class="form-control exp-de" placeholder="De (année)" style="flex:1; min-width:100px;">
                <input type="text" class="form-control exp-a" placeholder="À (année)" style="flex:1; min-width:100px;">
                <input type="text" class="form-control exp-description" placeholder="Société / projet / position / expérience pertinente" style="flex:3; min-width:220px;">
                <button type="button" class="btn-action remove-experience" style="background:#dc2626; color:#fff;">Supprimer</button>
            `;
            return row;
        }

        document.querySelectorAll('.per-block').forEach((block) => {
            syncExperiencesInput(block);

            block.querySelector('.add-experience').addEventListener('click', () => {
                block.querySelector('.experiences-container').appendChild(createExperienceRow());
            });

            block.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-experience')) {
                    event.target.closest('.experience-row').remove();
                    syncExperiencesInput(block);
                }
            });

            block.addEventListener('input', (event) => {
                if (event.target.classList.contains('exp-de') || event.target.classList.contains('exp-a') || event.target.classList.contains('exp-description')) {
                    syncExperiencesInput(block);
                }
            });
        });

        document.querySelector('form')?.addEventListener('submit', () => {
            document.querySelectorAll('.per-block').forEach(syncExperiencesInput);
        });
    });
</script>
