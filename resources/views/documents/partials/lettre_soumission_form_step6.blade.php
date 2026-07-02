<div id="lettre-soumission-step6">
    <div class="mb-3">
        <label class="wizard-label">Date</label>
        <input type="date" name="date" class="form-control wizard-input" value="{{ old('date', date('Y-m-d')) }}" required>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Référence DRP / N°</label>
        <input type="text" name="drp_number" class="form-control wizard-input" value="{{ $dossier->ref ?? '' }}">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Destinataire (À)</label>
        <input type="text" name="destinataire" class="form-control wizard-input" value="{{ $dossier->destinataires ?? '' }}">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Introduction</label>
        <textarea name="introduction" class="form-control wizard-input" rows="3">Nous, les soussignés attestons que :</textarea>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Point a)</label>
        <textarea name="point_a" class="form-control wizard-input" rows="3">Nous avons examiné le Dossier de demande de renseignements et de prix ...</textarea>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Point b)</label>
        <textarea name="point_b" class="form-control wizard-input" rows="3">Nous nous engageons à fournir ou exécuter conformément au Dossier ...</textarea>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Point c) — Montant total en chiffres</label>
        <input type="text" name="montant_chiffres" class="form-control wizard-input" value="">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Point c) — Montant total en lettres</label>
        <input type="text" name="montant_lettres" class="form-control wizard-input" value="">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Point d) — TVA (valeur)</label>
        <input type="text" name="tva_valeur" class="form-control wizard-input" value="">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Autres rubriques / rabais</label>
        <textarea name="rabais" class="form-control wizard-input" rows="2"></textarea>
    </div>

    <div class="mb-3">
        <label class="wizard-label">Nom du signataire</label>
        <input type="text" name="signataire_nom" class="form-control wizard-input" value="{{ $dossier->entreprise->responsable ?? '' }}">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Fonction du signataire</label>
        <input type="text" name="signataire_fonction" class="form-control wizard-input" value="{{ $dossier->entreprise->fonction_responsable ?? '' }}">
    </div>

    <div class="mb-3">
        <label class="wizard-label">Signature (image, optionnel)</label>
        <input type="file" name="signature" accept="image/*" class="form-control wizard-input">
    </div>

</div>
