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

    .wizard-field {
        margin-bottom: 14px;
    }

    .wizard-label {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        display: block;
        font-size: 0.85rem;
    }

    .wizard-input,
    .wizard-textarea {
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        padding: 10px 12px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .wizard-input:focus,
    .wizard-textarea:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        background: white;
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
            <div class="wizard-badge">Etape 4 sur 6</div>
            <h1 class="wizard-title">Informations du dossier</h1>
            <p class="wizard-subtitle">Ces informations seront utilisees pour la page de garde et le sommaire.</p>
        </div>

        <div class="wizard-body">
            <div class="wizard-step">
                <span>Informations du dossier</span>
                <div class="wizard-progress"><span style="width: 66.6%;"></span></div>
            </div>

            <form action="{{ route('dossiers.step5') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="categorie" value="{{ $categorie }}">
                <input type="hidden" name="type_dossier_id" value="{{ $type_dossier_id }}">
                <input type="hidden" name="entreprise_id" value="{{ $entreprise_id }}">

                <div class="wizard-field">
                    <label for="nom_dossier" class="wizard-label">Nom du dossier <span class="text-danger">*</span></label>
                    <input type="text" class="form-control wizard-input @error('nom_dossier') is-invalid @enderror" id="nom_dossier" name="nom_dossier" placeholder="Ex: DAO Fournitures 2026" required value="{{ old('nom_dossier') }}">
                    @error('nom_dossier')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="wizard-field">
                    <label for="titre_dossier" class="wizard-label">Titre principal du dossier</label>
                    <input type="text" class="form-control wizard-input @error('titre_dossier') is-invalid @enderror" id="titre_dossier" name="titre_dossier" placeholder="Ex: DOSSIER D'APPEL D'OFFRES" value="{{ old('titre_dossier') }}">
                    @error('titre_dossier')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="wizard-field">
                            <label for="republique" class="wizard-label">Republique</label>
                            <input type="text" name="republique" id="republique" class="form-control wizard-input" value="{{ old('republique') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="wizard-field">
                            <label for="ministere" class="wizard-label">Ministere</label>
                            <input type="text" name="ministere" id="ministere" class="form-control wizard-input" value="{{ old('ministere') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="wizard-field">
                            <label for="direction" class="wizard-label">Direction</label>
                            <input type="text" name="direction" id="direction" class="form-control wizard-input" value="{{ old('direction') }}">
                        </div>
                    </div>
                </div>

                <div class="wizard-field">
                    <label for="services_projet" class="wizard-label">Services / Projet</label>
                    <input type="text" name="services_projet" id="services_projet" class="form-control wizard-input" value="{{ old('services_projet') }}">
                </div>

                <div class="wizard-field">
                    <label for="destinataires" class="wizard-label">Destinataires</label>
                    <textarea name="destinataires" id="destinataires" rows="2" class="form-control wizard-textarea">{{ old('destinataires') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="wizard-field">
                            <label for="reference_dossier" class="wizard-label">Reference du dossier</label>
                            <input type="text" name="reference_dossier" id="reference_dossier" class="form-control wizard-input" value="{{ old('reference_dossier') }}">
                        </div>

                        <div class="wizard-field">
                            <label for="ref" class="wizard-label">Ref</label>
                            <input type="text" name="ref" id="ref" class="form-control wizard-input @error('ref') is-invalid @enderror" placeholder="Ex: REF-123" value="{{ old('ref') }}">
                            @error('ref')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="wizard-field">
                            <label for="date_lancement" class="wizard-label">Date de lancement</label>
                            <input type="date" name="date_lancement" id="date_lancement" class="form-control wizard-input" value="{{ old('date_lancement') }}">
                        </div>

                        <div class="wizard-field">
                            <label for="date_soumission" class="wizard-label">Date de soumission</label>
                            <input type="date" name="date_soumission" id="date_soumission" class="form-control wizard-input" value="{{ old('date_soumission') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="wizard-field">
                            <label for="type_offre" class="wizard-label">Type d'offres</label>
                            <input type="text" name="type_offre" id="type_offre" class="form-control wizard-input @error('type_offre') is-invalid @enderror" value="{{ old('type_offre') }}" placeholder="Ex: Offre technique, Offre financiere">
                            @error('type_offre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="wizard-field">
                    <label for="lots" class="wizard-label">Lots concernes</label>
                    <input type="text" class="form-control wizard-input @error('lots') is-invalid @enderror" id="lots" name="lots" placeholder="Ex: Lot 1, Lot 2" value="{{ old('lots', old('lot')) }}">
                    @error('lots')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="wizard-field">
                    <label for="titre_lot" class="wizard-label">Titre du lot</label>
                    <input type="text" name="titre_lot" id="titre_lot" class="form-control wizard-input" value="{{ old('titre_lot') }}">
                </div>

                <div class="wizard-field">
                    <label for="autres_details" class="wizard-label">Autres details</label>
                    <textarea name="autres_details" id="autres_details" rows="3" class="form-control wizard-textarea">{{ old('autres_details') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="wizard-field">
                            <label for="mois_depot" class="wizard-label">Mois de depot</label>
                            <input type="text" name="mois_depot" id="mois_depot" class="form-control wizard-input" value="{{ old('mois_depot') }}" placeholder="Ex: Septembre">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="wizard-field">
                            <label for="annee_depot" class="wizard-label">Annee de depot</label>
                            <input type="number" name="annee_depot" id="annee_depot" class="form-control wizard-input" value="{{ old('annee_depot') }}" placeholder="2025">
                        </div>
                    </div>
                </div>

                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost" onclick="history.back()">Retour</button>
                    <button type="submit" class="btn btn-primary-custom">Continuer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- lookup modals (outside main form) -->
<!-- Type d'offres modal removed; using free-text field instead -->

<!-- Procedure / Autorite / Source modals removed as requested -->

<script>
document.addEventListener('DOMContentLoaded', function(){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function postLookup(kind, nom){
        const url = `{{ url('lookups') }}/${kind}`;
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({nom})
            });

            const json = await res.json().catch(()=>({success:false,message:'Reponse invalide'}));
            return {status: res.status, body: json};
        } catch (e) {
            return {status: 0, body: {success:false, message: 'Erreur reseau'}};
        }
    }

    function showLookupError(inputId, message){
        const input = document.getElementById(inputId);
        if(!input) return;
        input.classList.add('is-invalid');
        let fb = input.nextElementSibling;
        if(!fb || !fb.classList || !fb.classList.contains('invalid-feedback')){
            fb = document.createElement('div');
            fb.className = 'invalid-feedback d-block';
            input.parentNode.insertBefore(fb, input.nextSibling);
        }
        fb.textContent = message;
    }

    function clearLookupError(inputId){
        const input = document.getElementById(inputId);
        if(!input) return;
        input.classList.remove('is-invalid');
        const fb = input.parentNode.querySelector('.invalid-feedback.d-block');
        if(fb) fb.remove();
    }
});
</script>

@endsection
