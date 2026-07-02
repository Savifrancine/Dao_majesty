@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h1>Modifier un utilisateur</h1>
        <p class="text-muted">Mettez à jour les informations ou le rôle de l'utilisateur.</p>
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

    <form action="{{ route('utilisateurs.update', $utilisateur) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" value="{{ old('nom', $utilisateur->nom) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $utilisateur->email) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe (laisser vide pour conserver)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-control" required>
                <option value="directeur" {{ old('role', $utilisateur->role) === 'directeur' ? 'selected' : '' }}>Directeur</option>
                <option value="employe" {{ old('role', $utilisateur->role) === 'employe' ? 'selected' : '' }}>Employé</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Actif</label>
            <select name="actif" class="form-control" required>
                <option value="1" {{ old('actif', $utilisateur->actif) ? 'selected' : '' }}>Oui</option>
                <option value="0" {{ ! old('actif', $utilisateur->actif) ? 'selected' : '' }}>Non</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
