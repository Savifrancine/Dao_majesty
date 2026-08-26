<div id="declaration-step6">
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
    @endphp

    <div class="mb-3">
        <label class="wizard-label">Choisir un modèle</label>
        <select id="template-select" name="template_id" class="form-control wizard-input">
            <option value="">-- Aucun --</option>
            @foreach(App\Models\Template::where('type','declaration')->get() as $t)
                <option value="{{ $t->id }}" {{ (string) $value('template_id') === (string) $t->id ? 'selected' : '' }}>{{ $t->nom }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Société / Fournisseur</label>
        <input type="text" name="societe" id="societe_input" class="form-control wizard-input" value="{{ $value('societe', $dossier && $dossier->entreprise ? $dossier->entreprise->nom : '') }}" required>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Date</label>
        <input type="date" name="date" id="date_input" class="form-control wizard-input" value="{{ $value('date', date('Y-m-d')) }}" required>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Référence (optionnel)</label>
        <input type="text" name="reference" id="reference_input" class="form-control wizard-input" value="{{ $value('reference', $dossier->reference_dossier ?? $dossier->ref ?? '') }}">
    </div>

    <div class="wizard-alert" style="margin-bottom:16px; font-size:0.85rem; color:#475569;">
        Le nom du déclarant et sa fonction sont repris automatiquement du signataire du dossier.
    </div>

    <div class="mb-3">
        <label class="wizard-label">Aperçu du modèle</label>
        <textarea id="templatePreview" name="template_content" class="form-control wizard-input" style="min-height:200px;font-family:monospace;white-space:pre-wrap;word-wrap:break-word"></textarea>
    </div>

    <script>
        (function(){
            const select = document.getElementById('template-select');
            const preview = document.getElementById('templatePreview');
            const fields = {
                societe: document.getElementById('societe_input'),
                date: document.getElementById('date_input'),
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
                    out = out.replace(re, val);
                });
                return out;
            }

            function updatePreview(){
                const replaced = replacePlaceholders(rawTemplate);
                preview.value = replaced;
            }

            select.addEventListener('change', async function(){
                const id = this.value;
                rawTemplate = '';
                preview.value = '';
                if (!id) return;
                try {
                    const res = await fetch(`{{ url('templates') }}/${id}/json`, { headers: {'X-Requested-With':'XMLHttpRequest'} });
                    if (!res.ok) throw new Error('Erreur');
                    const json = await res.json();
                    rawTemplate = json.content || '';
                    updatePreview();
                } catch (e) {
                    preview.value = 'Impossible de charger le modèle';
                }
            });

            Object.values(fields).forEach(f => f.addEventListener('input', updatePreview));
        })();
    </script>

</div>
