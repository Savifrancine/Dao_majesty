@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Entreprises</h1>
        <a href="{{ route('entreprises.create') }}" class="btn btn-primary">Ajouter une entreprise</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($entreprises->isEmpty())
        <p>Aucune entreprise n'a encore été ajoutée.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Sigle</th>
                    <th>Pays</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entreprises as $e)
                    <tr>
                        <td>{{ $e->nom }}</td>
                        <td>{{ $e->sigle ?? '-' }}</td>
                        <td>{{ $e->pays ?? '-' }}</td>
                        <td>{{ $e->telephone ?? '-' }}</td>
                        <td>
                            <a href="{{ route('entreprises.edit', $e) }}" class="btn btn-secondary btn-sm">Modifier</a>

                            <form action="{{ route('entreprises.destroy', $e) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Confirmer la suppression ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
