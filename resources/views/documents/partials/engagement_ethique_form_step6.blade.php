<div id="engagement-ethique-step6">
    <div class="mb-3">
        <label class="wizard-label">Choisir un modèle</label>
        <select id="engagement-template-select" class="form-control wizard-input">
            <option value="">-- Aucun --</option>
            @foreach(App\Models\Template::where('type', 'engagement')->get() as $t)
                <option value="{{ $t->id }}">{{ $t->nom }}</option>
            @endforeach
        </select>
        <small style="color:#64748b;">Sélectionner un modèle remplace le texte ci-dessous par son contenu. Vous pouvez ensuite le modifier avant d'enregistrer. Les modèles se gèrent dans la page "Modèles".</small>
    </div>

    <label class="wizard-label" for="content_{{ $docId }}">Contenu</label>
    <textarea name="content" id="content_{{ $docId }}" class="form-control wizard-input" rows="12" placeholder="Saisir le texte de l'engagement...">{{ old('content', $existingContent) }}</textarea>

    <script>
        (function(){
            const select = document.getElementById('engagement-template-select');
            const contentField = document.getElementById('content_{{ $docId }}');

            select.addEventListener('change', async function(){
                const id = this.value;
                if (!id) return;
                try {
                    const res = await fetch(`{{ url('templates') }}/${id}/json`, { headers: {'X-Requested-With':'XMLHttpRequest'} });
                    if (!res.ok) throw new Error('Erreur');
                    const json = await res.json();
                    if (contentField.value.trim() !== '' && !confirm('Remplacer le contenu actuel par celui du modèle ?')) {
                        return;
                    }
                    contentField.value = json.content || '';
                } catch (e) {
                    alert('Impossible de charger le modèle');
                }
            });
        })();
    </script>
</div>
