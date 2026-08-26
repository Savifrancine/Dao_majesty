<div id="formulaire-candidat-step6">
    <div class="wizard-field" style="margin-bottom: 18px;">
        <label class="wizard-label">Utiliser les informations existantes de l'entreprise ?</label>
        <div class="d-flex gap-3">
            <label class="form-check-label">
                <input type="radio" name="use_existing_info" value="yes" checked class="form-check-input"> Oui
            </label>
            <label class="form-check-label">
                <input type="radio" name="use_existing_info" value="no" class="form-check-input"> Non
            </label>
        </div>
    </div>

    <div id="existingEntrepriseInfo" class="card mb-3" style="padding:16px;">
        <div style="font-weight:700; margin-bottom:10px;">Informations actuelles de l'entreprise</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div><strong>Nom du candidat :</strong><br>{{ $dossier->entreprise->nom }}</div>
            <div><strong>Pays :</strong><br>{{ $dossier->entreprise->pays ?? 'N/A' }}</div>
            <div><strong>Numéro d'identification :</strong><br>{{ $dossier->entreprise->rccm ?? 'N/A' }}</div>
            <div><strong>Année d'enregistrement :</strong><br>{{ $dossier->entreprise->annee_enregistrement ?? 'N/A' }}</div>
            <div style="grid-column: span 2;"><strong>Adresse officielle :</strong><br>{{ $dossier->entreprise->adresse_officielle ?? $dossier->entreprise->adresse ?? 'N/A' }}</div>
            <div><strong>Responsable :</strong><br>{{ $dossier->entreprise->responsable ?? 'N/A' }}</div>
            <div><strong>Fonction du responsable :</strong><br>{{ $dossier->entreprise->fonction_responsable ?? 'N/A' }}</div>
            <div><strong>Téléphone :</strong><br>{{ $dossier->entreprise->telephone ?? 'N/A' }}</div>
            <div><strong>Email :</strong><br>{{ $dossier->entreprise->email ?? 'N/A' }}</div>
        </div>
    </div>

    <div id="manualCandidateForm" style="display:none;">
        <div class="row">
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="nom_candidat" class="wizard-label">Nom du candidat *</label>
                    <input type="text" class="form-control wizard-input" id="nom_candidat" name="nom_candidat">
                </div>
            </div>
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="pays_candidat" class="wizard-label">Pays *</label>
                    <input type="text" class="form-control wizard-input" id="pays_candidat" name="pays_candidat">
                </div>
            </div>
        </div>
        <div class="wizard-field">
            <label for="groupement_membres" class="wizard-label">En cas de groupement, noms des membres</label>
            <input type="text" class="form-control wizard-input" id="groupement_membres" name="groupement_membres">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="identification_nationale" class="wizard-label">Numéro d'identification nationale</label>
                    <input type="text" class="form-control wizard-input" id="identification_nationale" name="identification_nationale">
                </div>
            </div>
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="annee_enregistrement" class="wizard-label">Année d'enregistrement</label>
                    <input type="number" class="form-control wizard-input" id="annee_enregistrement" name="annee_enregistrement" min="1900" max="2100">
                </div>
            </div>
        </div>
        <div class="wizard-field">
            <label for="adresse_officielle" class="wizard-label">Adresse officielle</label>
            <input type="text" class="form-control wizard-input" id="adresse_officielle" name="adresse_officielle">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="nom_representant" class="wizard-label">Nom du représentant *</label>
                    <input type="text" class="form-control wizard-input" id="nom_representant" name="nom_representant">
                </div>
            </div>
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="fonction_representant" class="wizard-label">Fonction du représentant</label>
                    <input type="text" class="form-control wizard-input" id="fonction_representant" name="fonction_representant">
                </div>
            </div>
        </div>
        <div class="wizard-field">
            <label for="adresse_representant" class="wizard-label">Adresse du représentant</label>
            <input type="text" class="form-control wizard-input" id="adresse_representant" name="adresse_representant">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="telephone_representant" class="wizard-label">Téléphone du représentant</label>
                    <input type="tel" class="form-control wizard-input" id="telephone_representant" name="telephone_representant">
                </div>
            </div>
            <div class="col-md-6">
                <div class="wizard-field">
                    <label for="email_representant" class="wizard-label">Email du représentant</label>
                    <input type="email" class="form-control wizard-input" id="email_representant" name="email_representant">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const useExistingRadios = document.querySelectorAll('input[name="use_existing_info"]');
    const existingInfoBlock = document.getElementById('existingEntrepriseInfo');
    const manualFormBlock = document.getElementById('manualCandidateForm');

    function toggleCandidateForm() {
        const useExisting = document.querySelector('input[name="use_existing_info"]:checked').value === 'yes';
        existingInfoBlock.style.display = useExisting ? 'block' : 'none';
        manualFormBlock.style.display = useExisting ? 'none' : 'block';
    }

    useExistingRadios.forEach(radio => radio.addEventListener('change', toggleCandidateForm));
    toggleCandidateForm();
</script>
