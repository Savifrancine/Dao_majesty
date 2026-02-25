<div id="declaration-partial">
    <div class="mb-3">
        <label class="form-label">Société / Fournisseur</label>
        <input type="text" name="societe" class="form-control" value="{{ $dossier && $dossier->entreprise ? $dossier->entreprise->nom : '' }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Nom du déclarant</label>
        <input type="text" name="declarant" class="form-control" value="">
    </div>

    <div class="mb-3">
        <label class="form-label">Fonction</label>
        <input type="text" name="fonction" class="form-control" value="">
    </div>

    <div class="mb-3">
        <label class="form-label">Référence (optionnel)</label>
        <input type="text" name="reference" class="form-control" value="{{ $dossier->reference_dossier ?? $dossier->ref ?? '' }}">
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <button type="button" class="btn btn-secondary" onclick="closeDeclarationModal()">Annuler</button>
        <button type="button" class="btn btn-primary" onclick="saveDeclarationPartial({{ $docId ?? 'null' }})">Enregistrer et fermer</button>
    </div>

    <script>
        function saveDeclarationPartial(docId) {
            const container = document.getElementById('declaration-partial');
            const inputs = container.querySelectorAll('input[name]');
            const data = {};
            inputs.forEach(i => data[i.name] = i.value);

            if (!window.step5SaveDeclaration) {
                console.warn('step5SaveDeclaration not defined in parent page');
                return;
            }

            window.step5SaveDeclaration(docId, data);
        }

        function closeDeclarationModal() {
            if (window.closeDeclarationModalFromPartial) {
                window.closeDeclarationModalFromPartial();
            }
        }
    </script>
</div>
