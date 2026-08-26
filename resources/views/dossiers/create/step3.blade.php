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
        max-width: 820px;
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

    .wizard-input {
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        padding: 10px 12px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .wizard-input:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        background: white;
    }

    .wizard-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.3), transparent);
        margin: 18px 0;
        position: relative;
        text-align: center;
    }

    .wizard-divider::before {
        content: 'OU';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 0 12px;
        font-size: 11px;
        font-weight: 700;
        color: var(--primary-green);
        letter-spacing: 1px;
    }

    .wizard-subcard {
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 16px;
        padding: 16px;
        background: rgba(16, 185, 129, 0.05);
    }

    .wizard-subcard-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 12px;
        font-size: 0.95rem;
    }

    .wizard-link {
        color: var(--emerald);
        font-weight: 600;
        text-decoration: none;
        padding: 0;
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
            <div class="wizard-badge">Etape 3 sur 6</div>
            <h1 class="wizard-title">Informations de l'entreprise</h1>
            <p class="wizard-subtitle">Selectionnez une entreprise existante ou creez-en une nouvelle.</p>
        </div>

        <div class="wizard-body">
            <div class="wizard-step">
                <span>Entreprise</span>
                <div class="wizard-progress"><span style="width: 50%;"></span></div>
            </div>

            <form action="{{ route('dossiers.step4') }}" method="POST">
                @csrf
                <input type="hidden" name="categorie" value="{{ $categorie }}">
                <input type="hidden" name="type_dossier_id" value="{{ $typeDossierId }}">

                <div class="wizard-field">
                    <label for="entreprise_id" class="wizard-label">Entreprise <span class="text-danger">*</span></label>
                    <select class="form-select wizard-input @error('entreprise_id') is-invalid @enderror" name="entreprise_id" id="entreprise_id" required>
                        <option value="">-- Selectionnez une entreprise --</option>
                        @foreach($entreprises as $entreprise)
                            <option value="{{ $entreprise->id }}">{{ $entreprise->nom }} @if($entreprise->sigle)({{ $entreprise->sigle }})@endif</option>
                        @endforeach
                    </select>

                    <div class="mt-3">
                        <h5>Entreprises existantes</h5>
                        @if($entreprises->isEmpty())
                            <p>Aucune entreprise enregistrée.</p>
                        @else
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Sigle</th>
                                        <th>Pays</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entreprises as $ent)
                                        <tr>
                                            <td>{{ $ent->nom }}</td>
                                            <td>{{ $ent->sigle ?? '-' }}</td>
                                            <td>{{ $ent->pays ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('entreprises.edit', $ent) }}" class="btn btn-sm btn-secondary">Modifier</a>
                                                <button type="button" class="btn btn-sm btn-danger delete-entreprise-btn" data-url="{{ route('entreprises.destroy', $ent) }}">Supprimer</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                    @error('entreprise_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="wizard-divider"></div>

                <div id="newEntrepriseForm" class="wizard-subcard" style="display: none;">
                    <div class="wizard-subcard-title">Creer une nouvelle entreprise</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_nom" class="wizard-label">Nom *</label>
                                <input type="text" class="form-control wizard-input" id="new_nom" name="new_nom">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_sigle" class="wizard-label">Sigle</label>
                                <input type="text" class="form-control wizard-input" id="new_sigle" name="new_sigle" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <div class="wizard-field">
                        <label for="new_adresse" class="wizard-label">Adresse</label>
                        <input type="text" class="form-control wizard-input" id="new_adresse" name="new_adresse">
                    </div>

                    <div class="wizard-field">
                        <label for="new_adresse_officielle" class="wizard-label">Adresse officielle</label>
                        <input type="text" class="form-control wizard-input" id="new_adresse_officielle" name="new_adresse_officielle">
                    </div>

                    <div class="wizard-field">
                        <label for="new_annee_enregistrement" class="wizard-label">Année d'enregistrement</label>
                        <input type="number" class="form-control wizard-input" id="new_annee_enregistrement" name="new_annee_enregistrement" min="1900" max="2100">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_pays" class="wizard-label">Pays</label>
                                <select class="form-control wizard-input" id="new_pays" name="new_pays">
                                    @include('partials.pays_options', ['selectedPays' => old('new_pays')])
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_ifu" class="wizard-label">Numero IFU</label>
                                <input type="text" class="form-control wizard-input" id="new_ifu" name="new_ifu">
                            </div>
                        </div>
                    </div>

                    <div class="wizard-field">
                        <label for="new_registre" class="wizard-label">Registre de commerce (PDF/JPG/PNG)</label>
                        <input type="file" class="form-control wizard-input" id="new_registre" name="new_registre" accept="application/pdf,image/*">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_telephone" class="wizard-label">Telephone</label>
                                <input type="tel" class="form-control wizard-input" id="new_telephone" name="new_telephone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_email" class="wizard-label">Email</label>
                                <input type="email" class="form-control wizard-input" id="new_email" name="new_email">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_responsable" class="wizard-label">Responsable</label>
                                <input type="text" class="form-control wizard-input" id="new_responsable" name="new_responsable">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wizard-field">
                                <label for="new_fonction" class="wizard-label">Fonction</label>
                                <input type="text" class="form-control wizard-input" id="new_fonction" name="new_fonction">
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary-custom btn-sm" id="saveNewEntreprise">Creer cette entreprise</button>
                </div>

                <button type="button" class="btn btn-link wizard-link" id="toggleNewEntreprise">
                    + Creer une nouvelle entreprise
                </button>

                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost" onclick="history.back()">Retour</button>
                    <button type="submit" class="btn btn-primary-custom">Continuer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-entreprise-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!confirm('Confirmer la suppression de cette entreprise ?')) return;
        
        const url = this.getAttribute('data-url');
        
        // Use fetch instead of form submission to avoid conflicts
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        }).catch(err => {
            console.error(err);
            alert('Erreur réseau');
        });
    });
});

document.getElementById('toggleNewEntreprise').addEventListener('click', function(e) {
    e.preventDefault();
    const form = document.getElementById('newEntrepriseForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('saveNewEntreprise').addEventListener('click', async function() {
    const nom = document.getElementById('new_nom').value;
    if (!nom) {
        alert('Le nom de l\'entreprise est requis');
        return;
    }

    const formData = new FormData();
    formData.append('nom', nom);
    formData.append('sigle', document.getElementById('new_sigle').value || '');
    formData.append('adresse', document.getElementById('new_adresse').value || '');
    formData.append('telephone', document.getElementById('new_telephone').value || '');
    formData.append('email', document.getElementById('new_email').value || '');
    formData.append('responsable', document.getElementById('new_responsable').value || '');
    formData.append('fonction_responsable', document.getElementById('new_fonction').value || '');
    formData.append('adresse_officielle', document.getElementById('new_adresse_officielle') ? document.getElementById('new_adresse_officielle').value : '');
    formData.append('annee_enregistrement', document.getElementById('new_annee_enregistrement') ? document.getElementById('new_annee_enregistrement').value : '');
    formData.append('pays', document.getElementById('new_pays').value || '');
    formData.append('ifu', document.getElementById('new_ifu').value || '');

    const fileInput = document.getElementById('new_registre');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('registre', fileInput.files[0]);
    }
    const logoInput = document.getElementById('new_logo');
    if (logoInput && logoInput.files && logoInput.files[0]) {
        formData.append('logo', logoInput.files[0]);
    }

    try {
        const resRaw = await fetch('{{ route("dossiers.storeEntreprise") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const res = await resRaw.json();
        if (resRaw.status === 422) {
            const errors = res.errors || {};
            const messages = Object.values(errors).flat().join('\n');
            alert('Validation error:\n' + messages);
            return;
        }
        if (!res.success) {
            alert(res.message || 'Erreur lors de la création de l\'entreprise');
            return;
        }

        const option = document.createElement('option');
        option.value = res.entreprise.id;
        option.textContent = res.entreprise.nom + (res.entreprise.sigle ? ` (${res.entreprise.sigle})` : '');
        document.getElementById('entreprise_id').appendChild(option);
        document.getElementById('entreprise_id').value = res.entreprise.id;
        document.getElementById('newEntrepriseForm').style.display = 'none';
        alert('Entreprise créée avec succès!');
    } catch (err) {
        console.error(err);
        alert('Erreur réseau ou serveur. Vérifiez la console pour plus de détails.');
    }
});
</script>
@endsection
