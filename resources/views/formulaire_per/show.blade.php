@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="border: none; border-radius: 12px; box-shadow: var(--shadow-md);">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0" style="font-weight: 700;">
                            <i class="fas fa-file-pdf me-2"></i>{{ $formulaire_per->nom_candidat }}
                        </h4>
                        <div>
                            <a href="{{ route('formulaire_per.edit', $formulaire_per->id) }}" class="btn btn-warning btn-sm me-2">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('formulaire_per.pdf', $formulaire_per->id) }}" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-download me-1"></i>Télécharger PDF
                            </a>
                            <a href="{{ route('formulaire_per.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Section: Candidat -->
                    <div class="mb-5">
                        <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                            <i class="fas fa-user me-2"></i>Informations du Candidat
                        </h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Nom du candidat</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->nom_candidat }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Poste</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->poste }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Renseignements Personnels -->
                    <div class="mb-5">
                        <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                            <i class="fas fa-address-card me-2"></i>Renseignements Personnels
                        </h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Nom Complet</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->nom_personnel }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Date de Naissance</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->date_naissance?->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                        @if($formulaire_per->qualifications)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="text-muted small">Qualifications Professionnelles</label>
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $formulaire_per->qualifications }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Section: Employeur Actuel -->
                    <div class="mb-5">
                        <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                            <i class="fas fa-building me-2"></i>Employeur Actuel
                        </h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Nom de l'Employeur</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->nom_employeur }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Emploi tenu</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->emploi_tenu }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="text-muted small">Adresse de l'Employeur</label>
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $formulaire_per->adresse_employeur }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Téléphone</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->telephone }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Télécopie</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->telecopie ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Email</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->email }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Contact (Responsable/Chargé du Personnel)</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->contact_personnel }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Nombre d'années avec le présent employeur</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->nombre_annees_employeur }} ans</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Expériences Professionnelles -->
                    @if($formulaire_per->experiences && count($formulaire_per->experiences) > 0)
                    <div class="mb-5">
                        <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                            <i class="fas fa-briefcase me-2"></i>Expériences Professionnelles
                        </h5>
                        <div class="mt-3">
                            @foreach($formulaire_per->experiences as $exp)
                            <div class="card mb-3 p-3" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="text-muted small">De</label>
                                        <p class="mb-0" style="font-weight: 600;">{{ $exp['de'] ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">À</label>
                                        <p class="mb-0" style="font-weight: 600;">{{ $exp['a'] ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Société / projet / position</label>
                                        <p class="mb-0" style="font-weight: 600;">{{ $exp['description'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Section: Signature -->
                    @if($formulaire_per->lieu_signature)
                    <div class="mb-5">
                        <h5 style="color: var(--text-primary); font-weight: 700; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                            <i class="fas fa-signature me-2"></i>Signature
                        </h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Lieu</label>
                                    <p class="mb-0" style="font-weight: 600;">{{ $formulaire_per->lieu_signature }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div class="mt-5 pt-4 border-top">
                        <small class="text-muted">
                            Créé le {{ $formulaire_per->created_at->format('d/m/Y à H:i') }}<br>
                            Dernière modification : {{ $formulaire_per->updated_at->format('d/m/Y à H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
