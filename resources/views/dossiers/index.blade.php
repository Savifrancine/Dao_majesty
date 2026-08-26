@extends('layouts.app')

@section('content')
<style>
    .page-title {
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #0f172a;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 0.95rem;
    }

    .table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .table thead th {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .dossier-name {
        font-weight: 700;
        color: #0f172a;
    }

    .dossier-name small {
        display: block;
        color: #64748b;
        font-size: 0.8rem;
        margin-top: 2px;
    }

    .btn-action-group .btn {
        font-size: 0.82rem;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <h2 class="page-title mb-1">📁 Mes dossiers</h2>
            <p class="page-subtitle mb-0">Gérez vos dossiers et reprenez rapidement la saisie des documents à compléter.</p>
        </div>
        <a href="{{ route('dossiers.create') }}" class="btn btn-success">➕ Créer un nouveau dossier</a>
    </div>

    <form method="GET" action="{{ route('dossiers.index') }}" class="mb-4">
        <div class="input-group shadow-sm rounded overflow-hidden">
            <input
                type="search"
                name="search"
                class="form-control border-0"
                placeholder="Rechercher par nom, entreprise, type, statut ou date (JJ/MM/AAAA)"
                value="{{ request('search') }}"
                aria-label="Rechercher des dossiers"
            />
            <button class="btn btn-outline-secondary" type="submit">Recherche</button>
        </div>
    </form>

    @if(request('search'))
        <div class="mb-3 text-secondary">Résultats pour « {{ request('search') }} » : {{ $dossiers->count() }} dossier(s)</div>
    @endif

    @if($dossiers->count() > 0)
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                            <div class="dossier-name">
                                {{ $dossier->nom_dossier }}
                                <small>{{ $dossier->lot ?? 'Sans lot' }}</small>
                            </div>
                            @if($dossier->public_prive === 'prive')
                                <span class="badge bg-warning-subtle text-warning-emphasis">🔒 Privé</span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis">🌐 Public</span>
                            @endif
                        </td>
                        <td>{{ $dossier->typeDossier->nom }}</td>
                        <td>{{ $dossier->entreprise->nom }}</td>
                        <td>
                            @if($dossier->statut === 'en_cours')
                                <span class="badge bg-warning-subtle text-warning-emphasis">⏳ En cours</span>
                            @elseif($dossier->statut === 'termine')
                                <span class="badge bg-success-subtle text-success-emphasis">✅ Terminé</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">📄 Généré</span>
                            @endif
                        </td>
                        <td>{{ $dossier->created_at->format('d/m/Y') }}</td>
                        <td>
                            @php $info = $resumeInfo[$dossier->id] ?? null; @endphp

                            <div class="btn-action-group d-flex flex-wrap gap-2">
                                @if($dossier->documents->isEmpty())
                                    <a href="{{ route('dossiers.selectDocuments', $dossier) }}" class="btn btn-sm btn-info">▶ Continuer</a>
                                @elseif($dossier->statut === 'en_cours')
                                    <a href="{{ route('dossiers.continuer', ['dossier' => $dossier->id]) }}" class="btn btn-sm btn-info">▶ Continuer</a>
                                @elseif($info)
                                    <form action="{{ route('dossiers.step6', $dossier->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        @foreach($info['document_type_ids'] as $docId)
                                            <input type="hidden" name="documents[]" value="{{ $docId }}">
                                        @endforeach
                                        <input type="hidden" name="current_index" value="{{ $info['current_index'] }}">
                                        <button class="btn btn-sm btn-info" type="submit">▶ Continuer</button>
                                    </form>
                                @else
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-sm btn-outline-secondary">▶ Voir</a>
                                @endif

                                <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-sm btn-outline-primary">✎ Modifier</a>
                                <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" type="submit">🗑 Supprimer</button>
                                </form>
                                @if($dossier->statut === 'genere' || $dossier->statut === 'termine')
                                    <a href="{{ route('dossiers.pdf', $dossier) }}" class="btn btn-sm btn-primary">📥 PDF</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info shadow-sm">
            <p class="mb-0">📭 Vous n'avez pas encore créé de dossier.</p>
            <p class="mb-0"><a href="{{ route('dossiers.create') }}" class="alert-link">Créer un nouveau dossier</a></p>
        </div>
    @endif
</div>
@endsection
