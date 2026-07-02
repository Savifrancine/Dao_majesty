@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Gestion des utilisateurs</h1>
            <p class="text-muted">Créer, modifier ou supprimer des utilisateurs.</p>
        </div>
        <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary">Créer un utilisateur</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($utilisateurs as $utilisateur)
                    <tr>
                        <td>{{ $utilisateur->nom }}</td>
                        <td>{{ $utilisateur->prenom }}</td>
                        <td>{{ $utilisateur->email }}</td>
                        <td>{{ ucfirst($utilisateur->role) }}</td>
                        <td>{{ $utilisateur->actif ? 'Oui' : 'Non' }}</td>
                        <td>
                            <a href="{{ route('utilisateurs.edit', $utilisateur) }}" class="btn btn-sm btn-secondary">Modifier</a>
                            @if($utilisateur->id !== auth()->id())
                                <form action="{{ route('utilisateurs.destroy', $utilisateur) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
