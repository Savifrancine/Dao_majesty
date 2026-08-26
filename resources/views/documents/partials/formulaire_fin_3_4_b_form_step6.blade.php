<div id="formulaire-fin-3-4-b-step6">
    @php
        $doc = $dossier->documents->firstWhere('type_document_id', $docId);
        $uploadedFiles = $doc && $doc->fichiers ? $doc->fichiers : collect();
    @endphp

    <div class="wizard-field" style="margin-bottom:16px;">
        <label class="wizard-label">Modèle de lettre de confirmation de la capacité financière</label>
        <p style="margin:0 0 10px 0; font-size:13px; color:#4b5563; line-height:1.5; max-width:680px;">Veuillez télécharger la lettre de confirmation de la capacité financière émise par l'établissement de crédit ou un organisme financier reconnu.</p>
    </div>

    <div class="wizard-field" style="margin-bottom:16px;">
        <label for="fin34b-files" class="wizard-label">Pièces jointes (Lettres de confirmation)</label>
        <input type="file" id="fin34b-files" name="fichiers[{{ $docId }}][]" class="form-control" multiple accept="image/*,application/pdf" style="margin-bottom:8px;">
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
