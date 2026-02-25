<div id="declaration-step6">
    <div class="mb-3">
        <label class="wizard-label">Choisir un modèle</label>
        <select id="template-select" name="template_id" class="form-control wizard-input">
            <option value="">-- Aucun --</option>
            @foreach(App\Models\Template::where('type','declaration')->get() as $t)
                <option value="{{ $t->id }}">{{ $t->nom }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Société / Fournisseur</label>
        <input type="text" name="societe" id="societe_input" class="form-control wizard-input" value="{{ $dossier && $dossier->entreprise ? $dossier->entreprise->nom : '' }}" required>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Date</label>
        <input type="date" name="date" id="date_input" class="form-control wizard-input" value="{{ old('date', date('Y-m-d')) }}" required>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Nom du déclarant</label>
        <input type="text" name="declarant" id="declarant_input" class="form-control wizard-input" value="" required>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Fonction</label>
        <input type="text" name="fonction" id="fonction_input" class="form-control wizard-input" value="">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Référence (optionnel)</label>
        <input type="text" name="reference" id="reference_input" class="form-control wizard-input" value="{{ $dossier->reference_dossier ?? $dossier->ref ?? '' }}">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Aperçu du modèle</label>
        <div id="templatePreview" style="background:#fff;padding:12px;border-radius:8px;border:1px solid #e6e6e6;min-height:120px"></div>
    </div>

    <script>
        (function(){
            const select = document.getElementById('template-select');
            const preview = document.getElementById('templatePreview');
            const fields = {
                societe: document.getElementById('societe_input'),
                date: document.getElementById('date_input'),
                declarant: document.getElementById('declarant_input'),
                fonction: document.getElementById('fonction_input'),
                reference: document.getElementById('reference_input')
            };
            let rawTemplate = '';

            function escapeHtml(str) {
                return String(str).replace(/[&<>"'`]/g, function (s) {
                    return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;', '`':'&#96;'})[s];
                });
            }

            function replacePlaceholders(content){
                let out = content || '';
                Object.keys(fields).forEach(k => {
                    const val = fields[k].value || '';
                    const re = new RegExp('\\{\\{\\s*' + k + '\\s*\\}\\}', 'g');
                    out = out.replace(re, escapeHtml(val));
                });
                return out;
            }

            function nl2br(s) {
                return s.replace(/(\r\n|\n\r|\n|\r)/g, '<br>');
            }

            function updatePreview(){
                const replaced = replacePlaceholders(rawTemplate);
                preview.innerHTML = nl2br(escapeHtml(replaced));
            }

            select.addEventListener('change', async function(){
                const id = this.value;
                rawTemplate = '';
                preview.innerHTML = '';
                if (!id) return;
                try {
                    const res = await fetch(`{{ url('templates') }}/${id}/json`, { headers: {'X-Requested-With':'XMLHttpRequest'} });
                    if (!res.ok) throw new Error('Erreur');
                    const json = await res.json();
                    rawTemplate = json.content || '';
                    updatePreview();
                } catch (e) {
                    preview.innerHTML = '<div style="color:#c53030">Impossible de charger le modèle</div>';
                }
            });

            Object.values(fields).forEach(f => f.addEventListener('input', updatePreview));
        })();
    </script>

</div>
