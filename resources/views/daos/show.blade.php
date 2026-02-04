@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-4">Détails du DAO</h2>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ID :</strong> {{ $dao->id }}</p>
                            <p><strong>Nom :</strong> {{ $dao->nom }}</p>
                            <p><strong>Email :</strong> {{ $dao->email ?? '-' }}</p>
                            <p><strong>Téléphone :</strong> {{ $dao->telephone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Adresse :</strong> {{ $dao->adresse ?? '-' }}</p>
                            <p><strong>Ville :</strong> {{ $dao->ville ?? '-' }}</p>
                            <p><strong>Code Postal :</strong> {{ $dao->code_postal ?? '-' }}</p>
                            <p><strong>Statut :</strong> 
                                @if ($dao->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($dao->description)
                        <div class="mb-3">
                            <p><strong>Description :</strong></p>
                            <p>{{ $dao->description }}</p>
                        </div>
                    @endif

                    <div class="mb-3">
                        <p><strong>Créé le :</strong> {{ $dao->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Modifié le :</strong> {{ $dao->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('daos.edit', $dao) }}" class="btn btn-warning">Éditer</a>
                <form action="{{ route('daos.destroy', $dao) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                </form>
                <a href="{{ route('daos.index') }}" class="btn btn-secondary">Retour</a>
            </div>
        </div>
    </div>
</div>
@endsection
