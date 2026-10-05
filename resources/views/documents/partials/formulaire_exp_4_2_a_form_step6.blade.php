<div id="formulaire-exp-4-2-a-step6">
    @php
        $formulairesExp42a = \App\Models\FormulaireExp42A::where('dossier_id', $dossier->id)
            ->whereNotNull('marche_position')
            ->orderBy('marche_position')
            ->get();
    @endphp

    <div class="wizard-field" style="margin-bottom:16px;">
        <label class="wizard-label">Formulaires EXP – 4.2 a) par marché</label>
        <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Un formulaire est créé automatiquement pour chaque marché listé dans le Formulaire EXP – 4.1. Complétez les informations manquantes ; chaque formulaire apparaît dans le PDF du dossier après cet intercalaire.</p>
    </div>

    @if($formulairesExp42a->isEmpty())
        <div style="font-size:13px; color:#6b7280; font-style:italic;">Aucun marché trouvé. Renseignez d'abord le Formulaire EXP – 4.1 (expérience générale).</div>
    @else
        @foreach($formulairesExp42a as $f)
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:8px 10px; background:#fff; margin-bottom:6px; border:1px solid #ddd; border-radius:3px;">
                <span>Marché {{ $f->marche_position + 1 }} : {{ $f->identification_marche ?: '—' }}</span>
                <a href="{{ route('formulaire_exp_4_2_a.edit', $f->id) }}" class="btn-action" target="_blank" rel="noopener noreferrer">Modifier</a>
            </div>
        @endforeach
    @endif
</div>
