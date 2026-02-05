@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>📊 Dashboard</h2>
            <p class="text-muted">Bienvenue {{ Auth::user()->prenom }} {{ Auth::user()->nom }}</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('dossiers.create') }}" class="btn btn-success btn-lg">
                ➕ Créer un nouveau dossier
            </a>
        </div>
    </div>

    <!-- Profil utilisateur -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Prénom:</strong> {{ Auth::user()->prenom }}</p>
                    <p><strong>Nom:</strong> {{ Auth::user()->nom }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Rôle:</strong> <span class="badge bg-primary">{{ ucfirst(Auth::user()->role) }}</span></p>
                    <p><strong>Actif:</strong> 
                        @if(Auth::user()->actif)
                            <span class="badge bg-success">✅ Oui</span>
                        @else
                            <span class="badge bg-danger">❌ Non</span>
                        @endif
                    </p>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">🔓 Déconnexion</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Mes dossiers -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📁 Mes dossiers</h5>
        </div>
        <div class="card-body">
            @if($dossiers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Entreprise</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossiers as $dossier)
                            <tr>
                                <td>
                                    <strong>{{ $dossier->nom_dossier }}</strong>
                                    @if($dossier->public_prive === 'prive')
                                        <span class="badge bg-warning">🔒 Privé</span>
                                    @else
                                        <span class="badge bg-info">🌐 Public</span>
                                    @endif
                                </td>
                                <td>{{ $dossier->typeDossier->nom }}</td>
                                <td>{{ $dossier->entreprise->nom }}</td>
                                <td>
                                    @if($dossier->statut === 'en_cours')
                                        <span class="badge bg-warning">⏳ En cours</span>
                                    @elseif($dossier->statut === 'termine')
                                        <span class="badge bg-success">✅ Terminé</span>
                                    @else
                                        <span class="badge bg-secondary">📄 Généré</span>
                                    @endif
                                </td>
                                <td>{{ $dossier->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-sm btn-info">👁️ Voir</a>
                                    @if($dossier->statut === 'genere')
                                        <a href="{{ route('dossiers.pdf', $dossier) }}" class="btn btn-sm btn-primary">📥 PDF</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <p class="mb-0">📭 Vous n'avez pas encore créé de dossier.</p>
                    <p class="mb-0"><a href="{{ route('dossiers.create') }}" class="alert-link">Cliquez ici pour en créer un</a></p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
