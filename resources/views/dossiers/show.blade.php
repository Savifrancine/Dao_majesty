@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>📄 {{ $dossier->nom_dossier }}</h2>
            <p class="text-muted">
                Type: <strong>{{ $dossier->typeDossier->nom }}</strong> | 
                Entreprise: <strong>{{ $dossier->entreprise->nom }}</strong>
            </p>
        </div>
        <div class="col-md-4 text-end">
            @if($dossier->statut === 'genere')
                <a href="{{ route('dossiers.pdf', $dossier) }}" class="btn btn-primary btn-lg">📥 Télécharger PDF</a>
            @endif
            <a href="{{ route('dossiers.index') }}" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <!-- Informations du dossier -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">📋 Informations du dossier</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom:</strong> {{ $dossier->nom_dossier }}</p>
                    <p><strong>Type:</strong> {{ $dossier->typeDossier->nom }}</p>
                    <p><strong>Lot:</strong> {{ $dossier->lot ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Entreprise:</strong> {{ $dossier->entreprise->nom }}</p>
                    <p><strong>Visibilité:</strong> 
                        @if($dossier->public_prive === 'prive')
                            <span class="badge bg-warning">🔒 Privé</span>
                        @else
                            <span class="badge bg-info">🌐 Public</span>
                        @endif
                    </p>
                    <p><strong>Statut:</strong> 
                        @if($dossier->statut === 'en_cours')
                            <span class="badge bg-warning">⏳ En cours</span>
                        @elseif($dossier->statut === 'termine')
                            <span class="badge bg-success">✅ Terminé</span>
                        @else
                            <span class="badge bg-secondary">📄 Généré</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($dossier->objectif)
            <hr>
            <p><strong>Objectif:</strong></p>
            <p>{{ $dossier->objectif }}</p>
            @endif
        </div>
    </div>

    <!-- Informations de l'entreprise -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">🏢 Informations de l'entreprise</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom:</strong> {{ $dossier->entreprise->nom }}</p>
                    <p><strong>Sigle:</strong> {{ $dossier->entreprise->sigle ?? '—' }}</p>
                    <p><strong>Email:</strong> {{ $dossier->entreprise->email ?? '—' }}</p>
                    <p><strong>Téléphone:</strong> {{ $dossier->entreprise->telephone ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Adresse:</strong> {{ $dossier->entreprise->adresse ?? '—' }}</p>
                    <p><strong>Responsable:</strong> {{ $dossier->entreprise->responsable ?? '—' }}</p>
                    <p><strong>Fonction:</strong> {{ $dossier->entreprise->fonction_responsable ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sommaire des documents -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📑 Sommaire ({{ $dossier->documents->count() }} documents)</h5>
        </div>
        <div class="card-body">
            @if($dossier->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ordre</th>
                                <th>Document</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossier->documents->sortBy('ordre') as $doc)
                            <tr>
                                <td><strong>{{ $doc->ordre }}</strong></td>
                                <td>{{ $doc->typeDocument->nom }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($doc->typeDocument->type_formulaire) }}</span>
                                </td>
                                <td>
                                    @if($doc->statut === 'vide')
                                        <span class="badge bg-danger">⬜ Vide</span>
                                    @elseif($doc->statut === 'en_cours')
                                        <span class="badge bg-warning">⏳ En cours</span>
                                    @else
                                        <span class="badge bg-success">✅ Complet</span>
                                    @endif
                                </td>
                                <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <p class="mb-0">📭 Aucun document n'a été ajouté à ce dossier.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
