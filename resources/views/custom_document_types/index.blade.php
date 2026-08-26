@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Documents personnalisés</h1>
        <a href="{{ route('custom-document-types.create') }}" class="btn btn-primary">Créer un document personnalisé</a>
    </div>

    <p class="text-muted">À la création, vous choisissez le dossier concerné : le document est ajouté directement au sommaire du dossier, prêt à être rempli.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($types->isEmpty())
        <p>Aucun document personnalisé n'a encore été créé.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nom du document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($types as $t)
                    <tr>
                        <td>{{ $t->nom }}</td>
                        <td>
                            <a href="{{ route('custom-document-types.edit', $t) }}" class="btn btn-secondary btn-sm">Modifier</a>

                            <form action="{{ route('custom-document-types.destroy', $t) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Confirmer la suppression ?');">
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
