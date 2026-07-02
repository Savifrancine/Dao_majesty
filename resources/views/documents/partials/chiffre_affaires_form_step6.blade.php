<div id="chiffre-affaires-step6">
    <div class="wizard-field" style="margin-bottom:18px;">
        <label class="wizard-label">Ajouter les chiffres d'affaires par année</label>
        
        <div id="chiffres-input-container" style="margin-bottom: 18px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #f9fafb;">
            <div id="chiffres-list"></div>
            <button type="button" id="add-chiffre-row" class="btn btn-sm btn-outline-secondary" style="margin-top: 10px;">
                + Ajouter une année
            </button>
        </div>

        <div class="wizard-field" style="margin-bottom:18px;">
            <label class="wizard-label">Sélectionnez les années à inclure dans le PDF</label>
            <div id="years-selection">
                @if($globalChiffres->isEmpty())
                    <div class="alert alert-info" id="no-years-message">
                        Aucun chiffre d'affaires enregistré dans la liste globale. <a href="{{ route('chiffres.index') }}">Allez ajouter des chiffres</a>.
                    </div>
                @else
                    <div class="row g-2">
                        @foreach($globalChiffres as $entry)
                            <div class="col-md-4">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="years[]" value="{{ $entry->annee }}" checked class="form-check-input">
                                    <span>{{ $entry->annee }} — {{ number_format($entry->montant, 0, ',', ' ') }} {{ $entry->monnaie }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-muted">Le PDF affichera les années sélectionnées et les montants enregistrés.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .chiffre-row {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: flex-end;
    }
    .chiffre-row input {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 13px;
    }
    .chiffre-row input[type="number"]:first-of-type {
        flex: 2;
    }
    .chiffre-row input[type="number"]:not(:first-of-type) {
        flex: 1;
    }
    .chiffre-row input[type="text"] {
        flex: 0 0 120px;
    }
    .chiffre-row button {
        padding: 6px 12px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('chiffres-list');
    const addBtn = document.getElementById('add-chiffre-row');
    const dossierIdValue = {{ $dossier->id }};
    let rowCount = 0;

    // Ajouter une ligne pour entrer les chiffres
    function addChiffreRow(annee = '', montant = '', monnaie = 'XOF') {
        const row = document.createElement('div');
        row.className = 'chiffre-row';
        row.dataset.rowId = rowCount++;
        
        row.innerHTML = `
            <input type="number" placeholder="Année (ex: 2024)" class="annee-input" value="${annee}" min="1900" max="2099">
            <input type="number" placeholder="Montant" class="montant-input" value="${montant}" step="0.01" min="0">
            <input type="text" placeholder="Monnaie (XOF)" class="monnaie-input" value="${monnaie}">
            <button type="button" class="delete-row-btn">Supprimer</button>
        `;

        const deleteBtn = row.querySelector('.delete-row-btn');
        deleteBtn.addEventListener('click', function() {
            row.remove();
        });

        container.appendChild(row);
    }

    // Ajouter une première ligne par défaut
    addChiffreRow();

    // Bouton pour ajouter une ligne
    addBtn.addEventListener('click', function() {
        addChiffreRow();
    });

    // Fonction pour sauvegarder les chiffres (appelée avant submit du formulaire)
    window.saveChiffresAffaires = async function() {
        const rows = document.querySelectorAll('.chiffre-row');
        const chiffres = [];

        for (const row of rows) {
            const annee = parseInt(row.querySelector('.annee-input').value);
            const montant = parseFloat(row.querySelector('.montant-input').value);
            const monnaie = row.querySelector('.monnaie-input').value;

            if (annee && montant >= 0) {
                chiffres.push({ annee, montant, monnaie });
            }
        }

        if (chiffres.length === 0) {
            alert('Veuillez ajouter au moins une année et un montant');
            return false;
        }

        // Envoyer au serveur
        try {
            const response = await fetch('{{ route("dossiers.save-chiffres") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    dossier_id: dossierIdValue,
                    chiffres: chiffres
                })
            });

            const result = await response.json();
            if (result.success) {
                // Actualiser la sélection des années
                location.reload();
                return false; // Empêcher le formulaire de se soumettre pour l'instant
            } else {
                alert('Erreur: ' + (result.message || 'Impossible de sauvegarder'));
                return false;
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur lors de la sauvegarde');
            return false;
        }
    }
});
</script>
