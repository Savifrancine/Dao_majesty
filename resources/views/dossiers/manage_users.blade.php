@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Contributeurs du dossier</h1>
            <p class="text-muted">Partagez ce dossier avec plusieurs utilisateurs.</p>
        </div>
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-secondary">Retour au dossier</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('dossiers.utilisateurs.update', $dossier) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Utilisateurs avec accès</label>
            <select name="utilisateurs[]" class="form-control" multiple size="10" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ in_array($user->id, $selectedUsers) ? 'selected' : '' }}>
                        {{ $user->prenom }} {{ $user->nom }} — {{ ucfirst($user->role) }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Maintenez Ctrl ou Command pour sélectionner plusieurs utilisateurs.</small>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
