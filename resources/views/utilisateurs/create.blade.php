@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h1>Créer un utilisateur</h1>
        <p class="text-muted">Ajoutez un nouvel utilisateur avec un rôle directeur ou employé.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('utilisateurs.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" value="{{ old('nom') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-control" required>
                <option value="directeur" {{ old('role') === 'directeur' ? 'selected' : '' }}>Directeur</option>
                <option value="employe" {{ old('role') === 'employe' ? 'selected' : '' }}>Employé</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
