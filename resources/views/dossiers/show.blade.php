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

    .show-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .btn-primary-custom,
    .btn-ghost,
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

    .section-card {
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 16px;
        padding: 16px;
        background: white;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.08);
        margin-bottom: 16px;
    }

    .section-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
    }

    .info-item strong {
        color: #0f172a;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
    }

    .status-pill.warning { background: rgba(245, 158, 11, 0.15); color: #b45309; }
    .status-pill.info { background: rgba(59, 130, 246, 0.15); color: #1d4ed8; }
    .status-pill.danger { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }

    .table-wrap {
        overflow-x: auto;
    }

    .simple-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .simple-table th,
    .simple-table td {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
    }

    .simple-table th {
        text-align: left;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    @media (max-width: 768px) {
        .show-actions {
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
            <h1 class="wizard-title">{{ $dossier->nom_dossier }}</h1>
            <p class="wizard-subtitle">
                Type: <strong>{{ $dossier->typeDossier->nom }}</strong> | Entreprise: <strong>{{ $dossier->entreprise->nom }}</strong>
            </p>

            <div class="show-actions">
                @if(!is_null($resumeIndex) && !empty($resumeDocumentIds))
                    <form action="{{ route('dossiers.step6', $dossier->id) }}" method="POST">
                        @csrf
                        @foreach($resumeDocumentIds as $docId)
                            <input type="hidden" name="documents[]" value="{{ $docId }}">
                        @endforeach
                        <input type="hidden" name="current_index" value="{{ $resumeIndex }}">
                        <button type="submit" class="btn btn-primary-custom">Continuer la creation</button>
                    </form>
                @endif

                @if($dossier->statut === 'genere')
                    <a href="{{ route('dossiers.pdf', $dossier) }}" class="btn btn-success-custom">Telecharger PDF</a>
                @endif
                <a href="{{ route('dossiers.index') }}" class="btn btn-ghost">Retour</a>
            </div>
        </div>

        <div class="wizard-body">
            <div class="section-card">
                <div class="section-title">Informations du dossier</div>
                <div class="info-grid">
                    <div class="info-item"><strong>Nom:</strong> {{ $dossier->nom_dossier }}</div>
                    <div class="info-item"><strong>Type:</strong> {{ $dossier->typeDossier->nom }}</div>
                    <div class="info-item"><strong>Lot:</strong> {{ $dossier->lot ?? '—' }}</div>
                    <div class="info-item"><strong>Entreprise:</strong> {{ $dossier->entreprise->nom }}</div>
                    <div class="info-item">
                        <strong>Visibilite:</strong>
                        @if($dossier->public_prive === 'prive')
                            <span class="status-pill warning">Prive</span>
                        @else
                            <span class="status-pill info">Public</span>
                        @endif
                    </div>
                    <div class="info-item">
                        <strong>Statut:</strong>
                        @if($dossier->statut === 'en_cours')
                            <span class="status-pill warning">En cours</span>
                        @elseif($dossier->statut === 'termine')
                            <span class="status-pill">Termine</span>
                        @else
                            <span class="status-pill info">Genere</span>
                        @endif
                    </div>
                </div>

                @if($dossier->objectif)
                    <div style="margin-top: 12px;">
                        <strong>Objectif:</strong>
                        <div>{{ $dossier->objectif }}</div>
                    </div>
                @endif
            </div>

            <div class="section-card">
                <div class="section-title">Informations de l'entreprise</div>
                <div class="info-grid">
                    <div class="info-item"><strong>Nom:</strong> {{ $dossier->entreprise->nom }}</div>
                    <div class="info-item"><strong>Sigle:</strong> {{ $dossier->entreprise->sigle ?? '—' }}</div>
                    <div class="info-item"><strong>Email:</strong> {{ $dossier->entreprise->email ?? '—' }}</div>
                    <div class="info-item"><strong>Telephone:</strong> {{ $dossier->entreprise->telephone ?? '—' }}</div>
                    <div class="info-item"><strong>Adresse:</strong> {{ $dossier->entreprise->adresse ?? '—' }}</div>
                    <div class="info-item"><strong>Responsable:</strong> {{ $dossier->entreprise->responsable ?? '—' }}</div>
                    <div class="info-item"><strong>Fonction:</strong> {{ $dossier->entreprise->fonction_responsable ?? '—' }}</div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Sommaire ({{ $dossier->documents->count() }} documents)</div>
                @if($dossier->documents->count() > 0)
                    <div class="table-wrap">
                        <table class="simple-table">
                            <thead>
                                                <tr>
                                                    <th>Ordre</th>
                                                    <th>Document</th>
                                                    <th>Type</th>
                                                    <th>Statut</th>
                                                    <th>Cree le</th>
                                                    <th>Actions</th>
                                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $uploadable = $dossier->documents->filter(function($dd){
                                        return $dd->typeDocument && $dd->typeDocument->nom !== 'Lettre de soumission';
                                    })->sortBy('ordre')->values();
                                    $uploadableTypeIds = $uploadable->pluck('type_document_id');
                                @endphp

                                @foreach($dossier->documents->sortBy('ordre') as $doc)
                                <tr>
                                    <td><strong>{{ $doc->ordre }}</strong></td>
                                    <td>{{ $doc->typeDocument->nom }}</td>
                                    <td><span class="status-pill info">{{ ucfirst($doc->typeDocument->type_formulaire) }}</span></td>
                                    <td>
                                        @if($doc->statut === 'vide')
                                            <span class="status-pill danger">Vide</span>
                                        @elseif($doc->statut === 'en_cours')
                                            <span class="status-pill warning">En cours</span>
                                        @else
                                            <span class="status-pill">Complet</span>
                                        @endif
                                    </td>
                                    <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @php
                                            $pos = $uploadable->pluck('id')->search($doc->id);
                                        @endphp

                                        @if($pos !== false)
                                            <form action="{{ route('dossiers.step6', $dossier->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @foreach($uploadable as $ud)
                                                    <input type="hidden" name="documents[]" value="{{ $ud->type_document_id }}">
                                                @endforeach
                                                <input type="hidden" name="current_index" value="{{ $pos }}">
                                                <button class="btn btn-sm btn-primary" type="submit">✎ Modifier</button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="wizard-alert">Aucun document n'a ete ajoute a ce dossier.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
