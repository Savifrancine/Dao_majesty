@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: var(--shadow-md);">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                    <h4 class="mb-0" style="font-weight: 700;">
                        <i class="fas fa-file-pdf me-2"></i>Modifier le Formulaire PER
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('formulaire_per.update', $formulaire_per->id) }}" method="POST" id="formulairePERForm">
                        @csrf
                        @method('PUT')

                        <!-- Section: Candidat -->
                        <div class="mb-4">
                            <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fas fa-user me-2"></i>Informations du Candidat
                            </h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nom_candidat" class="form-label" style="font-weight: 600;">Nom du candidat *</label>
                                        <input type="text" class="form-control @error('nom_candidat') is-invalid @enderror" id="nom_candidat" name="nom_candidat" value="{{ old('nom_candidat', $formulaire_per->nom_candidat) }}" required>
                                        @error('nom_candidat')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="poste" class="form-label" style="font-weight: 600;">Poste *</label>
                                        <input type="text" class="form-control @error('poste') is-invalid @enderror" id="poste" name="poste" value="{{ old('poste', $formulaire_per->poste) }}" required>
                                        @error('poste')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Renseignements Personnels -->
                        <div class="mb-4">
                            <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fas fa-address-card me-2"></i>Renseignements Personnels
                            </h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nom_personnel" class="form-label" style="font-weight: 600;">Nom Complet *</label>
                                        <input type="text" class="form-control @error('nom_personnel') is-invalid @enderror" id="nom_personnel" name="nom_personnel" value="{{ old('nom_personnel', $formulaire_per->nom_personnel) }}" required>
                                        @error('nom_personnel')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="date_naissance" class="form-label" style="font-weight: 600;">Date de Naissance *</label>
                                        <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $formulaire_per->date_naissance?->format('Y-m-d')) }}" required>
                                        @error('date_naissance')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="qualifications" class="form-label" style="font-weight: 600;">Qualifications Professionnelles</label>
                                        <textarea class="form-control @error('qualifications') is-invalid @enderror" id="qualifications" name="qualifications" rows="3" placeholder="Ingénieur Génie électrique et informatique, etc.">{{ old('qualifications', $formulaire_per->qualifications) }}</textarea>
                                        @error('qualifications')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Employeur Actuel -->
                        <div class="mb-4">
                            <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fas fa-building me-2"></i>Employeur Actuel
                            </h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nom_employeur" class="form-label" style="font-weight: 600;">Nom de l'Employeur *</label>
                                        <input type="text" class="form-control @error('nom_employeur') is-invalid @enderror" id="nom_employeur" name="nom_employeur" value="{{ old('nom_employeur', $formulaire_per->nom_employeur) }}" required>
                                        @error('nom_employeur')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="emploi_tenu" class="form-label" style="font-weight: 600;">Emploi tenu *</label>
                                        <input type="text" class="form-control @error('emploi_tenu') is-invalid @enderror" id="emploi_tenu" name="emploi_tenu" value="{{ old('emploi_tenu', $formulaire_per->emploi_tenu) }}" required>
                                        @error('emploi_tenu')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="adresse_employeur" class="form-label" style="font-weight: 600;">Adresse de l'Employeur *</label>
                                        <textarea class="form-control @error('adresse_employeur') is-invalid @enderror" id="adresse_employeur" name="adresse_employeur" rows="2" required>{{ old('adresse_employeur', $formulaire_per->adresse_employeur) }}</textarea>
                                        @error('adresse_employeur')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="telephone" class="form-label" style="font-weight: 600;">Téléphone *</label>
                                        <input type="tel" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $formulaire_per->telephone) }}" required>
                                        @error('telephone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="telecopie" class="form-label" style="font-weight: 600;">Télécopie</label>
                                        <input type="tel" class="form-control @error('telecopie') is-invalid @enderror" id="telecopie" name="telecopie" value="{{ old('telecopie', $formulaire_per->telecopie) }}">
                                        @error('telecopie')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label" style="font-weight: 600;">Email *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $formulaire_per->email) }}" required>
                                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="contact_personnel" class="form-label" style="font-weight: 600;">Contact (Responsable/Chargé du Personnel) *</label>
                                        <input type="text" class="form-control @error('contact_personnel') is-invalid @enderror" id="contact_personnel" name="contact_personnel" value="{{ old('contact_personnel', $formulaire_per->contact_personnel) }}" required>
                                        @error('contact_personnel')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nombre_annees_employeur" class="form-label" style="font-weight: 600;">Nombre d'années avec le présent employeur *</label>
                                        <input type="number" class="form-control @error('nombre_annees_employeur') is-invalid @enderror" id="nombre_annees_employeur" name="nombre_annees_employeur" value="{{ old('nombre_annees_employeur', $formulaire_per->nombre_annees_employeur) }}" min="0" required>
                                        @error('nombre_annees_employeur')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Expériences Professionnelles -->
                        <div class="mb-4">
                            <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fas fa-briefcase me-2"></i>Expériences Professionnelles des 10 dernières années
                            </h5>
                            <p class="text-muted small mt-3">Indiquer l'expérience pertinente pour le projet (maximum 10 dernières années en ordre chronologique inverse)</p>
                            <div id="experiencesContainer">
                                @if($formulaire_per->experiences && count($formulaire_per->experiences) > 0)
                                    @foreach($formulaire_per->experiences as $exp)
                                    <div class="experience-item card mb-3 p-3" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" placeholder="De (année)" name="exp_de[]" value="{{ $exp['de'] ?? '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" placeholder="À (année)" name="exp_a[]" value="{{ $exp['a'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm" placeholder="Société / projet / position / expérience pertinente" name="exp_description[]" value="{{ $exp['description'] ?? '' }}">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeExperience(this)">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="experience-item card mb-3 p-3" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" placeholder="De (année)" name="exp_de[]">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" placeholder="À (année)" name="exp_a[]">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm" placeholder="Société / projet / position / expérience pertinente" name="exp_description[]">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeExperience(this)">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-info mt-2" onclick="addExperience()">
                                <i class="fas fa-plus me-1"></i>Ajouter une expérience
                            </button>
                        </div>

                        <!-- Section: Signature -->
                        <div class="mb-4">
                            <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fas fa-signature me-2"></i>Signature
                            </h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="lieu_signature" class="form-label" style="font-weight: 600;">Lieu</label>
                                        <input type="text" class="form-control @error('lieu_signature') is-invalid @enderror" id="lieu_signature" name="lieu_signature" value="{{ old('lieu_signature', $formulaire_per->lieu_signature) }}">
                                        @error('lieu_signature')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="mt-5 pt-4 border-top">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                                <a href="{{ route('formulaire_per.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addExperience() {
    const container = document.getElementById('experiencesContainer');
    const newExperience = `
        <div class="experience-item card mb-3 p-3" style="background: #f8f9fa; border: 1px solid #e9ecef;">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" placeholder="De (année)" name="exp_de[]">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" placeholder="À (année)" name="exp_a[]">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control form-control-sm" placeholder="Société / projet / position / expérience pertinente" name="exp_description[]">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeExperience(this)">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newExperience);
}

function removeExperience(button) {
    button.closest('.experience-item').remove();
}

// Convertir les expériences en JSON avant submission
document.getElementById('formulairePERForm').addEventListener('submit', function(e) {
    const experiences = [];
    const des = document.querySelectorAll('input[name="exp_de[]"]');
    const aus = document.querySelectorAll('input[name="exp_a[]"]');
    const descs = document.querySelectorAll('input[name="exp_description[]"]');

    for (let i = 0; i < des.length; i++) {
        if (des[i].value || aus[i].value || descs[i].value) {
            experiences.push({
                de: des[i].value,
                a: aus[i].value,
                description: descs[i].value
            });
        }
    }

    // Créer un input hidden pour les expériences
    const experiencesInput = document.createElement('input');
    experiencesInput.type = 'hidden';
    experiencesInput.name = 'experiences';
    experiencesInput.value = JSON.stringify(experiences);
    this.appendChild(experiencesInput);
});
</script>
@endsection
