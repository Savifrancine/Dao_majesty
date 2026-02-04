@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Liste des DAOs</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('daos.create') }}" class="btn btn-success">+ Ajouter un DAO</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($daos->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daos as $dao)
                        <tr>
                            <td>{{ $dao->id }}</td>
                            <td><strong>{{ $dao->nom }}</strong></td>
                            <td>{{ $dao->email ?? '-' }}</td>
                            <td>{{ $dao->telephone ?? '-' }}</td>
                            <td>{{ $dao->ville ?? '-' }}</td>
                            <td>
                                @if ($dao->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('daos.show', $dao) }}" class="btn btn-sm btn-info">Voir</a>
                                <a href="{{ route('daos.edit', $dao) }}" class="btn btn-sm btn-warning">Éditer</a>
                                <form action="{{ route('daos.destroy', $dao) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $daos->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="alert alert-info">
            Aucun DAO trouvé. <a href="{{ route('daos.create') }}">Créer le premier</a>
        </div>
    @endif
</div>
@endsection
