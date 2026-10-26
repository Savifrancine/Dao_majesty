<div id="qualification-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $stored = [];
        if ($doc && !empty($doc->content)) {
            $decoded = json_decode($doc->content, true);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $nombreMarches = old('nombre_marches', $stored['nombre_marches'] ?? 0);
        $marches = old('marches', $stored['marches'] ?? []);

        // Si aucun marché n'est encore saisi ici, on reprend ceux déjà renseignés
        // dans le Formulaire EXP – 4.1 (Expérience générale), pour ne pas avoir à
        // les ressaisir deux fois.
        if (!old('marches') && empty($marches)) {
            $exp41Doc = $dossier->documents->first(fn ($d) => $d->typeDocument && trim($d->typeDocument->nom) === 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services');
            if ($exp41Doc && !empty($exp41Doc->content)) {
                $expDecoded = json_decode($exp41Doc->content, true);
                if (is_array($expDecoded)) {
                    $expDeparts = $expDecoded['mois_depart'] ?? [];
                    $expIdents = $expDecoded['identification'] ?? [];
                    $count = max(count($expDeparts), count($expIdents));
                    for ($i = 0; $i < $count; $i++) {
                        $parsed = \App\Support\MarcheIdentification::parse(trim($expIdents[$i] ?? ''));
                        $annee = trim($expDeparts[$i] ?? '');
                        if ($annee === '' && $parsed['nom'] === '' && $parsed['reference'] === '') {
                            continue;
                        }
                        $marches[] = ['annee' => $annee, 'nom' => $parsed['nom'], 'reference' => $parsed['reference']];
                    }
                    if (!empty($marches) && empty($nombreMarches)) {
                        $nombreMarches = count($marches);
                    }
                }
            }
        }
        $marchesExecutes = $dossier->entreprise
            ? \App\Models\MarcheExecute::where('entreprise_id', $dossier->entreprise_id)->orderByDesc('id')->get()
            : collect();
    @endphp

    @if($doc && $marchesExecutes->isNotEmpty())
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:10px; margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap; align-items:flex-start;">
            <label for="marcheExecuteSelectQualif" style="font-size:13px; color:#1e40af; font-weight:600; margin-top:6px;">Importer des marchés déjà exécutés :</label>
            <select id="marcheExecuteSelectQualif" class="form-control" multiple size="{{ min(6, max(3, $marchesExecutes->count())) }}" style="max-width:420px;">
                @foreach($marchesExecutes as $m)
                    <option value="{{ $m->id }}">{{ \Illuminate\Support\Str::limit(trim($m->identification_marche) ?: ('Marché #' . $m->id), 80) }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.importMarcheExecuteQualif()">Importer la sélection</button>
            <div style="font-size:11px; color:#1e40af; flex-basis:100%;">Maintenez Ctrl (ou Cmd sur Mac) pour en sélectionner plusieurs. Ajoute une nouvelle ligne pré-remplie par marché ci-dessous (sans toucher aux lignes existantes) et remplit aussi le Formulaire EXP – 4.1 ; relie le contrat/bon de commande et l'attestation de bonne fin de chaque marché à ce dossier. Un marché déjà importé ici n'est jamais dupliqué.</div>
        </div>
        <script>
            window.importMarcheExecuteQualif = function () {
                const select = document.getElementById('marcheExecuteSelectQualif');
                if (!select) return;
                const selectedIds = Array.from(select.selectedOptions).map((o) => o.value);
                if (selectedIds.length === 0) return;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('dossiers.importMarcheExecute', $dossier->id) }}';
                form.style.display = 'none';

                const addField = (name, value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                };

                addField('_token', token);
                selectedIds.forEach((id) => addField('marche_execute_ids[]', id));

                document.body.appendChild(form);
                form.submit();
            };
        </script>
    @endif

    <div class="mb-3">
        <label class="wizard-label">Nombre de marchés similaires</label>
        <input type="number" name="nombre_marches" id="qualification_nombre_marches" class="form-control wizard-input" min="0" max="20" style="max-width:160px;" value="{{ $nombreMarches }}">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Marchés similaires (année, nom et référence)</label>
        <p style="margin:0 0 10px 0; font-size:12px; color:#6b7280;">Le nombre de lignes ci-dessous suit automatiquement la valeur saisie dans « Nombre de marchés similaires ».</p>
        <div id="qualification-marches-container"></div>
    </div>

    <script>
        (function(){
            const container = document.getElementById('qualification-marches-container');
            const nombreInput = document.getElementById('qualification_nombre_marches');
            const storedMarches = @json(is_array($marches) ? array_values($marches) : []);

            function currentValues(){
                const rows = container.querySelectorAll('.qualification-marche-row');
                const values = [];
                rows.forEach(function(row){
                    values.push({
                        annee: row.querySelector('[data-field="annee"]').value,
                        nom: row.querySelector('[data-field="nom"]').value,
                        reference: row.querySelector('[data-field="reference"]').value
                    });
                });
                return values;
            }

            function buildRow(index, annee, nom, reference){
                const row = document.createElement('div');
                row.className = 'qualification-marche-row';
                row.style.cssText = 'display:flex; gap:10px; margin-bottom:8px; align-items:flex-start;';
                row.innerHTML =
                    '<div style="flex:0 0 30px; padding-top:8px; font-weight:600;">' + (index + 1) + '.</div>' +
                    '<div style="flex:0 0 110px;">' +
                        '<input type="number" data-field="annee" class="form-control wizard-input" placeholder="Année" name="marches[' + index + '][annee]">' +
                    '</div>' +
                    '<div style="flex:1;">' +
                        '<textarea data-field="nom" class="form-control wizard-input" rows="1" placeholder="Nom du marché" name="marches[' + index + '][nom]" style="resize:vertical;"></textarea>' +
                    '</div>' +
                    '<div style="flex:1;">' +
                        '<textarea data-field="reference" class="form-control wizard-input" rows="1" placeholder="Référence du marché" name="marches[' + index + '][reference]" style="resize:vertical;"></textarea>' +
                    '</div>';
                row.querySelector('[data-field="annee"]').value = annee || '';
                row.querySelector('[data-field="nom"]').value = nom || '';
                row.querySelector('[data-field="reference"]').value = reference || '';
                return row;
            }

            function renderRows(count, preserved){
                container.innerHTML = '';
                for (let i = 0; i < count; i++) {
                    const existing = preserved[i] || {};
                    container.appendChild(buildRow(i, existing.annee, existing.nom, existing.reference));
                }
            }

            function syncRows(){
                let count = parseInt(nombreInput.value, 10);
                if (isNaN(count) || count < 0) count = 0;
                if (count > 20) count = 20;
                const preserved = currentValues();
                renderRows(count, preserved);
            }

            renderRows(storedMarches.length, storedMarches);
            if (parseInt(nombreInput.value, 10) !== storedMarches.length) {
                syncRows();
            }

            nombreInput.addEventListener('input', syncRows);
        })();
    </script>
</div>
