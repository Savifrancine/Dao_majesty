<div id="formulaire-fin-3-4-a-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $uploadedFiles = $doc && $doc->fichiers ? $doc->fichiers : collect();
    @endphp

    <div class="wizard-field" style="margin-bottom:16px;">
        <label class="wizard-label">Modèle d'attestation de capacité financière</label>
        <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Veuillez télécharger le modèle d'attestation de capacité financière émis par l'établissement de crédit ou un organisme financier reconnu.</p>
    </div>

    <div class="wizard-field" style="margin-bottom:16px;">
        <label for="fin34a-files" class="wizard-label">Pièces jointes (Attestations de capacité financière)</label>
        <input type="file" id="fin34a-files" name="fichiers[{{ $docId }}][]" class="form-control" multiple accept="image/*,application/pdf" style="margin-bottom:8px;">
        <div style="font-size:12px; color:#666;">Formats acceptés : images (PNG, JPG, GIF) et PDF</div>
    </div>

    @if($uploadedFiles->isNotEmpty())
        <div class="wizard-field" style="margin-bottom:16px; background:#f0f9ff; padding:12px; border-radius:4px;">
            <label class="wizard-label" style="margin-bottom:8px;">Fichiers actuellement uploadés</label>
            <div style="font-size:12px;">
                @foreach($uploadedFiles as $f)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px; background:#fff; margin-bottom:4px; border:1px solid #ddd; border-radius:3px;">
                        <span>{{ basename($f->chemin_fichier) }}</span>
                        <span style="color:#666; font-size:11px;">Téléversé</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
