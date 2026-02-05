@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>📁 Mes dossiers</h2>

    @if($dossiers->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
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
                                <span class="badge bg-warning">🔒</span>
                            @else
                                <span class="badge bg-info">🌐</span>
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
        <div class="alert alert-info">
            <p class="mb-0">📭 Vous n'avez pas encore créé de dossier.</p>
            <p class="mb-0"><a href="{{ route('dossiers.create') }}" class="alert-link">Créer un nouveau dossier</a></p>
        </div>
    @endif

    <a href="{{ route('dossiers.create') }}" class="btn btn-success">➕ Créer un nouveau dossier</a>
</div>
@endsection
