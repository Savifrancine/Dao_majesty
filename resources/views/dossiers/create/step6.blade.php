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
        max-width: 980px;
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

    .wizard-alert {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background: rgba(16, 185, 129, 0.08);
        padding: 12px 14px;
        color: #0f172a;
    }

    .wizard-body .card {
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.08);
    }

    .wizard-body .card-header {
        background: rgba(16, 185, 129, 0.08);
        border-bottom: 1px solid rgba(16, 185, 129, 0.15);
        font-weight: 700;
    }

    .wizard-body .card-body {
        background: white;
    }

    .wizard-label {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        display: block;
        font-size: 0.85rem;
    }

    .wizard-input {
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        padding: 10px 12px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        color: #0f172a;
        -webkit-text-fill-color: initial;
    }

    .wizard-input:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        background: white;
        color: #0f172a;
        -webkit-text-fill-color: initial;
    }

    .wizard-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }

    .btn-ghost,
    .btn-primary-custom,
    .btn-success-custom {
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

    .btn-success-custom {
        border: none;
        background: linear-gradient(135deg, #0f766e, #10b981);
        color: white;
        box-shadow: var(--green-glow);
    }

    .btn-primary-custom:hover,
    .btn-success-custom:hover {
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
            <div class="wizard-badge">Etape 6 sur 6</div>
            <h1 class="wizard-title">Pieces jointes</h1>
            <p class="wizard-subtitle">Ajoutez les pieces selectionnees.</p>
        </div>

        <div class="wizard-body">
            <div class="wizard-step">
                <span>Depot des pieces</span>
                <div class="wizard-progress"><span style="width: 100%;"></span></div>
            </div>

            <div class="wizard-alert">
                <strong>{{ $dossier->nom_dossier }}</strong> - {{ $dossier->typeDossier->nom }}
            </div>

            @if(session('error'))
                <div class="alert alert-danger" style="margin-bottom:16px;">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom:16px;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Ordre des pieces</h6>
                </div>
                <div class="card-body">
                    <ol>
                        @foreach($uploadQueue as $doc)
                            <li>
                                {{ $doc->nom }}
                                @if($loop->index < $currentIndex)
                                    <span class="text-success">(televerse)</span>
                                @elseif($loop->index === $currentIndex)
                                    <span class="text-primary">(en cours)</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <form action="{{ route('dossiers.step6', ['dossierId' => $dossier->id, 'current_index' => $currentIndex]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="upload_step" value="1">
                <input type="hidden" name="current_index" value="{{ $currentIndex }}">
                <input type="hidden" name="current_document_id" value="{{ $currentDocument->id }}">
                @foreach($selectedDocumentIds as $docId)
                    <input type="hidden" name="documents[]" value="{{ $docId }}">
                @endforeach

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Piece {{ $currentIndex + 1 }} / {{ $totalCount }}</h6>
                    </div>
                        <div class="card-body">
                            <label class="wizard-label">{{ $currentDocument->nom }}</label>

                            @php
                                // Pièces "bordereau" partageant une colonne Désignation : si l'une
                                // d'elles est déjà remplie dans ce dossier, on pré-remplit les autres
                                // avec les mêmes désignations tant qu'elles sont encore vides.
                                $bordereauFamilyNames = [
                                    'Bordereau prix unitaire',
                                    'Bordereau des prix pour les fournitures à importer',
                                    'Bordereau des prix des fournitures, déjà importées',
                                    'Bordereau des prix pour les fournitures fabriquées au Bénin',
                                    'Bordereau des prix et calendrier d\'exécution des services connexes',
                                    'Listes des services connexes et calendrier de réalisation',
                                    'Listes des Fournitures et Calendrier de livraison',
                                    'Cadres de sous détails des prix unitaire',
                                    'Programme d\'activités',
                                    'Méthodes d\'exécution',
                                    'Calendrier d\'exécution',
                                    'Description technique des fournitures/services',
                                ];

                                $sharedDesignationPrefill = collect();
                                if (in_array(trim($currentDocument->nom), $bordereauFamilyNames, true)) {
                                    $bordereauSiblingCandidates = $dossier->documents
                                        ->filter(function ($d) use ($currentDocument, $bordereauFamilyNames) {
                                            return $d->typeDocument
                                                && $d->type_document_id !== $currentDocument->id
                                                && in_array(trim($d->typeDocument->nom), $bordereauFamilyNames, true)
                                                && $d->bordereau->contains(fn ($b) => $b->lignes->isNotEmpty());
                                        })
                                        ->sortBy(function ($d) use ($bordereauFamilyNames) {
                                            return array_search(trim($d->typeDocument->nom), $bordereauFamilyNames, true);
                                        });

                                    // Préfère une source qui a aussi renseigné quantité et date
                                    // (une simple "Bordereau prix unitaire", plus sommaire, ne les
                                    // contient pas et ne doit pas priver les autres champs de pré-remplissage).
                                    $bordereauSiblingSource = $bordereauSiblingCandidates->first(function ($d) {
                                        return $d->bordereau->contains(function ($b) {
                                            return $b->lignes->contains(function ($l) {
                                                return !empty($l->quantite) && !empty($l->date_prestation);
                                            });
                                        });
                                    }) ?? $bordereauSiblingCandidates->first();

                                    if ($bordereauSiblingSource) {
                                        $sharedDesignationPrefill = $bordereauSiblingSource->bordereau->map(function ($bordereau) {
                                            return [
                                                'titre' => $bordereau->titre,
                                                'lignes' => $bordereau->lignes->map(function ($ligne) {
                                                    return [
                                                        'designation' => $ligne->designation,
                                                        'unite_physique' => $ligne->unite_physique,
                                                        'quantite' => $ligne->quantite,
                                                        'prix_unitaire' => $ligne->prix_unitaire,
                                                        'site' => $ligne->site,
                                                        'date_prestation' => $ligne->date_prestation,
                                                    ];
                                                })->toArray(),
                                            ];
                                        });
                                    }
                                }
                            @endphp

                            <script src="{{ asset('js/table-import.js') }}"></script>

                            @if(App\Models\TypeDocument::isReferenceLineName($currentDocument->nom) || $currentDocument->type_formulaire === 'libre')
                                @php
                                    $dossierDocumentForRef = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $refModelValue = old('reference_model', $dossierDocumentForRef->reference_model ?? App\Models\TypeDocument::defaultReferenceModelFor($currentDocument->nom));
                                @endphp
                                <div class="wizard-field" style="margin-bottom:16px; border:1px solid #d1d5db; padding:12px; background:#f9fafb;">
                                    <label class="wizard-label" for="reference_model">Modèle d'affichage de la référence (numéro) dans le PDF</label>
                                    <select id="reference_model" name="reference_model" class="form-control wizard-input">
                                        @foreach(App\Support\ReferenceLine::labels() as $modelKey => $modelLabel)
                                            <option value="{{ $modelKey }}" {{ $refModelValue === $modelKey ? 'selected' : '' }}>{{ $modelLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if(trim($currentDocument->nom) === "Déclaration de garantie d'offre")
                                {{-- Afficher le formulaire de déclaration inline pour step6 --}}
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                @endphp
                                @include('documents.partials.declaration_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id, 'dossierDocument' => $dossierDocument])
                            @elseif(trim($currentDocument->nom) === 'Formulaire de qualification')
                                {{-- Afficher le formulaire de qualification inline pour step6 --}}
                                @include('documents.partials.formulaire_qualification_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat')
                                @include('documents.partials.formulaire_renseignements_candidat_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Lettre de soumission')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                @endphp
                                @include('documents.partials.lettre_soumission_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id, 'dossierDocument' => $dossierDocument])
                            @elseif(trim($currentDocument->nom) === 'Formulaire FIN – 3.1 Situation financière')
                                @include('documents.partials.formulaire_fin_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire FIN 3.3')
                                @include('documents.partials.formulaire_fin_3_3_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire FIN 3.4 (a) Modèle d\'attestation de capacité financière')
                                @include('documents.partials.formulaire_fin_3_4_a_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire FIN 3.4 (b) Modèle de lettre de confirmation de la capacité financière')
                                @include('documents.partials.formulaire_fin_3_4_b_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire MTC/FIN – 3.5 : Marchés de fournitures/services en cours')
                                @include('documents.partials.formulaire_mtc_fin_3_5_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire EXP – 4.1 : Expérience générale de fournitures/services')
                                @include('documents.partials.formulaire_exp_4_1_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire EXP – 4.2 a) Expérience spécifique de fournitures/services')
                                @include('documents.partials.formulaire_exp_4_2_a_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id, 'formTitle' => 'Formulaire EXP – 4.2 a) : Expérience spécifique de fournitures/services'])
                            @elseif(trim($currentDocument->nom) === 'Formulaire EXP – 4.2 b) (suite) Expérience spécifique de fournitures/services dans les activités principales (suite)')
                                @include('documents.partials.formulaire_exp_4_2_b_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d\'antécédents de litiges')
                                @include('documents.partials.formulaire_antecedents_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Chiffre d\'affaires annuel moyen des activités de services')
                                @include('documents.partials.chiffre_affaires_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id])
                            @elseif(trim($currentDocument->nom) === 'Liste du personnel affecté à l\'exécution du marché')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                @endphp

                                @include('documents.partials.liste_personnel_affecte_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id, 'dossierDocument' => $dossierDocument])
                            @elseif(trim($currentDocument->nom) === 'Formulaire de divulgation des bénéficiaires effectifs')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                @endphp

                                @include('documents.partials.formulaire_divulgation_beneficiaires_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id, 'dossierDocument' => $dossierDocument])
                            @elseif(trim($currentDocument->nom) === 'Programme d\'activités')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.programme_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Plan de charge')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                @endphp
                                @include('documents.partials.plan_charge_form_step6', ['dossier' => $dossier, 'docId' => $currentDocument->id, 'dossierDocument' => $dossierDocument])
                            @elseif(trim($currentDocument->nom) === 'Méthodes d\'exécution')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.methodes_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Calendrier d\'exécution')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.calendrier_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Description technique des fournitures/services')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.description_technique_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Bordereau des prix pour les fournitures à importer')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.bordereau_fournitures_importer_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Bordereau des prix des fournitures, déjà importées')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.bordereau_fournitures_deja_importees_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Bordereau des prix pour les fournitures fabriquées au Bénin')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.bordereau_fournitures_benin_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Bordereau des prix et calendrier d\'exécution des services connexes')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.bordereau_prix_calendrier_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Listes des services connexes et calendrier de réalisation')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.listes_services_connexes_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Listes des Fournitures et Calendrier de livraison')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.listes_fournitures_calendrier_livraison_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Tableau de résumé des bordereaux de prix')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $values = [];
                                    if ($dossierDocument) {
                                        if (!empty($dossierDocument->content)) {
                                            $decoded = json_decode($dossierDocument->content, true);
                                            if (is_array($decoded)) {
                                                $values = $decoded;
                                            }
                                        }
                                    }

                                    if (empty($values['a'])) {
                                        $bordereauFournituresDoc = $dossier->documents->first(function ($doc) {
                                            return optional($doc->typeDocument)->nom === 'Bordereau des prix pour les fournitures à importer';
                                        });

                                        if ($bordereauFournituresDoc && $bordereauFournituresDoc->bordereau->count() > 0) {
                                            $aTotal = $bordereauFournituresDoc->bordereau->sum(function ($bordereau) {
                                                return $bordereau->lignes->sum('montant');
                                            });

                                            if ($aTotal > 0) {
                                                $values['a'] = number_format($aTotal, 2, '.', '');
                                            }
                                        }
                                    }

                                    if (empty($values['d'])) {
                                        $prixCalendrierDoc = $dossier->documents->first(function ($doc) {
                                            return optional($doc->typeDocument)->nom === "Bordereau des prix et calendrier d'exécution des services connexes";
                                        });

                                        if ($prixCalendrierDoc && $prixCalendrierDoc->bordereau->count() > 0) {
                                            $dTotal = $prixCalendrierDoc->bordereau->sum(function ($bordereau) {
                                                return $bordereau->lignes->sum('montant');
                                            });

                                            if ($dTotal > 0) {
                                                $values['d'] = number_format($dTotal, 2, '.', '');
                                            }
                                        }
                                    }
                                @endphp

                                @include('documents.partials.tableau_resume_bordereaux_form_step6', ['docId' => $currentDocument->id, 'values' => $values])
                            @elseif(trim($currentDocument->nom) === 'Cadres de sous détails des prix unitaire')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.cadres_sous_details_prix_unitaire_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif($currentDocument->type_formulaire === 'bordereau' || trim($currentDocument->nom) === 'Bordereau prix unitaire')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingBordereaux = $dossierDocument ? $dossierDocument->bordereau : collect();
                                @endphp

                                @include('documents.partials.bordereau_form_step6', ['bordereaux' => $existingBordereaux, 'docId' => $currentDocument->id, 'prefillSections' => $sharedDesignationPrefill->toArray()])
                            @elseif(trim($currentDocument->nom) === 'Engagement du soumissionnaire à respecter le code d\'éthique et de déontologie')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingContent = $dossierDocument ? $dossierDocument->content : '';
                                @endphp

                                @include('documents.partials.engagement_ethique_form_step6', ['docId' => $currentDocument->id, 'existingContent' => $existingContent])

                            @elseif($currentDocument->type_formulaire === 'libre')
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                @endphp

                                @include('documents.partials.document_libre_form_step6', ['docId' => $currentDocument->id, 'dossierDocument' => $dossierDocument])

                            @else
                                @php
                                    $dossierDocument = $dossier->documents->firstWhere('type_document_id', $currentDocument->id);
                                    $existingFiles = $dossierDocument ? $dossierDocument->fichiers : collect();
                                @endphp

                                <label class="wizard-label" for="fichier_{{ $currentDocument->id }}">Fichiers à téléverser</label>

                                @if(trim($currentDocument->nom) === "RCCM" && $existingFiles && $existingFiles->count() > 0)
                                    <div class="wizard-alert" style="margin-bottom:8px">
                                        Le registre de commerce téléversé lors de la création de l'entreprise a été ajouté automatiquement. Vous pouvez le supprimer et téléverser un autre document si besoin.
                                    </div>
                                @endif

                                @if($existingFiles && $existingFiles->count() > 0)
                                    <div style="margin-bottom:8px">
                                        <div style="font-weight:700;margin-bottom:6px">Fichiers existants</div>
                                        @foreach($existingFiles as $f)
                                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px">
                                                <a href="{{ asset('media-files/' . $f->chemin_fichier) }}" target="_blank">{{ basename($f->chemin_fichier) }}</a>
                                                <label style="font-size:0.9rem;color:#b91c1c"><input type="checkbox" name="delete_file_ids[]" value="{{ $f->id }}"> Supprimer</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <input type="file" class="form-control wizard-input" name="fichiers[{{ $currentDocument->id }}][]" id="fichier_{{ $currentDocument->id }}" multiple {{ ($existingFiles && $existingFiles->count() > 0) ? '' : 'required' }}>
                            @endif
                        </div>
                </div>

                <div class="wizard-actions">
                        @php
                            $allFilled = $dossier->documents->count() > 0 && $dossier->documents->every(fn($dd) => $dd->statut === 'complete' || ($dd->fichiers && $dd->fichiers->count() > 0));
                            $isLast = ($currentIndex + 1) >= $totalCount;
                        @endphp

                        <button type="button" class="btn btn-ghost" onclick="history.back()">Retour</button>
                        <button type="submit" class="btn btn-success-custom">@if($isLast) Terminer @else Continuer @endif</button>

                        {{-- Le bouton "Générer le dossier" se trouve dans la vue de dossier (liste) quand tout est complété. --}}
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
