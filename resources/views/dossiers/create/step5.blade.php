@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-green: #10b981;
        --dark-green: #059669;
        --light-green: #34d399;
        --emerald: #047857;
        --mint: #d1fae5;
        --green-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --green-glow: 0 0 30px rgba(16, 185, 129, 0.25);
    }

    html,
    body {
        height: 100%;
        margin: 0;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%);
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
        animation: rotateBackground 30s linear infinite;
        z-index: 0;
    }

    @keyframes rotateBackground {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .floating-particles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .particle {
        position: absolute;
        width: 8px;
        height: 8px;
        background: var(--primary-green);
        border-radius: 50%;
        opacity: 0.15;
        animation: floatParticle 20s infinite ease-in-out;
    }

    .particle:nth-child(1) { left: 12%; animation-delay: 0s; animation-duration: 15s; }
    .particle:nth-child(2) { left: 24%; animation-delay: 2s; animation-duration: 18s; }
    .particle:nth-child(3) { left: 36%; animation-delay: 4s; animation-duration: 12s; }
    .particle:nth-child(4) { left: 48%; animation-delay: 1s; animation-duration: 16s; }
    .particle:nth-child(5) { left: 60%; animation-delay: 3s; animation-duration: 14s; }
    .particle:nth-child(6) { left: 72%; animation-delay: 5s; animation-duration: 19s; }
    .particle:nth-child(7) { left: 84%; animation-delay: 2.5s; animation-duration: 17s; }
    .particle:nth-child(8) { left: 92%; animation-delay: 4.5s; animation-duration: 13s; }

    @keyframes floatParticle {
        0%, 100% {
            transform: translateY(100vh) scale(0);
            opacity: 0;
        }
        10% { opacity: 0.2; }
        90% { opacity: 0.2; }
        50% {
            transform: translateY(-20vh) scale(1.5);
            opacity: 0.3;
        }
    }

    .wizard-wrapper {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 14px;
        position: relative;
        z-index: 1;
    }

    .wizard-orbit {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
        animation: orbit 25s linear infinite;
        pointer-events: none;
        z-index: 0;
    }

    .wizard-orbit.orbit-1 {
        width: 480px;
        height: 480px;
        top: -140px;
        right: -140px;
        animation-duration: 20s;
    }

    .wizard-orbit.orbit-2 {
        width: 320px;
        height: 320px;
        bottom: -90px;
        left: -90px;
        animation-direction: reverse;
        animation-duration: 28s;
        background: radial-gradient(circle, rgba(5, 150, 105, 0.12) 0%, transparent 70%);
    }

    @keyframes orbit {
        0% { transform: rotate(0deg) scale(1); opacity: 1; }
        50% { transform: rotate(180deg) scale(1.1); opacity: 0.8; }
        100% { transform: rotate(360deg) scale(1); opacity: 1; }
    }

    .wizard-card {
        width: 100%;
        max-width: 860px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 22px;
        box-shadow:
            0 25px 60px rgba(16, 185, 129, 0.2),
            0 10px 30px rgba(0, 0, 0, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(16, 185, 129, 0.2);
        overflow: hidden;
        position: relative;
        animation: cardIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        z-index: 1;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(40px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .wizard-header {
        padding: 22px 24px 14px;
        text-align: left;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.03));
        border-bottom: 1px solid rgba(16, 185, 129, 0.15);
        position: relative;
    }

    .wizard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--green-gradient);
        animation: slideWidth 2s ease-out;
    }

    @keyframes slideWidth {
        from { width: 0; }
        to { width: 100%; }
    }

    .wizard-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 50px;
        background: rgba(16, 185, 129, 0.12);
        color: var(--emerald);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }

    .wizard-title {
        margin: 12px 0 6px;
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        background: linear-gradient(135deg, #047857 0%, #10b981 50%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .wizard-subtitle {
        color: #047857;
        font-weight: 500;
        font-size: 0.88rem;
        margin: 0;
    }

    .wizard-body {
        padding: 18px 24px 22px;
    }

    .wizard-step {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        font-size: 0.85rem;
        color: #475569;
    }

    .wizard-progress {
        flex: 1;
        height: 6px;
        background: rgba(16, 185, 129, 0.12);
        border-radius: 999px;
        overflow: hidden;
    }

    .wizard-progress span {
        display: block;
        height: 100%;
        background: var(--green-gradient);
    }

    .doc-option {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px;
        display: flex;
        gap: 12px;
        align-items: center;
        transition: all 0.3s ease;
        background: #f8fafc;
        cursor: pointer;
    }

    .checkbox-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        min-width: 28px;
        height: 28px;
    }

    .doc-option input[type="checkbox"] {
        position: absolute;
        width: 100%;
        height: 100%;
        margin: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }

    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #ffffff;
        transition: all 0.2s ease;
        position: relative;
    }

    .doc-option input[type="checkbox"]:checked + .checkbox-custom {
        background: #10b981;
        border-color: #10b981;
    }

    .doc-option input[type="checkbox"]:checked + .checkbox-custom::after {
        content: '';
        position: absolute;
        left: 5px;
        top: 1px;
        width: 6px;
        height: 12px;
        border: solid #ffffff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .doc-option input[type="checkbox"]:disabled + .checkbox-custom {
        background: #f1f5f9;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    .doc-option + .doc-option {
        margin-top: 12px;
    }

    .doc-option:hover {
        border-color: rgba(16, 185, 129, 0.5);
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.12);
    }

    .doc-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
    }

    .doc-meta {
        font-size: 0.82rem;
        color: #64748b;
    }

    .wizard-alert {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background: rgba(16, 185, 129, 0.08);
        padding: 12px 14px;
        color: #0f172a;
    }


    .wizard-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }

    .btn-ghost,
    .btn-primary-custom {
        border-radius: 999px;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }

    .btn-ghost {
        border: 2px solid rgba(16, 185, 129, 0.35);
        background: white;
        color: var(--emerald);
    }

    .btn-ghost:hover {
        background: rgba(16, 185, 129, 0.08);
        border-color: var(--primary-green);
        transform: translateY(-2px);
    }

    .btn-primary-custom {
        border: none;
        background: var(--green-gradient);
        color: white;
        box-shadow: var(--green-glow);
    }

    .btn-primary-custom:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    .btn-primary-custom:hover {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 14px 30px rgba(16, 185, 129, 0.35);
    }

    @media (max-width: 768px) {
        .wizard-actions {
            flex-direction: column;
        }
    }
</style>

<div class="floating-particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="wizard-wrapper">
    <div class="wizard-orbit orbit-1"></div>
    <div class="wizard-orbit orbit-2"></div>

    <div class="wizard-card">
        <div class="wizard-header">
            <div class="wizard-badge">Etape 5 sur 6</div>
            <h1 class="wizard-title">Selection des documents</h1>
            <p class="wizard-subtitle">Selectionnez les documents qui composeront le sommaire.</p>
        </div>

        <div class="wizard-body">
            <div class="wizard-step">
                <span>Choix des documents</span>
                <div class="wizard-progress"><span style="width: 83.3%;"></span></div>
            </div>

            <form action="{{ route('dossiers.step6', $dossier->id) }}" method="POST" id="documentsForm">
                @csrf
                <input type="hidden" name="dossier_id" value="{{ $dossier->id }}">

                <div class="wizard-field" style="margin-bottom:16px;">
                    <label for="signataire_id" class="wizard-label">Signataire (pour déclaration de garantie)</label>
                    <select id="signataire_id" name="signataire_id" class="form-control wizard-input">
                        <option value="">-- Aucun --</option>
                        @foreach($signataires as $signataire)
                            <option value="{{ $signataire->id }}"
                                {{ old('signataire_id') == $signataire->id ? 'selected' : '' }}>
                                {{ $signataire->nom }} {{ $signataire->prenom }}{{ $signataire->fonction ? ' – ' . $signataire->fonction : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="wizard-field" style="margin-bottom:16px;">
                    <label for="documentSearch" class="wizard-label">Rechercher un document</label>
                    <input id="documentSearch" type="search" class="form-control wizard-input" placeholder="Rechercher un document...">
                </div>

                @php
                    $docsByName = $documents->keyBy('nom');
                    $selectedTypeIds = $selectedTypeIds ?? [];
                    $pieceNames = [
                        "Déclaration de garantie d'offre",
                        "Formulaire de qualification",
                        "Lettre de soumission",
                        "RCCM",
                        "Relevé d'identité bancaire (RIB)",
                        "Formulaire de divulgation des bénéficiaires effectifs",
                        "Pièce d'identité du premier responsable",
                        "Déclaration de l'autorité contractante",
                        "Déclaration des Conflits d'Intérêts",
                        "Procuration spéciale",
                        "Fiche technique de chaque article, délivrée par le fabricant",
                        "Copie de l'arrêté du Ministre de la Santé portant autorisation d'importation, de détention et de vente des équipements médicaux",
                        "Attestation d'identification de statut",
                        "Attestation de visite de site",
                        "Attestation de bonne fin",
                        "Bon de commande et contrats",
                        "Preuves de propriété des matériels adéquats nécessaire à la bonne exécution du marché",
                        "Attestation / Preuve de vente des équipements ou des pièces de recharge",
                        "Attestation / Certificat de formation ou de qualifications en maintenance(sur au moins une équipements)",
                        "Etats financiers certifiés",
                        "Attestation de capacité financière",
                        "Copie legalisee de l'Identifiant Fiscal Unique (IFU)",
                        "Attestation de non-faillite datant de moins de trois (03) mois",
                        "Attestation d'imposition ou de situation fiscale en cours de validite",
                        "Attestation de regularite a la CNSS",
                        "Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat",
                        "Formulaire FIN – 3.1 Situation financière",
                        "Formulaire FIN 3.3",
                        "Formulaire FIN 3.4 (a) Modèle d'attestation de capacité financière",
                        "Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière",
                        "Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours",
                        "Formulaire EXP – 4.1 : Expérience générale de fournitures/services",
                        "Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services",
                        "Formulaire EXP – 4.2 a) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)",
                        "Formulaire EXP – 4.2 b)  Expérience spécifique de fournitures",
                        "Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)",
                        "Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d'antécédents de litiges",
                        "Formulaire MAT",
                        "Formulaire PER",
                        "Liste du personnel affecté à l'exécution du marché",
                        "Chiffre d'affaires annuel moyen des activités de services",
                        "Attestation de non-exclusion de la commande publique",
                        "Engagement du soumissionnaire à respecter le code d'éthique et de déontologie",
                        "Engagement a respecter le code d'ethique et de deontologie de la commande publique",
                        "Attestation de non-condamnation pour fraude, corruption ou fausse declaration",
                        "Attestation de nationalite ou document de constitution legale de l'entreprise",
                        "Statuts de la societe et PV de nomination du gerant",
                        "Copie du quitus fiscal",
                        "Attestation de situation reguliere vis-a-vis des organismes de credit",
                        "Bordereau prix unitaire",
                        "Bordereau des prix unitaires pour les prestations de services",
                        "Bordereau des prix pour les fournitures à importer",
                        "Bordereau des prix des fournitures, déjà importées",
                        "Bordereau des prix pour les fournitures fabriquées au Bénin",
                        "Bordereau des prix et calendrier d'exécution des services connexes",
                        "Listes des services connexes et calendrier de réalisation",
                        "Listes des Fournitures et Calendrier de livraison",
                        "Tableau de résumé des bordereaux de prix",
                        "Cadres de sous détails des prix unitaire",
                        "Programme d'activités",
                        "Plan de charge",
                        "Méthodes d'exécution",
                        "Calendrier d'exécution",
                        "Description technique des fournitures/services",
                    ];

                    $pieceNames = array_values(array_unique(array_merge(
                        $pieceNames,
                        App\Models\TypeDocument::whereIn('type_formulaire', ['libre', 'piece_jointe'])->pluck('nom')->all()
                    )));
                @endphp

                <div>
                    @foreach($pieceNames as $pieceName)
                        @php
                            $doc = $docsByName->get($pieceName);
                            $isSelected = $doc && in_array($doc->id, $selectedTypeIds, true);
                        @endphp
                        @continue($isSelected)
                        <label class="doc-option" for="doc_{{ $doc?->id ?? Str::slug($pieceName) }}">
                            <div class="checkbox-wrapper">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="documents[]"
                                    value="{{ $doc?->id }}"
                                    id="doc_{{ $doc?->id ?? Str::slug($pieceName) }}"
                                    @if(!$doc) disabled @endif
                                >
                                <span class="checkbox-custom"></span>
                            </div>
                            <div class="doc-content" style="flex: 1;">
                                <div class="doc-title">
                                    {{ $pieceName }}
                                    @if($pieceName === 'Lettre de soumission')
                                        (datee, signee, cachetee)
                                    @endif
                                </div>
                                <div class="doc-meta">
                                    @if($doc)
                                        Piece administrative
                                    @else
                                        Piece non disponible
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>



                <div id="selectedList" class="wizard-alert" style="display: none; margin-top: 16px;">
                    <strong>Pieces selectionnees :</strong>
                    <ol id="documentsList"></ol>
                </div>

                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost" onclick="history.back()">Retour</button>
                    <button type="submit" class="btn btn-primary-custom" id="continueBtn" disabled>Continuer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const checkboxes = document.querySelectorAll('input[name="documents[]"]');
const selectedList = document.getElementById('selectedList');
const documentsList = document.getElementById('documentsList');
const continueBtn = document.getElementById('continueBtn');
const searchInput = document.getElementById('documentSearch');
const docOptions = Array.from(document.querySelectorAll('.doc-option'));

function updateList() {
    const selected = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => {
            const docOption = cb.closest('.doc-option');
            const titleEl = docOption ? docOption.querySelector('.doc-title') : null;
            return {
                id: cb.value,
                label: titleEl ? titleEl.textContent.trim() : cb.value
            };
        });

    if (selected.length === 0) {
        selectedList.style.display = 'none';
        continueBtn.disabled = true;
    } else {
        documentsList.innerHTML = selected
            .map(s => `<li>${s.label}</li>`)
            .join('');
        selectedList.style.display = 'block';
        continueBtn.disabled = false;
    }
}

function updateSearch() {
    const query = searchInput.value.trim().toLowerCase();

    docOptions.forEach(option => {
        const title = option.querySelector('.doc-title')?.textContent.trim().toLowerCase() ?? '';
        const meta = option.querySelector('.doc-meta')?.textContent.trim().toLowerCase() ?? '';
        const matches = title.includes(query) || meta.includes(query);
        option.style.display = matches ? 'flex' : 'none';
    });
}

checkboxes.forEach(cb => cb.addEventListener('change', updateList));
searchInput.addEventListener('input', updateSearch);
</script>
@endsection
